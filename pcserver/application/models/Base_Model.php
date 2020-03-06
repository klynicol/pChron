<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . "/libraries/Cipher_Sweet.php";
/**
 * Common database functionality. All models should extend this model.
 * 
 * @author Mark Wickline 2020-03-03
 */
abstract class Base_Model extends CI_Model{

    protected $cipher;

    public function __construct(){
        parent::__construct();
        $this->cipher = new Cipher_Sweet();
    }

    /**
     * Updates a table and returns true or false.
     * 
     * @param string $table
     * @param array|object $where
     * @param array|object $data
     * @return bool
     */
    protected function updWhere($table, $where, $data)
    {
        foreach($where as $key => $value){
            $this->db->where($key, $value);
        }
        $this->db->update($table, $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * inserts data into any table and returns the insert id
     * 
     * @param string $table
     * @param array|object $data
     */
    protected function ins($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    /**
     * A general method for fetching data with a simple query.
     * 
     * @param string $table
     * @param array|object $where Key value pairs of WHERE clause items or keycol value
     * @param string $select Comma separated list of selects.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function getWhere($table, $where = NULL, $select = NULL, $limit = NULL, $offset = NULL, $single = false){

        if($select)
            $this->db->select($select);

        //If we're not dealing with an array or object return empty.
        if(isset($where) && !in_array(gettype($where), ['array', 'object'], true))
            return [];

        $result = $this->db->get_where($table, $where, $limit, $offset);

        if($result && $result->num_rows())
            if($single)
                return $result->row_array();
            else
                return $result->result_array();
        return [];
    }

    /**
     * A general method for fetching A SINGLE ROW with a simple query.
     * 
     * @param string $table
     * @param array|object $where Key value pairs of WHERE clause items or keycol value
     * @param string $select Comma separated list of selects.
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function getRowWhere($table, $where = NULL, $select = NULL, $limit = NULL, $offset = NULL){
        return $this->getWhere($table, $where, $select, $limit, $offset, true);
    }

    /**
     * A catch-all delete method.
     * 
     * @param string $table
     * @param array|object $where
     * @return bool
     */
    public function delWhere($table, $where){
        $this->db->delete($table, $where);
        if($this->db->affected_rows() > 0)
            return true;
        return false;
    }
}