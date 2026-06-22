<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schema7 extends CI_Controller
{
    public function add_operator_columns()
    {
        $this->db->db_debug = FALSE;
        $output = array();

        $columns = array(
            'day_shift_operators'   => "TEXT NULL DEFAULT NULL",
            'night_shift_operators' => "TEXT NULL DEFAULT NULL",
            'operator_name'         => "TEXT NULL DEFAULT NULL",
            'day_shift_op1'         => "VARCHAR(200) NULL DEFAULT NULL",
            'day_shift_op2'         => "VARCHAR(200) NULL DEFAULT NULL",
            'night_shift_op1'       => "VARCHAR(200) NULL DEFAULT NULL",
            'night_shift_op2'       => "VARCHAR(200) NULL DEFAULT NULL",
            'production_images'     => "TEXT NULL DEFAULT NULL",
        );

        foreach ($columns as $col => $def) {
            $check = $this->db->query("SHOW COLUMNS FROM tbl_production_report LIKE '" . $col . "'");
            if ($check->num_rows() === 0) {
                $this->db->query("ALTER TABLE tbl_production_report ADD COLUMN " . $col . " " . $def);
                $output[] = "ADDED: " . $col;
            } else {
                $output[] = "EXISTS: " . $col;
            }
        }

        $verify = $this->db->query("SHOW COLUMNS FROM tbl_production_report WHERE Field IN ('day_shift_operators','night_shift_operators','production_images')");
        $output[] = "---";
        $output[] = "Key columns present: " . $verify->num_rows() . "/3";
        $output[] = "Migration complete.";

        header('Content-Type: text/plain');
        echo implode("\n", $output);
    }
}
