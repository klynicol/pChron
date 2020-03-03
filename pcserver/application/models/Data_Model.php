<?php defined('BASEPATH') OR exit('No direct script access allowed');
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
class Data_Model extends CI_Model{

    protected $table;

    public function __construct(){
        parent::__construct();
    }

    /**
     * Will take in a result array and load $this from it.
     */
    public function loadThis($resultArray){
        foreach($resultArray as $key => $value){
            if(property_exists($this, $key))
                $this[$key] = $value;
        }
    }

    public function saveThis(){
        $data = [];
        foreach($this as $key => $value){
            if($key == 'table') continue;
            $data[$key] = $value;
        }
        $this->db->replace($this->table, $data);
    }

    /**
     * Overloader for private properties. Don't need this?
     */
    public function __set($name, $value){
        $this[$name] = $value;
    }
}

