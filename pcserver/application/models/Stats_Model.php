<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Base_Model.php';
/**
 * This model will pull various stats from the database.
 * 
 * Model originally built to handle user post counts and such.
 * 
 * @author Mark Wickline 2020-03-03
 */

class Stats_Model extends Base_Model{

    public function __construct(){
        parent::__construct();
    }

    public function getUserPostCount($id){
        //TODO
    }

    public function getUserReviewCount(){
        //TODO
    }
}