<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Pizza Chronicle Install Controller.
 * 
 * To run these modules use the follow command as example
 * php index.php Install method_name
 * 
 * @author Mark Wickline 2020-03-16
 */

class Install extends CI_Controller{
	public function __construct(){
		parent::__construct();
		is_cli() or die("This controller is CLI only.");
		//TODO lock this down to a specific IP address.
		$this->load->model('install_model');
	}

	public function index(){
		$this->install_model->run();
	}
}