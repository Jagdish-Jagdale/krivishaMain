<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DatabaseSetup extends CI_Controller
{
    public function setup_operators()
    {
        $this->load->dbforge();
        
        if (!$this->db->table_exists('tbl_production_operators')) {
            $fields = array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
                'production_id' => array('type' => 'INT', 'constraint' => 11),
                'operator_id' => array('type' => 'INT', 'constraint' => 11),
                'shift' => array('type' => 'VARCHAR', 'constraint' => '10'),
            );
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_field($fields);
            
            if ($this->dbforge->create_table('tbl_production_operators', TRUE)) {
                echo "Table tbl_production_operators created successfully.\n";
            } else {
                echo "Failed to create tbl_production_operators.\n";
            }
        } else {
            echo "Table tbl_production_operators already exists.\n";
        }
    }
}
