<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Base_Model.php';
/**
 * Models that extend from this model are synonymous with their
 * database conterpart. This way they can be easily loaded and saved.
 * 
 * It's essential allowing to load $this and save $this from models.
 * 
 * I created this model as an alterantive to codeigniters custom_row_object
 * and custom_results_object which didn't seem to allow loading $this.
 * 
 */
abstract class Data_Model extends Base_Model{

    /**
     * To be set from classes that extend. The table the data lives on.
     */
    protected $table;

    /**
     * A list of fields that should be converted to integers when pulling data
     * from the database. This unfotunately isn't being done automatically.
     */
    protected $intFields = ['id'];

    /**
     * Fields that should be json_encoded when storing to database
     */
    protected $jsonFields = [];

    /**
     * A list of class properties that should be ignored
     * when saving to the database.
     * 
     * These names will be off limits to regular property names.
     */
    
    protected $ignoreFields = [
        'id',
        'table',
        'ignoreFields',
        'intFields',
        'cipher',
        'encryptedFields',
        'jsonFields'
    ];

    /**
     * These fields require to be encrypted before being stored
     * data will be array as such
     * 
     * 'example_field' => [ 'blind_index' => 'blind_index_name' ]
     * 
     * If there's no blind index one will not be added.
     */
    protected $encryptedFields = [];


    public function __construct(){
        parent::__construct();
    }

    /**
     * Every table in the database has a primary key of 'id'.
     * This is a simple method to load a single row based on id.
     */
    public function loadFromId($id){
        $row = $this->getRowWhere($this->table, ['id' => $id]);
        if(empty($row)) return false;
        $this->loadThis($row);
        return true;
    }

    /**
     * A simple way to populate the properties of $this
     * from and object or an associative array.
     */
    public function setProperties($data){
        foreach($data as $key => $value){
            $this->_setProperty($key, $value);
        }
    }

    /**
     * Set a single property on the calling class.
     * 
     * @param string $key
     * @param mixed $value
     * @param boolean $handleCrypto Should we decrypt this field?
     * @param boolean $handleJson Should we json_decode this field?
     */
    private function _setProperty($key, $value, $handleCrypto = false, $handleJson = false){
        if(!property_exists($this, $key))
            return;
        if(in_array($key, $this->intFields)){
            $value = intval($value);
        }
        if($handleCrypto && array_key_exists($key, $this->encryptedFields)){
            //TODO, find a better way to handle this.
            $this->cipher->clear();
            $this->cipher->setTable($this->table);
            $value = $this->cipher->decryptField($value, $key);
        }
        if($handleJson && in_array($key, $this->jsonFields)){
            $value = json_decode($value, true);
        }
        $this->$key = $value;
    }

    /**
     * Prepares the data in this for storage.
     * Paying attention to ignore fields, encrypted fields
     * Creates a new data array from this and returns it.
     * 
     * @param boolean $ignoreCrypto Ignoring crypto will speed things up though changes to senstive data won't be saved.
     * @return array Data array to be stored in the database.
     */
    private function _prepareStorageData($ignoreCrypto){
        $data = [];
        $this->cipher->clear();
        $ignoreFields = $ignoreCrypto ? array_merge($this->ignoreFields, array_keys($this->encryptedFields)) : $this->ignoreFields;
        $hasEncryptedData = false;
        foreach($this as $key => $value){
            if(in_array($key, $ignoreFields))
                continue;
            elseif(array_key_exists($key, $this->encryptedFields)){
                $hasEncryptedData = true;
                $blindIndex = $this->encryptedFields[$key]['blind_index'] ?? '';
                $this->cipher->addField($value, $key, $blindIndex);
            }
            elseif(in_array($key, $this->jsonFields)){
                $data[$key] = json_encode($value);
            } else
                $data[$key] = $value;
        }
        if($hasEncryptedData){
            $this->cipher->setTable($this->table);
            if($cryptoData = $this->cipher->encryptFields())
                $data = array_merge($cryptoData, $data);
        }
        return $data;
    }

    /**
     * Will take in a result array and load $this from it.
     * 
     * @param arrayy $resultArray
     */
    public function loadThis($resultArray){
        foreach($resultArray as $key => $value){
            if(!isset($value))
                continue;
            $this->_setProperty($key, $value, true, true);
        }
    }

    /**
     * Iterate through the calling object and replace the database data.
     * 
     * @param boolean $ignoreCrypto Ignoring crypto will speed things up though changes to senstive data won't be saved.
     */
    public function saveThis($ignoreCrypto = false){
        $data = $this->_prepareStorageData($ignoreCrypto);
        return $this->updWhere($this->table, ['id' => $this->id], $data);
    }

    /**
     * Iterate through the calling object and insert the database data.
     * Saves the new id to the calling object.
     */
    public function insertThis(){
        $data = $this->_prepareStorageData(false);
        $this->id = $this->ins($this->table, $data);
    }

    /**
     * Overloader for private properties. Don't need this?
     */
    public function __set($name, $value){
        $this->$name = $value;
    }
}

