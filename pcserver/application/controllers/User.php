<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User based transactions.
 * 
 * @author Mark Wickline 2020-01-13
 */

class User extends Base{

    public function __construct(){
        parent::__construct();
        $this->loadAuthToken();
        $this->_init();
        $this->load->model('objects/user_object');
    }

    public function hello_get(){
        $this->response([
            'status' => true,
            'message' => "Hello world!"
        ], 200);
    }
}