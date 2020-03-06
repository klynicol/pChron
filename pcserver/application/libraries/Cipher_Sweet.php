<?php defined('BASEPATH') OR exit('No direct script access allowed');
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\Transformation\LastFourDigits;
use ParagonIE\ConstantTime\Hex;

/**
 * CipherSweet is a backend library developed by Paragon Initiative Enterprises for implementing searchable field-level encryption.
 * 
 * Requires PHP 5.5+, although 7.2 is recommended for better performance.
 * 
 * TODO, need methods for swaping out the master key.
 * 
 * There's a lot more functionality that can be added to the this class.
 * See links for details.
 * 
 * @see https://github.com/paragonie/ciphersweet
 * @see https://paragonie.com/blog/2019/01/ciphersweet-searchable-encryption-doesn-t-have-be-bitter
 * @see https://ciphersweet.paragonie.com/php
 * 
 * 
 * # USE EXAMPLES
 * 
 * @author Mark Wickline 2020-03-04
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
     * Decrypte a single field. Will not decrypt blind indexes.
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
     * Decrypt object or associative array and return the result array.
     * TODO, there's most likely a way to speed this up.
     * 
     * @param object|array
     * @return boolean|array|object
     * @throws Exception
     */
    public function decryptRow($row){
        if(!is_array($row) || !is_object($row)){
            $this->error = "decryptFields param needs to be an array or object";
            throw new Exception("Cipher_Sweet::decryptFields param needs to be an array or object");
        }
        if(!$this->_checkProperties())
            throw new Exception("Cipher_Sweet Table name cannot be empty");
        
        foreach($row as $key => &$value){
            $value = $this->decryptField($value, $key);
        }
        return $row;
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
        return $field->getBlindIndex($input, $blindColumnName)[$blindColumnName];
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
            $this->error = "Table name cannot be emtpty";
            return false;
        }
        if(!isset($this->engine)){
            $this->error = "Crypto engine is not specified";
            return false;
        }
        return true;
    }

}