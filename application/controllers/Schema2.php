<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schema2 extends CI_Controller
{
    public function execute_backfill()
    {
        $this->db->db_debug = FALSE;
        
        $sql = "
            SELECT dod.*, asr.plant_id as article_plant_id
            FROM tbl_dispatch_order_data dod
            JOIN tbl_outward_orders o ON dod.dispatch_id = o.id
            LEFT JOIN tbl_article_stock_report asr ON dod.article_id = asr.article_id AND asr.is_deleted = '0'
            WHERE dod.is_deleted = '0'
            AND NOT EXISTS (
                SELECT 1 FROM tbl_raw_material_stock_report_history h
                WHERE h.article_id = dod.article_id
                  AND h.is_inward_outward = '2'
                  AND h.outward_qty = dod.dispatch_quantity
                  AND DATE(h.created_on) = DATE(dod.created_on)
            )
            LIMIT 4000
        ";
        
        $query = $this->db->query($sql);
        $missing = $query->result_array();
        
        if (count($missing) == 0) {
            echo "All caught up!";
            return;
        }
        
        $history_batch = [];
        $transaction_batch = [];
        
        foreach ($missing as $row) {
            $plant_id = !empty($row['article_plant_id']) ? $row['article_plant_id'] : 11;
            
            $history_batch[] = array(
                'article_id' => $row['article_id'],
                'plant_id' => $plant_id,
                'opening_stock' => 0,
                'outward_qty' => $row['dispatch_quantity'],
                'total_quantity' => 0,
                'is_inward_outward' => '2',
                'date' => date('Y-m-d', strtotime($row['created_on'])),
                'created_on' => $row['created_on'],
            );
            
            $transaction_batch[] = array(
                'item_type' => 'article',
                'item_id' => $row['article_id'],
                'plant_id' => $plant_id,
                'transaction_type' => 'KVI Sales',
                'movement_type' => 'OUT',
                'qty' => $row['dispatch_quantity'],
                'balance_qty' => 0,
                'reference_source' => 'dispatch',
                'created_by' => 0,
                'transaction_date' => date('Y-m-d', strtotime($row['created_on'])),
                'created_on' => date('Y-m-d H:i:s', strtotime($row['created_on']))
            );
        }
        
        if (!empty($history_batch)) {
            $r1 = $this->db->insert_batch('tbl_raw_material_stock_report_history', $history_batch);
            if (!$r1) { echo "History Error: "; print_r($this->db->error()); return; }
        }
        
        if (!empty($transaction_batch) && $this->db->table_exists('tbl_stock_transactions')) {
            $r2 = $this->db->insert_batch('tbl_stock_transactions', $transaction_batch);
            if (!$r2) { echo "Trans Error: "; print_r($this->db->error()); return; }
        }
        
        echo "Successfully backfilled " . count($history_batch) . " records in batch.";
    }
}
