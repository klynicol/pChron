<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_Model extends Data_Model{

    private $id;
    public $username;
    public $first_name;
	public $last_name;
	public $email;
	public $create_date;
	public $last_login_date;
	private $user_type;
    private $pass_hash;

    public function __construct(){
        parent::__construct();
        $this->table = 'users';
    }

    public function loadFromUsername($username){
        $qrs = $this->db->get_where('users', ['username' => $username]);
        if(!$qrs || $qrs->num_rows() <= 0)
            return false;
        $this->loadThis($qrs->result_array());
    }

    public function verifyPassword($password){
        if(password_verify($password, $this->passHash))
            return true;
        return false;
	}

	public function hashPassword($password){
		$this->pass_hash = password_hash($password, PASSWORD_DEFAULT);
	}
}