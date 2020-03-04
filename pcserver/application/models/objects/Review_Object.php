<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Data_Model.php';
/**
 * Review Object Model
 * 
 * @author Mark Wickline 2020-03-03
 */

class Review_Object extends Data_Model{

    public $id;
    public $user_id;
    public $pizza_id;
    public $create_date;
    public $body;

    public function __construct(){
        parent::__construct();
        $this->table = 'users';
        $this->intFields = array_merge($this->intFields, ['user_id', 'pizza_id']);
    }
}