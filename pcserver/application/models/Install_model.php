<?php

class Install_Model extends CI_Model{
	public function __construct(){
		parent::__construct();
	}

	public function run(){
		$this->db->query("
		CREATE TABLE IF NOT EXISTS users (
			id INT AUTO_INCREMENT PRIMARY KEY,
			username VARCHAR(255) NOT NULL,
			first_name VARCHAR(255),
			last_name VARCHAR(255),
			email VARCHAR(255),
			create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			last_login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			pass_hash VARCHAR(580) NOT NULL,
			user_type TINYINT NOT NULL
		)  ENGINE=INNODB;
		");
	}
}