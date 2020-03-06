<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Modify the database quickly and efficiently.
 * 
 * THIS CLASS WAS NOT INTENTED TO PROVIDE FUNCTIONALITY FOR
 * RENAMING COLUMNS. ONLY CREATING AND DELETING.
 * 
 * If you need to modify a column do so in a database editor and replicate
 * the changes onto the.
 * 
 * It's a good idea to create a backup before running this install.
 * 
 * @author Mark Wickline 2020-03-03
 */
class Install_Model extends CI_Model{
    /**
     * properties are names of tables with create code as values.
     */

    private $users = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(255) NOT NULL',
        'first_name' => 'VARCHAR(255)',
        'first_name_bli' => 'VARCHAR(110)',
        'last_name' => 'VARCHAR(255)',
        'last_name_bli' => 'VARCHAR(110)',
        'email' => 'VARCHAR(255)',
        'email_bli' => 'VARCHAR(110)',
        'create_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'last_login_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'last_activity_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'pass_hash' => 'VARCHAR(580) NOT NULL',
        'user_type' => 'TINYINT NOT NULL'
    ];

    private $reviews = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'pizza_id' => 'INT NOT NULL',
        'create_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'body' => 'TEXT'
    ];


	public function __construct(){
		parent::__construct();
        $this->load->dbforge();
	}

	public function run(){
        $engine = array('ENGINE' => 'InnoDB');
        foreach($this as $table => $array){

            $tableExits = false;

            if($this->db->table_exists($table)){
                $this->dbforge->rename_table($table, $table . "_old");
                $tableExits = true;
            }

            if(is_array($array)){
                //Create the new table from array values.
                foreach($array as $fieldName => $definition){
                    $this->dbforge->add_field($fieldName . " " . $definition);
                }
                $this->dbforge->create_table($table, FALSE, $engine);
            } else {
                //We are working with a string, just run it in a query
                $this->db->query($array);
            }


            if($tableExits){
                //We have to iterate over old fields and see if any were dropped.
                //Then run a query to grab the old data.
                $copyFields = [];
                foreach($this->db->list_fields($table . "_old") as $field){
                    if(array_key_exists($field, $this->$table))
                        $copyFields[] = $field;
                }
                $copyFieldsString = implode(',', $copyFields);
                $this->db->query("
                    INSERT INTO {$table} ({$copyFieldsString})
                    SELECT $copyFieldsString
                    FROM {$table}_old
                ");
                //Finally drop the old table.
                $this->dbforge->drop_table($table . "_old"); 
            }
        }
	}
}