<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Interacts with the User_Model to privide user functionality.
 * 
 * @author Mark Wickline 2020-01-13
 */

class User extends Base{

    public function __construct(){
        parent::__construct();
        $this->loadAuthToken();
        $this->_init();
    }

    public function hello_get(){
        $this->response([
            'status' => true,
            'message' => "Hello world!"
        ], 200);
    }
}