<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once APPPATH . 'models/Data_Model.php';
/**
 * User Object model.
 * 
 * @author Mark Wickline 2020-03-03
 */

class User_Object extends Data_Model{

    public $id;
    public $username;
    public $first_name;
	public $last_name;
	public $email;
	public $create_date;
	public $last_login_date;
    public $last_activity_date;
    public $app_state;
	protected $user_type = 0;
    protected $pass_hash;

    public function __construct(){
        parent::__construct();
        //Help describe $this to Data_Model
        $this->table = 'users';
        $ignoreFields = [
            'app_state'
        ];
        $this->ignoreFields = array_merge($this->ignoreFields, $ignoreFields);
        $encryptedFields = [
            'first_name' => [ 'blind_index' => 'first_name_bli' ],
            'last_name' => [ 'blind_index' => 'last_name_bli' ],
            'email' => [ 'blind_index' => 'email_bli' ]
        ];
        $this->encryptedFields = array_merge($this->encryptedFields, $encryptedFields);
    }

    public function loadFromUsername($username){
        $user = $this->getRowWhere($this->table, ['username' => $username]);
        if(empty($user)) return false;
        $this->loadThis($user);
        $this->stampActivity();
        return true;
    }

    public function verifyPassword($password){
        if(password_verify($password, $this->pass_hash))
            return true;
        return false;
	}

	public function hashPassword($password){
		$this->pass_hash = password_hash($password, PASSWORD_DEFAULT);
	}

    /**
     * Whenever a user reloads or performs activities, lets stamp
     * their activity column. Wondering if this could be handled using JWT instead.
     */
    public function stampActivity(){
        $this->last_activity_date = sqlTimeStamp();
        $this->saveThis(true);
    }
}