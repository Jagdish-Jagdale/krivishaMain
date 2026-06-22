<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schema extends CI_Controller
{
    public function check_production()
    {
        $sql = "SELECT is_inward_outward, COUNT(*) as cnt FROM tbl_raw_material_stock_report_history WHERE article_id IS NOT NULL AND article_id != 0 GROUP BY is_inward_outward";
        $query = $this->db->query($sql);
        echo "<pre>";
        print_r($query->result_array());
        
        $sql2 = "SELECT * FROM tbl_raw_material_stock_report_history WHERE article_id IS NOT NULL AND article_id != 0 AND is_inward_outward = '5' LIMIT 5";
        $query2 = $this->db->query($sql2);
        print_r($query2->result_array());
        
        $sql3 = "SELECT * FROM tbl_raw_material_stock_report_history WHERE article_id IS NOT NULL AND article_id != 0 AND is_inward_outward = '1' LIMIT 5";
        $query3 = $this->db->query($sql3);
        print_r($query3->result_array());
    }
}
