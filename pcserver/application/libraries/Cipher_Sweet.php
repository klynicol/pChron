<?php defined('BASEPATH') OR exit('No direct script access allowed');
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\EncryptedMultiRows;
use ParagonIE\CipherSweet\Transformation\LastFourDigits;
use ParagonIE\ConstantTime\Hex;
//include_once 'vendor/autoload.php';

/**
 * #-------- CLASS BASIC INFORMATION -----------
 * 
 * CipherSweet is a backend library developed by Paragon Initiative Enterprises for implementing searchable field-level encryption.
 * 
 * Requires PHP 5.5+, although 7.2 is recommended for better performance.
 * 
 * Installation, use `composer require paragonie/ciphersweet`
 * 
 * TODO, need methods for swaping out the key file.
 * 
 * This class is ultimately setup to handle encrypting
 * or decrypting one table at a time. Multi-table and other functionality
 * may be added if necessary. See links below for documentation.
 * 
 * @see https://github.com/paragonie/ciphersweet
 * @see https://paragonie.com/blog/2019/01/ciphersweet-searchable-encryption-doesn-t-have-be-bitter
 * @see https://ciphersweet.paragonie.com/php
 * 
 * @author Mark Wickline 2020-03-04
 * 
 * #--------- USAGE AND EXAMPLES -------------
 * 
//First, create a new Cipher_Sweet instance
$cipher = new Cipher_Sweet();

//Second, set the table name you'll be working on.
//CipherSweet uses table name and column name as part of the encryption key.
//It's very important to set table name before performing anything with this class.
$cipher->setTable('table_name');

//Now you can add fields that you want ecrypted.
//The 3rd parameter adds a searchable "blind index" to the encrypted results.
$cipher->addField('The value you want encrypted', 'column_name', 'blind_index_name'); //example with blind index.
$cipher->addField('The value you want encrypted', 'column_name_2'); //no blind index on this.

//Run the encryption and get the resulting array.
if(!$fields = $cipher->encryptFields())
    echo $cipher->error;
var_dump($fields);

//Clear the class for the next opperations! IMPORTANT
$cipher->clear();

//To decrypt a single field use the code below.
$cipher->setTable('table_name');
try{
    $cryotText = 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU';
    echo $cipher->decryptField($cryptoText, 'column_name');
} catch(Exception $e){
    echo $e->message();
}

//To decrypt a whole row at a time call decryptRow() like so.
$cipher->setTable('table_name');
try{
    $encrtypedArray = [
        'column_name' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU',
        'column_name_2' => 'nacl:9odZ8qGZC8Qiy_1Dxg_YOQbORwtwgRaaIKKab-PTgLvriQcxhx2t0BMHiUTFbiAU'
    ];
    var_dump($cipher->decryptRow($encrtypedArray));
} catch (Exception $e){
    echo $e->message();
}

//To get a blind index (so you can search a blind index field in the database) use the code below.
//2nd parameter is the regular column namne.
//3rd parameter is the blind index column name.
//Both should have been used while encrypting so we need to specify them again to decrypt properly.
$cipher->setTable('table_name');
$blindIndex = $cipher->getBlindIndex('Mark Wickline', 'full_name', 'full_name_blind_index');
mysqli_query("SELECT * FROM 'table' WHERE 'full_name_blind_index' = {$blindIndex}");

 * 
 */
class Cipher_Sweet{

    const ENC_KEY_PATH = APPPATH . "/config/enc.key";
    /**
     *  Specifying 32 bits, this should be good for over 250,000 records. 
     * More records will need more. The more bits you have the easier it is for 
     * attackers to crack the code. The less you have you will run into collisions
     * on blind indexes. It's a catch 22.
     */
    const INDEX_SIZE = 32;

    /**@var CipherSweet */
    private $engine;
    /**@var EncryptedMultiRow */
    private $encrytedRow;
    /**@var string */
    private $tableName = '';
    /**@var array */
    private $columns = [];
    /**@var array */
    private $blindIndexColumns = [];
    /**@var string */
    public $error;
    
    public function __construct($backend = 'modern'){
        $provider = new StringProvider(file_get_contents(self::ENC_KEY_PATH));
        if($backend === 'modern'){
            $this->engine = new CipherSweet($provider);
        } elseif($backend === 'fips'){
            $this->engine = new CipherSweet($provider, new FIPSCrypto());
        } else{
            $this->error = "A valid crypto method was not specified";
        }
    }

    /**
     * Set the table name to used in all algorithms.
     */
    public function setTable($tableName){
        $this->tableName = $tableName;
    }

    /**
     * Clears columns and table name.
     */
    public function clear(){
        $this->errror = '';
        $this->tableName = '';
        $this->columns = [];
        $this->blindIndexColumns = [];
    }

