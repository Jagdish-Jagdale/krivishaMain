<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Stock_helper.php
 *
 * Centralised stock transaction logger.
 * Call log_stock_transaction() after every successful stock-affecting DB write.
 * This writes one row to tbl_stock_transactions — the single source of truth
 * for the Stock Ledger Report.
 *
 * All existing tables (tbl_raw_material_stock_report_history etc.) are kept
 * intact; this is purely additive.
 */

/**
 * Map an is_inward_outward numeric flag + item info to a human transaction_type
 * and movement_type (IN / OUT).
 *
 * @param  string $flag       The is_inward_outward value (0-7)
 * @param  array  $row_data   The original stock_log_data array (for contextual clues)
 * @param  string $item_type  'raw_material' | 'master_batch' | 'article' | 'mould'
 * @return array  ['transaction_type' => string, 'movement_type' => 'IN'|'OUT']
 */
function _stock_map_flag_to_type(string $flag, array $row_data, string $item_type): array
{
    switch ($flag) {
        case '0':
            if (!empty($row_data['inward_no']) || !empty($row_data['inward_qty'])) {
                return ['transaction_type' => 'Inward (Supplier)', 'movement_type' => 'IN'];
            }
            return ['transaction_type' => 'Opening Balance', 'movement_type' => 'IN'];

        case '1':
            return ['transaction_type' => 'Inward (Supplier)', 'movement_type' => 'IN'];

        case '2':
            // Distinguish dispatch vs requisition vs transfer
            if (!empty($row_data['dispatch_party_name']) || !empty($row_data['production_id'])) {
                return ['transaction_type' => 'KVI Sales', 'movement_type' => 'OUT'];
            }
            if (!empty($row_data['schedule_id'])) {
                return ['transaction_type' => 'Requisition Issued', 'movement_type' => 'OUT'];
            }
            return ['transaction_type' => 'Plant to Plant Transfer', 'movement_type' => 'OUT'];

        case '3':
            return ['transaction_type' => 'Stock Adj. +', 'movement_type' => 'IN'];

        case '4':
            return ['transaction_type' => 'Stock Adj. -', 'movement_type' => 'OUT'];

        case '5':
            if (in_array($item_type, ['article', 'mould'])) {
                return ['transaction_type' => 'Production Output', 'movement_type' => 'IN'];
            }
            return ['transaction_type' => 'Production Used', 'movement_type' => 'OUT'];

        case '6':
            return ['transaction_type' => 'Return to Store', 'movement_type' => 'IN'];

        case '7':
            return ['transaction_type' => 'Stock Transfer IN', 'movement_type' => 'IN'];

        default:
            return ['transaction_type' => 'Other', 'movement_type' => 'IN'];
    }
}

/**
 * log_stock_transaction()
 *
 * Insert one row into tbl_stock_transactions.
 *
 * Accepted $params keys:
 *   item_type        string  'raw_material'|'master_batch'|'article'|'mould'  (required)
 *   item_id          int     ID of the item in its master table               (required)
 *   plant_id         int                                                       (required)
 *   transaction_type string  Human-readable movement type                     (required)
 *   movement_type    string  'IN' or 'OUT'                                    (required)
 *   qty              float   Quantity moved                                   (required)
 *   balance_qty      float   Running balance AFTER this transaction           (required)
 *   uom_id           int     (optional)
 *   reference_no     string  inward_no / schedule_id / production_id etc.    (optional)
 *   reference_source string  'inward'/'production'/'dispatch'/'adjustment'   (optional)
 *   transaction_date string  Y-m-d  (defaults to today if omitted)
 *   created_by       int     session user id (optional)
 *   legacy_source    string  'rm_history'|'mb_history' — set during migration only
 *   legacy_id        int     original row id from the legacy table
 *
 * @param array $params
 * @return int|false  Insert ID on success, false on failure
 */
function log_stock_transaction(array $params)
{
    $CI =& get_instance();

    // Guard: required fields
    $required = ['item_type', 'item_id', 'plant_id', 'transaction_type', 'movement_type', 'qty', 'balance_qty'];
    foreach ($required as $field) {
        if (!isset($params[$field]) || $params[$field] === '' || $params[$field] === null) {
            // Silently skip to avoid breaking existing flows
            return false;
        }
    }

    // Fetch denormalized item name if not provided
    $item_name = $params['item_name'] ?? null;
    if (empty($item_name)) {
        switch ($params['item_type']) {
            case 'raw_material':
                $r = $CI->db->select('rm_name')->where('id', $params['item_id'])->get('tbl_rm_master')->row();
                $item_name = $r ? $r->rm_name : null;
                break;
            case 'master_batch':
                $r = $CI->db->select('name')->where('id', $params['item_id'])->get('tbl_mb_master')->row();
                $item_name = $r ? $r->name : null;
                break;
            case 'article':
            case 'mould':
                $r = $CI->db->select('article_name')->where('id', $params['item_id'])->get('tbl_mould_parts')->row();
                $item_name = $r ? $r->article_name : null;
                break;
        }
    }

    // Fetch denormalized plant name if not provided
    $plant_name = $params['plant_name'] ?? null;
    if (empty($plant_name) && !empty($params['plant_id'])) {
        $r = $CI->db->select('plant_name')->where('id', $params['plant_id'])->get('tbl_plant_master')->row();
        $plant_name = $r ? $r->plant_name : null;
    }

    $data = [
        'transaction_date' => $params['transaction_date'] ?? date('Y-m-d'),
        'created_on'       => date('Y-m-d H:i:s'),
        'item_type'        => $params['item_type'],
        'item_id'          => (int) $params['item_id'],
        'item_name'        => $item_name,
        'plant_id'         => (int) $params['plant_id'],
        'plant_name'       => $plant_name,
        'transaction_type' => $params['transaction_type'],
        'movement_type'    => $params['movement_type'],
        'qty'              => (float) $params['qty'],
        'uom_id'           => isset($params['uom_id']) ? (int) $params['uom_id'] : null,
        'reference_no'     => $params['reference_no']     ?? null,
        'reference_source' => $params['reference_source'] ?? null,
        'balance_qty'      => (float) $params['balance_qty'],
        'created_by'       => $params['created_by']       ?? null,
        'legacy_source'    => $params['legacy_source']    ?? null,
        'legacy_id'        => isset($params['legacy_id']) ? (int) $params['legacy_id'] : null,
        'is_deleted'       => 0,
    ];

    $CI->db->insert('tbl_stock_transactions', $data);
    return $CI->db->insert_id();
}
