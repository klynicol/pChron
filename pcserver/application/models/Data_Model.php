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
     * from the database.
     */
    protected $intFields = ['id'];

    /**
     * A list of class properties that should be ignored
     * when saving to the database.
     * 
     * These names will be off limits to regular property names.
     */
    
    protected $ignoreFields = ['id', 'table', 'ignoreFields', 'intFields'];


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
     * Will take in a result array and load $this from it.
     * 
     * @param arrayy $resultArray
     */
    public function loadThis($resultArray){
        foreach($resultArray as $key => $value){
            if(property_exists($this, $key)){
                if(in_array($key, $this->intFields)){
                    $value = intval($value);
                }
                $this->$key = $value;
            }
        }
    }

    /**
     * Iterate through the calling object and replace the database data.
     */
    public function saveThis(){
        $data = [];
        foreach($this as $key => $value){
            if(in_array($key, $this->ignore)) continue;
            $data[$key] = $value;
        }
        $this->db->replace($this->table, $data);
    }

    /**
     * Iterate through the calling object and insert the database data.
     * Saves the new id to the calling object.
     */
    public function insertThis(){
        $data = [];
        foreach($this as $key => $value){
            if(in_array($key, $this->ignore)) continue;
            $data[$key] = $value;
        }
        $this->db->insert($this->table, $data);
        $this->id = $this->db->insert_id();
    }

    /**
     * Overloader for private properties. Don't need this?
     */
    public function __set($name, $value){
        $this->$name = $value;
    }
}