    /**
     * Add field to columns to be encrypted.
     * 
     * We need the table and column names to be used in the algorithm because...
     * "specific row, thereby preventing an attacker capable of replacing ciphertexts 
     * and using legitimate app access to decrypt ciphertexts they wouldn't otherwise have access to."
     * 
     * @param mixed $input
     * @param string $columnName
     * @param string $blindIndexName
     */
    public function addField($input, $columnName, $blindIndexName = ''){
        $this->columns[$columnName] = $input;
        if(!empty($blindIndexName))
            $this->blindIndexColumns[$columnName] = $blindIndexName;
    }

    /**
     * Encrypts the fields and returns an associated array with
     * key value pairs of data. False on failure.
     * 
     * @param int $indexSize The size of the blind indexes that will be created, if any.
     * @return array|false
     */
    public function encryptFields($indexSize = self::INDEX_SIZE){
        if(!$this->_checkProperties()){
            return false;
        }
        $this->encrytedRow = new EncryptedRow($this->engine, $this->tableName);
        $this->encrytedRow->setFlatIndexes(true);
        foreach($this->columns as $colKey => $colVal){
            $type = gettype($colVal);
            switch($type){
                case 'boolean':
                    $this->encrytedRow->addBooleanField($colKey);
                    break;
                case 'double':
                    $this->encrytedRow->addFloatField($colKey);
                    break;
                case 'integer':
                    $this->encrytedRow->addIntegerField($colKey);
                    break;
                case 'string':
                    $this->encrytedRow->addTextField($colKey);
                    break;
                default:
                    $this->error = "Can't add field type {$type} to fields";
                    return false;
            }
        }
        foreach($this->blindIndexColumns as $regName => $blindName){
            $this->encrytedRow->addBlindIndex( $regName,  new BlindIndex ( $blindName, [], $indexSize) );
        }
        $prepared = $this->encrytedRow->prepareRowForStorage($this->columns);
        return array_merge($prepared[0], $prepared[1]);
    }

    /**
     * Decrypte a single field.
     * 
     * @param mixed $cryptoValue
     * @param string $columnName
     * @return mixed Decrypted Value
     * @throws Exception
     */
    public function decryptField($cryptoValue, $columnName){
        if(!$this->_checkProperties()){
            throw new Exception("Cipher_Sweet Table name cannot be empty");
        }
        $field = new EncryptedField($this->engine, $this->tableName, $columnName);
        return $field->decryptValue($cryptoValue);
    }

    /**
     * Decrypt associative array and return the result array.
     * example input 
     * 
     * [
     *  "column_name" => "nacl:PFviUirHkVlsL-siDeN1GOW1Cx3dlF9R2sdv_ctO3Qn3QRDsBok73JIyVH0=",
     *  "column_name_2" => "nacl:PFviUirHkVlsL-siDeN1GOW1Cx3dlF9R2sdv_ctO3Qn3QRDsBok73JIyVH0="
     * ]
     * 
     * @param array
     * @return array<string, array<string, string|int|float|bool|null>
     * @throws Exception
     * @throws CryptoOperationException
     * @throws SodiumException
     * @throws CipherSweetException
     */
    public function decryptRow($row){
        if(!is_array($row)){
            $this->error = "decryptFields param needs to be an array";
            throw new Exception("Cipher_Sweet::decrypMulti param one needs to be an array");
        }
        if(!$this->_checkProperties()){
            throw new Exception("Cipher_Sweet Table name cannot be empty");
        }
        $multiRow = new EncryptedMultiRows($this->engine);
        foreach($row as $key => $value){
            $multiRow->addField($this->tableName, $key);
        }
        return $multiRow->decryptManyRows( [ $this->tableName => $row ] );
    }

    /**
     * Return a searchable blind index
     * 
     * @param mixed $input The plain text to search for
     * @param string $columnName
     * @param string $blindColumnName
     * @param integer $indexSize
     * @return string Encrypted value to use against the blind index in the database.
     * @throws Exception
     */
    public function getBlindIndex($input, $columnName, $blindColumnName, $indexSize = self::INDEX_SIZE){
        if(!$this->_checkProperties()){
            throw new Exception("Cipher_Sweet Table name cannot be empty");
        }
        $field = new EncryptedField($this->engine, $this->tableName, $columnName);
        $field->addBlindIndex( new BlindIndex($blindColumnName, [],$indexSize) );
        return $field->getBlindIndex($input, $blindColumnName);
    }

    /**
     * Just like it says, generate a new key.
     */
    public function generateKey(){
        return Hex::encode(random_bytes(32));
    }

    /**
     * Check that the properties in this class are correctly set.
     * 
     * @return boolean
     */
    private function _checkProperties(){
        if(empty($this->tableName)){
            $this->error = "Table name cannot be empty";
            return false;
        }
        if(!isset($this->engine)){
            $this->error = "Crypto engine is not specified";
            return false;
        }
        return true;
    }

}