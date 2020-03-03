<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Install extends CI_Controller{
	public function __construct(){
		parent::__construct();
		if(!is_cli()) die("This controller is CLI only.");
		//TODO lock this down to a specific IP address.
		$this->load->model('install_model');
	}

	public function index(){
		$this->install_model->run();
	}
}