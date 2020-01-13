<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_Model extends CI_Model{
    public function __construct(){
        parent::__construct();
    }

    public function verifyCredentials($username, $password){
        $qrs = $this->db->get_where('users', ['username' => $username]);
        if(!$qrs || $qrs->num_rows() <= 0)
            return false;
        if(password_verify($password, $hash))
            return true;
        return false;
    }

    public function createUser(){
        //TODO
    }
}