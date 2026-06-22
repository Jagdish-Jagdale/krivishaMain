<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schema6 extends CI_Controller
{
    public function check_stock_transfer()
    {
        $q = $this->db->query("SELECT * FROM tbl_stock_transactions WHERE transaction_type = 'Stock Transfer IN' AND item_type = 'article' LIMIT 10");
        echo "<pre>";
        print_r($q->result_array());
        echo "</pre>";

        $q2 = $this->db->query("SELECT * FROM tbl_raw_material_stock_report_history WHERE is_inward_outward = '7' AND article_id IS NOT NULL AND article_id != 0 LIMIT 10");
        echo "<pre>History:\n";
        print_r($q2->result_array());
        echo "</pre>";
    }
}
