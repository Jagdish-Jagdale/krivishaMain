<?php
require_once FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Firebase\JWT\JWT;

class Admin_model extends CI_model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	private function _normalize_id_list($value)
	{
		if (is_array($value)) {
			$parts = $value;
		} else {
			$value = trim((string) $value);
			if ($value === '') {
				return [];
			}
			$parts = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
		}

		$unique = [];
		foreach ($parts as $part) {
			$part = trim((string) $part);
			if ($part === '' || !is_numeric($part)) {
				continue;
			}
			$id = (int) $part;
			if ($id > 0) {
				$unique[$id] = true;
			}
		}

		return array_keys($unique);
	}

	private function _assigned_department_ids()
	{
		return $this->_normalize_id_list($this->session->userdata('assign_department_id'));
	}

	private function _assigned_plant_ids()
	{
		return $this->_normalize_id_list($this->session->userdata('assign_plant_id'));
	}

	private function _user_has_department($department_id)
	{
		$department_id = (int) $department_id;
		return in_array($department_id, $this->_assigned_department_ids(), true);
	}

	private function _user_is_unrestricted_access_user()
	{
		// Unrestricted if admin user OR department includes 25 (super admin department).
		if ((string) $this->session->userdata('is_admin') === '1') {
			return true;
		}
		return $this->_user_has_department(25);
	}

	private function _deny_all_rows()
	{
		$this->db->where('1 = 0', null, false);
	}

	private function _apply_where_in_from_ids($column, $ids)
	{
		$id_list = $this->_normalize_id_list($ids);
		if (empty($id_list)) {
			$this->_deny_all_rows();
			return;
		}

		if (strpos($column, 'user_data') !== false) {
			$conditions = [];
			foreach ($id_list as $id) {
				$conditions[] = "FIND_IN_SET({$this->db->escape($id)}, {$column}) > 0";
			}
			$condition_string = '(' . implode(' OR ', $conditions) . ')';
			$this->db->where($condition_string, null, false);
		} else {
			$this->db->where_in($column, $id_list);
		}
	}

	private function _apply_assigned_plants_scope($column = 'plant_id')
	{
		if ($this->_user_is_unrestricted_access_user()) {
			return;
		}
		$this->_apply_where_in_from_ids($column, $this->_assigned_plant_ids());
	}

	private function _apply_assigned_departments_scope($column = 'department_id')
	{
		if ($this->_user_is_unrestricted_access_user()) {
			return;
		}
		$this->_apply_where_in_from_ids($column, $this->_assigned_department_ids());
	}

	private function _restrict_customer_queries($party_id_field = null)
	{
		if ((string) $this->session->userdata('is_admin') !== '1') {
			if ($this->_user_has_department(11) || $this->_user_has_department(12) || $this->_user_has_department(17) || $this->_user_has_department(25)) {
				return;
			}
			$user_id = $this->session->userdata('id');
			if (!empty($user_id)) {
				$esc_user_id = $this->db->escape($user_id);
				if ($party_id_field !== null) {
					$this->db->group_start();
					$this->db->where("($party_id_field IN (SELECT id FROM tbl_customers WHERE attending_salesperson_id = $esc_user_id OR dg_id = $esc_user_id OR asm_id = $esc_user_id OR state_head_id = $esc_user_id OR telecaller_id = $esc_user_id))", null, false);
					$this->db->or_where($party_id_field, '0');
					$this->db->or_where($party_id_field . ' IS NULL', null, false);
					$this->db->group_end();
				} else {
					$this->db->group_start();
					$this->db->where('tbl_customers.attending_salesperson_id', $user_id);
					$this->db->or_where('tbl_customers.dg_id', $user_id);
					$this->db->or_where('tbl_customers.asm_id', $user_id);
					$this->db->or_where('tbl_customers.state_head_id', $user_id);
					$this->db->or_where('tbl_customers.telecaller_id', $user_id);
					$this->db->group_end();
				}
			}
		}
	}

	public function get_user_profile()
	{
		$this->db->where('id', $this->session->userdata('id'));
		$result = $this->db->get('user_data');
		$result = $result->row();
		return $result;
	}

	private function _process_parameter_table_name()
	{
		foreach (['tbl_process_parameter', 'tbl_process_parameters', 'tbl_process_parameter_sheet'] as $table_name) {
			if ($this->db->table_exists($table_name)) {
				return $table_name;
			}
		}

		return null;
	}
	public function login()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->where('email', $this->input->post('email'));
		$this->db->where('org_password', $this->input->post('password'));
		$res = $this->db->get('user_data')->row();
		if (!empty($res)) {
			$data = array(
				'id' => $res->id,
				'is_admin' => $res->is_admin,
				'assign_department_id' => $res->department_id,
				'assign_plant_id' => $res->plant_id,
				'name' => $res->first_name,
			);
			$this->session->set_userdata($data);
			return 1;
		} else {
			return 0;
		}
	}
	public function change_current_password()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->session->userdata('id'));
		$res = $this->db->get('user_data')->row();

		if (!empty($res)) {
			$data = array(
				'org_password' => $this->input->post('new_password'),
			);
			$this->db->where('id', $res->id);
			$this->db->update('user_data', $data);
			return 2;
		} else {
			return 0;
		}
	}

	public function update_profile_image($image_name)
	{
		$user_id = $this->session->userdata('id');
		if (!$user_id)
			return false;

		$data = [
			'emp_photo' => $image_name,
			'updated_on' => date('Y-m-d H:i:s')
		];
		$this->db->where('id', $user_id);
		return $this->db->update('user_data', $data);
	}

	/////////////////////Dashboard Functions ///////////////////////////////
	public function get_own_vehicle_metrics()
	{
		$location_id = $this->input->get('location') ?? '';
		$party_id = $this->input->get('party') ?? '';
		$vehical_id = $this->input->get('vehical') ?? '';
		$date_range = $this->input->get('date') ?? '';

		$this->db->from('tbl_own_vehicle_details');
		$this->db->where('is_deleted', '0');

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		if (!empty($location_id)) {
			$this->db->where('location_id', $location_id);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		if (!empty($vehical_id)) {
			$this->db->where('vehical_id', $vehical_id);
		}

		$this->db->select('tbl_own_vehicle_details.*, (SELECT t2.in_km FROM tbl_own_vehicle_details t2 WHERE t2.vehical_id = tbl_own_vehicle_details.vehical_id AND t2.id < tbl_own_vehicle_details.id AND t2.is_deleted = "0" ORDER BY t2.id DESC LIMIT 1) as actual_out_km');
		
		$query = $this->db->get();
		$records = $query->result();

		$total_exact_km = 0;
		$total_diesel_expense = 0;
		$total_market_freight = 0;
		$total_invoice_value = 0;
		
		$total_driver_exp = 0;
		$total_maint_exp = 0;

		foreach ($records as $member) {
			$in_val = (float)$member->in_km;
			$out_val = (float)($member->actual_out_km !== null ? $member->actual_out_km : $member->out_km);

			$exact_km = (float)$member->exact_km;
			if ($exact_km == 0 && $in_val > 0 && $out_val > 0 && $in_val != $out_val) {
				$exact_km = abs($in_val - $out_val);
			}

			$diesel_rate = (float)$member->diesel_rate;
			$diesel_exp = (float)$member->diesel_expense;
			if ($diesel_exp == 0 && $exact_km > 0 && $diesel_rate > 0) {
				$diesel_exp = ($exact_km / 9) * $diesel_rate;
			}
			
			$invoice_val = (float)$member->invoice_value;

			$total_exact_km += $exact_km;
			$total_diesel_expense += $diesel_exp;
			$total_market_freight += (float)$member->market_freight;
			$total_invoice_value += $invoice_val;
			
			$total_driver_exp += (float)$member->driver_expense;
			$total_maint_exp += (float)$member->maintenance;
		}

		// Transport % calculation matching the datatable JS (Driver Exp + Diesel Exp) / Invoice Value
        $transport_percent = 0;
        if ($total_invoice_value > 0) {
            $transport_percent = (($total_driver_exp + $total_diesel_expense) / $total_invoice_value) * 100;
        }

		return [
			'exact_km' => $total_exact_km,
			'transport_percent' => $transport_percent,
			'diesel_expense' => $total_diesel_expense,
			'market_freight' => $total_market_freight,
			'invoice_count' => $total_invoice_value, // Mapped to invoice_count for the view
		];
	}
	public function get_other_rm_mb_rejection_data($machine_id)
	{
		$date_range = $this->input->get('date') ?? '';

		$this->db->where('is_deleted', '0');
		$this->db->where('id', $machine_id);
		$machine_res = $this->db->get('tbl_machine_master')->row();
		
		$plant_id = $machine_res->plant_id;
		$this->db->select('tbl_raw_material_production_details.*, tbl_production_report.production_date');
		$this->db->where('tbl_raw_material_production_details.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_raw_material_production_details.plant_id', $plant_id);
		}
		
		if (!empty($machine_id)) {
			$this->db->where('tbl_raw_material_production_details.machine_id', $machine_id);
			// $this->db->where('tbl_raw_material_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_raw_material_production_details.production_id');
		$rm = $this->db->select_sum('tbl_raw_material_production_details.total_qty')->get('tbl_raw_material_production_details')->row()->total_qty ?? 0;

		$this->db->reset_query();

		$this->db->select('tbl_balance_quantity_production_detail.*, tbl_production_report.production_date');
		$this->db->where('tbl_balance_quantity_production_detail.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
	
		if (!empty($machine_id)) {
			$this->db->where('tbl_production_report.machine_id', $machine_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_balance_quantity_production_detail.production_id');
		$balanced_rm = $this->db->select_sum('tbl_balance_quantity_production_detail.rm_total_qty')->get('tbl_balance_quantity_production_detail')->row()->rm_total_qty ?? 0;
		// echo"<pre>";print_r($balanced_rm);exit;
		$this->db->reset_query();
		if(!empty($balanced_rm)){
			$rm = $rm - $balanced_rm;
		}
	
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('production_date >=', $start);
					$this->db->where('production_date <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(production_date)', $single);
				}
			}
		}

		$this->db->where('machine_id', $machine_id);
		$delta = $this->db->select_sum('delta')->get('tbl_production_report')->row()->delta ?? 0;
		$this->db->reset_query();
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_master_batch_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_master_batch_production_details.plant_id');
		if (!empty($master_batch_id)) {
			$this->db->where('tbl_master_batch_production_details.master_batch_id', $master_batch_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('tbl_master_batch_production_details.machine_id', $machine_id);
			// $this->db->where('tbl_master_batch_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_master_batch_production_details.production_id');
		$mb = $this->db->select_sum('tbl_master_batch_production_details.total_qty')->get('tbl_master_batch_production_details')->row()->total_qty ?? 0;
		$this->db->reset_query();

		$this->db->select('tbl_balance_quantity_production_detail.*, tbl_production_report.production_date');
		$this->db->where('tbl_balance_quantity_production_detail.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		
		if (!empty($master_batch_id)) {
			$this->db->where('tbl_balance_quantity_production_detail.master_batch_id', $master_batch_id);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_report.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_production_report.plant_id');
		if (!empty($machine_id)) {
			$this->db->where('tbl_production_report.machine_id', $machine_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_balance_quantity_production_detail.production_id');
		$balanced_mb = $this->db->select_sum('tbl_balance_quantity_production_detail.mb_total_qty')->get('tbl_balance_quantity_production_detail')->row()->mb_total_qty ?? 0;
		$this->db->reset_query();
		if(!empty($balanced_mb)){
			$mb = $mb - $balanced_mb;
		}

		$this->db->select('tbl_rejection_article_list_production_details.*, tbl_production_report.production_date');
		$this->db->where('tbl_rejection_article_list_production_details.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_rejection_article_list_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_rejection_article_list_production_details.plant_id');
		if (!empty($machine_id)) {
			$this->db->where('tbl_rejection_article_list_production_details.machine_id', $machine_id);
			// $this->db->where('tbl_rejection_article_list_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_rejection_article_list_production_details.production_id');
		$rejection = $this->db->select_sum('tbl_rejection_article_list_production_details.total_qty')->get('tbl_rejection_article_list_production_details')->row()->total_qty ?? 0;
		$this->db->reset_query();
		$total_input = $rm + $mb;
		$rejection_percent = ($rm + $mb) > 0 ? ($rejection / ($rm + $mb)) * 100 : 0;
		$mb_percent = $total_input > 0 ? round(($mb / $total_input) * 100, 2) : 0;
		return [
			'total_rm' => $rm,
			'rejection_percent' => round($rejection_percent, 2),
			'mb_percent' => round($mb_percent, 2),
			'delta' => $delta,
		];
	}
	public function get_dashboard_metrics()
	{
		$machine = $this->input->get('machine') ?? '';
		$article = $this->input->get('article') ?? '';
		$raw_material = $this->input->get('raw_material') ?? '';
		$master_batch_id = $this->input->get('master_batch_id') ?? '';
		$date_range = $this->input->get('date') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';
		
		if (!empty($machine)) {
			$this->db->where('is_deleted', '0');
			$this->db->where('id', $machine);
			$machine_res = $this->db->get('tbl_machine_master')->row();
		}
		$this->db->select('tbl_raw_material_production_details.*, tbl_production_report.production_date');
		$this->db->where('tbl_raw_material_production_details.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_raw_material_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_raw_material_production_details.plant_id');
		if (!empty($raw_material)) {
			$this->db->where('tbl_raw_material_production_details.raw_material_id', $raw_material);
		}
		if (!empty($machine)) {
			$this->db->where('tbl_raw_material_production_details.machine_id', $machine);
			// $this->db->where('tbl_raw_material_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_raw_material_production_details.production_id');
		$rm = $this->db->select_sum('tbl_raw_material_production_details.total_qty')->get('tbl_raw_material_production_details')->row()->total_qty ?? 0;
		
		$this->db->reset_query();

		$this->db->select('tbl_balance_quantity_production_detail.*, tbl_production_report.production_date,tbl_production_report.machine_id,tbl_production_report.plant_id');
		$this->db->where('tbl_balance_quantity_production_detail.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		
		if (!empty($raw_material)) {
			$this->db->where('tbl_balance_quantity_production_detail.raw_material_id', $raw_material);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_report.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_production_report.plant_id');
		if (!empty($machine)) {
			$this->db->where('tbl_production_report.machine_id', $machine);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_balance_quantity_production_detail.production_id');
		$balanced_rm = $this->db->select_sum('tbl_balance_quantity_production_detail.rm_total_qty')->get('tbl_balance_quantity_production_detail')->row()->rm_total_qty ?? 0;
		$this->db->reset_query();
		if(!empty($balanced_rm)){
			$rm = $rm - $balanced_rm;
		}
	
		$this->db->select('tbl_master_batch_production_details.*, tbl_production_report.production_date');
		$this->db->where('tbl_master_batch_production_details.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_master_batch_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_master_batch_production_details.plant_id');
		if (!empty($master_batch_id)) {
			$this->db->where('tbl_master_batch_production_details.master_batch_id', $master_batch_id);
		}
		if (!empty($machine)) {
			$this->db->where('tbl_master_batch_production_details.machine_id', $machine);
			// $this->db->where('tbl_master_batch_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_master_batch_production_details.production_id');
		$mb = $this->db->select_sum('tbl_master_batch_production_details.total_qty')->get('tbl_master_batch_production_details')->row()->total_qty ?? 0;
		$this->db->reset_query();
		$this->db->select('tbl_balance_quantity_production_detail.*, tbl_production_report.production_date,tbl_production_report.machine_id,tbl_production_report.plant_id');
		$this->db->where('tbl_balance_quantity_production_detail.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_report.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_production_report.plant_id');
		if (!empty($master_batch_id)) {
			$this->db->where('tbl_balance_quantity_production_detail.master_batch_id', $master_batch_id);
		}
		if (!empty($machine)) {
			$this->db->where('tbl_production_report.machine_id', $machine);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_balance_quantity_production_detail.production_id');
		$balanced_mb = $this->db->select_sum('tbl_balance_quantity_production_detail.mb_total_qty')->get('tbl_balance_quantity_production_detail')->row()->mb_total_qty ?? 0;
		$this->db->reset_query();
		if(!empty($balanced_mb)){
			$mb = $mb - $balanced_mb;
		}
		$this->db->select('tbl_rejection_article_list_production_details.*, tbl_production_report.production_date');
		$this->db->where('tbl_rejection_article_list_production_details.is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_production_report.production_date) >=', $start);
					$this->db->where('DATE(tbl_production_report.production_date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_production_report.production_date)', $single);
				}
			}
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_rejection_article_list_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_rejection_article_list_production_details.plant_id');
		if (!empty($raw_material)) {
			$this->db->where('tbl_rejection_article_list_production_details.rejection_id', $raw_material);
		}
		if (!empty($machine)) {
			$this->db->where('tbl_rejection_article_list_production_details.machine_id', $machine);
			// $this->db->where('tbl_rejection_article_list_production_details.plant_id', $machine_res->plant_id);
		}
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_rejection_article_list_production_details.production_id');
		$rejection = $this->db->select_sum('tbl_rejection_article_list_production_details.total_qty')->get('tbl_rejection_article_list_production_details')->row()->total_qty ?? 0;

		$this->db->reset_query();

		$total_input = $rm + $mb;
		$rejection_percent = $total_input > 0 ? round(($rejection / $total_input) * 100, 2) : 0;
		$mb_percent = $total_input > 0 ? round(($mb / $total_input) * 100, 2) : 0;

		///////////////////////////// Pending Approved Production Details /////////////////////////////////////
		$this->db->from('tbl_article_production_details AS apd'); // Alias 'apd' for tbl_article_production_details
		$this->db->where('apd.status IS NULL OR apd.status = ""');
		$this->db->where_not_in('apd.status', ['0', '1']);

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(apd.created_on) >=', $start);
					$this->db->where('DATE(apd.created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(apd.created_on)', $single);
				}
			}
		}
		if (!empty($machine)) {
			$this->db->where('apd.machine_id', $machine);
			// $this->db->where('apd.plant_id', $machine_res->plant_id);
		}
		if (!empty($plant_id)) {
			$this->db->where('apd.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('apd.plant_id');

		if (!empty($article)) {
			$this->db->where('apd.article_id', $article);
		}

		$this->db->group_by('apd.production_id');

		$pending_approved = $this->db->count_all_results() ?? 0;
		$this->db->reset_query();

		//////////////////////////////// / Pending Maintenance Details //////////////////////////////////////

		$this->db->from('tbl_maintenance_production');
		$this->db->join(
			'tbl_maintenance_list',
			'tbl_maintenance_production.mwo_code = tbl_maintenance_list.mwo_code',
			'left'
		);
		$this->db->where('tbl_maintenance_production.status_of_work', '1');
		$this->db->where('tbl_maintenance_list.plant_manager_approval_status IS NULL', null, false);
		if (!empty($date_range)) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) === 2) {
				$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				$end   = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));

				if ($start && $end) {
					$this->db->where('DATE(tbl_maintenance_production.date) >=', $start);
					$this->db->where('DATE(tbl_maintenance_production.date) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(tbl_maintenance_production.date)', $single);
				}
			}
    	}
		if (!empty($machine)) {
			$this->db->where('tbl_maintenance_production.sub_type_id', $machine);
			$this->db->where('tbl_maintenance_production.plant_id', $machine_res->plant_id);
		}
		if (!empty($article)) {
			$this->db->where('tbl_maintenance_production.sub_type_id', $article);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_maintenance_production.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_maintenance_production.plant_id');
		$pending_maintenance = $this->db->count_all_results() ?? 0;

		$this->db->reset_query();
		
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->where('order_department', '2');
		$this->db->where('task_status', '1');
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('plant_id');
		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('employee_id', $this->session->userdata('id'));
		}
		$pending_task = $this->db->count_all_results('tbl_auto_task_list') ?? 0;
		$this->db->reset_query();

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->where('order_department', '1');
		$this->db->where('task_status', '1');
		$this->db->where('is_deleted', '0');
		$this->db->where('department_id', '12');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$auto_pending_account_task = $this->db->count_all_results('tbl_auto_task_list') ?? 0;
		$this->db->reset_query();

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->where('task_status', '1');
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('plant_id');
		$this->db->where('department_id', '12');
		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('assign_to_id', $this->session->userdata('id'));
		}
		$manual_pending_account_task = $this->db->count_all_results('tbl_manual_task') ?? 0;
		$this->db->reset_query();

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		// Pending Dispatch: matches outward_order_list (order_department_status=3, order_status IN 3,9)
		$this->db->where('order_department_status', '3');
		$this->db->where_in('order_status', ['3', '9']);
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('plant_id');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				$end   = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			} else if (count($dates) == 1) {
				$single = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				if ($single) { $this->db->where('DATE(created_on)', $single); }
			}
		}
		$pending_dispatch_onwer = $this->db->count_all_results('tbl_auto_task_list') ?? 0;
		$this->db->reset_query();

		// Pending Dispatch age breakdown (Today / 1-7 days / 7+ days)
		$dispatch_age = ['today' => 0, 'week' => 0, 'older' => 0];
		$this->db->select("
			SUM(CASE WHEN DATE(created_on) = CURDATE() THEN 1 ELSE 0 END) AS today,
			SUM(CASE WHEN DATE(created_on) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND DATE(created_on) < CURDATE() THEN 1 ELSE 0 END) AS week,
			SUM(CASE WHEN DATE(created_on) < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS older
		", false);
		$this->db->where('order_department_status', '3');
		$this->db->where_in('order_status', ['3', '9']);
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) { $this->db->where('plant_id', $plant_id); }
		$this->_apply_assigned_plants_scope('plant_id');
		$dispatch_age_row = $this->db->get('tbl_auto_task_list')->row();
		if ($dispatch_age_row) {
			$dispatch_age = [
				'today' => (int)($dispatch_age_row->today ?? 0),
				'week'  => (int)($dispatch_age_row->week  ?? 0),
				'older' => (int)($dispatch_age_row->older ?? 0),
			];
		}
		$this->db->reset_query();

		// Pending Printing: matches printing_order_list (order_status IN 0,5,7, order_department_status IN 2,3)
		$this->db->where_in('order_status', ['0', '5', '7']);
		$this->db->where_in('order_department_status', ['2', '3']);
		$this->db->where('is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				$end   = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				if ($start && $end) {
					$this->db->where('DATE(order_date) >=', $start);
					$this->db->where('DATE(order_date) <=', $end);
				}
			} else if (count($dates) == 1) {
				$single = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				if ($single) { $this->db->where('DATE(order_date)', $single); }
			}
		}
		$pending_printing_onwer = $this->db->count_all_results('tbl_order_sub_details') ?? 0;
		$this->db->reset_query();

		// Pending Printing age breakdown (Today / 1-7 days / 7+ days)
		$printing_age = ['today' => 0, 'week' => 0, 'older' => 0];
		$this->db->select("
			SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) AS today,
			SUM(CASE WHEN DATE(order_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND DATE(order_date) < CURDATE() THEN 1 ELSE 0 END) AS week,
			SUM(CASE WHEN DATE(order_date) < DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS older
		", false);
		$this->db->where_in('order_status', ['0', '5', '7']);
		$this->db->where_in('order_department_status', ['2', '3']);
		$this->db->where('is_deleted', '0');
		$printing_age_row = $this->db->get('tbl_order_sub_details')->row();
		if ($printing_age_row) {
			$printing_age = [
				'today' => (int)($printing_age_row->today ?? 0),
				'week'  => (int)($printing_age_row->week  ?? 0),
				'older' => (int)($printing_age_row->older ?? 0),
			];
		}
		$this->db->reset_query();

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->like('task_id', 'ENQ', 'after');
		$total_enquiry_orders = $this->db->count_all_results('tbl_manual_task') ?? 0;
		$this->db->reset_query();

		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->like('task_id', 'ENQ', 'after');
		$this->db->where('task_status', '2');
		$total_enquiry_orders_completed = $this->db->count_all_results('tbl_manual_task') ?? 0;
		$this->db->reset_query();

		$enquiry_order_generation_ratio = ($total_enquiry_orders > 0)
			? round(($total_enquiry_orders_completed / $total_enquiry_orders) * 100, 2)
			: 0;

		$customer_active = 0;
		$customer_inactive = 0;
		$customers_lost = 0;
		$customers_old = 0;   // very old
		$customers_noorder = 0;   // no orders

		// Lost customers breakdown by last order status
		$lost_awaiting_dispatch = 0;
		$lost_in_printing       = 0;
		$lost_others            = 0;

		// --- Customers ---
		$this->db->where('is_deleted', '0');
		$customers = $this->db->get('tbl_customers')->result();
		$this->db->reset_query();

		$this->db->where('is_deleted', '0');
		$this->db->where('order_department', '1');
		$orders = $this->db->get('tbl_auto_task_list')->result();
		$this->db->reset_query();

		$current_date = date('Y-m-d');

		// Pre-group orders by party_id for O(1) lookups instead of nested array_filter loops
		$orders_by_party = [];
		if (!empty($orders)) {
			foreach ($orders as $order) {
				$orders_by_party[$order->party_id][] = $order;
			}
		}

		if (!empty($customers)) {
			foreach ($customers as $cust) {
				$customer_id = $cust->id;

				// Get all orders of this customer
				$customer_orders = $orders_by_party[$customer_id] ?? [];

				if (!empty($customer_orders)) {
					// Find latest updated_on
					$last_order_date = max(array_column($customer_orders, 'updated_on'));
					$date_diff = (strtotime($current_date) - strtotime($last_order_date)) / (60 * 60 * 24);

					if ($date_diff <= 30) {
						$customer_active++;
					} elseif ($date_diff > 30 && $date_diff <= 60) {
						$customer_inactive++;
					} elseif ($date_diff > 60 && $date_diff <= 90) {
						$customers_lost++;

						// Categorise by last order's status
						usort($customer_orders, function($a, $b) {
							return strtotime($b->updated_on) - strtotime($a->updated_on);
						});
						$last_order = reset($customer_orders);
						$last_status = (string)($last_order->order_status ?? '');
						// Awaiting Dispatch: order_department_status=3, order_status IN (3,9)
						if (in_array($last_status, ['3', '9']) && (string)($last_order->order_department_status ?? '') === '3') {
							$lost_awaiting_dispatch++;
						// In Printing: order_department_status IN (2,3), order_status IN (0,7)
						} elseif (in_array($last_status, ['0', '7'])) {
							$lost_in_printing++;
						} else {
							$lost_others++;
						}
					} else {
						$customers_old++;
					}
				} else {
					$customers_noorder++;
				}
			}
		}

		// ── Active customers: daily count for last 7 days (Mon–Sun) ──────────
		$active_weekly = [];
		for ($i = 6; $i >= 0; $i--) {
			$day_label = date('D', strtotime("-$i days"));
			$day_date  = date('Y-m-d', strtotime("-$i days"));
			// Count customers whose last order updated_on falls on this day
			$day_count = 0;
			if (!empty($customers)) {
				foreach ($customers as $cust) {
					$cust_orders = $orders_by_party[$cust->id] ?? [];
					if (!empty($cust_orders)) {
						$last = max(array_column($cust_orders, 'updated_on'));
						if (date('Y-m-d', strtotime($last)) === $day_date) {
							$day_count++;
						}
					}
				}
			}
			$active_weekly[] = ['label' => $day_label, 'count' => $day_count];
		}

		// ── Inactive customers: breakdown by inactivity period ───────────────
		$inactive_1m  = 0; // 31–60 days
		$inactive_3m  = 0; // 61–90 days
		$inactive_4m  = 0; // 90+ days
		if (!empty($customers)) {
			foreach ($customers as $cust) {
				$cust_orders = $orders_by_party[$cust->id] ?? [];
				if (!empty($cust_orders)) {
					$last = max(array_column($cust_orders, 'updated_on'));
					$diff = (strtotime($current_date) - strtotime($last)) / 86400;
					if ($diff > 30 && $diff <= 60)  $inactive_1m++;
					elseif ($diff > 60 && $diff <= 90) $inactive_3m++;
					elseif ($diff > 90) $inactive_4m++;
				}
			}
		}


		// ── TAT donut: orders by current stage ───────────────────────────────
		$this->db->select("
			SUM(CASE WHEN order_department_status = '3' AND order_status IN ('3','9') THEN 1 ELSE 0 END) AS awaiting_dispatch,
			SUM(CASE WHEN order_department_status = '2' AND order_status IN ('0','7') THEN 1 ELSE 0 END) AS in_printing,
			SUM(CASE WHEN order_department_status = '3' AND order_status IN ('0','7') THEN 1 ELSE 0 END) AS printing_dispatch
		", false);
		$this->db->where('is_deleted', '0');
		$this->db->where('order_department', '1');
		$tat_row = $this->db->get('tbl_auto_task_list')->row();
		$this->db->reset_query();
		$tat_breakdown = [
			'awaiting_dispatch'  => (int)($tat_row->awaiting_dispatch  ?? 0),
			'printing_dispatch'  => (int)($tat_row->printing_dispatch  ?? 0),
			'in_printing'        => (int)($tat_row->in_printing        ?? 0),
		];
		$this->db->select('id, order_id, created_on');
		$this->db->where('type_of_order', '1');
		$this->db->where('is_deleted', '0');
		$orders = $this->db->get('tbl_order_details')->result();
		$this->db->reset_query();

		// Collect all order IDs from both order lists for bulk dispatch lookup
		$all_order_ids = [];
		if (!empty($orders)) {
			foreach ($orders as $order) {
				$all_order_ids[] = $order->order_id;
			}
		}
		if (!empty($orders_container)) {
			foreach ($orders_container as $order) {
				$all_order_ids[] = $order->order_id;
			}
		}

		$dispatches_by_order = [];
		if (!empty($all_order_ids)) {
			$this->db->select('updated_on, task_id, order_status');
			$this->db->where_in('task_id', array_unique($all_order_ids));
			$this->db->where('order_status', '4');
			$this->db->where('order_department', '1');
			$this->db->order_by('updated_on', 'DESC');
			$all_dispatches = $this->db->get('tbl_auto_task_list')->result();
			$this->db->reset_query();

			foreach ($all_dispatches as $d) {
				if (!isset($dispatches_by_order[$d->task_id])) {
					$dispatches_by_order[$d->task_id] = $d;
				}
			}
		}

		$total_days = 0;
		$count = 0;

		if (!empty($orders)) {
			foreach ($orders as $order) {
				$dispatch = $dispatches_by_order[$order->order_id] ?? null;

				if ($dispatch && $order->created_on) {
					$order_date = new DateTime($order->created_on);
					$dispatch_date = new DateTime($dispatch->updated_on);
					$diff = $order_date->diff($dispatch_date)->days;
					$total_days += $diff;
					$count++;
				}
			}
		}
		$household_order_execution = $count > 0 ? round($total_days / $count, 1) : 0;

		$this->db->select('id, order_id, created_on');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		$this->db->where('type_of_order', '2');
		$this->db->where('is_deleted', '0');
		$orders_container = $this->db->get('tbl_order_details')->result();
		$this->db->reset_query();

		$total_days_container = 0;
		$count_of_container = 0;

		if (!empty($orders_container)) {
			foreach ($orders_container as $order) {
				$dispatch_container = $dispatches_by_order[$order->order_id] ?? null;

				if ($dispatch_container && $order->created_on) {
					$order_d = new DateTime($order->created_on);
					$dispatch_d = new DateTime($dispatch_container->updated_on);
					$differ = $order_d->diff($dispatch_d)->days;
					$total_days_container += $differ;
					$count_of_container++;
				}
			}
		}
		$container_order_execution = $count_of_container > 0 ? round($total_days_container / $count_of_container, 1) : 0;


		$date = date('Y-m-d');

		$this->db->select('COUNT(DISTINCT sales_person_id) as total');
		$this->db->from('tbl_side_visit_details');
		$this->db->where('is_deleted', '0');
		$this->db->where('DATE(date)', $date);
		$this->db->where_in('status_of_visit', ['1', '2', '3']); // Status for completed visits
		$result = $this->db->get()->row();
		$this->db->reset_query();
		$total_on_field = $result ? (int) $result->total : 0;


		$this->db->select('COUNT(id) as total_visits');
		$this->db->from('tbl_side_visit_details');
		$this->db->where('is_deleted', '0');
		$this->db->where('DATE(date)', date('Y-m-d'));
		$result = $this->db->get()->row();
		$this->db->reset_query();
		$total_visits = $result ? (int) $result->total_visits : 0;

		$this->db->select('id');
		$this->db->where('is_deleted', '0');
		$brands = $this->db->get('tbl_brand_master')->result();
		$this->db->reset_query();
		$days = 45;
		$inactive_count = 0;
		$today = date('Y-m-d');

		// Fetch latest created_on for all brands at once
		$latest_order_by_brand = [];
		$this->db->select('brand_type_id, MAX(created_on) as latest_created_on', false);
		$this->db->where('is_deleted', '0');
		$this->db->group_by('brand_type_id');
		$brand_orders = $this->db->get('tbl_order_sub_details')->result();
		$this->db->reset_query();
		foreach ($brand_orders as $bo) {
			$latest_order_by_brand[$bo->brand_type_id] = $bo->latest_created_on;
		}

		if (!empty($brands)) {
			foreach ($brands as $brand) {
				$latest_created_on = $latest_order_by_brand[$brand->id] ?? null;

				if (empty($latest_created_on)) {
					$inactive_count++;
				} else {
					$diff = (strtotime($today) - strtotime($latest_created_on)) / (60 * 60 * 24);
					if ($diff > $days) {
						$inactive_count++;
					}
				}
			}
		}
		$department_id = $this->input->get('departments') ?? '';
		$employee_id = $this->input->get('employees') ?? '';
		

		$this->db->select('id,updated_on,created_on,department_id,assign_to_id,task_id');
		$this->db->where('is_deleted', '0');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(created_on) >=', $start);
					$this->db->where('DATE(created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('DATE(created_on)', $single);
				}
			}
		}
		if (!empty($employee_id)) {
			$this->db->where('assign_to_id', $employee_id);
		}
		if (!empty($department_id)) {
			$this->db->where('department_id', $department_id);
		}
		$this->db->group_by('task_id');
		$all_assign_tasks = $this->db->get('tbl_manual_task_history')->result();
		$this->db->reset_query();

		// Fetch all potentially matching manual task history rows in one query
		$task_ids = [];
		$assign_to_ids = [];
		if (!empty($all_assign_tasks)) {
			foreach ($all_assign_tasks as $task) {
				$task_ids[] = $task->task_id;
				$assign_to_ids[] = $task->assign_to_id;
			}
		}

		$completed_tasks_by_key = [];
		if (!empty($task_ids)) {
			$this->db->select('updated_on, task_id, last_updated_by, assign_to_id');
			$this->db->where_in('task_id', array_unique($task_ids));
			$this->db->where_in('last_updated_by', array_unique($assign_to_ids));
			$this->db->order_by('updated_on', 'ASC');
			$histories = $this->db->get('tbl_manual_task_history')->result();
			$this->db->reset_query();

			foreach ($histories as $h) {
				if ($h->assign_to_id != $h->last_updated_by) {
					$key = $h->task_id . '_' . $h->last_updated_by;
					if (!isset($completed_tasks_by_key[$key])) {
						$completed_tasks_by_key[$key] = $h;
					}
				}
			}
		}

		$total_tat = 0;
		$count = 0;
		if (!empty($all_assign_tasks)) {
			foreach ($all_assign_tasks as $task) {
				$key = $task->task_id . '_' . $task->assign_to_id;
				$completed = $completed_tasks_by_key[$key] ?? null;
				
				if ($completed && $task->updated_on) {
					$start = new DateTime($task->updated_on);
					$end = new DateTime($completed->updated_on);
					$interval = $start->diff($end);
					$tat = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
					$total_tat += $tat;
					$count++;
				}
			}
		}
		$average_tat = $count > 0 ? round($total_tat / $count, 2) : 0;


		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('complete_by_date >=', $start);
					$this->db->where('complete_by_date <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if ($single) {
					$this->db->where('complete_by_date', $single);
				}
			}
		}
		$this->db->where('task_status', '1');
		$this->db->where('department_id', '25');
		$this->db->where('assign_to_id', $this->session->userdata('id'));
		$total_peding_task_super_admin = $this->db->count_all_results('tbl_manual_task') ?? 0;
		$this->db->reset_query();

		return [
			'total_rm' => $rm,
			'total_mb' => $mb,
			'rejection_percent' => round($rejection_percent, 2),
			'mb_percent' => round($mb_percent, 2),
			'pending_approved' => $pending_approved,
			'pending_maintenance' => $pending_maintenance,
			'pending_task' => $pending_task,
			'pending_account_task' => $auto_pending_account_task,
			'manual_pending_account_task' => $manual_pending_account_task,
			'pending_dispatch_onwer' => $pending_dispatch_onwer,
			'pending_printing_onwer' => $pending_printing_onwer,
			'pending_dispatch_age'   => $dispatch_age,
			'pending_printing_age' => $printing_age,
			'enquiry_order_generation_ratio' => $enquiry_order_generation_ratio,
			'customer_active' => $customer_active,
			'customer_inactive' => $customer_inactive,
			'customers_lost' => $customers_lost,
			'customers_lost_breakdown' => [
				'awaiting_dispatch' => $lost_awaiting_dispatch,
				'in_printing'       => $lost_in_printing,
				'others'            => $lost_others,
			],
			'active_customers_weekly'  => $active_weekly,
			'inactive_customers_breakdown' => [
				'one_month'   => $inactive_1m,
				'three_month' => $inactive_3m,
				'four_plus'   => $inactive_4m,
			],
			'tat_breakdown' => $tat_breakdown,
			'household_order_execution' => $household_order_execution,
			'container_order_execution' => $container_order_execution,
			'total_on_field' => $total_on_field,
			'total_visits' => $total_visits,
			'inactive_brands' => $inactive_count,
			'total_peding_task_super_admin' => $total_peding_task_super_admin,
			'average_tat' => $average_tat,
		];
	}

	public function get_store_dashboard_metrics()
	{
		$date_range = $this->input->get('date') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';
		$machine_id = $this->input->get('machine') ?? '';
		// ---- DATE RANGE HANDLING ----
		$from_date = '';
		$to_date = '';

		if (!empty($date_range)) {

			$date_range = str_replace('+', ' ', $date_range);

			// CASE 1: Date range (01-11-2025 to 15-11-2025)
			if (strpos($date_range, 'to') !== false) {

				$parts = explode('to', $date_range);

				if (count($parts) == 2) {
					$from_date = trim($parts[0]);
					$to_date = trim($parts[1]);

					$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
					$to_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
				}

			}
			// CASE 2: Single date (01-01-2026)
			else {

				$single_date = trim($date_range);
				$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				$to_date = $from_date; // same day
			}

		} else {

			// Default today
			$from_date = date('Y-m-d');
			$to_date = date('Y-m-d');
		}

		$fully_dispatched = 0;
		$partially_dispatched = 0;
		$pending = 0;


		// echo"<pre>";print_r($to_date);
		// echo"<pre>";print_r($machine_id);exit;

		$this->db->select('tbl_production_schedules.*, tbl_mould_parts.article_name, tbl_group_of_article.group_of_article, tbl_plant_master.plant_name, tbl_machine_master.machine_name');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id');
		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id');
		$this->db->where('tbl_production_schedules.is_deleted', '0');

		$this->db->where('tbl_production_schedules.date >=', $from_date . ' 00:00:00');
		$this->db->where('tbl_production_schedules.date <=', $to_date . ' 23:59:59');

		if ($plant_id != "") {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if ($machine_id != "") {
			if ($machine_id != 'all') {
				$this->db->where('tbl_production_schedules.machine_id', $machine_id);
			}
		}

		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'ASC');
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();

		$from_date = '';
		$to_date = '';

		if (!empty($date_range)) {
			$date_range = str_replace('+', ' ', $date_range);
			// CASE 1: Date range (01-11-2025 to 15-11-2025)
			if (strpos($date_range, 'to') !== false) {
				$parts = explode('to', $date_range);
				if (count($parts) == 2) {
					$from_date = trim($parts[0]);
					$to_date = trim($parts[1]);
					$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
					$to_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
				}
			}
			// CASE 2: Single date (01-01-2026)
			else {
				$single_date = trim($date_range);
				$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				$to_date = $from_date; // same day
			}
		}

		if (!empty($from_date)) {
			$this->db->where('created_on >=', $from_date . ' 00:00:00');
			$this->db->where('created_on <=', $to_date . ' 23:59:59');
		}
		$this->db->where('task_status', '1');
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->db->where('department_id', '24');
		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('assign_to_id', $this->session->userdata('id'));
		}
		$manual_pending_store_task = $this->db->count_all_results('tbl_manual_task') ?? 0;

		if (!empty($from_date)) {
			$this->db->where('created_on >=', $from_date . ' 00:00:00');
			$this->db->where('created_on <=', $to_date . ' 23:59:59');
		}
		$this->db->where('request_status', '1');
		$this->db->where('request_type', '1');
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if (!$this->_user_is_unrestricted_access_user()) {
			$assigned_plants = $this->_assigned_plant_ids();
			if (count($assigned_plants) === 1) {
				$this->db->where('(my_plant_id <> ' . $this->db->escape($assigned_plants[0]) . ' OR plant_id IS NULL)', null, false);
			} else if (count($assigned_plants) > 1) {
				$this->db->where('(my_plant_id NOT IN (' . implode(',', array_map([$this->db, 'escape'], $assigned_plants)) . ') OR plant_id IS NULL)', null, false);
			} else {
				$this->db->where('1 = 0', null, false);
			}
		}
		$miscellaneous_pending_dispatch = $this->db->count_all_results('tbl_rm_request_qty') ?? 0;
		if (!empty($result)) {

			foreach ($result as $row) {
				$raw_materials = $this->get_raw_materials($row->raw_materials != "" ? explode(',', $row->raw_materials) : []);
				$colors = $this->get_colors($row->color_id != "" ? explode(',', $row->color_id) : []);
				$bom = $this->get_artical_bom($row->article_id);

				$particulars = [];
				if (!empty($bom)) {
					$particulars = $this->get_artical_particaulars($row->article_id, $bom->id);
				}
				if (!empty($raw_materials)) {
					foreach ($raw_materials as $rm) {
						$rm_exist = $this->get_production_schedule_item_rm_status('1', $row->id, $rm->id);
						if (!empty($rm_exist) && $rm_exist->item_status == '1') {
							$partially_dispatched++;
						} else if (!empty($rm_exist) && $rm_exist->item_status == '2') {
							$fully_dispatched++;
						} else {
							$pending++;
						}
					}
				}
				if (!empty($colors)) {
					foreach ($colors as $color) {
						$color_exist = $this->get_production_schedule_item_rm_status('2', $row->id, $color->id);
						if (!empty($color_exist) && $color_exist->item_status == '1') {
							$partially_dispatched++;
						} else if (!empty($color_exist) && $color_exist->item_status == '2') {
							$fully_dispatched++;
						} else {
							$pending++;
						}
					}
				}
				if (!empty($particulars)) {
					foreach ($particulars as $part) {
						$total_items++;

						$part_exist = $this->get_production_schedule_item_rm_status('3', $row->id, $part->sub_category_id);

						if (!empty($color_exist) && $color_exist->item_status == '1') {
							$partially_dispatched++;
						} else if (!empty($color_exist) && $color_exist->item_status == '2') {
							$fully_dispatched++;
						} else {
							$pending++;
						}
					}
				}

			}
		}


		return [
			'fully_dispatched_items_count' => $fully_dispatched,
			'partially_dispatched_items_count' => $partially_dispatched,
			'pending_dispatched_items_count' => $pending,
			'manual_pending_store_task' => $manual_pending_store_task,
			'miscellaneous_pending_dispatch' => $miscellaneous_pending_dispatch,
		];
	}


	public function get_purchase_dashboard_metrics()
	{
		$date_range = $this->input->get('date') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';
		$party_id = $this->input->get('party_id') ?? '';
		$raw_material_id = $this->input->get('raw_material_id') ?? '';

		if ($date_range) {
			$dates = explode(' to ', $date_range);
			$start_date = date('Y-m-d', strtotime($dates[0]));
			$end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
			$this->db->where('DATE(created_on) >=', $start_date);
			$this->db->where('DATE(created_on) <=', $end_date);
		}
		$this->db->from('tbl_manual_task');
		$this->db->where('is_deleted', '0');
		$this->db->where('department_id', '19'); // PURCHASE & PLANNING  Department
		$this->db->where('task_status', '1'); // Pending
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		$pending_tasks = $this->db->count_all_results() ?? 0;
		$this->db->reset_query();
		// RM Stock Level (below reorder)
		$this->db->select('COUNT(tbl_raw_material_stock_report.id) as total_count');
		$this->db->from('tbl_raw_material_stock_report');
		$this->db->join('tbl_rm_master', 'tbl_raw_material_stock_report.raw_material_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_raw_material_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_raw_material_stock_report.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_raw_material_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_raw_material_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_rm_master.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		if ($plant_id != "") {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $plant_id);
		}
		if ($raw_material_id != "") {
			$this->db->where('tbl_raw_material_stock_report.raw_material_id', $raw_material_id);
		}
		if ($date_range) {
			$dates = explode(' to ', $date_range);
			$start_date = date('Y-m-d', strtotime($dates[0]));
			$end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
			$this->db->where('DATE(tbl_raw_material_stock_report.created_on) >=', $start_date);
			$this->db->where('DATE(tbl_raw_material_stock_report.created_on) <=', $end_date);
		}
		$count_query = $this->db->get();
		$rm_stock_level = $count_query->row()->total_count ?? 0;

		// Article Stock Level (below reorder)
		$this->db->select('COUNT(tbl_article_stock_report.id) as total_count');
		$this->db->from('tbl_article_stock_report');
		$this->db->join('tbl_mould_parts', 'tbl_article_stock_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_article_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_article_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_article_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_mould_parts.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		if ($plant_id != "") {
			$this->db->where('tbl_article_stock_report.plant_id', $plant_id);
		}
		if ($date_range) {
			$dates = explode(' to ', $date_range);
			$start_date = date('Y-m-d', strtotime($dates[0]));
			$end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
			$this->db->where('DATE(tbl_article_stock_report.created_on) >=', $start_date);
			$this->db->where('DATE(tbl_article_stock_report.created_on) <=', $end_date);
		}
		$count_query = $this->db->get();
		$article_stock_level = $count_query->row()->total_count ?? 0;
		return [
			'pending_tasks' => $pending_tasks,
			'rm_stock_level' => $rm_stock_level,
			'article_stock_level' => $article_stock_level,
		];
	}

	public function get_maintenance_approve_pending_count()
	{
		$date_range = $this->input->get('date') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';
		$this->db->where('is_deleted', '0');
		$this->db->where('status_of_work', '1');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->db->where('plant_manager_approval_status', '2');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('date >=', $start);
					$this->db->where('date <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(date)', $single);
				}
			}
		}
		return $this->db->count_all_results('tbl_maintenance_list');
	}
	public function get_all_machines_production()
	{
		$machine = $this->input->get('machine') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if (!empty($machine)) {
			$this->db->where('id', $machine);
		}
		$this->db->where('is_deleted', '0');
		$this->_apply_assigned_plants_scope('plant_id');

		$this->db->where_in('department_id', ['2', '3', '6', '14']);
		$result = $this->db->get('tbl_machine_master');
		return $result->result();
	}

	public function get_all_article_level_analysis()
	{
		$machine = $this->input->get('machine') ?? '';
		$article = $this->input->get('article') ?? '';
		$date_range = $this->input->get('date') ?? '';
		$plant_id = $this->input->get('plant_id') ?? '';

		$this->db->where('is_deleted', '0');
		$this->db->where('id', $machine);
		$machine_res = $this->db->get('tbl_machine_master')->row();
		$this->db->select('
			tbl_article_production_details.machine_id, 
			tbl_article_production_details.article_id,
			tbl_article_production_details.created_on, 
			tbl_machine_master.machine_name, 
			tbl_mould_parts.article_name,
			tbl_article_production_details.approved_qty AS total_approved_qty,
			tbl_article_production_details.average_qty AS total_approved_weight 
		');
		$this->db->from('tbl_article_production_details');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_article_production_details.machine_id');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_article_production_details.article_id');
		$this->db->where('tbl_article_production_details.is_deleted', '0');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		$this->_apply_assigned_plants_scope('tbl_article_production_details.plant_id');
		if (!empty($machine)) {
			$this->db->where('tbl_article_production_details.machine_id', $machine);
			$this->db->where('tbl_article_production_details.plant_id', $machine_res->plant_id);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_article_production_details.plant_id', $plant_id);
		}
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if ($start && $end) {
					$this->db->where('DATE(tbl_article_production_details.created_on) >=', $start);
					$this->db->where('DATE(tbl_article_production_details.created_on) <=', $end);
				}
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				
				if ($single) {
					$this->db->where('DATE(tbl_article_production_details.created_on)', $single);
				}
			}
		}
		if ($article) {
			$this->db->where('tbl_article_production_details.article_id', $article);
		}
		$this->db->group_by('tbl_article_production_details.id');
		$result = $this->db->get();
		return $result->result();
	}

	public function get_machines()
	{
		$this->db->select('id AS machine_id, machine_name');
		$this->db->from('tbl_machine_master');
		$machines = $this->db->get()->result_array();
		$this->output->set_output(json_encode($machines));
	}

	private function generateRandomColor($machine_id)
	{
		srand(crc32($machine_id));
		$r = rand(0, 255);
		$g = rand(0, 255);
		$b = rand(0, 255);
		srand();
		return [
			'border' => "rgb($r, $g, $b)",
			'background' => "rgba($r, $g, $b, 0.5)"
		];
	}

	public function get_machine_production_month()
	{
		$date = $this->input->post('date');
		$machine = $this->input->post('machine');
		$plant_id = $this->input->post('plant_id') ?? '';

		if (empty($date)) {
			$current_year = date('Y'); // e.g., 2025
			$start = $current_year . '-01-01';
			$end = $current_year . '-12-31';
		} else {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

				if (!$start || !$end) {
					$current_year = date('Y');
					$start = $current_year . '-01-01';
					$end = $current_year . '-12-31';
				}
			}else if (count($dates) == 1 && !empty(trim($dates[0]))) {

				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));

				if (!empty($single)) {
					$start = $single;
					$end   = $single;
				} else {
					$current_year = date('Y');
					$start = $current_year . '-01-01';
					$end   = $current_year . '-12-31';
				}

			} else {
				$current_year = date('Y');
				$start = $current_year . '-01-01';
				$end = $current_year . '-12-31';
			}
		}

		$this->db->select('id AS machine_id, machine_name');
		$this->db->from('tbl_machine_master');
		$this->db->where('is_deleted', '0');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('plant_id');

		$this->db->where_in('department_id', ['2', '3', '6', '14']);
		$all_machines = $this->db->get()->result_array();


		$this->db->select('tbl_article_production_details.machine_id, tbl_article_production_details.created_on, tbl_article_production_details.approved_qty, tbl_machine_master.machine_name,tbl_production_report.production_date');
		$this->db->from('tbl_article_production_details');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_article_production_details.machine_id', 'left');
		$this->db->join('tbl_production_report','tbl_production_report.id = tbl_article_production_details.production_id');
		$this->db->where('tbl_production_report.production_date >=', $start);
		$this->db->where('tbl_production_report.production_date <=', $end);

		if (!empty($machine)) {
			$this->db->where('tbl_article_production_details.machine_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_article_production_details.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_article_production_details.plant_id');

		$query = $this->db->get()->result_array();
		$colors = [
			'1' => ['border' => 'rgb(255, 99, 132)', 'background' => 'rgba(255, 99, 132, 0.5)'],
			'2' => ['border' => 'rgb(54, 162, 235)', 'background' => 'rgba(54, 162, 235, 0.5)'],
			'3' => ['border' => 'rgb(75, 192, 192)', 'background' => 'rgba(75, 192, 192, 0.5)'],
			'4' => ['border' => 'rgb(153, 102, 255)', 'background' => 'rgba(153, 102, 255, 0.5)'],
			'5' => ['border' => 'rgb(255, 159, 64)', 'background' => 'rgba(255, 159, 64, 0.5)']
		];

		$machine_data = [];
		$machine_names = [];
		foreach ($all_machines as $machine) {
			$machine_id = $machine['machine_id'];
			$machine_data[$machine_id] = array_fill(1, 12, 0); // Initialize with zeros for all months
			$machine_names[$machine_id] = $machine['machine_name'] ?? 'Machine ' . $machine_id; // Store machine name
		}

		foreach ($query as $row) {
			$month = date('n', strtotime($row['production_date'])); // Extract month (1-12)
			$machine_id = $row['machine_id'];
			$machine_data[$machine_id][$month] += $row['approved_qty'];
		}

		$datasets = [];
		foreach ($machine_data as $machine_id => $data) {
			$color = isset($colors[$machine_id]) ? $colors[$machine_id] : $this->generateRandomColor($machine_id);
			$datasets[] = [
				'label' => $machine_names[$machine_id],
				'data' => array_values($data),
				'borderColor' => $color['border'],
				'backgroundColor' => $color['background'],
				'yAxisID' => ($machine_id == 1) ? 'y' : 'y1'
			];
		}
		echo json_encode(['datasets' => $datasets]);
	}
	// public function get_prouction_planed_actual()
	// {
	// 	$date = $this->input->post('date');
	// 	$machine = $this->input->post('machine');
	// 	$article_id = $this->input->post('article_id');
	// 	$plant_id = $this->input->post('plant_id') ?? '';

	// 	if (empty($date)) {

	// 		// No date â†’ full year
	// 		$current_year = date('Y');
	// 		$start = $current_year . '-01-01';
	// 		$end = $current_year . '-12-31';

	// 	} else {

	// 		// Check if date range
	// 		if (strpos($date, ' to ') !== false) {

	// 			// RANGE: 01-01-2026 to 22-01-2026
	// 			$dates = explode(' to ', $date);

	// 			$start_obj = DateTime::createFromFormat('d-m-Y', trim($dates[0]));
	// 			$end_obj = DateTime::createFromFormat('d-m-Y', trim($dates[1]));

	// 			if ($start_obj && $end_obj) {
	// 				$start = $start_obj->format('Y-m-d');
	// 				$end = $end_obj->format('Y-m-d');
	// 			}

	// 		} else {

	// 			// SINGLE DATE: 06-02-2026
	// 			$date_obj = DateTime::createFromFormat('d-m-Y', $date);

	// 			if ($date_obj) {
	// 				$start = $date_obj->format('Y-m-d');
	// 				$end = $date_obj->format('Y-m-d');
	// 			}
	// 		}
	// 	}
	// 	$this->db->select('tbl_production_schedules.machine_id, tbl_production_schedules.article_id, tbl_production_schedules.created_on, tbl_production_schedules.qty AS planned_qty');
	// 	$this->db->from('tbl_production_schedules');
	// 	$this->db->where('tbl_production_schedules.created_on >=', $start . ' 00:00:00');
	// 	$this->db->where('tbl_production_schedules.created_on <=', $end . ' 23:59:59');
	// 	if (!empty($machine)) {
	// 		$this->db->where('tbl_production_schedules.machine_id', $machine);
	// 	}
	// 	if (!empty($article_id)) {
	// 		$this->db->where('tbl_production_schedules.article_id', $article_id);
	// 	}
	// 	if (!empty($plant_id)) {
	// 		$this->db->where('tbl_production_schedules.plant_id', $plant_id);
	// 	}
	// 	$planned_qty_result = $this->db->get()->result_array();

	// 	$this->db->select('tbl_article_production_details.machine_id,tbl_article_production_details.article_id, tbl_article_production_details.created_on, tbl_article_production_details.approved_qty AS actual_qty');
	// 	$this->db->from('tbl_article_production_details');
	// 	$this->db->where('tbl_article_production_details.created_on >=', $start . ' 00:00:00');
	// 	$this->db->where('tbl_article_production_details.created_on <=', $end . ' 23:59:59');

	// 	if (!empty($machine)) {
	// 		$this->db->where('tbl_article_production_details.machine_id', $machine);
	// 	}
	// 	if (!empty($article_id)) {
	// 		$this->db->where('tbl_article_production_details.article_id', $article_id);
	// 	}
	// 	if (!empty($plant_id)) {
	// 		$this->db->where('tbl_article_production_details.plant_id', $plant_id);
	// 	}

	// 	$actual_qty_result = $this->db->get()->result_array();

	// 	$planned_qty = array_fill(1, 12, 0);
	// 	$actual_qty = array_fill(1, 12, 0);

	// 	foreach ($planned_qty_result as $row) {
	// 		$month = date('n', strtotime($row['created_on']));
	// 		$planned_qty[$month] += $row['planned_qty'];
	// 	}
	// 	foreach ($actual_qty_result as $row) {
	// 		$month = date('n', strtotime($row['created_on']));
	// 		$actual_qty[$month] += $row['actual_qty'];
	// 	}

	// 	$datasets = [
	// 		[
	// 			'label' => 'Actual',
	// 			'data' => array_values($actual_qty),
	// 			'borderColor' => 'rgb(255, 99, 132)',
	// 			'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
	// 			'yAxisID' => 'y1'
	// 		],
	// 		[
	// 			'label' => 'Planned',
	// 			'data' => array_values($planned_qty),
	// 			'borderColor' => 'rgb(75, 192, 192)',
	// 			'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
	// 			'yAxisID' => 'y'
	// 		]
	// 	];

	// 	echo json_encode(['datasets' => $datasets]);
	// }
	public function get_prouction_planed_actual()
{
    $date = $this->input->post('date');
    $machine = $this->input->post('machine');
    $article_id = $this->input->post('article_id');
    $plant_id = $this->input->post('plant_id') ?? '';

    if (empty($date)) {

        $current_year = date('Y');
        $start = $current_year . '-01-01';
        $end = $current_year . '-12-31';

    } else {

        if (strpos($date, ' to ') !== false) {

            $dates = explode(' to ', $date);

            $start_obj = DateTime::createFromFormat('d-m-Y', trim($dates[0]));
            $end_obj   = DateTime::createFromFormat('d-m-Y', trim($dates[1]));

            if ($start_obj && $end_obj) {
                $start = $start_obj->format('Y-m-d');
                $end   = $end_obj->format('Y-m-d');
            }

        } else {

            $date_obj = DateTime::createFromFormat('d-m-Y', $date);

            if ($date_obj) {
                $start = $date_obj->format('Y-m-d');
                $end   = $date_obj->format('Y-m-d');
            }
        }
    }

    /* ================= PLANNED ================= */

    $this->db->select('
        tbl_production_schedules.machine_id,
        tbl_production_schedules.article_id,
        tbl_production_schedules.created_on,
        tbl_production_schedules.date,
        tbl_production_schedules.qty AS planned_qty,
        tbl_production_schedules.production_schedule_start_time,
        tbl_production_schedules.production_schedule_end_time
    ');
    $this->db->from('tbl_production_schedules');
    $this->db->where('tbl_production_schedules.date >=', $start);
    $this->db->where('tbl_production_schedules.date <=', $end);

    if (!empty($machine)) {
        $this->db->where('tbl_production_schedules.machine_id', $machine);
    }
    if (!empty($article_id)) {
        $this->db->where('tbl_production_schedules.article_id', $article_id);
    }
    if (!empty($plant_id)) {
        $this->db->where('tbl_production_schedules.plant_id', $plant_id);
    }

    $planned_qty_result = $this->db->get()->result_array();

    /* ================= MONTH INIT ================= */

    $planned_qty = array_fill(1, 12, 0);
    $actual_qty  = array_fill(1, 12, 0);

    // Planned calculation (use schedule date for month)
    foreach ($planned_qty_result as $row) {
        $month = date('n', strtotime($row['date']));
        $planned_qty[$month] += (float)$row['planned_qty'];
    }

    /* ================= ACTUAL (Exact Dashboard Logic) ================= */

    foreach ($planned_qty_result as $entry) {

        $this->db->where('DATE(tbl_article_production_details.production_date)', $entry['date']);
        $this->db->where('tbl_article_production_details.article_id', $entry['article_id']);
        $this->db->where('tbl_article_production_details.machine_id', $entry['machine_id']);

        if (!empty($plant_id)) {
            $this->db->where('tbl_article_production_details.plant_id', $plant_id);
        }

        $production = $this->db->get('tbl_article_production_details')->row();

        $total_achieved_qty = 0;

        if (!empty($production)) {

            $hourly_fields = [
                '08:00-09:00' => $production->qty_eight_nine,
                '09:00-10:00' => $production->qty_nine_ten,
                '10:00-11:00' => $production->qty_ten_eleven,
                '11:00-12:00' => $production->qty_eleven_twelve,
                '12:00-13:00' => $production->qty_twelve_thirteen,
                '13:00-14:00' => $production->qty_thirteen_fourteen,
                '14:00-15:00' => $production->qty_fourteen_fifteen,
                '15:00-16:00' => $production->qty_fifteen_sixteen,
                '16:00-17:00' => $production->qty_sixteen_seventeen,
                '17:00-18:00' => $production->qty_seventeen_eighteen,
                '18:00-19:00' => $production->qty_eighteen_nineteen,
                '19:00-20:00' => $production->qty_nineteen_twenty,
                '20:00-21:00' => $production->qty_twenty_twentyone,
                '21:00-22:00' => $production->qty_twentyone_twentytwo,
                '22:00-23:00' => $production->qty_twentytwo_twentythree,
                '23:00-24:00' => $production->qty_twentythree_zero,
                '00:00-01:00' => $production->qty_zero_one,
                '01:00-02:00' => $production->qty_one_two,
                '02:00-03:00' => $production->qty_two_three,
                '03:00-04:00' => $production->qty_three_four,
                '04:00-05:00' => $production->qty_four_five,
                '05:00-06:00' => $production->qty_five_six,
                '06:00-07:00' => $production->qty_six_seven,
                '07:00-08:00' => $production->qty_seven_eight,
            ];

            $start_time = $entry['production_schedule_start_time'];
            $end_time   = $entry['production_schedule_end_time'];

            $start_int = intval(str_replace(':', '', $start_time));
            $end_int   = intval(str_replace(':', '', $end_time));

            if ($end_int <= $start_int) {
                $end_int += 2400;
            }

            foreach ($hourly_fields as $range => $qty) {

                list($from, $to) = explode('-', $range);

                $from_int = intval(str_replace(':', '', $from));
                $to_int   = intval(str_replace(':', '', $to));

                if ($to_int === 0) {
                    $to_int = 2400;
                }

                if ($to_int <= $start_int) {
                    $from_int += 2400;
                    $to_int   += 2400;
                }

                if ($from_int >= $start_int && $to_int <= $end_int) {
                    $total_achieved_qty += (float)$qty;
                }
            }
        }

        // Use schedule DATE for month grouping
        $month = date('n', strtotime($entry['date']));
        $actual_qty[$month] += $total_achieved_qty;
    }

    /* ================= RESPONSE ================= */

    $datasets = [
        [
            'label' => 'Actual',
            'data' => array_values($actual_qty),
            'borderColor' => 'rgb(255, 99, 132)',
            'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
            'yAxisID' => 'y1'
        ],
        [
            'label' => 'Planned',
            'data' => array_values($planned_qty),
            'borderColor' => 'rgb(75, 192, 192)',
            'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
            'yAxisID' => 'y'
        ]
    ];

    echo json_encode(['datasets' => $datasets]);
}

	public function get_idle_state_details()
	{
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		$machine = $this->input->post('machine');
		$plant_id = $this->input->post('plant_id');
		$start_date = date('Y-m-d', strtotime($start_date));
		$end_date = date('Y-m-d', strtotime($end_date));
		$this->db->select('tbl_article_production_details.*, tbl_machine_master.machine_name');
		$this->db->from('tbl_article_production_details');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_article_production_details.machine_id', 'left');
		$this->db->where('tbl_article_production_details.production_date >=', $start_date);
		$this->db->where('tbl_article_production_details.production_date <=', $end_date);

		if (!empty($machine)) {
			$this->db->where('tbl_article_production_details.machine_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_article_production_details.plant_id', $plant_id);
		}

		$query = $this->db->get()->result();
		$result = $query ? $query : [];

		echo json_encode($result);
	}

	public function get_coverage_reports_details()
	{
		$date = date('Y-m-d');
		$this->db->select('tbl_side_visit_details.*, user_data.first_name AS employee_name,tbl_customers.party_name');
		$this->db->from('tbl_side_visit_details');
		$this->db->join('user_data', 'user_data.id = tbl_side_visit_details.sales_person_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_side_visit_details.party_id', 'left');
		// $this->db->where_in('status_of_visit', ['1', '2', '3']);
		$this->db->where('tbl_side_visit_details.is_deleted', '0');
		$this->db->where('DATE(tbl_side_visit_details.date)', $date);
		$result = $this->db->get()->result();

		echo json_encode($result);
	}

	public function get_all_salesman_on_fields_details($length, $start, $search)
	{
		$this->db->select('tbl_side_visit_details.*,user_data.first_name AS salesman_name ,tbl_customers.*, tbl_location_master.*');
		$this->db->from('tbl_side_visit_details');
		$this->db->join('user_data', 'user_data.id = tbl_side_visit_details.sales_person_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_side_visit_details.party_id', 'left');
		$this->db->join('tbl_location_master', 'tbl_location_master.id = tbl_customers.city_id', 'left');
		$this->db->where('tbl_side_visit_details.is_deleted', '0');
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_side_visit_details.date) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_side_visit_details.date) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_side_visit_details.date)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		} else {
			$this->db->where('DATE(tbl_side_visit_details.date)', date("Y-m-d"));
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_side_visit_details.sales_person_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_side_visit_details.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_side_visit_details.source_of_visit', $this->input->post('order_status_action'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_customers.mobile', $search);
			$this->db->or_like('tbl_customers.address', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_location_master.state_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_location_master.pincode', $search);
			$this->db->or_like('tbl_side_visit_details.date', $search);
			$this->db->or_like('tbl_side_visit_details.source_of_visit', $search);
			$this->db->or_like('tbl_side_visit_details.visit_request_id', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_side_visit_details.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(tbl_side_visit_details.id) as total_count ,user_data.first_name AS salesman_name ,tbl_customers.*, tbl_location_master.*');
		$this->db->from('tbl_side_visit_details');
		$this->db->join('user_data', 'user_data.id = tbl_side_visit_details.sales_person_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_side_visit_details.party_id', 'left');
		$this->db->join('tbl_location_master', 'tbl_location_master.id = tbl_customers.city_id', 'left');
		$this->db->where('tbl_side_visit_details.is_deleted', '0');
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_side_visit_details.date) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_side_visit_details.date) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_side_visit_details.date)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		} else {
			$this->db->where('DATE(tbl_side_visit_details.date)', date("Y-m-d"));
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_side_visit_details.sales_person_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_side_visit_details.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_side_visit_details.source_of_visit', $this->input->post('order_status_action'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_customers.mobile', $search);
			$this->db->or_like('tbl_customers.address', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_location_master.state_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_location_master.pincode', $search);
			$this->db->or_like('tbl_side_visit_details.date', $search);
			$this->db->or_like('tbl_side_visit_details.source_of_visit', $search);
			$this->db->or_like('tbl_side_visit_details.visit_request_id', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	////////////////////////// Account Dashboard Analysis Functions ///////////////////////////////
	public function get_all_task_tat_analysis()
	{
		$this->db->select('
        tbl_auto_task_list_history.*,
        tbl_auto_task_list.id,
        tbl_auto_task_list.created_on,
		tbl_auto_task_list.task_id AS order_id,
		tbl_auto_task_list.date AS order_date,
        tbl_auto_task_list_history.updated_on,
        DATEDIFF(tbl_auto_task_list_history.updated_on, tbl_auto_task_list.created_on) AS task_duration_days,
		tbl_customers.party_name
    ');
		$this->db->from('tbl_auto_task_list_history');
		$this->db->join('tbl_auto_task_list', 'tbl_auto_task_list.id = tbl_auto_task_list_history.task_id');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_auto_task_list.party_id', 'left');
		$this->db->where('tbl_auto_task_list_history.is_deleted', '0');
		$this->db->where('tbl_auto_task_list_history.task_status', '2');
		$this->db->group_by('tbl_auto_task_list_history.task_id');
		$this->db->order_by('tbl_auto_task_list_history.id', 'DESC');
		$result = $this->db->get();
		return $result->result();
	}
	///////////////////////////// Onwer Dashboard Functions ///////////////////////////////
	public function get_all_kri_department_dashboard()
	{

		$this->db->where('is_deleted', '0');
		$this->db->group_by('department');
		$this->db->order_by('department', 'ASC');
		$result = $this->db->get('tbl_krivisha_department');
		return $result->result();
	}
	public function get_all_kri_employee_dashboard()
	{
		$department_id = $this->input->get('departments') ?? '';
		if (!empty($department_id)) {
			$this->db->where('department_id', $department_id);
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('user_data');
		return $result->result();
	}
	public function set_purchase_sales_report()
	{
		if (isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['error'] == 0) {
			$filePath = $_FILES['csv_file']['tmp_name'];

			$spreadsheet = IOFactory::load($filePath);
			$sheet = $spreadsheet->getActiveSheet();
			$rows = $sheet->toArray();

			foreach (array_slice($rows, 1) as $row) {
				if (count(array_filter($row)) < 10)
					continue;

				$row = array_pad($row, 21, null);

				$data = [
					'date' => $row[0],
					'supplier' => $row[1],
					'supplier_address' => $row[2],
					'consignee' => $row[3],
					'plant_name' => $row[4],
					'supplier_invoice_no' => $row[5],
					'supplier_invoice_date' => $row[6],
					'gstin_uin' => $row[7],
					'pan_no' => $row[8],
					'order_no_and_date' => $row[9],
					'terms_of_payment' => $row[10],
					'receipt_no_and_date' => $row[11],
					'receipt_doc_lr_no' => $row[12],
					'despatch_through' => $row[13],
					'destination' => $row[14],
					'article_name' => $row[15],
					'rate' => $row[16],
					'value' => $row[17],
					'addl_cost' => $row[18],
					'taxes_gst' => $row[19],
					'gross_total' => $row[20],
					'status' => '1',
					'is_deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'updated_on' => date('Y-m-d H:i:s')
				];

				$this->db->insert('tbl_purchase_sales', $data);
			}

			return 1;
		}

		return 0;
	}

	public function set_sales_report()
	{
		if (isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['error'] == 0) {
			$filePath = $_FILES['csv_file']['tmp_name'];

			$spreadsheet = IOFactory::load($filePath);
			$sheet = $spreadsheet->getActiveSheet();
			$rows = $sheet->toArray();

			foreach (array_slice($rows, 1) as $row) {
				if (count(array_filter($row)) < 10)
					continue;

				$row = array_pad($row, 27, null); // Pad up to 27 columns

				$data = [
					'date' => $row[0],
					'buyer' => $row[1],
					'buyer_address' => $row[2],
					'consignee_address' => $row[3],
					'voucher_type' => $row[4],
					'voucher_no' => $row[5],
					'voucher_ref_no' => $row[6],
					'gstin_uin' => $row[7],
					'pan_no' => $row[8],
					'narration' => $row[9],
					'order_no_and_date' => $row[10],
					'terms_of_payment' => $row[11],
					'other_references' => $row[12],
					'terms_of_delivery' => $row[13],
					'delivery_note_no_and_date' => $row[14],
					'despatch_doc_no' => $row[15],
					'despatch_through' => $row[16],
					'destination' => $row[17],
					'particulars' => $row[18],
					'quantity' => $row[19],
					'rate' => $row[20],
					'value' => $row[21],
					'gst' => $row[22],
					'discounts' => $row[23],
					'other_charges' => $row[24],
					'gross_total' => $row[25],
					'is_deleted' => '0',
					'status' => '1',
					'created_on' => date('Y-m-d H:i:s'),
					'updated_on' => date('Y-m-d H:i:s')
				];
				$this->db->insert('tbl_sales_report', $data);
			}

			return 1;
		}

		return 0;
	}






	public function forgot_password()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->where('email', $this->input->post('email'));
		$res = $this->db->get('user_data')->row();

		if (!empty($res)) {
			$data = array(
				'password' => ($this->input->post('password')),
				'org_password' => $this->input->post('password'),
			);
			$this->db->where('id', $res->id);
			$this->db->update('user_data', $data);
			return 1;
		} else {
			return 2;
		}
	}
	public function change_password()
	{
		$this->db->where("id", $this->session->userdata("admin_id"));
		$this->db->where("password", md5($this->input->post("old_password")));
		if ($this->db->get("tbl_users")->num_rows() > 0) {
			$data = array(
				"password" => md5($this->input->post("new_password")),
			);
			$this->db->update("tbl_users", $data);
			return true;
		} else {
			return false;
		}
	}
	public function delete()
	{
		$data = array(
			"is_deleted" => "1"
		);
		$this->db->where('id', $this->uri->segment(2));
		$this->db->update($this->uri->segment(3), $data);
		return true;
	}
	public function get_all_machines()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where_in('status', ['1', '2']);
		$this->db->where('plant_id', $this->input->post('plant_id'));
		$this->db->where_in('department_id', ['2', '3', '6', '14']);
		$result = $this->db->get('tbl_machine_master');
		echo json_encode($result->result());
	}
	public function get_pincode_by_location()
	{
		$this->db->select('tbl_location_master.pincode');
		$this->db->where('tbl_location_master.is_deleted', '0');
		$this->db->where('id', $this->input->post('location_id'));
		$result = $this->db->get('tbl_location_master');
		echo json_encode($result->row());
	}
	public function get_plants_by_department()
	{
		$this->db->select('tbl_krivisha_department.*, tbl_plant_master.plant_name');
		$this->db->from('tbl_krivisha_department');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_krivisha_department.plant_id');
		$this->db->where('tbl_krivisha_department.is_deleted', '0');
		$this->db->where('tbl_plant_master.is_deleted', '0');
		$this->db->where('tbl_krivisha_department.department', $this->input->post('department'));
		$result = $this->db->get();

		echo json_encode($result->result());
	}

	public function save_schedule()
	{
		$schedule_id = $this->input->post('schedule_id');

		// $data = array(
		// 	'production_schedule_start_date' => $this->input->post('production_schedule_start_date'),
		// 	'production_schedule_end_date' => $this->input->post('production_schedule_end_date'),
		// 	'production_schedule_start_time' => $this->input->post('production_schedule_start_time'),
		// 	'production_schedule_end_time' => $this->input->post('production_schedule_end_time'),
		// 	'date' => ($d = DateTime::createFromFormat('d-m-Y', $this->input->post('date'))) ? $d->format('Y-m-d') : null,
		// 	'plant_id' => $this->input->post('plant_id'),
		// 	'machine_id' => $this->input->post('machine_id'),
		// 	'created_on' => date("Y-m-d H:i:s")
		// );

		// if (!empty($schedule_id)) {
		// 	$data['updated_on'] = date("Y-m-d H:i:s");
		// 	$this->db->where('id', $schedule_id);
		// 	$this->db->update('tbl_production_schedules', $data);
		// } else {
		// 	$data['created_on'] = date("Y-m-d H:i:s");
		// 	$this->db->insert('tbl_production_schedules', $data);
		// 	$schedule_insert_id = $this->db->insert_id();
		// 	$order_id = 'PRO-' . str_pad($schedule_insert_id, 2, '0', STR_PAD_LEFT);
		// 	$process_data = array(
		// 		'task_id' => $order_id,
		// 		'order_department' => 2,
		// 		'employee_id' => $this->session->userdata("id"),
		// 		'date' => date('Y-m-d'),
		// 		'created_on' => date('Y-m-d H:i:s')
		// 	);
		// 	if (!empty($schedule_id)) {
		// 		$data['updated_on'] = date("Y-m-d H:i:s");
		// 		$this->db->where('task_id', $order_id);
		// 		$this->db->update('tbl_auto_task_list', $process_data);
		// 	} else {
		// 		$this->db->insert('tbl_auto_task_list', $process_data);
		// 	}
		// }
		// if (!empty($schedule_id)) {
		// 	$id = $schedule_id;
		// } else {
		// 	$id = $schedule_insert_id;
		// }
		if ($this->input->post('save_to_session')) {
			$this->session->set_userdata([
				'production_schedule_start_time' => $this->input->post('production_schedule_start_time'),
				'production_schedule_end_time' => $this->input->post('production_schedule_end_time'),
				'production_schedule_start_date' => $this->input->post('production_schedule_start_date'),
				'production_schedule_end_date' => $this->input->post('production_schedule_end_date'),
				'pro_scheduled_date' => ($d = DateTime::createFromFormat('d-m-Y', $this->input->post('date'))) ? $d->format('Y-m-d') : null,
				'plant_name' => $this->input->post('plant'),
				'machine_name' => $this->input->post('machine'),
				'plant_id' => $this->input->post('plant_id'),
				'machine_id' => $this->input->post('machine_id'),
				'schedule_id' => $this->input->post('schedule_id'),
			]);
		}
		return $schedule_id;
	}

	public function get_single_production_schedule()
	{
		$schedule_id = $this->session->userdata('schedule_id');
		$this->db->where('id', $schedule_id);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_production_schedules');
		return $result->row();
	}
	public function set_production_schedule_form()
	{
		// echo"<pre>";print_r($_POST);echo"</pre>";exit;
		$id = $this->input->post('schedule_id');
		$raw_materials = isset($_POST['row_material']) ? $_POST['row_material'] : '';
		$color_id = isset($_POST['color_id']) ? $_POST['color_id'] : '';
		$data = [
			'article_group_id' => $this->input->post('article_group_id'),
			'article_id' => $this->input->post('article_id'),
			'qty' => $this->input->post('qty'),
			'color_id' => $color_id,
			'raw_materials' => $raw_materials,
			'plant_id' => $this->input->post('plant_id'),
			'machine_id' => $this->input->post('machine_id'),
			'production_schedule_start_date' => $this->input->post('production_schedule_start_date'),
			'production_schedule_end_date' => $this->input->post('production_schedule_end_date'),
			'production_schedule_start_time' => $this->input->post('production_schedule_start_time'),
			'production_schedule_end_time' => $this->input->post('production_schedule_end_time'),
			'date' => $this->input->post('pro_scheduled_date'),
		];

		$this->db->where('plant_id', $data['plant_id']);
		$this->db->where('machine_id', $data['machine_id']);
		$this->db->where('production_schedule_start_date', $data['production_schedule_start_date']);
		$this->db->where('production_schedule_end_date', $data['production_schedule_end_date']);
		$this->db->where('is_deleted', '0');
		if (!empty($id)) {
			$this->db->where('id !=', $id);
		}
		$existing_schedules = $this->db->get('tbl_production_schedules')->result();

		$new_start = strtotime($data['production_schedule_start_date'] . ' ' . $data['production_schedule_start_time']);
		$new_end = strtotime($data['production_schedule_end_date'] . ' ' . $data['production_schedule_end_time']);

		foreach ($existing_schedules as $slot) {
			$exist_start = strtotime($slot->production_schedule_start_date . ' ' . $slot->production_schedule_start_time);
			$exist_end = strtotime($slot->production_schedule_end_date . ' ' . $slot->production_schedule_end_time);

			if ($new_start < $exist_end && $new_end > $exist_start) {
				return '2'; // Overlapping schedule found
			}
		}

		$this->db->where('plant_id', $data['plant_id']);
		$this->db->where('machine_id', $data['machine_id']);
		$this->db->where('production_schedule_start_date', $data['production_schedule_start_date']);
		$this->db->where('production_schedule_end_date', $data['production_schedule_end_date']);
		$this->db->where('production_schedule_start_time', $data['production_schedule_start_time']);
		$this->db->where('production_schedule_end_time', $data['production_schedule_end_time']);
		$this->db->where('is_deleted', '0');
		if (!empty($id)) {
			$this->db->where('id !=', $id);
		}
		$existing = $this->db->get('tbl_production_schedules')->row();

		if ($existing) {
			$this->db->where('id', $existing->id);
			$data['updated_on'] = date("Y-m-d H:i:s");
			$this->db->update('tbl_production_schedules', $data);
		}
		if (empty($id)) {
			$data['created_on'] = date("Y-m-d H:i:s");
			$this->db->insert('tbl_production_schedules', $data);
			$schedule_insert_id = $this->db->insert_id();
			$order_id = 'PRO-' . str_pad($schedule_insert_id, 2, '0', STR_PAD_LEFT);
			$this->db->where('id', $schedule_insert_id);
			$this->db->update('tbl_production_schedules', ['order_id' => $order_id]);
			$process_data = array(
				'task_id' => $order_id,
				'order_department' => 2,
				'employee_id' => $this->session->userdata("id"),
				'date' => date('Y-m-d'),
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			if (!empty($schedule_id)) {
				$data['updated_on'] = date("Y-m-d H:i:s");
				$this->db->where('task_id', $order_id);
				$this->db->update('tbl_auto_task_list', $process_data);
			} else {
				$this->db->insert('tbl_auto_task_list', $process_data);
				$last_insert_id = $this->db->insert_id();

				$department_id = 3;
				$log_data = array(
					'task_id' => $last_insert_id,
					'task_status' => '1',
					'details_of_task' => 'Production Scheduled',
					'last_updated_date' => date('Y-m-d'),
					'last_updated_by' => $this->session->userdata("id"),
					'plant_id' => $this->input->post('plant_id'),
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $log_data);
				$history_data = array(
					'task_id' => $last_insert_id,
					'task_status' => '1',
					'task_action' => '1',
					'department_id' => $department_id,
					'details_of_task' => 'Assigned to Production Department',
					'last_updated_date' => date('Y-m-d'),
					'last_updated_by' => $this->session->userdata("id"),
					'plant_id' => $this->input->post('plant_id'),
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $history_data);

			}

			// Notification Work for Production Schedile
			$title = 'Production Schedule';
			$description = 'Production Schedule Created ' . $order_id . ' by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(54, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));

			return '1';
		} else {
			$this->db->where('id', $id);
			$this->db->update('tbl_production_schedules', $data);
			$this->db->where('id', $id);
			$res = $this->db->get('tbl_production_schedules')->row();
			// Notification Work
			$title = 'Production Schedule Updates';
			$description = 'Production Schedule Updated ' . $res->order_id . ' by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $res->order_id,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(54, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));
			return '0';
		}
	}
	public function get_schedule_data()
	{
		$start_date = $this->input->post('start_date');
		$plant_id = $this->input->post('plant_id');
		$machine_id = $this->input->post('machine_id');

		$this->db->select('tbl_production_schedules.*, tbl_plant_master.plant_name, tbl_machine_master.machine_name');
		$this->db->from('tbl_production_schedules');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');

		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->where('tbl_production_schedules.status', '1');

		if (!empty($start_date)) {
			$this->db->where('DATE(tbl_production_schedules.date)', $start_date);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		}

		$query = $this->db->get();
		$result = $query->result();
		//echo"<pre>";print_r($result);exit;

		$leads = array();
		if (!empty($result)) {
			foreach ($result as $int_result) {
				$start_date_fmt = date('Y-m-d', strtotime($int_result->production_schedule_start_date));
				$start_time_24 = date('H:i:s', strtotime($int_result->production_schedule_start_time));
				$start_time_ampm = date('h:i A', strtotime($int_result->production_schedule_start_time));
				$end_date = date('Y-m-d', strtotime($int_result->production_schedule_end_date));
				$end_time_24 = date('H:i:s', strtotime($int_result->production_schedule_end_time));
				$end_time_ampm = date('h:i A', strtotime($int_result->production_schedule_end_time));

				$data = new stdClass();
				$data->id = $int_result->id;
				$data->title = 'Plant: ' . $int_result->plant_name . ' | Machine: ' . $int_result->machine_name;
				$data->start = $start_date_fmt . 'T' . $start_time_24;
				$data->end = $end_date . 'T' . $end_time_24;
				$data->extendedProps = [
					'displayStart' => $start_time_ampm,
					'displayEnd' => $end_time_ampm
				];

				$leads[] = $data;
			}
		}

		echo json_encode($leads);
	}
	public function get_month_schedule_data($month, $plant_id, $machine_id)
	{
		$year = substr($month, 0, 4);
		$month = substr($month, 5, 2);

		$this->db->select('tbl_production_schedules.*, tbl_mould_parts.article_name, GROUP_CONCAT(DISTINCT tbl_mb_master.name ORDER BY tbl_mb_master.name ASC) AS color_name');
		$this->db->from('tbl_production_schedules');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id', 'left');
		$this->db->join('tbl_mb_master', 'FIND_IN_SET(tbl_mb_master.id, tbl_production_schedules.color_id)', 'left');
		$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		$this->db->where('YEAR(production_schedule_start_date)', $year);
		$this->db->where('MONTH(production_schedule_start_date)', $month);
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->group_by('tbl_production_schedules.id');
		$query = $this->db->get();

		$schedules = $query->result_array();

		$events = [];

		foreach ($schedules as $schedule) {
			$start_time = new DateTime($schedule['production_schedule_start_time']);
			$end_time = new DateTime($schedule['production_schedule_end_time']);

			$is_fully_scheduled = ($start_time->format('H') == '08' && $end_time->format('H') == '08');

			$is_partially_scheduled = ($start_time->format('H') >= 8 && $end_time->format('H') < 20);

			$is_unscheduled = ($start_time->format('H') == '00' && $end_time->format('H') == '00');

			$backgroundColor = '';
			if ($is_fully_scheduled) {
				$backgroundColor = 'green';
			} elseif ($is_partially_scheduled) {
				$backgroundColor = 'yellow';
			} elseif ($is_unscheduled) {
				$backgroundColor = 'red';
			}

			$event = [
				'id' => $schedule['id'],
				'name' => $schedule['article_name'],
				'qty' => $schedule['qty'],
				'start' => $schedule['production_schedule_start_date'] . 'T' . $schedule['production_schedule_start_time'],
				//'start' => ($start_time->format('H') < 8 ? (new DateTime($schedule['production_schedule_start_date']))->modify('-1 day')->format('Y-m-d') : $schedule['production_schedule_start_date']) . 'T' . $schedule['production_schedule_start_time'],
				'end' => $schedule['production_schedule_end_date'] . 'T' . $schedule['production_schedule_end_time'],
				'backgroundColor' => $backgroundColor,  // Add color based on schedule status
				'color_name' => $schedule['color_name'],
			];
			$events[] = $event;
		}

		return $events;
	}

	public function get_raw_materials($ids = [])
	{
		$ids = array_values(array_filter($ids, function ($id) {
			return is_numeric($id) && $id !== '';
		}));

		if (!empty($ids)) {
			$this->db->where_in('id', $ids);
			$order = implode(',', $ids); // e.g., "5,6,4"
			$this->db->order_by("FIELD(id, $order)", '', false);
		}

		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_master');
		return $result->result();
	}


	public function get_colors($ids = [])
	{
		$ids = array_values(array_filter($ids, function ($id) {
			return is_numeric($id) && $id !== '';
		}));
		if (!empty($ids)) {
			$this->db->where_in('id', $ids);
			$order = implode(',', $ids);
			$this->db->order_by("FIELD(id, $order)", '', false);
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mb_master');
		return $result->result();
	}

	public function get_artical_particaulars($article_id, $bom_id)
	{

		$this->db->select('tbl_particulars_bom.*, tbl_particulars_master.particulars_type,tbl_rm_master.rm_name as sub_category, tbl_uom_master.uom_name');
		$this->db->join('tbl_production_bom', 'tbl_production_bom.id = tbl_particulars_bom.bom_id');
		$this->db->join('tbl_particulars_master', 'tbl_particulars_master.id = tbl_particulars_bom.particulars_id');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_particulars_bom.sub_category_id');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_particulars_bom.uom_id');
		$this->db->where('tbl_production_bom.article_id', $article_id);
		$this->db->where('tbl_particulars_bom.bom_id', $bom_id);
		$this->db->where('tbl_production_bom.is_deleted', '0');
		if ($this->db->field_exists('is_deleted', 'tbl_particulars_bom')) {
			$this->db->where('tbl_particulars_bom.is_deleted', '0');
		}
		$this->db->order_by('tbl_particulars_bom.id', 'desc');
		$query = $this->db->get('tbl_particulars_bom');
		$result = $query->result();
		// echo "<pre>";print_r($result);exit;
		return $result;
	}
	public function get_artical_bom($article_id)
	{

		$this->db->select('tbl_production_bom.*');
		$this->db->where('tbl_production_bom.article_id', $article_id);
		$this->db->where('tbl_production_bom.is_deleted', '0');
		$this->db->order_by('tbl_production_bom.id', 'DESC');
		$this->db->limit(1);
		$query = $this->db->get('tbl_production_bom');
		$result = $query->row();
		return $result;
	}


	public function get_production_schedule_item_rm_status($type, $schedule_id, $item_table_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('item_type', $type);
		$this->db->where('item_table_id', $item_table_id);
		$this->db->where('schedule_id', $schedule_id);
		$exist = $this->db->get('tbl_production_schedules_rm_details');
		$exist = $exist->row();
		return $exist;
	}

	public function set_production_bom_status_ajx()
	{
		$schedule_id = $this->input->post('schedule_id');
		$this->db->select('tbl_production_schedules.*, tbl_mould_parts.article_name, tbl_group_of_article.group_of_article, tbl_plant_master.plant_name, tbl_machine_master.machine_name');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id');
		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id');
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->where('tbl_production_schedules.id', $schedule_id);
		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'asc');
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->row();
		if (!empty($result)) {
			$type = $this->input->post('type');
			$item_table_id = $this->input->post('item_id');

			$return_qty = $this->input->post('return_qty') ? $this->input->post('return_qty') : 0;
			// echo"<pre>";print_r($return_qty);exit;
			$total_qty = $this->input->post('total_qty');
			$qty = $this->input->post('qty');

			$item_type = $this->input->post('item_type');

			if ($qty > $total_qty) {
				$qty = $total_qty;
			}
			if ($qty == 0.00) {
				$item_status = '0';
			} else if ($qty > 0.00 && $total_qty <= $qty) {
				$item_status = '2';
			} else {
				$item_status = '1';
			}

			$exist = $this->get_production_schedule_item_rm_status($item_type, $schedule_id, $item_table_id);
			if (empty($exist)) {
				$data = array(
					'schedule_id' => $schedule_id,
					'item_table_id' => $item_table_id,
					'item_type' => $item_type,
					'current_qty' => $qty,
					'required_qty' => $total_qty,
					'item_status' => $item_status,
					'return_stock_qty' => $return_qty,
					'created_on' => date('Y-m-d H:i:s')
				);
				// echo"<pre>";print_r($data);exit;
				$this->db->insert('tbl_production_schedules_rm_details', $data);
				if ($item_type == '1' || $item_type == '3') {
					$this->db->select('tbl_raw_material_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('raw_material_id', $item_table_id);
					$result = $this->db->get('tbl_raw_material_stock_report')->row();
					if (!empty($result)) {
						$opening_stock = $result->total_quantity ?? 0;
						$total_qty = $result->total_quantity - $qty;
						$this->db->where('is_deleted', '0');
						$this->db->where('id', $result->id);
						$this->db->update('tbl_raw_material_stock_report', array('total_quantity' => $total_qty));
						$stock_log_data = array(
							'raw_material_id' => $item_table_id,
							'uom_id' => $result->uom_id,
							'plant_id' => $this->input->post('plant_id'),
							'opening_stock' => $opening_stock,
							'outward_qty' => $qty,
							'total_quantity' => $total_qty,
							'is_inward_outward' => '2',
							'created_on' => date('Y-m-d H:i:s'),
							'date' => date('Y-m-d'),
						);
						$this->db->insert('tbl_raw_material_stock_report_history', $stock_log_data);
						log_stock_transaction([
							'item_type' => 'raw_material', 'item_id' => $item_table_id,
							'plant_id'  => $this->input->post('plant_id'),
							'transaction_type' => 'Requisition Issued', 'movement_type' => 'OUT',
							'qty' => $qty, 'balance_qty' => $total_qty, 'uom_id' => $result->uom_id ?? null,
							'reference_no' => 'SCH-' . $schedule_id, 'reference_source' => 'production',
							'created_by' => $this->session->userdata('id'),
						]);
					}else{
						$this->db->where('id', $item_table_id);
						$this->db->where('is_deleted', '0');
						$rm_res = $this->db->get('tbl_rm_master')->row();
						$material_data = array(
							'raw_material_id' => $item_table_id,
							'uom_id' => $rm_res->uom_id,
							'reorder_level' => $rm_res->reorder_level,
							'plant_id' => $this->input->post('plant_id'),
							'total_quantity' => $total_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_raw_material_stock_report', $material_data);
						$last_instert_id = $this->db->insert_id();
						$this->db->where('id', $last_instert_id);
						$result = $this->db->get('tbl_raw_material_stock_report')->row();
						$opening_stock = $result->total_quantity ?? 0;
						$total_qty = $result->total_quantity - $qty;
						$this->db->where('is_deleted', '0');
						$this->db->where('id', $result->id);
						$this->db->update('tbl_raw_material_stock_report', array('total_quantity' => $total_qty));
						$stock_log_data = array(
							'raw_material_id' => $item_table_id,
							'uom_id' => $result->uom_id,
							'plant_id' => $this->input->post('plant_id'),
							'opening_stock' => $opening_stock,
							'outward_qty' => $qty,
							'total_quantity' => $total_qty,
							'is_inward_outward' => '2',
							'created_on' => date('Y-m-d H:i:s'),
							'date' => date('Y-m-d'),
						);
						$this->db->insert('tbl_raw_material_stock_report_history', $stock_log_data);
						log_stock_transaction([
							'item_type' => 'raw_material', 'item_id' => $item_table_id,
							'plant_id'  => $this->input->post('plant_id'),
							'transaction_type' => 'Requisition Issued', 'movement_type' => 'OUT',
							'qty' => $qty, 'balance_qty' => $total_qty, 'uom_id' => $result->uom_id ?? null,
							'reference_no' => 'SCH-' . $schedule_id, 'reference_source' => 'production',
							'created_by' => $this->session->userdata('id'),
						]);
					}
				} else {
					$this->db->select('tbl_master_batch_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('master_batch_id', $item_table_id);
					$result = $this->db->get('tbl_master_batch_stock_report')->row();
					if (!empty($result)) {
						$opening_stock = $result->total_quantity ?? 0;
						$total_qty = $result->total_quantity - $qty;
						$this->db->where('is_deleted', '0');
						$this->db->where('id', $result->id);
						$this->db->update('tbl_master_batch_stock_report', array('total_quantity' => $total_qty));
						$stock_log_data = array(
							'master_batch_id' => $item_table_id,
							'plant_id' => $this->input->post('plant_id'),
							'opening_stock' => $opening_stock,
							'outward_qty' => $qty,
							'total_quantity' => $total_qty,
							'is_inward_outward' => '2',
							'created_on' => date('Y-m-d H:i:s'),
							'date' => date('Y-m-d'),
						);
						$this->db->insert('tbl_master_batch_stock_report_history', $stock_log_data);
						log_stock_transaction([
							'item_type' => 'master_batch', 'item_id' => $item_table_id,
							'plant_id'  => $this->input->post('plant_id'),
							'transaction_type' => 'Requisition Issued', 'movement_type' => 'OUT',
							'qty' => $qty, 'balance_qty' => $total_qty,
							'reference_no' => 'SCH-' . $schedule_id, 'reference_source' => 'production',
							'created_by' => $this->session->userdata('id'),
						]);
					}

				}
			} else if ($return_qty > 0) {
				$data = array(
					'current_qty' => $qty,
					'required_qty' => $total_qty,
					'return_stock_qty' => $return_qty,
				);
				$this->db->where('is_deleted', '0');
				$this->db->where('id', $exist->id);
				$this->db->where('item_type', $item_type);
				$this->db->where('item_table_id', $item_table_id);
				$this->db->where('schedule_id', $schedule_id);
				$this->db->update('tbl_production_schedules_rm_details', $data);

				if ($this->input->post('item_type') == "1" || $this->input->post('item_type') == "3") {
					$this->db->select('tbl_raw_material_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('raw_material_id', $item_table_id);
					$result = $this->db->get('tbl_raw_material_stock_report')->row();
					$opening_stock = $result->total_quantity ?? 0;
					$total_qty = $result->total_quantity + $return_qty;

					$this->db->where('is_deleted', '0');
					$this->db->where('id', $result->id);
					$this->db->update('tbl_raw_material_stock_report', array('total_quantity' => $total_qty));
					$stock_log_data = array(
						'raw_material_id' => $item_table_id,
						'plant_id' => $this->input->post('plant_id'),
						'opening_stock' => $opening_stock,
						'return_stock_qty' => $return_qty,
						'total_quantity' => $total_qty,
						'uom_id' => $result->uom_id,
						'is_inward_outward' => '6',
						'created_on' => date('Y-m-d H:i:s'),
						'date' => date('Y-m-d'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $stock_log_data);
				} else {
					$this->db->select('tbl_master_batch_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('master_batch_id', $item_table_id);
					$result = $this->db->get('tbl_master_batch_stock_report')->row();
					if (!empty($result)) {
						$opening_stock = $result->total_quantity ?? 0;
						$total_qty = $result->total_quantity + $return_qty;
						$this->db->where('id', $result->id);
						$this->db->update('tbl_master_batch_stock_report', array('total_quantity' => $total_qty));

						$stock_log_data = array(
							'master_batch_id' => $item_table_id,
							'plant_id' => $this->input->post('plant_id'),
							'opening_stock' => $opening_stock,
							'return_stock_qty' => $return_qty,
							'total_quantity' => $total_qty,
							'is_inward_outward' => '6',
							'created_on' => date('Y-m-d H:i:s'),
							'date' => date('Y-m-d'),
						);
						$this->db->insert('tbl_master_batch_stock_report_history', $stock_log_data);
					}
				}
				// Notification Work when Return Stock To Store
				if ($this->input->post('item_type') == "1" || $this->input->post('item_type') == "1") {
					$this->db->where('is_deleted', '0');
					$this->db->where('id', $item_table_id);
					$item_name = $this->db->get('tbl_rm_master')->row()->rm_name ?? '';
				} else {
					$this->db->where('is_deleted', '0');
					$this->db->where('id', $item_table_id);
					$item_name = $this->db->get('tbl_mb_master')->row()->name ?? '';
				}
				$title = 'Return Stock Update';
				$description = 'Returned stock of ' . $item_name . ' (' . $return_qty . ') has been updated by ' . $this->session->userdata('name');
				$landing_page = 'plant_list';
				$notification_according = '1';//means according department
				$departments = [25, 24]; // 25 = Admin Department, 24 = store  Department, 
				$departments_str = implode(',', $departments);
				$notification_data = array(
					'notification_title' => $title,
					'notification_description' => $description,
					'notification_department' => $departments_str,
					'landing_page' => $landing_page,
					'order_id' => $schedule_id,
					'plant_id' => $this->input->post('plant_id'),
					'created_on' => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_notifications', $notification_data);
				$this->send_task_notification_by_token(57, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));
			} else {
				$this->db->where('is_deleted', '0');
				$this->db->where('id', $exist->id);
				$this->db->where('item_type', $item_type);
				$this->db->where('item_table_id', $item_table_id);
				$this->db->where('schedule_id', $schedule_id);
				$for_exist_diduct_qty = $this->db->get('tbl_production_schedules_rm_details')->row();
				$current_qty = $for_exist_diduct_qty->current_qty ?? 0;
				$data = array(
					'current_qty' => $qty,
					'required_qty' => $total_qty,
					'item_status' => $item_status,
					'return_stock_qty' => $return_qty,
				);
				$this->db->where('is_deleted', '0');
				$this->db->where('id', $exist->id);
				$this->db->where('item_type', $item_type);
				$this->db->where('item_table_id', $item_table_id);
				$this->db->where('schedule_id', $schedule_id);
				$this->db->update('tbl_production_schedules_rm_details', $data);

				if ($item_type == '1') {
					$this->db->select('tbl_raw_material_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('raw_material_id', $item_table_id);
					$result = $this->db->get('tbl_raw_material_stock_report')->row();

					$total_qty = $result->total_quantity + $current_qty;
					$this->db->where('is_deleted', '0');
					$this->db->where('id', $result->id);
					$this->db->update('tbl_raw_material_stock_report', array('total_quantity' => $total_qty));

					$this->db->select('tbl_raw_material_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('raw_material_id', $item_table_id);
					$last_updated_record = $this->db->get('tbl_raw_material_stock_report')->row();
					// echo"<pre>";print_r($last_updated_record);exit;

					$opening_stock = $last_updated_record->total_quantity ?? 0;
					$total_qty_log = $last_updated_record->total_quantity - $qty;
					$this->db->where('is_deleted', '0');
					$this->db->where('id', $result->id);
					$this->db->update('tbl_raw_material_stock_report', array('total_quantity' => $total_qty_log));
					$stock_log_data = array(
						'raw_material_id' => $item_table_id,
						'uom_id' => $last_updated_record->uom_id,
						'plant_id' => $this->input->post('plant_id'),
						'opening_stock' => $opening_stock,
						'outward_qty' => $qty,
						'total_quantity' => $total_qty_log,
						'is_inward_outward' => '2',
						'created_on' => date('Y-m-d H:i:s'),
						'date' => date('Y-m-d'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $stock_log_data);
				} else {
					$this->db->select('tbl_master_batch_stock_report.*');
					$this->db->where('is_deleted', '0');
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('master_batch_id', $item_table_id);
					$result = $this->db->get('tbl_master_batch_stock_report')->row();
					if (!empty($result)) {
						$total_qty = $result->total_quantity + $current_qty;
						$this->db->where('id', $result->id);
						$this->db->update('tbl_master_batch_stock_report', array('total_quantity' => $total_qty));

						$this->db->select('tbl_master_batch_stock_report.*');
						$this->db->where('is_deleted', '0');
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->where('master_batch_id', $item_table_id);
						$last_updated_record = $this->db->get('tbl_master_batch_stock_report')->row();

						$opening_stock = $last_updated_record->total_quantity ?? 0;
						$total_qty_log = $last_updated_record->total_quantity - $qty;
						$this->db->where('is_deleted', '0');
						$this->db->where('id', $result->id);
						$this->db->update('tbl_master_batch_stock_report', array('total_quantity' => $total_qty_log));
						$stock_log_data = array(
							'master_batch_id' => $item_table_id,
							'plant_id' => $this->input->post('plant_id'),
							'opening_stock' => $total_qty_log,
							'outward_qty' => $qty,
							'total_quantity' => $total_qty,
							'is_inward_outward' => '2',
							'created_on' => date('Y-m-d H:i:s'),
							'date' => date('Y-m-d'),
						);
						$this->db->insert('tbl_master_batch_stock_report_history', $stock_log_data);
					}
				}
			}
			echo '1';
		} else {
			echo '0';
		}
	}

	public function get_article_bom_data_for_store()
	{
		$article = $this->input->post('article_id');

		$this->db->select('tbl_production_bom.*,tbl_mould_parts.article_name,tbl_particulars_bom.particulars_id,tbl_particulars_bom.uom_id,tbl_particulars_bom.quantity,tbl_particulars_master.particulars_type,tbl_uom_master.uom_name');
		$this->db->from('tbl_production_bom');
		$this->db->join('tbl_particulars_bom', 'tbl_particulars_bom.bom_id = tbl_production_bom.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_particulars_bom.uom_id', 'left');
		$this->db->join('tbl_particulars_master', 'tbl_particulars_master.id = tbl_particulars_bom.particulars_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_production_bom.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_bom.article_id', $article);
		$this->db->where('tbl_production_bom.is_deleted', '0');
		$query = $this->db->get();
		$result = $query->result();

		echo json_encode($result);
	}
	public function get_article_bom_data_for_store_dashboard()
	{
		$schedule_id = $this->input->post('schedule_id');
		$this->db->select('tbl_production_schedules.article_id, tbl_production_schedules.qty, tbl_production_bom.id as bom_id');
		$this->db->from('tbl_production_schedules');
		$this->db->join('tbl_production_bom', 'tbl_production_bom.article_id = tbl_production_schedules.article_id');
		$this->db->where('tbl_production_schedules.id', $schedule_id);
		$result = $this->db->get()->row();
		$bom_details = $this->get_artical_particaulars($result->article_id, $result->bom_id);
		echo json_encode($bom_details);
	}


	public function get_all_colors()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$result = $this->db->get('tbl_mb_master');
		return $result->result();
	}

	public function get_single_mb_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mb_master');
		return $result->row();
	}
	public function set_mb_master()
	{
		$data = array(
			'name' => $this->input->post('name'),
			'alias' => $this->input->post('alias'),
			'base' => $this->input->post('base'),
			'make_id' => $this->input->post('make'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_mb_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$updated = $this->db->update('tbl_mb_master', $data);
			if ($updated) {
				return '2';
			} else {
				return '0';
			}
		}
	}
	public function get_all_rm_type_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_type');
		return $result->result();
	}
	public function get_all_rm_make_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_make');
		return $result->result();
	}
	public function get_all_raw_material()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_master');
		return $result->result();
	}
	public function get_all_rm_ink_master()
	{
		$this->db->select('tbl_rm_master.id, tbl_rm_master.rm_name');
		$this->db->join('tbl_rm_type', 'tbl_rm_type.id = tbl_rm_master.type_id');
		$this->db->where('tbl_rm_type.type', 'INK');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$result = $this->db->get('tbl_rm_master');
		return $result->result();
	}

	public function get_single_rm_master()
	{
		$type_of_rm = $this->uri->segment(3);
		if ($type_of_rm == '1') {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('tbl_rm_master');
			return $result->row();
		} else {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('tbl_rm_rejection');
			return $result->row();
		}
	}
	public function set_rm_master()
	{
		$existing_rm = null;
		if ($this->input->post('id') != "") {
			$this->db->select('id, rm_name, type_id');
			$this->db->where('id', $this->input->post('id'));
			$existing_rm = $this->db->get('tbl_rm_master')->row();
		}

		$data = array(
			'plant_id' => $this->session->userdata('assign_plant_id'),
			'reorder_level' => $this->input->post('reorder_level'),
			'rm_name' => $this->input->post('rm_name'),
			'type_of_rm' => $this->input->post('type_of_rm'),
			'mfi' => $this->input->post('mfi'),
			'alias' => $this->input->post('alias'),
			'code' => $this->input->post('code'),
			'make_id' => $this->input->post('make'),
			'type_id' => $this->input->post('type'),
			'uom_id' => $this->input->post('uom_id'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$last_insert_id = $this->input->post('id');
		$is_updated = '1';
		if ($this->input->post('type_of_rm') == '1') {
			if ($this->input->post('id') == "") {
				$data['created_on'] = date('Y-m-d H:i:s');
				$this->db->insert('tbl_rm_master', $data);
				$last_insert_id = $this->db->insert_id();
				$is_updated = '0';
			} else {
				$this->db->where('id', $this->input->post('id'));
				$this->db->update('tbl_rm_master', $data);

			}
		} else {
			if ($this->input->post('id') == "") {
				$data['created_on'] = date('Y-m-d H:i:s');
				$this->db->insert('tbl_rm_rejection', $data);
				$last_insert_id = $this->db->insert_id();
				$is_updated = '0';
			} else {
				$this->db->where('id', $this->input->post('id'));
				$this->db->update('tbl_rm_rejection', $data);
			}
		}

		$this->db->Select('raw_material_id');
		$this->db->where('raw_material_id', $this->input->post('id'));
		$this->_apply_assigned_plants_scope('plant_id');
		$exist = $this->db->get('tbl_raw_material_stock_report')->row();
		if (empty($exist)) {
			$stock_data = array(
				'raw_material_id' => $last_insert_id,
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'reorder_level' => $this->input->post('reorder_level'),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_raw_material_stock_report', $stock_data);
		} else {
			$stock_data = array(
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'reorder_level' => $this->input->post('reorder_level'),
				'updated_on' => date('Y-m-d H:i:s'),
			);
			$this->db->where('raw_material_id', $this->input->post('id'));
			$this->_apply_assigned_plants_scope('plant_id');
			$this->db->update('tbl_raw_material_stock_report', $stock_data);
		}

		// Keep legacy ALANKEY BOLT master in sync when RM is maintained from Add RM.
		// Add Article dropdown reads from tbl_alankey_bolt.
		if ($this->input->post('type_of_rm') == '1' && $this->is_alankey_bolt_rm_type($this->input->post('type'))) {
			$this->sync_alankey_bolt_master(
				trim((string) $this->input->post('rm_name')),
				$existing_rm ? trim((string) $existing_rm->rm_name) : ''
			);
		}

		return $is_updated;
	}

	private function is_alankey_bolt_rm_type($type_id)
	{
		if (empty($type_id)) {
			return false;
		}

		$this->db->select('type');
		$this->db->where('id', $type_id);
		$this->db->where('is_deleted', '0');
		$type_row = $this->db->get('tbl_rm_type')->row();
		if (empty($type_row) || empty($type_row->type)) {
			return false;
		}

		$type_name = strtolower((string) $type_row->type);
		return (
			(strpos($type_name, 'alankey') !== false || strpos($type_name, 'allenkey') !== false)
			&& strpos($type_name, 'bolt') !== false
		);
	}

	private function sync_alankey_bolt_master($new_name, $old_name = '')
	{
		if ($new_name === '') {
			return;
		}

		$this->db->select('id');
		$this->db->where('is_deleted', '0');
		$this->db->where('LOWER(alankey_bolt)', strtolower($new_name));
		$existing_by_new = $this->db->get('tbl_alankey_bolt')->row();
		if (!empty($existing_by_new)) {
			return;
		}

		if ($old_name !== '') {
			$this->db->select('id');
			$this->db->where('is_deleted', '0');
			$this->db->where('LOWER(alankey_bolt)', strtolower($old_name));
			$existing_by_old = $this->db->get('tbl_alankey_bolt')->row();
			if (!empty($existing_by_old)) {
				$this->db->where('id', $existing_by_old->id);
				$this->db->update('tbl_alankey_bolt', array(
					'alankey_bolt' => $new_name,
					'updated_on' => date('Y-m-d H:i:s'),
				));
				return;
			}
		}

		$this->db->insert('tbl_alankey_bolt', array(
			'alankey_bolt' => $new_name,
			'created_on' => date('Y-m-d H:i:s'),
		));
	}
	public function get_type_of_mould($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_type_of_mould');
		return $result->row();
	}
	public function get_air_pin($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_air_pin');
		return $result->row();
	}
	public function get_alankey_bolt($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_alankey_bolt');
		return $result->row();
	}
	public function get_air_spring($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_spring_master');
		return $result->row();
	}
	public function get_air_pu_nipples($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_pu_nipples_master');
		return $result->row();
	}
	public function get_air_ejector_pin($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_ejector_pin_master');
		return $result->row();
	}
	public function get_air_i_bolt($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_i_bolt');
		return $result->row();
	}
	public function get_air_cord($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_cord');
		return $result->row();
	}
	public function get_air_o_ring($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_o_ring');
		return $result->row();
	}
	public function get_air_insert_slot_plate($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_insert_slot_plate');
		return $result->row();
	}
	public function get_air_core_cylinder_seal($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_core_cylinder_seal');
		return $result->row();
	}
	public function get_air_seal($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_seal');
		return $result->row();
	}
	public function get_air_hose_pipe($part_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $part_id);
		$result = $this->db->get('tbl_hose_pipe');
		return $result->row();
	}
	public function get_single_type_of_mould()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_type_of_mould');
		return $result->result();
	}
	public function get_single_air_pin()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_air_pin');
		return $result->result();
	}
	public function get_single_spring()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_spring_master');
		return $result->result();
	}
	public function get_single_pu_nipples()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_pu_nipples_master');
		return $result->result();
	}
	public function get_single_ejector_pin()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_ejector_pin_master');
		return $result->result();
	}
	public function get_single_i_bolt()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_i_bolt');
		return $result->result();
	}
	public function get_single_cord()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_cord');
		return $result->result();
	}
	public function get_single_o_ring()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_o_ring');
		return $result->result();
	}
	public function get_single_insert_slot_plate()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_insert_slot_plate');
		return $result->result();
	}
	public function get_single_core_cylinder_seal()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_core_cylinder_seal');
		return $result->result();
	}
	public function get_single_seal()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_seal');
		return $result->result();
	}
	public function get_single_hose_pipe()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_hose_pipe');
		return $result->result();
	}
	public function get_single_alankey_bolt()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_alankey_bolt');
		return $result->result();
	}
	public function get_single_group_of_article()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_group_of_article');
		return $result->result();
	}

	/**
	 * P1: Get RM or Maintenance materials from tbl_rm_master for Article linking.
	 * @param string $type 'rm' = Raw Materials (type_id 10-16), 'maintenance' = Maintenance Materials (type_id 17,20)
	 */
	/**
	 * Helper to fetch raw materials (from tbl_rm_master) by Category Name (from tbl_rm_type).
	 * Auto-creates the category if it does not exist.
	 */
	public function get_rm_by_category_name($category_name, $alias_name = 'rm_name')
	{
		// Fetch all raw materials for the DDL, ignoring the specific category filter
		// to satisfy the requirement of showing all materials in each dropdown.
		$this->db->select("id, rm_name, rm_name AS $alias_name");
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->order_by('rm_name', 'ASC');
		$res = $this->db->get('tbl_rm_master')->result();
		return $res;
	}

	private function _get_all_rm_by_category_json($category_name, $alias_name)
	{
		$res = $this->get_rm_by_category_name($category_name, $alias_name);
		echo json_encode($res);
	}

	public function get_rm_materials_for_article($type = 'rm')
	{
		$this->db->select('id, rm_name, type_id');
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		if ($type === 'maintenance') {
			$this->db->where_in('type_id', [17, 20]); // MAINTAINANCE MATERIAL, MACHINE MAINTAINANCE
		} else {
			$this->db->where_in('type_id', [10, 11, 12, 13, 14, 15, 16]); // ICP, PP, HDPE, RP INTERNAL, RP, NATURAL PP, REJECTION
		}
		$this->db->order_by('rm_name', 'ASC');
		return $this->db->get('tbl_rm_master')->result();
	}
	public function get_single_article_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mould_parts');
		return $result->row();
	}
	public function set_article_master($insert_data)
	{
		if ($this->input->post('id') == "") {
			$insert_data['plant_id'] = $this->session->userdata('assign_plant_id');
			$this->db->insert('tbl_mould_parts', $insert_data);
			$last_insert_id = $this->db->insert_id();
			// Notification Work when article added
			$title = 'Article Update';
			$description = 'New Article ' . $insert_data['article_name'] . 'has been added  by ' .
				$this->session->userdata('name');
			$landing_page = 'article_master_list';
			$notification_according = '1';//means according department
			$departments = [11, 24, 3]; // 11 = Accounts Department, 25 = store  Department, 3 = Production Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $insert_data['article_name'],
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);


			$this->send_task_notification_by_token(53, $title, $description, $landing_page, $notification_according, $this->session->userdata('assign_plant_id'));

			$stock_data = array(
				'article_id' => $last_insert_id,
				'reorder_level' => $insert_data['reorder_level'],
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_article_stock_report', $stock_data);
			return $last_insert_id;
		} else {
			$insert_data['plant_id'] = $this->session->userdata('assign_plant_id');
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_mould_parts', $insert_data);

			// Notification Work when article added
			$title = 'Article Update';
			$description = 'Article ' . $insert_data['article_name'] . ' has been updated  by ' .
				$this->session->userdata('name');
			$landing_page = 'article_master_list';
			$notification_according = '1';//means according department
			$departments = [11, 24, 3]; // 11 = Accounts Department, 25 = store  Department, 3 = Production Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $insert_data['article_name'],
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(53, $title, $description, $landing_page, $notification_according, $this->session->userdata('assign_plant_id'));

			$this->db->Select('article_id');
			$this->db->where('article_id', $this->input->post('id'));
			$this->_apply_assigned_plants_scope('plant_id');
			$exist = $this->db->get('tbl_article_stock_report')->row();
			if (empty($exist)) {
				$stock_data = array(
					'article_id' => $this->input->post('id'),
					'reorder_level' => $insert_data['reorder_level'],
					'plant_id' => $this->session->userdata('assign_plant_id'),
					'created_on' => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_article_stock_report', $stock_data);
			} else {
				$stock_data = array(
					'reorder_level' => $insert_data['reorder_level'],
					'updated_on' => date('Y-m-d H:i:s')
				);
				$this->db->where('article_id', $this->input->post('id'));
				$this->_apply_assigned_plants_scope('plant_id');
				$this->db->update('tbl_article_stock_report', $stock_data);
			}
			return $this->input->post('id');
		}
	}
	public function get_single_uom_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_uom_master');
		return $result->row();
	}
	public function get_single_kri_department_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_krivisha_department');
		return $result->row();
	}
	public function get_single_extra_payment_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_extra_payment_master');
		return $result->row();
	}
	public function get_single_remark_master()
	{
		$this->ensure_remark_master_table();
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_remark_master');
		return $result->row();
	}

	public function get_single_employee_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('user_data');
		return $result->row();
	}
	public function get_all_kri_department()
	{
		$this->db->where('is_deleted', '0');
		$this->db->group_by('department');
		$result = $this->db->get('tbl_krivisha_department');
		return $result->result();
	}
	public function get_all_kri_employee()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('user_data');
		return $result->result();
	}
	public function set_uom_master()
	{
		$data = array(
			'uom_name' => $this->input->post('uom_name'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_uom_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_uom_master', $data);
			return '2';
		}
	}
	public function set_krivisha_department()
	{
		$data = array(
			'department' => $this->input->post('department'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_krivisha_department', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_krivisha_department', $data);
			return '2';
		}
	}
	public function set_extra_payment_master()
	{
		$data = array(
			'extra_payment_option' => $this->input->post('extra_payment_id'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_extra_payment_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_extra_payment_master', $data);
			return '2';
		}
	}
	public function set_remark_master()
	{
		$this->ensure_remark_master_table();
		$data = array(
			'remark_name' => $this->input->post('remark_name'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_remark_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_remark_master', $data);
			return '2';
		}
	}
	public function set_krivisha_employee()
	{
		// Handle multi-select for department and plant
		$dept_ids = $this->input->post('department_id');
		if (is_array($dept_ids)) {
			$dept_ids_str = implode(',', array_filter($dept_ids));
		} else {
			$dept_ids_str = (string)$dept_ids;
		}

		$plant_ids = $this->input->post('plant_id');
		if (is_array($plant_ids)) {
			$plant_ids_str = implode(',', array_filter($plant_ids));
		} else {
			$plant_ids_str = (string)$plant_ids;
		}

		$user_data = array(
			'first_name' => $this->input->post('employee_name'),
			'email' => $this->input->post('employee_email'),
			'mobile_number' => $this->input->post('employee_contact'),
			'department_id' => $dept_ids_str,
			'plant_id' => $plant_ids_str,
			'designation' => $this->input->post('designation'),
			'date_of_joininig' => $this->input->post('joining_date'),
			'org_password' => $this->input->post('employee_password'),
			'is_admin' => '0',
			'updated_on' => date('Y-m-d H:i:s'),
		);
		// If any selected department is 25 (admin), mark as admin
		$dept_arr = explode(',', $dept_ids_str);
		if (in_array('25', $dept_arr)) {
			$user_data['is_admin'] = '1';
		}
		if ($this->input->post('id') == "") {
			$emp_id = $this->generate_employee_id();
			$user_data['created_on'] = date('Y-m-d H:i:s');
			$user_data['emp_id'] = $emp_id;
			$this->db->insert('user_data', $user_data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('user_data', $user_data);
			return '2';
		}
	}
	private function generate_employee_id()
	{
		$this->db->select('emp_id');
		$this->db->from('user_data');
		$this->db->order_by('emp_id', 'DESC');
		$this->db->limit(1);
		$result_user = $this->db->get();

		if ($result_user && $result_user->num_rows() > 0) {
			$latest_employee_id = $result_user->row()->emp_id;
			preg_match('/(\d+)/', $latest_employee_id, $matches);
			$next_number = intval($matches[0]) + 1;
			return 'EMP-' . str_pad($next_number, 2, '0', STR_PAD_LEFT);
		} else {
			return 'EMP-01';
		}
	}


	public function get_single_plant_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_plant_master');
		return $result->row();
	}
	public function get_all_single_articale()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mould_parts');
		return $result->row();
	}
	public function set_plant_list()
	{
		$data = array(
			'plant_name' => $this->input->post('plant_name'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_plant_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_plant_master', $data);
			return '2';
		}
	}
	public function get_printing_unit_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_printing_unit');
		return $result->row();
	}
	public function set_printing_unit()
	{
		$data = array(
			'printing_name' => $this->input->post('printing_name'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_printing_unit', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_printing_unit', $data);
			return '2';
		}
	}
	public function get_single_machine_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_machine_master');
		return $result->row();
	}
	public function get_all_machine_master()
	{
		$this->db->where('is_deleted', '0');
		$this->_apply_assigned_plants_scope('plant_id');
		$this->db->where_in('department_id', ['2', '3', '6', '14']);
		$result = $this->db->get('tbl_machine_master');
		return $result->result();
	}
	public function get_all_department_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_machine_department');
		return $result->result();
	}
	public function get_all_plant()
	{
		$this->db->where('is_deleted', '0');
		$this->_apply_assigned_plants_scope('id');
		$result = $this->db->get('tbl_plant_master');
		return $result->result();
	}
	public function get_all_plant_for_request_material()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_plant_master');
		return $result->result();
	}
	public function get_all_plant_for_production()
	{
		$this->db->where('is_deleted', '0');
		$this->_apply_assigned_plants_scope('id');
		$result = $this->db->get('tbl_plant_master');
		return $result->result();
	}
	public function get_all_transport()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_transport_master');
		return $result->result();
	}
	public function get_all_location()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_location_master');
		return $result->result();
	}
	public function set_machine_master()
	{
		$department_id = $this->input->post('department');
		$type = '';
		$data = array(
			'machine_name' => $this->input->post('machine_name'),
			'department_id' => $department_id,
			'plant_id' => $this->input->post('plant'),
			'department_type' => $type,
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if (!empty($department_id)) {
			$this->db->where('id', $department_id);
			$this->db->select('department');
			$query = $this->db->get('tbl_machine_department');

			if ($query->num_rows() > 0) {
				$result = $query->row();
				if ($result->department == 'PRINTING') {
					$type = '1';
					$data['department_type'] = $type;
				} else {
					$type = '0';
					$data['department_type'] = $type;
				}
			}
		}
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_machine_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_machine_master', $data);
			return '2';
		}
	}
	public function get_single_location_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_location_master');
		return $result->row();
	}
	public function set_location()
	{
		$data = array(
			'city' => $this->input->post('city'),
			'district_name' => $this->input->post('district_name'),
			'state_name' => $this->input->post('state_name'),
			'pincode' => $this->input->post('pincode'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_location_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_location_master', $data);
			return '2';
		}
	}
	public function get_all_city_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_location_master');
		return $result->result();
	}
	public function get_single_designation_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_designation');
		return $result->result();
	}
	public function get_single_division_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_division');
		return $result->result();
	}
	public function get_single_nature_of_business()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_nature_of_business');
		return $result->result();
	}
	public function get_single_type_of_business()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_type_of_business');
		return $result->result();
	}
	public function get_all_employee_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('user_data');
		return $result->result();
	}

	public function get_employees_by_designation($designation)
	{
		$this->db->where('is_deleted', '0');
		$this->db->like('designation', $designation);
		$result = $this->db->get('user_data');
		return $result->result();
	}

	public function get_all_operators()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('UPPER(designation)', 'OPERATOR');
		$result = $this->db->get('user_data');
		return $result->result();
	}
	public function get_single_party_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_customers');
		return $result->row();
	}
	public function set_party($insert_data)
	{
		if ($this->input->post('id') == "") {
			$this->db->insert('tbl_customers', $insert_data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_customers', $insert_data);
			return '2';
		}
	}
	public function get_all_party_master()
	{
		$this->db->where('is_deleted', '0');
		$this->_restrict_customer_queries();
		$result = $this->db->get('tbl_customers');
		return $result->result();
	}
	public function get_all_sales_officer()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('department_id', '23');
		$result = $this->db->get('user_data');
		return $result->result();
	}
	public function get_all_supplier_party_master()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where_in('party_type', ['2', '3']);
		$this->_restrict_customer_queries();
		$result = $this->db->get('tbl_customers');
		return $result->result();
	}
	public function get_all_article_gruop()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('group_of_article', 'ASC');
		$result = $this->db->get('tbl_group_of_article');
		return $result->result();
	}
	public function get_all_brands()
	{
		$this->db->where('is_deleted', '0');
		$this->db->group_by('brand_name');
		$result = $this->db->get('tbl_brand_master');
		return $result->result();
	}
	public function get_single_brand_type()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_brand_type');
		return $result->result();
	}
	public function get_single_department()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_department');
		return $result->result();
	}
	public function get_single_brand_master()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->uri->segment(2));
		$result = $this->db->get('tbl_brand_master');
		return $result->row();
	}
	public function set_brand($data)
	{
		if ($this->input->post('id') == "") {
			$this->db->insert('tbl_brand_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_brand_master', $data);
			return '2';
		}
	}
	public function get_single_transport_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_transport_master');
		return $result->row();
	}
	public function set_transport($data)
	{
		if ($this->input->post('id') == "") {
			$this->db->insert('tbl_transport_master', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_transport_master', $data);
			return '2';
		}
	}
	public function get_all_type_of_uom()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_uom_master');
		return $result->result();
	}

	public function get_default_uom_id_for_part_label($part_label)
	{
		$part_label = trim((string) $part_label);
		if ($part_label === '') {
			return null;
		}

		$plant_id = $this->session->userdata('assign_plant_id');
		$label_lc = strtolower($part_label);

		$this->db->select('rm.uom_id');
		$this->db->from('tbl_rm_master rm');
		$this->db->join('tbl_rm_type t', 't.id = rm.type_id', 'left');
		$this->db->where('rm.is_deleted', '0');
		$this->db->where('rm.type_of_rm', '1');
		if (!empty($plant_id)) {
			$this->db->where('rm.plant_id', $plant_id);
		}
		$this->db->where('LOWER(TRIM(t.type))', $label_lc);
		$this->db->where('rm.uom_id IS NOT NULL', null, false);
		$this->db->order_by('rm.id', 'DESC');
		$this->db->limit(1);
		$row = $this->db->get()->row();

		return !empty($row) ? $row->uom_id : null;
	}
	public function get_all_extra_payment_option()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_extra_payment_master');
		return $result->result();
	}
	public function get_all_remark_master()
	{
		$this->ensure_remark_master_table();
		$this->db->where('is_deleted', '0');
		$this->db->order_by('remark_name', 'ASC');
		$result = $this->db->get('tbl_remark_master');
		return $result->result();
	}
	public function get_all_particulars_type()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_particulars_master');
		return $result->result();
	}
	public function get_all_mwo_numbers()
	{
		$this->db->select('mwo_code');
		$result = $this->db->get('tbl_maintenance_production');
		return $result->result();
	}
	//old Flow According 
	// public function get_all_sub_category_type()
	// {
	// 	$this->db->where('is_deleted', '0');
	// 	$result = $this->db->get('tbl_particular_sub_type');
	// 	return $result->result();
	// }

	//New Flow According
	public function get_all_sub_category_type($perticular_name)
	{

		$this->db->select('id');
		$this->db->from('tbl_rm_type');
		$this->db->where([
			'is_deleted' => '0',
			'type' => $perticular_name
		]);
		$type_result = $this->db->get()->row();

		if (!$type_result) {
			return [];
		}
		$this->db->select('tbl_rm_master.*, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where([
			'tbl_rm_master.is_deleted' => '0',
			'tbl_rm_master.type_id' => $type_result->id
		]);
		$result = $this->db->get()->result();
		return $result;
	}
	public function get_all_sub_category()
	{
		$particular_id = $this->input->post('particular_id');
		$this->db->select('id,particulars_type');
		$this->db->from('tbl_particulars_master');
		$this->db->where([
			'is_deleted' => '0',
			'id' => $particular_id
		]);
		$type_result = $this->db->get()->row();

		$this->db->where('is_deleted', '0');
		$this->db->where('type', $type_result->particulars_type);
		$rm_type_result = $this->db->get('tbl_rm_type')->row();

		$this->db->select('tbl_rm_master.*, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$this->db->where('tbl_rm_master.type_id', $rm_type_result->id);
		$this->db->order_by('tbl_rm_master.id', 'ASC');
		$result = $this->db->get();
		$result = $result->result();
		echo json_encode($result);
	}
	// public function get_all_sub_category()
	// {
	// 	$particular_id = $this->input->post('particular_id');
	// 	$this->db->where('is_deleted', '0');
	// 	$this->db->where('particular_id', $particular_id);
	// 	$this->db->order_by('id', 'ASC');
	// 	$result = $this->db->get('tbl_particular_sub_type');
	// 	$result = $result->result();
	// 	echo json_encode($result);
	// }

	public function get_single_bom_master()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_maintaince_bom');
		return $result->result();
	}
	public function set_maintaince_bom($bom_data)
	{
		$ar_id = $this->uri->segment(2);
		if ($ar_id != '') {
			$this->db->where('article_id', $ar_id);
			$this->db->delete('tbl_maintaince_bom');
		}
		foreach ($bom_data as $bom) {
			$article_id = $bom['article_id'];
			$group_of_article_id = $bom['group_of_article_id'];
			$size_of_mould = $bom['size_of_mould_id'];
			$type_of_mould = $bom['type_of_mould'];

			$uom = $bom['uom_id'];
			$quantity = $bom['quantity'];
			if ($id == '') {
				$this->db->insert('tbl_maintaince_bom', [
					'article_id' => $article_id,
					'group_of_article_id' => $group_of_article_id,
					'size_of_parts_id' => $size_of_mould,
					'part_of_mould' => $type_of_mould,
					'uom_id' => $uom,
					'quantity' => $quantity,
					'created_on' => date('Y-m-d H:i:s')
				]);
			}
		}
		return $article_id;
	}

	public function get_single_problems_master()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_maintaince_problems');
		return $result->row();
	}
	public function get_single_production_bom()
	{
		$bom_id = $this->uri->segment(3);
		if (!empty($bom_id)) {
			$this->db->where('id', $bom_id);
		} else {
			$this->db->where('article_id', $this->uri->segment(2));
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_production_bom');
		return $result->row();
	}
	public function get_single_order_details()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_order_details');
		return $result->row();
	}
	public function get_single_order_outward_details()
	{
		$this->db->where('order_id', $this->uri->segment(4));
		$this->db->where('sub_order_id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_outward_orders');
		return $result->row();
	}
	public function get_order_outward_details()
	{
		$this->db->where('order_id', $this->uri->segment(4));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_order_details');
		return $result->row();
	}
	public function get_all_sub_order_details_create_order()
	{
		if ($this->uri->segment(3) == 'Container') {
			$this->db->select('tbl_order_sub_details.*,tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_sub_details');
			$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('tbl_order_sub_details.order_id', $this->uri->segment(4));
			$this->db->where('tbl_order_sub_details.is_deleted', '0');
			$result = $this->db->get();
			return $result->result();
		} else {
			$this->db->select('order_container_details.*, tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_container_details as order_container_details');
			$this->db->join('tbl_group_of_article', 'order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('order_container_details.order_id', $this->uri->segment(4));
			$this->db->where('order_container_details.is_deleted', '0');
			$result = $this->db->get();
			return $result->result();
		}
	}

	public function get_all_sub_order_details_add_order()
	{
		if ($this->uri->segment(3) == '2') {
			$this->db->select('tbl_order_sub_details.*,tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_sub_details');
			$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('tbl_order_sub_details.order_id', $this->uri->segment(4));
			$this->db->where('tbl_order_sub_details.is_deleted', '0');
			$result = $this->db->get();
			return $result->result();
		} else {
			$this->db->select('order_container_details.*, tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_container_details as order_container_details');
			$this->db->join('tbl_group_of_article', 'order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('order_container_details.order_id', $this->uri->segment(4));
			$this->db->where('order_container_details.is_deleted', '0');
			$result = $this->db->get();
			return $result->result();
		}
	}
	public function get_all_sub_order_details()
	{
		$article_batch_cache = [];
		if ($this->uri->segment(3) == '2') {
			$this->db->select('tbl_order_sub_details.*,tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_sub_details');
			$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('tbl_order_sub_details.order_id', $this->uri->segment(4));
			$this->db->where_in('tbl_order_sub_details.order_status', ['1', '3', '4', '5', '6', '8', '9']);
			$this->db->where('tbl_order_sub_details.is_deleted', '0');
			$result = $this->db->get();
			$rows = $result->result();
			foreach ($rows as &$row) {
				$bundle_qty = isset($row->bundle_bag_qty) ? trim((string)$row->bundle_bag_qty) : '';
				if ($bundle_qty === '' || floatval($bundle_qty) <= 0) {
					$qty = floatval($row->order_quantity ?? 0);
					$article_id = intval($row->article_id ?? 0);
					$batch_size = 0;
					if ($article_id > 0) {
						if (array_key_exists($article_id, $article_batch_cache)) {
							$batch_size = $article_batch_cache[$article_id];
						} else {
							$bom = $this->get_artical_bom($article_id);
							$batch_size = (!empty($bom) && !empty($bom->batch)) ? floatval($bom->batch) : 0;
							$article_batch_cache[$article_id] = $batch_size;
						}
					}
					$row->bundle_bag_qty = ($qty > 0 && $batch_size > 0) ? rtrim(rtrim(number_format($qty / $batch_size, 2, '.', ''), '0'), '.') : '';
				}
			}
			return $rows;
		} else {
			$this->db->select('order_container_details.*, tbl_group_of_article.group_of_article');
			$this->db->from('tbl_order_container_details as order_container_details');
			$this->db->join('tbl_group_of_article', 'order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->where('order_container_details.order_id', $this->uri->segment(4));
			$this->db->where('order_container_details.order_status !=', '2');
			$this->db->where('order_container_details.is_deleted', '0');
			$result = $this->db->get();
			$rows = $result->result();
			foreach ($rows as &$row) {
				$bundle_qty = isset($row->bundle_bag_qty) ? trim((string)$row->bundle_bag_qty) : '';
				if ($bundle_qty === '' || floatval($bundle_qty) <= 0) {
					$qty = floatval($row->order_quantity ?? 0);
					$article_id = intval($row->article_id ?? 0);
					$batch_size = 0;
					if ($article_id > 0) {
						if (array_key_exists($article_id, $article_batch_cache)) {
							$batch_size = $article_batch_cache[$article_id];
						} else {
							$bom = $this->get_artical_bom($article_id);
							$batch_size = (!empty($bom) && !empty($bom->batch)) ? floatval($bom->batch) : 0;
							$article_batch_cache[$article_id] = $batch_size;
						}
					}
					$row->bundle_bag_qty = ($qty > 0 && $batch_size > 0) ? rtrim(rtrim(number_format($qty / $batch_size, 2, '.', ''), '0'), '.') : '';
				}
			}
			return $rows;
		}
	}

	public function get_single_bom()
	{
		$bom_id = $this->uri->segment(3);
		$this->db->select('tpb.*, tum.uom_name');
		$this->db->from('tbl_particulars_bom tpb');
		$this->db->join('tbl_uom_master tum', 'tum.id = tpb.uom_id', 'left');
		if (!empty($bom_id)) {
			$this->db->where('tpb.bom_id', $bom_id);
		} else {
			$this->db->where('tpb.article_id', $this->uri->segment(2));
		}
		if ($this->db->field_exists('is_deleted', 'tbl_particulars_bom')) {
			$this->db->where('tpb.is_deleted', '0');
		}
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_article()
	{
		$this->db->where('is_deleted', '0');
		// $this->db->where('status', '1');
		$result = $this->db->get('tbl_mould_parts');
		return $result->result();
	}

	public function get_all_container_articles()
	{
		$this->db->where('is_deleted', '0');
		// Filter to only include articles that are likely containers or have a size in LTR/KG
		$this->db->group_start();
		$this->db->like('article_name', 'CONTAINER');
		$this->db->or_like('article_name', 'LTR');
		$this->db->or_like('article_name', 'KG');
		$this->db->or_like('article_name', 'BUCKET');
		$this->db->or_like('article_name', 'MILKCAN');
		$this->db->group_end();
		$this->db->order_by('article_name', 'ASC');
		$result = $this->db->get('tbl_mould_parts');
		return $result->result();
	}
	public function set_problem()
	{
		$data = array(
			'maintaince' => $this->input->post('maintain_actions'),
			'type_id' => $this->input->post('type'),
			'problem' => $this->input->post('problem'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$this->db->update('tbl_machine_master', array('status' => '2'), array('id' => $this->input->post('type')));
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_maintaince_problems', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_maintaince_problems', $data);
			return '2';
		}
	}
	public function get_single_maintenance()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_maintenance_production');
		return $result->row();
	}
	public function get_machine_data_by_id()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_production_report');
		return $result->row();
	}
	public function set_maintenance($data, $mwo_code)
	{
		$id = $this->uri->segment(2);
		if (empty($id)) {
			// Notification Work for maintenance
			$title = 'Maintenance Schedule';
			$description = 'Maintenance Schedule Created ' . $mwo_code . ' by ' .
				$this->session->userdata('name');
			$landing_page = 'maintenance_list';
			$notification_according = '1';//means according department
			$departments = [13, 18]; // 13 = Maintenance Department, 18 = pro Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $mwo_code,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(13, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));

			if ($data['maintaince'] == '2') {
				$this->db->update('tbl_mould_parts', array('status' => '0'), array('id' => $this->input->post('sub_type')));
			} else if ($data['maintaince'] == '4') {
				$this->db->update('tbl_plant_master', array('status' => '0'), array('id' => $this->input->post('sub_type')));
			} else {
				$this->db->update('tbl_machine_master', array('status' => '0'), array('id' => $this->input->post('sub_type')));
			}
			$data['created_on'] = date('Y-m-d H:i:s');
			$data['mwo_code'] = $mwo_code;
			$this->db->insert('tbl_maintenance_production', $data);
			$process_data = array(
				'task_id' => $mwo_code,
				'order_department' => 3,
				'employee_id' => $this->session->userdata("id"),
				'date' => date('Y-m-d'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_auto_task_list', $process_data);
			$last_insert_id = $this->db->insert_id();
			$department_id = 13;
			$log_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'details_of_task' => 'Maintenance Created',
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $this->session->userdata("id"),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $log_data);
			$history_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'task_action' => '1',
				'department_id' => $department_id,
				'details_of_task' => 'Assigned to Maintenance Department',
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $this->session->userdata("id"),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);
			return '1';
		} else {
			// Notification Work for maintenance
			$title = 'Maintenance Schedule Updates';
			$description = 'Maintenance Schedule Updated ' . $mwo_code . ' by ' .
				$this->session->userdata('name');
			$landing_page = 'maintenance_list';
			$notification_according = '1';//means according department
			$departments = [13, 18]; // 13 = Maintenance Department, 18 = pro Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $mwo_code,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);

			$this->send_task_notification_by_token(13, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));
			$this->db->update('tbl_machine_master', array('status' => '0'), array('id' => $this->input->post('sub_type')));
			$this->db->where('id', $id);
			$updated = $this->db->update('tbl_maintenance_production', $data);
			if ($updated) {
				return '2';
			} else {
				return '0';
			}
		}

	}
	public function generate_mwo_code()
	{
		$this->db->select('mwo_code');
		$this->db->from('tbl_maintenance_production');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get();

		if ($result->num_rows() > 0) {
			$latest_mwo_code = $result->row()->mwo_code;
			preg_match('/(\d+)/', $latest_mwo_code, $matches);
			$next_number = intval($matches[0]) + 1;
			return 'MWO-' . str_pad($next_number, 2, '0', STR_PAD_LEFT);
		} else {
			return 'MWO-01';
		}
	}

	public function set_production_bom()
	{
		$data = array(
			'article_id' => $this->uri->segment(2),
			'batch' => $this->input->post('batch'),
			'weight' => $this->input->post('weight'),
			'raw_material_one' => $this->input->post('raw_material_one'),
			'raw_material_two' => $this->input->post('raw_material_two'),
			'other_rm' => $this->input->post('other_rm'),
			'master_batch' => $this->input->post('master_batch'),
			// P1 - New BOM Spec Fields (optional; depends on DB schema)
			// These keys are appended below only if the columns exist.
		);

		if ($this->db->field_exists('std_cycle_time', 'tbl_production_bom')) {
			$data['std_cycle_time'] = $this->input->post('std_cycle_time') ?: NULL;
		}
		if ($this->db->field_exists('std_weight', 'tbl_production_bom')) {
			$data['std_weight'] = $this->input->post('std_weight') ?: NULL;
		}

		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_production_bom', $data);
			$production_bom_id = $this->db->insert_id();
			$action = '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_production_bom', $data);
			$production_bom_id = $this->input->post('id');
			$action = '2';
		}
		$particular_ids = $this->input->post('particular_id') ?: [];
		$sub_categories = $this->input->post('sub_category') ?: [];
		$uoms = $this->input->post('uom_id') ?: [];
		$quantities = $this->input->post('quantity') ?: [];
		$count = count($particular_ids);

		if ($count > 0) {
			if (!empty($production_bom_id)) {
				$this->db->where('bom_id', $production_bom_id);
				$this->db->delete('tbl_particulars_bom');
			}
			for ($i = 0; $i < $count; $i++) {
				$sub_cat_id = isset($sub_categories[$i]) ? $sub_categories[$i] : '';
				$uom_id = isset($uoms[$i]) ? $uoms[$i] : '';
				$qty = isset($quantities[$i]) ? $quantities[$i] : '';

				if ($sub_cat_id == '' || $qty == '') {
					continue;
				}
				if ($uom_id == '') {
					$rm_query = $this->db->select('uom_id')->where('id', $sub_cat_id)->get('tbl_rm_master')->row();
					$uom_id = $rm_query ? $rm_query->uom_id : '';
				}
				$insert_particular_data = array(
					'particulars_id' => $particular_ids[$i],
					'article_id' => $this->uri->segment(2),
					'sub_category_id' => $sub_cat_id,
					'uom_id' => $uom_id,
					'quantity' => $qty,
					'bom_id' => $production_bom_id,
					'created_on' => date('Y-m-d H:i:s'),
				);
				if ($this->db->field_exists('is_deleted', 'tbl_particulars_bom')) {
					$insert_particular_data['is_deleted'] = '0';
				}
				$this->db->insert('tbl_particulars_bom', $insert_particular_data);
			}
		}
		// Debug logging to workspace
		$log_data = "--- " . date('Y-m-d H:i:s') . " ---\n";
		$log_data .= "Action: " . $action . "\n";
		$log_data .= "Post Data: " . print_r($_POST, true) . "\n";
		$log_data .= "Insert Particulars Count: " . $count . "\n";
		$log_data .= "DB Error: " . print_r($this->db->error(), true) . "\n";
		$log_data .= "Last Query: " . $this->db->last_query() . "\n\n";
		@file_put_contents('debug_bom_log.txt', $log_data, FILE_APPEND);

		return $action;
	}
	public function set_order_details()
	{
		$article_batch_cache = [];
		$resolve_article_batch_size = function ($article_id) use (&$article_batch_cache) {
			$article_id = intval($article_id);
			if ($article_id <= 0) {
				return 0;
			}

			if (array_key_exists($article_id, $article_batch_cache)) {
				return $article_batch_cache[$article_id];
			}

			$batch_size = 0;
			$bom = $this->get_artical_bom($article_id);
			if (!empty($bom) && !empty($bom->batch)) {
				$batch_size = floatval($bom->batch);
			}

			if ($batch_size <= 0 && $this->db->field_exists('bundle_bag_qty', 'tbl_mould_parts')) {
				$article_row = $this->db
					->select('bundle_bag_qty')
					->where('id', $article_id)
					->where('is_deleted', '0')
					->get('tbl_mould_parts')
					->row();
				if (!empty($article_row) && isset($article_row->bundle_bag_qty)) {
					$batch_size = floatval($article_row->bundle_bag_qty);
				}
			}

			$article_batch_cache[$article_id] = $batch_size;
			return $batch_size;
		};

		$calculate_bundle_qty = function ($bundle_value, $quantity_value, $article_id = 0) use ($resolve_article_batch_size) {
			$bundle_value = trim((string)$bundle_value);
			if ($bundle_value !== '') {
				return $bundle_value;
			}

			$qty = floatval($quantity_value);
			if ($qty <= 0) {
				return '0';
			}

			$batch_size = $resolve_article_batch_size($article_id);
			if ($batch_size <= 0) {
				$batch_size = 120;
			}

			$bundle = round($qty / $batch_size, 2);
			return rtrim(rtrim(number_format($bundle, 2, '.', ''), '0'), '.');
		};
	
		$order_id = $this->input->post('single_order');
		$order_status = '';
		if ($this->input->post('proceed') == 'save_order') {
			$order_status = '1';
		} else {
			$order_status = '2';
		}
		$party_id = $this->input->post('party_hidden');
		$sales_person = $this->db->select('attending_salesperson_id')->from('tbl_customers')->where('id', $party_id)->get()->row();
		$sales_person_id = $sales_person->attending_salesperson_id ?? null;
		$data = array(
			'party_id' => $this->input->post('party_hidden'),
			'type_of_order' => $this->input->post('type_of_order_hidden'),
			'order_status' => $order_status,
			'plant_id' => $this->session->userdata("assign_plant_id"),
			'sales_person_id' => $sales_person_id
		);
		if ($this->input->post('type_of_order_hidden') == '2') {
			$data['ink_type'] = $this->input->post('ink_type_hidden');
		}
		$current_time = date('Y-m-d H:i:s');
		if (empty($order_id)) {
			$order_id = $this->generate_custom_order_id();
			$data['order_id'] = $order_id;
			$data['created_on'] = $current_time;
			$data['order_date'] = date('Y-m-d');

			$this->db->insert('tbl_order_details', $data);
		} else {
			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_details', $data);
		}
		$this->db->select('id');
		$this->db->from('tbl_krivisha_department');
		$this->db->where('department', 'ACCOUNTS');
		$query = $this->db->get()->row();

		$department_id = $query ? $query->id : null;
		$process_data = array(
			'task_id' => $order_id,
			'order_department' => 1,
			'department_id' => $department_id,
			'employee_id' => $this->session->userdata("id"),
			'party_id' => $this->input->post('party_hidden'),
			'type_of_order' => $this->input->post('type_of_order_hidden'),
			'date' => date('Y-m-d'),
			'plant_id' => $this->session->userdata("assign_plant_id"),
			'created_on' => $current_time
		);
		if ($order_status != '1') {
			$this->db->insert('tbl_auto_task_list', $process_data);
			$last_insert_id = $this->db->insert_id();
			$this->db->select('*');
			$this->db->from('tbl_order_details');
			$this->db->where('order_id', $order_id);
			$order_result = $this->db->get()->row();
			if ($order_result) {
				$log_data = array(
					'task_id' => $last_insert_id,
					'task_status' => '1',
					'party_id' => $order_result->party_id,
					'type_of_order' => $order_result->type_of_order,
					'details_of_task' => 'Order Created',
					'last_updated_date' => date('Y-m-d'),
					'last_updated_by' => $this->session->userdata("id"),
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => $order_result->created_on,
					'updated_on' => $order_result->updated_on,
				);
				$this->db->insert('tbl_auto_task_list_history', $log_data);
			}
			$history_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'task_action' => '1',
				'department_id' => $department_id,
				'details_of_task' => 'Assigned to Accounts Department',
				'party_id' => $this->input->post('party_hidden'),
				'type_of_order' => $this->input->post('type_of_order_hidden'),
				'last_updated_date' => date('Y-m-d'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'last_updated_by' => $this->session->userdata("id"),
				'created_on' => date('Y-m-d H:i:s'),
			);

			$this->db->insert('tbl_auto_task_list_history', $history_data);

			// Notification Work
			$title = 'Order Update';
			$description = 'Order Created ' . $order_id . ' And Assigned to Accounts Department by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 25]; // 11 = Accounts Department, 25 = Admin Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);

			$this->send_task_notification_by_token(50, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));
		}
		$group_ids_update = $this->input->post('group_new_id');

		$article_ids_update = $this->input->post('article_new_id');
		$brand_update = $this->input->post('brand_new_id');
		$order_quantitys_update = $this->input->post('quantity_new_id');
		$bundle_qty_update = $this->input->post('bundle_new_id');

		$remarks_update = $this->input->post('remark_new_id');
		$update_id = $this->input->post('update_ids');
		$group_ids_update = is_array($group_ids_update) ? $group_ids_update : [];
		$article_ids_update = is_array($article_ids_update) ? $article_ids_update : [];
		$brand_update = is_array($brand_update) ? $brand_update : [];
		$brand_update = array_map(function ($value) {
			return ($value === '' || $value === 'undefined' || $value === 'null') ? null : $value;
		}, $brand_update);
		$order_quantitys_update = is_array($order_quantitys_update) ? $order_quantitys_update : [];
		$bundle_qty_update = is_array($bundle_qty_update) ? $bundle_qty_update : [];

		$remarks_update = is_array($remarks_update) ? $remarks_update : [];
		if (count($article_ids_update) > 0) {
			$container_has_bundle = $this->db->field_exists('bundle_bag_qty', 'tbl_order_container_details');
			$sub_has_bundle = $this->db->field_exists('bundle_bag_qty', 'tbl_order_sub_details');
			for ($i = 0; $i < count($article_ids_update); $i++) {
				$id = $update_id[$i];
				$insert_data = array(
					'group_of_article_id' => $group_ids_update[$i],
					'order_id' => $order_id,
					'article_id' => $article_ids_update[$i],
					'order_quantity' => $order_quantitys_update[$i],
					'remark' => $remarks_update[$i],
					'party_id' => $this->input->post('party_hidden'),
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => $current_time,
				);
				$bundle_val = $calculate_bundle_qty(
					$bundle_qty_update[$i] ?? '',
					$order_quantitys_update[$i] ?? 0,
					$article_ids_update[$i] ?? 0
				);

				$b_data = array(
					'brand_type_id' => $brand_update[$i],
					'ink_type' => $this->input->post('ink_type_hidden'),
					'order_date' => date('Y-m-d'),
				);
				$merge_data = array_merge($insert_data, $b_data);
				// echo"<pre>";print_r($merge_data);exit;
				if (in_array($merge_data['brand_type_id'], [null, 'null'], true)) {
					if ($container_has_bundle) {
						$insert_data['bundle_bag_qty'] = $bundle_val;
					}
					$this->db->where('order_id', $order_id);
					$this->db->where('id', $id);
					$this->db->update('tbl_order_container_details', $insert_data);
				} else {
					if ($sub_has_bundle) {
						$merge_data['bundle_bag_qty'] = $bundle_val;
					}
					$this->db->where('order_id', $order_id);
					$this->db->where('id', $id);
					$this->db->update('tbl_order_sub_details', $merge_data);
				}
			}
		}
		$group_ids = $this->input->post('group_id');
		$article_ids = $this->input->post('article_id');
		$brand_type_id = $this->input->post('brand_id');
		$order_quantitys = $this->input->post('quantity_id');
		$bundle_qtys = $this->input->post('bundle_id');
		$remarks = $this->input->post('remark_id');

		$group_ids = is_array($group_ids) ? $group_ids : [];
		$article_ids = is_array($article_ids) ? $article_ids : [];
		$brand_type_id = is_array($brand_type_id) ? $brand_type_id : [];
		$order_quantitys = is_array($order_quantitys) ? $order_quantitys : [];
		$bundle_qtys = is_array($bundle_qtys) ? $bundle_qtys : [];
		$remarks = is_array($remarks) ? $remarks : [];
		$brand_type_id = array_map(function ($value) {
			return ($value === '' || $value === 'undefined' || $value === 'null') ? null : $value;
		}, $brand_type_id);

		if (count($article_ids) > 0) {
			$sub_details_data = [];
			$container_details_data = [];

			$container_has_bundle = $this->db->field_exists('bundle_bag_qty', 'tbl_order_container_details');
			$sub_has_bundle = $this->db->field_exists('bundle_bag_qty', 'tbl_order_sub_details');

			for ($i = 0; $i < count($group_ids); $i++) {
				$data = array(
					'group_of_article_id' => $group_ids[$i],
					'article_id' => $article_ids[$i],
					'order_quantity' => $order_quantitys[$i],
					'remark' => $remarks[$i],
					'order_id' => $order_id,
					'party_id' => $this->input->post('party_hidden'),
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => $current_time,
				);
				$bundle_val = $calculate_bundle_qty(
					$bundle_qtys[$i] ?? '',
					$order_quantitys[$i] ?? 0,
					$article_ids[$i] ?? 0
				);

				$brand_data = array(
					'brand_type_id' => $brand_type_id[$i],
					'ink_type' => $this->input->post('ink_type_hidden'),
					'order_date' => date('Y-m-d'),
				);

				if (in_array($brand_data['brand_type_id'], [null, 'null'], true)) {
					$c_data = $data;
					if ($container_has_bundle) {
						$c_data['bundle_bag_qty'] = $bundle_val;
					}
					$container_details_data[] = $c_data;
				} else {
					$merged_data = array_merge($data, $brand_data);
					if ($sub_has_bundle) {
						$merged_data['bundle_bag_qty'] = $bundle_val;
					}
					$sub_details_data[] = $merged_data;
				}
			}
			//echo"<pre>";print_r($container_details_data);exit;
			if (!empty($sub_details_data)) {
				$this->db->insert_batch('tbl_order_sub_details', $sub_details_data);
			}

			if (!empty($container_details_data)) {
				$this->db->insert_batch('tbl_order_container_details', $container_details_data);
			}
		}
		return '1';
	}

	private function generate_custom_order_id()
	{
		$this->db->select('order_id');
		$this->db->from('tbl_order_details');
		$this->db->like('order_id', 'ORD-');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get();

		if ($result->num_rows() > 0) {
			$last_order_id = $result->row()->order_id;
			$number = intval(substr($last_order_id, 4));
			$new_number = $number + 1;
			return 'ORD-' . str_pad($new_number, 3, '0', STR_PAD_LEFT);
		} else {
			return 'ORD-001';
		}
	}

	public function delete_sub_order_item()
	{
		$type = $this->input->post('type');

		if ($type == '2') {
			$this->db->where('id', $this->input->post('update_id'));
			$this->db->delete('tbl_order_sub_details');
			echo json_encode(['success' => true]);
		} else {
			$this->db->where('id', $this->input->post('update_id'));
			$this->db->delete('tbl_order_container_details');
			echo json_encode(['success' => true]);
		}
	}
	public function set_task()
	{
		$task_head = $this->input->post('task_head');
		$task_type = $this->input->post('task_type');
		$task_id_prefix = '';

		if ($task_head == 1) {
			$task_id_prefix = 'ENQ';
		} else if ($task_head == 2) {
			$task_id_prefix = 'CC';
		} else if ($task_head == 3) {
			$task_id_prefix = 'OFR';
		} else if ($task_head == 4) {
			$task_id_prefix = 'ST';
		} else if ($task_head == 5) {
			$task_id_prefix = 'COMP';
		}
		$task_id = $this->generate_task_id($task_id_prefix);

		$complete_by_date = date('Y-m-d', strtotime($this->input->post('complete_by_date')));
		$assign_department = $this->input->post('assign_department');
		$assign_to = $this->input->post('assign_to');
		$party_id = $this->input->post('party_name');
		$remark = $this->input->post('remark');
		$priority = $this->input->post('priority');

		if ($task_type == 'auto') {
			$auto_data = array(
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'task_id' => $task_id,
				'order_department' => '3',
				'employee_id' => $this->session->userdata("id"),
				'date' => $complete_by_date,
				'task_status' => '1',
				'task_action' => '1',
				'details_of_task' => $remark,
				'department_id' => $assign_department,
				'assign_to_id' => $assign_to,
				'party_id' => $party_id,
				'last_updated_date' => date('Y-m-d'),
				'created_on' => date('Y-m-d H:i:s'),
			);

			$this->db->insert('tbl_auto_task_list', $auto_data);
			$last_insert_id = $this->db->insert_id();

			$history_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'task_action' => '1',
				'details_of_task' => 'Task Created',
				'department_id' => $assign_department,
				'assign_to_id' => $assign_to,
				'party_id' => $party_id,
				'last_updated_by' => $this->session->userdata("id"),
				'last_updated_date' => date('Y-m-d'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);

			$this->db->insert('tbl_auto_task_list_history', $history_data);

			$this->db->select('id, plant_id');
			$this->db->where('id', $assign_to);
			$user_data = $this->db->get('user_data')->row();

			if (!empty($user_data)) {
				$title = 'Task Assigned';
				$description = 'You have been assigned an auto task ' . $task_id . ' by ' . $this->session->userdata('name');
				$landing_page = 'auto_task_list';
				$notification_according = '0';

				$notification_data = array(
					'notification_title' => $title,
					'notification_description' => $description,
					'notification_department' => $assign_department,
					'landing_page' => $landing_page,
					'order_id' => $task_id,
					'plant_id' => $user_data->plant_id,
					'employee_id' => $assign_to,
					'created_on' => date('Y-m-d H:i:s')
				);

				$this->db->insert('tbl_notifications', $notification_data);
				$this->send_task_notification_by_token($assign_to, $title, $description, $landing_page, $notification_according, $user_data->plant_id);
			}

			return '2';
		}

		$data = array(
			'task_id' => $task_id,
			'task_head' => $task_head,
			'employee_id' => $this->session->userdata("id"),
			'party_id' => $party_id,
			'complete_by_date' => $complete_by_date,
			'complete_by_time' => $this->input->post('complete_by_time'),
			'priority' => $priority,
			'remark' => $remark,
			'department_id' => $assign_department,
			'assign_to_id' => $assign_to,
			'plant_id' => $this->session->userdata("assign_plant_id"),
		);
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_manual_task', $data);
		$last_insert_id = $this->db->insert_id();
		$history_data = array(
			'task_id' => $last_insert_id,
			'task_status' => '1',
			'task_action' => '1',
			'department_id' => $assign_department,
			'assign_to_id' => $assign_to,
			'last_updated_by' => $this->session->userdata("id"),
			'details_of_task' => 'Task Created',
			'last_updated_date' => date('Y-m-d'),
			'plant_id' => $this->session->userdata("assign_plant_id"),
			'created_on' => date('Y-m-d H:i:s'),
		);

		// send notification to assign id 
		$this->db->select('id, plant_id'); 
		$this->db->where('id', $assign_to);
		$user_data = $this->db->get('user_data')->row();
		$title = 'Task Assigned';
		$description = 'You have been assigned a task ' . $task_id . ' by ' .
			$this->session->userdata('name');
		$landing_page = 'manual_task_list';
		$notification_according = '0';//means according employee
		$notification_data = array(
			'notification_title' => $title,
			'notification_description' => $description,
			'notification_department' => $assign_department,
			'landing_page' => $landing_page,
			'order_id' => $task_id,
			'plant_id' => $user_data->plant_id,
			'employee_id' => $assign_to,
			'created_on' => date('Y-m-d H:i:s')
		);
		$this->db->insert('tbl_notifications', $notification_data);

		$this->send_task_notification_by_token($assign_to, $title, $description, $landing_page, $notification_according, $user_data->plant_id);

		$this->db->insert('tbl_manual_task_history', $history_data);
		return '1';
	}
	private function generate_task_id($prefix)
	{
		// Fetch latest task ID for the given prefix
		$this->db->select('task_id');
		$this->db->like('task_id', $prefix . '-', 'after'); // only matches IDs starting with prefix-
		$this->db->order_by('CAST(SUBSTRING_INDEX(task_id, "-", -1) AS UNSIGNED)', 'DESC', FALSE); // numeric sort
		$this->db->limit(1);
		$result = $this->db->get('tbl_manual_task');

		if ($result->num_rows() > 0) {
			$last_task = $result->row()->task_id;

			// Extract the number at the end (e.g., from ENQ-100 â†’ 100)
			if (preg_match('/(\d+)$/', $last_task, $matches)) {
				$next_number = (int) $matches[1] + 1;
			} else {
				$next_number = 1;
			}
		} else {
			$next_number = 1;
		}

		// Format with leading zeros if you want (e.g., ENQ-001)
		return $prefix . '-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
	}


	public function get_single_update_task()
	{
		if ($this->uri->segment(3) == "143") {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('tbl_auto_task_list');
			return $result->row();
		} else {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('tbl_manual_task');
			return $result->row();
		}
	}
	public function set_update_task()
	{
		// echo"<pre>";print_r($_POST);exit;
		$id = $this->uri->segment(2);
		$task_action = $this->input->post('task_action');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_auto_task_list');

		$result = $result->row();

		if ($task_action == '2') {
			$department_id = '25';
			$order_id = $result->task_id;

			$this->db->where('is_deleted', '0');
			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_details', array('order_status' => '5'));

			$this->db->where('is_deleted', '0');
			$this->db->where('id', $id);
			$this->db->update('tbl_auto_task_list', array('order_status' => '5'));
		} else {
			$department_id = $this->input->post('assign_department');
		}

		$data = array(
			'task_status' => $this->input->post('task_status'),
			'task_action' => $this->input->post('task_action'),
			'details_of_task' => $this->input->post('enquiry_details'),
			'department_id' => $department_id,
			'type_of_order' => $result->type_of_order,
			'party_id' => $result->party_id,
			'assign_to_id' => $this->input->post('assign_to'),
			'last_updated_date' => date('Y-m-d'),
		);
		$order_department_status = '';
		if ($this->uri->segment(3) == "143") {
			$department_name = trim($this->input->post('assign_department_name'));
			if ($task_action == '1') {
				if ($department_name == "PRINTING") {
					$order_department_status = '2';
					$order_id = $this->input->post('order_id');

					$this->db->where('order_id', $order_id);
					$order_res = $this->db->get('tbl_order_details')->row();
					$department_status = '2'; // 2 means in printing process 
					if ($order_res->type_of_order == '2') {
						$this->db->where('order_id', $order_id);
						$sub_pri_result = $this->db->get('tbl_order_sub_details')->result();

						if (!empty($sub_pri_result)) {

							foreach ($sub_pri_result as $sub_pri) {
								// Update only those records where printing is not completed
								if ($sub_pri->order_status != '9' && $sub_pri->order_status != '10' && $sub_pri->order_status != '8' && $sub_pri->order_status != '3' && $sub_pri->order_status != '4') {
									$this->db->where('id', $sub_pri->id);
									$this->db->update('tbl_order_sub_details', array(
										'order_status' => '7',
										'order_department_status' => $order_department_status
									));

								} else {
									$department_status = '3'; // If any sub detail is already in status 8, 9, or 10, set to false
								}
							}
						}
						$this->db->where('order_id', $order_id);
						$this->db->update('tbl_order_details', array('order_status' => '7'));
						$this->db->where('task_id', $order_id);
						$this->db->update('tbl_auto_task_list', array('order_status' => '7', 'order_department_status' => $department_status));
					}
				}
				if ($department_name == "LOGISTICS") {
					$order_department_status = '3';
					$order_id = $this->input->post('order_id');

					$this->db->where('order_id', $order_id);
					$sub_pri_result = $this->db->get('tbl_order_sub_details')->result();
					if (!empty($sub_pri_result)) {
						foreach ($sub_pri_result as $sub_pri) {
							// Update only those records where printing is completed (status = 8)
							if ($sub_pri->order_status == '8') {
								$this->db->where('id', $sub_pri->id);
								$this->db->update('tbl_order_sub_details', array(
									'order_status' => '9',
									'order_department_status' => $order_department_status
								));
							}
						}
					}

					$this->db->where('order_id', $order_id);
					$this->db->update('tbl_order_details', array('order_status' => '9'));
					$this->db->where('task_id', $order_id);
					$this->db->update('tbl_auto_task_list', array('order_status' => '9', 'order_department_status' => $order_department_status));
				}
			}
			$history_data = $data;
			$this->db->where('id', $id);
			$this->db->update('tbl_auto_task_list', $data);
			$last_insert_id = $this->uri->segment(2);
			$history_data['task_id'] = $last_insert_id;
			$history_data['created_on'] = date('Y-m-d H:i:s');
			$history_data['last_updated_date'] = date('Y-m-d');
			$history_data['last_updated_by'] = $this->session->userdata("id");
			$this->db->where('task_id', $last_insert_id);
			$this->db->order_by('id', 'DESC');
			$this->db->limit(1);
			$last_history_entry = $this->db->get('tbl_auto_task_list_history')->row();
			if (
				!$last_history_entry ||
				$last_history_entry->task_status != $data['task_status'] ||
				$last_history_entry->task_action != $data['task_action'] ||
				$last_history_entry->details_of_task != $data['details_of_task'] ||
				$last_history_entry->department_id != $data['department_id'] ||
				$last_history_entry->assign_to_id != $data['assign_to_id']
			) {
				$this->db->insert('tbl_auto_task_list_history', $history_data);
			}
			if ($task_action == '2') {
				$order_id = $result->task_id;
				$this->db->where('is_deleted', '0');
				$this->db->where('order_id', $order_id);
				$this->db->update('tbl_order_sub_details', ['order_status' => '10']);
				$this->db->where('order_id', $order_id);
				$this->db->update('tbl_order_container_details', ['order_status' => '10']);
				
				$this->db->where('order_id', $order_id);
				$this->db->update('tbl_order_details', array('order_status' => '5'));//order close manually by admin 
			}
			if ($task_action == "3") {
				return '3';
			} else {
				return '1';
			}
		} else {
			$this->db->where('id', $id);
			$task_data = $this->db->get('tbl_manual_task')->row();
			if ($data['department_id'] === '' || $data['department_id'] === null) {
				$data['department_id'] = $task_data->department_id;
			}
			if ($data['assign_to_id'] == '') {
				$data['assign_to_id'] = $task_data->assign_to_id;
			}
			$data['party_id'] = $this->input->post('party_id');
			$this->db->where('id', $id);
			$this->db->update('tbl_manual_task', $data);

			$last_insert_id = $this->uri->segment(2);
			$history_data = $data;
			$history_data['task_id'] = $last_insert_id;
			$history_data['last_updated_date'] = date('Y-m-d');
			$history_data['last_updated_by'] = $this->session->userdata("id");
			$history_data['created_on'] = date('Y-m-d H:i:s');
			$this->db->where('task_id', $last_insert_id);
			$this->db->order_by('id', 'DESC');
			$this->db->limit(1);
			$last_history_entry = $this->db->get('tbl_manual_task_history')->row();

			if (
				!$last_history_entry ||
				$last_history_entry->task_status != $data['task_status'] ||
				$last_history_entry->task_action != $data['task_action'] ||
				$last_history_entry->details_of_task != $data['details_of_task'] ||
				$last_history_entry->department_id != $data['department_id'] ||
				$last_history_entry->assign_to_id != $data['assign_to_id']
			) {

				$this->db->insert('tbl_manual_task_history', $history_data);
			}

			if ($task_action == "3") {
				return '3';
			} else {
				return '2';
			}
		}

	}

	public function set_outward_transport()
	{
		// echo"<pre>";print_r($_POST);exit;
		$division = $this->uri->segment(3);
		$sub_order_id = $this->uri->segment(2);
		$data = array(
			'order_id' => $this->input->post('order_id'),
			'dc_no' => $this->input->post('dc_no'),
			'invoice_no' => $this->input->post('invoice_no'),
			'division' => $this->input->post('division'),
			'party_id' => $this->input->post('party'),
			'invoice_value' => $this->input->post('invoice_value'),
			'freight_amount' => $this->input->post('freight_amount'),
			'location_id' => $this->input->post('location_id'),
			'pincode' => $this->input->post('pincode'),
			'transport_id' => $this->input->post('transport_id'),
			'vehicle' => $this->input->post('vehicle'),
			'vehicle_no' => $this->input->post('vehicle_no'),
			'driver_name' => $this->input->post('driver_name'),
			'order_status' => $this->input->post('order_status'),
			'driver_mobile' => $this->input->post('driver_mobile'),
			'freight_status' => $this->input->post('freight_status'),
			'remark' => $this->input->post('remark'),
			'sub_order_id' => $sub_order_id,
			// Save the dispatching plant — from POST field or session
			'plant_id' => $this->input->post('dispatch_plant_id') ?: ($this->session->userdata('assign_plant_id') ?: null),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_outward_orders', $data);
		$dispatch_id = $this->db->insert_id();

		$article_ids = $this->input->post('article_ids');
		$brand_ids = $this->input->post('brand_ids');
		$order_quantities = $this->input->post('quantity');
		$approved_quantity = $this->input->post('approved_quantity');
		$dispatch_quantities = $this->input->post('dispatch_quantity');
		$remaining_quantity = $this->input->post('remaining_quantity');
		$sub_order_ids = $this->input->post('sub_order_ids');
		$order_id = $this->input->post('order_id');
		$total_dis_quantity = $this->input->post('total_dispatch_quantity');
		// echo"<pre>";print_r($_POST);exit;
		$main_order = array(
			'order_status' => '3',
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$main_order_data = array(
			'order_status' => '4',
			'task_status' => '2',
			'task_action' => '2',
			'department_id' => '25',
			'details_of_task' => 'Order Closed Fully Dispatched',
			'updated_on' => date('Y-m-d H:i:s'),
		);

		$order_list_data = array(
			'order_status' => '4',
			'updated_on' => date('Y-m-d H:i:s'),
		);

		if (!empty($article_ids) && is_array($article_ids) && !empty($dispatch_quantities && is_array($dispatch_quantities))) {
			foreach ($article_ids as $key => $article_id) {
				$dispatch_qty = isset($dispatch_quantities[$key]) ? (int) $dispatch_quantities[$key] : 0;
				if ($dispatch_qty > 0) {
					$order_qty = isset($order_quantities[$key]) ? (int) $order_quantities[$key] : 0;
					$approved_qty = isset($approved_quantity[$key]) ? (int) $approved_quantity[$key] : 0;
					$brand_id = isset($brand_ids[$key]) ? (int) $brand_ids[$key] : 0;
					$total_dispatch_quantity = isset($total_dis_quantity[$key]) ? (int) $total_dis_quantity[$key] : 0;
					$remaining_qty = isset($remaining_quantity[$key]) ? (int) $remaining_quantity[$key] : 0;
					$sub_or_id = isset($sub_order_ids[$key]) ? (int) $sub_order_ids[$key] : 0;
					
					if ($approved_qty) {
						$total_dispatch_qty = $approved_qty - $remaining_qty;
					} else {
						$total_dispatch_qty = $order_qty - $remaining_qty;
					}

					$data_order = array(
						'dispatch_quantity' => $total_dispatch_qty,
						'pending_qty' => $remaining_qty,
					);

					if ($remaining_qty == '0' && $total_dispatch_quantity != '0') {
						$this->db->where('task_id', $order_id);
						$this->db->update('tbl_auto_task_list', $main_order);

						$this->db->where('order_id', $order_id);
						$this->db->update('tbl_order_details', $main_order);

						$data_order['order_status'] = '4';
						if ($division == '2') {
							// echo"<pre>";print_r($data_order);exit;
							$this->db->where('id', $sub_or_id);
							$this->db->update('tbl_order_sub_details', $data_order);
						} else {
							$this->db->where('id', $sub_or_id);
							$this->db->update('tbl_order_container_details', $data_order);
						}
					} else {
						$data_order['order_status'] = '3';
						$this->db->where('order_id', $order_id);
						$this->db->update('tbl_order_details', $main_order);

						$this->db->where('task_id', $order_id);
						$this->db->update('tbl_auto_task_list', $main_order);

						if ($division == '2') {
							$this->db->where('id', $sub_or_id);
							$this->db->update('tbl_order_sub_details', $data_order);
						} else {
							$this->db->where('id', $sub_or_id);
							$this->db->update('tbl_order_container_details', $data_order);
						}
					}
					$total_remaining = $remaining_qty - $dispatch_qty;
					if ($dispatch_qty > 0) {
						// Get snapshot of current impression rate
						$ir_snapshot = $this->db->select('impression_rate')->where('article_id', $article_id)->where('is_deleted', '0')->get('tbl_impression_rate')->row();
						$current_ir = $ir_snapshot ? $ir_snapshot->impression_rate : 0;

						$dispatch_data = array(
							'order_id' => $this->input->post('order_id'),
							'dispatch_id' => $dispatch_id,
							'article_id' => $article_id,
							'brand_type_id' => $brand_id,
							'order_quantity' => $order_qty,
							'approved_quantity' => $approved_qty,
							'dispatch_quantity' => $dispatch_qty,
							'remaining_quantity' => $remaining_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);
						if ($this->db->field_exists('impression_rate', 'tbl_dispatch_order_data')) {
							$dispatch_data['impression_rate'] = $current_ir;
						}
						$this->db->insert('tbl_dispatch_order_data', $dispatch_data);

						$this->db->where('article_id', $article_id);
						// Remove session plant_id restriction to allow finding the actual stock across the company
						$this->db->where('is_deleted', '0');
						$stock_dispatch = $this->db->get('tbl_article_stock_report')->row();

						if ($stock_dispatch) {
							$opening_stock = $stock_dispatch->total_quantity;
							$total_qty = $opening_stock - $dispatch_qty;
							$update_data = array(
								'total_quantity' => $total_qty,
								'updated_on' => date('Y-m-d H:i:s'),
							);
							$this->db->where('id', $stock_dispatch->id);
							$this->db->update('tbl_article_stock_report', $update_data);

							$stock_log_data = array(
								'article_id' => $article_id,
								'plant_id' => $stock_dispatch->plant_id,
								'opening_stock' => $opening_stock,
								'outward_qty' => $dispatch_qty,
								'total_quantity' => $total_qty,
								'is_inward_outward' => '2',
								'date' => date('Y-m-d'),
								'created_on' => date('Y-m-d H:i:s'),
							);
							$this->db->insert('tbl_raw_material_stock_report_history', $stock_log_data);
							log_stock_transaction([
								'item_type' => 'article', 'item_id' => $article_id,
								'plant_id'  => $stock_dispatch->plant_id,
								'transaction_type' => 'KVI Sales', 'movement_type' => 'OUT',
								'qty' => $dispatch_qty, 'balance_qty' => $total_qty,
								'reference_source' => 'dispatch',
								'created_by' => $this->session->userdata('id'),
							]);

						}

					}
				}
			}
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('order_id', $order_id);
		$sub_orders = $this->db->get('tbl_order_sub_details');

		$this->db->where('is_deleted', '0');
		$this->db->where('order_id', $order_id);
		$household_orders = $this->db->get('tbl_order_container_details');


		$order_status = true;

		if ($sub_orders->num_rows() > 0) {
			foreach ($sub_orders->result() as $order) {
				if ($order->order_status !== '4' && $order->order_status !== '2') {
					$order_status = false;
					break;
				}
			}
		} elseif ($household_orders->num_rows() > 0) {
			foreach ($household_orders->result() as $order) {
				if ($order->order_status !== '4' && $order->order_status !== '2') {
					$order_status = false;
					break;
				}
			}
		}

		if ($order_status) {
			$this->db->where('task_id', $order_id);
			$this->db->update('tbl_auto_task_list', $main_order_data);
			$this->db->select('id');
			$this->db->from('tbl_auto_task_list');
			$this->db->where('task_id', $order_id);
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				$row = $query->row();
				$last_updated_id = $row->id;
				$main_order_log_data = array(
					'task_id' => $last_updated_id,
					'task_status' => '2',
					'task_action' => '2',
					'department_id' => '25',
					'details_of_task' => $this->input->post('final_order_remark'),
					'last_updated_date' => date('Y-m-d'),
					'last_updated_by' => $this->session->userdata("id"),
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $main_order_log_data);
			}

			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_details', $order_list_data);

			// Notification Work when Final Order completed
			$party_data = $this->db->get_where('tbl_customers', array('id' => $this->input->post('party')))->row();
			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Full Dispatched  and updated by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 25]; // 11 = Accounts Department, 25 = admin  Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(56, $title, $description, $landing_page, $notification_according, $this->session->userdata('assign_plant_id'));


			$this->db->select('id, plant_id'); 
			$this->db->where('id', $party_data->attending_salesperson_id);
			$user_data = $this->db->get('user_data')->row();

			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Full Dispatched  and updated by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '0';//means according employee
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'employee_id' => $party_data->attending_salesperson_id,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $user_data->plant_id,
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token($party_data->attending_salesperson_id, $title, $description, $landing_page, $notification_according, $user_data->plant_id);
		} else {
			// Notification Work when Order Patially completed
			$party_data = $this->db->get_where('tbl_customers', array('id' => $this->input->post('party')))->row();
			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Partially Dispatched  and updated by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 25]; // 11 = Accounts Department, 25 = admin  Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(56, $title, $description, $landing_page, $notification_according, $this->session->userdata('assign_plant_id'));


			$this->db->select('id, plant_id'); 
			$this->db->where('id', $party_data->attending_salesperson_id);
			$user_data = $this->db->get('user_data')->row();

			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Partially Dispatched  and updated by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '0';//means according employee
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'employee_id' => $party_data->attending_salesperson_id,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $user_data->plant_id,
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token($party_data->attending_salesperson_id, $title, $description, $landing_page, $notification_according, $user_data->plant_id);
		}
		return 1;
	}


	/////////////////////////////////////////////////All Ajax Models/////////////////////////////////////////

	public function check_current_password_match_or_not()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('org_password', $this->input->post('current_password'));
		$result = $this->db->get('user_data')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_mb_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('name', $this->input->post('name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_mb_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_mb_alias()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('alias', $this->input->post('alias'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_mb_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_mb_base()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('base', $this->input->post('base'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_mb_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_article_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('article_name', $this->input->post('article_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_mould_parts')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_employee_id()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('mobile_number', $this->input->post('employee_contact'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('user_data')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function get_all_mb_list($length, $start, $search)
	{
		$this->db->select('tbl_mb_master.*,tbl_make.make');
		$this->db->from('tbl_mb_master');
		$this->db->join('tbl_make', 'tbl_mb_master.make_id = tbl_make.id', 'left');
		$this->db->where('tbl_mb_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('name', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('base', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_mb_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_mb_list_count($search)
	{
		$this->db->select('tbl_mb_master.*,tbl_make.make');
		$this->db->from('tbl_mb_master');
		$this->db->join('tbl_make', 'tbl_mb_master.make_id = tbl_make.id', 'left');
		$this->db->where('tbl_mb_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('name', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('base', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_all_krivisha_employee_list($length, $start, $search)
	{
		$this->db->select('user_data.*, GROUP_CONCAT(DISTINCT tbl_krivisha_department.department SEPARATOR ", ") as department, GROUP_CONCAT(DISTINCT tbl_plant_master.plant_name SEPARATOR ", ") as plant_name');
		$this->db->from('user_data');
		$this->db->join('tbl_krivisha_department', 'FIND_IN_SET(tbl_krivisha_department.id, user_data.department_id)', 'left');
		$this->db->join('tbl_plant_master', 'FIND_IN_SET(tbl_plant_master.id, user_data.plant_id)', 'left');
		$this->db->where('user_data.is_deleted', '0');
		$this->_apply_assigned_plants_scope('user_data.plant_id');
		$this->_apply_assigned_departments_scope('user_data.department_id');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('first_name', $search);
			$this->db->or_like('emp_id', $search);
			$this->db->or_like('email', $search);
			$this->db->or_like('mobile_number', $search);
			$this->db->or_like('designation', $search);
			$this->db->or_like("DATE_FORMAT(date_of_joininig, '%d-%m-%Y')", $search);
			$this->db->or_like('org_password', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->group_end();
		}
		$this->db->group_by('user_data.id');
		$this->db->order_by('user_data.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_krivisha_employee_list_count($search)
	{
		$this->db->select('COUNT(DISTINCT user_data.id) as total');
		$this->db->from('user_data');
		$this->db->join('tbl_krivisha_department', 'FIND_IN_SET(tbl_krivisha_department.id, user_data.department_id)', 'left');
		$this->db->join('tbl_plant_master', 'FIND_IN_SET(tbl_plant_master.id, user_data.plant_id)', 'left');
		$this->db->where('user_data.is_deleted', '0');
		$this->_apply_assigned_plants_scope('user_data.plant_id');
		$this->_apply_assigned_departments_scope('user_data.department_id');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('first_name', $search);
			$this->db->or_like('emp_id', $search);
			$this->db->or_like('email', $search);
			$this->db->or_like('mobile_number', $search);
			$this->db->or_like('designation', $search);
			$this->db->or_like("DATE_FORMAT(date_of_joininig, '%d-%m-%Y')", $search);
			$this->db->or_like('org_password', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		$row = $result->row();
		return $row ? $row->total : 0;
	}
	public function get_all_rm_list($length, $start, $search)
	{
		$this->db->select('tbl_rm_master.*,tbl_rm_type.type,tbl_make.make,tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_master.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_master.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('rm_name', $search);
			$this->db->or_like('mfi', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('code', $search);
			$this->db->or_like('make', $search);
			$this->db->or_like('tbl_rm_type.type', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_rm_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_rm_list_count($search)
	{
		$this->db->select('tbl_rm_master.*,tbl_rm_type.type,tbl_make.make, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_master.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_master.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('rm_name', $search);
			$this->db->or_like('mfi', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('code', $search);
			$this->db->or_like('make', $search);
			$this->db->or_like('tbl_rm_type.type', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();

		return $result->num_rows();
	}
	public function get_all_rm_rejection_list($length, $start, $search)
	{
		$this->db->select('tbl_rm_rejection.*,tbl_rm_type.type,tbl_make.make, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_rejection');
		$this->db->join('tbl_rm_type', 'tbl_rm_rejection.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_rejection.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_rejection.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_rejection.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('rm_name', $search);
			$this->db->or_like('mfi', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('code', $search);
			$this->db->or_like('make', $search);
			$this->db->or_like('tbl_rm_type.type', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_rm_rejection.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_rm_rejection_list_count($search)
	{
		$this->db->select('tbl_rm_rejection.*,tbl_rm_type.type,tbl_make.make,tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_rejection');
		$this->db->join('tbl_rm_type', 'tbl_rm_rejection.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_rejection.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_rejection.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_rejection.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('rm_name', $search);
			$this->db->or_like('mfi', $search);
			$this->db->or_like('alias', $search);
			$this->db->or_like('code', $search);
			$this->db->or_like('make', $search);
			$this->db->or_like('tbl_rm_type.type', $search);
			$this->db->or_like('tbl_make.make', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();

		return $result->num_rows();
	}
	public function check_unique_rm_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('rm_name', $this->input->post('rm_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_rm_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_rm_mfi()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('mfi', $this->input->post('mfi'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_rm_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_rm_alias()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('alias', $this->input->post('alias'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_rm_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function set_new_type_option()
	{
		if ($this->input->post('master_type') == 'type') {
			$this->db->where('is_deleted', '0');
			$this->db->where('type', $this->input->post('new_option'));
			$result = $this->db->get('tbl_rm_type');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"type" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_rm_type', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'make') {
			$this->db->where('is_deleted', '0');
			$this->db->where('make', $this->input->post('new_option'));
			$result = $this->db->get('tbl_make');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"make" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_make', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'ink') {
			$this->db->where('is_deleted', '0');
			$this->db->where('ink', $this->input->post('new_option'));
			$result = $this->db->get('tbl_ink');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"ink" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_ink', $data);
				echo 0;
			} else {
				echo 1;
			}
		}
	}
	public function set_new_option()
	{
		if ($this->input->post('master_type') == 'type_of_mould') {
			$this->db->where('is_deleted', '0');
			$this->db->where('type_of_mould', $this->input->post('new_option'));
			$result = $this->db->get('tbl_type_of_mould');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"type_of_mould" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_type_of_mould', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else {
			$master_type_to_category = array(
				'air_pin' => 'AIR PIN',
				'spring' => 'SPRING',
				'pu_nipples' => 'PU NIPPLES',
				'ejector_pin' => 'EJECTOR PIN',
				'i_bolt' => 'I BOLT',
				'cord' => 'CORD',
				'o_ring' => 'O RING',
				'insert_slot_plate' => 'INSERT SLOT PLATE',
				'core_cylinder_seal' => 'CORE CYLINDER SEAL',
				'seal' => 'SEAL',
				'hose_pipe' => 'HOSE PIPE',
				'alankey_bolt' => 'ALANKEY BOLT'
			);

			$req_type = $this->input->post('master_type');
			$new_val = trim($this->input->post('new_option'));

			if (isset($master_type_to_category[$req_type])) {
				$cat_name = $master_type_to_category[$req_type];

				// 1. Look up or auto-create raw material category
				$this->db->where('type', $cat_name);
				$this->db->where('is_deleted', '0');
				$type_row = $this->db->get('tbl_rm_type')->row();
				if (empty($type_row)) {
					$ins_type = array(
						'type' => $cat_name, 
						'status' => '1', 
						'created_on' => date('Y-m-d H:i:s'),
						'updated_on' => date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_rm_type', $ins_type);
					$type_id = $this->db->insert_id();
				} else {
					$type_id = $type_row->id;
				}

				// 2. Check if standard raw material already exists with this name
				$this->db->where('type_id', $type_id);
				$this->db->where('rm_name', $new_val);
				$this->db->where('is_deleted', '0');
				$result = $this->db->get('tbl_rm_master')->row();

				if (empty($result)) {
					$data = array(
						"type_id" => $type_id,
						"rm_name" => $new_val,
						"status" => '1',
						"created_on" => date('Y-m-d H:i:s'),
						"updated_on" => date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_rm_master', $data);
					echo 0;
				} else {
					echo 1;
				}
			} else if ($this->input->post('master_type') == 'group_of_article') {
				$this->db->where('is_deleted', '0');
				$this->db->where('group_of_article', $this->input->post('new_option'));
				$result = $this->db->get('tbl_group_of_article');
				$result = $result->row();
				if (empty($result)) {
					$data = array(
						"group_of_article" => $this->input->post('new_option'),
						"created_on" => date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_group_of_article', $data);
					echo 0;
				} else {
					echo 1;
				}
			} else {
				echo 1;
			}
		}
	}
	public function get_all_rm_make()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_make');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_rm_type()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_rm_type');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_rm_ink()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_ink');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_type_of_mould()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_type_of_mould');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_air_pin()
	{
		$this->_get_all_rm_by_category_json('AIR PIN', 'air_pin');
	}
	public function get_all_air_spring()
	{
		$this->_get_all_rm_by_category_json('SPRING', 'spring');
	}
	public function get_all_pu_nipples()
	{
		$this->_get_all_rm_by_category_json('PU NIPPLES', 'pu_nipples');
	}
	public function get_all_ejector_pin()
	{
		$this->_get_all_rm_by_category_json('EJECTOR PIN', 'ejector_pin');
	}
	public function get_all_i_bolt()
	{
		$this->_get_all_rm_by_category_json('I BOLT', 'i_bolt');
	}
	public function get_all_cord()
	{
		$this->_get_all_rm_by_category_json('CORD', 'cord');
	}
	public function get_all_o_ring()
	{
		$this->_get_all_rm_by_category_json('O RING', 'o_ring');
	}
	public function get_all_insert_slot_plate()
	{
		$this->_get_all_rm_by_category_json('INSERT SLOT PLATE', 'insert_slot_plate');
	}
	public function get_all_core_cylinder_seal()
	{
		$this->_get_all_rm_by_category_json('CORE CYLINDER SEAL', 'core_cylinder_seal');
	}
	public function get_all_seal()
	{
		$this->_get_all_rm_by_category_json('SEAL', 'seal');
	}
	public function get_all_hose_pipe()
	{
		$this->_get_all_rm_by_category_json('HOSE PIPE', 'hose_pipe');
	}
	public function get_all_alankey_bolt()
	{
		$this->_get_all_rm_by_category_json('ALANKEY BOLT', 'alankey_bolt');
	}
	public function get_all_group_of_article()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_group_of_article');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_maintance_bom_list($length, $start, $search)
	{
		$this->db->select('tbl_maintaince_bom.*,tbl_uom_master.uom_name,tbl_mould_parts.article_name,tbl_group_of_article.group_of_article');
		$this->db->from('tbl_maintaince_bom');
		$this->db->join('tbl_uom_master', 'tbl_maintaince_bom.uom_id=tbl_uom_master.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintaince_bom.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_group_of_article', 'tbl_maintaince_bom.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->group_by('tbl_maintaince_bom.id');
		$this->db->where('tbl_maintaince_bom.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_group_of_article.group_of_article', $search);
			$this->db->or_like('tbl_maintaince_bom.part_of_mould', $search);
			$this->db->or_like('tbl_maintaince_bom.size_of_parts_id', $search);
			$this->db->or_like('tbl_maintaince_bom.quantity', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_maintaince_bom.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_maintance_bom_list_count($search)
	{
		$this->db->select('tbl_maintaince_bom.*,tbl_uom_master.uom_name,tbl_mould_parts.article_name,tbl_group_of_article.group_of_article');
		$this->db->from('tbl_maintaince_bom');
		$this->db->join('tbl_uom_master', 'tbl_maintaince_bom.uom_id=tbl_uom_master.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintaince_bom.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_group_of_article', 'tbl_maintaince_bom.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->group_by('tbl_maintaince_bom.id');
		$this->db->where('tbl_maintaince_bom.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_group_of_article.group_of_article', $search);
			$this->db->or_like('tbl_maintaince_bom.part_of_mould', $search);
			$this->db->or_like('tbl_maintaince_bom.size_of_parts_id', $search);
			$this->db->or_like('tbl_maintaince_bom.quantity', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function check_unique_uom_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('uom_name', $this->input->post('uom_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$res = $this->db->get('tbl_uom_master')->row();
		if (!empty($res)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_department_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('department', $this->input->post('department'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$res = $this->db->get('tbl_krivisha_department')->row();
		if (!empty($res)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_extra_payment_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('extra_payment_option', $this->input->post('extra_payment_id'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$res = $this->db->get('tbl_extra_payment_master')->row();
		if (!empty($res)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_remark_name()
	{
		$this->ensure_remark_master_table();
		$this->db->where('is_deleted', '0');
		$this->db->where('remark_name', $this->input->post('remark_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$res = $this->db->get('tbl_remark_master')->row();
		if (!empty($res)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function get_all_uom_list($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_uom_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('uom_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_uom_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_uom_list_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_uom_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('uom_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}

	public function check_unique_challan_dc_no()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('challan_dc_no', $this->input->post('challan_dc_no'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_invoice_no()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('invoice_no', $this->input->post('invoice_no'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_dc_no()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('dc_no', $this->input->post('dc_no'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_outward_orders')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_outward_invoice_no()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('invoice_no', $this->input->post('invoice_no'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_outward_orders')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function get_all_krivisha_department_list($length, $start, $search)
	{
		$this->db->select('tbl_krivisha_department.*');
		$this->db->from('tbl_krivisha_department');
		$this->db->where('tbl_krivisha_department.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('department', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_krivisha_department.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_krivisha_department_list_count($search)
	{
		$this->db->select('tbl_krivisha_department.*');
		$this->db->from('tbl_krivisha_department');
		$this->db->where('tbl_krivisha_department.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('department', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_all_extra_payment_option_list($length, $start, $search)
	{
		$this->db->select('tbl_extra_payment_master.*');
		$this->db->from('tbl_extra_payment_master');
		$this->db->where('tbl_extra_payment_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('extra_payment_option', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_extra_payment_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_extra_payment_option_list_count($search)
	{
		$this->db->select('tbl_extra_payment_master.*');
		$this->db->from('tbl_extra_payment_master');
		$this->db->where('tbl_extra_payment_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('extra_payment_option', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_all_remark_master_list($length, $start, $search)
	{
		$this->ensure_remark_master_table();
		$this->db->select('tbl_remark_master.*');
		$this->db->from('tbl_remark_master');
		$this->db->where('tbl_remark_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('remark_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_remark_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_remark_master_list_count($search)
	{
		$this->ensure_remark_master_table();
		$this->db->select('tbl_remark_master.*');
		$this->db->from('tbl_remark_master');
		$this->db->where('tbl_remark_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('remark_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}

	private function ensure_remark_master_table()
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS `tbl_remark_master` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`remark_name` varchar(255) DEFAULT NULL,
			`is_deleted` tinyint(1) NOT NULL DEFAULT '0',
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`created_on` datetime DEFAULT NULL,
			`updated_on` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
	}

	public function get_all_brand_type_list($length, $start, $search)
	{
		$this->db->select('tbl_machine_master.*,tbl_machine_department.department,tbl_plant_master.plant_name');
		$this->db->from('tbl_machine_master');
		$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_machine_master.plant_id = tbl_plant_master.id', 'left');
		$this->db->group_by('tbl_machine_master.id');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('machine_name', $search);
			$this->db->or_like('tbl_machine_department.department', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_machine_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_machine_list($length, $start, $search)
	{
		$this->db->select('tbl_machine_master.*,tbl_machine_department.department,tbl_plant_master.plant_name');
		$this->db->from('tbl_machine_master');
		$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_machine_master.plant_id = tbl_plant_master.id', 'left');
		$this->db->group_by('tbl_machine_master.id');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('machine_name', $search);
			$this->db->or_like('tbl_machine_department.department', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);

			$this->db->group_end();
		}
		$this->db->order_by('tbl_machine_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		return $query->result();
	}
	public function get_all_machine_list_count($search)
	{
		$this->db->select('tbl_machine_master.*,tbl_machine_department.department,tbl_plant_master.plant_name');
		$this->db->from('tbl_machine_master');
		$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_machine_master.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('machine_name', $search);
			$this->db->or_like('tbl_machine_department.department', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_all_printing_unit_list($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_printing_unit');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('printing_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_printing_unit.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_printing_unit_list_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_printing_unit');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('printing_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function check_unique_printing_unit()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('printing_name', $this->input->post('printing_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_printing_unit')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_machine_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('machine_name', $this->input->post('machine_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_machine_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function get_all_plant_list($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_plant_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('plant_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_plant_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_plant_list_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_plant_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('plant_name', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();

		return $result->num_rows();
	}
	public function check_unique_plant_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('plant_name', $this->input->post('plant_name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_plant_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function get_all_location_list($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_location_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('city', $search);
			$this->db->or_like('district_name', $search);
			$this->db->or_like('state_name', $search);
			$this->db->or_like('pincode', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_location_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_location_list_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_location_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('city', $search);
			$this->db->or_like('district_name', $search);
			$this->db->or_like('state_name', $search);
			$this->db->or_like('pincode', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function check_unique_city_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('city', $this->input->post('city'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_location_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_pincode_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('pincode', $this->input->post('pincode'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_location_master')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_party_name()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('party_name', $this->input->post('party_name'));
		$this->db->where('gst_pan', $this->input->post('gst_pan'));
		$this->db->where('mobile', $this->input->post('mobile'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_customers')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_mobile()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('mobile', $this->input->post('mobile'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_customers')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function check_unique_gst_pan()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('gst_pan', $this->input->post('gst_pan'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$result = $this->db->get('tbl_customers')->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo "0";
		}
	}
	public function set_new_party_option()
	{
		if ($this->input->post('master_type') == 'designation') {
			$this->db->where('is_deleted', '0');
			$this->db->where('designation', $this->input->post('new_option'));
			$result = $this->db->get('tbl_designation');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"designation" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_designation', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'designation_two') {
			$this->db->where('is_deleted', '0');
			$this->db->where('designation', $this->input->post('new_option'));
			$result = $this->db->get('tbl_designation');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"designation" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_designation', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'attending_salesperson') {
			$this->db->where('is_deleted', '0');
			$this->db->where('division', $this->input->post('new_option'));
			$result = $this->db->get('tbl_division');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"division" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_division', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'nature_of_business') {
			$this->db->where('is_deleted', '0');
			$this->db->where('nature_of_business', $this->input->post('new_option'));
			$result = $this->db->get('tbl_nature_of_business');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"nature_of_business" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_nature_of_business', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'type_of_business') {
			$this->db->where('is_deleted', '0');
			$this->db->where('type_of_business', $this->input->post('new_option'));
			$result = $this->db->get('tbl_type_of_business');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"type_of_business" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_type_of_business', $data);
				echo 0;
			} else {
				echo 1;
			}
		}
	}

	public function get_all_designation()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_designation');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_division()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_division');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_nature_of_business()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_nature_of_business');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_type_of_business()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_type_of_business');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_parties_list($length, $start, $search)
	{
		// --- Base customer query ---
		$this->db->select('tbl_customers.*,tbl_location_master.city,
        tbl_designation.designation AS designation,
        designation_two.designation AS designation_two,
        user_data.first_name,
        tbl_nature_of_business.nature_of_business,
        tbl_type_of_business.type_of_business');
		$this->db->from('tbl_customers');
		$this->db->join('tbl_location_master', 'tbl_customers.city_id=tbl_location_master.id', 'left');
		$this->db->join('tbl_designation', 'tbl_customers.designation_id=tbl_designation.id', 'left');
		$this->db->join('tbl_designation AS designation_two', 'tbl_customers.designation_two_id=designation_two.id', 'left');
		$this->db->join('user_data', 'tbl_customers.attending_salesperson_id=user_data.id', 'left');
		$this->db->join('tbl_nature_of_business', 'tbl_customers.nature_of_business_id=tbl_nature_of_business.id', 'left');
		$this->db->join('tbl_type_of_business', 'tbl_customers.type_of_business_id=tbl_type_of_business.id', 'left');
		$this->db->group_by('tbl_customers.id');
		$this->db->where('tbl_customers.is_deleted', '0');

		if ((string) $this->session->userdata('is_admin') !== '1') {
			$user_id = $this->session->userdata('id');
			if (!empty($user_id)) {
				$this->db->group_start();
				$this->db->where('tbl_customers.attending_salesperson_id', $user_id);
				$this->db->or_where('tbl_customers.dg_id', $user_id);
				$this->db->or_where('tbl_customers.asm_id', $user_id);
				$this->db->or_where('tbl_customers.state_head_id', $user_id);
				$this->db->or_where('tbl_customers.telecaller_id', $user_id);
				$this->db->group_end();
			}
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_customers.address', $search);
			$this->db->or_like('tbl_customers.mobile', $search);
			$this->db->or_like('tbl_customers.gst_pan', $search);
			$this->db->or_like('tbl_customers.sec_contact', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_designation.designation', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_nature_of_business.nature_of_business', $search);
			$this->db->or_like('tbl_type_of_business.type_of_business', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_customers.id', 'DESC');

		$customers = $this->db->get()->result();

		// --- Orders data ---
		$this->db->where('is_deleted', '0');
		$this->db->where('order_department', '1');
		$orders = $this->db->get('tbl_auto_task_list')->result();

		$current_date = date('Y-m-d');
		$final_result = [];

		foreach ($customers as $cust) {
			$customer_id = $cust->id;

			// Get customer orders
			$customer_orders = array_filter($orders, function ($order) use ($customer_id) {
				return $order->party_id == $customer_id;
			});

			$status = "No Orders"; // default
			if (!empty($customer_orders)) {
				$last_order_date = max(array_column($customer_orders, 'updated_on'));
				$date_diff = (strtotime($current_date) - strtotime($last_order_date)) / (60 * 60 * 24);

				if ($date_diff <= 30) {
					$status = "active";
				} elseif ($date_diff > 30 && $date_diff <= 60) {
					$status = "inactive";
				} elseif ($date_diff > 60 && $date_diff <= 90) {
					$status = "lost";
				} else {
					$status = "Very Old";
				}
			}

			$cust->party_status = $status;
			$final_result[] = $cust;
		}

		// --- Filter by party status ---
		$filter = $this->input->post('party_filter');
		if ($filter) {
			$final_result = array_filter($final_result, function ($row) use ($filter) {
				return strtolower($row->party_status) == strtolower($filter);
			});
		}

		// âœ… Pagination Apply karo yaha
		return array_slice($final_result, $start, $length);
	}


	public function get_all_parties_list_count($search)
	{
		$this->db->select('tbl_customers.*,tbl_location_master.city,
        tbl_designation.designation AS designation,
        designation_two.designation AS designation_two,
        user_data.first_name,
        tbl_nature_of_business.nature_of_business,
        tbl_type_of_business.type_of_business');
		$this->db->from('tbl_customers');
		$this->db->join('tbl_location_master', 'tbl_customers.city_id=tbl_location_master.id', 'left');
		$this->db->join('tbl_designation', 'tbl_customers.designation_id=tbl_designation.id', 'left');
		$this->db->join('tbl_designation AS designation_two', 'tbl_customers.designation_two_id=designation_two.id', 'left');
		$this->db->join('user_data', 'tbl_customers.attending_salesperson_id=user_data.id', 'left');
		$this->db->join('tbl_nature_of_business', 'tbl_customers.nature_of_business_id=tbl_nature_of_business.id', 'left');
		$this->db->join('tbl_type_of_business', 'tbl_customers.type_of_business_id=tbl_type_of_business.id', 'left');
		$this->db->group_by('tbl_customers.id');
		$this->db->where('tbl_customers.is_deleted', '0');

		if ((string) $this->session->userdata('is_admin') !== '1') {
			$user_id = $this->session->userdata('id');
			if (!empty($user_id)) {
				$this->db->group_start();
				$this->db->where('tbl_customers.attending_salesperson_id', $user_id);
				$this->db->or_where('tbl_customers.dg_id', $user_id);
				$this->db->or_where('tbl_customers.asm_id', $user_id);
				$this->db->or_where('tbl_customers.state_head_id', $user_id);
				$this->db->or_where('tbl_customers.telecaller_id', $user_id);
				$this->db->group_end();
			}
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_customers.mobile', $search);
			$this->db->or_like('tbl_customers.address', $search);
			$this->db->or_like('tbl_customers.gst_pan', $search);
			$this->db->or_like('tbl_customers.sec_contact', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_designation.designation', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_nature_of_business.nature_of_business', $search);
			$this->db->or_like('tbl_type_of_business.type_of_business', $search);
			$this->db->group_end();
		}

		$customers = $this->db->get()->result();

		$this->db->where('is_deleted', '0');
		$this->db->where('order_department', '1');
		$orders = $this->db->get('tbl_auto_task_list')->result();

		$current_date = date('Y-m-d');
		$final_result = [];

		foreach ($customers as $cust) {
			$customer_id = $cust->id;

			$customer_orders = array_filter($orders, function ($order) use ($customer_id) {
				return $order->party_id == $customer_id;
			});

			$status = "No Orders";
			if (!empty($customer_orders)) {
				$last_order_date = max(array_column($customer_orders, 'updated_on'));
				$date_diff = (strtotime($current_date) - strtotime($last_order_date)) / (60 * 60 * 24);

				if ($date_diff <= 30) {
					$status = "active";
				} elseif ($date_diff > 30 && $date_diff <= 60) {
					$status = "inactive";
				} elseif ($date_diff > 60 && $date_diff <= 90) {
					$status = "lost";
				} else {
					$status = "Very Old";
				}

			}

			$cust->party_status = $status;
			$final_result[] = $cust;
		}

		$filter = $this->input->post('party_filter');
		if ($filter) {
			$final_result = array_filter($final_result, function ($row) use ($filter) {
				return strtolower($row->party_status) == strtolower($filter);
			});
		}

		return count($final_result);
	}

	public function set_new_brand_option()
	{
		if ($this->input->post('master_type') == 'brand_type') {
			$this->db->where('is_deleted', '0');
			$this->db->where('brand_type', $this->input->post('new_option'));
			$result = $this->db->get('tbl_brand_type');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"brand_type" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_brand_type', $data);
				echo 0;
			} else {
				echo 1;
			}
		} else if ($this->input->post('master_type') == 'department') {
			$this->db->where('is_deleted', '0');
			$this->db->where('department', $this->input->post('new_option'));
			$result = $this->db->get('tbl_department');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"department" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_department', $data);
				echo 0;
			} else {
				echo 1;
			}
		}
	}
	public function get_all_brand_type()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_brand_type');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_department()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_department');
		$result = $result->result();
		echo json_encode($result);
	}
	public function check_unique_brand_name()
	{
		// P1: Brand uniqueness restriction removed â€” same brand name allowed for different parties/plants
		echo '0';
	}
	public function get_all_brands_list($length, $start, $search)
	{
		$brand_filter = $this->input->get('brand_filter') ?? $this->input->post('brand_filter') ?? '';
		// echo"<pre>";print_r($brand_filter);exit;
		$this->db->select('
			tbl_brand_master.*,
			tbl_brand_type.brand_type,
			tbl_customers.party_name,
			tbl_department.department,
			GROUP_CONCAT(DISTINCT tbl_rm_master.rm_name ORDER BY tbl_rm_master.rm_name SEPARATOR ", ") as ink_names
		');
		$this->db->from('tbl_brand_master');
		$this->db->join('tbl_brand_type', 'tbl_brand_master.brand_type_id = tbl_brand_type.id', 'left');
		$this->db->join('tbl_customers', 'tbl_brand_master.party_name_id = tbl_customers.id', 'left');
		$this->db->join('tbl_department', 'tbl_brand_master.department_id = tbl_department.id', 'left');
		$this->db->join('tbl_rm_master', 'FIND_IN_SET(tbl_rm_master.id, tbl_brand_master.ink_ids)', 'left');
		$this->db->where('tbl_brand_master.is_deleted', '0');

		if ($brand_filter == 'inactive') {
			$days = 45;

			// Subquery: get last order date per brand
			$subquery = "(SELECT brand_type_id, MAX(created_on) as last_order_date 
						FROM tbl_order_sub_details 
						WHERE is_deleted = '0' 
						GROUP BY brand_type_id) as orders";

			$this->db->join($subquery, 'orders.brand_type_id = tbl_brand_master.id', 'left');

			// Filter brands with no orders OR last order older than 45 days
			$this->db->group_start();
			$this->db->where('orders.last_order_date IS NULL');
			$this->db->or_where('DATEDIFF(CURDATE(), orders.last_order_date) >', $days);
			$this->db->group_end();
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_brand_master.brand_name', $search);
			$this->db->or_like('tbl_brand_type.brand_type', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_department.department', $search);
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->group_end();
		}

		$this->db->group_by('tbl_brand_master.id');
		$this->db->order_by('tbl_brand_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}

	public function get_all_brands_list_count($search)
	{
		$brand_filter = $this->input->get('brand_filter') ?? $this->input->post('brand_filter') ?? '';
		$this->db->select('tbl_brand_master.id');
		$this->db->from('tbl_brand_master');
		$this->db->join('tbl_brand_type', 'tbl_brand_master.brand_type_id = tbl_brand_type.id', 'left');
		$this->db->join('tbl_customers', 'tbl_brand_master.party_name_id = tbl_customers.id', 'left');
		$this->db->join('tbl_department', 'tbl_brand_master.department_id = tbl_department.id', 'left');
		$this->db->join('tbl_rm_master', 'FIND_IN_SET(tbl_rm_master.id, tbl_brand_master.ink_ids)', 'left');
		$this->db->where('tbl_brand_master.is_deleted', '0');
		if ($brand_filter == 'inactive') {
			$days = 45;

			// Subquery: get last order date per brand
			$subquery = "(SELECT brand_type_id, MAX(created_on) as last_order_date 
						FROM tbl_order_sub_details 
						WHERE is_deleted = '0' 
						GROUP BY brand_type_id) as orders";

			$this->db->join($subquery, 'orders.brand_type_id = tbl_brand_master.id', 'left');

			// Filter brands with no orders OR last order older than 45 days
			$this->db->group_start();
			$this->db->where('orders.last_order_date IS NULL');
			$this->db->or_where('DATEDIFF(CURDATE(), orders.last_order_date) >', $days);
			$this->db->group_end();
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_brand_master.brand_name', $search);
			$this->db->or_like('tbl_brand_type.brand_type', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_department.department', $search);
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->group_end();
		}

		$this->db->group_by('tbl_brand_master.id');
		$result = $this->db->get();
		return $result->num_rows();
	}

	public function get_all_transport_list($length, $start, $search)
	{
		$this->db->select('tbl_transport_master.*,GROUP_CONCAT(DISTINCT tbl_location_master.city ORDER BY tbl_location_master.city SEPARATOR ", ") as cities');
		$this->db->from('tbl_transport_master');
		$this->db->join('tbl_location_master', 'FIND_IN_SET(tbl_location_master.id, tbl_transport_master.city_id)', 'left');
		$this->db->where('tbl_transport_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_transport_master.transport_name', $search);
			$this->db->or_like('tbl_transport_master.mobile_one', $search);
			$this->db->or_like('tbl_transport_master.mobile_two', $search);
			$this->db->or_like('tbl_transport_master.transport_id', $search);
			$this->db->or_like('tbl_transport_master.transport_rating', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->group_end();
		}
		$this->db->group_by('tbl_transport_master.id');
		$this->db->order_by('tbl_transport_master.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_transport_list_count($search)
	{
		$this->db->select('tbl_transport_master.*,GROUP_CONCAT(DISTINCT tbl_location_master.city ORDER BY tbl_location_master.city SEPARATOR ", ") as cities');
		$this->db->from('tbl_transport_master');
		$this->db->join('tbl_location_master', 'FIND_IN_SET(tbl_location_master.id, tbl_transport_master.city_id)', 'left');

		$this->db->where('tbl_transport_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_transport_master.transport_name', $search);
			$this->db->or_like('tbl_transport_master.mobile_one', $search);
			$this->db->or_like('tbl_transport_master.mobile_two', $search);
			$this->db->or_like('tbl_transport_master.transport_id', $search);
			$this->db->or_like('tbl_transport_master.transport_rating', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->group_end();
		}
		$this->db->group_by('tbl_transport_master.id');
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_selected_mould($article_id, $part_id, $label)
	{
		$this->db->where('article_id', $article_id);
		$this->db->where('size_of_parts_id', $part_id);
		$this->db->where('part_of_mould', $label);
		$result = $this->db->get('tbl_maintaince_bom');
		return $result->row();
	}
	public function get_machine_master()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_machine_master');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_machine_type_problem($maintaince)
	{
		if ($maintaince == '1') {
			$this->db->select('tbl_machine_master.*, tbl_machine_department.department');
			$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
			$this->db->where('tbl_machine_department.department !=', 'PRINTING');
			$this->db->where('tbl_machine_master.is_deleted', '0');
			$result = $this->db->get('tbl_machine_master');
			return $result->result();
		} else {
			$this->db->select('tbl_machine_master.*, tbl_machine_department.department');
			$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
			$this->db->where('tbl_machine_department.department', 'PRINTING');
			$this->db->where('tbl_machine_master.is_deleted', '0');
			$result = $this->db->get('tbl_machine_master');
			return $result->result();
		}
	}
	public function get_article_type_group_releted($maintaince)
	{
		$this->db->select('tbl_mould_parts.*');
		$this->db->where('tbl_mould_parts.group_of_article_id', $maintaince);
		$this->db->where('tbl_mould_parts.is_deleted', '0');
		$result = $this->db->get('tbl_mould_parts');
		return $result->result();
	}
	public function get_article_type_problem()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$result = $this->db->get('tbl_mould_parts');
		return $result->result();
	}
	public function get_plant_type_problem()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_plant_master');
		return $result->result();
	}

	public function get_printing_unit()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_printing_unit');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_article_master()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$this->db->where('status', '1');
		$result = $this->db->get('tbl_mould_parts');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_plant_master()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_plant_master');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_problems_list($length, $start, $search)
	{

		$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name, tbl_mould_parts.article_name, tbl_plant_master.plant_name');
		$this->db->from('tbl_maintaince_problems');

		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintaince_problems.type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_maintaince_problems.type_id ', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_maintaince_problems.type_id ', 'left');


		$this->db->where('tbl_maintaince_problems.is_deleted', '0');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_machine_master.machine_name', $search);
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_maintaince_problems.problem', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_maintaince_problems.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_problems_list_count($search)
	{
		$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name, tbl_mould_parts.article_name, tbl_plant_master.plant_name');
		$this->db->from('tbl_maintaince_problems');

		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintaince_problems.type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_maintaince_problems.type_id ', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_maintaince_problems.type_id ', 'left');


		$this->db->where('tbl_maintaince_problems.is_deleted', '0');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_machine_master.machine_name', $search);
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_maintaince_problems.problem', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_type_of_machine($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_machine_master');
		return $result->row();
	}
	public function get_order_details_according_order_id($order_id)
	{
		$this->db->select('tbl_auto_task_list.*, tbl_customers.party_name');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_auto_task_list.party_id ', 'left');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');
		$this->db->where('tbl_auto_task_list.task_id', $order_id);
		$result = $this->db->get();
		return $result->row();
	}
	public function get_inks_according_brand($id)
	{
		$this->db->select('tbl_brand_master.*,GROUP_CONCAT(DISTINCT tbl_rm_master.rm_name ORDER BY tbl_rm_master.rm_name SEPARATOR ", ") as ink_names');
		$this->db->from('tbl_brand_master');
		$this->db->join('tbl_rm_master', 'FIND_IN_SET(tbl_rm_master.id, tbl_brand_master.ink_ids)', 'left');
		$this->db->where('tbl_brand_master.is_deleted', '0');
		$this->db->where('tbl_brand_master.id', $id);
		$result = $this->db->get();
		return $result->row();
	}
	public function get_type_of_article($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_mould_parts');
		return $result->row();
	}
	public function get_type_of_plant($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_plant_master');
		return $result->row();
	}
	public function get_task_from_create_order($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_customers');
		return $result->row();
	}
	public function get_task_from_maintenance_production($id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_maintenance_production');
		return $result->row();
	}
	public function get_all_type_of_maintenance()
	{
		$selected_master = $this->input->post('selected_master');
		$this->db->order_by('id', 'DESC');
		switch ($selected_master) {
			case '1':
				$this->db->select('tbl_machine_master.*, tbl_machine_department.department');
				$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
				$this->db->where('tbl_machine_department.department !=', 'PRINTING');
				$result = $this->db->get('tbl_machine_master');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				break;
			case '2':
				$this->db->where('tbl_mould_parts.is_deleted', '0');
				$result = $this->db->get('tbl_mould_parts');
				break;
			case '3':
				$this->db->select('tbl_machine_master.*, tbl_machine_department.department');
				$this->db->join('tbl_machine_department', 'tbl_machine_master.department_id = tbl_machine_department.id', 'left');
				$this->db->where('tbl_machine_department.department', 'PRINTING');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				$result = $this->db->get('tbl_machine_master');
				break;
			case '4':
				$this->db->where('tbl_plant_master.is_deleted', '0');
				$result = $this->db->get('tbl_plant_master');
				break;
			default:
				$this->db->select('tbl_maintaince_problems.*');
				$this->db->where('tbl_maintaince_problems.maintaince', '5');
				$this->db->where('tbl_maintaince_problems.type_id', '0');
				$this->db->where('tbl_maintaince_problems.is_deleted', '0');
				$result = $this->db->get('tbl_maintaince_problems');
				break;
		}
		echo json_encode($result->result());
	}

	public function get_machine_types()
	{
		$selected_master = $this->input->post('selected_master');

		$this->db->where('tbl_maintaince_problems.is_deleted', '0');
		$this->db->order_by('id', 'DESC');

		switch ($selected_master) {
			case '1':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				$this->db->where('tbl_maintaince_problems.maintaince', $selected_master);
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;
			case '2':
				$this->db->select('tbl_maintaince_problems.*, tbl_mould_parts.article_name');
				$this->db->join('tbl_mould_parts', 'tbl_maintaince_problems.type_id = tbl_mould_parts.id', 'left');
				$this->db->where('tbl_mould_parts.is_deleted', '0');
				$this->db->where('tbl_maintaince_problems.maintaince', $selected_master);
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;
			case '3':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				$this->db->where('tbl_machine_master.department_type', '1');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				$this->db->where('tbl_maintaince_problems.maintaince', $selected_master);
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;
			case '4':
				$this->db->select('tbl_maintaince_problems.*, tbl_plant_master.plant_name');
				$this->db->join('tbl_plant_master', 'tbl_maintaince_problems.type_id = tbl_plant_master.id', 'left');
				$this->db->where('tbl_plant_master.is_deleted', '0');
				$this->db->where('tbl_maintaince_problems.maintaince', '4');
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;
			case '5':
				$this->db->select('tbl_maintaince_problems.*');
				$this->db->where('tbl_maintaince_problems.maintaince', '5');
				break;
			default:
				$result = [];
				echo json_encode($result);
				return;
		}
		$result = $this->db->get('tbl_maintaince_problems');
		echo json_encode($result->result());
	}

	public function get_all_task_list()
	{
		$task_depatment = $this->input->post('task_depatment');

		switch ($task_depatment) {
			case '1':
				$this->db->select('tbl_order_details.*, tbl_customers.party_name');
				$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
				$this->db->where('tbl_order_details.order_status', '2');
				$this->db->where('tbl_order_details.is_deleted', '0');
				$result = $this->db->get('tbl_order_details');
				break;
			case '2':
				$this->db->select('tbl_maintenance_production.*');
				$this->db->where('tbl_maintenance_production.is_deleted', '0');
				$result = $this->db->get('tbl_maintenance_production');
				break;
			case '3':
				$this->db->select('tbl_maintenance_production.*');
				$this->db->where('tbl_maintenance_production.is_deleted', '0');
				$result = $this->db->get('tbl_maintenance_production');
				break;
			default:
				$result = [];
				echo json_encode($result);
				return;
		}
		echo json_encode($result->result());
	}

	public function get_all_sub_types()
	{
		if ($this->input->post('selected_master') == '1') {
			$this->db->select('tbl_maintaince_problems.*,tbl_machine_master.machine_name');
			$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id=tbl_machine_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $this->input->post('selected_master'));
			$this->db->where('tbl_maintaince_problems.type_id', $this->input->post('selected_type'));
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			echo json_encode($result);
		} else if ($this->input->post('selected_master') == '2') {
			$this->db->select('tbl_maintaince_problems.*,tbl_mould_parts.article_name');
			$this->db->join('tbl_mould_parts', 'tbl_maintaince_problems.type_id=tbl_mould_parts.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $this->input->post('selected_master'));
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->where('tbl_maintaince_problems.type_id', $this->input->post('selected_type'));
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			echo json_encode($result);
		} else if ($this->input->post('selected_master') == '3') {
			$this->db->select('tbl_maintaince_problems.*,tbl_machine_master.machine_name');
			$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id=tbl_machine_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.type_id', $this->input->post('selected_type'));
			$this->db->where('tbl_maintaince_problems.maintaince', $this->input->post('selected_master'));
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			echo json_encode($result);
		} else if ($this->input->post('selected_master') == '4') {
			$this->db->select('tbl_maintaince_problems.*,tbl_plant_master.plant_name');
			$this->db->join('tbl_plant_master', 'tbl_maintaince_problems.type_id=tbl_plant_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $this->input->post('selected_master'));
			$this->db->where('tbl_maintaince_problems.type_id', $this->input->post('selected_type'));
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			echo json_encode($result);
		} else {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', '5');
			$this->db->where('tbl_maintaince_problems.type_id', '0');
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			echo json_encode($result);
		}
	}
	public function get_all_sub_types_display($maintaince)
	{
		if ($maintaince == '1') {
			$this->db->select('tbl_maintaince_problems.*,tbl_machine_master.machine_name as name');
			$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id=tbl_machine_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->group_by('tbl_maintaince_problems.type_id');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '2') {
			$this->db->select('tbl_maintaince_problems.*,tbl_mould_parts.article_name as name');
			$this->db->join('tbl_mould_parts', 'tbl_maintaince_problems.type_id=tbl_mould_parts.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->group_by('tbl_maintaince_problems.type_id');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '3') {
			$this->db->select('tbl_maintaince_problems.*,tbl_machine_master.machine_name as name');
			$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id=tbl_machine_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->group_by('tbl_maintaince_problems.type_id');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '4') {
			$this->db->select('tbl_maintaince_problems.*,tbl_plant_master.plant_name as name');
			$this->db->join('tbl_plant_master', 'tbl_maintaince_problems.type_id=tbl_plant_master.id', 'left');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->group_by('tbl_maintaince_problems.type_id');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		}
	}
	public function get_all_problems_display($maintaince, $sub_type_id)
	{
		if ($maintaince == '1') {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.type_id', $sub_type_id);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '2') {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.type_id', $sub_type_id);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '3') {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.type_id', $sub_type_id);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else if ($maintaince == '4') {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', $maintaince);
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		} else {
			$this->db->select('tbl_maintaince_problems.*');
			$this->db->where('tbl_maintaince_problems.maintaince', '5');
			$this->db->where('tbl_maintaince_problems.is_deleted', '0');
			$this->db->order_by('id', 'DESC');
			$result = $this->db->get('tbl_maintaince_problems');
			$result = $result->result();
			return $result;
		}
	}
	public function get_all_production_maintenance_list($length, $start, $search)
	{
		$this->db->select('tbl_maintenance_production.*,tbl_maintenance_list.status_of_work as main_status,tbl_maintenance_list.plant_manager_approval_status, GROUP_CONCAT(tbl_maintaince_problems.problem ORDER BY tbl_maintaince_problems.problem SEPARATOR ", ") as problems,tbl_plant_master.plant_name,user_data.first_name');
		$this->db->from('tbl_maintenance_production');
		$this->db->join(' tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
		$this->db->join('tbl_maintenance_list', 'tbl_maintenance_production.mwo_code=tbl_maintenance_list.mwo_code', 'left');
		$this->db->join('tbl_plant_master', 'tbl_maintenance_production.plant_id=tbl_plant_master.id', 'left');
		$this->db->join('user_data', 'tbl_maintenance_production.employee_id=user_data.id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_production.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_production.sub_type_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_maintenance_production.is_deleted', '0');
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_maintenance_production.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('status_of_work') != "") {
			$this->db->where('tbl_maintenance_production.status_of_work', $this->input->post('status_of_work'));
			$this->db->where('tbl_maintenance_list.plant_manager_approval_status IS NULL', null, false);
		}
		if ($this->input->post('search_mwo_code') != "") {
			$this->db->where('tbl_maintenance_production.mwo_code', $this->input->post('search_mwo_code'));
		}
		if ($this->input->post('search_sub_category') != "") {
			$subCategory = $this->input->post('search_sub_category');
			$maintainAction = $this->input->post('search_maintain_action');

			if ($maintainAction == '1' || $maintainAction == '3' || $maintainAction == '2' || $maintainAction == '4' || $maintainAction == '5') {
				$this->db->where('tbl_maintenance_production.sub_type_id', $subCategory);
			}
		}

		if ($this->input->post('search_type_action') != "") {
			$this->db->where('tbl_maintenance_production.type_of_action', $this->input->post('search_type_action'));
		}

		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('tbl_maintenance_production.maintaince', $this->input->post('search_maintain_action'));
		}

		$this->db->group_by('tbl_maintenance_production.id');

		if (!empty($search)) {

			$this->db->group_start();

			$this->db->or_like('tbl_maintenance_production.date', $search);
			$this->db->or_like('tbl_maintenance_production.mwo_code', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_maintenance_production.type_of_action', $search);
			$this->db->or_like('tbl_maintenance_production.maintaince', $search);

			$this->db->or_like(' tbl_maintaince_problems.problem', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_maintenance_production.id', 'DESC');

		if ($length > 0) { $this->db->limit($length, $start); }

		$result = $this->db->get();

		return $result->result();
	}

	public function get_total_production_list_count()
	{
		$this->db->select('COUNT(*)as total');
		$this->db->from('tbl_maintenance_production');
		$this->db->where('tbl_maintenance_production.is_deleted', '0');
		$this->db->join(' tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
		$this->db->join('tbl_maintenance_list', 'tbl_maintenance_production.mwo_code=tbl_maintenance_list.mwo_code', 'left');
		$this->db->join('tbl_plant_master', 'tbl_maintenance_production.plant_id=tbl_plant_master.id', 'left');
		$this->db->join('user_data', 'tbl_maintenance_production.employee_id=user_data.id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_production.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_production.sub_type_id = tbl_mould_parts.id', 'left');
		if ($this->input->post('status_of_work') != "") {
			$this->db->where('tbl_maintenance_production.status_of_work', $this->input->post('status_of_work'));
			$this->db->where('tbl_maintenance_list.plant_manager_approval_status IS NULL', null, false);
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_maintenance_production.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('status_of_work') != "") {
			$this->db->where('tbl_maintenance_production.status_of_work', $this->input->post('status_of_work'));
		}
		if ($this->input->post('search_mwo_code') != "") {
			$this->db->where('tbl_maintenance_production.mwo_code', $this->input->post('search_mwo_code'));
		}
		if ($this->input->post('search_sub_category') != "") {
			$subCategory = $this->input->post('search_sub_category');
			$maintainAction = $this->input->post('search_maintain_action');

			if ($maintainAction == '1' || $maintainAction == '3' || $maintainAction == '2' || $maintainAction == '4' || $maintainAction == '5') {
				$this->db->where('tbl_maintenance_production.sub_type_id', $subCategory);
			}
		}

		if ($this->input->post('search_type_action') != "") {
			$this->db->where('tbl_maintenance_production.type_of_action', $this->input->post('search_type_action'));
		}

		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('tbl_maintenance_production.maintaince', $this->input->post('search_maintain_action'));
		}
		$result = $this->db->get()->row();
		return $result->total;
	}

	public function get_all_production_list_count($search)
	{
		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_maintenance_production');
		$this->db->join(' tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
		$this->db->join('tbl_maintenance_list', 'tbl_maintenance_production.mwo_code=tbl_maintenance_list.mwo_code', 'left');
		$this->db->join('tbl_plant_master', 'tbl_maintenance_production.plant_id=tbl_plant_master.id', 'left');
		$this->db->join('user_data', 'tbl_maintenance_production.employee_id=user_data.id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_production.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_production.sub_type_id = tbl_mould_parts.id', 'left');

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_maintenance_production.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_production.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('status_of_work') != "") {
			$this->db->where('tbl_maintenance_production.status_of_work', $this->input->post('status_of_work'));
			$this->db->where('tbl_maintenance_list.plant_manager_approval_status IS NULL', null, false);
		}
		if ($this->input->post('search_mwo_code') != "") {
			$this->db->where('tbl_maintenance_production.mwo_code', $this->input->post('search_mwo_code'));
		}

		if ($this->input->post('search_type_action') != "") {
			$this->db->where('tbl_maintenance_production.type_of_action', $this->input->post('search_type_action'));
		}

		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('tbl_maintenance_production.maintaince', $this->input->post('search_maintain_action'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_maintenance_production.date', $search);
			$this->db->or_like('tbl_maintenance_production.mwo_code', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_maintenance_production.type_of_action', $search);
			$this->db->or_like('tbl_maintenance_production.maintaince', $search);

			$this->db->or_like(' tbl_maintaince_problems.problem', $search);
			;
			$this->db->group_end();
		}

		$this->db->order_by('tbl_maintenance_production.id', 'DESC');
		$result = $this->db->get()->row();
		return $result->total;
	}

	public function set_new_particular()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('particulars_type', $this->input->post('add_particular'));
		$result = $this->db->get('tbl_particulars_master');
		$result = $result->row();
		if (empty($result)) {
			$this->db->where('is_deleted', '0');
			$this->db->where('type', $this->input->post('add_particular'));
			$rm_result = $this->db->get('tbl_rm_type')->row();
			if (empty($rm_result)) {
				$rm_data = array(
					"type" => $this->input->post('add_particular'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_rm_type', $rm_data);
			}
			$data = array(
				"particulars_type" => $this->input->post('add_particular'),
				"created_on" => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_particulars_master', $data);
			$result = $this->db->insert_id();
			echo $result;
		} else {
			echo '';
		}
	}
	public function get_all_particular()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('tbl_particulars_master');
		$result = $result->result();
		echo json_encode($result);
	}

	// public function set_new_sub_type()
	// {
	// 	$this->db->where('is_deleted', '0');
	// 	$this->db->where('particular_id', $this->input->post('particular_id'));
	// 	$this->db->where('sub_category', $this->input->post('new_option'));
	// 	$result = $this->db->get('tbl_particular_sub_type');
	// 	$result = $result->row();
	// 	if (empty($result)) {
	// 		$data = array(
	// 			"sub_category" => $this->input->post('new_option'),
	// 			"particular_id" => $this->input->post('particular_id'),
	// 			"created_on" => date('Y-m-d H:i:s')
	// 		);
	// 		$this->db->insert('tbl_particular_sub_type', $data);
	// 		echo 0;
	// 	} else {
	// 		echo 1;
	// 	}
	// }
	public function set_new_sub_type()
	{
		// echo"<pre>"; print_r($this->input->post()); exit;
		$this->db->where('is_deleted', '0');
		$this->db->where('rm_name', $this->input->post('new_option'));
		$result = $this->db->get('tbl_rm_master');
		$result = $result->row();
		if (empty($result)) {
			$this->db->where('is_deleted', '0');
			$this->db->where('id', $this->input->post('rm_type'));
			$per_result = $this->db->get('tbl_particulars_master')->row();
			$last_insert_id = 0;
			if ($per_result) {
				$type_name = $per_result->particulars_type;
				$this->db->where('is_deleted', '0');
				$this->db->where('type', $type_name);
				$rm_result = $this->db->get('tbl_rm_type')->row();

				if (empty($rm_result)) {
					$rm_data = array(
						"type" => $type_name,
						"created_on" => date('Y-m-d H:i:s')
					);
					$this->db->insert('tbl_rm_type', $rm_data);
					$last_insert_id = $this->db->insert_id();
				}
			}
			$data = array(
				"rm_name" => $this->input->post('new_option'),
				"reorder_level" => $this->input->post('reorder_level'),
				"mfi" => $this->input->post('mfi'),
				"code" => $this->input->post('code'),
				"alias" => $this->input->post('alias'),
				"uom_id" => $this->input->post('uom'),
				"type_id" => $rm_result ? $rm_result->id : $last_insert_id,
				"make_id" => $this->input->post('make'),
				"created_on" => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_rm_master', $data);
			echo 0;
		} else {
			echo 1;
		}
	}


	public function get_all_production_bom_list($length, $start, $search)
	{
		$this->db->select('tbl_production_bom.*,tbl_mould_parts.article_name');
		$this->db->from('tbl_production_bom');
		$this->db->join('tbl_mould_parts', 'tbl_production_bom.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_bom.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('batch', $search);
			$this->db->or_like('weight', $search);
			$this->db->or_like('raw_material_one', $search);
			$this->db->or_like('raw_material_two', $search);
			$this->db->or_like('master_batch', $search);
			$this->db->or_like('other_rm', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_production_bom.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_all_bom_list_count($search)
	{
		$this->db->select('tbl_production_bom.*,tbl_mould_parts.article_name');
		$this->db->from('tbl_production_bom');
		$this->db->join('tbl_mould_parts', 'tbl_production_bom.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_bom.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('batch', $search);
			$this->db->or_like('weight', $search);
			$this->db->or_like('raw_material_one', $search);
			$this->db->or_like('raw_material_two', $search);
			$this->db->or_like('master_batch', $search);
			$this->db->or_like('other_rm', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function set_new_department()
	{
		if ($this->input->post('master_type') == 'department') {
			$this->db->where('is_deleted', '0');
			$this->db->where('department', $this->input->post('new_option'));
			$result = $this->db->get('tbl_machine_department');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"department" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_machine_department', $data);
				echo 0;
			} else {
				echo 1;
			}
		}
	}
	public function get_all_machine_department()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_machine_department');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_article_by_group()
	{
		$selected_article = $this->input->post('selected_article');
		$this->db->select('tbl_mould_parts.*');
		$this->db->where('tbl_mould_parts.group_of_article_id', $selected_article);
		$this->db->order_by('id', 'DESC');
		$this->db->group_by('tbl_mould_parts.article_name');
		$this->db->where('tbl_mould_parts.is_deleted', '0');
		// $this->db->where('tbl_mould_parts.status', '1');
		$result = $this->db->get('tbl_mould_parts');
		echo json_encode($result->result());
	}
	public function get_all_article_by_group_production()
	{
		$selected_article = $this->input->post('selected_article');
		$this->db->select('tbl_mould_parts.*');
		$this->db->where('tbl_mould_parts.group_of_article_id', $selected_article);
		$this->db->where("tbl_mould_parts.article_name NOT IN (SELECT article_name FROM tbl_mould_parts WHERE status = '0' AND is_deleted = '0')", NULL, FALSE);
		$this->db->order_by('id', 'DESC');
		$this->db->group_by('tbl_mould_parts.article_name');
		$this->db->where('tbl_mould_parts.is_deleted', '0');
		$this->db->where('tbl_mould_parts.status', '1');
		$result = $this->db->get('tbl_mould_parts');
		echo json_encode($result->result());
	}
	public function get_all_brand_by_party()
	{
		$this->db->select('tbl_brand_master.*');
		$this->db->where('tbl_brand_master.party_name_id', $this->input->post('party_id'));
		$this->db->order_by('id', 'DESC');
		$this->db->where('tbl_brand_master.is_deleted', '0');
		$result = $this->db->get('tbl_brand_master');

		echo json_encode($result->result());
	}
	public function get_selected_party_details()
	{
		$selected_id = $this->input->post('selected_party');
		$this->db->select('tbl_order_details.*,tbl_customers.party_name');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.party_id', $selected_id);
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_order_details');
		$this->db->where('tbl_order_details.is_deleted', '0');
		echo json_encode($result->result());
	}

	public function get_all_order_list($length, $start, $search)
	{
		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');

		$this->_restrict_customer_queries();

		if (!empty($search)) {
			$this->apply_search_filters($search);
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_order_details.order_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_order_details.order_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_order_details.order_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_order_details.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_order_details.order_status', $this->input->post('order_status_action'));
		}
		if ($this->input->post('division_action') != "") {
			$this->db->where('tbl_order_details.type_of_order', $this->input->post('division_action'));
		}

		$this->db->order_by('tbl_order_details.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(tbl_order_details.id) as total_count');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');

		$this->_restrict_customer_queries();

		if (!empty($search)) {
			$this->apply_search_filters($search);
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_order_details.order_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_order_details.order_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_order_details.order_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_order_details.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_order_details.order_status', $this->input->post('order_status_action'));
		}
		if ($this->input->post('division_action') != "") {
			$this->db->where('tbl_order_details.type_of_order', $this->input->post('division_action'));
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	private function apply_search_filters($search)
	{
		$search_lower = strtolower(trim($search));
		$formatted_date = false;

		if (preg_match('/^\d{2}-\d{2}-\d{2,4}$/', $search)) {
			$formatted_date = date('Y-m-d', strtotime($search));
		}
		$status_map = [
			'pending' => '1',
			'processed to account' => '2',
			'forwarded' => '2',
			'partially dispatched' => '3',
			'full dispatched' => '4',
			'order closed' => '5',
			'closed' => '5',
			'order cancelled' => '6',
			'close' => '6',
			'printing' => '7',
			'printing inprocess' => '7',
			'printing completed' => '8',
			'dispatch inprocess' => '9',
		];

		$type_map = [
			'household' => '1',
			'container' => '2',
			'both' => '3'
		];

		$ink_type_map = [
			'plain' => '1',
			'printing' => '2'
		];

		$status_value = false;
		foreach ($status_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$status_value = $value;
				break;
			}
		}

		$type_value = false;
		foreach ($type_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$type_value = $value;
				break;
			}
		}

		$ink_value = false;
		foreach ($ink_type_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$ink_value = $value;
				break;
			}
		}

		$this->db->group_start();
		$this->db->or_like('order_id', $search);
		$this->db->or_like('tbl_customers.party_name', $search);

		if ($ink_value !== false) {
			$this->db->or_like('ink_type', $ink_value);
		} else {
			$this->db->or_like('ink_type', $search);
		}

		if ($type_value !== false) {
			$this->db->or_like('type_of_order', $type_value);
		} else {
			$this->db->or_like('type_of_order', $search);
		}

		if ($formatted_date !== false) {
			$this->db->or_like('order_date', $formatted_date);
		} else {
			$this->db->or_like('order_date', $search);
		}

		if ($status_value !== false) {
			$this->db->or_like('order_status', $status_value);
		} else {
			$this->db->or_like('order_status', $search);
		}

		$this->db->group_end();
	}


	public function get_all_outward_order_list($length, $start, $search)
	{
		$bundleColumn = $this->db->field_exists('bundle_bag_qty', 'tbl_order_sub_details')
			? 'bundle_bag_qty'
			: 'order_quantity';

		$this->db->select('
			tbl_auto_task_list.*, 
			employee_user.first_name AS employee_name, 
			assign_user.first_name AS assigned_to_name, 
			tbl_krivisha_department.department AS d_name,
			tbl_customers.party_name,
			(
				SELECT tbl_transport_master.transport_name
				FROM tbl_outward_orders
				LEFT JOIN tbl_transport_master ON tbl_outward_orders.transport_id = tbl_transport_master.id
				WHERE tbl_outward_orders.order_id = tbl_auto_task_list.task_id
					AND tbl_outward_orders.is_deleted = \'0\'
				ORDER BY tbl_outward_orders.updated_on DESC, tbl_outward_orders.id DESC
				LIMIT 1
			) AS transport_name,
			(
				(SELECT COALESCE(SUM(CAST(order_quantity AS DECIMAL(10,2)) / COALESCE(NULLIF(CAST(tbl_production_bom.batch AS DECIMAL(10,2)), 0), 120)), 0)
				FROM tbl_order_sub_details
				LEFT JOIN tbl_production_bom ON tbl_order_sub_details.article_id = tbl_production_bom.article_id AND tbl_production_bom.is_deleted = \'0\'
				WHERE tbl_order_sub_details.order_id = tbl_auto_task_list.task_id
					AND tbl_order_sub_details.is_deleted = \'0\')
				+
				(SELECT COALESCE(SUM(CAST(order_quantity AS DECIMAL(10,2)) / COALESCE(NULLIF(CAST(tbl_production_bom.batch AS DECIMAL(10,2)), 0), 120)), 0)
				FROM tbl_order_container_details
				LEFT JOIN tbl_production_bom ON tbl_order_container_details.article_id = tbl_production_bom.article_id AND tbl_production_bom.is_deleted = \'0\'
				WHERE tbl_order_container_details.order_id = tbl_auto_task_list.task_id
					AND tbl_order_container_details.is_deleted = \'0\')
			) AS total_bundle,
			(
				SELECT tbl_outward_orders.created_on
				FROM tbl_outward_orders
				WHERE tbl_outward_orders.order_id = tbl_auto_task_list.task_id
					AND tbl_outward_orders.is_deleted = \'0\'
				ORDER BY tbl_outward_orders.updated_on DESC, tbl_outward_orders.id DESC
				LIMIT 1
			) AS dispatch_date,
			COALESCE(
				(
					SELECT tbl_auto_task_list_history.last_updated_date
					FROM tbl_auto_task_list_history
					WHERE tbl_auto_task_list_history.task_id = tbl_auto_task_list.id
						AND tbl_auto_task_list_history.details_of_task = \'Assigned to Logistics Department\'
						AND tbl_auto_task_list_history.is_deleted = \'0\'
					ORDER BY tbl_auto_task_list_history.id DESC
					LIMIT 1
				),
				tbl_auto_task_list.date
			) AS forwarded_to_logistics_date
		');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->join('tbl_order_details', 'tbl_auto_task_list.task_id = tbl_order_details.order_id', 'left');
		$this->db->join('user_data AS employee_user', 'COALESCE(tbl_order_details.sales_person_id, tbl_customers.attending_salesperson_id) = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');


		$this->db->where_in('tbl_auto_task_list.order_department', ['1', '2']);
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		$this->_restrict_customer_queries('tbl_auto_task_list.party_id');

		// Exclude orders that are Closed (5) or Cancelled (6) in the main order table
		$this->db->where("tbl_auto_task_list.task_id NOT IN (SELECT order_id FROM tbl_order_details WHERE order_status IN ('5','6') AND is_deleted = '0')", null, false);

		if ($this->input->post('final_status') != "1") {
			// Exclude fully dispatched orders (where all sub details or all container details are status '4')
			$this->db->where("
				NOT (
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) > 0
					AND
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) =
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status = '4')
				)
			", null, false);
			$this->db->where("
				NOT (
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) > 0
					AND
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) =
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status = '4')
				)
			", null, false);
		}
		$this->db->order_by('tbl_auto_task_list.id', 'DESC');

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_auto_task_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_auto_task_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_auto_task_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('final_status') == "1") {
			$this->db->where('tbl_auto_task_list.department_id', '25');
			$this->db->where('tbl_auto_task_list.order_status', '4');
		} else {
			$this->db->where('tbl_auto_task_list.order_department_status', '3');
			if ($this->input->post('order_status_action') == "") {
				$this->db->where_in('tbl_auto_task_list.order_status', ['3', '9']);
			}
		}
		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_auto_task_list.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('salesman_action') != "") {
			$this->db->where('COALESCE(tbl_order_details.sales_person_id, tbl_customers.attending_salesperson_id) =', $this->input->post('salesman_action'));
		}
		if ($this->input->post('order_id_action') != "") {
			$this->db->where('tbl_auto_task_list.task_id', $this->input->post('order_id_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			if ($this->input->post('order_status_action') == "9") {
				$this->db->where_in('tbl_auto_task_list.order_status', ['9', '8']);
			} else {
				$this->db->where('tbl_auto_task_list.order_status', $this->input->post('order_status_action'));
			}

		}
		if ($this->input->post('division_action') != "") {
			$this->db->where('tbl_auto_task_list.type_of_order', $this->input->post('division_action'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('tbl_auto_task_list.task_id', $search);
			$this->db->or_like('tbl_auto_task_list.task_status', $search);
			$this->db->or_like('tbl_auto_task_list.task_action', $search);
			$this->db->or_like('tbl_auto_task_list.details_of_task', $search);
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(tbl_auto_task_list.id) as total_count');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->join('tbl_order_details', 'tbl_auto_task_list.task_id = tbl_order_details.order_id', 'left');
		$this->db->join('user_data AS employee_user', 'COALESCE(tbl_order_details.sales_person_id, tbl_customers.attending_salesperson_id) = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');
		$this->db->where_in('tbl_auto_task_list.order_department', ['1', '2']);
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		$this->_restrict_customer_queries('tbl_auto_task_list.party_id');

		// Exclude orders that are Closed (5) or Cancelled (6) in the main order table
		$this->db->where("tbl_auto_task_list.task_id NOT IN (SELECT order_id FROM tbl_order_details WHERE order_status IN ('5','6') AND is_deleted = '0')", null, false);

		if ($this->input->post('final_status') != "1") {
			// Exclude fully dispatched orders (where all sub details or all container details are status '4')
			$this->db->where("
				NOT (
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) > 0
					AND
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) =
					(SELECT COUNT(id) FROM tbl_order_sub_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status = '4')
				)
			", null, false);
			$this->db->where("
				NOT (
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) > 0
					AND
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status IN ('0','1','3','4','9')) =
					(SELECT COUNT(id) FROM tbl_order_container_details WHERE order_id = tbl_auto_task_list.task_id AND is_deleted = '0' AND order_status = '4')
				)
			", null, false);
		}


		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_auto_task_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_auto_task_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_auto_task_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('final_status') == "1") {
			$this->db->where('tbl_auto_task_list.department_id', '25');
			$this->db->where('tbl_auto_task_list.order_status', '4');
		} else {
			$this->db->where('tbl_auto_task_list.order_department_status', '3');
			if ($this->input->post('order_status_action') == "") {
				$this->db->where_in('tbl_auto_task_list.order_status', ['3', '9']);
			}
		}
		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_auto_task_list.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('salesman_action') != "") {
			$this->db->where('COALESCE(tbl_order_details.sales_person_id, tbl_customers.attending_salesperson_id) =', $this->input->post('salesman_action'));
		}
		if ($this->input->post('order_id_action') != "") {
			$this->db->where('tbl_auto_task_list.task_id', $this->input->post('order_id_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			if ($this->input->post('order_status_action') == "9") {
				$this->db->where_in('tbl_auto_task_list.order_status', ['9', '8']);
			} else {
				$this->db->where('tbl_auto_task_list.order_status', $this->input->post('order_status_action'));
			}

		}
		if ($this->input->post('division_action') != "") {
			$this->db->where('tbl_auto_task_list.type_of_order', $this->input->post('division_action'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('tbl_auto_task_list.task_id', $search);
			$this->db->or_like('tbl_auto_task_list.task_status', $search);
			$this->db->or_like('tbl_auto_task_list.task_action', $search);
			$this->db->or_like('tbl_auto_task_list.details_of_task', $search);
			$this->db->group_end();
		}
		
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_outward_order_log_details($length, $start, $search)
	{
		$search_date = $this->input->post('search_date');
		$transport_action = $this->input->post('transport_action');
		$payment_status_action = $this->input->post('payment_status_action');
		$division_action = $this->input->post('division_action');

		$this->db->select('
			tbl_outward_orders.*, tbl_location_master.city,tbl_customers.party_name,tbl_transport_master.transport_name
		');
		$this->db->from('tbl_outward_orders');
		$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
		$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_outward_orders.order_id', $this->input->post('order_id'));
		$this->db->where('tbl_outward_orders.is_deleted', '0');
		$this->db->order_by('tbl_outward_orders.updated_on', 'DESC');
		if (!empty($search_date)) {
			$exp = explode("to", $search_date);
			if (isset($exp[0]) && isset($exp[1])) {
				$start_date = date("Y-m-d", strtotime(trim($exp[0])));
				$end_date = date("Y-m-d", strtotime(trim($exp[1])));
				$this->db->where('DATE(tbl_outward_orders.created_on) >=', $start_date);
				$this->db->where('DATE(tbl_outward_orders.created_on) <=', $end_date);
			} else if (isset($exp[0])) {
				$start_date = date("Y-m-d", strtotime(trim($exp[0])));
				$this->db->where('DATE(tbl_outward_orders.created_on)', $start_date);
			}
		}
		if ($transport_action) {
			$this->db->where('tbl_outward_orders.transport_id', $transport_action);
		}
		if ($payment_status_action) {
			$this->db->where('tbl_outward_orders.freight_status', $payment_status_action);
		}
		if ($division_action) {
			$this->db->where('tbl_outward_orders.division', $division_action);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('order_id', $search);
			$this->db->or_like('dc_no', $search);
			$this->db->or_like('invoice_no', $search);
			$this->db->or_like('invoice_value', $search);
			$this->db->or_like('freight_amount', $search);
			$this->db->or_like('vehicle', $search);
			$this->db->or_like('driver_name', $search);
			$this->db->or_like('driver_mobile', $search);
			$this->db->or_like('remark', $search);
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(tbl_outward_orders.id) as total_count');
		$this->db->from('tbl_outward_orders');
		$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
		$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_outward_orders.order_id', $this->input->post('order_id'));
		$this->db->where('tbl_outward_orders.is_deleted', '0');
		$this->db->order_by('tbl_outward_orders.updated_on', 'DESC');
		if (!empty($search_date)) {
			$exp = explode("to", $search_date);
			if (isset($exp[0]) && isset($exp[1])) {
				$start_date = date("Y-m-d", strtotime(trim($exp[0])));
				$end_date = date("Y-m-d", strtotime(trim($exp[1])));
				$this->db->where('DATE(tbl_outward_orders.created_on) >=', $start_date);
				$this->db->where('DATE(tbl_outward_orders.created_on) <=', $end_date);
			} else if (isset($exp[0])) {
				$start_date = date("Y-m-d", strtotime(trim($exp[0])));
				$this->db->where('DATE(tbl_outward_orders.created_on)', $start_date);
			}
		}
		if ($transport_action) {
			$this->db->where('tbl_outward_orders.transport_id', $transport_action);
		}
		if ($payment_status_action) {
			$this->db->where('tbl_outward_orders.freight_status', $payment_status_action);
		}
		if ($division_action) {
			$this->db->where('tbl_outward_orders.division', $division_action);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('order_id', $search);
			$this->db->or_like('dc_no', $search);
			$this->db->or_like('invoice_no', $search);
			$this->db->or_like('invoice_value', $search);
			$this->db->or_like('freight_amount', $search);
			$this->db->or_like('vehicle', $search);
			$this->db->or_like('driver_name', $search);
			$this->db->or_like('driver_mobile', $search);
			$this->db->or_like('remark', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_printing_order_list($length, $start, $search)
	{
		$this->db->select('tbl_order_sub_details.*,tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name,tbl_customers.party_name');
		$this->db->from('tbl_order_sub_details');
		$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_order_sub_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		// if ($this->db->where('tbl_order_sub_details.order_status', '0')) {
		// 	$this->db->where_in('tbl_order_sub_details.order_department_status', ['2','3']);
		// }else{
		// 	$this->db->where('tbl_order_sub_details.order_department_status', '2');
		// }
		$this->db->where_in('tbl_order_sub_details.order_status', ['0', '5', '7']);
		$this->db->where_in('tbl_order_sub_details.order_department_status', ['2', '3']);
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_order_sub_details.order_date) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_order_sub_details.order_date) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_order_sub_details.order_date)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_order_sub_details.brand_type_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('article_action') != "") {
			$this->db->where('tbl_order_sub_details.article_id', $this->input->post('article_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where_in('tbl_order_sub_details.order_status', ['0', '7']);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_order_sub_details.order_id', $search);
			$this->db->or_like('tbl_order_sub_details.order_quantity', $search);
			$this->db->or_like('tbl_order_sub_details.approved_qty', $search);
			$this->db->or_like('tbl_order_sub_details.remark', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_group_of_article.group_of_article', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_order_sub_details.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(tbl_order_sub_details.id) as total_count');
		$this->db->from('tbl_order_sub_details');
		$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_order_sub_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		$this->db->where_in('tbl_order_sub_details.order_status', ['0', '5', '7']);
		$this->db->where_in('tbl_order_sub_details.order_department_status', ['2', '3']);

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_order_sub_details.order_date) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_order_sub_details.order_date) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_order_sub_details.order_date)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_order_sub_details.brand_type_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('article_action') != "") {
			$this->db->where('tbl_order_sub_details.article_id', $this->input->post('article_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where_in('tbl_order_sub_details.order_status', ['0', '7']);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_order_sub_details.order_id', $search);
			$this->db->or_like('tbl_order_sub_details.order_quantity', $search);
			$this->db->or_like('tbl_order_sub_details.approved_qty', $search);
			$this->db->or_like('tbl_order_sub_details.remark', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_group_of_article.group_of_article', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_sub_order_details()
	{
		$order_type = $this->input->post('order_type');
		$order_id = $this->input->post('order_id');
		$result = null;

		if ($order_type == 'Container') {
			$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
			$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
			$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
			$this->db->where('tbl_order_sub_details.order_id', $order_id);
			$this->db->where('tbl_order_sub_details.is_deleted', '0');
			$this->db->order_by('tbl_order_sub_details.id', 'ASC');
			$result = $this->db->get('tbl_order_sub_details');
		} else {
			$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
			$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
			$this->db->where('tbl_order_container_details.order_id', $order_id);
			$this->db->where('tbl_order_container_details.is_deleted', '0');
			$this->db->order_by('tbl_order_container_details.id', 'ASC');
			$result = $this->db->get('tbl_order_container_details');
		}

		if ($result && $result->num_rows() > 0) {
			$details = $result->result_array();
			$article_batch_cache = [];

			foreach ($details as &$item) {
				$dispatch_row = null;
				$brand_type_id = isset($item['brand_type_id']) ? intval($item['brand_type_id']) : 0;

				if ($brand_type_id > 0) {
					$this->db->select('GROUP_CONCAT(DISTINCT DATE_FORMAT(created_on, "%d-%m-%Y") ORDER BY created_on ASC SEPARATOR ", ") as dispatch_dates', false);
					$this->db->from('tbl_dispatch_order_data');
					$this->db->where('is_deleted', '0');
					$this->db->where('order_id', $item['order_id']);
					$this->db->where('article_id', $item['article_id']);
					$this->db->where('brand_type_id', $brand_type_id);
					$dispatch_row = $this->db->get()->row();
				}

				// Fallback for legacy/partial data where dispatch rows are saved without brand_type_id.
				if (empty($dispatch_row) || empty($dispatch_row->dispatch_dates)) {
					$this->db->select('GROUP_CONCAT(DISTINCT DATE_FORMAT(created_on, "%d-%m-%Y") ORDER BY created_on ASC SEPARATOR ", ") as dispatch_dates', false);
					$this->db->from('tbl_dispatch_order_data');
					$this->db->where('is_deleted', '0');
					$this->db->where('order_id', $item['order_id']);
					$this->db->where('article_id', $item['article_id']);
					$dispatch_row = $this->db->get()->row();
				}

				$item['dispatch_dates'] = (!empty($dispatch_row) && !empty($dispatch_row->dispatch_dates)) ? $dispatch_row->dispatch_dates : '';

				$bundle_qty = isset($item['bundle_bag_qty']) ? trim((string)$item['bundle_bag_qty']) : '';
				if ($bundle_qty === '' || floatval($bundle_qty) <= 0) {
					$qty = floatval($item['order_quantity'] ?? 0);
					$article_id = intval($item['article_id'] ?? 0);
					$batch_size = 0;

					if ($article_id > 0) {
						if (array_key_exists($article_id, $article_batch_cache)) {
							$batch_size = $article_batch_cache[$article_id];
						} else {
							$bom = $this->get_artical_bom($article_id);
							$batch_size = (!empty($bom) && !empty($bom->batch)) ? floatval($bom->batch) : 0;
							$article_batch_cache[$article_id] = $batch_size;
						}
					}

					if ($qty > 0 && $batch_size > 0) {
						$item['bundle_bag_qty'] = rtrim(rtrim(number_format($qty / $batch_size, 2, '.', ''), '0'), '.');
					} else {
						$qty = floatval($item['order_quantity'] ?? 0);
						if ($qty > 0) {
							$item['bundle_bag_qty'] = rtrim(rtrim(number_format($qty / 120, 2, '.', ''), '0'), '.');
						} else {
							$item['bundle_bag_qty'] = '';
						}
					}
				}
			}

			echo json_encode($details);
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}
	public function get_extra_charges_details()
	{
		$database_inward_id = $this->input->post('database_inward_id');

		$this->db->select('tbl_inward_extra_charges.*, tbl_extra_payment_master.extra_payment_option');
		$this->db->join('tbl_extra_payment_master', 'tbl_inward_extra_charges.extra_payment_option_id = tbl_extra_payment_master.id', 'left');
		$this->db->where('tbl_inward_extra_charges.database_inward_id', $database_inward_id);
		$this->db->where('tbl_inward_extra_charges.is_deleted', '0');
		// $this->db->where('tbl_inward_extra_charges.inward_for', '0');
		$this->db->order_by('tbl_inward_extra_charges.id', 'ASC');
		$result = $this->db->get('tbl_inward_extra_charges');

		if ($result && $result->num_rows() > 0) {
			echo json_encode($result->result_array());
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}


	public function get_employees_by_department()
	{
		$department_id = $this->input->post('department_id');
		$this->db->select('user_data.*');
		$this->db->where('user_data.is_deleted', '0');
		// Use FIND_IN_SET to support multi-department CSV values (e.g. "13,18")
		$this->db->where("FIND_IN_SET('" . (int)$department_id . "', user_data.department_id) > 0", NULL, FALSE);
		$this->db->order_by('user_data.id', 'ASC');
		$result = $this->db->get('user_data');
		echo json_encode($result->result_array());
	}
	public function get_employee_by_department($department_id)
	{
		$this->db->select('user_data.*');
		$this->db->where('user_data.is_deleted', '0');
		// Use FIND_IN_SET to support multi-department CSV values
		$this->db->where("FIND_IN_SET('" . (int)$department_id . "', user_data.department_id) > 0", NULL, FALSE);
		$this->db->order_by('user_data.id', 'ASC');
		$result = $this->db->get('user_data');
		return $result->result();
	}

	// public function get_party_order_details()
	// {
	// 	$party_id = $this->input->post('selectedValue');
	// 	$this->db->select('tbl_order_details.*, tbl_customers.party_name');
	// 	$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
	// 	$this->db->where('tbl_order_details.is_deleted', '0');
	// 	$this->db->where('tbl_order_details.party_id', $party_id);
	// 	$this->db->order_by('tbl_order_details.id', 'ASC');
	// 	$result = $this->db->get('tbl_order_details');
	// 	echo json_encode($result->result_array());
	// }
	public function get_party_order_details()
	{
		$party_id = $this->input->post('selectedValue');
		$status_filter = trim((string)$this->input->post('status_filter'));

		// DataTables parameters
		$order_post = $this->input->post('order');
		$order_column_index = (is_array($order_post) && isset($order_post[0]['column'])) ? $order_post[0]['column'] : null;
		$order_dir = (is_array($order_post) && isset($order_post[0]['dir'])) ? $order_post[0]['dir'] : 'DESC';
		$columns = array(
			0 => 'tbl_order_details.id',
			1 => 'tbl_customers.party_name',
			2 => 'tbl_order_details.order_no',
			3 => 'tbl_order_details.order_date',
			// add actual columns from your table in the correct order
		);

		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');
		$this->db->where('tbl_order_details.party_id', $party_id);

		if (isset($columns[$order_column_index])) {
			$this->db->order_by($columns[$order_column_index], $order_dir);
		} else {
			$this->db->order_by('tbl_order_details.id', 'DESC');
		}

		$result = $this->db->get('tbl_order_details')->result_array();
		$filtered_result = [];
		// Loop through each order & update status
		foreach ($result as &$row) {
			$order_status = $this->Admin_model->get_outward_order_status($row['order_id']);
			$auto_task_order = $this->db->get_where('tbl_auto_task_list', ['task_id' => $row['order_id']])->row();
			$this->db->select('DATE_FORMAT(MAX(created_on), "%d-%m-%Y") as dispatch_date', false);
			$this->db->where('is_deleted', '0');
			$this->db->where('order_id', $row['order_id']);
			$dispatch_row = $this->db->get('tbl_outward_orders')->row();
			$row['dispatch_date'] = (!empty($dispatch_row) && !empty($dispatch_row->dispatch_date)) ? $dispatch_row->dispatch_date : '';

			$order_date_obj = DateTime::createFromFormat('Y-m-d', $row['order_date']);
			$end_date_obj = null;
			if (!empty($dispatch_row) && !empty($dispatch_row->dispatch_date)) {
				$end_date_obj = DateTime::createFromFormat('d-m-Y', $dispatch_row->dispatch_date);
			} else {
				$end_date_obj = new DateTime();
			}
			if ($order_date_obj && $end_date_obj) {
				$days = (int) $order_date_obj->diff($end_date_obj)->days;
				$row['delay_days'] = (string) $days;
			} else {
				$row['delay_days'] = '';
			}
			// if ($row['order_status'] == '1') {
			// 	$order_status = 'Pending';
			// } else if ($row['order_status'] == '2') {
			// 	$order_status = 'Processed to Account';
			// } else if ($row['order_status'] == '3') {
			if ($row['order_status'] == '3') {
				$order_status = 'Partially Dispatched';
			} else if ($row['order_status'] == '4') {
				$order_status = 'Full Dispatched';
			} else if ($row['order_status'] == '5') {
				$order_status = 'Order Closed';
			} else if ($row['order_status'] == '6') {
				$order_status = 'Order Cancelled';
			} else if ($row['order_status'] == '7') {
				$order_status = 'Printing Inprocess';
			} else if ($row['order_status'] == '8') {
				$order_status = 'Printing Completed';
			} else if ($row['order_status'] == '9') {
				$order_status = 'Dispatch Inprocess';
			} else if ($order_status == 'Pending') {
				if ($row['order_status'] == '3') {
					$order_status = 'Partially Dispatched';
				} else if ($row['order_status'] == '2') {
					if ($auto_task_order && $auto_task_order->order_department_status == '3') {
						$order_status = 'Dispatch Inprocess';
					} else if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
						$order_status = 'Printing Inprocess';
					} else {
						$order_status = 'Processed to Account';
					}
				} else if ($row['order_status'] == '6') {
					$order_status = 'Order Cancelled';
				} else if ($row['order_status'] == '5') {
					$order_status = 'Order Closed';
				} else {
					$order_status = 'Pending';
				}
			} else if ($order_status == 'Processed to Account') {
				if ($row['order_status'] == '5') {
					$order_status = 'Order Closed';
				} else if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
					$order_status = 'Printing Inprocess';
				} else {
					$order_status = 'Processed to Account';
				}
			} else if ($order_status == 'Partially Dispatched') {
				if ($row['order_status'] == '5') {
					$order_status = 'Order Closed';
				} else {
					$order_status = 'Partially Dispatched';
				}
			} else if ($order_status == 'Dispatch Inprocess') {
				if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
					$order_status = 'Printing Inprocess';
				} else if ($auto_task_order && $auto_task_order->order_status == '2' && $auto_task_order->order_department_status == '2') {
					$order_status = 'Printing Completed';
				} else {
					$order_status = 'Dispatch Inprocess';
				}
			}

			$row['order_status_text'] = $order_status;

			if ($status_filter === '' || strcasecmp($row['order_status_text'], $status_filter) === 0) {
				$filtered_result[] = $row;
			}
		}

		echo json_encode($filtered_result);
	}

	public function get_sub_order_task_details()
	{
		$order_type = $this->input->post('order_type');
		$order_id = $this->input->post('order_id');
		$result = null;

		if ($order_type == 'Create Order') {
			$this->db->where('order_id', $order_id);
			$this->db->where('is_deleted', '0');
			$check_sub = $this->db->get('tbl_order_sub_details');
			if ($check_sub->num_rows() > 0) {
				$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
				$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
				$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
				$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
				$this->db->where('tbl_order_sub_details.order_id', $order_id);
				$this->db->where('tbl_order_sub_details.is_deleted', '0');
				$this->db->order_by('tbl_order_sub_details.id', 'ASC');
				$result = $this->db->get('tbl_order_sub_details');
			} else {
				$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
				$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
				$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
				$this->db->where('tbl_order_container_details.order_id', $order_id);
				$this->db->where('tbl_order_container_details.is_deleted', '0');
				$this->db->order_by('tbl_order_container_details.id', 'ASC');
				$result = $this->db->get('tbl_order_container_details');
			}
		} elseif ($order_type == 'Maintenance') {
			$this->db->select('tbl_maintenance_production.*, GROUP_CONCAT(tbl_maintaince_problems.problem ORDER BY tbl_maintaince_problems.problem SEPARATOR ", ") as problems, tbl_plant_master.plant_name');
			$this->db->join('tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
			$this->db->join('tbl_plant_master', 'tbl_maintenance_production.plant_id = tbl_plant_master.id', 'left');
			$this->db->where('tbl_maintenance_production.mwo_code', $order_id);
			$this->db->where('tbl_maintenance_production.is_deleted', '0');
			$result = $this->db->get('tbl_maintenance_production');
		} else {
			$this->db->select('
				tbl_production_schedules.*,
				tbl_plant_master.plant_name,
				tbl_machine_master.machine_name,
				GROUP_CONCAT(DISTINCT rm.rm_name ORDER BY rm.rm_name ASC) AS raw_material_names,
				GROUP_CONCAT(DISTINCT mb.name ORDER BY mb.name ASC) AS master_batch_names
			');
			$this->db->from('tbl_production_schedules');
			$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, tbl_production_schedules.raw_materials)', 'left');
			$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, tbl_production_schedules.color_id)', 'left');
			$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
			$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');
			$this->db->where('tbl_production_schedules.is_deleted', '0');
			$this->db->where('tbl_production_schedules.order_id', $order_id);
			$this->db->group_by('tbl_production_schedules.id'); // âœ… This is important
			$result = $this->db->get();
		}
		if ($result && $result->num_rows() > 0) {
			echo json_encode($result->result_array());
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}
	public function get_outward_sub_order_details()
	{
		$order_id = $this->input->post('order_id');
		$result = null;
		$article_batch_cache = [];

		$this->db->where('order_id', $order_id);
		$this->db->where('is_deleted', '0');
		$check_sub = $this->db->get('tbl_order_sub_details');
		if ($check_sub->num_rows() > 0) {
			$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
			$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
			$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
			$this->db->where('tbl_order_sub_details.order_id', $order_id);
			$this->db->where_in('tbl_order_sub_details.order_status', ['1', '3', '4', '9']);
			$this->db->where('tbl_order_sub_details.is_deleted', '0');
			$this->db->order_by('tbl_order_sub_details.id', 'ASC');
			$result = $this->db->get('tbl_order_sub_details');
		} else {
			$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
			$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
			$this->db->where('tbl_order_container_details.order_id', $order_id);
			$this->db->where('tbl_order_container_details.order_status !=', '2');
			$this->db->where('tbl_order_container_details.is_deleted', '0');
			$this->db->order_by('tbl_order_container_details.id', 'ASC');
			$result = $this->db->get('tbl_order_container_details');
		}

		if ($result && $result->num_rows() > 0) {
			$details = $result->result_array();

			foreach ($details as &$item) {
				$bundle_qty = isset($item['bundle_bag_qty']) ? trim((string)$item['bundle_bag_qty']) : '';
				if ($bundle_qty === '' || floatval($bundle_qty) <= 0) {
					$qty = floatval($item['order_quantity'] ?? 0);
					$article_id = intval($item['article_id'] ?? 0);
					$batch_size = 0;

					if ($article_id > 0) {
						if (array_key_exists($article_id, $article_batch_cache)) {
							$batch_size = $article_batch_cache[$article_id];
						} else {
							$bom = $this->get_artical_bom($article_id);
							$batch_size = (!empty($bom) && !empty($bom->batch)) ? floatval($bom->batch) : 0;
							$article_batch_cache[$article_id] = $batch_size;
						}
					}

					if ($qty > 0 && $batch_size > 0) {
						$item['bundle_bag_qty'] = rtrim(rtrim(number_format($qty / $batch_size, 2, '.', ''), '0'), '.');
					} else {
						$item['bundle_bag_qty'] = '';
					}
				}
			}

			echo json_encode($details);
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}
	public function get_outward_dispatch_details()
	{
		$dispatch_id = $this->input->post('dispatch_id');
		$details = $this->get_outward_dispatch_details_array($dispatch_id);
		if (!empty($details) && !isset($details['error'])) {
			echo json_encode($details);
		} else {
			echo json_encode($details ?: ['error' => 'No records found']);
		}
	}

	public function get_outward_dispatch_details_array($dispatch_id)
	{
		$article_batch_cache = [];
		$has_ir = $this->db->field_exists('impression_rate', 'tbl_dispatch_order_data');
		$ir_col = $has_ir ? 'tbl_dispatch_order_data.impression_rate' : '0';
		$this->db->select('tbl_dispatch_order_data.*, tbl_brand_master.brand_name, tbl_mould_parts.article_name, ' . $ir_col . ' as unit_price');
		$this->db->from('tbl_dispatch_order_data');
		$this->db->join('tbl_mould_parts', 'tbl_dispatch_order_data.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_dispatch_order_data.brand_type_id = tbl_brand_master.id', 'left');
		$this->db->where('tbl_dispatch_order_data.dispatch_id', $dispatch_id);
		$this->db->where('tbl_dispatch_order_data.is_deleted', '0');
		$result = $this->db->get();
		$details = $result->result_array();
		if (!empty($details)) {
			foreach ($details as &$item) {
				$bundle_qty = isset($item['bundle_bag_qty']) ? trim((string)$item['bundle_bag_qty']) : '';
				if ($bundle_qty === '' || floatval($bundle_qty) <= 0) {
					$qty = floatval($item['dispatch_quantity'] ?? 0);
					$article_id = intval($item['article_id'] ?? 0);
					$batch_size = 0;
					if ($article_id > 0) {
						if (array_key_exists($article_id, $article_batch_cache)) {
							$batch_size = $article_batch_cache[$article_id];
						} else {
							$bom = $this->get_artical_bom($article_id);
							$batch_size = (!empty($bom) && !empty($bom->batch)) ? floatval($bom->batch) : 0;
							$article_batch_cache[$article_id] = $batch_size;
						}
					}

					if ($qty > 0 && $batch_size > 0) {
						$item['bundle_bag_qty'] = rtrim(rtrim(number_format($qty / $batch_size, 2, '.', ''), '0'), '.');
					} else {
						$item['bundle_bag_qty'] = '0';
					}
				}
			}
			return $details;
		}
		return ['error' => 'No records found'];
	}
	public function get_sub_task_details()
	{
		$order_id = $this->input->post('order_id');

		$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
		$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
		$this->db->where('tbl_order_sub_details.order_id', $order_id);
		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		$this->db->order_by('tbl_order_sub_details.id', 'ASC');
		$query = $this->db->get('tbl_order_sub_details');
		//echo "<pre>";print_r($query);exit;
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
			$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
			$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
			$this->db->where('tbl_order_container_details.order_id', $order_id);
			$this->db->where('tbl_order_container_details.is_deleted', '0');
			$this->db->order_by('tbl_order_container_details.id', 'ASC');
			$query = $this->db->get('tbl_order_container_details');

			return $query->result();
		}
	}


	public function set_order_status()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->input->post('id'));
		$result = $this->db->get('tbl_order_details');
		$result = $result->row();

		if ($result) {
			$this->db->select('id');
			$this->db->from('tbl_krivisha_department');
			$this->db->where('department', 'ACCOUNTS');
			$query = $this->db->get()->row();

			$department_id = $query ? $query->id : null;
			$process_data = array(
				'task_id' => $result->order_id,
				'order_department' => '1',
				'employee_id' => $this->session->userdata('id'),
				'department_id' => $department_id,
				'party_id' => $result->party_id,
				'type_of_order' => $result->type_of_order,
				'date' => date('Y-m-d'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_auto_task_list', $process_data);
			$last_insert_id = $this->db->insert_id();
			$this->db->select('*');
			$this->db->from('tbl_order_details');
			$this->db->where('order_id', $result->order_id);
			$order_result = $this->db->get()->row();
			if ($order_result) {
				$log_data = array(
					'task_id' => $last_insert_id,
					'task_status' => '1',
					'party_id' => $order_result->party_id,
					'type_of_order' => $order_result->type_of_order,
					'details_of_task' => 'Order Created',
					'last_updated_date' => date('Y-m-d'),
					'last_updated_by' => $this->session->userdata('id'),
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => $order_result->created_on,
					'updated_on' => $order_result->updated_on,
				);
				$this->db->insert('tbl_auto_task_list_history', $log_data);
			}
			$history_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'task_action' => '1',
				'department_id' => $department_id,
				'details_of_task' => 'Assigned to Accounts Department',
				'party_id' => $result->party_id,
				'type_of_order' => $result->type_of_order,
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $this->session->userdata('id'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);

			// Notification Work to order send account department
			$title = 'Order Update';
			$description = 'Order Created ' . $result->order_id . ' And Assigned to Accounts Department by ' .
				$this->session->userdata('name');
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 25]; // 11 = Accounts Department, 25 = Admin Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'order_id' => $result->order_id,
				'plant_id' => $this->input->post('plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);

			$this->send_task_notification_by_token(50, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));
		}
		if (!empty($result)) {
			$data = array(
				"order_status" => '2'
			);
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_order_details', $data);

			$newdata = array(
				"order_status" => '5'
			);

			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_sub_details', $newdata);
			echo 1;
		} else {
			echo 0;
		}
	}

	public function set_order_status_logistics()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->input->post('id'));
		$result = $this->db->get('tbl_order_details')->row();

		if ($result) {
			$this->db->select('id');
			$this->db->from('tbl_krivisha_department');
			$this->db->where('department', 'LOGISTICS');
			$query = $this->db->get()->row();

			$department_id = $query ? $query->id : 11;
			
			// Update main order status to '9' (Dispatch Inprocess)
			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_details', array('order_status' => '9', 'updated_on' => date('Y-m-d H:i:s')));

			// Update sub details status
			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_sub_details', array('order_status' => '9', 'order_department_status' => '3'));

			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_container_details', array('order_status' => '9'));

			// Check if task already exists in tbl_auto_task_list
			$this->db->where('task_id', $result->order_id);
			$this->db->where('is_deleted', '0');
			$existing_task = $this->db->get('tbl_auto_task_list')->row();

			if ($existing_task) {
				$task_data = array(
					'department_id' => $department_id,
					'order_department_status' => '3',
					'order_status' => '9',
					'last_updated_date' => date('Y-m-d'),
					'updated_on' => date('Y-m-d H:i:s')
				);
				$this->db->where('id', $existing_task->id);
				$this->db->update('tbl_auto_task_list', $task_data);
				$task_id = $existing_task->id;
			} else {
				$process_data = array(
					'task_id' => $result->order_id,
					'order_department' => '1',
					'employee_id' => $this->session->userdata('id'),
					'department_id' => $department_id,
					'party_id' => $result->party_id,
					'type_of_order' => $result->type_of_order,
					'date' => date('Y-m-d'),
					'order_department_status' => '3',
					'order_status' => '9',
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => date('Y-m-d H:i:s'),
					'updated_on' => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_auto_task_list', $process_data);
				$task_id = $this->db->insert_id();
			}

			// Insert history
			$history_data = array(
				'task_id' => $task_id,
				'task_status' => '1',
				'task_action' => '1',
				'department_id' => $department_id,
				'details_of_task' => 'Assigned to Logistics Department',
				'party_id' => $result->party_id,
				'type_of_order' => $result->type_of_order,
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $this->session->userdata('id'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);

			// Notification
			$title = 'Order Update';
			$description = 'Order ' . $result->order_id . ' Assigned to Logistics Department by ' . $this->session->userdata('name');
			$landing_page = 'outward_order_list';
			$notification_according = '1'; // according department
			$departments = [11, 25]; // 11 = Logistics / Accounts, 25 = Admin
			$departments_str = implode(',', $departments);
			
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'order_id' => $result->order_id,
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);

			$this->send_task_notification_by_token(50, $title, $description, $landing_page, $notification_according, $this->session->userdata("assign_plant_id"));

			echo json_encode(array('status' => '1', 'message' => 'Processed successfully'));
		} else {
			echo json_encode(array('status' => '2', 'message' => 'Order not found'));
		}
	}

	public function set_order_status_printing()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->input->post('id'));
		$result = $this->db->get('tbl_order_details')->row();

		if ($result) {
			$this->db->select('id');
			$this->db->from('tbl_krivisha_department');
			$this->db->where('department', 'PRINTING');
			$query = $this->db->get()->row();

			$department_id = $query ? $query->id : 10;
			
			// Update main order status to '7' (Printing Inprocess)
			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_details', array('order_status' => '7', 'updated_on' => date('Y-m-d H:i:s')));

			// Update sub details status
			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_sub_details', array('order_status' => '7', 'order_department_status' => '2'));

			$this->db->where('order_id', $result->order_id);
			$this->db->update('tbl_order_container_details', array('order_status' => '7'));

			// Check if task already exists in tbl_auto_task_list
			$this->db->where('task_id', $result->order_id);
			$this->db->where('is_deleted', '0');
			$existing_task = $this->db->get('tbl_auto_task_list')->row();

			if ($existing_task) {
				$task_data = array(
					'department_id' => $department_id,
					'order_department_status' => '2',
					'order_status' => '7',
					'last_updated_date' => date('Y-m-d'),
					'updated_on' => date('Y-m-d H:i:s')
				);
				$this->db->where('id', $existing_task->id);
				$this->db->update('tbl_auto_task_list', $task_data);
				$task_id = $existing_task->id;
			} else {
				$process_data = array(
					'task_id' => $result->order_id,
					'order_department' => '1',
					'employee_id' => $this->session->userdata('id'),
					'department_id' => $department_id,
					'party_id' => $result->party_id,
					'type_of_order' => $result->type_of_order,
					'date' => date('Y-m-d'),
					'order_department_status' => '2',
					'order_status' => '7',
					'plant_id' => $this->session->userdata("assign_plant_id"),
					'created_on' => date('Y-m-d H:i:s'),
					'updated_on' => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_auto_task_list', $process_data);
				$task_id = $this->db->insert_id();
			}

			// Insert history
			$history_data = array(
				'task_id' => $task_id,
				'task_status' => '1',
				'task_action' => '1',
				'department_id' => $department_id,
				'details_of_task' => 'Assigned to Printing Department',
				'party_id' => $result->party_id,
				'type_of_order' => $result->type_of_order,
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $this->session->userdata('id'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);

			// Notification
			$title = 'Order Update';
			$description = 'Order ' . $result->order_id . ' Assigned to Printing Department by ' . $this->session->userdata('name');
			$landing_page = 'printing_order_list';
			$notification_according = '1'; // according department
			$departments = [$department_id, 25]; // Printing Department, Admin
			$departments_str = implode(',', $departments);
			
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'order_id' => $result->order_id,
				'plant_id' => $this->session->userdata("assign_plant_id"),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);

			$this->send_task_notification_by_token(50, $title, $description, $landing_page, $notification_according, $this->session->userdata("assign_plant_id"));

			echo json_encode(array('status' => '1', 'message' => 'Processed successfully'));
		} else {
			echo json_encode(array('status' => '2', 'message' => 'Order not found'));
		}
	}

	public function get_task_type_of_machine()
	{
		$id = $this->input->post('sub_type_id');
		$type = $this->input->post('type');
		if ($type == '1') {
			$this->db->where('tbl_machine_master.is_deleted', '0');
			$this->db->where('id', $id);
			$result = $this->db->get('tbl_machine_master');
			$result = $result->row();
		} else {
			$this->db->where('tbl_machine_master.is_deleted', '0');
			$this->db->where('tbl_machine_master.department_type', '1');
			$this->db->where('id', $id);
			$result = $this->db->get('tbl_machine_master');
			$result = $result->row();
		}
		echo json_encode(['machine_name' => $result->machine_name]);
	}
	public function get_task_type_of_article()
	{
		$id = $this->input->post('sub_type_id');
		$this->db->where('tbl_mould_parts.is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_mould_parts');
		$result = $result->row();
		echo json_encode(['article_name' => $result->article_name]);
	}
	public function get_task_type_of_plant()
	{
		$id = $this->input->post('sub_type_id');
		$this->db->where('tbl_plant_master.is_deleted', '0');
		$this->db->where('id', $id);
		$result = $this->db->get('tbl_plant_master');
		$result = $result->row();
		echo json_encode(['plant_name' => $result->plant_name]);
	}
	// public function get_all_manual_task_list($length, $start, $search)
	// {
	// 	$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
	// 	$this->db->from('tbl_manual_task');
	// 	$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
	// 	$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
	// 	$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
	// 	$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
	// 	$this->db->where('tbl_manual_task.is_deleted', '0');
	// 	if ($this->session->userdata('assign_department_id') != '25') {
	// 		$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
	// 	}
	// 	if ($this->input->post('search_date') != "") {
	// 		$exp = explode("to", $this->input->post('search_date'));

	// 		if (isset($exp[0]) && isset($exp[1])) {
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
	// 		} else if (isset($exp[0]) && !isset($exp[1])) {
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
	// 		}
	// 	}

	// 	if ($this->session->userdata('assign_department_id') != '25') {
	// 		$this->db->where('tbl_manual_task.department_id', $this->session->userdata('assign_department_id'));
	// 	} else if ($this->input->post('account') != "") {
	// 		$this->db->where('tbl_manual_task.department_id', '12');
	// 	}

	// 	$this->db->order_by('tbl_manual_task.id', 'DESC');
	// 	if ($this->input->post('search_task_head') != "") {
	// 		$this->db->where('task_head', $this->input->post('search_task_head'));
	// 	}

	// 	if ($this->input->post('search_status_of_work') != "") {
	// 		$this->db->where('task_status', $this->input->post('search_status_of_work'));
	// 		if ($this->session->userdata('assign_department_id') != '25') {
	// 			$this->db->where('assign_to_id =', $this->session->userdata('id'));
	// 		}
	// 	}

	// 	if ($this->input->post('search_priority') != "") {
	// 		$this->db->where('priority', $this->input->post('search_priority'));
	// 	}
	// 	if (!empty($search)) {
	// 		$search_lower = strtolower(trim($search));
	// 		$type_map = [
	// 			'forward to other department/person' => '1',
	// 			'mark as closed' => '2',
	// 			'create order' => '3'
	// 		];
	// 		$type_value = false;
	// 		foreach ($type_map as $label => $value) {
	// 			if (strpos($label, $search_lower) !== false) {
	// 				$type_value = $value;
	// 				break;
	// 			}
	// 		}
	// 		if (preg_match('/\b(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)\b/i', $search)) {
	// 			$converted_time = date("H:i", strtotime($search)); // convert to 24-hour format
	// 		} else {
	// 			$converted_time = $search;
	// 		}
	// 		$this->db->group_start();
	// 		$this->db->or_like('complete_by_time', $converted_time);
	// 		$this->db->or_like('tbl_customers.party_name', $search);
	// 		$this->db->or_like('tbl_krivisha_department.department', $search);
	// 		$this->db->or_like('assign_user.first_name', $search);
	// 		$this->db->or_like('employee_user.first_name', $search);
	// 		$this->db->or_like('task_id', $search);
	// 		$this->db->or_like('task_head', $search);
	// 		$this->db->or_like('priority', $search);
	// 		$this->db->or_like('task_action', $search);
	// 		$this->db->or_like('remark', $search);
	// 		$this->db->or_like('details_of_task', $search);
	// 		$this->db->or_like('complete_by_date', $search);
	// 		if ($type_value !== false) {
	// 			$this->db->or_like('task_action', $type_value);
	// 		} else {
	// 			$this->db->or_like('task_action', $search);
	// 		}
	// 		$this->db->group_end();
	// 	}
	// 	if ($length > 0) { $this->db->limit($length, $start); }
	// 	$query = $this->db->get();
	// 	$result = $query->result();
	// 	$this->db->select('COUNT(tbl_manual_task.id) as total_count');
	// 	$this->db->from('tbl_manual_task');
	// 	$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
	// 	$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
	// 	$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
	// 	$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
	// 	$this->db->where('tbl_manual_task.is_deleted', '0');
	// 	if ($this->session->userdata('assign_department_id') != '25') {
	// 		$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
	// 	}

	// 	if ($this->input->post('search_date') != "") {
	// 		$exp = explode("to", $this->input->post('search_date'));

	// 		if (isset($exp[0]) && isset($exp[1])) {
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
	// 		} else if (isset($exp[0]) && !isset($exp[1])) {
	// 			$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
	// 		}
	// 	}

	// 	if ($this->session->userdata('assign_department_id') != '25') {
	// 		$this->db->where('tbl_manual_task.department_id', $this->session->userdata('assign_department_id'));
	// 	} else if ($this->input->post('account') != "") {
	// 		$this->db->where('tbl_manual_task.department_id', '12');
	// 	}

	// 	$this->db->order_by('tbl_manual_task.id', 'DESC');
	// 	if ($this->input->post('search_task_head') != "") {
	// 		$this->db->where('task_head', $this->input->post('search_task_head'));
	// 	}

	// 	if ($this->input->post('search_status_of_work') != "") {
	// 		$this->db->where('task_status', $this->input->post('search_status_of_work'));
	// 	}
	// 	if ($this->session->userdata('assign_department_id') != '25') {
	// 		$this->db->where('assign_to_id =', $this->session->userdata('id'));

	// 	}

	// 	if ($this->input->post('search_priority') != "") {
	// 		$this->db->where('priority', $this->input->post('search_priority'));
	// 	}

	// 	if (!empty($search)) {
	// 		$this->db->group_start();
	// 		$this->db->or_like('complete_by_time', $search);
	// 		$this->db->or_like('tbl_customers.party_name', $search);
	// 		$this->db->or_like('assign_user.first_name', $search);
	// 		$this->db->or_like('employee_user.first_name', $search);
	// 		$this->db->or_like('task_id', $search);
	// 		$this->db->or_like('task_head', $search);
	// 		$this->db->or_like('priority', $search);
	// 		$this->db->or_like('task_status', $search);
	// 		$this->db->or_like('task_action', $search);
	// 		$this->db->or_like('remark', $search);
	// 		$this->db->or_like('details_of_task', $search);
	// 		$this->db->or_like('complete_by_date', $search);


	// 		$this->db->group_end();
	// 	}
	// 	$count_query = $this->db->get();
	// 	$total_count = $count_query->row()->total_count ?? 0;
	// 	return [
	// 		'data' => $result,
	// 		'total_count' => $total_count
	// 	];
	// }
	public function get_all_manual_task_list($length, $start, $search)
	{
		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');
		$this->_restrict_customer_queries('tbl_manual_task.party_id');
		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('account') != "") {
			$this->db->where('tbl_manual_task.department_id', '12');
		} else if ($this->input->post('store') != "") {
			$this->db->where('tbl_manual_task.department_id', '24');
		} else if ($this->input->post('purchase') != "") {
			$this->db->where('tbl_manual_task.department_id', '19');
		} else if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->group_start();
			$this->_apply_assigned_departments_scope('tbl_manual_task.department_id');
			$this->db->or_where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
			$this->db->group_end();
		}

		$this->db->order_by('tbl_manual_task.id', 'DESC');

		if ($this->input->post('search_task_head') != "") {
			$this->db->where('tbl_manual_task.task_head', $this->input->post('search_task_head'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('task_status', $this->input->post('search_status_of_work'));
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->group_start();
				$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
				$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		if ($this->input->post('super_admin_task') != "") {
			$this->db->where('tbl_manual_task.department_id','25');
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->where('task_status', $this->input->post('search_status_of_work'));
		}

		if ($this->input->post('search_priority') != "") {
			$this->db->where('tbl_manual_task.priority', $this->input->post('search_priority'));
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_map = [
				'forward to other department/person' => '1',
				'mark as closed' => '2',
				'create order' => '3'
			];
			$type_value = false;
			foreach ($type_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$type_value = $value;
					break;
				}
			}
			if (preg_match('/\b(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)\b/i', $search)) {
				$converted_time = date("H:i", strtotime($search)); // convert to 24-hour format
			} else {
				$converted_time = $search;
			}
			$this->db->group_start();
			$this->db->or_like('complete_by_time', $converted_time);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_head', $search);
			$this->db->or_like('priority', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('remark', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->or_like('complete_by_date', $search);
			if ($type_value !== false) {
				$this->db->or_like('task_action', $type_value);
			} else {
				$this->db->or_like('task_action', $search);
			}
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result1 = $query->result();

		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');
		$this->_restrict_customer_queries('tbl_manual_task.party_id');

		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('tbl_manual_task.employee_id', $this->session->userdata('id'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('account') != "") {
			$this->db->where('tbl_manual_task.department_id', '12');
		} else if ($this->input->post('purchase') != "") {
			$this->db->where('tbl_manual_task.department_id', '19');
		} else if ($this->input->post('store') != "") {
			$this->db->where('tbl_manual_task.department_id', '24');
		}

		$this->db->order_by('tbl_manual_task.id', 'DESC');
		if ($this->input->post('search_task_head') != "") {
			$this->db->where('tbl_manual_task.task_head', $this->input->post('search_task_head'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->group_start();
				$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
				$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		if ($this->input->post('super_admin_task') != "") {
			$this->db->where('tbl_manual_task.department_id','25');
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
		}

		if ($this->input->post('search_priority') != "") {
			$this->db->where('tbl_manual_task.priority', $this->input->post('search_priority'));
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_map = [
				'forward to other department/person' => '1',
				'mark as closed' => '2',
				'create order' => '3'
			];
			$type_value = false;
			foreach ($type_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$type_value = $value;
					break;
				}
			}
			if (preg_match('/\b(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)\b/i', $search)) {
				$converted_time = date("H:i", strtotime($search)); // convert to 24-hour format
			} else {
				$converted_time = $search;
			}
			$this->db->group_start();
			$this->db->or_like('complete_by_time', $converted_time);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_head', $search);
			$this->db->or_like('priority', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('remark', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->or_like('complete_by_date', $search);
			if ($type_value !== false) {
				$this->db->or_like('task_action', $type_value);
			} else {
				$this->db->or_like('task_action', $search);
			}
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result2 = $query->result();
		$result = array_merge($result1, $result2);

		// Group by id
		$grouped_result = [];
		foreach ($result as $row) {
			$grouped_result[$row->id] = $row;
		}

		$result = array_values($grouped_result);

		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');
		$this->_restrict_customer_queries('tbl_manual_task.party_id');
		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('account') != "") {
			$this->db->where('tbl_manual_task.department_id', '12');
		} else if ($this->input->post('purchase') != "") {
			$this->db->where('tbl_manual_task.department_id', '19');
		} else if ($this->input->post('store') != "") {
			$this->db->where('tbl_manual_task.department_id', '24');
		} else if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->group_start();
			$this->_apply_assigned_departments_scope('tbl_manual_task.department_id');
			$this->db->or_where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
			$this->db->group_end();
		}

		$this->db->order_by('tbl_manual_task.id', 'DESC');
		if ($this->input->post('search_task_head') != "") {
			$this->db->where('tbl_manual_task.task_head', $this->input->post('search_task_head'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->group_start();
				$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
				$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		if ($this->input->post('super_admin_task') != "") {
			$this->db->where('tbl_manual_task.department_id','25');
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
		}

		if ($this->input->post('search_priority') != "") {
			$this->db->where('tbl_manual_task.priority', $this->input->post('search_priority'));
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_map = [
				'forward to other department/person' => '1',
				'mark as closed' => '2',
				'create order' => '3'
			];
			$type_value = false;
			foreach ($type_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$type_value = $value;
					break;
				}
			}
			if (preg_match('/\b(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)\b/i', $search)) {
				$converted_time = date("H:i", strtotime($search)); // convert to 24-hour format
			} else {
				$converted_time = $search;
			}
			$this->db->group_start();
			$this->db->or_like('complete_by_time', $converted_time);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_head', $search);
			$this->db->or_like('priority', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('remark', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->or_like('complete_by_date', $search);
			if ($type_value !== false) {
				$this->db->or_like('task_action', $type_value);
			} else {
				$this->db->or_like('task_action', $search);
			}
			$this->db->group_end();
		}
		$query = $this->db->get();
		$result3 = $query->result();

		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');
		$this->_restrict_customer_queries('tbl_manual_task.party_id');

		if (!$this->_user_is_unrestricted_access_user()) {
			$this->db->where('tbl_manual_task.employee_id', $this->session->userdata('id'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_manual_task.complete_by_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_manual_task.complete_by_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('account') != "") {
			$this->db->where('tbl_manual_task.department_id', '12');
		} else if ($this->input->post('store') != "") {
			$this->db->where('tbl_manual_task.department_id', '24');
		} else if ($this->input->post('purchase') != "") {
			$this->db->where('tbl_manual_task.department_id', '19');
		}

		$this->db->order_by('tbl_manual_task.id', 'DESC');
		if ($this->input->post('search_task_head') != "") {
			$this->db->where('tbl_manual_task.task_head', $this->input->post('search_task_head'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->group_start();
				$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
				$this->db->or_where('tbl_manual_task.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		if ($this->input->post('super_admin_task') != "") {
			$this->db->where('tbl_manual_task.department_id','25');
			$this->db->where('tbl_manual_task.assign_to_id', $this->session->userdata('id'));
			$this->db->where('tbl_manual_task.task_status', $this->input->post('search_status_of_work'));
		}

		if ($this->input->post('search_priority') != "") {
			$this->db->where('tbl_manual_task.priority', $this->input->post('search_priority'));
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_map = [
				'forward to other department/person' => '1',
				'mark as closed' => '2',
				'create order' => '3'
			];
			$type_value = false;
			foreach ($type_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$type_value = $value;
					break;
				}
			}
			if (preg_match('/\b(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)\b/i', $search)) {
				$converted_time = date("H:i", strtotime($search)); // convert to 24-hour format
			} else {
				$converted_time = $search;
			}
			$this->db->group_start();
			$this->db->or_like('complete_by_time', $converted_time);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_head', $search);
			$this->db->or_like('priority', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('remark', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->or_like('complete_by_date', $search);
			if ($type_value !== false) {
				$this->db->or_like('task_action', $type_value);
			} else {
				$this->db->or_like('task_action', $search);
			}
			$this->db->group_end();
		}
		$query = $this->db->get();
		$result4 = $query->result();
		$count_result = array_merge($result3, $result4);

		// Group by id
		$grouped_count_result = [];
		foreach ($count_result as $row) {
			$grouped_count_result[$row->id] = $row;
		}

		$total_count = count($grouped_count_result);

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_all_auto_task_list($length, $start, $search)
	{
		$this->db->select('
		tbl_auto_task_list.*, 
		employee_user.first_name AS employee_name, 
		assign_user.first_name AS assigned_to_name, 
		tbl_krivisha_department.department AS d_name,
		tbl_customers.party_name,
	');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('user_data AS employee_user', 'tbl_auto_task_list.employee_id = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_auto_task_list.party_id', 'left');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		$this->_restrict_customer_queries('tbl_auto_task_list.party_id');

		$this->db->order_by('tbl_auto_task_list.updated_on', 'DESC');

		if (!$this->_user_is_unrestricted_access_user()) {
			// $this->db->where('tbl_auto_task_list.assign_to_id', $this->session->userdata('id'));
			if ($this->_user_has_department(12)) {
				// $this->db->where_not_in('order_status', ['1', '2', '3', '4', '5', '6', '7', '9']);
				$this->db->group_start();
				$this->db->where_in('tbl_auto_task_list.department_id', ['11', '12', '17']);
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			} else if ($this->_user_has_department(17)) {
				$this->db->group_start();
				$this->db->where_in('tbl_auto_task_list.department_id', ['11', '17']);
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			} else if ($this->input->post('production_pending_task') == "") {
				$this->db->group_start();
				$this->_apply_assigned_departments_scope('tbl_auto_task_list.department_id');
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_auto_task_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_auto_task_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_auto_task_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('search_task_id') != "") {
			$this->db->where('task_action', $this->input->post('search_task_id'));
		}
		if ($this->input->post('account_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.department_id', '12');
			$this->db->where('tbl_auto_task_list.order_department', '1');
			$this->db->where('tbl_auto_task_list.task_status', $this->input->post('account_pending_task'));
		}

		if ($this->input->post('production_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.order_department', '2');
			$this->db->where('tbl_auto_task_list.task_status', '1');
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
			}
		}
		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('order_status', $this->input->post('search_status_of_work'));
		}
		if ($this->input->post('type_of_task') != "") {
			$this->db->where('type_of_order', $this->input->post('type_of_task'));
		}
		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('order_department', $this->input->post('search_maintain_action'));
		}
		if ($this->input->post('order_id') != "") {
			$this->db->where('tbl_auto_task_list.task_id', $this->input->post('order_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_status', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(tbl_auto_task_list.id) as total_count');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('user_data AS employee_user', 'tbl_auto_task_list.employee_id = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_auto_task_list.party_id', 'left');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		$this->_restrict_customer_queries('tbl_auto_task_list.party_id');

		if (!$this->_user_is_unrestricted_access_user()) {
			// $this->db->where('tbl_auto_task_list.assign_to_id', $this->session->userdata('id'));
			if ($this->_user_has_department(12)) {
				// $this->db->where_not_in('order_status', ['1', '2', '3', '4', '5', '6', '7', '9']);
				$this->db->group_start();
				$this->db->where_in('tbl_auto_task_list.department_id', ['11', '12', '17']);
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			} else if ($this->_user_has_department(17)) {
				$this->db->group_start();
				$this->db->where_in('tbl_auto_task_list.department_id', ['11', '17']);
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			} else if ($this->input->post('production_pending_task') == "") {
				$this->db->group_start();
				$this->_apply_assigned_departments_scope('tbl_auto_task_list.department_id');
				$this->db->or_where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
				$this->db->group_end();
			}
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_auto_task_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_auto_task_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_auto_task_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('search_task_id') != "") {
			$this->db->where('task_action', $this->input->post('search_task_id'));
		}
		if ($this->input->post('account_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.department_id', '12');
			$this->db->where('tbl_auto_task_list.order_department', '1');
			$this->db->where('tbl_auto_task_list.task_status', $this->input->post('account_pending_task'));
		}

		if ($this->input->post('production_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.order_department', '2');
			$this->db->where('tbl_auto_task_list.task_status', '1');
			if (!$this->_user_is_unrestricted_access_user()) {
				$this->db->where('tbl_auto_task_list.employee_id', $this->session->userdata('id'));
			}
		}
		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('order_status', $this->input->post('search_status_of_work'));
		}
		if ($this->input->post('type_of_task') != "") {
			$this->db->where('type_of_order', $this->input->post('type_of_task'));
		}
		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('order_department', $this->input->post('search_maintain_action'));
		}
		if ($this->input->post('order_id') != "") {
			$this->db->where('tbl_auto_task_list.task_id', $this->input->post('order_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->or_like('task_id', $search);
			$this->db->or_like('task_status', $search);
			$this->db->or_like('task_action', $search);
			$this->db->or_like('details_of_task', $search);
			$this->db->group_end();
		}
		if ($this->input->post('account_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.department_id', '12');
			$this->db->where('tbl_auto_task_list.order_department', '1');
			$this->db->where('tbl_auto_task_list.task_status', $this->input->post('account_pending_task'));
		}
		if ($this->input->post('production_pending_task') != "") {
			$this->db->where('tbl_auto_task_list.order_department', '2');
			$this->db->where('tbl_auto_task_list.task_status', $this->input->post('production_pending_task'));
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}


	public function get_all_stock_report_inward_list($length, $start, $search)
	{
		$this->db->select('
		tbl_raw_material_stock_report_history.*,tbl_plant_master.plant_name,tbl_rm_master.rm_name,tbl_uom_master.uom_name');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '0');
		$this->_apply_assigned_plants_scope('tbl_raw_material_stock_report_history.plant_id');
		$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$date = DateTime::createFromFormat('d-m-Y', $search);
			if ($date) {
				$formatted_date = $date->format('Y-m-d');
				$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $formatted_date);
			} else {
				$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);
			}
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '0');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_master_batch_stock_inward_report($length, $start, $search)
	{
		$this->db->select('
		tbl_master_batch_stock_report_history.*,tbl_plant_master.plant_name,tbl_mb_master.name as mb_name');
		$this->db->from('tbl_master_batch_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_master_batch_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_master_batch_stock_report_history.master_batch_id', 'left');
		$this->db->where('tbl_master_batch_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_master_batch_stock_report_history.is_inward_outward', '0');
		$this->_apply_assigned_plants_scope('tbl_master_batch_stock_report_history.plant_id');
		$this->db->order_by('tbl_master_batch_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('master_batch_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.master_batch_id', $this->input->post('master_batch_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_mb_master.name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.total_quantity', $search);
			$date = DateTime::createFromFormat('d-m-Y', $search);
			if ($date) {
				$formatted_date = $date->format('Y-m-d');
				$this->db->or_like('tbl_master_batch_stock_report_history.created_on', $formatted_date);
			} else {
				$this->db->or_like('tbl_master_batch_stock_report_history.created_on', $search);
			}

			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_master_batch_stock_report_history');
		$this->db->where('tbl_master_batch_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_master_batch_stock_report_history.is_inward_outward', '0');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('master_batch_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.master_batch_id', $this->input->post('master_batch_id'));
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}



	public function get_all_stock_report_raw_material_list($length, $start, $search)
	{
		$this->db->select('
		tbl_raw_material_stock_report_history.*,tbl_plant_master.plant_name,tbl_rm_master.rm_name,tbl_uom_master.uom_name');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '1');
		$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '1');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_stock_ledger_list($length, $start, $search)
	{
		$type = $this->input->post('search_report_type');

		// ── Resolve item_type and item_id for the new unified table ──────────
		$item_type = null;
		$item_id   = null;
		if ($type === 'raw_material' && $this->input->post('raw_material_id') != '') {
			$item_type = 'raw_material';
			$item_id   = (int)$this->input->post('raw_material_id');
		} elseif ($type === 'color' && $this->input->post('color_id') != '') {
			$item_type = 'master_batch';
			$item_id   = (int)$this->input->post('color_id');
		} elseif (($type === 'article' || $type === 'mould') && $this->input->post('article_id') != '') {
			$item_type = 'article';
			$item_id   = (int)$this->input->post('article_id');
		}

		// ── Check whether tbl_stock_transactions has data for this item ───────
		$use_new_table = false;
		// Temporarily disabled until full history migration is complete
		// if ($item_type && $item_id) {
		// 	$count_new = $this->db
		// 		->where('item_type', $item_type)
		// 		->where('item_id',   $item_id)
		// 		->where('is_deleted', 0)
		// 		->count_all_results('tbl_stock_transactions');
		// 	$use_new_table = ($count_new > 0);
		// }

		if ($use_new_table) {
			// ── NEW: Single-table query on tbl_stock_transactions ─────────────
			$this->_apply_new_ledger_filters($item_type, $item_id, $search);
			if ($length > 0) { $this->db->limit($length, $start); }
			$result = $this->db->get('tbl_stock_transactions')->result();

			// Count
			$this->_apply_new_ledger_filters($item_type, $item_id, $search, true);
			$total_count = $this->db->get('tbl_stock_transactions')->row()->total_count ?? 0;

			return [
				'data'        => $result,
				'total_count' => $total_count,
				'source'      => 'new',
			];
		}

		// ── LEGACY FALLBACK: original multi-join query ────────────────────────
		$this->_apply_stock_ledger_filters($type, $search);
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get()->result();

		$this->_apply_stock_ledger_filters($type, $search, true);
		$total_count = $this->db->get()->row()->total_count ?? 0;

		return [
			'data'        => $result,
			'total_count' => $total_count,
			'source'      => 'legacy',
		];
	}

	public function get_article_production_ledger_rows($type)
	{
		if ($type !== 'article' && $type !== 'mould') {
			return [];
		}

		$this->db->select('
			apd.id,
			apd.production_id,
			apd.article_id,
			apd.plant_id,
			apd.machine_id,
			apd.approved_qty,
			apd.created_on,
			COALESCE(tbl_production_report.production_date, DATE(apd.created_on)) AS date,
			tbl_plant_master.plant_name,
			tbl_mould_parts.article_name,
			tbl_machine_master.machine_name
		', false);
		$this->db->from('tbl_article_production_details AS apd');
		$this->db->join('tbl_production_report', 'tbl_production_report.id = apd.production_id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = apd.plant_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = apd.article_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = apd.machine_id', 'left');
		$this->db->where('apd.is_deleted', '0');

		if ($this->input->post('article_id') != '') {
			$this->db->where('apd.article_id', $this->input->post('article_id'));
		}
		if ($this->input->post('plant_id') != '') {
			$this->db->where('apd.plant_id', $this->input->post('plant_id'));
		}

		if ($this->input->post('search_date') != '') {
			$exp = explode('to', $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(COALESCE(tbl_production_report.production_date, apd.created_on)) >=', date('Y-m-d', strtotime($exp[0])));
				$this->db->where('DATE(COALESCE(tbl_production_report.production_date, apd.created_on)) <=', date('Y-m-d', strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(COALESCE(tbl_production_report.production_date, apd.created_on)) =', date('Y-m-d', strtotime($exp[0])));
			}
		}

		$this->db->order_by('COALESCE(tbl_production_report.production_date, apd.created_on)', 'DESC', false);
		$this->db->order_by('apd.id', 'DESC');
		$rows = $this->db->get()->result();

		foreach ($rows as $row) {
			$row->date = $row->date ?? $row->created_on;
			$row->is_inward_outward = '5';
			$row->opening_stock = 0;
			$row->total_quantity = floatval($row->approved_qty ?? 0);
			$row->inward_no = null;
			$row->order_id = 'PROD-' . $row->production_id;
			$row->outward_qty = 0;
			$row->adjusted_qty = 0;
			$row->return_stock_qty = 0;
		}

		return $rows;
	}

	/**
	 * _apply_new_ledger_filters()
	 * Builds SELECT / WHERE clauses for tbl_stock_transactions.
	 */
	private function _apply_new_ledger_filters(string $item_type, int $item_id, string $search, bool $is_count = false)
	{
		if ($is_count) {
			$this->db->select('COUNT(*) as total_count');
		} else {
			$this->db->select('
				t.id,
				t.transaction_date AS `date`,
				t.transaction_type,
				t.movement_type,
				t.reference_no,
				t.reference_source,
				t.item_name,
				t.plant_name,
				t.qty,
				t.balance_qty AS total_quantity,
				CASE WHEN t.movement_type = "IN"  THEN t.qty ELSE 0 END AS inward_qty,
				CASE WHEN t.movement_type = "OUT" THEN t.qty ELSE 0 END AS outward_qty,
				t.uom_id,
				t.created_on
			');
			$this->db->order_by('t.transaction_date', 'DESC');
			$this->db->order_by('t.id', 'DESC');
		}

		$this->db->from('tbl_stock_transactions t');
		$this->db->where('t.item_type',  $item_type);
		$this->db->where('t.item_id',    $item_id);
		$this->db->where('t.is_deleted', 0);

		// Plant filter
		if ($this->input->post('plant_id') != '') {
			$this->db->where('t.plant_id', (int)$this->input->post('plant_id'));
		}

		// Date range filter
		if ($this->input->post('search_date') != '') {
			$exp = explode('to', $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('t.transaction_date >=', date('Y-m-d', strtotime($exp[0])));
				$this->db->where('t.transaction_date <=', date('Y-m-d', strtotime($exp[1])));
			} elseif (isset($exp[0]) && trim($exp[0]) !== '') {
				$this->db->where('t.transaction_date', date('Y-m-d', strtotime($exp[0])));
			}
		}

		// Transaction type filter
		if ($this->input->post('transaction_type') != '') {
			$this->db->where('t.transaction_type', $this->input->post('transaction_type'));
		}

		// Text search
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('t.item_name', $search);
			$this->db->or_like('t.plant_name', $search);
			$this->db->or_like('t.transaction_type', $search);
			$this->db->or_like('t.reference_no', $search);
			$this->db->group_end();
		}
	}

	private function _apply_stock_ledger_filters($type, $search, $is_count = false)
	{
		if ($type == 'color') {
			if ($is_count) {
				$this->db->select('COUNT(*) as total_count');
			} else {
				$this->db->select('
				tbl_master_batch_stock_report_history.*,
				tbl_plant_master.plant_name,
				tbl_mb_master.name as mb_name,
				tbl_inward.inward_no,
				tbl_machine_master.machine_name,
				tbl_customers.party_name as supplier_party_name');
			}
			$this->db->from('tbl_master_batch_stock_report_history');
			if (!$is_count || !empty($search)) {
				$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_master_batch_stock_report_history.plant_id', 'left');
				$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_master_batch_stock_report_history.master_batch_id', 'left');
			}
			if (!$is_count) {
				$this->db->join('tbl_inward_order_data', 'tbl_inward_order_data.plant_id = tbl_master_batch_stock_report_history.plant_id AND tbl_inward_order_data.master_batch_id = tbl_master_batch_stock_report_history.master_batch_id AND tbl_inward_order_data.created_on = tbl_master_batch_stock_report_history.created_on', 'left');
				$this->db->join('tbl_inward', 'tbl_inward.id = tbl_inward_order_data.database_inward_id', 'left');
				$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
				$this->db->join('tbl_production_report', 'tbl_production_report.id = tbl_master_batch_stock_report_history.production_id', 'left');
				$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_report.machine_id', 'left');
			}
			$this->db->where('tbl_master_batch_stock_report_history.is_deleted', '0');

			if ($this->input->post('color_id') != "") {
				$this->db->where('tbl_master_batch_stock_report_history.master_batch_id', $this->input->post('color_id'));
			}

			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_master_batch_stock_report_history.plant_id', $this->input->post('plant_id'));
			}

			if ($this->input->post('search_date') != "") {
				$exp = explode("to", $this->input->post('search_date'));
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(tbl_master_batch_stock_report_history.date) >=', date("Y-m-d", strtotime($exp[0])));
					$this->db->where('DATE(tbl_master_batch_stock_report_history.date) <=', date("Y-m-d", strtotime($exp[1])));
				}
			}

			if (!empty($search)) {
				$this->db->group_start();
				$this->db->or_like('tbl_mb_master.name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_inward.inward_no', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->group_end();
			}

			if (!$is_count) {
				$this->db->order_by('tbl_master_batch_stock_report_history.date', 'DESC');
				$this->db->order_by('tbl_master_batch_stock_report_history.id', 'DESC');
			}
		} else {
			if ($is_count) {
				$this->db->select('COUNT(*) as total_count');
			} else {
				$this->db->select('
				tbl_raw_material_stock_report_history.*,
				tbl_plant_master.plant_name,
				tbl_rm_master.rm_name,
				tbl_uom_master.uom_name,
				tbl_mould_parts.article_name,
				tbl_inward.inward_no,
				tbl_machine_master.machine_name,
				tbl_customers.party_name as supplier_party_name');
			}
			$this->db->from('tbl_raw_material_stock_report_history');
			$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_raw_material_stock_report_history.article_id', 'left');
			if (!$is_count || !empty($search)) {
				$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
				$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
			}
			if (!$is_count) {
				$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
				$this->db->join('tbl_inward_order_data', 'tbl_inward_order_data.plant_id = tbl_raw_material_stock_report_history.plant_id AND tbl_inward_order_data.raw_material_id = tbl_raw_material_stock_report_history.raw_material_id AND tbl_inward_order_data.created_on = tbl_raw_material_stock_report_history.created_on', 'left');
				$this->db->join('tbl_inward', 'tbl_inward.id = tbl_inward_order_data.database_inward_id', 'left');
				$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
				$this->db->join('tbl_production_report', 'tbl_production_report.id = tbl_raw_material_stock_report_history.production_id', 'left');
				$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_report.machine_id', 'left');
			} elseif (!empty($search)) {
				// For count query with search, join required tables so search can filter on them
				$this->db->join('tbl_inward_order_data', 'tbl_inward_order_data.plant_id = tbl_raw_material_stock_report_history.plant_id AND tbl_inward_order_data.raw_material_id = tbl_raw_material_stock_report_history.raw_material_id AND tbl_inward_order_data.created_on = tbl_raw_material_stock_report_history.created_on', 'left');
				$this->db->join('tbl_inward', 'tbl_inward.id = tbl_inward_order_data.database_inward_id', 'left');
				$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
				$this->db->join('tbl_production_report', 'tbl_production_report.id = tbl_raw_material_stock_report_history.production_id', 'left');
				$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_report.machine_id', 'left');
			}
			$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');

			if ($type == 'raw_material') {
				$this->db->where('tbl_raw_material_stock_report_history.raw_material_id IS NOT NULL', null, false);
				$this->db->where('tbl_raw_material_stock_report_history.raw_material_id !=', 0);
				if ($this->input->post('raw_material_id') != "") {
					$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
				}
			} else if ($type == 'article' || $type == 'mould') {
				$this->db->where('tbl_raw_material_stock_report_history.article_id IS NOT NULL', null, false);
				$this->db->where('tbl_raw_material_stock_report_history.article_id !=', 0);
				if ($this->input->post('article_id') != "") {
					$this->db->where('tbl_raw_material_stock_report_history.article_id', $this->input->post('article_id'));
				}
				if ($type == 'mould' && $this->input->post('mould_id') != "") {
					$this->db->where('tbl_mould_parts.type_of_mould_id', $this->input->post('mould_id'));
				}
			}

			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('search_date') != "") {
				$exp = explode("to", $this->input->post('search_date'));
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(tbl_raw_material_stock_report_history.date) >=', date("Y-m-d", strtotime($exp[0])));
					$this->db->where('DATE(tbl_raw_material_stock_report_history.date) <=', date("Y-m-d", strtotime($exp[1])));
				}
			}

			if (!empty($search)) {
				// Detect computed Order ID prefixes (SCH-, PROD-, INW-, ADJ-)
				$schedule_id_search   = null;
				$production_id_search = null;
				$adj_id_search        = null;

				if (preg_match('/^SCH-?(\d+)$/i', trim($search), $m)) {
					$schedule_id_search = (int)$m[1];
				} elseif (preg_match('/^PROD-?(\d+)$/i', trim($search), $m)) {
					$production_id_search = (int)$m[1];
				} elseif (preg_match('/^ADJ-?(\d+)$/i', trim($search), $m)) {
					$adj_id_search = (int)$m[1];
				}

				$this->db->group_start();
				if ($schedule_id_search !== null) {
					// SCH-XXXX: pre-fetch matching (item_table_id, created_on) pairs from
					// tbl_production_schedules_rm_details — fast because schedule_id is indexed.
					// Then filter history rows using IN() on created_on for the matching item_table_ids.
					$sch_rows = $this->db
						->select('item_table_id, created_on')
						->where('schedule_id', $schedule_id_search)
						->get('tbl_production_schedules_rm_details')
						->result();

					if (!empty($sch_rows)) {
						$pairs = [];
						foreach ($sch_rows as $sr) {
							$pairs[] = '(' .
								'tbl_raw_material_stock_report_history.raw_material_id = ' . (int)$sr->item_table_id .
								' AND tbl_raw_material_stock_report_history.created_on = ' . $this->db->escape($sr->created_on) .
								')';
						}
						$this->db->or_where('(' . implode(' OR ', $pairs) . ')', null, false);
					} else {
						// No rows found for this schedule — force empty result
						$this->db->where('1 = 0', null, false);
					}
				} elseif ($production_id_search !== null) {
					// PROD-XXXX: direct column search — very fast
					$this->db->or_where('tbl_raw_material_stock_report_history.production_id', $production_id_search);
				} elseif ($adj_id_search !== null) {
					// ADJ-XXXX: pre-fetch matching (plant_id, date) from tbl_stock_adjustment
					$adj_row = $this->db
						->select('id, plant_id, created_on')
						->where('id', $adj_id_search)
						->get('tbl_stock_adjustment')
						->row();
					if (!empty($adj_row)) {
						$this->db->or_where('tbl_raw_material_stock_report_history.plant_id', $adj_row->plant_id);
						$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date('Y-m-d', strtotime($adj_row->created_on)));
					} else {
						$this->db->where('1 = 0', null, false);
					}
				} else {
					// Plain text: search all visible columns
					$this->db->or_like('tbl_rm_master.rm_name', $search);
					$this->db->or_like('tbl_plant_master.plant_name', $search);
					$this->db->or_like('tbl_mould_parts.article_name', $search);
					$this->db->or_like('tbl_inward.inward_no', $search);
					$this->db->or_like('tbl_customers.party_name', $search);
					$this->db->or_like('tbl_machine_master.machine_name', $search);
				}
				$this->db->group_end();
			}

			if (!$is_count) {
				$this->db->order_by('tbl_raw_material_stock_report_history.date', 'DESC');
				$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');
			}
		}
	}

	public function get_stock_ledger_summary($type, $search)
	{
		// Fetch all records matching the current filters (without pagination limit)
		$this->_apply_stock_ledger_filters($type, $search, false);
		// Order ascending to calculate opening stock at the start of the date range
		// However, _apply_stock_ledger_filters orders DESC. We can just fetch it all and reverse, or calculate from it.
		$query = $this->db->get();
		$records = $query->result();

		$summary = [
			'opening_stock' => 0,
			'total_inward' => 0,
			'total_outward' => 0,
			'closing_stock' => 0
		];

		if (empty($records)) {
			return $summary;
		}

		// Because _apply_stock_ledger_filters sorts DESC (newest first), the last element in the array is the oldest record in the selected date range.
		$oldest_record = end($records);
		
		// For opening stock, if it's the legacy tables, we can just use the opening_stock of the oldest record in the filtered range
		$summary['opening_stock'] = floatval($oldest_record->opening_stock ?? 0);

		foreach ($records as $member) {
			$flag = $member->is_inward_outward ?? '0';
			$production_inward_qty = floatval($member->approved_qty ?? 0);
			$is_article_production_row = ($type === 'article' || $type === 'mould') && $production_inward_qty > 0;

			$inward_qty = 0;
			$inward_flags = ['0', '1', '3', '6', '7'];
			if ($type === 'article' || $type === 'mould') {
				$inward_flags[] = '5';
			}
			if ($is_article_production_row || in_array($flag, $inward_flags)) {
				if ($is_article_production_row || $flag === '5') {
					$inward_qty = $production_inward_qty;
				} elseif ($flag === '3') {
					$inward_qty = floatval($member->adjusted_qty ?? 0);
				} elseif ($flag === '6') {
					$inward_qty = floatval($member->return_stock_qty ?? 0);
				} else {
					$inward_qty = floatval($member->inward_qty ?? 0);
				}
			}

			$outward_qty = 0;
			$outward_flags = ['2', '4'];
			if ($type !== 'article' && $type !== 'mould') {
				$outward_flags[] = '5';
			}
			if (in_array($flag, $outward_flags)) {
				if ($flag === '4') {
					$outward_qty = floatval($member->adjusted_qty ?? 0);
				} elseif ($flag === '5') {
					$outward_qty = floatval($member->approved_qty ?? 0);
				} else {
					if ($type === 'article' && $flag === '2' && !empty($member->created_on)) {
						$this->db->select('dispatch_quantity');
						$this->db->where('article_id', $member->article_id);
						$this->db->where('created_on', $member->created_on);
						$dp_sum = $this->db->get('tbl_dispatch_order_data')->row();
						if (!empty($dp_sum->dispatch_quantity)) {
							$outward_qty = floatval($dp_sum->dispatch_quantity);
						} else {
							$outward_qty = floatval($member->outward_qty ?? 0);
						}
					} else {
						$outward_qty = floatval($member->outward_qty ?? 0);
					}
				}
			}

			$summary['total_inward'] += $inward_qty;
			$summary['total_outward'] += $outward_qty;
		}

		// Wait, for Article/Mould, the true closing stock is dynamically calculated from the beginning of time.
		// For Raw Material/Color, it's just the newest record's stored balance.
		// The newest record is at index 0 because of DESC sorting.
		$newest_record = $records[0];
		
		if ($type === 'article' || $type === 'mould') {
			// For articles, since we need dynamic full history balance, we can't reliably just use total_quantity.
			// But wait, the Stock Ledger Report in Ajax_controller computes full history! 
			// So Ajax_controller will override closing_stock anyway if we return it here.
			// For now, let's just return the math:
			$summary['closing_stock'] = $summary['opening_stock'] + $summary['total_inward'] - $summary['total_outward'];
		} else {
			$summary['closing_stock'] = floatval($newest_record->total_quantity ?? 0);
			// Also for raw materials, the opening stock is just closing stock - total_inward + total_outward
			$summary['opening_stock'] = $summary['closing_stock'] - $summary['total_inward'] + $summary['total_outward'];
		}

		return $summary;
	}

	public function get_all_task_history()
	{
		$task_id = $this->input->post('task_id');
		$this->db->select('tbl_auto_task_list_history.*,tbl_krivisha_department.department,assign_to_table.first_name,last_updated_user.first_name as last_updated_name');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list_history.department_id', 'left');
		$this->db->join('user_data as assign_to_table', 'assign_to_table.id = tbl_auto_task_list_history.assign_to_id', 'left');
		$this->db->join('user_data as last_updated_user', 'last_updated_user.id = tbl_auto_task_list_history.last_updated_by', 'left');
		// $this->db->where('tbl_auto_task_list_history.department_id', $this->session->userdata('assign_department_id'));
		$this->db->where('tbl_auto_task_list_history.is_deleted', '0');
		$this->db->where('task_id', $task_id);
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_auto_task_list_history');
		$result = $result->result();
		echo json_encode($result);
	}
	public function get_all_manual_task_history()
	{
		$task_id = $this->input->post('task_id');
		$this->db->select('tbl_manual_task_history.*,tbl_krivisha_department.department,assign_to_table.first_name ,last_updated_user.first_name as last_updated_name');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task_history.department_id', 'left');
		$this->db->join('user_data as assign_to_table', 'assign_to_table.id = tbl_manual_task_history.assign_to_id', 'left');
		$this->db->join('user_data as last_updated_user', 'last_updated_user.id = tbl_manual_task_history.last_updated_by', 'left');
		$this->db->where('tbl_manual_task_history.is_deleted', '0');
		$this->db->where('task_id', $task_id);
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_manual_task_history');
		$result = $result->result();
		echo json_encode($result);
	}

	public function get_all_purchase_sales_report_details($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_purchase_sales');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('date', $search);
			$this->db->or_like('supplier', $search);
			$this->db->or_like('supplier_address', $search);
			$this->db->or_like('consignee', $search);
			$this->db->or_like('plant_name', $search);
			$this->db->or_like('supplier_invoice_no', $search);
			$this->db->or_like('supplier_invoice_date', $search);
			$this->db->or_like('gstin_uin', $search);
			$this->db->or_like('pan_no', $search);
			$this->db->or_like('order_no_and_date', $search);
			$this->db->or_like('terms_of_payment', $search);
			$this->db->or_like('receipt_no_and_date', $search);
			$this->db->or_like('receipt_doc_lr_no', $search);
			$this->db->or_like('despatch_through', $search);
			$this->db->or_like('destination', $search);
			$this->db->or_like('article_name', $search);
			$this->db->or_like('rate', $search);
			$this->db->or_like('value', $search);
			$this->db->or_like('addl_cost', $search);
			$this->db->or_like('taxes_gst', $search);
			$this->db->or_like('gross_total', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_purchase_sales');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('date', $search);
			$this->db->or_like('supplier', $search);
			$this->db->or_like('supplier_address', $search);
			$this->db->or_like('consignee', $search);
			$this->db->or_like('plant_name', $search);
			$this->db->or_like('supplier_invoice_no', $search);
			$this->db->or_like('supplier_invoice_date', $search);
			$this->db->or_like('gstin_uin', $search);
			$this->db->or_like('pan_no', $search);
			$this->db->or_like('order_no_and_date', $search);
			$this->db->or_like('terms_of_payment', $search);
			$this->db->or_like('receipt_no_and_date', $search);
			$this->db->or_like('receipt_doc_lr_no', $search);
			$this->db->or_like('despatch_through', $search);
			$this->db->or_like('destination', $search);
			$this->db->or_like('article_name', $search);
			$this->db->or_like('rate', $search);
			$this->db->or_like('value', $search);
			$this->db->or_like('addl_cost', $search);
			$this->db->or_like('taxes_gst', $search);
			$this->db->or_like('gross_total', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_all_sales_report_details($length, $start, $search)
	{
		$this->db->select('*');
		$this->db->from('tbl_sales_report');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('date', $search);
			$this->db->or_like('buyer', $search);
			$this->db->or_like('buyer_address', $search);
			$this->db->or_like('consignee_address', $search);
			$this->db->or_like('voucher_type', $search);
			$this->db->or_like('voucher_no', $search);
			$this->db->or_like('voucher_ref_no', $search);
			$this->db->or_like('gstin_uin', $search);
			$this->db->or_like('pan_no', $search);
			$this->db->or_like('narration', $search);
			$this->db->or_like('order_no_and_date', $search);
			$this->db->or_like('terms_of_payment', $search);
			$this->db->or_like('other_references', $search);
			$this->db->or_like('terms_of_delivery', $search);
			$this->db->or_like('delivery_note_no_and_date', $search);
			$this->db->or_like('despatch_doc_no', $search);
			$this->db->or_like('despatch_through', $search);
			$this->db->or_like('destination', $search);
			$this->db->or_like('particulars', $search);
			$this->db->or_like('quantity', $search);
			$this->db->or_like('rate', $search);
			$this->db->or_like('value', $search);
			$this->db->or_like('gst', $search);
			$this->db->or_like('discounts', $search);
			$this->db->or_like('other_charges', $search);
			$this->db->or_like('gross_total', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_sales_report');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('date', $search);
			$this->db->or_like('buyer', $search);
			$this->db->or_like('buyer_address', $search);
			$this->db->or_like('consignee_address', $search);
			$this->db->or_like('voucher_type', $search);
			$this->db->or_like('voucher_no', $search);
			$this->db->or_like('voucher_ref_no', $search);
			$this->db->or_like('gstin_uin', $search);
			$this->db->or_like('pan_no', $search);
			$this->db->or_like('narration', $search);
			$this->db->or_like('order_no_and_date', $search);
			$this->db->or_like('terms_of_payment', $search);
			$this->db->or_like('other_references', $search);
			$this->db->or_like('terms_of_delivery', $search);
			$this->db->or_like('delivery_note_no_and_date', $search);
			$this->db->or_like('despatch_doc_no', $search);
			$this->db->or_like('despatch_through', $search);
			$this->db->or_like('destination', $search);
			$this->db->or_like('particulars', $search);
			$this->db->or_like('quantity', $search);
			$this->db->or_like('rate', $search);
			$this->db->or_like('value', $search);
			$this->db->or_like('gst', $search);
			$this->db->or_like('discounts', $search);
			$this->db->or_like('other_charges', $search);
			$this->db->or_like('gross_total', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	//-----------------------------------------------------hidden ssub master list -------------------------------------------------------- 

	public function get_sub_brand_type()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_brand_type');
		return $result->row();
	}
	public function set_sub_brand_type()
	{
		$data = array(
			'brand_type' => $this->input->post('brand_type'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_brand_type', $data);
		return '2';
	}
	public function get_sub_department()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_department');
		return $result->row();
	}
	public function set_sub_department()
	{
		$data = array(
			'department' => $this->input->post('department'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_department', $data);
		return '2';
	}
	public function get_sub_designation()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_designation');
		return $result->row();
	}
	public function set_sub_designation()
	{
		$data = array(
			'designation' => $this->input->post('designation'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_designation', $data);
		return '2';
	}
	public function get_sub_nature_of_business()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_nature_of_business');
		return $result->row();
	}
	public function set_sub_nature_of_business()
	{
		$data = array(
			'nature_of_business' => $this->input->post('nature_of_business'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_nature_of_business', $data);
		return '2';
	}
	public function get_sub_type_of_business()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_type_of_business');
		return $result->row();
	}
	public function set_sub_type_of_business()
	{
		$data = array(
			'type_of_business' => $this->input->post('type_of_business'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_type_of_business', $data);
		return '2';
	}
	public function get_sub_group_of_list()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_group_of_article');
		return $result->row();
	}
	public function set_sub_group_of_list()
	{
		$data = array(
			'group_of_article' => $this->input->post('group_of_article'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_group_of_article', $data);
		return '2';
	}
	public function get_sub_alanky_bolt()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_alankey_bolt');
		return $result->row();
	}
	public function set_sub_alanky_bolt()
	{
		$data = array(
			'alankey_bolt' => $this->input->post('alankey_bolt'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_alankey_bolt', $data);
		return '2';
	}
	public function get_sub_type_of_mould()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_type_of_mould');
		return $result->row();
	}
	public function set_sub_type_of_mould()
	{
		$data = array(
			'type_of_mould' => $this->input->post('type_of_mould'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_type_of_mould', $data);
		return '2';
	}
	public function get_sub_air_pin()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_air_pin');
		return $result->row();
	}
	public function set_sub_air_pin()
	{
		$data = array(
			'air_pin' => $this->input->post('air_pin'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_air_pin', $data);
		return '2';
	}
	public function get_sub_spring()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_spring_master');
		return $result->row();
	}
	public function set_sub_sprin()
	{
		$data = array(
			'spring' => $this->input->post('spring'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_spring_master', $data);
		return '2';
	}
	public function get_sub_pu_nipples()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_pu_nipples_master');
		return $result->row();
	}
	public function set_sub_pu_nipples()
	{
		$data = array(
			'pu_nipples' => $this->input->post('pu_nipples'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_pu_nipples_master', $data);
		return '2';
	}
	public function get_sub_ejector_pin()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_ejector_pin_master');
		return $result->row();
	}
	public function set_sub_ejector_pin()
	{
		$data = array(
			'ejector_pin' => $this->input->post('ejector_pin'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_ejector_pin_master', $data);
		return '2';
	}
	public function get_sub_i_bolt()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_i_bolt');
		return $result->row();
	}
	public function set_sub_i_bolt()
	{
		$data = array(
			'i_bolt' => $this->input->post('i_bolt'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_i_bolt', $data);
		return '2';
	}
	public function get_sub_cord()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_cord');
		return $result->row();
	}
	public function set_sub_cord()
	{
		$data = array(
			'cord' => $this->input->post('cord'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_cord', $data);
		return '2';
	}
	public function get_sub_o_ring()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_o_ring');
		return $result->row();
	}
	public function set_sub_o_ring()
	{
		$data = array(
			'o_ring' => $this->input->post('o_ring'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_o_ring', $data);
		return '2';
	}
	public function get_sub_insert_slot_plate()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_insert_slot_plate');
		return $result->row();
	}
	public function set_sub_insert_slot_plate()
	{
		$data = array(
			'insert_slot_plate' => $this->input->post('insert_slot_plate'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_insert_slot_plate', $data);
		return '2';
	}
	public function get_sub_core_cylinder_seal()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_core_cylinder_seal');
		return $result->row();
	}
	public function set_sub_core_cylinder_seal()
	{
		$data = array(
			'core_cylinder_seal' => $this->input->post('core_cylinder_seal'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_core_cylinder_seal', $data);
		return '2';
	}
	public function get_sub_seal()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_seal');
		return $result->row();
	}
	public function set_sub_seal()
	{
		$data = array(
			'seal' => $this->input->post('seal'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_seal', $data);
		return '2';
	}
	public function get_sub_hope_pipe()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_hose_pipe');
		return $result->row();
	}
	public function set_sub_hope_pipe()
	{
		$data = array(
			'hose_pipe' => $this->input->post('hose_pipe'),
		);
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('tbl_hose_pipe', $data);
		return '2';
	}
	public function get_sub_brand_type_list($length, $start, $search)
	{
		$this->db->select('tbl_brand_type.*');
		$this->db->from('tbl_brand_type');
		$this->db->group_by('tbl_brand_type.id');
		$this->db->where('tbl_brand_type.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('brand_type', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_brand_type.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_brand_type_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_brand_type');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('brand_type', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_department_list($length, $start, $search)
	{
		$this->db->select('tbl_department.*');
		$this->db->from('tbl_department');
		$this->db->group_by('tbl_department.id');
		$this->db->where('tbl_department.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('department', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_department.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_department_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_department');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('department', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	// public function get_type_of_printing_unit($id)
	// {
	// 	$this->db->where('is_deleted', '0');
	// 	$this->db->where('id', $id);
	// 	$query = $this->db->get('tbl_printing_unit');
	// 	return $query->row();
	// }
	public function get_sub_party_designation_list($length, $start, $search)
	{
		$this->db->select('tbl_designation.*');
		$this->db->from('tbl_designation');
		$this->db->group_by('tbl_designation.id');
		$this->db->where('tbl_designation.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('designation', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_designation.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_designation_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_designation');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('designation', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_party_nature_list($length, $start, $search)
	{
		$this->db->select('tbl_nature_of_business.*');
		$this->db->from('tbl_nature_of_business');
		$this->db->group_by('tbl_nature_of_business.id');
		$this->db->where('tbl_nature_of_business.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('nature_of_business', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_nature_of_business.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_party_nature_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_nature_of_business');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('nature_of_business', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_party_type_list($length, $start, $search)
	{
		$this->db->select('tbl_type_of_business.*');
		$this->db->from('tbl_type_of_business');
		$this->db->group_by('tbl_type_of_business.id');
		$this->db->where('tbl_type_of_business.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('type_of_business', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_type_of_business.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_party_type_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_type_of_business');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('type_of_business', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_group_of_article($length, $start, $search)
	{
		$this->db->select('tbl_group_of_article.*');
		$this->db->from('tbl_group_of_article');
		$this->db->group_by('tbl_group_of_article.id');
		$this->db->where('tbl_group_of_article.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('group_of_article', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_group_of_article.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_group_of_article_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_group_of_article');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('group_of_article', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_alankey_bolt_list($length, $start, $search)
	{
		$this->db->select('tbl_alankey_bolt.*');
		$this->db->from('tbl_alankey_bolt');
		$this->db->group_by('tbl_alankey_bolt.id');
		$this->db->where('tbl_alankey_bolt.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('alankey_bolt', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_alankey_bolt.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_alankey_bolt_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_alankey_bolt');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('alankey_bolt', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_type_of_mould_list($length, $start, $search)
	{
		$this->db->select('tbl_type_of_mould.*');
		$this->db->from('tbl_type_of_mould');
		$this->db->group_by('tbl_type_of_mould.id');
		$this->db->where('tbl_type_of_mould.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('type_of_mould', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_type_of_mould.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_sub_type_of_mould_count($search)
	{
		$this->db->select('id');
		$this->db->from('tbl_type_of_mould');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('type_of_mould', $search);
			$this->db->group_end();
		}
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_sub_air_pin_list($length, $start, $search)
	{
		$this->db->select('tbl_air_pin.*');
		$this->db->from('tbl_air_pin');
		$this->db->where('tbl_air_pin.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('air_pin', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		// Get the total count of records matching the search criteria without limit
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_air_pin');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('air_pin', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_spring_list($length, $start, $search)
	{
		$this->db->select('tbl_spring_master.*');
		$this->db->from('tbl_spring_master');
		$this->db->where('tbl_spring_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('spring', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_spring_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('spring', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_pu_nipples_list($length, $start, $search)
	{
		$this->db->select('tbl_pu_nipples_master.*');
		$this->db->from('tbl_pu_nipples_master');
		$this->db->where('tbl_pu_nipples_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('pu_nipples', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_pu_nipples_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('pu_nipples', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_ejector_pin_list($length, $start, $search)
	{
		$this->db->select('tbl_ejector_pin_master.*');
		$this->db->from('tbl_ejector_pin_master');
		$this->db->where('tbl_ejector_pin_master.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('ejector_pin', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_ejector_pin_master');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('ejector_pin', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_i_bolt_list($length, $start, $search)
	{
		$this->db->select('tbl_i_bolt.*');
		$this->db->from('tbl_i_bolt');
		$this->db->where('tbl_i_bolt.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('i_bolt', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_i_bolt');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('i_bolt', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_cord_list($length, $start, $search)
	{
		$this->db->select('tbl_cord.*');
		$this->db->from('tbl_cord');
		$this->db->where('tbl_cord.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('cord', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_cord');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('cord', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_o_ring_list($length, $start, $search)
	{
		$this->db->select('tbl_o_ring.*');
		$this->db->from('tbl_o_ring');
		$this->db->where('tbl_o_ring.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('o_ring', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_o_ring');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('o_ring', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_insert_slot_plate_list($length, $start, $search)
	{
		$this->db->select('tbl_insert_slot_plate.*');
		$this->db->from('tbl_insert_slot_plate');
		$this->db->where('tbl_insert_slot_plate.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('insert_slot_plate', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_insert_slot_plate');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('insert_slot_plate', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_core_cylender_list($length, $start, $search)
	{
		$this->db->select('tbl_core_cylinder_seal.*');
		$this->db->from('tbl_core_cylinder_seal');
		$this->db->where('tbl_core_cylinder_seal.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('core_cylinder_seal', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_core_cylinder_seal');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('core_cylinder_seal', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_sub_seal_list($length, $start, $search)
	{
		$this->db->select('tbl_seal.*');
		$this->db->from('tbl_seal');
		$this->db->where('tbl_seal.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('seal', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_seal');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('seal', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_inward_form_list($length, $start, $search)
	{
		$this->db->select('tbl_inward.*, tbl_customers.party_name,tbl_plant_master.plant_name');
		$this->db->from('tbl_inward');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_inward.plant_id', 'left');
		$this->db->where('tbl_inward.is_deleted', '0');
		$this->_apply_assigned_plants_scope('tbl_inward.plant_id');

		if ($this->input->post('inward_for') != "") {
			$this->db->where('tbl_inward.inward_for', $this->input->post('inward_for'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_inward.inward_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_inward.inward_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_inward.inward_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('party_action') != "") {
			$this->db->where('tbl_inward.party_id', $this->input->post('party_action'));
		}
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_inward.plant_id', $this->input->post('plant_id'));
		}
		if (!empty($search)) {
			$this->apply_search_filter($search);
		}

		// if ($length > 0) { $this->db->limit($length, $start); }
		$this->db->order_by('tbl_inward.id', 'DESC');
		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_inward');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->apply_search_filter($search);
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_material_qty_request_list($length, $start, $search, $according_plant)
	{
		$this->db->select('
        tbl_rm_request_qty.*,
        plant1.plant_name AS plant_name,
        plant2.plant_name AS my_plant_name,
		user_data.first_name AS request_by
		');
		$this->db->from('tbl_rm_request_qty');
		$this->db->join('tbl_plant_master AS plant1', 'plant1.id = tbl_rm_request_qty.plant_id', 'left');
		$this->db->join('tbl_plant_master AS plant2', 'plant2.id = tbl_rm_request_qty.my_plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_rm_request_qty.employee_id', 'left');
		$this->db->where('tbl_rm_request_qty.is_deleted', '0');
		if (!$this->_user_is_unrestricted_access_user()) {
			if ($according_plant == '1') {
				$this->_apply_assigned_plants_scope('tbl_rm_request_qty.plant_id');
			} else {
				$this->_apply_assigned_plants_scope('tbl_rm_request_qty.my_plant_id');
			}
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_rm_request_qty.request_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_rm_request_qty.request_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_rm_request_qty.request_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('request_order_status') != "") {
			$this->db->where('tbl_rm_request_qty.request_status', $this->input->post('request_order_status'));
		}
		if ($this->input->post('request_type') != "") {
			$this->db->where('tbl_rm_request_qty.request_type', $this->input->post('request_type'));
		}
		if ($this->input->post('request_for') != "") {
			$this->db->where('tbl_rm_request_qty.is_article_or_rm_material', $this->input->post('request_for'));
		}
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_rm_request_qty.plant_id', $this->input->post('plant_id'));
		}
		$this->db->order_by('tbl_rm_request_qty.id', 'DESC');
		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_rm_request_qty');
		$this->db->where('is_deleted', '0');
		if (!$this->_user_is_unrestricted_access_user()) {
			if ($according_plant == '1') {
				$this->_apply_assigned_plants_scope('tbl_rm_request_qty.plant_id');
			} else {
				$this->_apply_assigned_plants_scope('tbl_rm_request_qty.my_plant_id');
			}
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_rm_request_qty.request_date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_rm_request_qty.request_date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_rm_request_qty.request_date)', date("Y-m-d", strtotime($exp[0])));
			}
		}
		if ($this->input->post('request_order_status') != "") {
			$this->db->where('tbl_rm_request_qty.request_status', $this->input->post('request_order_status'));
		}
		if ($this->input->post('request_type') != "") {
			$this->db->where('tbl_rm_request_qty.request_type', $this->input->post('request_type'));
		}
		if ($this->input->post('request_for') != "") {
			$this->db->where('tbl_rm_request_qty.is_article_or_rm_material', $this->input->post('request_for'));
		}
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_rm_request_qty.plant_id', $this->input->post('plant_id'));
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_inward_form_data_list()
	{
		$inward_id = $this->input->post('inward_id');
		$this->db->select('tbl_inward_order_data.*, tbl_rm_master.rm_name, tbl_mb_master.name as mb_name, tbl_uom_master.uom_name,tbl_plant_master.plant_name');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_inward_order_data.raw_material_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_inward_order_data.master_batch_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_inward_order_data.uom_id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_inward_order_data.plant_id', 'left');
		$this->db->where('tbl_inward_order_data.database_inward_id', $inward_id);
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->_apply_assigned_plants_scope('tbl_inward_order_data.plant_id');
		$this->db->order_by('tbl_inward_order_data.id', 'DESC');
		$result = $this->db->get();
		return $result->result();
	}

	public function get_all_inward_form_data_list_count()
	{
		$inward_id = $this->input->post('inward_id');
		$this->db->select('tbl_inward_order_data.*, tbl_rm_master.rm_name,tbl_mb_master.name as mb_name, tbl_uom_master.uom_name');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_inward_order_data.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_inward_order_data.uom_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_inward_order_data.master_batch_id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_inward_order_data.plant_id', 'left');
		$this->db->where('tbl_inward_order_data.database_inward_id', $inward_id);
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->_apply_assigned_plants_scope('tbl_inward_order_data.plant_id');
		$result = $this->db->get();
		return $result->num_rows();
	}
	public function get_inward_supplier_name($inward_id)
	{
		$this->db->select('tbl_inward.party_id, tbl_customers.party_name');
		$this->db->from('tbl_inward');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
		$this->db->where('tbl_inward.id', $inward_id);
		$this->db->where('tbl_inward.is_deleted', '0');
		$result = $this->db->get();
		return $result->row()->party_name;
	}
	public function get_all_material_qty_request_data_list()
	{
		$request_id = $this->input->post('request_id');
		$this->db->select('tbl_request_rm_qty_data.*, tbl_rm_master.rm_name, tbl_uom_master.uom_name,tbl_mb_master.name as mb_name, tbl_mould_parts.article_name,user_data.first_name as approved_by_name');
		$this->db->from('tbl_request_rm_qty_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_request_rm_qty_data.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_request_rm_qty_data.uom_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_request_rm_qty_data.master_batch_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_request_rm_qty_data.article_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_request_rm_qty_data.approved_by', 'left');
		$this->db->where('tbl_request_rm_qty_data.database_request_id', $request_id);
		$this->db->where('tbl_request_rm_qty_data.is_deleted', '0');
		$this->db->order_by('tbl_request_rm_qty_data.id', 'DESC');
		$result = $this->db->get();
		return $result->result();
	}

	public function get_all_material_qty_request_data_list_count()
	{
		$request_id = $this->input->post('request_id');
		$this->db->select('tbl_request_rm_qty_data.*');
		$this->db->from('tbl_request_rm_qty_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_request_rm_qty_data.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_request_rm_qty_data.uom_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_request_rm_qty_data.master_batch_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_request_rm_qty_data.article_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_request_rm_qty_data.approved_by', 'left');
		$this->db->where('tbl_request_rm_qty_data.database_request_id', $request_id);
		$this->db->where('tbl_request_rm_qty_data.is_deleted', '0');
		$result = $this->db->get();
		return $result->num_rows();
	}

	private function apply_search_filter($search)
	{
		$this->db->group_start();
		$this->db->or_like('inward_no', $search);
		$this->db->or_like('tbl_customers.party_name', $search);
		$this->db->or_like('inward_date', $search);
		$this->db->or_like('gate_entry_no', $search);
		$this->db->or_like('gate_entry_date', $search);
		$this->db->or_where('tbl_inward.party_id IS NULL');
		$this->db->group_end();
	}

	public function get_sub_hose_pipe_list($length, $start, $search)
	{
		$this->db->select('tbl_hose_pipe.*');
		$this->db->from('tbl_hose_pipe');
		$this->db->where('tbl_hose_pipe.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('hose_pipe', $search);
			$this->db->group_end();
		}
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();
		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_hose_pipe');
		$this->db->where('is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('hose_pipe', $search);
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;
		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_machine_data()
	{
		$this->db->select('tbl_machine_master.*,tbl_plant_master.plant_name');
		$this->db->from('tbl_machine_master');
		$this->db->join('tbl_plant_master', 'tbl_machine_master.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		$this->db->where('tbl_machine_master.status', '1');
		$this->db->where_in('tbl_machine_master.department_id', ['2', '3', '6', '14']);
		if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_machine_master.plant_id', $this->session->userdata('assign_plant_id'));
		}
		$result = $this->db->get();
		return $result->result();
	}
	public function get_article_data($group_id = null)
	{
		$this->db->select(['tbl_mould_parts.id', 'tbl_mould_parts.article_name']);
		$this->db->from('tbl_mould_parts');

		if (!empty($group_id)) {
			if (!is_array($group_id)) {
				$group_id = explode(',', $group_id);
			}

			// Ensure only numeric group IDs are used
			$group_id = array_filter($group_id, 'is_numeric');

			if (!empty($group_id)) {
				$this->db->where_in('tbl_mould_parts.group_of_article_id', $group_id);
			}
		}

		$this->db->where('tbl_mould_parts.is_deleted', '0');
		$this->db->where('tbl_mould_parts.status', '1');
		$query = $this->db->get();
		return $query->result();
	}


	public function get_raw_material_data()
	{
		$this->db->select(['id', 'rm_name']);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_master');
		return $result->result();
	}

	public function get_rejection_data()
	{
		$this->db->select(['id', 'rm_name']);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_rejection');
		return $result->result();
	}

	public function get_master_batch_data()
	{
		$this->db->select(['id', 'name']);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mb_master');
		return $result->result();
	}

	public function get_production_article_data()
	{
		$id = $this->input->post('id');

		$this->db->select('pr.article_id');
		$this->db->from('tbl_production_report pr');
		$this->db->where('pr.id', $id);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$row = $query->row();
			$article_ids = $row->article_id;

			if (!empty($article_ids)) {
				$article_ids_array = explode(',', $article_ids);

				$this->db->select('mp.id as article_id, mp.article_name');
				$this->db->from('tbl_mould_parts mp');
				$this->db->where_in('mp.id', $article_ids_array);
				$this->db->order_by('FIELD(mp.id, ' . implode(',', array_map('intval', $article_ids_array)) . ')');
				$query = $this->db->get();

				if ($query->num_rows() > 0) {
					return $query->result_array();
				}
			}
		}

		return [];
	}


	public function get_production_article_images_data($production_id)
	{
		$this->db->select([
			'mp.id            AS article_id',
			'mp.article_name  AS article_name',
			'pm.image_names   AS image_name'
		]);
		$this->db->from('tbl_mould_parts mp');
		$this->db->join(
			'tbl_production_report pr',
			"FIND_IN_SET(mp.id, pr.article_id) > 0 AND pr.id = {$production_id}",
			'inner'
		);
		$this->db->join(
			'tbl_production_images pm',
			"pm.production_id = pr.id AND pm.article_id = mp.id",
			'left'
		);
		$result = $this->db->get();
		return $result->result_array();
	}
	public function set_production_report()
	{
		$article_id = '';
		if (isset($_POST['article_id_temp'])) {
			if (is_array($_POST['article_id_temp'])) {
				$article_id = implode(',', array_unique(array_map('trim', array_map('htmlspecialchars', $_POST['article_id_temp']))));
			} elseif (is_string($_POST['article_id_temp']) && !empty($_POST['article_id_temp'])) {
				$article_id = implode(',', array_unique(array_filter(array_map('trim', array_map('htmlspecialchars', explode(',', $_POST['article_id_temp']))))));
			}
		}
		$raw_materials = isset($_POST['raw_material_id']) ? $_POST['raw_material_id'] : '';
		if (is_array($raw_materials)) {
			$raw_materials = implode(',', array_filter(array_map('trim', $raw_materials)));
		} elseif (is_string($raw_materials) && !empty($raw_materials)) {
			$raw_materials = implode(',', array_filter(array_map('trim', explode(',', $raw_materials))));
		}
		$raw_materials = isset($_POST['raw_material_id']) ? $_POST['raw_material_id'] : '';
		if (is_array($raw_materials)) {
			$raw_materials = implode(',', array_filter(array_map('trim', $raw_materials)));
		} elseif (is_string($raw_materials) && !empty($raw_materials)) {
			$raw_materials = implode(',', array_filter(array_map('trim', explode(',', $raw_materials))));
		}

		$master_batch_id = isset($_POST['master_batch_id']) ? $_POST['master_batch_id'] : '';
		if (is_array($master_batch_id)) {
			$master_batch_id = implode(',', array_filter(array_map('trim', $master_batch_id)));
		} elseif (is_string($master_batch_id) && !empty($master_batch_id)) {
			$master_batch_id = implode(',', array_filter(array_map('trim', explode(',', $master_batch_id))));
		}

		$rejection_id = isset($_POST['rejection_id']) ? $_POST['rejection_id'] : '';
		if (is_array($rejection_id)) {
			$rejection_id = implode(',', array_filter(array_map('trim', $rejection_id)));
		} elseif (is_string($rejection_id) && !empty($rejection_id)) {
			$rejection_id = implode(',', array_filter(array_map('trim', explode(',', $rejection_id))));
		}

		$data = [
			'production_date' => date("Y-m-d H:i:s", strtotime($this->input->post('production_date'))),
			'machine_id' => $this->input->post('machine_id'),
			'article_group_id' => isset($_POST['article_group_ordered']) ? $_POST['article_group_ordered'] : '',
			'article_id' => $article_id,     
			'raw_material_id' => $raw_materials,
			'master_batch_id' => $master_batch_id,
			'rejection_id' => $rejection_id,
			'day_shift_op1' => $this->input->post('day_shift_op1'),
			'day_shift_op2' => $this->input->post('day_shift_op2'),
			'night_shift_op1' => $this->input->post('night_shift_op1'),
			'night_shift_op2' => $this->input->post('night_shift_op2'),
		];
		//echo"<pre>";print_r($data);exit;
		if ($this->input->post('id') == '') {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_production_report', $data);
			return $this->db->affected_rows() > 0;
		} else {
			$id = $this->uri->segment(2);
			$this->db->where('id', $id);
			$this->db->update('tbl_production_report', $data);
			return $this->db->affected_rows() >= 0;
		}
	}
	public function get_single_production_report()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_production_report');
		return $result->row();
	}
	// public function get_all_production_report_list($length, $start, $search)
	// {
	// 	$this->db->select([
	// 		'pr.id',
	// 		'pr.production_date',
	// 		'pr.machine_id',
	// 		'pr.production_date',
	// 		'pr.remark',
	// 		'tbl_article_production_details.production_id',
	// 		'mm.machine_name',
	// 		'GROUP_CONCAT(DISTINCT ga.group_of_article ORDER BY FIELD(ga.id, ' . implode(',', array_map('intval', explode(',', $pr->article_group_id))) . ') ) AS article_group',
	// 		'GROUP_CONCAT(DISTINCT mp.article_name ORDER BY FIELD(mp.id, ' . implode(',', array_map('intval', explode(',', $pr->article_id))) . ') ) AS article_names',
	// 		'GROUP_CONCAT(DISTINCT rm.rm_name ORDER BY FIELD(rm.id, ' . implode(',', array_map('intval', explode(',', $pr->raw_material_id))) . ') ) AS raw_material_names',
	// 		'GROUP_CONCAT(DISTINCT mb.name ORDER BY FIELD(mb.id, ' . implode(',', array_map('intval', explode(',', $pr->master_batch_id))) . ') ) AS master_batch_names',
	// 		'GROUP_CONCAT(DISTINCT rj.rm_name ORDER BY FIELD(rj.id, ' . implode(',', array_map('intval', explode(',', $pr->rejection_id))) . ') ) AS rejection_names',
	// 		'pr.status',
	// 		'pr.is_deleted',
	// 		'pr.created_on',
	// 		'(SELECT COUNT(*) FROM tbl_production_images i WHERE i.production_id = pr.id) AS image_count'
	// 	]);

	// 	$this->db->from('tbl_production_report pr');
	// 	$this->db->join('tbl_machine_master mm', 'pr.machine_id = mm.id', 'left');
	// 	$this->db->join('tbl_group_of_article ga', 'FIND_IN_SET(ga.id, pr.article_group_id)', 'left');
	// 	$this->db->join('tbl_mould_parts mp', 'FIND_IN_SET(mp.id, pr.article_id)', 'left');
	// 	$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, pr.raw_material_id)', 'left');
	// 	$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, pr.master_batch_id)', 'left');
	// 	$this->db->join('tbl_rm_rejection rj', 'FIND_IN_SET(rj.id, pr.rejection_id)', 'left');
	// 	$this->db->join('tbl_article_production_details', 'tbl_article_production_details.production_id = pr.id', 'left');
	// 	$this->db->group_by('pr.id');
	// 	$this->db->where('pr.is_deleted', '0');

	// 	if (!empty($search)) {
	// 		$this->db->group_start();
	// 		$this->db->like('pr.production_date', $search);
	// 		$this->db->or_like('mm.machine_name', $search);
	// 		$this->db->or_like('mp.article_name', $search);
	// 		$this->db->or_like('rm.rm_name', $search);
	// 		$this->db->or_like('mb.name', $search);
	// 		$this->db->or_like('rj.rm_name', $search);
	// 		$this->db->or_like('ga.group_of_article', $search);
	// 		$this->db->group_end();
	// 	}
	// 	if ($this->input->post('pending_approved') != "") {
	// 		$this->db->where('pr.id', 'tbl_article_production_details.production_id');
	// 		$this->db->where('tbl_article_production_details.status IS NULL OR tbl_article_production_details.status = ""');
	// 		$this->db->group_by('tbl_article_production_details.production_id');
	// 		$this->db->where_not_in('tbl_article_production_details.status', ['0', '1']);
	// 	}
	// 	if ($this->input->post('search_date') != "") {
	// 		$exp = explode("to", $this->input->post('search_date'));
	// 		if (isset($exp[0]) && isset($exp[1])) {
	// 			$this->db->where('DATE(pr.production_date) >=', date("Y-m-d", strtotime($exp[0])));
	// 			$this->db->where('DATE(pr.production_date) <=', date("Y-m-d", strtotime($exp[1])));
	// 		} else if (isset($exp[0])) {
	// 			$this->db->where('DATE(pr.production_date)', date("Y-m-d", strtotime($exp[0])));
	// 		}
	// 	}
	// 	if ($this->input->post('raw_material_id') != "") {
	// 		$this->db->where('pr.raw_material_id', $this->input->post('raw_material_id'));
	// 	}
	// 	if ($this->input->post('master_batch_id') != "") {
	// 		$this->db->where("FIND_IN_SET(" . intval($this->input->post('master_batch_id')) . ", pr.master_batch_id) >", 0);
	// 	}
	// 	if ($this->input->post('article_id') != "") {
	// 		$this->db->where('pr.article_id', $this->input->post('article_id'));
	// 	}


	// 	// Sort by production report ID in descending order
	// 	$this->db->order_by('pr.id', 'DESC');

	// 	// Limit the number of rows returned
	// 	if ($length > 0) { $this->db->limit($length, $start); }

	// 	$result = $this->db->get()->result();
	// 	return $result;
	// }

	public function get_all_production_report_list($length, $start, $search, $operator_id = '', $operator_name = '')
	{
		$this->db->select([
			'pr.id',
			'pr.production_date',
			'pr.machine_id',
			'pr.remark',
			'pr.created_on',
			'pr.day_shift_operators',
			'pr.night_shift_operators',

			'mm.machine_name',

			'GROUP_CONCAT(DISTINCT ga.group_of_article ORDER BY ga.id) AS article_group',
			'GROUP_CONCAT(DISTINCT mp.article_name ORDER BY mp.id) AS article_names',
			'GROUP_CONCAT(DISTINCT rm.rm_name ORDER BY rm.id) AS raw_material_names',
			'GROUP_CONCAT(DISTINCT mb.name ORDER BY mb.id) AS master_batch_names',
			'GROUP_CONCAT(DISTINCT rj.rm_name ORDER BY rj.id) AS rejection_names',

			'(SELECT COUNT(*) FROM tbl_production_images i WHERE i.production_id = pr.id) AS image_count'
		]);

		$this->db->from('tbl_production_report pr');
		$this->db->join('tbl_machine_master mm', 'pr.machine_id = mm.id', 'left');
		$this->db->join('tbl_group_of_article ga', 'FIND_IN_SET(ga.id, pr.article_group_id)', 'left');
		$this->db->join('tbl_mould_parts mp', 'FIND_IN_SET(mp.id, pr.article_id)', 'left');
		$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, pr.raw_material_id)', 'left');
		$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, pr.master_batch_id)', 'left');
		$this->db->join('tbl_rm_rejection rj', 'FIND_IN_SET(rj.id, pr.rejection_id)', 'left');

		$this->db->where('pr.is_deleted', '0');
		$this->_apply_assigned_plants_scope('mm.plant_id');
		$this->db->group_by('pr.id');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('pr.production_date', $search);
			$this->db->or_like('mm.machine_name', $search);
			$this->db->or_like('ga.group_of_article', $search);
			$this->db->or_like('mp.article_name', $search);
			$this->db->or_like('rm.rm_name', $search);
			$this->db->or_like('mb.name', $search);
			$this->db->or_like('rj.rm_name', $search);
			$this->db->group_end();
		}

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(pr.production_date) >=', date('Y-m-d', strtotime($exp[0])));
				$this->db->where('DATE(pr.production_date) <=', date('Y-m-d', strtotime($exp[1])));
			} else {
				$this->db->where('DATE(pr.production_date)', date('Y-m-d', strtotime($exp[0])));
			}
		}

		if ($this->input->post('raw_material_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('raw_material_id')) . ", pr.raw_material_id) > 0"
			);
		}

		if ($this->input->post('master_batch_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('master_batch_id')) . ", pr.master_batch_id) > 0"
			);
		}

		if ($this->input->post('article_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('article_id')) . ", pr.article_id) > 0"
			);
		}

		$this->apply_production_operator_filter($operator_id, $operator_name);

		$this->db->order_by('pr.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		return $this->db->get()->result();
	}





	public function get_all_production_report_list_count($search, $operator_id = '', $operator_name = '')
	{
		$this->db->select('pr.id');
		$this->db->from('tbl_production_report pr');

		$this->db->join('tbl_machine_master mm', 'pr.machine_id = mm.id', 'left');
		$this->db->join('tbl_group_of_article ga', 'FIND_IN_SET(ga.id, pr.article_group_id)', 'left');
		$this->db->join('tbl_mould_parts mp', 'FIND_IN_SET(mp.id, pr.article_id)', 'left');
		$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, pr.raw_material_id)', 'left');
		$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, pr.master_batch_id)', 'left');
		$this->db->join('tbl_rm_rejection rj', 'FIND_IN_SET(rj.id, pr.rejection_id)', 'left');

		$this->db->where('pr.is_deleted', '0');
		$this->_apply_assigned_plants_scope('mm.plant_id');
		$this->db->group_by('pr.id');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('pr.production_date', $search);
			$this->db->or_like('mm.machine_name', $search);
			$this->db->or_like('ga.group_of_article', $search);
			$this->db->or_like('mp.article_name', $search);
			$this->db->or_like('rm.rm_name', $search);
			$this->db->or_like('mb.name', $search);
			$this->db->or_like('rj.rm_name', $search);
			$this->db->group_end();
		}

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(pr.production_date) >=', date('Y-m-d', strtotime($exp[0])));
				$this->db->where('DATE(pr.production_date) <=', date('Y-m-d', strtotime($exp[1])));
			} else {
				$this->db->where('DATE(pr.production_date)', date('Y-m-d', strtotime($exp[0])));
			}
		}

		if ($this->input->post('raw_material_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('raw_material_id')) . ", pr.raw_material_id) > 0"
			);
		}

		if ($this->input->post('master_batch_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('master_batch_id')) . ", pr.master_batch_id) > 0"
			);
		}

		if ($this->input->post('article_id') != "") {
			$this->db->where(
				"FIND_IN_SET(" . intval($this->input->post('article_id')) . ", pr.article_id) > 0"
			);
		}

		$this->apply_production_operator_filter($operator_id, $operator_name);

		return $this->db->get()->num_rows();
	}

	private function apply_production_operator_filter($operator_id, $operator_name = '')
	{
		$operator_id = intval($operator_id);
		if ($operator_id <= 0) {
			return;
		}

		$operator_name = trim((string)$operator_name);
		if ($operator_name === '' || strtolower($operator_name) === 'select operator') {
			return;
		}

		$parts = preg_split('/\s+/', $operator_name);
		$first_name = isset($parts[0]) ? trim((string)$parts[0]) : '';
		$last_name = isset($parts[1]) ? trim((string)$parts[1]) : '';

		if ($first_name === '' && $last_name === '') {
			return;
		}

		$this->db->group_start();
		if ($first_name !== '') {
			$this->db->like('pr.remark', $first_name);
		}
		if ($last_name !== '') {
			if ($first_name !== '') {
				$this->db->or_like('pr.remark', $last_name);
			} else {
				$this->db->like('pr.remark', $last_name);
			}
		}
		$this->db->group_end();
	}


	public function set_uploded_production_images($data)
	{
		$data['created_on'] = date('Y-m-d H:i:s');
		return $this->db->insert('tbl_production_images', $data);
	}

	public function set_uploded_production_image($data)
	{
		$this->db->insert('tbl_production_form_image', $data);
		return '1';
	}
	public function update_production_images($article_id, $images)
	{
		$this->db->where('article_id', $article_id);
		$existing_rows = $this->db->get('tbl_production_images')->result_array();

		$i = 0;
		foreach ($existing_rows as $row) {
			if (isset($images[$i])) {
				$this->db->where('id', $row['id'])
					->update('tbl_production_images', ['image_names' => $images[$i]]);
				if ($this->db->affected_rows() === 0) {
					log_message('error', 'Failed to update image for ID: ' . $row['id']);
				}
				$i++;
			}
		}

		while ($i < count($images)) {
			$this->db->insert('tbl_production_images', [
				'article_id' => $article_id,
				'image_names' => $images[$i]
			]);
			if (!$this->db->insert_id()) {
				log_message('error', 'Failed to insert image: ' . $images[$i]);
			}
			$i++;
		}
		return true;
	}

	public function fetch_production_images($article_id)
	{
		return $this->db
			->select('image_names')
			->where('article_id', $article_id)
			->get('tbl_production_images')
			->result_array();
	}


	public function set_remark()
	{
		$production_id = $this->input->post('id');
		$data = array(
			'remark' => $this->input->post('remark'),
		);
		$this->db->where('id', $production_id);
		$this->db->update('tbl_production_report', $data);
		return '1';
	}

	public function get_article_data_by_id()
	{
		$this->db->select('article_id');
		$this->db->where('id', $this->uri->segment(2));
		$result = $this->db->get('tbl_production_report')->row_array();

		$result_ids = $result['article_id']; // e.g., "28,27"
		$result_ids_array = explode(',', $result_ids);
		if (!empty($result_ids_array)) {
			$this->db->where_in('id', $result_ids_array);

			$order = implode(',', $result_ids_array);
			$this->db->order_by("FIELD(id, $order)", '', false);
		}
		$this->db->where('is_deleted', '0');
		// $this->db->where('status', '1');
		$query = $this->db->get('tbl_mould_parts');
		return $query->result();
	}


	public function get_raw_material_data_by_id()
	{
		$production_id = $this->uri->segment(2);
		if (empty($production_id)) {
			return array();
		}
		$this->db->select('tbl_production_report.*, tbl_mould_parts.article_name, tbl_production_report.article_id');
		$this->db->from('tbl_production_report');
		$this->db->join('tbl_mould_parts', 'tbl_production_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_report.id', $production_id);
		$result = $this->db->get();

		if ($result->num_rows() == 0) {
			return array();
		}

		$row = $result->row_array();
		$article_name = $row['article_name'];
		$article_id = $row['article_id'];

		if (empty($row['raw_material_id'])) {
			return array();
		}
		$raw_material_ids = explode(',', $row['raw_material_id']);

		if (!empty($raw_material_ids)) {
			$this->db->where_in('id', $raw_material_ids);
			$order = implode(',', $raw_material_ids);
			$this->db->order_by("FIELD(id, $order)", '', false);
		}

		$query = $this->db->get('tbl_rm_master');
		$raw_materials = $query->result();

		foreach ($raw_materials as $rm) {
			$rm->article_name = $article_name;
			$rm->article_id = $article_id;
		}

		return $raw_materials;
	}

	public function get_master_batch_data_by_id()
	{
		$production_id = $this->uri->segment(2);
		if (empty($production_id)) {
			return array();
		}
		$this->db->select('tbl_production_report.*, tbl_mould_parts.article_name, tbl_production_report.article_id');
		$this->db->from('tbl_production_report');
		$this->db->join('tbl_mould_parts', 'tbl_production_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_report.id', $production_id);
		$result = $this->db->get();

		if ($result->num_rows() == 0) {
			return array();
		}
		$row = $result->row_array();
		$article_name = $row['article_name'];
		$article_id = $row['article_id'];
		if (empty($row['master_batch_id'])) {
			return array();
		}
		$master_batch_ids = explode(',', $row['master_batch_id']);
		if (!empty($master_batch_ids)) {
			$this->db->where_in('id', $master_batch_ids);
			$order = implode(',', $master_batch_ids);
			$this->db->order_by("FIELD(id, $order)", '', false);
		}

		$query = $this->db->get('tbl_mb_master');
		$master_batch = $query->result();

		foreach ($master_batch as $mb) {
			$mb->article_name = $article_name;
			$mb->article_id = $article_id;
		}

		return $master_batch;
	}

	public function get_balance_quantity_data_by_id()
	{
		$production_id = $this->uri->segment(2);
		if (empty($production_id)) {
			return array();
		}
		$this->db->select('raw_material_id, master_batch_id');
		$this->db->from('tbl_production_report');
		$this->db->where('id', $production_id);
		$result = $this->db->get();
		if ($result->num_rows() == 0) {
			return array();
		}
		$row = $result->row_array();
		$data = array('raw_materials' => [], 'master_batches' => []);
		if (!empty($row['raw_material_id'])) {
			$raw_material_ids = explode(',', $row['raw_material_id']);
			$this->db->select('id, rm_name');
			$this->db->from('tbl_rm_master');
			$this->db->where_in('id', $raw_material_ids);
			$query = $this->db->get();
			$data['raw_materials'] = $query->result();
		}
		if (!empty($row['master_batch_id'])) {
			$master_batch_ids = explode(',', $row['master_batch_id']);
			$this->db->select('id, name');
			$this->db->from('tbl_mb_master');
			$this->db->where_in('id', $master_batch_ids);
			$query = $this->db->get();
			$data['master_batches'] = $query->result();
		}
		return $data;
	}



	public function get_balance_quantity_details($production_id, $raw_material_id, $master_batch_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);

		if (!empty($raw_material_id)) {
			$this->db->where('raw_material_id', $raw_material_id);
		}

		if (!empty($master_batch_id)) {
			$this->db->where('master_batch_id', $master_batch_id);
		}

		$result = $this->db->get('tbl_balance_quantity_production_detail');
		return $result->row();
	}

	public function get_rejection_data_by_id()
	{
		$production_id = $this->uri->segment(2);
		if (empty($production_id)) {
			return array();
		}
		$this->db->select('tbl_production_report.*, tbl_production_report.article_id');
		$this->db->from('tbl_production_report');
		$this->db->join('tbl_mould_parts', 'tbl_production_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_production_report.id', $production_id);
		$result = $this->db->get();

		if ($result->num_rows() == 0) {
			return array();
		}
		$row = $result->row_array();
		$article_id = $row['article_id'];
		if (empty($row['rejection_id'])) {
			return array();
		}
		$rejection_ids = explode(',', $row['rejection_id']);
		if (!empty($rejection_ids)) {
			$this->db->where_in('id', $rejection_ids);
			$order = implode(',', $rejection_ids);
			$this->db->order_by("FIELD(id, $order)", '', false);
		}

		$query = $this->db->get('tbl_rm_rejection');
		$rejection = $query->result();
		foreach ($rejection as $rmj) {
			$rmj->article_id = $article_id;
		}

		return $rejection;
	}

	public function get_article_name_by_id($article_id)
	{
		$this->db->where('id', $article_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$query = $this->db->get('tbl_mould_parts');

		if ($query->num_rows() > 0) {
			return $query->row('article_name');
		}

		return null;
	}

	public function get_article_production_details($production_id, $article_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_article_production_details');
		return $result->row();
	}


	public function get_article_production_detail($production_id, $article_id)
	{
		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$this->db->order_by('created_on', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get('tbl_article_production_details_logs');
		return $result->row();
	}

	public function get_production_summary($production_id, $article_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_production_summary');
		return $result->row();
	}


	public function get_raw_material_production_details($production_id, $raw_material_id, $article_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('raw_material_id', $raw_material_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_raw_material_production_details');
		return $result->row();
	}
	public function get_master_batch_production_details($production_id, $master_batch_id, $article_id)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('master_batch_id', $master_batch_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_master_batch_production_details');
		return $result = $result->row();
	}


	public function get_balance_quantity_production_details_by_data($production_id, $raw_material_id = null, $master_batch_id = null)
	{
		$this->db->where('production_id', $production_id);
		$this->db->where('is_deleted', '0');
		if (!is_null($raw_material_id)) {
			$this->db->where('raw_material_id', $raw_material_id);
		}
		if (!is_null($master_batch_id)) {
			$this->db->where('master_batch_id', $master_batch_id);
		}
		$result = $this->db->get('tbl_balance_quantity_production_details');
		return $result->row_array();
	}
	public function get_master_batch_production_details_by_data($production_id, $master_batch_id)
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('master_batch_id', $master_batch_id);
		$query = $this->db->get('tbl_master_batch_production_details');
		$result = $query->result_array();
		return $result;
	}

	public function get_rejection_production_details($production_id, $rejection_id)
	{
		// $this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('rejection_id', $rejection_id);
		$result = $this->db->get('tbl_rejection_article_list_production_details');
		return $result = $result->row();
	}


	// function set_article_production_details()
	// {
	// 	$qty_data = $this->input->post('qty_data');
	// 	$weight_data = $this->input->post('weight_data');
	// 	if ($qty_data == '0') {
	// 		$qty_data = '';
	// 	}

	// 	$data = array_merge($qty_data, $weight_data);
	// 	$data['approved_qty'] = array_sum($qty_data);
	// 	$nonZeroWeights = array_filter($weight_data, function ($value) {
	// 		return $value != 0;
	// 	});
	// 	$data['average_qty'] = count($nonZeroWeights) > 0 ? array_sum($nonZeroWeights) / count($nonZeroWeights) : '';

	// 	$data['status'] = $this->input->post('status');
	// 	$data['remark'] = $this->input->post('remark');
	// 	$data['production_id'] = $this->input->post('production_id');
	// 	$data['article_id'] = $this->input->post('article_id');

	// 	$this->db->insert('tbl_article_production_details_logs', $data);

	// 	$this->db->where('production_id', $data['production_id']);
	// 	$this->db->where('article_id', $data['article_id']);
	// 	$result = $this->db->get('tbl_article_production_details');

	// 	if ($result->num_rows() > 0) {
	// 		$this->db->where('id', $result->row()->id);
	// 		$this->db->update('tbl_article_production_details', $data);
	// 	} else {
	// 		$data['created_on'] = date('Y-m-d H:i:s');
	// 		$data['machine_id'] = $this->input->post('machine_id');
	// 		$this->db->insert('tbl_article_production_details', $data);
	// 	}
	// 	echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	// }

	// jayesh changes 
	function set_article_production_details()
	{
		$qty_data = $this->input->post('qty_data');
		$weight_data = $this->input->post('weight_data') ?: [];

		$sanitized_qty_data = array_map(function ($val) {
			return is_numeric($val) ? (float) $val : 0;
		}, $qty_data ?: []);

		$data = array_merge($qty_data, $weight_data);

		$data['approved_qty'] = array_sum($sanitized_qty_data);

		$validWeights = array_filter($weight_data, function ($value) {
			return is_numeric(trim($value)) && (float) trim($value) > 0;
		});
		$data['average_qty'] = count($validWeights) > 0 ? array_sum(array_map('floatval', $validWeights)) / count($validWeights) : 0;

		$data['status'] = $this->input->post('status') ?? '1'; 
		$data['remark'] = $this->input->post('remark');
		$data['production_id'] = $this->input->post('production_id');
		$data['article_id'] = $this->input->post('article_id');
		$data['day_shift_op1'] = $this->input->post('day_shift_op1');
		$data['day_shift_op2'] = $this->input->post('day_shift_op2');
		$data['night_shift_op1'] = $this->input->post('night_shift_op1');
		$data['night_shift_op2'] = $this->input->post('night_shift_op2');
		
		// Collect operator names for backward compatibility with reports
		$op_ids = array_filter([$data['day_shift_op1'], $data['day_shift_op2'], $data['night_shift_op1'], $data['night_shift_op2']]);
		if (!empty($op_ids)) {
			$this->db->select('first_name');
			$this->db->where_in('id', $op_ids);
			$op_results = $this->db->get('user_data')->result();
			$op_names = array_map(fn($o) => $o->first_name, $op_results);
			$data['operator_name'] = implode(', ', $op_names);
		}

		$this->db->where('id', $data['production_id']);
		$production_result = $this->db->get('tbl_production_report')->row();

		$this->db->where('id', $production_result->machine_id);
		$existing_machine = $this->db->get('tbl_machine_master')->row();
		$data['plant_id'] = $existing_machine->plant_id ?? null;
		
		// Filter fields safely before inserting to log table to prevent "Unknown column" errors
		$log_fields = $this->db->list_fields('tbl_article_production_details_logs');
		$log_data = array_intersect_key($data, array_flip($log_fields));
		$this->db->insert('tbl_article_production_details_logs', $log_data);

		$this->db->where('production_id', $data['production_id']);
		$this->db->where('article_id', $data['article_id']);
		$result = $this->db->get('tbl_article_production_details');
		

		// FIX: Create stock history whenever approved_qty > 0 (regardless of status field)
		// This ensures production quantities are always counted as inward stock
		// Status field is for approval workflow, not stock accounting
		if ($data['approved_qty'] > 0) {
			// Check if stock history already exists for this production (avoid duplicates)
			$this->db->where('article_id', $data['article_id']);
			$this->db->where('production_id', $data['production_id']);
			$this->db->where('is_inward_outward', '5');
			$existing_history = $this->db->get('tbl_raw_material_stock_report_history')->row();
			
			if (!$existing_history) {
				// Only create new stock history if it doesn't already exist
				$total_qty = $data['approved_qty'];
				$opening_stock = 0;
				$stock_data = array(
					'article_id' => $data['article_id'],
					'total_quantity' => $total_qty,
					'plant_id' => $existing_machine->plant_id,
				);
				$this->db->where('article_id', $data['article_id']);
				$this->db->where('plant_id', $existing_machine->plant_id);
				$this->db->where('is_deleted', '0');
				$existing_stock = $this->db->get('tbl_article_stock_report')->row();
				if ($existing_stock) {
					$opening_stock = $existing_stock->total_quantity;
					$stock_data['total_quantity'] += $existing_stock->total_quantity;
					$total_qty = $stock_data['total_quantity'];
					$this->db->where('id', $existing_stock->id);
					$this->db->update('tbl_article_stock_report', $stock_data);
				} else {
					$stock_data['created_on'] = date('Y-m-d H:i:s');
					$this->db->insert('tbl_article_stock_report', $stock_data);
				}
				$stock_history_data = array(
					'article_id' => $data['article_id'],
					'total_quantity' => $total_qty,
					'opening_stock' => $opening_stock,
					'approved_qty' => $data['approved_qty'],
					'plant_id' => $existing_machine->plant_id,
					'production_id' => $data['production_id'],
					'is_inward_outward' => '5',
					'date' => date('Y-m-d'),
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);
			} else {
				// Stock history exists, just update the approved_qty if it changed
				$this->db->where('article_id', $data['article_id']);
				$this->db->where('production_id', $data['production_id']);
				$this->db->where('is_inward_outward', '5');
				$this->db->update('tbl_raw_material_stock_report_history', array('approved_qty' => $data['approved_qty']));
			}
			// Send notification when stock history is created for production
			if ($data['approved_qty'] > 0) {
				$this->db->select('article_name');
				$this->db->where('id', $data['article_id']);
				$article_name = $this->db->get('tbl_mould_parts')->row()->article_name ?? '';
				// Notification Work when production completed
				$title = 'Production Report Updates';
				$description = $article_name . ' Production Completed at ' . date('d-m-Y H:i:s') . ' updated by ' .
					$this->session->userdata('name');
				$landing_page = 'plant_list';
				$notification_according = '1';//means according department
				$departments = [11, 25, 23]; // 11 = Accounts Department, 25 = admin Department, 23 = sales Department
				$departments_str = implode(',', $departments);
				$notification_data = array(
					'notification_title' => $title,
					'notification_description' => $description,
					'notification_department' => $departments_str,
					'landing_page' => $landing_page,
					'order_id' => $article_name,
					'plant_id' => $existing_machine->plant_id,
					'created_on' => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_notifications', $notification_data);
				$this->send_task_notification_by_token(52, $title, $description, $landing_page, $notification_according, $existing_machine->plant_id);
			}
		}
		$data['machine_id'] = $production_result->machine_id ?? null;
		$data['production_date'] = $production_result->production_date ?? null;

		// Filter fields safely before inserting/updating detail table to prevent "Unknown column" errors
		$detail_fields = $this->db->list_fields('tbl_article_production_details');
		$final_detail_data = array_intersect_key($data, array_flip($detail_fields));

		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_article_production_details', $final_detail_data);
		} else {
			$final_detail_data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_article_production_details', $final_detail_data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}


	public function set_article_remark()
	{

		$data['production_id'] = $this->input->post('production_id');
		$data['article_id'] = $this->input->post('article_id');

		$remarkData = $this->input->post('remark_data');
		if (is_array($remarkData)) {
			$data = array_merge($data, $remarkData);
		}
		$this->db->where('production_id', $data['production_id']);
		$this->db->where('article_id', $data['article_id']);
		$result = $this->db->get('tbl_article_production_details');

		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_article_production_details', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_article_production_details', $data);
		}

		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}
	public function set_raw_material_production_details()
	{
		$data = array(
			'plant_manager_approval_status' => $this->input->post('plant_manager_approval_status'),
			'remark' => $this->input->post('remark'),
			'production_id' => $this->input->post('production_id'),
			'article_id' => $this->input->post('article_id'),
			'raw_material_id' => $this->input->post('raw_material_id'),
			'total_qty' => $this->input->post('total_qty'),
		);
		
		// if ($this->input->post('plant_manager_approval_status') == '0') {
		// 	$total_qty = $data['total_qty'];
		// 	$opening_stock = 0;
		// 	$stock_data = array(
		// 		'raw_material_id' => $data['raw_material_id'],
		// 		'total_quantity' => $total_qty,
		// 		'plant_id' => $this->session->userdata('assign_plant_id'),
		// 	);
		// 	$this->db->where('raw_material_id', $data['raw_material_id']);
		// 	$this->_apply_assigned_plants_scope('plant_id');
		// 	$this->db->where('is_deleted', '0');
		// 	$existing_stock = $this->db->get('tbl_raw_material_stock_report')->row();
		// 	if ($existing_stock) {
		// 		$opening_stock = $existing_stock->total_quantity;
		// 		$stock_data['total_quantity'] = $existing_stock->total_quantity - $stock_data['total_quantity'];
		// 		$total_qty = $stock_data['total_quantity'];
		// 		$this->db->where('id', $existing_stock->id);
		// 		$this->db->update('tbl_raw_material_stock_report', $stock_data);
		// 	}
		// 	$stock_history_data = array(
		// 		'raw_material_id' => $data['raw_material_id'],
		// 		'total_quantity' => $total_qty,
		// 		'opening_stock' => $opening_stock,
		// 		'used_qty' => $data['total_qty'],
		// 		'plant_id' => $this->session->userdata('assign_plant_id'),
		// 		'production_id' => $data['production_id'],
		// 		'is_inward_outward' => '5',
		// 		'date' => date('Y-m-d'),
		// 		'created_on' => date('Y-m-d H:i:s'),
		// 	);
		// 	$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);

		// }

		$this->db->where('production_id', $data['production_id']);
		$this->db->where('article_id', $data['article_id']);
		$this->db->where('raw_material_id', $data['raw_material_id']);
		$result = $this->db->get('tbl_raw_material_production_details');

		$this->db->where('id', $this->input->post('machine_id'));
		$existing_machine = $this->db->get('tbl_machine_master')->row();
		$data['plant_id'] = $existing_machine->plant_id ?? null;
		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_raw_material_production_details', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$data['machine_id'] = $this->input->post('machine_id');
			$this->db->insert('tbl_raw_material_production_details', $data);
		}
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_raw_material_production_details_logs', $data);
		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}

	public function set_master_batch_production_details()
	{
		$data = array(
			'plant_manager_approval_status' => $this->input->post('plant_manager_approval_status'),
			'remark' => $this->input->post('remark'),
			'production_id' => $this->input->post('production_id'),
			'article_id' => $this->input->post('article_id'),
			'master_batch_id' => $this->input->post('master_batch_id'),
			'total_qty' => $this->input->post('total_qty'),
		);
		
		$this->db->where('production_id', $data['production_id']);
		$this->db->where('article_id', $data['article_id']);
		$this->db->where('master_batch_id', $data['master_batch_id']);
		$result = $this->db->get('tbl_master_batch_production_details');

		$this->db->where('id', $this->input->post('machine_id'));
		$existing_machine = $this->db->get('tbl_machine_master')->row();
		$data['plant_id'] = $existing_machine->plant_id ?? null;

		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_master_batch_production_details', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$data['machine_id'] = $this->input->post('machine_id');
			$this->db->insert('tbl_master_batch_production_details', $data);
		}
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_master_batch_production_details_logs', $data);
		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}
	public function set_rejection_production_details()
	{
		$data = array(
			'production_id' => $this->input->post('production_id'),
			'rejection_id' => $this->input->post('rejection_id'),
			'total_qty' => $this->input->post('total_qty'),
			'pc' => $this->input->post('pc'),
			'runner_gms' => $this->input->post('runner_gms'),
			'flash_gm' => $this->input->post('flash_gm'),
			'lumps_gm' => $this->input->post('lumps_gm'),
			'plant_manager_approval_status' => $this->input->post('plant_manager_approval_status'),
			'remark' => $this->input->post('remark'),
		);
		$this->db->where('id', $this->input->post('machine_id'));
		$existing_machine = $this->db->get('tbl_machine_master')->row();
		$data['plant_id'] = $existing_machine->plant_id ?? null;

		if ($this->input->post('plant_manager_approval_status') == '0') {
			$total_qty = $data['total_qty'];
			$uom_id = null;
			$this->db->where('id', $data['rejection_id']);
			$this->db->where('is_deleted', '0');
			$rm_master = $this->db->get('tbl_rm_master')->row();
			if ($rm_master) {
				$uom_id = $rm_master->uom_id;
			}

			$stock_data = array(
				'raw_material_id' => $data['rejection_id'],
				'total_quantity' => $total_qty,
				'plant_id' => $existing_machine->plant_id,
				'uom_id' => $uom_id,
			);
			$this->db->where('raw_material_id', $data['rejection_id']);
			$this->db->where('plant_id', $existing_machine->plant_id);
			$this->db->where('is_deleted', '0');
			$existing_stock = $this->db->get('tbl_raw_material_stock_report')->row();
			if ($existing_stock) {
				$opening_stock = $existing_stock->total_quantity;
				$stock_data['total_quantity'] = $existing_stock->total_quantity + $total_qty;
				$total_qty = $stock_data['total_quantity'];
				$this->db->where('id', $existing_stock->id);
				$this->db->update('tbl_raw_material_stock_report', $stock_data);
			} else {
				$stock_data['created_on'] = date('Y-m-d H:i:s');
				$this->db->insert('tbl_raw_material_stock_report', $stock_data);
			}
			$stock_history_data = array(
				'raw_material_id' => $data['rejection_id'],
				'uom_id' => $uom_id,
				'total_quantity' => $total_qty,
				'opening_stock' => $opening_stock,
				'adjusted_qty' => $data['total_qty'],
				'plant_id' => $existing_machine->plant_id,
				'production_id' => $data['production_id'],
				'is_inward_outward' => '5',
				'date' => date('Y-m-d'),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);

		}
		$this->db->where('production_id', $data['production_id']);
		$this->db->where('rejection_id', $data['rejection_id']);
		$result = $this->db->get('tbl_rejection_article_list_production_details');
		
		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$data['updated_on'] = date('Y-m-d H:i:s');
			$this->db->update('tbl_rejection_article_list_production_details', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$data['machine_id'] = $this->input->post('machine_id');
			$this->db->insert('tbl_rejection_article_list_production_details', $data);
		}
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_rejection_article_list_production_details_logs', $data);
		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}
	public function set_balance_quantity_production_details()
	{
		$data = array(
			'plant_manager_approval_status' => $this->input->post('plant_manager_approval_status'),
			'remark' => $this->input->post('remark'),
			'production_id' => $this->input->post('production_id'),
			'raw_material_id' => $this->input->post('raw_material_id'),
			'master_batch_id' => $this->input->post('master_batch_id'),
			'rm_total_qty' => $this->input->post('rm_total_qty'),
			'mb_total_qty' => $this->input->post('mb_total_qty'),
		);
		
		$this->db->where('production_id', $data['production_id']);
		$this->db->where('raw_material_id', $data['raw_material_id']);
		$this->db->where('master_batch_id', $data['master_batch_id']);
		$result = $this->db->get('tbl_balance_quantity_production_detail');

		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_balance_quantity_production_detail', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_balance_quantity_production_detail', $data);
		}
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_balance_quantity_production_detail_logs', $data);

		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}

	public function set_all_article_summary_details()
	{


		$dataArray = $this->input->post('data');
		$response = [];

		if ($dataArray && is_array($dataArray)) {
			foreach ($dataArray as $data) {
				if (empty($data['article_id'])) {
					continue;
				}
				$data['approved_qty'] = trim($data['approved_qty']);
				$data['average_qty'] = trim($data['average_qty']);
				$data['total_weight'] = trim($data['total_weight']);
				$data['delta'] = trim($data['delta']);
				$data['remark'] = trim($data['remark']);
				$data['machine_id'] = trim($data['machine_id']);
				$data['created_on'] = date('Y-m-d H:i:s');
				$this->db->where('production_id', $data['production_id']);
				$this->db->where('article_id', $data['article_id']);
				$result = $this->db->get('tbl_production_summary');
				
				if ($result->num_rows() > 0) {
					$this->db->where('id', $result->row()->id);
					$data['updated_on'] = date('Y-m-d H:i:s');
					$this->db->update('tbl_production_summary', $data);
				} else {
					$this->db->insert('tbl_production_summary', $data);
				}
				$delta['delta'] = trim($data['delta']);
				$this->db->where('id', $data['production_id']);
				$result = $this->db->get('tbl_production_report');
				if ($result->num_rows() > 0) {
					$this->db->where('id', $data['production_id']);
					$this->db->update('tbl_production_report', $delta);
				}
			}
			$response = ['status' => 'success', 'message' => 'Data saved'];
		} else {
			$response = ['status' => 'error', 'message' => 'Invalid data received'];
		}

		echo json_encode($response);
	}

	public function set_production_remarks()
	{
		$data = array(

			'remarks' => $this->input->post('remarks'),
			'production_id' => $this->input->post('production_id')

		);
		$this->db->where('production_id', $data['production_id']);

		$result = $this->db->get('tbl_production_remarks');
		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_production_remarks', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_production_remarks', $data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Data saved']);
	}

	public function set_production_images($image_names)
	{
		$data = array(

			'image_names' => $image_names,
			'production_id' => $this->input->post('production_id')

		);
		$this->db->where('production_id', $data['production_id']);

		$result = $this->db->get('tbl_production_form_image');
		if ($result->num_rows() > 0) {
			$this->db->where('id', $result->row()->id);
			$this->db->update('tbl_production_form_image', $data);
		} else {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_production_form_image', $data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Image saved']);
	}
	// public function insert_image($data)
	// {
	// 	return $this->db->insert('tbl_production_form_image', $data);
	// }

	public function insert_image($data)
	{
		$this->db->insert('tbl_production_form_image', $data);
		return $this->db->affected_rows() > 0;
	}

	//////////////////////////////////Jayesh///////////////////////////////

	public function get_single_maintenance_list()
	{
		$this->db->select('tbl_maintenance_list.*,user_data.first_name');
		$this->db->from('tbl_maintenance_list');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_list.employee_id', 'left');
		$this->db->where('tbl_maintenance_list.mwo_code', $this->uri->segment(3));
		$this->db->where('tbl_maintenance_list.is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get();
		return $result->row();
	}
	public function get_single_maintenance_list_show()
	{
		$this->db->select('tbl_maintenance_production.*, tbl_plant_master.plant_name,user_data.first_name');
		$this->db->from('tbl_maintenance_production');
		$this->db->join('tbl_plant_master ', 'tbl_plant_master.id = tbl_maintenance_production.plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_production.employee_id', 'left');
		$this->db->where('tbl_maintenance_production.id', $this->uri->segment(2));
		$this->db->where('tbl_maintenance_production.is_deleted', '0');
		$result = $this->db->get();
		return $result->row();
	}
	public function get_all_maintenance_list_details($length, $start, $search)
	{
		$this->db->select('tbl_maintenance_list.*, tbl_plant_master.plant_name,user_data.first_name');
		$this->db->from('tbl_maintenance_list');
		$this->db->join('tbl_plant_master ', 'tbl_plant_master.id = tbl_maintenance_list.plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_list.employee_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_list.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_list.sub_type_id = tbl_mould_parts.id', 'left');

		$this->db->where('tbl_maintenance_list.is_deleted', '0');

		if ($this->input->post('approve_status') != "") {
			// $this->db->where('tbl_maintenance_list.search_status_of_work', '1');
			$this->db->where('tbl_maintenance_list.plant_manager_approval_status', $this->input->post('approve_status'));
		}
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_maintenance_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('search_mwo_code') != "") {
			$this->db->where('tbl_maintenance_list.mwo_code', $this->input->post('search_mwo_code'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('tbl_maintenance_list.status_of_work', $this->input->post('search_status_of_work'));
		}
		if ($this->input->post('search_maintain_action') != "") {
			$this->db->where('tbl_maintenance_list.maintaince', $this->input->post('search_maintain_action'));
		}

		if ($this->input->post('search_sub_category') != "") {
			$subCategory = $this->input->post('search_sub_category');
			$maintainAction = $this->input->post('search_maintain_action');

			if ($maintainAction == '1' || $maintainAction == '3' || $maintainAction == '2' || $maintainAction == '4' || $maintainAction == '5') {
				$this->db->where('tbl_maintenance_list.sub_type_id', $subCategory);
			}
		}
		$this->db->group_by('tbl_maintenance_list.id');

		if (!empty($search)) {

			$this->db->group_start();

			$this->db->or_like('tbl_maintenance_list.date', $search);
			$this->db->or_like('tbl_maintenance_list.mwo_code', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like(' tbl_maintenance_list.status_of_work', $search);
			$this->db->or_like('tbl_maintenance_list.material_used_for_maintenance', $search);
			$this->db->or_like('tbl_maintenance_list.total_labour_hour_involved', $search);
			$this->db->or_like('tbl_maintenance_list.labour_cost_per_hour', $search);
			$this->db->or_like('tbl_maintenance_list.total_cost', $search);
			$this->db->or_like('tbl_maintenance_list.plant_manager_approval_status', $search);
			$this->db->or_like('tbl_maintenance_list.remark_of_plant_manager', $search);
			$this->db->or_like(' tbl_maintenance_list.material_cost', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_maintenance_list.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_total_maintenance_list_details_count()
	{
		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_maintenance_list');
		$this->db->join('tbl_plant_master ', 'tbl_plant_master.id = tbl_maintenance_list.plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_list.employee_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_list.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_list.sub_type_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_maintenance_list.is_deleted', '0');
		$result = $this->db->get()->row();
		return $result->total;
	}

	public function get_all_maintenance_list_details_count($search)
	{
		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_maintenance_list');
		$this->db->join('tbl_plant_master ', 'tbl_plant_master.id = tbl_maintenance_list.plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_list.employee_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_maintenance_list.sub_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_maintenance_list.sub_type_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_maintenance_list.is_deleted', '0');

		if ($this->input->post('approve_status') != "") {
			$this->db->where('tbl_maintenance_list.plant_manager_approval_status', $this->input->post('approve_status'));
		}

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));

			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_list.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_maintenance_list.date) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0]) && !isset($exp[1])) {
				$this->db->where('DATE(tbl_maintenance_list.date)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('search_mwo_code') != "") {
			$this->db->where('tbl_maintenance_list.mwo_code', $this->input->post('search_mwo_code'));
		}

		if ($this->input->post('search_status_of_work') != "") {
			$this->db->where('tbl_maintenance_list.status_of_work', $this->input->post('search_status_of_work'));
		}

		if ($this->input->post('search_material_used_for_maintenance') != "") {
			$this->db->where('tbl_maintenance_list.material_used_for_maintenance', $this->input->post('search_material_used_for_maintenance'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_maintenance_list.date', $search);
			$this->db->or_like('tbl_maintenance_list.mwo_code', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like(' tbl_maintenance_list.status_of_work', $search);
			$this->db->or_like('tbl_maintenance_list.material_used_for_maintenance', $search);
			$this->db->or_like('tbl_maintenance_list.total_labour_hour_involved', $search);
			$this->db->or_like('tbl_maintenance_list.labour_cost_per_hour', $search);
			$this->db->or_like('tbl_maintenance_list.total_cost', $search);
			$this->db->or_like('tbl_maintenance_list.plant_manager_approval_status', $search);
			$this->db->or_like('tbl_maintenance_list.remark_of_plant_manager', $search);
			$this->db->or_like(' tbl_maintenance_list.material_cost', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_maintenance_list.id', 'DESC');
		$result = $this->db->get()->row();
		return $result->total;
	}
	// complete histroy

	public function get_all_complete_maintenance_list_details($mwo_code)
	{
		$this->db->select('tbl_maintenance_list_history.*, tbl_plant_master.plant_name, user_data.first_name');
		$this->db->from('tbl_maintenance_list_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_maintenance_list_history.plant_id', 'left');
		$this->db->join('user_data', 'user_data.id = tbl_maintenance_list_history.employee_id', 'left');
		$this->db->where('tbl_maintenance_list_history.mwo_code', $mwo_code);
		$this->db->where('tbl_maintenance_list_history.is_deleted', '0');
		$this->db->order_by('tbl_maintenance_list_history.id', 'DESC');
		return $this->db->get()->result();
	}

	private function get_latest_maintenance_log_subquery()
	{
		return "
			SELECT ml.*
			FROM tbl_maintenance_list ml
			INNER JOIN (
				SELECT mwo_code, MAX(id) AS max_id
				FROM tbl_maintenance_list
				WHERE is_deleted = '0'
				GROUP BY mwo_code
			) latest ON latest.max_id = ml.id
		";
	}

	private function get_base_maintenance_report_rows($type_actions, $from_date = null, $to_date = null, $type_of_action = null, $machine_id = null, $mold_id = null)
	{
		$type_actions = array_map('intval', (array) $type_actions);
		if (empty($type_actions)) {
			return array();
		}

		$in_clause = implode(',', $type_actions);
		$date_where = '';
		$action_where = '';

		if (!empty($from_date) && !empty($to_date)) {
			$date_where = " AND DATE(mp.date) >= " . $this->db->escape($from_date) . " AND DATE(mp.date) <= " . $this->db->escape($to_date);
		} elseif (!empty($from_date)) {
			$date_where = " AND DATE(mp.date) = " . $this->db->escape($from_date);
		}

		if (!empty($type_of_action) && is_numeric($type_of_action)) {
			$action_where = " AND mp.type_of_action = " . (int) $type_of_action;
		}

		if (!empty($machine_id) && !empty($mold_id)) {
			$action_where .= " AND ( (mp.maintaince IN ('1', '3') AND mp.sub_type_id = " . $this->db->escape($machine_id) . ") OR (mp.maintaince = '2' AND mp.sub_type_id = " . $this->db->escape($mold_id) . ") )";
		} elseif (!empty($machine_id)) {
			$action_where .= " AND mp.maintaince IN ('1', '3') AND mp.sub_type_id = " . $this->db->escape($machine_id);
		} elseif (!empty($mold_id)) {
			$action_where .= " AND mp.maintaince = '2' AND mp.sub_type_id = " . $this->db->escape($mold_id);
		}

		$sql = "
			SELECT
				mp.mwo_code,
				mp.date AS planned_date,
				COALESCE(ml.date, mp.date) AS done_date,
				COALESCE((
					SELECT kd.department
					FROM tbl_krivisha_department kd
					WHERE FIND_IN_SET(kd.id, tech_open.department_id)
					LIMIT 1
				), '-') AS department_name,
				CASE
					WHEN mp.maintaince IN ('1','3') THEN COALESCE(mm.machine_name, '-')
					WHEN mp.maintaince = '4' THEN COALESCE(plant.plant_name, '-')
					ELSE '-'
				END AS machine_name,
				CASE
					WHEN mp.maintaince = '2' THEN COALESCE(mould.article_name, '-')
					ELSE '-'
				END AS mold_name,
				GROUP_CONCAT(DISTINCT prob.problem ORDER BY prob.problem SEPARATOR ', ') AS maintenance_activity,
				CASE
					WHEN ml.status_of_work = '1' THEN 'Completed'
					WHEN ml.status_of_work = '2' THEN 'Pending'
					WHEN ml.status_of_work = '3' THEN 'Reopen'
					WHEN ml.status_of_work = '4' THEN 'Out of Scope'
					ELSE 'Pending'
				END AS work_status,
				COALESCE(tech_open.first_name, '-') AS technician_name,
				COALESCE(CAST(NULLIF(ml.material_cost, '') AS DECIMAL(12,2)), 0) AS material_cost,
				COALESCE(CAST(NULLIF(ml.total_labour_hour_involved, '') AS DECIMAL(12,2)), 0) AS labour_hours,
				COALESCE(CAST(NULLIF(ml.labour_cost_per_hour, '') AS DECIMAL(12,2)), 0) AS labour_rate,
				COALESCE(ml_agg.sum_total_cost, 0) AS total_cost,
				(
					SELECT COUNT(*)
					FROM tbl_maintenance_list fl
					WHERE fl.mwo_code = mp.mwo_code
					AND fl.is_deleted = '0'
				) AS frequency,
				ABS(DATEDIFF(COALESCE(ml.date, mp.date), mp.date)) AS mtbf_days
			FROM tbl_maintenance_production mp
			LEFT JOIN (" . $this->get_latest_maintenance_log_subquery() . ") ml ON ml.mwo_code = mp.mwo_code
			LEFT JOIN (
				SELECT mwo_code,
					SUM(COALESCE(CAST(NULLIF(total_cost, '') AS DECIMAL(12,2)), 0)) AS sum_total_cost
				FROM tbl_maintenance_list
				WHERE is_deleted = '0'
				GROUP BY mwo_code
			) ml_agg ON ml_agg.mwo_code = mp.mwo_code
			LEFT JOIN tbl_machine_master mm ON mm.id = mp.sub_type_id AND mm.is_deleted = '0'
			LEFT JOIN tbl_machine_department md ON md.id = mm.department_id AND md.is_deleted = '0'
			LEFT JOIN tbl_mould_parts mould ON mould.id = mp.sub_type_id AND mould.is_deleted = '0'
			LEFT JOIN tbl_plant_master plant ON plant.id = mp.sub_type_id AND plant.is_deleted = '0'
			LEFT JOIN tbl_maintaince_problems prob ON FIND_IN_SET(prob.id, mp.problem_id) AND prob.is_deleted = '0'
			LEFT JOIN user_data tech_open ON tech_open.id = mp.employee_id
			WHERE mp.is_deleted = '0'
			AND mp.type_of_action IN (" . $in_clause . ")
			" . $date_where . "
			" . $action_where . "
			GROUP BY mp.id
			ORDER BY mp.date DESC, mp.id DESC
		";

		return $this->db->query($sql)->result();
	}

	public function get_preventive_maintenance_report($from_date = null, $to_date = null, $type_of_action = null, $machine_id = null, $mold_id = null)
	{
		return $this->get_base_maintenance_report_rows(array(3), $from_date, $to_date, $type_of_action, $machine_id, $mold_id);
	}

	public function get_breakdown_maintenance_report($from_date = null, $to_date = null, $type_of_action = null, $machine_id = null, $mold_id = null)
	{
		return $this->get_base_maintenance_report_rows(array(1, 2), $from_date, $to_date, $type_of_action, $machine_id, $mold_id);
	}

	public function get_maintenance_cost_report($from_date = null, $to_date = null, $type_of_action = null, $machine_id = null, $mold_id = null)
	{
		$rows = $this->get_base_maintenance_report_rows(array(1, 2, 3, 4, 5, 6), $from_date, $to_date, $type_of_action, $machine_id, $mold_id);
		$grouped = array();

		foreach ($rows as $row) {
			$key = $row->department_name . '||' . $row->machine_name . '||' . $row->mold_name;
			if (!isset($grouped[$key])) {
				$grouped[$key] = (object) array(
					'department_name' => $row->department_name,
					'machine_name' => $row->machine_name,
					'mold_name' => $row->mold_name,
					'total_material_cost' => 0,
					'total_labour_cost' => 0,
					'total_cost' => 0,
				);
			}

			$grouped[$key]->total_material_cost += (float) $row->material_cost;
			$grouped[$key]->total_labour_cost += ((float) $row->labour_hours * (float) $row->labour_rate);
			$grouped[$key]->total_cost += (float) $row->total_cost;
		}

		return array_values($grouped);
	}

	public function get_printing_customer_purchase_history($from_date = null, $to_date = null, $party_id = null)
	{
		$this->db->select("tbl_order_sub_details.order_id, tbl_order_sub_details.order_date, tbl_order_sub_details.order_quantity, tbl_customers.party_name, tbl_brand_master.brand_name, tbl_mould_parts.article_name, (SELECT GROUP_CONCAT(rm_name SEPARATOR ', ') FROM tbl_rm_master WHERE FIND_IN_SET(id, tbl_brand_master.ink_ids)) AS ink_associated, COALESCE((SELECT SUM(d.dispatch_quantity) FROM tbl_dispatch_order_data d WHERE d.order_id = tbl_order_sub_details.order_id AND d.article_id = tbl_order_sub_details.article_id AND d.brand_type_id = tbl_order_sub_details.brand_type_id AND d.is_deleted = '0'), 0) AS dispatch_quantity", false);
		$this->db->from('tbl_order_sub_details');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_order_sub_details.party_id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_brand_master.id = tbl_order_sub_details.brand_type_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_order_sub_details.article_id', 'left');
		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		$this->db->where_in('tbl_order_sub_details.order_department_status', array('2', '3'));

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_order_sub_details.order_date) >=', $from_date);
			$this->db->where('DATE(tbl_order_sub_details.order_date) <=', $to_date);
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_order_sub_details.order_date)', $from_date);
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_order_sub_details.party_id', $party_id);
		}

		$this->db->having('dispatch_quantity > 0', null, false);
		$this->db->order_by('tbl_order_sub_details.order_date', 'DESC');
		$this->db->order_by('tbl_order_sub_details.id', 'DESC');
		return $this->db->get()->result();
	}

	public function get_printing_impression_report($from_date = null, $to_date = null, $party_id = null, $brand_id = null, $article_id = null)
	{
		$this->db->select("tbl_printing_material_report.id, tbl_printing_material_report.created_on, tbl_printing_material_report.order_id, tbl_printing_material_report.order_qty, tbl_printing_material_report.approvd_qty, tbl_printing_material_report.color_job, tbl_printing_material_report.completed_days, tbl_printing_material_report.remark, tbl_customers.party_name, tbl_brand_master.brand_name, tbl_mould_parts.article_name, COALESCE(NULLIF(tbl_printing_material_report.impression_rate, 0), tbl_impression_rate.impression_rate, 0) AS impression_rate, GROUP_CONCAT(DISTINCT CONCAT(tbl_rm_master.rm_name, ' (', COALESCE(tbl_printing_material_inks.quantity, '0'), ')') ORDER BY tbl_rm_master.rm_name SEPARATOR ', ') AS inks_used", false);
		$this->db->from('tbl_printing_material_report');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_printing_material_report.party_id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_brand_master.id = tbl_printing_material_report.brand_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_printing_material_report.article_id', 'left');
		$this->db->join('tbl_impression_rate', 'tbl_impression_rate.article_id = tbl_printing_material_report.article_id AND tbl_impression_rate.is_deleted = "0"', 'left');
		$this->db->join('tbl_printing_material_inks', 'tbl_printing_material_inks.report_id = tbl_printing_material_report.id AND tbl_printing_material_inks.is_deleted = "0"', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_printing_material_inks.ink_id', 'left');
		$this->db->where('tbl_printing_material_report.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_printing_material_report.created_on) >=', $from_date);
			$this->db->where('DATE(tbl_printing_material_report.created_on) <=', $to_date);
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_printing_material_report.created_on)', $from_date);
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_printing_material_report.party_id', $party_id);
		}

		if (!empty($brand_id)) {
			$this->db->where('tbl_printing_material_report.brand_id', $brand_id);
		}

		if (!empty($article_id)) {
			$this->db->where('tbl_printing_material_report.article_id', $article_id);
		}

		$this->db->group_by('tbl_printing_material_report.id');
		$this->db->order_by('tbl_printing_material_report.created_on', 'DESC');
		return $this->db->get()->result();
	}

	public function get_printing_store_issue_report($from_date = null, $to_date = null)
	{
		$where_date = "r.is_deleted = '0'";
		if (!empty($from_date) && !empty($to_date)) {
			$where_date .= " AND DATE(r.created_on) >= '" . $this->db->escape_str($from_date) . "' AND DATE(r.created_on) <= '" . $this->db->escape_str($to_date) . "'";
		} elseif (!empty($from_date)) {
			$where_date .= " AND DATE(r.created_on) = '" . $this->db->escape_str($from_date) . "'";
		}

		$sql = "
			SELECT combined.issue_date, combined.material_name, SUM(combined.qty) as total_qty, COALESCE(ir.rate, 0) as material_rate
			FROM (
				SELECT DATE(r.created_on) as issue_date, m.rm_name as material_name, CAST(NULLIF(r.other_material_qty_1, '') AS DECIMAL(12,2)) as qty, r.other_material as material_id
				FROM tbl_printing_material_report r
				JOIN tbl_rm_master m ON m.id = r.other_material
				WHERE $where_date
				UNION ALL
				SELECT DATE(r.created_on) as issue_date, m.rm_name as material_name, CAST(NULLIF(r.other_material_qty_2, '') AS DECIMAL(12,2)) as qty, r.other_material_two as material_id
				FROM tbl_printing_material_report r
				JOIN tbl_rm_master m ON m.id = r.other_material_two
				WHERE $where_date
				UNION ALL
				SELECT DATE(r.created_on) as issue_date, m.rm_name as material_name, CAST(NULLIF(i.quantity, '') AS DECIMAL(12,2)) as qty, i.ink_id as material_id
				FROM tbl_printing_material_report r
				JOIN tbl_printing_material_inks i ON i.report_id = r.id AND i.is_deleted = '0'
				JOIN tbl_rm_master m ON m.id = i.ink_id
				WHERE $where_date
			) as combined
			LEFT JOIN (
				SELECT raw_material_id, rate 
				FROM tbl_inward_order_data 
				WHERE id IN (SELECT MAX(id) FROM tbl_inward_order_data WHERE is_deleted = '0' GROUP BY raw_material_id)
			) ir ON ir.raw_material_id = combined.material_id
			WHERE combined.qty > 0
			GROUP BY combined.issue_date, combined.material_name
			ORDER BY combined.issue_date DESC, combined.material_name ASC
		";

		return $this->db->query($sql)->result();
	}


	public function get_by_code()
	{
		$code = $this->input->post('mwo_code');
		$this->db->where('mwo_code', $code);
		$this->db->order_by('id', 'DESC');
		$result = $this->db->get('tbl_maintenance_production');
		return $result->row();
	}
	public function get_last_maintenance_list()
	{
		$this->db->where('mwo_code', $this->uri->segment(3));
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get('tbl_maintenance_list');
		return $result->row();
	}

	// get data using particular date
	public function get_data_by_date_rm()
	{
		$production_schedule_start_date = $this->input->post('production_schedule_start_date');
		$this->db->where('production_schedule_start_date', $production_schedule_start_date);
		$result = $this->db->get('tbl_production_schedules');
		return $result->result();
	}

	public function get_all_article_production_details_logs($production_id, $article_id)
	{
		$this->db->select('tbl_article_production_details_logs.*, tbl_mould_parts.article_name');
		$this->db->from('tbl_article_production_details_logs');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_article_production_details_logs.article_id', 'left');
		$this->db->where('tbl_article_production_details_logs.production_id', $production_id);
		$this->db->where('tbl_article_production_details_logs.article_id', $article_id);
		$this->db->where('tbl_article_production_details_logs.is_deleted', '0');
		$this->db->order_by('tbl_article_production_details_logs.id', 'DESC');
		return $this->db->get()->result();
	}



	public function get_all_raw_material_production_details_logs($production_id, $raw_material_id, $article_id)
	{
		$this->db->select('tbl_raw_material_production_details_logs.*, tbl_mould_parts.article_name, tbl_rm_master.rm_name');
		$this->db->from('tbl_raw_material_production_details_logs');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_raw_material_production_details_logs.article_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_production_details_logs.raw_material_id', 'left');
		$this->db->where('tbl_raw_material_production_details_logs.production_id', $production_id);
		$this->db->where('tbl_raw_material_production_details_logs.raw_material_id', $raw_material_id);
		$this->db->where('tbl_raw_material_production_details_logs.article_id', $article_id);
		$this->db->where('tbl_raw_material_production_details_logs.is_deleted', '0');
		$this->db->order_by('tbl_raw_material_production_details_logs.id', 'DESC');
		return $this->db->get()->result();
	}

	public function get_all_master_batch_production_details_logs_ajax($production_id, $master_batch_id, $article_id)
	{
		$this->db->select('tbl_master_batch_production_details_logs.*, tbl_mould_parts.article_name, tbl_mb_master.name');
		$this->db->from('tbl_master_batch_production_details_logs');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_master_batch_production_details_logs.article_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_master_batch_production_details_logs.master_batch_id', 'left');
		$this->db->where('tbl_master_batch_production_details_logs.production_id', $production_id);
		$this->db->where('tbl_master_batch_production_details_logs.master_batch_id', $master_batch_id);
		$this->db->where('tbl_master_batch_production_details_logs.article_id', $article_id);
		$this->db->where('tbl_master_batch_production_details_logs.is_deleted', '0');
		$this->db->order_by('tbl_master_batch_production_details_logs.id', 'DESC');
		return $this->db->get()->result();
	}

	public function get_all_rejection_production_details_logs_ajax($production_id, $rejection_id)
	{
		$this->db->select('tbl_rejection_article_list_production_details_logs.*, tbl_rm_rejection.rm_name');
		$this->db->from('tbl_rejection_article_list_production_details_logs');
		$this->db->join('tbl_rm_rejection', 'tbl_rm_rejection.id = tbl_rejection_article_list_production_details_logs.rejection_id', 'left');
		$this->db->where('tbl_rejection_article_list_production_details_logs.production_id', $production_id);
		$this->db->where('tbl_rejection_article_list_production_details_logs.rejection_id', $rejection_id);
		$this->db->where('tbl_rejection_article_list_production_details_logs.is_deleted', '0');
		$this->db->order_by('tbl_rejection_article_list_production_details_logs.id', 'DESC');
		return $this->db->get()->result();
	}
	public function get_all_balance_quantity_production_details_logs_ajax($production_id, $master_batch_id, $raw_material_id)
	{
		$this->db->select('tbl_balance_quantity_production_detail_logs.*, tbl_rm_master.rm_name, tbl_mb_master.name');
		$this->db->from('tbl_balance_quantity_production_detail_logs');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_balance_quantity_production_detail_logs.raw_material_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_balance_quantity_production_detail_logs.master_batch_id', 'left');
		$this->db->where('tbl_balance_quantity_production_detail_logs.production_id', $production_id);
		$this->db->where('tbl_balance_quantity_production_detail_logs.master_batch_id', $master_batch_id);
		if (!empty($raw_material_id)) {
			$this->db->where('tbl_balance_quantity_production_detail_logs.raw_material_id', $raw_material_id);
		} else {
			$this->db->where('tbl_balance_quantity_production_detail_logs.raw_material_id IS NULL');
		}
		$this->db->where('tbl_balance_quantity_production_detail_logs.is_deleted', '0');
		$this->db->order_by('tbl_balance_quantity_production_detail_logs.id', 'DESC');
		return $this->db->get()->result();
	}
	public function get_single_uom_name()
	{
		$materialId = $this->input->post('materialId');
		$plant_id = $this->input->post('plant_id');
		$this->db->select('tbl_rm_master.*, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_rm_master.uom_id', 'left');
		$this->db->where('tbl_rm_master.id', $materialId);
		$result = $this->db->get()->row();

		$this->db->select('total_quantity');
		$this->db->from('tbl_raw_material_stock_report');
		$this->db->where('tbl_raw_material_stock_report.raw_material_id', $materialId);
		if ($plant_id) {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $plant_id);
		}
		$stock_result = $this->db->get()->row();

		if ($result) {
			echo json_encode([
				'uom_name' => $result->uom_name,
				'uom_id' => $result->uom_id,
				'material_id' => $result->id,
				'total_stock_qty' => $stock_result ? $stock_result->total_quantity : 0
			]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Material not found']);
		}
	}
	public function get_master_batch_stock_qty()
	{
		$master_batch_id = $this->input->post('master_batch_id');
		$plant_id = $this->input->post('plant_id');
		$this->db->select('total_quantity');
		$this->db->from('tbl_master_batch_stock_report');
		$this->db->where('tbl_master_batch_stock_report.master_batch_id', $master_batch_id);
		if ($plant_id) {
			$this->db->where('tbl_master_batch_stock_report.plant_id', $plant_id);
		}
		$stock_result = $this->db->get()->row();

		if ($stock_result) {
			echo json_encode([
				'total_stock_qty' => $stock_result ? $stock_result->total_quantity : 0
			]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'color not found']);
		}
	}
	public function get_article_stock_qty()
	{
		$article_id = $this->input->post('article_id');
		$plant_id = $this->input->post('plant_id');
		$this->db->select('total_quantity');
		$this->db->from('tbl_article_stock_report');
		$this->db->where('tbl_article_stock_report.article_id', $article_id);
		if ($plant_id) {
			$this->db->where('tbl_article_stock_report.plant_id', $plant_id);
		}
		$this->db->where('tbl_article_stock_report.is_deleted', '0');
		$stock_result = $this->db->get()->row();

		if ($stock_result) {
			echo json_encode([
				'total_stock_qty' => $stock_result ? $stock_result->total_quantity : 0
			]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'stock not found']);
		}
	}

	public function set_maintenance_list()
	{
		$mwo_record = $this->Admin_model->get_by_code();
		$cost = $this->input->post('material_cost');
		$status_of_work = $this->input->post('status_of_work');
		$approve_status = $this->input->post('plant_manager_approval_status');
		if ($approve_status != '0' && $approve_status != '1') {
			$approve_status = '2';
		}
		if ($status_of_work == '1' && $approve_status == '0') {
			if ($mwo_record->maintaince == '1' || $mwo_record->maintaince == '3' || $mwo_record->maintaince == '5') {
				$this->db->update('tbl_machine_master', array('status' => '1'), array('id' => $mwo_record->sub_type_id));
			} else if ($mwo_record->maintaince == '2') {
				$this->db->update('tbl_mould_parts', array('status' => '1'), array('id' => $mwo_record->sub_type_id));
			} else {
				$this->db->update('tbl_plant_master', array('status' => '1'), array('id' => $mwo_record->sub_type_id));
			}
			$this->db->update('tbl_maintenance_production', array('status_of_work' => '2'), array('mwo_code' => $mwo_record->mwo_code));
		}
		if ($cost !== '') {
			$data = array(
				'mwo_code' => $mwo_record->mwo_code,
				'sub_type_id' => $mwo_record->sub_type_id,
				'plant_id' => $mwo_record->plant_id,
				'employee_id' => $this->session->userdata('id'),
				'date' => date('Y-m-d'),
				'status_of_work' => $this->input->post('status_of_work'),
				'material_used_for_maintenance' => $this->input->post('material_used_for_maintenance'),
				'material_cost' => $this->input->post('material_cost'),
				'total_labour_hour_involved' => $this->input->post('total_labour_hour_involved'),
				'labour_cost_per_hour' => $this->input->post('labour_cost_per_hour'),
				'plant_manager_approval_status' => $approve_status,
				'remark_of_plant_manager' => $this->input->post('remark_of_plant_manager'),
				'total_cost' => $this->input->post('total_cost'),
				'maintaince' => $mwo_record->maintaince,
				'updated_on' => date('Y-m-d H:i:s'),
			);
		} else {
			$data = array(
				'mwo_code' => $mwo_record->mwo_code,
				'sub_type_id' => $mwo_record->sub_type_id,
				'plant_id' => $mwo_record->plant_id,
				'employee_id' => $mwo_record->employee_id,
				'date' => date('Y-m-d'),
				'status_of_work' => $this->input->post('status_of_work'),
				'plant_manager_approval_status' => $approve_status,
				'remark_of_plant_manager' => $this->input->post('remark_of_plant_manager'),
				'updated_on' => date('Y-m-d H:i:s'),
			);
		}

		$this->db->insert('tbl_maintenance_list_history', $data);
		if ($this->input->post('id') == '') {
			$data['created_on'] = date('Y-m-d H:i:s');
			$data['type_of_action'] = $this->input->post('type_of_action');
			$this->db->insert('tbl_maintenance_list', $data);
			return 1;
		} else {
			$data['type_of_action'] = $this->input->post('type_of_action');
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_maintenance_list', $data);
			return 2;
		}
	}

	public function get_by_article_id_details()
	{
		$production_id = $this->input->post('production_id');
		$article_id = $this->input->post('article_id');
		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_article_production_details');
		return $result->row();
	}


	public function get_article_production_summary($production_id)
	{
		$this->db->select('tbl_article_production_details.*, tbl_mould_parts.article_name');
		$this->db->from('tbl_article_production_details');
		$this->db->join('tbl_mould_parts', 'tbl_article_production_details.article_id = tbl_mould_parts.id', 'left');
		$this->db->where('tbl_article_production_details.production_id', $production_id);
		$query = $this->db->get();
		return $query->result();
	}
	// images
	public function update_production($id, $data)
	{

		if (!empty($id)) {

			$this->db->where('production_id', $id);
			return $this->db->update('tbl_production_form_image', $data);
		} else {

			$data['created_on'] = date('Y-m-d H:i:s');
			return $this->db->insert('tbl_production_form_image', $data);
		}
	}

	public function get_images_by_article_id($articleId)
	{
		return $this->db
			->select('image_names')
			->where('article_id', $articleId)
			->where('is_deleted', 0)
			->get('tbl_article_images')
			->result_array();
	}

	public function get_images_by_article($article_id)
	{
		return $this->db
			->where('article_id', $article_id)
			->get('tbl_production_images')
			->result_array();
	}

	public function insert_production_images($production_id, $article_id, array $filenames)
	{
		foreach ($filenames as $f) {
			$this->db->insert('tbl_production_images', [
				'production_id' => $production_id,
				'article_id' => $article_id,
				'image_names' => $f
			]);
		}
	}
	// Jayesh 6/5/25
	public function set_own_vehicle_details()
	{
		// echo"<pre>";print_r($_POST);exit;
		$vehical_id = $this->input->post('vehical');
		$in_km = (float)$this->input->post('in_km');

		// Fetch the last in_km for this vehicle on the server side
		$last_in_km = 0.0;
		if (!empty($vehical_id)) {
			$this->db->select('in_km');
			$this->db->where('vehical_id', $vehical_id);
			$this->db->where('is_deleted', '0');
			if ($this->input->post('id') != "") {
				$this->db->where('id !=', $this->input->post('id'));
			}
			$this->db->order_by('id', 'DESC');
			$this->db->limit(1);
			$prev = $this->db->get('tbl_own_vehicle_details')->row();
			if ($prev) {
				$last_in_km = (float)$prev->in_km;
			}
		}

		if ($in_km <= $last_in_km) {
			return '0';
		}

		$data = array(
			'vehical_id' => $vehical_id,
			// 'vehical_type' => $type,
			'challan_dc_no' => $this->input->post('challan_dc_no'),
			'invoice_no' => $this->input->post('invoice_no'),
			'invoice_value' => $this->input->post('invoice_value'),
			'location_id' => $this->input->post('location_id'),
			'pincode' => $this->input->post('pincode'),
			'purpose' => str_replace(' ', '', implode(', ', $this->input->post('purpose'))),
			'party_id' => $this->input->post('party_id'),
			'in_km' => $in_km,
			'out_km' => $last_in_km,
			'market_freight' => $this->input->post('market_freight'),
			'diesel_topup' => $this->input->post('diesel_topup'),
			'driver_expense' => $this->input->post('driver_expense'),
			'diesel_rate' => $this->input->post('diesel_rate'),
			'maintenance' => $this->input->post('maintenance'),
			'exact_km' => $this->input->post('exact_km'),
			'diesel_expense' => $this->input->post('diesel_expense'),
			'transport_percent' => $this->input->post('transport_percent'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		// echo"<pre>";
		// print_r($data);
		// exit;
		if (!empty($vehical_id)) {
			$this->db->where('id', $vehical_id);
			$this->db->select('vehical');
			$query = $this->db->get('tbl_vehical');

			if ($query->num_rows() > 0) {
				$result = $query->row();
				if ($result->vehical == 'TRUCK') {
				} else {
				}
			}
		}
		if ($this->input->post('id') == "") {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_own_vehicle_details', $data);
			return '1';
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_own_vehicle_details', $data);
			return '2';
		}
	}

	public function set_new_vehical()
	{
		if ($this->input->post('master_type') == 'vehical') {
			$this->db->where('is_deleted', '0');
			$this->db->where('vehical', $this->input->post('new_option'));
			$result = $this->db->get('tbl_vehical');
			$result = $result->row();
			if (empty($result)) {
				$data = array(
					"vehical" => $this->input->post('new_option'),
					"created_on" => date('Y-m-d H:i:s')
				);
				$this->db->insert('tbl_vehical', $data);
				echo $this->db->insert_id();
			} else {
				echo 0;
			}
		}
	}
	public function get_all_vehical()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_vehical');
		return $result->result();
	}
	public function get_single_own_vehicle_details()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_own_vehicle_details');
		return $result->row();
	}
	public function get_last_out_km_vehicle_details()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get('tbl_own_vehicle_details');
		return $result->row();
	}
	public function get_last_in_km_by_vehicle($vehical_id)
	{
		$this->db->select('in_km');
		$this->db->where('vehical_id', $vehical_id);
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		return $result ? (float)$result->in_km : 0.0;
	}
	public function get_all_vehical_list_details($length, $start, $search)
	{
		$date_range = $this->input->post('search_date');
		$location_id = $this->input->post('location_id');
		$party_id = $this->input->post('party_action');
		$vehical_id = $this->input->post('vehical_id');
		$this->db->select('tbl_own_vehicle_details.*, tbl_vehical.vehical,tbl_location_master.city,tbl_customers.party_name, (SELECT t2.in_km FROM tbl_own_vehicle_details t2 WHERE t2.vehical_id = tbl_own_vehicle_details.vehical_id AND t2.id < tbl_own_vehicle_details.id AND t2.is_deleted = "0" ORDER BY t2.id DESC LIMIT 1) as actual_out_km');
		$this->db->from('tbl_own_vehicle_details');
		$this->db->join('tbl_vehical ', 'tbl_vehical.id = tbl_own_vehicle_details.vehical_id', 'left');
		$this->db->join('tbl_location_master', 'tbl_location_master.id = tbl_own_vehicle_details.location_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_own_vehicle_details.party_id', 'left');
		$this->db->where('tbl_own_vehicle_details.is_deleted', '0');
		$this->db->group_by('tbl_own_vehicle_details.id');
		if (!empty($date_range)) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				$end_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				$this->db->where('(tbl_own_vehicle_details.created_on) >=', $start_date);
				$this->db->where('(tbl_own_vehicle_details.created_on) <=', $end_date);
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				if ($single) {
					$this->db->where('DATE(tbl_own_vehicle_details.created_on)', $single);
				}
			}
		}

		if (!empty($location_id)) {
			$this->db->where('location_id', $location_id);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		if (!empty($vehical_id)) {
			$this->db->where('vehical_id', $vehical_id);
		}
		if (!empty($division_id)) {
			$this->db->where('division_id', $division_id);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_vehical.vehical', $search);
			$this->db->or_like('tbl_own_vehicle_details.challan_dc_no', $search);
			$this->db->or_like('tbl_own_vehicle_details.invoice_no', $search);
			$this->db->or_like('tbl_own_vehicle_details.invoice_value', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_own_vehicle_details.pincode', $search);
			$this->db->or_like(' tbl_own_vehicle_details.purpose', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_own_vehicle_details.in_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.out_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.market_freight', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_topup', $search);
			$this->db->or_like('tbl_own_vehicle_details.driver_expense', $search);
			$this->db->or_like('tbl_own_vehicle_details.maintenance', $search);
			$this->db->or_like('tbl_own_vehicle_details.transport_percent', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_expense', $search);
			$this->db->or_like('tbl_own_vehicle_details.exact_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_rate', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_own_vehicle_details.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$result = $this->db->get();
		return $result->result();
	}
	public function get_total_vehical_list_details_count()
	{
		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_own_vehicle_details');
		$this->db->where('is_deleted', '0');
		$result = $this->db->get()->row();
		return $result->total;
	}
	public function get_all_vehical_list_details_count($search)
	{
		$date_range = $this->input->post('search_date');
		$location_id = $this->input->post('location_id');
		$party_id = $this->input->post('party_action');
		$vehical_id = $this->input->post('vehical_id');
		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_own_vehicle_details');
		$this->db->join('tbl_vehical ', 'tbl_vehical.id = tbl_own_vehicle_details.vehical_id', 'left');
		$this->db->join('tbl_location_master', 'tbl_location_master.id = tbl_own_vehicle_details.location_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_own_vehicle_details.party_id', 'left');
		$this->db->where('tbl_own_vehicle_details.is_deleted', '0');
		if (!empty($date_range)) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
				$end_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				$this->db->where('DATE(tbl_own_vehicle_details.created_on) >=', $start_date);
				$this->db->where('DATE(tbl_own_vehicle_details.created_on) <=', $end_date);
			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				if ($single) {
					$this->db->where('DATE(tbl_own_vehicle_details.created_on)', $single);
				}
			}
		}
		// Other filters
		if (!empty($location_id)) {
			$this->db->where('location_id', $location_id);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		if (!empty($vehical_id)) {
			$this->db->where('vehical_id', $vehical_id);
		}
		if (!empty($division_id)) {
			$this->db->where('division_id', $division_id);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_vehical.vehical', $search);
			$this->db->or_like('tbl_own_vehicle_details.challan_dc_no', $search);
			$this->db->or_like('tbl_own_vehicle_details.invoice_no', $search);
			$this->db->or_like('tbl_own_vehicle_details.invoice_value', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_own_vehicle_details.pincode', $search);
			$this->db->or_like(' tbl_own_vehicle_details.purpose', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_own_vehicle_details.in_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.out_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.market_freight', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_topup', $search);
			$this->db->or_like('tbl_own_vehicle_details.driver_expense', $search);
			$this->db->or_like('tbl_own_vehicle_details.maintenance', $search);
			$this->db->or_like('tbl_own_vehicle_details.transport_percent', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_expense', $search);
			$this->db->or_like('tbl_own_vehicle_details.exact_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_rate', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_own_vehicle_details.id', 'DESC');
		$result = $this->db->get()->row();
		return $result->total;
	}

	public function get_selected_link($id)
	{
		$this->db->where('previlege', $id);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_subprevilege');
		return $result->result();
	}
	public function ensure_task_detail_privilege_links()
	{
		$this->db->select('tp.id');
		$this->db->from('tbl_previleges tp');
		$this->db->join('tbl_subprevilege tsp', 'tsp.previlege = tp.id AND tsp.is_deleted = 0', 'left');
		$this->db->where('tp.is_deleted', '0');
		$this->db->group_start();
		$this->db->where('LOWER(tp.previlege)', 'task management');
		$this->db->or_where('LOWER(tp.previlege)', 'task mangement');
		$this->db->or_where_in('tsp.link', array('manual_task_access', 'auto_task_access', 'task_list', 'auto_task_list', 'add_task'));
		$this->db->group_end();
		$this->db->order_by('tp.id', 'ASC');
		$this->db->limit(1);
		$task_privilege = $this->db->get()->row();
		if (empty($task_privilege)) {
			return;
		}

		$required_links = array(
			'manual_task_reply' => 'Manual Task Details Page',
			'auto_task_reply' => 'Auto Task Details Page',
		);

		foreach ($required_links as $link => $submenu) {
			$this->db->select('id');
			$this->db->where('is_deleted', '0');
			$this->db->where('previlege', $task_privilege->id);
			$this->db->where('link', $link);
			$existing = $this->db->get('tbl_subprevilege')->row();

			if (empty($existing)) {
				$this->db->insert('tbl_subprevilege', array(
					'previlege' => $task_privilege->id,
					'submenu' => $submenu,
					'link' => $link,
					'created_on' => date('Y-m-d H:i:s'),
				));
			}
		}

		// Hide legacy combined details permission to avoid confusion in UI.
		$this->db->where('previlege', $task_privilege->id);
		$this->db->where('link', 'update_auto_manual_task');
		$this->db->where('is_deleted', '0');
		$this->db->update('tbl_subprevilege', array(
			'is_deleted' => '1',
		));
	}
	public function get_active_privileges()
	{
		// i was mark Sub Master Management as is_deleted = 1 because it is now showing client side whenwe need to show it again then we can change is_deleted = 0
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->order_by('previlege', 'ASC');
		$result = $this->db->get('tbl_previleges');
		return $result->result();
	}
	public function get_single_privilege_staff()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $this->uri->segment(2));
		$result = $this->db->get('user_data');
		return $result->row();
	}
	public function get_staff_added_privilege()
	{
		$this->db->where('staff_id', $this->uri->segment(2));
		$result = $this->db->get('tbl_assign_previlege');
		$result = $result->row();
		return $result;
	}
	public function get_previlege()
	{
		$id = $this->session->userdata('id');
		$link = array();
		$this->db->where('staff_id', $id);
		$emp = $this->db->get('tbl_assign_previlege');
		$emp = $emp->row();
		if (!empty($emp)) {
			$exp = explode(',', $emp->previleges);
			$this->db->select('link');
			$this->db->where_in('id', $exp);
			$result = $this->db->get('tbl_subprevilege');
			$result = $result->result();
			if (!empty($result)) {
				foreach ($result as $result_arr) {
					$link[] = $result_arr->link;
				}
			}
		}
		return $link;
	}

	public function add_previleges()
	{
		$data = array(
			'previlege' => $this->input->post('previleges'),
		);
		if ($this->input->post('id') == '') {
			$date = array(
				'created_on' => date('Y-m-d H:i:s'),
			);
			$new_arr = array_merge($data, $date);
			$this->db->insert('tbl_previleges', $new_arr);
			return 1;
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_previleges', $data);
			return 0;
		}
	}
	public function add_submenu()
	{
		$data = array(
			'previlege' => $this->input->post('previleges'),
			'submenu' => $this->input->post('submenu'),
			'link' => $this->input->post('link'),
		);
		if ($this->input->post('id') == '') {
			$date = array(
				'created_on' => date('Y-m-d H:i:s'),
			);
			$new_arr = array_merge($data, $date);
			$this->db->insert('tbl_subprevilege', $new_arr);
			return 1;
		} else {
			$this->db->where('id', $this->input->post('id'));
			$this->db->update('tbl_subprevilege', $data);
			return 0;
		}
	}
	public function assign_previlege()
	{
		$links    = $this->input->post('link');
		$values   = !empty($links) ? implode(',', $links) : '';
		$staff_id = (int)$this->input->post('employee');

		if (empty($staff_id)) {
			return 0;
		}

		$data = [
			'staff_id'   => $staff_id,
			'previleges' => $values,
			'is_deleted' => '0',
			'status'     => '1',
		];

		// Remove any duplicate rows first, keep only the latest
		$this->db->where('staff_id', $staff_id);
		$existing = $this->db->get('tbl_assign_previlege')->result();

		if (empty($existing)) {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_assign_previlege', $data);
			return 1;
		} else {
			// Keep only the first row, delete duplicates
			$keep_id = $existing[0]->id;
			if (count($existing) > 1) {
				$ids_to_delete = array_slice(array_column($existing, 'id'), 1);
				$this->db->where_in('id', $ids_to_delete);
				$this->db->delete('tbl_assign_previlege');
			}
			$this->db->where('id', $keep_id);
			$this->db->update('tbl_assign_previlege', $data);
			return 0;
		}
	}
	public function get_all_active_entities($table_name)
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$result = $this->db->get($table_name);
		return $result->result();
	}
	public function get_all_entities($table_name)
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'desc');
		$result = $this->db->get($table_name);
		return $result->result();
	}
	public function get_single_entity_active($table_name)
	{
		$this->db->where('id', $this->uri->segment(2));
		$result = $this->db->get($table_name);
		return $result->row();
	}
	public function get_subprevilege()
	{
		$this->db->select('tbl_subprevilege.*,tbl_previleges.previlege');
		$this->db->where('tbl_subprevilege.is_deleted', '0');
		$this->db->join('tbl_previleges', 'tbl_previleges.id = tbl_subprevilege.previlege');
		$this->db->order_by('tbl_subprevilege.id', 'desc');
		$result = $this->db->get('tbl_subprevilege');
		return $result->result();
	}
	public function get_active_staff()
	{
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$result = $this->db->get('user_data');
		return $result->result();
	}
	public function get_staff_priiliges()
	{
		$id = $this->session->userdata('id');
		$link = array();
		$this->db->where('staff_id', $id);
		$emp = $this->db->get('tbl_assign_previlege');
		$emp = $emp->row();

		if (!empty($emp)) {
			$exp = explode(',', $emp->previleges);

			$this->db->select('link');
			$this->db->where_in('id', $exp);
			$result = $this->db->get('tbl_subprevilege');
			$result = $result->result();
			if (!empty($result)) {
				foreach ($result as $result_arr) {
					$link[] = $result_arr->link;
				}
			}
		}
		return $link;
	}

	public function get_unique_name_ajax()
	{
		$this->db->where($this->input->post('label'), $this->input->post('name'));
		if ($this->input->post('id') != "0") {
			$this->db->where('id !=', $this->input->post('id'));
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get($this->input->post('table_name'));
		$result = $result->row();
		if (!empty($result)) {
			echo '1';
		} else {
			echo '0';
		}
	}

	public function set_production_operators()
	{
		$id = $this->input->post('production_id');
		$day_ops = $this->input->post('day_operators') ?: $this->input->post('day_operators[]');
		$night_ops = $this->input->post('night_operators') ?: $this->input->post('night_operators[]');

		if (!is_array($day_ops)) {
			$day_ops = !empty($day_ops) ? explode(',', $day_ops) : [];
		}
		if (!is_array($night_ops)) {
			$night_ops = !empty($night_ops) ? explode(',', $night_ops) : [];
		}

		$day_csv   = implode(',', $day_ops);
		$night_csv = implode(',', $night_ops);

		// Collect operator names
		$all_op_ids = array_filter(array_unique(array_merge($day_ops ?: [], $night_ops ?: [])));
		$operator_name = '';
		if (!empty($all_op_ids)) {
			$this->db->select('first_name');
			$this->db->where_in('id', $all_op_ids);
			$op_results = $this->db->get('user_data')->result();
			$operator_name = implode(', ', array_map(fn($o) => $o->first_name, $op_results));
		}

		// Only update columns that are guaranteed to exist
		$data = [
			'day_shift_operators' => $day_csv,
			'night_shift_operators' => $night_csv,
			'operator_name' => $operator_name,
		];

		// Check if legacy op columns exist before including them
		$fields = $this->db->list_fields('tbl_production_report');
		if (in_array('day_shift_op1', $fields)) {
			$data['day_shift_op1'] = (!empty($day_ops) && isset($day_ops[0])) ? $day_ops[0] : '';
			$data['day_shift_op2'] = (count($day_ops) > 1 && isset($day_ops[1])) ? $day_ops[1] : '';
			$data['night_shift_op1'] = (!empty($night_ops) && isset($night_ops[0])) ? $night_ops[0] : '';
			$data['night_shift_op2'] = (count($night_ops) > 1 && isset($night_ops[1])) ? $night_ops[1] : '';
		}

		$this->db->where('id', $id);
		$this->db->update('tbl_production_report', $data);

		// Synchronize to details table too
		$detail_fields = $this->db->list_fields('tbl_article_production_details');
		if (in_array('day_shift_operators', $detail_fields)) {
			$this->db->where('production_id', $id);
			$this->db->update('tbl_article_production_details', [
				'day_shift_operators' => $day_csv,
				'night_shift_operators' => $night_csv,
			]);
		}

		return ['status' => 'success', 'message' => 'Operators updated'];
	}
	public function get_production_remarks($production_id)
	{
		$this->db->where('production_id', $production_id);
		$result = $this->db->get('tbl_production_remarks');
		if ($result->num_rows() > 0) {
			return $result->row();
		}
		return false;
	}

	public function set_printing_unit_report()
	{
		$approved_qty = $this->input->post('approvd_qty');
		$order_status = $this->input->post('order_status');
		// Get snapshot of current impression rate
		$article_id = $this->input->post('article_id');
		$ir_snapshot = $this->db->select('impression_rate')->where('article_id', $article_id)->where('is_deleted', '0')->get('tbl_impression_rate')->row();
		$current_ir = $ir_snapshot ? $ir_snapshot->impression_rate : 0;

		$data = array(
			'order_status' => $order_status,
			'party_id' => $this->input->post('party_id'),
			'article_id' => $article_id,
			'order_qty' => $this->input->post('order_qty'),
			'approvd_qty' => $approved_qty,
			'order_id' => $this->input->post('order_id'),
			'brand_id' => $this->input->post('brand_id'),
			'color_job' => $this->input->post('ink_names'),
			'other_material' => $this->input->post('other_material'),
			'other_material_qty_1' => $this->input->post('other_material_qty_1'),
			'other_material_two' => $this->input->post('other_material_two'),
			'other_material_qty_2' => $this->input->post('other_material_qty_2'),
			'remark' => $this->input->post('remark'),
			'impression_rate' => $current_ir,
			'updated_on' => date('Y-m-d H:i:s'),
		);
		// echo"<pre>";print_r($data);exit;
		$id = $this->input->post('id');
		if ($order_status != '0') {
			$this->db->where([
				'order_id'   => $this->input->post('order_id'),
				'party_id'   => $this->input->post('party_id'),
				'article_id' => $this->input->post('article_id'),
				'brand_id' => $this->input->post('brand_id'),
				'color_job' => $this->input->post('ink_names'),
				'other_material' => $this->input->post('other_material'),
				'other_material_qty_1' => $this->input->post('other_material_qty_1'),
				'order_qty'  => $this->input->post('order_qty'),
				'approvd_qty'=> $approved_qty,
				'is_deleted' => '0'
			]);
			$exists = $this->db->get('tbl_printing_material_report')->row();
			if ($exists) {
				return '1';
			}
			$current_date = date('Y-m-d');
			$this->db->where('task_id', $this->input->post('order_id'));
			$main_order = $this->db->get('tbl_auto_task_list')->row();
			$completed_days = (strtotime($current_date) - strtotime($main_order->last_updated_date)) / (60 * 60 * 24);
			if ($completed_days == 0) {
				$completed_days = 1;
			}
			$data['completed_days'] = $completed_days;
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_printing_material_report', $data);
			$report_id = $this->db->insert_id();
		} else {
			return '1';
		}
		if ($this->input->post('order_status') == '1') {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->update('tbl_order_sub_details', array('order_status' => '8', 'approved_qty' => $approved_qty));
		} else if ($this->input->post('order_status') == '2') {
			$this->db->where('id', $this->uri->segment(2));
			$this->db->update('tbl_order_sub_details', array('order_status' => '2', 'order_department_status' => '5'));
		}

		$ink_consumed = $this->input->post('ink_consumed');
		$ink_qty = $this->input->post('ink_qty');
		if (!empty($ink_consumed) && !empty($ink_qty)) {
			for ($i = 0; $i < count($ink_consumed); $i++) {
				if (!empty($ink_consumed[$i]) && !empty($ink_qty[$i])) {
					$ink_data = array(
						'report_id' => $report_id,
						'sub_order_id' => $this->input->post('sub_order_id'),
						'ink_id' => $ink_consumed[$i],
						'quantity' => $ink_qty[$i],
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_printing_material_inks', $ink_data);
				}
			}
		}
		$order_id = $this->input->post('order_id');
		$main_order_data = array(
			'order_status' => '8',
			'task_status' => '2',
			'task_action' => '1',
			'department_id' => '12',
			'assign_to_id' => '0',
			'details_of_task' => 'Order Printing Completed',
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$this->db->where('is_deleted', '0');
		$this->db->where('order_id', $order_id);
		$sub_orders = $this->db->get('tbl_order_sub_details');

		$order_status = true;

		if ($sub_orders->num_rows() > 0) {
			foreach ($sub_orders->result() as $order) {
				if ($order->order_status !== '1' && $order->order_status !== '2' && $order->order_status !== '9') {
					$order_status = false;
					break;
				}
			}
		}
		if ($order_status) {
			$this->db->where('task_id', $order_id);
			$this->db->update('tbl_auto_task_list', $main_order_data);
			$this->db->where('order_id', $this->input->post('order_id'));
			$this->db->update('tbl_order_details', array('order_status' => '8'));
			$this->db->select('id');
			$this->db->from('tbl_auto_task_list');
			$this->db->where('task_id', $order_id);
			$query = $this->db->get();

			if ($query->num_rows() > 0) {
				$row = $query->row();
				$last_updated_id = $row->id;
				$main_order_log_data = array(
					'task_id' => $last_updated_id,
					'task_status' => '2',
					'task_action' => '1',
					'department_id' => '12',
					'last_updated_by' => $this->session->userdata('id'),
					'details_of_task' => 'Order Printing Completed Assigned to Account Department',
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $main_order_log_data);
			}
		}
		return empty($id) ? '1' : '2';
	}

	public function get_single_printing_material_report()
	{
		$this->db->where('id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_order_sub_details');
		return $result->row();
	}
	public function get_single_printing_inks()
	{
		$this->db->where('sub_order_id', $this->uri->segment(2));
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_printing_material_inks');
		return $result->result();
	}

	public function get_other_material_rm_names()
	{
		$this->db->select('tbl_rm_master.id, tbl_rm_master.rm_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_type.id = tbl_rm_master.type_id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$this->db->where('tbl_rm_type.is_deleted', '0');
		$this->db->where('tbl_rm_type.type', 'Printing Dept Other Material');
		$this->db->order_by('tbl_rm_master.rm_name', 'ASC');

		$result = $this->db->get();
		$result = $result->result();

		return $result;
	}
	public function get_other_ink_rm_names()
	{
		$this->db->select('tbl_rm_master.id, tbl_rm_master.rm_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_type.id = tbl_rm_master.type_id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$this->db->where('tbl_rm_type.is_deleted', '0');
		$this->db->where('tbl_rm_type.type', 'INK');
		$this->db->order_by('tbl_rm_master.rm_name', 'ASC');

		$result = $this->db->get();
		$result = $result->result();
		return $result;
	}
	public function get_all_brands_according_party()
	{
		$brand_id = $this->uri->segment(5);
		$this->db->where('is_deleted', '0');
		$this->db->where('id', $brand_id);
		$brand = $this->db->get('tbl_brand_master')->row();
		if (!empty($brand)) {
			if (!empty($brand->ink_ids)) {
				$ink_ids = explode(',', $brand->ink_ids);
				$ink_ids = array_map('intval', $ink_ids);

				$this->db->where_in('id', $ink_ids);
				$ink_query = $this->db->get('tbl_rm_master');

				$inks = $ink_query->result();

				$brand->ink_names = array_column($inks, 'rm_name');
			} else {
				$brand->ink_names = [];
			}
		}
		return $brand;
	}
	public function get_container_order_qty($order_id)
	{
		$this->db->select('*');
		$this->db->from('tbl_order_sub_details');
		$this->db->where('id', $order_id);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get()->row();
		return $result->order_quantity;
	}
	public function get_ink_data_by_order_id()
	{
		$report_id = $this->input->post('report_id');
		$this->db->select('tbl_printing_material_inks.*, tbl_rm_master.rm_name');
		$this->db->from('tbl_printing_material_inks');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_printing_material_inks.ink_id', 'left');
		$this->db->where('tbl_printing_material_inks.report_id', $report_id);
		$this->db->where('tbl_printing_material_inks.is_deleted', '0');
		$result = $this->db->get();
		if ($result && $result->num_rows() > 0) {
			echo json_encode($result->result_array());
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}
	public function get_other_material_order_id()
	{
		$report_id = $this->input->post('report_id');

		$this->db->select('
        tbl_printing_material_report.*,
        rm6.rm_name as six,
        rm7.rm_name as seven
    ');
		$this->db->from('tbl_printing_material_report');
		$this->db->join('tbl_rm_master as rm6', 'rm6.id = tbl_printing_material_report.other_material', 'left');
		$this->db->join('tbl_rm_master as rm7', 'rm7.id = tbl_printing_material_report.other_material_two', 'left');
		$this->db->where('tbl_printing_material_report.id', $report_id);
		$this->db->where('tbl_printing_material_report.is_deleted', '0');

		$result = $this->db->get();

		if ($result->num_rows() > 0) {
			$row = $result->row();
			echo json_encode($row);
		} else {
			echo json_encode(['error' => 'No records found']);
		}
	}

	public function get_all_printing_material_details($length, $start, $search = '')
	{
		$this->db->select('
        tbl_printing_material_report.*,
        rm6.rm_name as six,
        rm7.rm_name as seven,
        tbl_customers.party_name,
        tbl_brand_master.brand_name,
		tbl_mould_parts.article_name
    ');
		$this->db->from('tbl_printing_material_report');
		$this->db->join('tbl_rm_master as rm6', 'rm6.id = tbl_printing_material_report.other_material', 'left');
		$this->db->join('tbl_rm_master as rm7', 'rm7.id = tbl_printing_material_report.other_material_two', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_printing_material_report.party_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_printing_material_report.article_id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_brand_master.id = tbl_printing_material_report.brand_id', 'left');
		$this->db->where('tbl_printing_material_report.is_deleted', '0');
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_printing_material_report.created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_printing_material_report.created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_printing_material_report.created_on)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_printing_material_report.brand_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('article_action') != "") {
			$this->db->where('tbl_printing_material_report.article_id', $this->input->post('article_action'));
		}
		if ($this->input->post('party_id') != "") {
			$this->db->where('tbl_printing_material_report.party_id', $this->input->post('party_id'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_printing_material_report.order_status', $this->input->post('order_status_action'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_printing_material_report.order_id', $search);
			$this->db->or_like('tbl_printing_material_report.remark', $search);
			$this->db->or_like('rm6.rm_name', $search);
			$this->db->or_like('rm7.rm_name', $search);
			// $this->db->or_like('tbl_brand_master.brand_name', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_1', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_2', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_printing_material_report.approvd_qty', $search);
			$this->db->or_like('tbl_printing_material_report.order_qty', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_printing_material_report.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$list_data = $this->db->get()->result();

		$this->db->select('COUNT(*) as total');
		$this->db->from('tbl_printing_material_report');
		$this->db->join('tbl_rm_master as rm6', 'rm6.id = tbl_printing_material_report.other_material', 'left');
		$this->db->join('tbl_rm_master as rm7', 'rm7.id = tbl_printing_material_report.other_material_two', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_printing_material_report.party_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_printing_material_report.article_id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_brand_master.id = tbl_printing_material_report.brand_id', 'left');
		$this->db->where('tbl_printing_material_report.is_deleted', '0');
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_printing_material_report.created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
				$this->db->where('DATE(tbl_printing_material_report.created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_printing_material_report.created_on)', date("Y-m-d", strtotime(trim($exp[0]))));
			}
		}
		if ($this->input->post('brand_action') != "") {
			$this->db->where('tbl_printing_material_report.brand_id', $this->input->post('brand_action'));
		}
		if ($this->input->post('article_action') != "") {
			$this->db->where('tbl_printing_material_report.article_id', $this->input->post('article_action'));
		}
		if ($this->input->post('order_status_action') != "") {
			$this->db->where('tbl_printing_material_report.order_status', $this->input->post('order_status_action'));
		}
		if ($this->input->post('party_id') != "") {
			$this->db->where('tbl_printing_material_report.party_id', $this->input->post('party_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_printing_material_report.order_id', $search);
			$this->db->or_like('tbl_printing_material_report.remark', $search);
			$this->db->or_like('rm6.rm_name', $search);
			$this->db->or_like('rm7.rm_name', $search);
			// $this->db->or_like('tbl_brand_master.brand_name', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_1', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_2', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_printing_material_report.approvd_qty', $search);
			$this->db->or_like('tbl_printing_material_report.order_qty', $search);
			$this->db->group_end();
		}

		$total_count_result = $this->db->get()->row();
		$totalCount = $total_count_result ? $total_count_result->total : 0;

		$data = [];
		if (!empty($list_data)) {
			$offset = $start + 1;
			foreach ($list_data as $member) {
				$total_no_of_impressions = (float) $member->approvd_qty * (float) $member->color_job;

				$sub_array = [
					$offset++,
					$member->created_on = date('d-m-Y - H:i', strtotime($member->created_on)),
					$member->order_status == '1' ? 'Completed' : ($member->order_status == '2' ? 'Cancelled' : 'Pending'),

					$member->order_id,
					$member->party_name,
					$member->completed_days,
					$member->article_name,
					$member->order_qty,
					$member->approvd_qty,
					$member->brand_name,
					$member->color_job,
					$total_no_of_impressions ? $total_no_of_impressions : '',
					$member->id,
					$member->five,
					$member->remark,
					$member->id
				];

				$data[] = $sub_array;
			}
		}

		$output = [
			"draw" => intval($this->input->post("draw")),
			"recordsTotal" => $totalCount,
			"recordsFiltered" => $totalCount,
			"data" => $data,
		];

		echo json_encode($output);
		exit();
	}


	public function get_outward_order_status($order_id)
	{
		$order_status = true;
		$order_partially_dispatched = false;
		$this->db->where('is_deleted', '0');
		$this->db->where('order_id', $order_id);
		$this->db->where('order_status!=', '2', );
		$orders = $this->db->get('tbl_order_sub_details')->result();
		$order_type = 'container';

		if (empty($orders)) {
			$this->db->where('is_deleted', '0');
			$this->db->where('order_id', $order_id);
			$orders = $this->db->get('tbl_order_container_details')->result();
			$order_type = 'household';
		}
		// echo "<pre>";
		// print_r($orders);exit;
		/*if ($orders) {
			foreach ($orders as $order) {
				if ($order->order_status != 4) {
					$order_status = false;
				}

				if ($order->order_status == 3) {
					$order_partially_dispatched = true;
				}
			}
			if ($order_status && !$order_partially_dispatched) {
				return 'Order Dispatched';
			} elseif ($order_partially_dispatched) {
				return 'Pending';
			} else {
				return 'In Process';
			}
		} else {
			return 'Pending';
		}*/
		$full_dispatch_count = 0;
		$dispatch_count = 0;
		$printing_count = 0;
		$pending_count = 0;
		$account_count = 0;
		if ($orders) {
			foreach ($orders as $order) {

				if ($order->order_status == 4) {
					$full_dispatch_count += 1;
				}
				if ($order->order_status == 5) {
					$account_count += 1;
				}
				if (isset($order->order_department_status) && $order->order_department_status == 3) {
					$dispatch_count += 1;
				}
				if (isset($order->order_department_status) && $order->order_department_status == 2) {
					$printing_count += 1;
				}
				if ($order->order_status == 0) {
					$pending_count += 1;
				}
			}

			if ($pending_count != count($orders)) {
				if ($account_count != count($orders)) {
					if ($full_dispatch_count == count($orders)) {
						return 'Full Dispatched';
					} else if ($dispatch_count != 0 && $printing_count != 0) {
						return 'Partially Print';
					} else if ($full_dispatch_count < count($orders) && $printing_count < count($orders) && $full_dispatch_count > $printing_count) {
						return 'Partially Dispatched';
					} else if ($dispatch_count == 0 && $printing_count < count($orders)) {
						if ($order_type == 'container') {
							return 'Printing Inprocess';
						} else if ($order_type == 'household') {
							return 'Partially Dispatched';
						}
					} else if ($full_dispatch_count == count($orders) && $printing_count == 0) {
						return 'Full Dispatched';
					} else if ($printing_count == count($orders)) {
						return 'Dispatch Inprocess';
					} else {
						return 'Pending';
					}
				} else {
					return 'Processed to Account';
				}
			} else {
				return 'Pending';
			}
		} else {
			return 'Pending';
		}
	}


	public function get_dispatch_quantity($article_id, $order_id, $brand_id)
	{
		$this->db->select('order_quantity, dispatch_quantity,approved_quantity');
		$this->db->where('article_id', $article_id);
		$this->db->where('order_id', $order_id);
		if ($brand_id) {
			$this->db->where('brand_type_id', $brand_id);
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_dispatch_order_data')->result();
		$total_dispatch_quantity = 0;
		$order_quantity = 0;
		$approved_qty = 0;
		foreach ($result as $row) {
			$dispatch_quantity = (int) $row->dispatch_quantity;
			$total_dispatch_quantity += $dispatch_quantity;
			$approved_qty = (int) $row->approved_quantity;
			$order_quantity = (int) $row->order_quantity;
		}

		if ($brand_id) {
			if ($total_dispatch_quantity > $approved_qty) {
				$total_dispatch_quantity = $approved_qty;
			}
		} else {
			if ($total_dispatch_quantity > $order_quantity) {
				$total_dispatch_quantity = $order_quantity;
			}
		}
		return [
			'order_quantity' => $order_quantity,
			'total_dispatch_quantity' => $total_dispatch_quantity
		];
	}
	public function get_material_request_to_list()
	{
		$this->db->select('tbl_request_rm_qty_data.*,tbl_rm_master.rm_name,tbl_uom_master.uom_name,tbl_mould_parts.article_name,tbl_mb_master.name as mb_name');
		$this->db->from('tbl_request_rm_qty_data');
		$this->db->join('tbl_rm_master', 'tbl_request_rm_qty_data.raw_material_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_request_rm_qty_data.uom_id = tbl_uom_master.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_request_rm_qty_data.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_request_rm_qty_data.master_batch_id = tbl_mb_master.id', 'left');
		$this->db->where('tbl_request_rm_qty_data.database_request_id', $this->uri->segment(2));
		// $this->db->where_in('tbl_request_rm_qty_data.order_status', ['1', '3', '4']);
		$this->db->where('tbl_request_rm_qty_data.is_deleted', '0');
		$this->db->order_by('tbl_request_rm_qty_data.id', 'DESC');
		$result = $this->db->get();
		return $result->result();
	}

	public function set_rm_inward_form()
	{
		$inward_no = $this->generate_inward_no();
		$inward_date = date('Y-m-d');
		$data = array(
			'inward_no' => $inward_no,
			'inward_date' => $inward_date,
			'gate_entry_no' => $this->input->post('gate_entry_no'),
			'party_id' => $this->input->post('supplier_name'),
			'plant_id' => $this->input->post('plant_id'),
			'gate_entry_date' => $this->input->post('gate_entry_date'),
			'inward_for' => '0', // 0 for rm inward
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_inward', $data);
		$database_inward_id = $this->db->insert_id();

		$raw_material_ids = $this->input->post('raw_material_id');
		$uom_ids = $this->input->post('uom_ids');
		$quantity = $this->input->post('quantity');
		$ratee = $this->input->post('rate');

		if (!empty($quantity) && is_array($quantity) && !empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$inward_quantity = isset($quantity[$key]) ? $quantity[$key] : 0;
				if ($inward_quantity) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$uom_id = isset($uom_ids[$key]) ? (int) $uom_ids[$key] : 0;
					$rate = isset($ratee[$key]) ? $ratee[$key] : 0;

					$inward_data = array(
						'inward_no' => $inward_no,
						'plant_id' => $this->input->post('plant_id'),
						'database_inward_id' => $database_inward_id,
						'raw_material_id' => $raw_m__id,
						'uom_id' => $uom_id,
						'inward_quantity' => $inward_quantity,
						'rate' => $rate,
						'inward_for' => '0', // 0 for rm inward
						'created_on' => date('Y-m-d H:i:s'),
					);

					$this->db->insert('tbl_inward_order_data', $inward_data);

					$this->db->select('total_quantity');
					$this->db->from('tbl_raw_material_stock_report');
					$this->db->where('raw_material_id', $raw_m__id);
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('is_deleted', '0');
					$result = $this->db->get();
					$opening_stock = 0;
					if ($result && $result->num_rows() > 0) {
						$opening_stock = $result->row()->total_quantity;
						$updated_quantity = $result->row()->total_quantity + $inward_quantity;
					} else {
						$updated_quantity = $inward_quantity;
					}
					if ($result && $result->num_rows() > 0) {
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->update('tbl_raw_material_stock_report');
					} else {
						$inward_stock_data = array(
							'raw_material_id' => $raw_m__id,
							'plant_id' => $this->input->post('plant_id'),
							'uom_id' => $uom_id,
							'total_quantity' => $inward_quantity,
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_raw_material_stock_report', $inward_stock_data);
					}
					$inward_stock_history_data = array(
						'raw_material_id' => $raw_m__id,
						'plant_id' => $this->input->post('plant_id'),
						'uom_id' => $uom_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'inward_qty' => $inward_quantity,
						'rate' => $rate,
						'is_inward_outward' => '0',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $inward_stock_history_data);
					log_stock_transaction([
						'item_type' => 'raw_material', 'item_id' => $raw_m__id,
						'plant_id'  => $this->input->post('plant_id'),
						'transaction_type' => 'Inward (Supplier)', 'movement_type' => 'IN',
						'qty' => $inward_quantity, 'balance_qty' => $updated_quantity, 'uom_id' => $uom_id,
						'reference_no' => $inward_no ?? null, 'reference_source' => 'inward',
						'created_by' => $this->session->userdata('id'),
					]);
				}
			}
		}
		$extra_payment_option_ids = $this->input->post('extra_payment_option_ids');
		$trap_hamali_amount = $this->input->post('trap_hamali_amount');

		if (!empty($trap_hamali_amount) && is_array($trap_hamali_amount) && !empty($extra_payment_option_ids) && is_array($extra_payment_option_ids)) {
			foreach ($extra_payment_option_ids as $key => $extra_payment_id) {
				$amount = isset($trap_hamali_amount[$key]) ? (int) $trap_hamali_amount[$key] : 0;
				if ($amount) {
					$extra_payment_option_id = (int) $extra_payment_id;

					$extra_payment_data = array(
						'inward_no' => $inward_no,
						'database_inward_id' => $database_inward_id,
						'trap_hamali_amount' => $amount,
						'extra_payment_option_id' => $extra_payment_option_id,
						'inward_for' => '0', // 0 for raw material inward
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_inward_extra_charges', $extra_payment_data);

				}
			}
		}
		// Notification Work to RM INward 
		$title = 'RM Inward Update';
		$description = 'New Raw Material Inward ' . $inward_no . ' updated by ' . $this->session->userdata('name') . '. Please review';
		$landing_page = 'rm_inward_list';
		$notification_according = '1';//means according department
		$departments = [11, 19]; // 11 = Accounts Department, 19 = Purchase Department
		$departments_str = implode(',', $departments);
		$notification_data = array(
			'notification_title' => $title,
			'notification_description' => $description,
			'notification_department' => $departments_str,
			'order_id' => $inward_no,
			'plant_id' => $this->input->post('plant_id'),
			'created_on' => date('Y-m-d H:i:s')
		);
		$this->db->insert('tbl_notifications', $notification_data);

		$this->send_task_notification_by_token(51, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));

		return 1;
	}



	public function set_material_artical_requistion_data()
	{
		$request_no = $this->generate_request_no();
		$request_date = date('Y-m-d');
		$is_article_or_rm_material = $this->input->post('is_article_or_rm_material');

		$data = array(
			'request_no' => $request_no,
			'request_date' => $request_date,
			'plant_id' => $this->input->post('plant_id'),
			'is_article_or_rm_material' => $is_article_or_rm_material,
			'my_plant_id' => $this->session->userdata('assign_plant_id'),
			'employee_id' => $this->session->userdata('id'),
		);
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_rm_request_qty', $data);
		$database_request_id = $this->db->insert_id();

		$raw_material_ids = $this->input->post('raw_material_id');
		$uom_ids = $this->input->post('uom_ids');
		$quantity = $this->input->post('quantity');
		$article_ids = $this->input->post('article_id');
		$remarks = $this->input->post('remark');

		if ($is_article_or_rm_material == '1') {
			if (!empty($quantity) && is_array($quantity) && !empty($article_ids) && is_array($article_ids)) {
				foreach ($article_ids as $key => $raw_material_id) {
					$request_quantity = isset($quantity[$key]) ? $quantity[$key] : 0;
					if ($request_quantity) {
						$article__id = isset($article_ids[$key]) ? (int) $article_ids[$key] : 0;

						$request_data = array(
							'request_no' => $request_no,
							'database_request_id' => $database_request_id,
							'article_id' => $article__id,
							'request_quantity' => $request_quantity,
							'remark' => isset($remarks[$key]) ? $remarks[$key] : '',
							'plant_id' => $this->input->post('plant_id'),
							'my_plant_id' => $this->session->userdata('assign_plant_id'),
							'is_article_or_rm_material' => '1', // 1 for article request
							'created_on' => date('Y-m-d H:i:s'),

						);
						$this->db->insert('tbl_request_rm_qty_data', $request_data);
					}
				}
			}

		} else {
			if (!empty($quantity) && is_array($quantity) && !empty($raw_material_ids) && is_array($raw_material_ids)) {
				foreach ($raw_material_ids as $key => $raw_material_id) {
					$request_quantity = isset($quantity[$key]) ? $quantity[$key] : 0;
					if ($request_quantity) {
						$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
						$uom_id = isset($uom_ids[$key]) ? (int) $uom_ids[$key] : 0;

						$request_data = array(
							'request_no' => $request_no,
							'database_request_id' => $database_request_id,
							'raw_material_id' => $raw_m__id,
							'uom_id' => $uom_id,
							'is_article_or_rm_material' => '0', // 0 for raw material request
							'request_quantity' => $request_quantity,
							'remark' => isset($remarks[$key]) ? $remarks[$key] : '',
							'plant_id' => $this->input->post('plant_id'),
							'my_plant_id' => $this->session->userdata('assign_plant_id'),
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_request_rm_qty_data', $request_data);
					}
				}
			}
		}
		return 1;
	}

	private function generate_request_no()
	{
		$month_year = date('m-Y');
		$this->db->select('request_no');
		$this->db->from('tbl_rm_request_qty');
		$this->db->like('request_no', 'REQ-' . $month_year, 'after');
		$this->db->order_by('request_no', 'DESC');
		$this->db->limit(1);
		$result_user = $this->db->get();

		if ($result_user && $result_user->num_rows() > 0) {
			$latest_request_no = $result_user->row()->request_no;
			preg_match('/(\d{3})$/', $latest_request_no, $matches);

			if ($matches) {
				$next_number = intval($matches[0]) + 1;
				return 'REQ-' . $month_year . '-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
			}
		}

		return 'REQ-' . $month_year . '-001';
	}



	public function set_material_requistion_qty_data()
	{
		$raw_material_ids = $this->input->post('raw_material_id');
		$uom_ids = $this->input->post('uom_id');
		$request_no = $this->input->post('request_no');
		$request_quantity = $this->input->post('request_quantity');
		$received_qty = $this->input->post('received_qty');
		$my_plant_ids = $this->input->post('my_plant_id');
		$plant_id = $this->input->post('plant_id');
		if (!empty($received_qty) && is_array($received_qty) && !empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$receive_qty = isset($received_qty[$key]) ? (int) $received_qty[$key] : 0;

				if ($receive_qty > 0) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$uom_id = isset($uom_ids[$key]) ? (int) $uom_ids[$key] : 0;
					$request_qty = isset($request_quantity[$key]) ? $request_quantity[$key] : 0;
					$my_plant_id = isset($my_plant_ids[$key]) ? (int) $my_plant_ids[$key] : 0;
					
					$request_data = array(
						'request_no' => $request_no[$key],
						'received_qty' => $receive_qty,
						'raw_material_id' => $raw_m__id,
						'uom_id' => $uom_id,
						'request_quantity' => $request_qty,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_received_rm_qty_data', $request_data);

					$this->db->select('total_quantity');
					$this->db->from('tbl_raw_material_stock_report');
					$this->db->where('raw_material_id', $raw_m__id);
					$this->_apply_assigned_plants_scope('plant_id');
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();
					$opening_stock = 0;
					$updated_quantity = 0;
					if ($result) {
						$opening_stock = $result->total_quantity;
						$updated_quantity = $result->total_quantity - $receive_qty;
						$this->db->set('total_quantity', $updated_quantity);
						$this->_apply_assigned_plants_scope('plant_id');
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->update('tbl_raw_material_stock_report');
					}

					$outward_stock_history_data = array(
						'raw_material_id' => $raw_m__id,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'uom_id' => $uom_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'outward_qty' => $receive_qty,
						'is_inward_outward' => '2',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $outward_stock_history_data);
					if($my_plant_id != $plant_id){
						// qty + to requested palnt
						$this->db->select('total_quantity');
						$this->db->from('tbl_raw_material_stock_report');
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->where('plant_id', $my_plant_id);
						$this->db->where('is_deleted', '0');
						$ss_result = $this->db->get()->row();
						if ($ss_result) {
							$opening_stock = $ss_result->total_quantity;
							$updated_quantity = $ss_result->total_quantity + $receive_qty;
							$this->db->set('total_quantity', $updated_quantity);
							$this->db->where('plant_id', $my_plant_id);
							$this->db->where('raw_material_id', $raw_m__id);
							$this->db->update('tbl_raw_material_stock_report');
						} else {
							$stock_data = array(
								'raw_material_id' => $raw_m__id,
								'plant_id' => $my_plant_id,
								'uom_id' => $uom_id,
								'total_quantity' => $receive_qty,
								'created_on' => date('Y-m-d H:i:s'),
							);
							$this->db->insert('tbl_raw_material_stock_report', $stock_data);
						}
						$inward_stock_history_data = array(
							'raw_material_id' => $raw_m__id,
							'plant_id' => $my_plant_id,
							'uom_id' => $uom_id,
							'opening_stock' => $opening_stock,
							'total_quantity' => $updated_quantity,
							'inward_qty' => $receive_qty,
							'is_inward_outward' => '1',
							'date' => date('Y-m-d'),
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_raw_material_stock_report_history', $inward_stock_history_data);
						log_stock_transaction([
							'item_type' => 'raw_material', 'item_id' => $raw_m__id,
							'plant_id'  => $my_plant_id,
							'transaction_type' => 'Stock Transfer IN', 'movement_type' => 'IN',
							'qty' => $receive_qty, 'balance_qty' => $updated_quantity, 'uom_id' => $uom_id,
							'reference_no' => $request_no[$key] ?? null, 'reference_source' => 'transfer',
							'created_by' => $this->session->userdata('id'),
						]);
					}
					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('raw_material_id', $raw_m__id);
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('is_deleted', '0');
					$received_result = $this->db->get()->row();
					if ($received_result) {
						$up_received = $received_result->received_qty + $receive_qty;
						$this->db->set([
							'received_qty' => $up_received,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->update('tbl_request_rm_qty_data');
					}

					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('raw_material_id', $raw_m__id);
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('is_deleted', '0');
					$status_result = $this->db->get()->row();
					if ($status_result) {
						$req_status = ($status_result->received_qty == $request_qty) ? '2' : '3';
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->set([
							'request_status' => $req_status,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->update('tbl_request_rm_qty_data');
						$this->db->where('request_no', $request_no[$key]);
						$this->db->set('request_status', $req_status);
						$this->db->update('tbl_rm_request_qty');
					}

				}
			}
		}
		$this->db->select('plant_name');
		$this->db->from('tbl_plant_master');
		$this->db->where('id', $this->session->userdata('assign_plant_id'));
		$this->db->where('is_deleted', '0');
		$session_plant = $this->db->get()->row()->plant_name;

		$plant_id = $this->input->post('plant_id');
		$this->db->select('plant_name');
		$this->db->from('tbl_plant_master');
		$this->db->where('id', $plant_id);
		$this->db->where('is_deleted', '0');
		$from_plant = $this->db->get()->row()->plant_name;
		if($session_plant != $from_plant){
			// Notification Work when provide qty to another plant
			$title = 'Stock TRF Update';
			$description = 'Stock Transfer ' . $session_plant . ' to ' . $from_plant . '. Request No. ' . $this->input->post('request_no_for_notification') . ' updated by ' .
				$this->session->userdata('name');
			$landing_page = 'plant_list';
			$notification_according = '1';//means according department
			$departments = [11, 24, 19]; // 11 = Accounts Department, 24 = store  Department, 19 = Purchase Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $this->input->post('request_no_for_notification'),
				'plant_id' => $this->session->userdata('assign_plant_id'),
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(55, $title, $description, $landing_page, $notification_according, $this->session->userdata('assign_plant_id'));
		}
		return 1;
	}
	public function set_article_requistion_qty_data()
	{
		$article_id = $this->input->post('article_id');
		$request_no = $this->input->post('request_no');
		$request_quantity = $this->input->post('request_quantity');
		$received_qty = $this->input->post('received_qty');
		$my_plant_ids = $this->input->post('my_plant_id');

		if (!empty($received_qty) && is_array($received_qty) && !empty($article_id) && is_array($article_id)) {
			foreach ($article_id as $key => $article) {
				$receive_qty = isset($received_qty[$key]) ? (int) $received_qty[$key] : 0;

				if ($receive_qty > 0) {
					$article_idd = isset($article_id[$key]) ? (int) $article_id[$key] : 0;
					$request_qty = isset($request_quantity[$key]) ? $request_quantity[$key] : 0;
					$my_plant_id = isset($my_plant_ids[$key]) ? (int) $my_plant_ids[$key] : 0;

					$request_data = array(
						'request_no' => $request_no[$key],
						'received_qty' => $receive_qty,
						'article_id' => $article_idd,
						'request_quantity' => $request_qty,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_received_rm_qty_data', $request_data);

					$this->db->select('total_quantity');
					$this->db->from('tbl_article_stock_report');
					$this->db->where('article_id', $article_idd);
					$this->_apply_assigned_plants_scope('plant_id');
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();
					$opening_stock = 0;
					$updated_quantity = 0;
					if ($result) {
						$opening_stock = $result->total_quantity;
						$updated_quantity = $result->total_quantity - $receive_qty;
						$this->db->set('total_quantity', $updated_quantity);
						$this->_apply_assigned_plants_scope('plant_id');
						$this->db->where('article_id', $article_idd);
						$this->db->update('tbl_article_stock_report');
					}
					$outward_stock_history_data = array(
						'article_id' => $article_idd,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'outward_qty' => $receive_qty,
						'is_inward_outward' => '2',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $outward_stock_history_data);
					// qty + to requested palnt
					$this->db->select('total_quantity');
					$this->db->from('tbl_article_stock_report');
					$this->db->where('article_id', $article_idd);
					$this->db->where('plant_id', $my_plant_id);
					$this->db->where('is_deleted', '0');
					$ss_result = $this->db->get()->row();
					$updated_quantity = 0;
					$opening_stock = 0;
					if ($ss_result) {
						$opening_stock = $ss_result->total_quantity;
						$updated_quantity = $ss_result->total_quantity + $receive_qty;
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $my_plant_id);
						$this->db->where('article_id', $article_idd);
						$this->db->update('tbl_article_stock_report');
					} else {
						$stock_data = array(
							'article_id' => $article_idd,
							'plant_id' => $my_plant_id,
							'total_quantity' => $receive_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_article_stock_report', $stock_data);
						$updated_quantity = $receive_qty;
					}
					$inward_stock_history_data = array(
						'article_id' => $article_idd,
						'plant_id' => $my_plant_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'inward_qty' => $receive_qty,
						'is_inward_outward' => '1',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $inward_stock_history_data);

					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('article_id', $article_idd);
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('is_deleted', '0');
					$received_result = $this->db->get()->row();
					if ($received_result) {
						$up_received = $received_result->received_qty + $receive_qty;
						$this->db->set([
							'received_qty' => $up_received,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('article_id', $article_idd);
						$this->db->update('tbl_request_rm_qty_data');
					}

					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('article_id', $article_idd);
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('is_deleted', '0');
					$status_result = $this->db->get()->row();
					if ($status_result) {
						$req_status = ($status_result->received_qty == $request_qty) ? '2' : '3';
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('article_id', $article_idd);
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->set([
							'request_status' => $req_status,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->update('tbl_request_rm_qty_data');
						$this->db->where('request_no', $request_no[$key]);
						$this->db->set('request_status', $req_status);
						$this->db->update('tbl_rm_request_qty');
					}

				}
			}
		} else {
			return 2;
		}
		return 1;
	}
	public function set_mb_requistion_qty_data()
	{
		// echo"<pre>";print_r($_POST);exit;
		$master_batch_ids = $this->input->post('master_batch_id');
		$request_no = $this->input->post('request_no');
		$request_quantity = $this->input->post('request_quantity');
		$received_qty = $this->input->post('received_qty');
		$my_plant_ids = $this->input->post('my_plant_id');
		$plant_id = $this->input->post('plant_id');
		if (!empty($received_qty) && is_array($received_qty) && !empty($master_batch_ids) && is_array($master_batch_ids)) {
			foreach ($master_batch_ids as $key => $article) {
				$receive_qty = isset($received_qty[$key]) ? (int) $received_qty[$key] : 0;

				if ($receive_qty > 0) {
					$master_batch_id = isset($master_batch_ids[$key]) ? (int) $master_batch_ids[$key] : 0;
					$request_qty = isset($request_quantity[$key]) ? $request_quantity[$key] : 0;
					$my_plant_id = isset($my_plant_ids[$key]) ? (int) $my_plant_ids[$key] : 0;

					$request_data = array(
						'request_no' => $request_no[$key],
						'received_qty' => $receive_qty,
						'master_batch_id' => $master_batch_id,
						'request_quantity' => $request_qty,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_received_rm_qty_data', $request_data);

					$this->db->select('total_quantity');
					$this->db->from('tbl_master_batch_stock_report');
					$this->db->where('master_batch_id', $master_batch_id);
					$this->_apply_assigned_plants_scope('plant_id');
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();
					$opening_stock = 0;
					$updated_quantity = 0;
					if ($result) {
						$opening_stock = $result->total_quantity;
						$updated_quantity = $result->total_quantity - $receive_qty;
						$this->db->set('total_quantity', $updated_quantity);
						$this->_apply_assigned_plants_scope('plant_id');
						$this->db->where('master_batch_id', $master_batch_id);
						$this->db->update('tbl_master_batch_stock_report');
					}
					$outward_stock_history_data = array(
						'master_batch_id' => $master_batch_id,
						'plant_id' => $this->session->userdata('assign_plant_id'),
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'outward_qty' => $receive_qty,
						'is_inward_outward' => '2',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_master_batch_stock_report_history', $outward_stock_history_data);
					log_stock_transaction([
						'item_type' => 'master_batch', 'item_id' => $master_batch_id,
						'plant_id'  => $this->session->userdata('assign_plant_id'),
						'transaction_type' => 'Plant to Plant Transfer', 'movement_type' => 'OUT',
						'qty' => $receive_qty, 'balance_qty' => $updated_quantity,
						'reference_no' => $request_no[$key] ?? null, 'reference_source' => 'transfer',
						'created_by' => $this->session->userdata('id'),
					]);
					if($my_plant_id != $plant_id){
						// qty + to requested palnt
						$this->db->select('total_quantity');
						$this->db->from('tbl_master_batch_stock_report');
						$this->db->where('master_batch_id', $master_batch_id);
						$this->db->where('plant_id', $my_plant_id);
						$this->db->where('is_deleted', '0');
						$ss_result = $this->db->get()->row();
						$opening_stock = 0;
						$updated_quantity = 0;
						if ($ss_result) {
							$opening_stock = $ss_result->total_quantity;
							$updated_quantity = $ss_result->total_quantity + $receive_qty;
							$this->db->set('total_quantity', $updated_quantity);
							$this->db->where('plant_id', $my_plant_id);
							$this->db->where('master_batch_id', $master_batch_id);
							$this->db->update('tbl_master_batch_stock_report');
						} else {
							$stock_data = array(
								'master_batch_id' => $master_batch_id,
								'plant_id' => $my_plant_id,
								'total_quantity' => $receive_qty,
								'created_on' => date('Y-m-d H:i:s'),
							);
							$this->db->insert('tbl_master_batch_stock_report', $stock_data);
							$updated_quantity = $receive_qty;
						}
						$inward_stock_history_data = array(
							'master_batch_id' => $master_batch_id,
							'plant_id' => $my_plant_id,
							'opening_stock' => $opening_stock,
							'total_quantity' => $updated_quantity,
							'inward_qty' => $receive_qty,
							'is_inward_outward' => '1',
							'date' => date('Y-m-d'),
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_master_batch_stock_report_history', $inward_stock_history_data);
						log_stock_transaction([
							'item_type' => 'master_batch', 'item_id' => $master_batch_id,
							'plant_id'  => $my_plant_id,
							'transaction_type' => 'Stock Transfer IN', 'movement_type' => 'IN',
							'qty' => $receive_qty, 'balance_qty' => $updated_quantity,
							'reference_no' => $request_no[$key] ?? null, 'reference_source' => 'transfer',
							'created_by' => $this->session->userdata('id'),
						]);
					}

					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('master_batch_id', $master_batch_id);
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('is_deleted', '0');
					$received_result = $this->db->get()->row();
					if ($received_result) {
						$up_received = $received_result->received_qty + $receive_qty;
						$this->db->set([
							'received_qty' => $up_received,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('master_batch_id', $master_batch_id);
						$this->db->update('tbl_request_rm_qty_data');
					}

					$this->db->select('received_qty');
					$this->db->from('tbl_request_rm_qty_data');
					$this->db->where('request_no', $request_no[$key]);
					$this->db->where('master_batch_id', $master_batch_id);
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('is_deleted', '0');
					$status_result = $this->db->get()->row();
					if ($status_result) {
						$req_status = ($status_result->received_qty == $request_qty) ? '2' : '3';
						$this->db->where('request_no', $request_no[$key]);
						$this->db->where('master_batch_id', $master_batch_id);
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->set([
							'request_status' => $req_status,
							'approved_by' => $this->session->userdata('id')
						]);
						$this->db->update('tbl_request_rm_qty_data');
						$this->db->where('request_no', $request_no[$key]);
						$this->db->set('request_status', $req_status);
						$this->db->update('tbl_rm_request_qty');
					}

				}
			}
		}
		return 1;
	}
	public function set_rm_stock_adjustment_data()
	{
		$plant_id = $this->input->post('plant_id');
		$adjustment_typee = $this->input->post('adjustment_type');
		$raw_material_ids = $this->input->post('raw_material_id');
		$uom_ids = $this->input->post('uom_ids');
		// echo"<pre>";print_r($uom_ids);exit;
		$quantity = $this->input->post('quantity');
		$remarks = $this->input->post('remark');
		$date = date('Y-m-d');

		if (!empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$receive_qty = isset($quantity[$key]) ? (float) $quantity[$key] : 0;

				if ($receive_qty > 0) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$uom_id = isset($uom_ids[$key]) ? (int) $uom_ids[$key] : 0;
					$adjustment = isset($adjustment_typee[$key]) ? $adjustment_typee[$key] : '';
					$remark = isset($remarks[$key]) ? $remarks[$key] : '';
					$adjustment_type = '0';
					$is_increse_or_decrease = '0';
					if ($adjustment == 'Increasing') {
						$adjustment_type = '1';
						$is_increse_or_decrease = '3';
					} else {
						$adjustment_type = '2';
						$is_increse_or_decrease = '4';
					}
					$opening_stock = 0;
					$updated_quantity = 0;
					$this->db->select('total_quantity');
					$this->db->from('tbl_raw_material_stock_report');
					$this->db->where('raw_material_id', $raw_m__id);
					$this->db->where('plant_id', $plant_id);
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();


					if ($result) {
						$opening_stock = $result->total_quantity;
						if ($adjustment_type == '1') {
							$updated_quantity = $result->total_quantity + $receive_qty;
						} else {
							$updated_quantity = $result->total_quantity - $receive_qty;
						}
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $plant_id);
						$this->db->where('raw_material_id', $raw_m__id);
						$this->db->update('tbl_raw_material_stock_report');
					} else {
						$stock_data = array(
							'raw_material_id' => $raw_m__id,
							'plant_id' => $plant_id,
							'uom_id' => $uom_id,
							'total_quantity' => $receive_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);

						$this->db->insert('tbl_raw_material_stock_report', $stock_data);
						$updated_quantity = $receive_qty;
					}
					// echo"<pre>";print_r($opening_stock);exit;
					$stock_history_data = array(
						'raw_material_id' => $raw_m__id,
						'plant_id' => $plant_id,
						'uom_id' => $uom_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'adjusted_qty' => $receive_qty,
						'inward_qty' => ($adjustment_type == '1') ? $receive_qty : 0,
						'outward_qty' => ($adjustment_type == '2') ? $receive_qty : 0,
						'is_inward_outward' => $is_increse_or_decrease,
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);
					$adjust_stock_data = array(
						'plant_id' => $plant_id,
						'date' => $date,
						'adjustment_type' => $adjustment_type,
						'raw_material_id' => $raw_m__id,
						'uom_id' => $uom_id,
						'opening_stock' => $opening_stock,
						'quantity' => $receive_qty,
						'stock_adjusted_for' => '1',
						'total_quantity' => $updated_quantity,
						'remark' => $remark,
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_stock_adjustment', $adjust_stock_data);

				}
			}
		}
		return 1;
	}
	public function set_mb_stock_adjustment_data()
	{
		$plant_id = $this->input->post('plant_id');
		$adjustment_typee = $this->input->post('adjustment_type');
		$raw_material_ids = $this->input->post('master_batch_id');
		$quantity = $this->input->post('quantity');
		$remarks = $this->input->post('remark');
		$date = date('Y-m-d');

		if (!empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$receive_qty = isset($quantity[$key]) ? (float) $quantity[$key] : 0;

				if ($receive_qty > 0) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$adjustment = isset($adjustment_typee[$key]) ? $adjustment_typee[$key] : '';
					$remark = isset($remarks[$key]) ? $remarks[$key] : '';
					$adjustment_type = '0';
					$is_increse_or_decrease = '0';
					if ($adjustment == 'Increasing') {
						$adjustment_type = '1';
						$is_increse_or_decrease = '3';
					} else {
						$adjustment_type = '2';
						$is_increse_or_decrease = '4';
					}
					$opening_stock = 0;
					$updated_quantity = 0;
					$this->db->select('total_quantity');
					$this->db->from('tbl_master_batch_stock_report');
					$this->db->where('master_batch_id', $raw_m__id);
					$this->db->where('plant_id', $plant_id);
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();


					if ($result) {
						$opening_stock = $result->total_quantity;
						if ($adjustment_type == '1') {
							$updated_quantity = $result->total_quantity + $receive_qty;
						} else {
							$updated_quantity = $result->total_quantity - $receive_qty;
						}
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $plant_id);
						$this->db->where('master_batch_id', $raw_m__id);
						$this->db->update('tbl_master_batch_stock_report');
					} else {
						$stock_data = array(
							'master_batch_id' => $raw_m__id,
							'plant_id' => $plant_id,
							'total_quantity' => $receive_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);

						$this->db->insert('tbl_master_batch_stock_report', $stock_data);
						$updated_quantity = $receive_qty;
					}
					$stock_history_data = array(
						'master_batch_id' => $raw_m__id,
						'plant_id' => $plant_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'adjusted_qty' => $receive_qty,
						'inward_qty' => ($adjustment_type == '1') ? $receive_qty : 0,
						'outward_qty' => ($adjustment_type == '2') ? $receive_qty : 0,
						'is_inward_outward' => $is_increse_or_decrease,
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_master_batch_stock_report_history', $stock_history_data);
					log_stock_transaction([
						'item_type' => 'master_batch', 'item_id' => $raw_m__id,
						'plant_id'  => $plant_id,
						'transaction_type' => ($adjustment_type == '1') ? 'Stock Adj. +' : 'Stock Adj. -',
						'movement_type' => ($adjustment_type == '1') ? 'IN' : 'OUT',
						'qty' => $receive_qty, 'balance_qty' => $updated_quantity,
						'reference_source' => 'adjustment',
						'created_by' => $this->session->userdata('id'),
					]);
					$adjust_stock_data = array(
						'plant_id' => $plant_id,
						'date' => $date,
						'adjustment_type' => $adjustment_type,
						'master_batch_id	' => $raw_m__id,
						'opening_stock' => $opening_stock,
						'quantity' => $receive_qty,
						'total_quantity' => $updated_quantity,
						'stock_adjusted_for' => '2',
						'remark' => $remark,
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_stock_adjustment', $adjust_stock_data);

				}
			}
		}
		return 1;
	}
	public function set_article_stock_adjustment_data()
	{
		$plant_id = $this->input->post('plant_id');
		$adjustment_typee = $this->input->post('adjustment_type');
		$raw_material_ids = $this->input->post('article_ids');
		$quantity = $this->input->post('quantity');
		$remarks = $this->input->post('remark');
		$date = date('Y-m-d');

		if (!empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$receive_qty = isset($quantity[$key]) ? (float) $quantity[$key] : 0;

				if ($receive_qty > 0) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$adjustment = isset($adjustment_typee[$key]) ? $adjustment_typee[$key] : '';
					$remark = isset($remarks[$key]) ? $remarks[$key] : '';
					$adjustment_type = '0';
					$is_increse_or_decrease = '0';
					if ($adjustment == 'Increasing') {
						$adjustment_type = '1';
						$is_increse_or_decrease = '3';
					} else {
						$adjustment_type = '2';
						$is_increse_or_decrease = '4';
					}
					$opening_stock = 0;
					$updated_quantity = 0;
					$this->db->select('total_quantity');
					$this->db->from('tbl_article_stock_report');
					$this->db->where('article_id', $raw_m__id);
					$this->db->where('plant_id', $plant_id);
					$this->db->where('is_deleted', '0');
					$result = $this->db->get()->row();
					if ($result) {
						$opening_stock = $result->total_quantity;
						if ($adjustment_type == '1') {
							$updated_quantity = $result->total_quantity + $receive_qty;
						} else {
							$updated_quantity = $result->total_quantity - $receive_qty;
						}
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $plant_id);
						$this->db->where('article_id', $raw_m__id);
						$this->db->update('tbl_article_stock_report');
					} else {
						$stock_data = array(
							'article_id' => $raw_m__id,
							'plant_id' => $plant_id,
							'total_quantity' => $receive_qty,
							'created_on' => date('Y-m-d H:i:s'),
						);

						$this->db->insert('tbl_article_stock_report', $stock_data);
						$updated_quantity = $receive_qty;
					}
					$stock_history_data = array(
						'article_id' => $raw_m__id,
						'plant_id' => $plant_id,
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'adjusted_qty' => $receive_qty,
						'inward_qty' => ($adjustment_type == '1') ? $receive_qty : 0,
						'outward_qty' => ($adjustment_type == '2') ? $receive_qty : 0,
						'is_inward_outward' => $is_increse_or_decrease,
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);
					$adjust_stock_data = array(
						'plant_id' => $plant_id,
						'date' => $date,
						'adjustment_type' => $adjustment_type,
						'article_id	' => $raw_m__id,
						'opening_stock' => $opening_stock,
						'quantity' => $receive_qty,
						'total_quantity' => $updated_quantity,
						'stock_adjusted_for' => '3',
						'remark' => $remark,
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_stock_adjustment', $adjust_stock_data);

				}
			}
		}
		return 1;
	}
	public function get_stock_adjustment_list($length, $start, $search)
	{
		$this->db->select('tbl_stock_adjustment.*, tbl_rm_master.rm_name,tbl_plant_master.plant_name, tbl_uom_master.uom_name,tbl_mb_master.name as mb_name ,tbl_mould_parts.article_name');
		$this->db->from('tbl_stock_adjustment');
		$this->db->join('tbl_rm_master', 'tbl_stock_adjustment.raw_material_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_stock_adjustment.plant_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_stock_adjustment.uom_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_stock_adjustment.master_batch_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_stock_adjustment.article_id', 'left');
		$this->db->where('tbl_stock_adjustment.is_deleted', '0');
		$this->db->where('tbl_stock_adjustment.stock_adjusted_for', $this->input->post('stock_adjusted_for'));

		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_stock_adjustment.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_stock_adjustment.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_stock_adjustment.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_stock_adjustment.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_stock_adjustment.raw_material_id', $this->input->post('raw_material_id'));
		}
		if ($this->input->post('master_batch_id') != "") {
			$this->db->where('tbl_stock_adjustment.master_batch_id', $this->input->post('master_batch_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_stock_adjustment.article_id', $this->input->post('article_id'));
		}
		$search_lower = strtolower(trim($search));
		$status_map = [
			'inc' => '1',
			'dec' => '2',
			'increasing' => '1',
			'decreasing' => '2',
		];

		$status_value = false;
		foreach ($status_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$status_value = $value;
				break;
			}
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_mb_master.name', $search);
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_stock_adjustment.opening_stock', $search);
			$this->db->or_like('tbl_stock_adjustment.total_quantity', $search);
			$this->db->or_like('tbl_stock_adjustment.quantity', $search);
			$this->db->or_like('tbl_stock_adjustment.remark', $search);
			if ($status_value !== false) {
				$this->db->or_like('tbl_stock_adjustment.adjustment_type', $status_value);
			} else {
				$this->db->or_like('tbl_stock_adjustment.adjustment_type', $search);
			}
			$this->db->group_end();
		}

		$this->db->order_by('tbl_stock_adjustment.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(id) as total_count');
		$this->db->from('tbl_stock_adjustment');
		$this->db->where('is_deleted', '0');
		$this->db->where('stock_adjusted_for', $this->input->post('stock_adjusted_for'));
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_stock_adjustment.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_stock_adjustment.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_stock_adjustment.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_stock_adjustment.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_stock_adjustment.raw_material_id', $this->input->post('raw_material_id'));
		}
		if ($this->input->post('master_batch_id') != "") {
			$this->db->where('tbl_stock_adjustment.master_batch_id', $this->input->post('master_batch_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_stock_adjustment.article_id', $this->input->post('article_id'));
		}
		$search_lower = strtolower(trim($search));
		$status_map = [
			'inc' => '1',
			'dec' => '2',
			'increasing' => '1',
			'decreasing' => '2',
		];

		$status_value = false;
		foreach ($status_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$status_value = $value;
				break;
			}
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_stock_adjustment.remark', $search);
			$this->db->or_like('tbl_stock_adjustment.opening_stock', $search);
			$this->db->or_like('tbl_stock_adjustment.total_quantity', $search);
			$this->db->or_like('tbl_stock_adjustment.quantity', $search);
			if ($status_value !== false) {
				$this->db->or_like('tbl_stock_adjustment.adjustment_type', $status_value);
			} else {
				$this->db->or_like('tbl_stock_adjustment.adjustment_type', $search);
			}
			$this->db->group_end();
		}
		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_total_stock_list($length, $start, $search, $stock_type)
	{
		if ($stock_type == 'raw_material') {
			$this->db->select('tbl_raw_material_stock_report.*,tbl_rm_master.rm_name,tbl_plant_master.plant_name,tbl_uom_master.uom_name');
			$this->db->from('tbl_raw_material_stock_report');
			$this->db->join('tbl_rm_master', 'tbl_raw_material_stock_report.raw_material_id = tbl_rm_master.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_raw_material_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->join('tbl_uom_master', 'tbl_raw_material_stock_report.uom_id = tbl_uom_master.id', 'left');
			$this->_apply_assigned_plants_scope('tbl_raw_material_stock_report.plant_id');
			$this->db->where('tbl_raw_material_stock_report.is_deleted', '0');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_raw_material_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('raw_material_id') != "") {
				$this->db->where('tbl_raw_material_stock_report.raw_material_id', $this->input->post('raw_material_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_rm_master.rm_name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_uom_master.uom_name', $search);
				$this->db->or_like('tbl_raw_material_stock_report.reorder_level', $search);
				$this->db->or_like('tbl_raw_material_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$this->db->order_by('tbl_raw_material_stock_report.id', 'desc');
			if ($length > 0) { $this->db->limit($length, $start); }
			$query = $this->db->get();
			$result = $query->result();
			$this->db->select('COUNT(tbl_raw_material_stock_report.id) as total_count');
			$this->db->from('tbl_raw_material_stock_report');
			$this->db->join('tbl_rm_master', 'tbl_raw_material_stock_report.raw_material_id = tbl_rm_master.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_raw_material_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->join('tbl_uom_master', 'tbl_raw_material_stock_report.uom_id = tbl_uom_master.id', 'left');
			$this->db->where('tbl_raw_material_stock_report.is_deleted', '0');
			$this->_apply_assigned_plants_scope('tbl_raw_material_stock_report.plant_id');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_raw_material_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('raw_material_id') != "") {
				$this->db->where('tbl_raw_material_stock_report.raw_material_id', $this->input->post('raw_material_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_rm_master.rm_name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_uom_master.uom_name', $search);
				$this->db->or_like('tbl_raw_material_stock_report.reorder_level', $search);
				$this->db->or_like('tbl_raw_material_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$count_query = $this->db->get();
			$total_count = $count_query->row()->total_count;
			return [
				'data' => $result,
				'total_count' => $total_count
			];
		} else if ($stock_type == 'master_batch') {
			$this->db->select('tbl_master_batch_stock_report.*,tbl_mb_master.name as mb_name,tbl_plant_master.plant_name');
			$this->db->from('tbl_master_batch_stock_report');
			$this->db->join('tbl_mb_master', 'tbl_master_batch_stock_report.master_batch_id = tbl_mb_master.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_master_batch_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->where('tbl_master_batch_stock_report.is_deleted', '0');
			$this->_apply_assigned_plants_scope('tbl_master_batch_stock_report.plant_id');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_master_batch_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('master_batch_id') != "") {
				$this->db->where('tbl_master_batch_stock_report.master_batch_id', $this->input->post('master_batch_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_mb_master.name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_master_batch_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$this->db->order_by('tbl_master_batch_stock_report.id', 'desc');
			if ($length > 0) { $this->db->limit($length, $start); }
			$query = $this->db->get();
			$result = $query->result();
			$this->db->select('COUNT(tbl_master_batch_stock_report.id) as total_count');
			$this->db->from('tbl_master_batch_stock_report');
			$this->db->join('tbl_mb_master', 'tbl_master_batch_stock_report.master_batch_id = tbl_mb_master.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_master_batch_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->where('tbl_master_batch_stock_report.is_deleted', '0');
			$this->_apply_assigned_plants_scope('tbl_master_batch_stock_report.plant_id');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_master_batch_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('master_batch_id') != "") {
				$this->db->where('tbl_master_batch_stock_report.master_batch_id', $this->input->post('master_batch_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_mb_master.name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_master_batch_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$count_query = $this->db->get();
			$total_count = $count_query->row()->total_count;
			return [
				'data' => $result,
				'total_count' => $total_count
			];
		} else if ($stock_type == 'article') {
			$this->db->select('tbl_article_stock_report.*,tbl_mould_parts.article_name,tbl_plant_master.plant_name');
			$this->db->from('tbl_article_stock_report');
			$this->db->join('tbl_mould_parts', 'tbl_article_stock_report.article_id = tbl_mould_parts.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_article_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->where('tbl_article_stock_report.is_deleted', '0');
			$this->_apply_assigned_plants_scope('tbl_article_stock_report.plant_id');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_article_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('article_id') != "") {
				$this->db->where('tbl_article_stock_report.article_id', $this->input->post('article_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_mould_parts.article_name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_article_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$this->db->order_by('tbl_article_stock_report.id', 'desc');
			if ($length > 0) { $this->db->limit($length, $start); }
			$query = $this->db->get();
			$result = $query->result();
			$this->db->select('COUNT(tbl_article_stock_report.id) as total_count');
			$this->db->from('tbl_article_stock_report');
			$this->db->join('tbl_mould_parts', 'tbl_article_stock_report.article_id = tbl_mould_parts.id', 'left');
			$this->db->join('tbl_plant_master', 'tbl_article_stock_report.plant_id = tbl_plant_master.id', 'left');
			$this->db->where('tbl_article_stock_report.is_deleted', '0');
			$this->_apply_assigned_plants_scope('tbl_article_stock_report.plant_id');
			if ($this->input->post('plant_id') != "") {
				$this->db->where('tbl_article_stock_report.plant_id', $this->input->post('plant_id'));
			}
			if ($this->input->post('article_id') != "") {
				$this->db->where('tbl_article_stock_report.article_id', $this->input->post('article_id'));
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('tbl_mould_parts.article_name', $search);
				$this->db->or_like('tbl_plant_master.plant_name', $search);
				$this->db->or_like('tbl_article_stock_report.total_quantity', $search);
				$this->db->group_end();
			}
			$count_query = $this->db->get();
			$total_count = $count_query->row()->total_count;
			return [
				'data' => $result,
				'total_count' => $total_count
			];
		}
	}
	public function get_all_raw_material_reorder_level($length, $start, $search)
	{
		$this->db->select('tbl_raw_material_stock_report.*,tbl_rm_master.rm_name,tbl_plant_master.plant_name,tbl_uom_master.uom_name,tbl_rm_master.reorder_level as rm_reorder_level');
		$this->db->from('tbl_raw_material_stock_report');
		$this->db->join('tbl_rm_master', 'tbl_raw_material_stock_report.raw_material_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_raw_material_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_raw_material_stock_report.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_raw_material_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_raw_material_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_rm_master.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $this->input->post('plant_id'));
		}
		if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report.raw_material_id', $this->input->post('raw_material_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report.reorder_level', $search);
			$this->db->or_like('tbl_raw_material_stock_report.total_quantity', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_raw_material_stock_report.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(tbl_raw_material_stock_report.id) as total_count');
		$this->db->from('tbl_raw_material_stock_report');
		$this->db->join('tbl_rm_master', 'tbl_raw_material_stock_report.raw_material_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_raw_material_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_raw_material_stock_report.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_raw_material_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_raw_material_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_rm_master.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $this->input->post('plant_id'));
		}
		if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_raw_material_stock_report.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report.raw_material_id', $this->input->post('raw_material_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report.reorder_level', $search);
			$this->db->or_like('tbl_raw_material_stock_report.total_quantity', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_article_reorder_level($length, $start, $search)
	{
		$this->db->select('tbl_article_stock_report.*,tbl_mould_parts.article_name,tbl_plant_master.plant_name,tbl_mould_parts.reorder_level as article_reorder_level');
		$this->db->from('tbl_article_stock_report');
		$this->db->join('tbl_mould_parts', 'tbl_article_stock_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_article_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_article_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_article_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_mould_parts.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		// $this->db->where('tbl_article_stock_report.total_quantity < tbl_article_stock_report.reorder_level');
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_article_stock_report.plant_id', $this->input->post('plant_id'));
		}
		if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_article_stock_report.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_article_stock_report.article_id', $this->input->post('article_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_article_stock_report.reorder_level', $search);
			$this->db->or_like('tbl_article_stock_report.total_quantity', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_article_stock_report.id', 'DESC');
		if ($length > 0) { $this->db->limit($length, $start); }

		$query = $this->db->get();
		$result = $query->result();

		$this->db->select('COUNT(tbl_article_stock_report.id) as total_count');
		$this->db->from('tbl_article_stock_report');
		$this->db->join('tbl_mould_parts', 'tbl_article_stock_report.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_article_stock_report.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_article_stock_report.is_deleted', '0');
		$this->db->where(
			'CAST(tbl_article_stock_report.total_quantity AS DECIMAL(10,2)) 
			< CAST(tbl_mould_parts.reorder_level AS DECIMAL(10,2))',
			null,
			false
		);
		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_article_stock_report.plant_id', $this->input->post('plant_id'));
		}
		if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_article_stock_report.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_article_stock_report.article_id', $this->input->post('article_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_article_stock_report.reorder_level', $search);
			$this->db->or_like('tbl_article_stock_report.total_quantity', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_raw_material_report_list($length, $start, $search)
	{
		$this->db->select('
		tbl_raw_material_stock_report_history.*,tbl_plant_master.plant_name,tbl_rm_master.rm_name,tbl_uom_master.uom_name');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where_not_in('tbl_raw_material_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);
		$this->db->where("tbl_raw_material_stock_report_history.raw_material_id !=", '');
		$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}
		$search_lower = strtolower(trim($search));
		$status_map = [
			'in' => '1',
			'out' => '2',
			'increasing adjustment' => '3',
			'decreasing adjustment' => '4',
		];

		$status_value = false;
		foreach ($status_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$status_value = $value;
				break;
			}
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);
			// $this->db->or_like('tbl_raw_material_stock_report_history.is_inward_outward', $search);
			if ($status_value !== false) {
				$this->db->or_like('is_inward_outward', $status_value);
			} else {
				$this->db->or_like('is_inward_outward', $search);
			}
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where_not_in('tbl_raw_material_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);
		$this->db->where("tbl_raw_material_stock_report_history.raw_material_id !=", '');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.is_inward_outward', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_mb_stock_report_one_plant_to_other_list($length, $start, $search)
	{
		$this->db->select('
		tbl_master_batch_stock_report_history.*,tbl_plant_master.plant_name,tbl_mb_master.name as mb_name');
		$this->db->from('tbl_master_batch_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_master_batch_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_master_batch_stock_report_history.master_batch_id', 'left');
		$this->db->where('tbl_master_batch_stock_report_history.is_deleted', '0');
		$this->db->where_not_in('tbl_master_batch_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);
		$this->db->order_by('tbl_master_batch_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('master_batch_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.master_batch_id', $this->input->post('master_batch_id'));
		}
		$search_lower = strtolower(trim($search));
		$status_map = [
			'in' => '1',
			'out' => '2',
			'increasing adjustment' => '3',
			'decreasing adjustment' => '4',
		];

		$status_value = false;
		foreach ($status_map as $label => $value) {
			if (strpos($label, $search_lower) !== false) {
				$status_value = $value;
				break;
			}
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_mb_master.name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.created_on', $search);
			// $this->db->or_like('tbl_master_batch_stock_report_history.is_inward_outward', $search);
			if ($status_value !== false) {
				$this->db->or_like('is_inward_outward', $status_value);
			} else {
				$this->db->or_like('is_inward_outward', $search);
			}
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_master_batch_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_master_batch_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_master_batch_stock_report_history.master_batch_id', 'left');
		$this->db->where('tbl_master_batch_stock_report_history.is_deleted', '0');
		$this->db->where_not_in('tbl_master_batch_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);



		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_master_batch_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_master_batch_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();

			$this->db->or_like('tbl_master_batch_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.created_on', $search);
			$this->db->or_like('tbl_master_batch_stock_report_history.is_inward_outward', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_all_article_report_list($length, $start, $search, $production)
	{
		$this->db->select('
		tbl_raw_material_stock_report_history.*,tbl_plant_master.plant_name,tbl_mould_parts.article_name');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_raw_material_stock_report_history.article_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.article_id !=', '');
		if ($production == '0') {
			$this->db->where_not_in('tbl_raw_material_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);
		} else {
			$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '5');
		}
		$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.article_id', $this->input->post('article_id'));
		}

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_mould_parts.article_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.inward_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);
			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.article_id !=', '');
		if ($production == '0') {
			$this->db->where_not_in('tbl_raw_material_stock_report_history.is_inward_outward', ['0', '3', '4', '5']);
		} else {
			$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '5');
		}
		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('article_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.article_id', $this->input->post('article_id'));
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	public function get_production_schedule_data()
	{
		$start_date = $this->input->post('filter_date');
		$plant_id = $this->input->post('plant_id');
		$machine_id = $this->input->post('machine_id');

		$this->db->select('tbl_production_schedules.*, tbl_mould_parts.article_name, tbl_group_of_article.group_of_article, tbl_plant_master.plant_name, tbl_machine_master.machine_name');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id');
		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id');
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->_apply_assigned_plants_scope('tbl_production_schedules.plant_id');

		if ($start_date != "") {
			$this->db->where('DATE(tbl_production_schedules.date)', $start_date);
		}
		if ($plant_id != "") {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if ($machine_id != "") {
			if ($machine_id != 'all') {
				$this->db->where('tbl_production_schedules.machine_id', $machine_id);
			}
		}

		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'asc');
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();

		if (!empty($result)) {
			foreach ($result as $row) {

				$raw_materials = $this->get_raw_materials($row->raw_materials != "" ? explode(',', $row->raw_materials) : []);

				$colors = $this->get_colors($row->color_id != "" ? explode(',', $row->color_id) : []);
				$bom = $this->get_artical_bom($row->article_id);
				$production_qty = $row->qty != "" ? $row->qty : 0;
				$raw_material_one_qty = 0;
				$raw_material_two_qty = 0;
				$other_rm_qty = 0;
				$master_batch_qty = 0;
				$particulars = array();
				if (!empty($bom)) {
					$particulars = $this->get_artical_particaulars($row->article_id, $bom->id);
					// $batch = $bom->batch != "" ? $bom->batch : 0;
					// $total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

					// $weight = $bom->weight != "" ? $bom->weight : 0;
					// $total_batch_wt = ceil(($weight * $total_req_batches) * 100) / 100;

					// $raw_material_one = $bom->raw_material_one != "" ? $bom->raw_material_one : 0;
					// $raw_material_one_qty = ceil((($raw_material_one / 100) * $total_batch_wt) * 100) / 100;

					// $raw_material_two = $bom->raw_material_two != "" ? $bom->raw_material_two : 0;
					// $raw_material_two_qty = ceil((($raw_material_two / 100) * $total_batch_wt) * 100) / 100;

					// $other_rm = $bom->other_rm != "" ? $bom->other_rm : 0;
					// $other_rm_qty = ceil((($other_rm / 100) * $total_batch_wt) * 100) / 100;

					// $master_batch = $bom->master_batch != "" ? $bom->master_batch : 0;
					// $master_batch_qty = ceil(($master_batch / 100) * $total_batch_wt);

					$batch = !empty($bom->batch) ? (float) $bom->batch : 0;
					$total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

					$weight = !empty($bom->weight) ? (float) $bom->weight : 0;
					$total_batch_wt = $weight * $total_req_batches;

					$raw_material_one = !empty($bom->raw_material_one) ? (float) $bom->raw_material_one : 0;
					$raw_material_one_qty = ($raw_material_one / 100) * $total_batch_wt;

					$raw_material_two = !empty($bom->raw_material_two) ? (float) $bom->raw_material_two : 0;
					$raw_material_two_qty = ($raw_material_two / 100) * $total_batch_wt;

					$other_rm = !empty($bom->other_rm) ? (float) $bom->other_rm : 0;
					$other_rm_qty = ($other_rm / 100) * $total_batch_wt;

					$master_batch = !empty($bom->master_batch) ? (float) $bom->master_batch : 0;
					$master_batch_qty = ($master_batch / 100) * $total_batch_wt;
				}
				$total_rows = (!empty($particulars) ? count($particulars) : 0) + count($raw_materials) + count($colors);
				?>
																																																																	<div id="accordion_<?= $row->id; ?>">
																																																																	<div class="card mb-2">
																																																																	<div class="card-header p-2" id="heading_<?= $row->id; ?>">
																																																																	<h5 class="mb-0">
																																																																	<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse_<?= $row->id; ?>"
																																																																	aria-expanded="false" aria-controls="collapseOne">
																																																																	<b>Machine:</b> <?= $row->machine_name; ?> | <b>Article:</b> <?= $row->article_name; ?> |
																																																																	<b>Schedule:</b>
																																																																	<?= date('d M, Y', strtotime($row->production_schedule_start_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_start_time)); ?>
																																																																	-
																																																																	<?= date('d M, Y', strtotime($row->production_schedule_end_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_end_time)); ?>
																																																																	| <b>Qty:</b> <?= $row->qty; ?>
																																																																	</button>
																																																																	</h5>
																																																																	</div>

																																																																	<div id="collapse_<?= $row->id; ?>" class="collapse" aria-labelledby="heading_<?= $row->id; ?>"
																																																																	data-parent="#accordion_<?= $row->id; ?>">
																																																																	<div class="card-body p-2">
																																																																	<table class="table table-bordered table-sm">
																																																																	<thead>
																																																																	<tr>
																																																																	<th>Machine Name</th>
																																																																	<th>Article Name</th>
																																																																	<th>Schedule</th>
																																																																	<th>Qty</th>
																																																																	<th>Raw Material</th>
																																																																	<th>Colour / Master Batch</th>
																																																																	<th>BOM</th>
																																																																	<th>Size</th>
																																																																	<th>UOM</th>
																																																																	<th>BOM Qty</th>
																																																																	<th>Qty Provided</th>
																																																																	<th>Status</th>
																																																																	<th>Action</th>
																																																																	</tr>
																																																																	</thead>
																																																																	<tbody>
																																																																	<?php
																																																																	$row_count = 1;
																																																																	if (!empty($raw_materials)) {
																																																																		$raw_count = 1;
																																																																		foreach ($raw_materials as $raw_materials_row) {
																																																																			$raw_material_exist = $this->get_production_schedule_item_rm_status('1', $row->id, $raw_materials_row->id);

																																																																			$this->db->where('raw_material_id', $raw_materials_row->id);
																																																																			$this->db->where('plant_id', $this->input->post('plant_id'));
																																																																			$this->db->where('is_deleted', '0');
																																																																			$stock_report_qty = $this->db->get('tbl_raw_material_stock_report')->row();
																																																																			$stock_qty = !empty($stock_report_qty) ? $stock_report_qty->total_quantity : 0;
																																																																			if (!empty($raw_material_exist)) {
																																																																				$available_qty = $raw_material_exist->current_qty + $stock_qty;
																																																																			} else {
																																																																				$available_qty = $stock_qty;
																																																																			}

																																																																			if ($raw_count == 1) {
																																																																				$considered_raw_qty = $raw_material_one_qty;
																																																																			} elseif ($raw_count == 2) {
																																																																				$considered_raw_qty = $raw_material_two_qty;
																																																																			} else {
																																																																				$considered_raw_qty = $other_rm_qty;
																																																																			}

																																																																			$status_label = '<span class="nothing-ready">Nothing Ready</span>';
																																																																			if (!empty($available_qty)) {
																																																																				if ($available_qty <= $considered_raw_qty) {
																																																																					$status_label = '<span class="partially-ready">Partially Ready</span>';
																																																																				} elseif ($available_qty >= $considered_raw_qty) {
																																																																					$status_label = '<span class="fully-ready">Fully Ready</span>';
																																																																				}
																																																																			}


																																																																			?>
																																																																																															<tr>
																																																																																																<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																																<td><?= $row_count == 1 ? date('d M, Y', strtotime($row->production_schedule_start_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_start_time)) . '<br>To ' . date('d M, Y', strtotime($row->production_schedule_end_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_end_time)) : ''; ?>
																																																																																																</td>
																																																																																																<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																<td><?= !empty($raw_materials) && isset($raw_materials[$row_count - 1]) ? $raw_materials[$row_count - 1]->rm_name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= $raw_materials_row->rm_name; ?></td>
																																																																																																<td>-</td>
																																																																																																<td>-</td>
																																																																																																<td>
																																																																																																	<?= $considered_raw_qty; ?>
																																																																																																	<input type="hidden" value="<?= $considered_raw_qty; ?>"
																																																																																																		name="total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>"
																																																																																																		id="total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>">
																																																																																																</td>
																																																																																																<td><input type="number" min="0" <?php if (!empty($raw_material_exist) && $raw_material_exist->item_status == '2') {
																																																																																																	echo 'readonly';
																																																																																																}
																																																																																																?>
																																																																																																	value="<?php
																																																																																																	if (!empty($raw_material_exist)) {
																																																																																																		echo $raw_material_exist->current_qty;
																																																																																																	} else {
																																																																																																		echo '0';
																																																																																																	}
																																																																																																	?>" 
																																																																																																		max="<?= $considered_raw_qty; ?>"
																																																																																																		class="form-control" 
																																																																																																		name="raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>" 
																																																																																																		id="raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>"
																																																																																																		onkeyup="validateQty(this, <?= $available_qty; ?>); 
										setStatus(
											'1',
											'<?= $row->id; ?>',
											'<?= $raw_materials_row->id; ?>',
											'#raw_material_status_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>',
											'#total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>',
											'#raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>'
										)">
																																																																																																</td>
																																																																																																<td class="color_status" id="raw_material_status_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>">
																																																																																																	<?= $status_label; ?>
																																																																																																</td>
																																																																																																<td id="raw_material_action_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>">
																																																																																																	<?php

																																																																																																	if (!empty($raw_material_exist)) {
																																																																																																		if ($raw_material_exist->item_status != '2') {
																																																																																																			?>
																																																																																																																																<button type="button" class="btn btn-success"
																																																																																																																																	onclick="setRMStatus('1','2','<?= $row->id; ?>','<?= $raw_materials_row->id; ?>','#total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_material_action_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>')">Fully
																																																																																																																																	Dispatched</button>

																																																																																																																																<button type="button" class="btn btn-warning"
																																																																																																																																	onclick="setRMStatus('1','1','<?= $row->id; ?>','<?= $raw_materials_row->id; ?>','#total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_material_action_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>')">Partially
																																																																																																																																	Dispatch</button>
																																																																																																																	<?php } else { ?>
																																																																																																																																 <!-- <button type="button" class="btn btn-success" disabled>Fully Dispatched </button> -->

																																																																																																																	<?php }
																																																																																																	} else { ?>
																																																																																																																	<button type="button" class="btn btn-success"
																																																																																																																		onclick="setRMStatus('1','2','<?= $row->id; ?>','<?= $raw_materials_row->id; ?>','#total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_material_action_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>')">Fully
																																																																																																																		Dispatched</button>

																																																																																																																	<button type="button" class="btn btn-warning"
																																																																																																																		onclick="setRMStatus('1','1','<?= $row->id; ?>','<?= $raw_materials_row->id; ?>','#total_raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_materials_qty_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>','#raw_material_action_id_<?= $row->id; ?>_<?= $raw_materials_row->id; ?>')">Partially
																																																																																																																		Dispatch</button>
																																																																																																	<?php } ?>
																																																																																																</td>
																																																																																															</tr>
																																																																																															<?php $raw_count++;
																																																																																															$row_count++;
																																																																		}
																																																																	} ?>
																																																																	<?php if (!empty($colors)) {
																																																																		foreach ($colors as $colors_row) {
																																																																			$colors_exist = $this->get_production_schedule_item_rm_status('2', $row->id, $colors_row->id);
																																																																			$considered_raw_qty = $colors_exist->required_qty > 0 ? $colors_exist->required_qty : $master_batch_qty;
																																																																			$this->db->where('master_batch_id', $colors_row->id);
																																																																			$this->db->where('plant_id', $this->input->post('plant_id'));
																																																																			$this->db->where('is_deleted', '0');
																																																																			$stock_report_qty = $this->db->get('tbl_master_batch_stock_report')->row();
																																																																			$stock_qty = !empty($stock_report_qty) ? $stock_report_qty->total_quantity : 0;
																																																																			if (!empty($colors_exist)) {
																																																																				$available_qty = $colors_exist->current_qty + $stock_qty;
																																																																			} else {
																																																																				$available_qty = $stock_qty;
																																																																			}

																																																																			$status_label = '<span class="nothing-ready">Nothing Ready</span>';
																																																																			if (!empty($available_qty)) {
																																																																				if ($available_qty <= $considered_raw_qty) {
																																																																					$status_label = '<span class="partially-ready">Partially Ready</span>';
																																																																				} elseif ($available_qty >= $considered_raw_qty) {
																																																																					$status_label = '<span class="fully-ready">Fully Ready</span>';
																																																																				}
																																																																			}
																																																																			?>
																																																																																															<tr>
																																																																																																<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																																<td><?= $row_count == 1 ? date('d M, Y', strtotime($row->production_schedule_start_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_start_time)) . '<br>To ' . date('d M, Y', strtotime($row->production_schedule_end_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_end_time)) : ''; ?>
																																																																																																</td>
																																																																																																<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																<td><?= !empty($raw_materials) && isset($raw_materials[$row_count - 1]) ? $raw_materials[$row_count - 1]->rm_name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= $colors_row->name; ?></td>
																																																																																																<td>-</td>
																																																																																																<td>-</td>
																																																																																																<td>
																																																																																																	<?= $master_batch_qty; ?>
																																																																																																	<input type="hidden" value="<?= $master_batch_qty; ?>"
																																																																																																		name="total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>"
																																																																																																		id="total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>">
																																																																																																</td>
																																																																																																<td><input type="number" <?php if (!empty($colors_exist) && $colors_exist->item_status == '2') {
																																																																																																	echo 'readonly';
																																																																																																} ?>
																																																																																																 value="<?php if (!empty($colors_exist)) {
																																																																																																	 echo $colors_exist->current_qty;
																																																																																																 } else {
																																																																																																	 echo '0';
																																																																																																 } ?>" max="<?= $master_batch_qty; ?>" 
																																																																																																		class="form-control" name="colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>"
																																																																																																		id="colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>"
																																																																																																		onkeyup="validateQty(this, <?= $available_qty; ?>); setStatus('2','<?= $row->id; ?>','<?= $colors_row->id; ?>','#color_status_id_<?= $row->id; ?>_<?= $colors_row->id; ?>','#total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>')">
																																																																																																</td>
																																																																																																<td class="color_status" id="color_status_id_<?= $row->id; ?>_<?= $colors_row->id; ?>"><?= $status_label; ?>
																																																																																																</td>
																																																																																																<td id="color_action_id_<?= $row->id; ?>_<?= $colors_row->id; ?>">
																																																																																																	<?php
																																																																																																	if (!empty($colors_exist)) {
																																																																																																		if ($colors_exist->item_status != '2') {
																																																																																																			?>
																																																																																																																																	<button type="button" class="btn btn-success"
																																																																																																																																		onclick="setRMStatus('2','2','<?= $row->id; ?>','<?= $colors_row->id; ?>','#total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#color_action_id_<?= $row->id; ?>_<?= $colors_row->id; ?>')">Fully
																																																																																																																																		Dispatched</button>

																																																																																																																																	<button type="button" class="btn btn-warning"
																																																																																																																																		onclick="setRMStatus('2','1','<?= $row->id; ?>','<?= $colors_row->id; ?>','#total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#color_action_id_<?= $row->id; ?>_<?= $colors_row->id; ?>')">Partially
																																																																																																																																		Dispatch</button>
																																																																																																																<?php } else { ?>
																																																																																																																																	<!-- <button type="button" class="btn btn-success
																		disabled">Fully Dispatched</button> -->
																																																																																																																<?php }
																																																																																																	} else { ?>
																																																																																																																		<button type="button" class="btn btn-success"
																																																																																																																			onclick="setRMStatus('2','2','<?= $row->id; ?>','<?= $colors_row->id; ?>','#total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#color_action_id_<?= $row->id; ?>_<?= $colors_row->id; ?>')">Fully
																																																																																																																			Dispatched</button>

																																																																																																																		<button type="button" class="btn btn-warning"
																																																																																																																			onclick="setRMStatus('2','1','<?= $row->id; ?>','<?= $colors_row->id; ?>','#total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>','#color_action_id_<?= $row->id; ?>_<?= $colors_row->id; ?>')">Partially
																																																																																																																			Dispatch</button>
																																																																																																	<?php } ?>
																																																																																																</td>
																																																																																															</tr>
																																																																																															<?php $row_count++;
																																																																		}
																																																																	} ?>
																																																																		<?php if (!empty($particulars)) {
																																																																			foreach ($particulars as $particulars_row) {
																																																																				$particaulars_exist = $this->get_production_schedule_item_rm_status('3', $row->id, $particulars_row->sub_category_id);
																																																																				$qty = $row->qty / ($bom->batch ?? 0);
																																																																				$bom_qty = $particulars_row->quantity * $qty;
																																																																				// $bom_qty = ceil($bom_qty);
																																																														
																																																																				$this->db->where('raw_material_id', $particulars_row->sub_category_id);
																																																																				$this->db->where('plant_id', $this->input->post('plant_id'));
																																																																				$this->db->where('is_deleted', '0');
																																																																				$stock_report_qty = $this->db->get('tbl_raw_material_stock_report')->row();
																																																																				$stock_qty = !empty($stock_report_qty) ? $stock_report_qty->total_quantity : 0;
																																																																				if (!empty($particaulars_exist)) {
																																																																					$available_qty = $particaulars_exist->current_qty + $stock_qty;
																																																																				} else {
																																																																					$available_qty = $stock_qty;
																																																																				}

																																																																				$status_label = '<span class="nothing-ready">Nothing Ready</span>';
																																																																				if (!empty($available_qty)) {
																																																																					if ($available_qty <= $bom_qty) {
																																																																						$status_label = '<span class="partially-ready">Partially Ready</span>';
																																																																					} elseif ($available_qty >= $bom_qty) {
																																																																						$status_label = '<span class="fully-ready">Fully Ready</span>';
																																																																					}
																																																																				}

																																																																				?>

																																																																																																		<tr>
																																																																																																			<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																			<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																																			<td><?= $row_count == 1 ? date('d M, Y', strtotime($row->production_schedule_start_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_start_time)) . '<br>To ' . date('d M, Y', strtotime($row->production_schedule_end_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_end_time)) : ''; ?>
																																																																																																			</td>
																																																																																																			<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																			<td><?= !empty($raw_materials) && isset($raw_materials[$row_count - 1]) ? $raw_materials[$row_count - 1]->rm_name : ''; ?>
																																																																																																			</td>
																																																																																																			<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?>
																																																																																																			</td>
																																																																																																			<td><?= $particulars_row->particulars_type; ?></td>
																																																																																																			<td><?= $particulars_row->sub_category; ?></td>
																																																																																																			<td><?= $particulars_row->uom_name; ?></td>
																																																																																																			<td>
																																																																																																				<?= $bom_qty; ?>
																																																																																																				<input type="hidden" value="<?= $bom_qty; ?>"
																																																																																																					name="total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>"
																																																																																																					id="total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>">
																																																																																																			</td>
																																																																																																			<td><input type="number" <?php if (!empty($particaulars_exist) && $particaulars_exist->item_status == '2') {
																																																																																																				echo 'readonly';
																																																																																																			} ?>
															
																																																																																																			value="<?php if (!empty($particaulars_exist)) {
																																																																																																				echo $particaulars_exist->current_qty;
																																																																																																			} else {
																																																																																																				echo '0';
																																																																																																			} ?>" max="<?= $bom_qty; ?>"
																																																																																																					class="form-control" name="particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>"
																																																																																																					id="particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>"
																																																																																																					onkeyup="validateQty(this, <?= $available_qty; ?>); setStatus('3','<?= $row->id; ?>','<?= $particulars_row->sub_category_id; ?>','#particulars_status_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>')">
																																																																																																			</td>
																																																																																																			<td class="color_status" id="particulars_status_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>">
																																																																																																				<?= $status_label; ?>
																																																																																																			</td>
																																																																																																			<td id="particulars_action_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>">
																																																																																																				<?php
																																																																																																				if (!empty($particaulars_exist)) {
																																																																																																					if ($particaulars_exist->item_status != '2') {
																																																																																																						?>
																																																																																																																																			<button type="button" class="btn btn-success"
																																																																																																																																				onclick="setRMStatus('3','3','<?= $row->id; ?>','<?= $particulars_row->sub_category_id; ?>','#total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particulars_action_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>')">Fully
																																																																																																																																				Dispatched</button>

																																																																																																																																			<button type="button" class="btn btn-warning"
																																																																																																																																				onclick="setRMStatus('3','1','<?= $row->id; ?>','<?= $particulars_row->sub_category_id; ?>','#total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particulars_action_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>')">Partially
																																																																																																																																				Dispatch</button>
																																																																																																																				<?php } else { ?>
																																																																																																																																				<!-- <button type="button" class="btn btn-success" disabled>Fully Dispatched</button> -->
																																																																																																																				<?php }
																																																																																																				} else { ?>
																																																																																																																				<button type="button" class="btn btn-success"
																																																																																																																					onclick="setRMStatus('3','3','<?= $row->id; ?>','<?= $particulars_row->sub_category_id; ?>','#total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particulars_action_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>')">Fully
																																																																																																																					Dispatched</button>

																																																																																																																				<button type="button" class="btn btn-warning"
																																																																																																																					onclick="setRMStatus('3','1','<?= $row->id; ?>','<?= $particulars_row->sub_category_id; ?>','#total_particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particular_qty_<?= $row->id; ?>_<?= $particulars_row->id; ?>','#particulars_action_id_<?= $row->id; ?>_<?= $particulars_row->id; ?>')">Partially
																																																																																																																					Dispatch</button>
																																																																																																					<?php } ?>
																																																																																																					</td>
																																																																																																				</tr>
																																																																																																				<?php $row_count++;
																																																																			}
																																																																		} ?>
																																																																				</tbody>
																																																																			</table>
																																																																		</div>
																																																																	</div>
																																																																</div>
																																																															</div>
																																																															<?php
			}
		} else {
			?>
																																															<label class="error">Schedules not available</label>
																																															<?php
		}
	}
	public function get_return_stock_production_schedule_data()
	{
		$start_date = $this->input->post('filter_date');
		$plant_id = $this->input->post('plant_id');
		$machine_id = $this->input->post('machine_id');

		$this->db->select('
			tbl_production_schedules.*,
			tbl_mould_parts.article_name,
			tbl_group_of_article.group_of_article,
			tbl_plant_master.plant_name,
			tbl_machine_master.machine_name
		');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id');
		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id');
		$this->db->where('tbl_production_schedules.is_deleted', '0');

		if (!empty($start_date)) {
			$this->db->where('DATE(tbl_production_schedules.date)', $start_date);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if (!empty($machine_id) && $machine_id !== 'all') {
			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		}

		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'asc');
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();

		if (!empty($result)) {
			foreach ($result as $row) {
				$raw_materials = $this->get_raw_materials(
					$row->raw_materials != "" ? explode(',', $row->raw_materials) : []
				);

				$colors = $this->get_colors($row->color_id != "" ? explode(',', $row->color_id) : []);
				$bom = $this->get_artical_bom($row->article_id);
				$production_qty = !empty($row->qty) ? $row->qty : 0;
				$raw_material_one_qty = 0;
				$raw_material_two_qty = 0;
				$other_rm_qty = 0;
				$master_batch_qty = 0;
				$particulars = [];

				if (!empty($bom)) {
					$particulars = $this->get_artical_particaulars($row->article_id, $bom->id);

					// $batch = !empty($bom->batch) ? $bom->batch : 0;
					// $total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

					// $weight = !empty($bom->weight) ? $bom->weight : 0;
					// $total_batch_wt = round($weight * $total_req_batches, 2);

					// $raw_material_one = !empty($bom->raw_material_one) ? $bom->raw_material_one : 0;
					// $raw_material_one_qty = round(($raw_material_one / 100) * $total_batch_wt, 2);

					// $raw_material_two = !empty($bom->raw_material_two) ? $bom->raw_material_two : 0;
					// $raw_material_two_qty = round(($raw_material_two / 100) * $total_batch_wt, 2);

					// $other_rm = !empty($bom->other_rm) ? $bom->other_rm : 0;
					// $other_rm_qty = round(($other_rm / 100) * $total_batch_wt, 2);

					// $master_batch = !empty($bom->master_batch) ? $bom->master_batch : 0;
					// $master_batch_qty = round(($master_batch / 100) * $total_batch_wt, 2);

					$batch = !empty($bom->batch) ? (float) $bom->batch : 0;
					$total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

					$weight = !empty($bom->weight) ? (float) $bom->weight : 0;
					$total_batch_wt = $weight * $total_req_batches;

					$raw_material_one = !empty($bom->raw_material_one) ? (float) $bom->raw_material_one : 0;
					$raw_material_one_qty = ($raw_material_one / 100) * $total_batch_wt;

					$raw_material_two = !empty($bom->raw_material_two) ? (float) $bom->raw_material_two : 0;
					$raw_material_two_qty = ($raw_material_two / 100) * $total_batch_wt;

					$other_rm = !empty($bom->other_rm) ? (float) $bom->other_rm : 0;
					$other_rm_qty = ($other_rm / 100) * $total_batch_wt;

					$master_batch = !empty($bom->master_batch) ? (float) $bom->master_batch : 0;
					$master_batch_qty = ($master_batch / 100) * $total_batch_wt;
				}

				$total_rows = (!empty($particulars) ? count($particulars) : 0)
					+ count($raw_materials)
					+ count($colors);

				// --------------------
				// HTML STARTS HERE
				// --------------------
				?>
				
																																																												<div id="accordion_<?= $row->id; ?>">
																																																													<div class="card mb-2">
																																																														<div class="card-header p-2" id="heading_<?= $row->id; ?>">
																																																															<h5 class="mb-0">
																																																																<button class="btn btn-link collapsed" 
																																																																		data-toggle="collapse" 
																																																																		data-target="#collapse_<?= $row->id; ?>"
																																																																		aria-expanded="false" 
																																																																		aria-controls="collapseOne">
																																																																	<b>Machine:</b> <?= $row->machine_name; ?> | 
																																																																	<b>Article:</b> <?= $row->article_name; ?> | 
																																																																	<b>Schedule:</b>
																																																																	<?= date('d M, Y', strtotime($row->production_schedule_start_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_start_time)); ?>
																																																																	-
																																																																	<?= date('d M, Y', strtotime($row->production_schedule_end_date)) . ' ' . date('h:i A', strtotime($row->production_schedule_end_time)); ?>
																																																																	| <b>Qty:</b> <?= $row->qty; ?>
																																																																</button>
																																																															</h5>
																																																														</div>

																																																														<div id="collapse_<?= $row->id; ?>" 
																																																															class="collapse" 
																																																															aria-labelledby="heading_<?= $row->id; ?>"
																																																															data-parent="#accordion_<?= $row->id; ?>">
																																																															<div class="card-body p-2">
																																																																<table class="table table-bordered table-sm">
																																																																	<thead>
																																																																		<tr>
																																																																			<th>Machine Name</th>
																																																																			<th>Article Name</th>
																																																																			<th>Qty</th>
																																																																			<th>Raw Material</th>
																																																																			<th>Colour / Master Batch</th>
																																																																			<th>BOM</th>
																																																																			<th>Size</th>
																																																																			<th>UOM</th>
																																																																			<th>BOM Qty</th>
																																																																			<th>Qty Provided</th>
																																																																			<th>Return Qty</th>
																																																																			<th>Action</th>
																																																																		</tr>
																																																																	</thead>
																																																																	<tbody>
																																																																		<?php
																																																																		$row_count = 1;
																																																																		if (!empty($raw_materials)) {
																																																																			$raw_count = 1;
																																																																			foreach ($raw_materials as $raw_materials_row) {
																																																																				$raw_material_exist = $this->get_production_schedule_item_rm_status(
																																																																					'1',
																																																																					$row->id,
																																																																					$raw_materials_row->id
																																																																				);
																																																																				// echo"<pre>";print_r($raw_material_exist);
																																																														
																																																																				if ($raw_count == 1) {
																																																																					$considered_raw_qty = $raw_material_one_qty;
																																																																				} elseif ($raw_count == 2) {
																																																																					$considered_raw_qty = $raw_material_two_qty;
																																																																				} else {
																																																																					$considered_raw_qty = $other_rm_qty;
																																																																				}

																																																																				$max_return_qty = 0;

																																																																				$max_return_qty = $raw_material_exist->current_qty;
																																																																				?>
																																																																																																<?php
																																																																																																// use $row->id and $raw_count to make IDs unique
																																																																																																$uid = $row->id . '_' . $raw_count;
																																																																																																$total_input_id = "total_raw_materials_qty_{$uid}";
																																																																																																$qty_input_id = "raw_materials_qty_{$uid}";
																																																																																																$return_input_id = "raw_materials_return_qty_{$uid}";
																																																																																																$action_div_id = "raw_material_action_id_{$uid}";
																																																																																																?>
																																																																																																<tr>
																																																																																																	<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																	<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																																	<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																	<td><?= $raw_materials_row->rm_name; ?></td>
																																																																																																	<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?></td>
																																																																																																	<td><?= $raw_materials_row->rm_name; ?></td>
																																																																																																	<td>-</td>
																																																																																																	<td>-</td>
																																																																																																	<td>
																																																																																																		<?= $considered_raw_qty; ?>
																																																																																																		<input type="hidden"
																																																																																																			value="<?= $considered_raw_qty; ?>"
																																																																																																			name="<?= $total_input_id; ?>"
																																																																																																			id="<?= $total_input_id; ?>">
																																																																																																	</td>
																																																																																																	<td>
																																																																																																		<input type="number" min="0" step="0.01"
																																																																																																			value="<?= !empty($raw_material_exist) ? $raw_material_exist->current_qty : 0; ?>"
																																																																																																			class="form-control"
																																																																																																			name="<?= $qty_input_id; ?>"
																																																																																																			id="<?= $qty_input_id; ?>"
																																																																																																			readonly>
																																																																																																	</td>
																																																																																																	<td>
																																																																																																		<input 
																																																																																																			type="number"
																																																																																																			min="0"
																																																																																																			step="0.01"
																																																																																																			max="<?= $max_return_qty ?>"
																																																																																																			value="<?= (!empty($raw_material_exist) && $raw_material_exist->return_stock_qty) ? $raw_material_exist->return_stock_qty : ''; ?>"
																																																																																																			class="form-control"
																																																																																																			name="<?= $return_input_id; ?>"
																																																																																																			id="<?= $return_input_id; ?>"
																																																																																																			oninput="validateMax(this);"
																																																																																																			onblur="formatDecimals(this);"
																																																																																																			<?= (!empty($raw_material_exist) && $raw_material_exist->return_stock_qty != 0) ? 'readonly' : ''; ?>
																																																																																																		>
																																																																																																	</td>
																																																																																																	<td id="<?= $action_div_id; ?>">
																																																																																																		<button type="button" class="btn btn-success"
																																																																																																				onclick="setRMStatuFOrReturn(
																	'1',
																	'<?= $row->id; ?>',
																	'<?= $raw_materials_row->id; ?>',
																	'#<?= $total_input_id; ?>',
																	'#<?= $qty_input_id; ?>',
																	'#<?= $action_div_id; ?>',
																	'#<?= $return_input_id; ?>'
																)"
																																																																																																			<?= (!empty($raw_material_exist) && $raw_material_exist->return_stock_qty > 0) ? 'disabled' : ''; ?>>
																																																																																																			Save
																																																																																																		</button>
																																																																																																	</td>
																																																																																																</tr>
																																																																																																<?php
																																																																																																$raw_count++;
																																																																			}
																																																																		}
																																																																		?>
																																																																		<?php if (!empty($colors)) {
																																																																			foreach ($colors as $colors_row) {
																																																																				$colors_exist = $this->get_production_schedule_item_rm_status('2', $row->id, $colors_row->id);
																																																																				$considered_raw_qty = $colors_exist->required_qty > 0 ? $colors_exist->required_qty : $master_batch_qty;
																																																																				$this->db->where('master_batch_id', $colors_row->id);
																																																																				$this->db->where('plant_id', $this->input->post('plant_id'));
																																																																				$this->db->where('is_deleted', '0');
																																																																				$stock_report_qty = $this->db->get('tbl_master_batch_stock_report')->row();
																																																																				$stock_qty = !empty($stock_report_qty) ? $stock_report_qty->total_quantity : 0;
																																																																				if (!empty($colors_exist)) {
																																																																					$available_qty = $colors_exist->current_qty + $stock_qty;
																																																																				} else {
																																																																					$available_qty = $stock_qty;
																																																																				}
																																																																				$max_return_qty = 0;
																																																																				$max_return_qty = $colors_exist->required_qty;

																																																																				?>
																																																																																															<tr>
																																																																																																<?php
																																																																																																// use $row->id and $raw_count to make IDs unique
																																																																																																$uid = $row->id . '_' . $raw_count;
																																																																																																$total_input_id = "total_master_batch_qty_{$uid}";
																																																																																																$qty_input_id = "master_batch_qty_{$uid}";
																																																																																																$return_input_id = "master_batch_return_qty_{$uid}";
																																																																																																$action_div_id = "master_batch_action_id_{$uid}";
																																																																																																?>
																																																																																																<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																									
																																																																																																<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																<td><?= !empty($raw_materials) && isset($raw_materials[$row_count - 1]) ? $raw_materials[$row_count - 1]->rm_name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?>
																																																																																																</td>
																																																																																																<td><?= $colors_row->name; ?></td>
																																																																																																<td>-</td>
																																																																																																<td>-</td>
																																																																																																<td>
																																																																																																	<?= $master_batch_qty; ?>
																																																																																																	<input type="hidden" value="<?= $master_batch_qty; ?>"
																																																																																																		name="total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>"
																																																																																																		id="total_colors_qty_<?= $row->id; ?>_<?= $colors_row->id; ?>">
																																																																																																</td>
																																																																																																<td>
																																																																																																	<input type="number" min="0" step="0.01"
																																																																																																		value="<?= !empty($colors_exist) ? $colors_exist->current_qty : 0; ?>"
																																																																																																		class="form-control"
																																																																																																		name="<?= $qty_input_id; ?>"
																																																																																																		id="<?= $qty_input_id; ?>"
																																																																																																		readonly>
																																																																																																</td>
																																																																																																<td>
																																																																																																	<input type="number"
																																																																																																		min="0"
																																																																																																		step="0.01"
																																																																																																		max="<?= $max_return_qty ?>"
																																																																																																		value="<?= (!empty($colors_exist) && $colors_exist->return_stock_qty) ? $colors_exist->return_stock_qty : ''; ?>"
																																																																																																		class="form-control"
																																																																																																		name="<?= $return_input_id; ?>"
																																																																																																		id="<?= $return_input_id; ?>"
																																																																																																		oninput="validateMax(this);"
																																																																																																		<?= (!empty($colors_exist) && $colors_exist->return_stock_qty != 0) ? 'readonly' : ''; ?>>
																																																																																																</td>
																																																																																										
																																																																																									
																																																																																																<td id="<?= $action_div_id; ?>">
																																																																																																		<button type="button" class="btn btn-success"
																																																																																																				onclick="setRMStatuFOrReturn(
																	'2',
																	'<?= $row->id; ?>',
																	'<?= $colors_row->id; ?>',
																	'#<?= $total_input_id; ?>',
																	'#<?= $qty_input_id; ?>',
																	'#<?= $action_div_id; ?>',
																	'#<?= $return_input_id; ?>'
																)"
																																																																																																			<?= (!empty($colors_exist) && $colors_exist->return_stock_qty > 0) ? 'disabled' : ''; ?>>
																																																																																																			Save
																																																																																																		</button>
																																																																																																	</td>
																																																																																															</tr>
																																																																																															<?php $row_count++;
																																																																			}
																																																																		} ?>
																																																																		<?php if (!empty($particulars)) {
																																																																			foreach ($particulars as $particulars_row) {
																																																																				$particaulars_exist = $this->get_production_schedule_item_rm_status('3', $row->id, $particulars_row->sub_category_id);
																																																																				$qty = $row->qty / ($bom->batch ?? 0);
																																																																				$bom_qty = $particulars_row->quantity * $qty;
																																																																				// $bom_qty = ceil($bom_qty);
																																																														
																																																																				$this->db->where('raw_material_id', $particulars_row->sub_category_id);
																																																																				$this->db->where('plant_id', $this->input->post('plant_id'));
																																																																				$this->db->where('is_deleted', '0');
																																																																				$stock_report_qty = $this->db->get('tbl_raw_material_stock_report')->row();

																																																																				$stock_qty = !empty($stock_report_qty) ? $stock_report_qty->total_quantity : 0;
																																																																				if (!empty($particaulars_exist)) {
																																																																					$available_qty = $particaulars_exist->current_qty + $stock_qty;
																																																																				} else {
																																																																					$available_qty = $stock_qty;
																																																																				}
																																																																				$max_return_qty = 0;
																																																																				$max_return_qty = $particaulars_exist->required_qty;
																																																																				// $max_return_qty = ceil($particaulars_exist->required_qty);
																																																														

																																																																				?>

																																																																																																		<tr>
																																																																																																			<?php
																																																																																																			// use $row->id and $raw_count to make IDs unique
																																																																																																			$uid = $row->id . '_' . $raw_count;
																																																																																																			$total_input_id = "total_particaulars_qty_{$uid}";
																																																																																																			$qty_input_id = "particaulars_qty_{$uid}";
																																																																																																			$return_input_id = "particaulars_return_qty_{$uid}";
																																																																																																			$action_div_id = "particaulars_action_id_{$uid}";
																																																																																																			?>
																																																																																																			<td><?= $row_count == 1 ? $row->machine_name : ''; ?></td>
																																																																																																			<td><?= $row_count == 1 ? $row->article_name : ''; ?></td>
																																																																																													
																																																																																																			<td><?= $row_count == 1 ? $row->qty : ''; ?></td>
																																																																																																			<td><?= !empty($raw_materials) && isset($raw_materials[$row_count - 1]) ? $raw_materials[$row_count - 1]->rm_name : ''; ?>
																																																																																																			</td>
																																																																																																			<td><?= !empty($colors) && isset($colors[$row_count - 1]) ? $colors[$row_count - 1]->name : ''; ?>
																																																																																																			</td>
																																																																																																			<td><?= $particulars_row->particulars_type; ?></td>
																																																																																																			<td><?= $particulars_row->sub_category; ?></td>
																																																																																																			<td><?= $particulars_row->uom_name; ?></td>
																																																																																																			<?php /*
																																																																																																	   <td>
																																																																																																		   <?= ceil($bom_qty); ?>
																																																																																																		   <input type="hidden"
																																																																																																			   value="<?= ceil($bom_qty); ?>"
																																																																																																			   name="<?= $total_input_id; ?>"
																																																																																																			   id="<?= $total_input_id; ?>">
																																																																																																	   </td>
																																																																																																	   */ ?>
																																																																																																			<td>
																																																																																																				<?= $bom_qty; ?>
																																																																																																				<input type="hidden"
																																																																																																					value="<?= $bom_qty; ?>"
																																																																																																					name="<?= $total_input_id; ?>"
																																																																																																					id="<?= $total_input_id; ?>">
																																																																																																			</td>
																																																																																																			<td>
																																																																																																				<input type="number" min="0" step="0.01"
																																																																																																					value="<?= !empty($particaulars_exist) ? $particaulars_exist->current_qty : 0; ?>"
																																																																																																					class="form-control"
																																																																																																					name="<?= $qty_input_id; ?>"
																																																																																																					id="<?= $qty_input_id; ?>"
																																																																																																					readonly>
																																																																																																			</td>
																																																																																																			<td>
																																																																																																				<input type="number"
																																																																																																					min="0"
																																																																																																					step="0.01"
																																																																																																					max="<?= $max_return_qty ?>"
																																																																																																					value="<?= (!empty($particaulars_exist) && $particaulars_exist->return_stock_qty) ? $particaulars_exist->return_stock_qty : ''; ?>"
																																																																																																					class="form-control"
																																																																																																					name="<?= $return_input_id; ?>"
																																																																																																					id="<?= $return_input_id; ?>"
																																																																																																					oninput="validateMax(this);"
																																																																																																					<?= (!empty($particaulars_exist) && $particaulars_exist->return_stock_qty != 0) ? 'readonly' : ''; ?>>
																																																																																																			</td>
																																																																																													
																																																																																							
																																																																																																			<td id="<?= $action_div_id; ?>">
																																																																																																		<button type="button" class="btn btn-success"
																																																																																																				onclick="setRMStatuFOrReturn(
																	'3',
																	'<?= $row->id; ?>',
																	'<?= $particaulars_exist->item_table_id; ?>',
																	'#<?= $total_input_id; ?>',
																	'#<?= $qty_input_id; ?>',
																	'#<?= $action_div_id; ?>',
																	'#<?= $return_input_id; ?>'
																)"
																																																																																																			<?= (!empty($particaulars_exist) && $particaulars_exist->return_stock_qty > 0) ? 'disabled' : ''; ?>>
																																																																																																			Save
																																																																																																		</button>
																																																																																																	</td>
																																																																																																				</tr>
																																																																																																				<?php $row_count++;
																																																																			}
																																																																		} ?>
																																																																	</tbody>
																																																																</table>
																																																															</div>
																																																														</div>
																																																													</div>
																																																												</div>
																																																												<?php
			}
		} else {
			echo '<label class="error">Schedules not available</label>';
		}
	}
	public function get_all_return_stock_raw_material_report_list($length, $start, $search)
	{
		$this->db->select('
		tbl_raw_material_stock_report_history.*,tbl_plant_master.plant_name,tbl_rm_master.rm_name,tbl_uom_master.uom_name');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '6');
		$this->db->order_by('tbl_raw_material_stock_report_history.id', 'DESC');


		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.date) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}


		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.return_stock_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);

			$this->db->group_end();
		}

		if ($length > 0) { $this->db->limit($length, $start); }
		$query = $this->db->get();
		$result = $query->result();

		// Count query
		$this->db->select('COUNT(*) as total_count');
		$this->db->from('tbl_raw_material_stock_report_history');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_raw_material_stock_report_history.raw_material_id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_raw_material_stock_report_history.uom_id', 'left');
		$this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
		$this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '6');



		// Filter by date
		if ($this->input->post('search_date') != "") {
			$exp = explode("to", $this->input->post('search_date'));
			if (isset($exp[0]) && isset($exp[1])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) >=', date("Y-m-d", strtotime($exp[0])));
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on) <=', date("Y-m-d", strtotime($exp[1])));
			} else if (isset($exp[0])) {
				$this->db->where('DATE(tbl_raw_material_stock_report_history.created_on)', date("Y-m-d", strtotime($exp[0])));
			}
		}

		if ($this->input->post('plant_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
		}
		if ($this->input->post('raw_material_id') != "") {
			$this->db->where('tbl_raw_material_stock_report_history.raw_material_id', $this->input->post('raw_material_id'));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_rm_master.rm_name', $search);
			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('tbl_uom_master.uom_name', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.opening_stock', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.return_stock_qty', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.total_quantity', $search);
			$this->db->or_like('tbl_raw_material_stock_report_history.created_on', $search);
			$this->db->group_end();
		}

		$count_query = $this->db->get();
		$total_count = $count_query->row()->total_count ?? 0;

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}
	private function get_raw_material_by_id($id)
	{
		return $this->db->where('id', $id)->get('tbl_rm_master')->row();
	}
	public function get_required_stock_list_data($length, $start, $search)
	{
		$start_date = $this->input->post('filter_date');
		$plant_id = $this->input->post('plant_id');
		$raw_material_id = $this->input->post('raw_material_id');

		$this->db->select('tbl_production_schedules.*, tbl_plant_master.plant_name');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
		$this->db->where('tbl_production_schedules.is_deleted', '0');

		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if (!empty($raw_material_id)) {
			$this->db->where("FIND_IN_SET($raw_material_id, tbl_production_schedules.raw_materials) >", 0);
			$this->db->group_by('tbl_production_schedules.raw_materials');
		}

		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'asc');
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();
		$merged_output = [];

		if (!empty($result)) {
			foreach ($result as $row) {
				$raw_material_ids = !empty($row->raw_materials) ? explode(',', $row->raw_materials) : [];
				$bom = $this->get_artical_bom($row->article_id);
				$production_qty = !empty($row->qty) ? $row->qty : 0;

				$raw_material_one_qty = 0;
				if (!empty($bom)) {
					$batch = !empty($bom->batch) ? $bom->batch : 0;
					$total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

					$weight = !empty($bom->weight) ? $bom->weight : 0;
					$total_batch_wt = round($weight * $total_req_batches, 2);

					$raw_material_one = !empty($bom->raw_material_one) ? $bom->raw_material_one : 0;
					$raw_material_one_qty = ceil(($raw_material_one / 100) * $total_batch_wt);
				}

				if (!empty($raw_material_ids)) {
					foreach ($raw_material_ids as $rm_id) {
						$rm_data = $this->get_raw_material_by_id($rm_id);
						$rm_stock_qty = $this->db
							->select('total_quantity')
							->where('raw_material_id', $rm_data->id)
							->where('plant_id', $row->plant_id)
							->where('is_deleted', '0')
							->get('tbl_raw_material_stock_report')
							->row();
						if (!empty($rm_data)) {
							$key = $row->plant_name . '_' . $rm_data->id;
							if (!isset($merged_output[$key])) {
								$merged_output[$key] = [
									'id' => $rm_data->id,
									'rm_name' => $rm_data->rm_name,
									'plant_name' => $row->plant_name,
									'raw_material_one_qty' => 0,
									'stock_qty' => !empty($rm_stock_qty) ? $rm_stock_qty->total_quantity : 0
								];
							}

							$merged_output[$key]['raw_material_one_qty'] += $raw_material_one_qty;
							if ($rm_stock_qty->total_quantity > $merged_output[$key]['raw_material_one_qty']) {
								$merged_output[$key]['need_to_inward'] = 0;
							} else {
								$merged_output[$key]['need_to_inward'] = $merged_output[$key]['raw_material_one_qty'] - $merged_output[$key]['stock_qty'];
							}

						}
					}
				}
			}
		}

		$final_output = array_values($merged_output);
		if (!empty($raw_material_id)) {
			$final_output = array_filter($final_output, function ($item) use ($raw_material_id) {
				return $item['id'] == $raw_material_id;
			});
		}
		if (!empty($search)) {
			$final_output = array_filter($final_output, function ($item) use ($search) {
				$search = strtolower($search);
				return strpos(strtolower($item['plant_name']), $search) !== false ||
					strpos(strtolower($item['rm_name']), $search) !== false ||
					strpos((string) $item['raw_material_one_qty'], $search) !== false ||
					strpos((string) $item['stock_qty'], $search) !== false ||
					strpos((string) $item['need_to_inward'], $search) !== false;
			});
		}

		$filtered_total = count($final_output);


		$final_output = array_slice($final_output, $start, $length);

		return [
			'final_output' => $final_output,
			'total_count' => $filtered_total
		];
	}

	public function set_mb_inward_form()
	{
		$inward_no = $this->generate_inward_no();
		$inward_date = date('Y-m-d');
		$data = array(
			'inward_no' => $inward_no,
			'inward_date' => $inward_date,
			'gate_entry_no' => $this->input->post('gate_entry_no'),
			'party_id' => $this->input->post('supplier_name'),
			'plant_id' => $this->input->post('plant_id'),
			'inward_for' => '1', // 1 for master batch inward
			'gate_entry_date' => $this->input->post('gate_entry_date'),
			'updated_on' => date('Y-m-d H:i:s'),
		);
		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_inward', $data);
		$database_inward_id = $this->db->insert_id();

		$raw_material_ids = $this->input->post('color_id');
		$quantity = $this->input->post('quantity');
		$ratee = $this->input->post('rate');

		if (!empty($quantity) && is_array($quantity) && !empty($raw_material_ids) && is_array($raw_material_ids)) {
			foreach ($raw_material_ids as $key => $raw_material_id) {
				$inward_quantity = isset($quantity[$key]) ? $quantity[$key] : 0;
				if ($inward_quantity) {
					$raw_m__id = isset($raw_material_ids[$key]) ? (int) $raw_material_ids[$key] : 0;
					$rate = isset($ratee[$key]) ? $ratee[$key] : 0;

					$inward_data = array(
						'inward_no' => $inward_no,
						'plant_id' => $this->input->post('plant_id'),
						'database_inward_id' => $database_inward_id,
						'master_batch_id' => $raw_m__id,
						'inward_quantity' => $inward_quantity,
						'rate' => $rate,
						'inward_for' => '1', // 1 for master batch inward
						'created_on' => date('Y-m-d H:i:s'),
					);

					$this->db->insert('tbl_inward_order_data', $inward_data);

					$this->db->select('total_quantity');
					$this->db->from('tbl_master_batch_stock_report');
					$this->db->where('master_batch_id', $raw_m__id);
					$this->db->where('plant_id', $this->input->post('plant_id'));
					$this->db->where('is_deleted', '0');
					$result = $this->db->get();
					$opening_stock = 0;
					if ($result && $result->num_rows() > 0) {
						$opening_stock = $result->row()->total_quantity;
						$updated_quantity = $result->row()->total_quantity + $inward_quantity;
					} else {
						$updated_quantity = $inward_quantity;
					}
					if ($result && $result->num_rows() > 0) {
						$this->db->set('total_quantity', $updated_quantity);
						$this->db->where('plant_id', $this->input->post('plant_id'));
						$this->db->where('master_batch_id', $raw_m__id);
						$this->db->update('tbl_master_batch_stock_report');
					} else {
						$inward_stock_data = array(
							'master_batch_id' => $raw_m__id,
							'plant_id' => $this->input->post('plant_id'),
							'total_quantity' => $inward_quantity,
							'created_on' => date('Y-m-d H:i:s'),
						);
						$this->db->insert('tbl_master_batch_stock_report', $inward_stock_data);
					}
					$inward_stock_history_data = array(
						'master_batch_id' => $raw_m__id,
						'plant_id' => $this->input->post('plant_id'),
						'opening_stock' => $opening_stock,
						'total_quantity' => $updated_quantity,
						'inward_qty' => $inward_quantity,
						'rate' => $rate,
						'is_inward_outward' => '0',
						'date' => date('Y-m-d'),
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_master_batch_stock_report_history', $inward_stock_history_data);
					log_stock_transaction([
						'item_type' => 'master_batch', 'item_id' => $raw_m__id,
						'plant_id'  => $this->input->post('plant_id'),
						'transaction_type' => 'Inward (Supplier)', 'movement_type' => 'IN',
						'qty' => $inward_quantity, 'balance_qty' => $updated_quantity,
						'reference_no' => $inward_no ?? null, 'reference_source' => 'inward',
						'created_by' => $this->session->userdata('id'),
					]);
				}
			}
		}
		$extra_payment_option_ids = $this->input->post('extra_payment_option_ids');
		$trap_hamali_amount = $this->input->post('trap_hamali_amount');

		if (!empty($trap_hamali_amount) && is_array($trap_hamali_amount) && !empty($extra_payment_option_ids) && is_array($extra_payment_option_ids)) {
			foreach ($extra_payment_option_ids as $key => $extra_payment_id) {
				$amount = isset($trap_hamali_amount[$key]) ? (int) $trap_hamali_amount[$key] : 0;
				if ($amount) {
					$extra_payment_option_id = (int) $extra_payment_id;

					$extra_payment_data = array(
						'inward_no' => $inward_no,
						'database_inward_id' => $database_inward_id,
						'trap_hamali_amount' => $amount,
						'extra_payment_option_id' => $extra_payment_option_id,
						'inward_for' => '1', // 1 for master batch inward
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_inward_extra_charges', $extra_payment_data);

				}
			}
		}

		// Notification Work to MB INward 
		$title = 'MB Inward Update';
		$description = 'New Master Batch Inward ' . $inward_no . ' updated by ' . $this->session->userdata('name') . '. Please review';
		$landing_page = 'master_batch_inward_list';
		$notification_according = '1';//means according department
		$departments = [11, 19]; // 11 = Accounts Department, 19 = Purchase Department
		$departments_str = implode(',', $departments);
		$notification_data = array(
			'notification_title' => $title,
			'notification_description' => $description,
			'notification_department' => $departments_str,
			'order_id' => $inward_no,
			'plant_id' => $this->input->post('plant_id'),
			'created_on' => date('Y-m-d H:i:s')
		);
		$this->db->insert('tbl_notifications', $notification_data);

		$this->send_task_notification_by_token(51, $title, $description, $landing_page, $notification_according, $this->input->post('plant_id'));

		return 1;
	}

	private function generate_inward_no()
	{
		$month_year = date('m-Y');
		$this->db->select('inward_no');
		$this->db->from('tbl_inward');
		$this->db->like('inward_no', 'INW-' . $month_year, 'after');
		$this->db->order_by('inward_no', 'DESC');
		$this->db->limit(1);
		$result_user = $this->db->get();

		if ($result_user && $result_user->num_rows() > 0) {
			$latest_inward_no = $result_user->row()->inward_no;
			preg_match('/(\d{3})$/', $latest_inward_no, $matches);

			if ($matches) {
				$next_number = intval($matches[0]) + 1;
				return 'INW-' . $month_year . '-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
			}
		}

		return 'INW-' . $month_year . '-001';
	}
	public function set_master_batch_requistion_data()
	{
		$request_no = $this->generate_request_no();
		$request_date = date('Y-m-d');

		$data = array(
			'request_no' => $request_no,
			'request_date' => $request_date,
			'plant_id' => $this->input->post('plant_id'),
			'is_article_or_rm_material' => '2',
			'my_plant_id' => $this->session->userdata('assign_plant_id'),
			'employee_id' => $this->session->userdata('id'),
			'created_on' => date('Y-m-d H:i:s'),
		);
		$this->db->insert('tbl_rm_request_qty', $data);
		$database_request_id = $this->db->insert_id();

		$master_batch_ids = $this->input->post('master_batch_ids');
		$quantity = $this->input->post('quantity');
		$remarks = $this->input->post('remark');
		if (!empty($quantity) && is_array($quantity) && !empty($master_batch_ids) && is_array($master_batch_ids)) {
			foreach ($master_batch_ids as $key => $color_id) {
				$request_quantity = isset($quantity[$key]) ? $quantity[$key] : 0;
				if ($request_quantity) {
					$master_batch_id = isset($master_batch_ids[$key]) ? (int) $master_batch_ids[$key] : 0;

					$request_data = array(
						'request_no' => $request_no,
						'database_request_id' => $database_request_id,
						'master_batch_id' => $master_batch_id,
						'request_quantity' => $request_quantity,
						'remark' => isset($remarks[$key]) ? $remarks[$key] : '',
						'plant_id' => $this->input->post('plant_id'),
						'my_plant_id' => $this->session->userdata('assign_plant_id'),
						'is_article_or_rm_material' => '2',
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_request_rm_qty_data', $request_data);
				}
			}
		}
		return 1;
	}

	public function get_rm_item_stock_purchase_list()
	{
		$date_range = $this->input->post('date');
		$raw_material_id = $this->input->post('raw_material_id');
		$plant_id = $this->input->post('plant_id');
		$start = '';
		$end   = '';

		// âœ… DATE FILTER
		if (!empty($date_range)) {
			if (strpos($date_range, ' to ') !== false) {
				$dates = explode(' to ', $date_range);
				if (count($dates) == 2) {
					$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
					$end   = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				}else if (count($dates) == 1) {
					$single_date = trim($dates[0]);
					$start = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
					$end = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				}

			} else {
				$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($date_range))));
				$end   = $start;
			}
		}else {
			$end   = date('Y-m-d');
			$start = date('Y-m-d', strtotime('-30 days'));
		}
		$this->db->select('tbl_inward_order_data.raw_material_id, tbl_rm_master.rm_name, SUM(tbl_inward_order_data.inward_quantity) as total_qty,tbl_inward_order_data.created_on,tbl_inward_order_data.plant_id');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_inward_order_data.raw_material_id', 'left');
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->db->where('tbl_inward_order_data.created_on >=', $start.' 00:00:00');
    	$this->db->where('tbl_inward_order_data.created_on <=', $end.' 23:59:59');
		$this->db->where('tbl_inward_order_data.raw_material_id IS NOT NULL');
		$this->db->group_by('tbl_inward_order_data.raw_material_id, tbl_rm_master.rm_name');
		$this->db->order_by('total_qty', 'DESC');
		if (!empty($plant_id)) {
			$this->db->where('tbl_inward_order_data.plant_id', $plant_id);
		}
		if (!empty($raw_material_id)) {
			$this->db->where('tbl_inward_order_data.raw_material_id', $raw_material_id);
			$this->db->limit(1);
		}

		$this->db->group_by('tbl_inward_order_data.raw_material_id, tbl_rm_master.rm_name');
		$this->db->order_by('total_qty', 'DESC');

		$all_items = $this->db->get()->result_array();

		// Split into top and bottom 10
		$top_items = array_slice($all_items, 0, 10);
    	$bottom_items = array_reverse(array_slice($all_items, -10, 10));

		return [
			'top_items' => $top_items,
			'bottom_items' => $bottom_items
		];
	}

	public function get_fast_and_over_moving_list()
	{
		$plant_id = $this->input->post('plant_id');
		$date_range = $this->input->post('date') ?? '';
		$raw_material_id = $this->input->post('raw_material_id');
		$start = '';
		$end = '';

		if (!empty($date_range)) {
			if (strpos($date_range, ' to ') !== false) {
				$dates = explode(' to ', $date_range);
				if (count($dates) == 2) {
					$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[0]))));
					$end   = date('Y-m-d', strtotime(str_replace('/', '-', trim($dates[1]))));
				}else if (count($dates) == 1) {
					$single_date = trim($dates[0]);
					$start = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
					$end = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				}

			} else {
				$start = date('Y-m-d', strtotime(str_replace('/', '-', trim($date_range))));
				$end   = $start;
			}
		}
		
		if (empty($start) || empty($end)) {
			$end   = date('Y-m-d');
			$start = date('Y-m-d', strtotime('-30 days'));
		}
		$this->db->select('tbl_rm_master.rm_name, SUM(tbl_inward_order_data.inward_quantity) as total_qty');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_inward_order_data.raw_material_id', 'left');
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->db->where('tbl_inward_order_data.created_on >=', $start.' 00:00:00');
		$this->db->where('tbl_inward_order_data.created_on <=', $end.' 23:59:59');
		if (!empty($plant_id)) {
			$this->db->where('tbl_inward_order_data.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_inward_order_data.plant_id');
		if (!empty($raw_material_id)) {
			$this->db->where('tbl_inward_order_data.raw_material_id', $raw_material_id);
		}
		$this->db->group_by('tbl_rm_master.id');
		$this->db->order_by('total_qty', 'DESC');
		$this->db->limit(10);

		// âœ… then execute normally
		$fast_moving = $this->db->get()->result_array();

		// print_r($fast_moving);
		// âœ… OVER-PURCHASED (Purchased but NOT used)
		$this->db->select('tbl_rm_master.id as rm_id, tbl_rm_master.rm_name, SUM(tbl_inward_order_data.inward_quantity) as total_purchased');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_inward_order_data.raw_material_id', 'left');
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->db->where('tbl_inward_order_data.created_on >=', $start.' 00:00:00');
		$this->db->where('tbl_inward_order_data.created_on <=', $end.' 23:59:59');
		if (!empty($plant_id)) {
			$this->db->where('tbl_inward_order_data.plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('tbl_inward_order_data.plant_id');
		if (!empty($raw_material_id)) {
			$this->db->where('tbl_inward_order_data.raw_material_id', $raw_material_id);
		}
		$this->db->group_by('tbl_rm_master.id');
		$purchased_data = $this->db->get_compiled_select();

		// Get used qty from production details
		$this->db->select('raw_material_id, SUM(total_qty) as total_used');
		$this->db->from('tbl_raw_material_production_details');
		$this->db->where('is_deleted', '0');
		$this->db->where('created_on >=', $start.' 00:00:00');
		$this->db->where('created_on <=', $end.' 23:59:59');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->_apply_assigned_plants_scope('plant_id');
		if (!empty($raw_material_id)) {
			$this->db->where('raw_material_id', $raw_material_id);
		}
		$this->db->group_by('raw_material_id');
		$used_data = $this->db->get_compiled_select();
		// Join both to find over-purchased items
		$query = $this->db->query("
        SELECT p.rm_name, p.total_purchased - IFNULL(u.total_used, 0) AS over_purchased_qty
        FROM ($purchased_data) AS p
        LEFT JOIN ($used_data) AS u ON p.rm_id = u.raw_material_id
        WHERE (p.total_purchased - IFNULL(u.total_used, 0)) > 0
        ORDER BY over_purchased_qty DESC
        LIMIT 10
    ");
		$over_purchased = $query->result_array();
		// Prepare final JSON for DataTable
		$data = [];
		for ($i = 0; $i < max(count($fast_moving), count($over_purchased)); $i++) {
			$data[] = [
				'fast_rm' => $fast_moving[$i]['rm_name'] ?? '',
				'fast_qty' => $fast_moving[$i]['total_qty'] ?? '',
				'over_rm' => $over_purchased[$i]['rm_name'] ?? '',
				'over_qty' => $over_purchased[$i]['over_purchased_qty'] ?? ''
			];
		}
		echo json_encode(['data' => $data]);
	}

	public function get_monthly_purchase_data()
	{
		$raw_material_id = $this->input->post('raw_material_id');
		$plant_id = $this->input->post('plant_id');
		$date = $this->input->post('date'); 

		$this->db->select('
        MONTH(tbl_inward_order_data.created_on) as month,
        SUM(tbl_inward_order_data.inward_quantity) as total_qty
    ');
		$this->db->from('tbl_inward_order_data');
		$this->db->where('tbl_inward_order_data.is_deleted', '0');


		$this->db->where('tbl_inward_order_data.raw_material_id', $raw_material_id);


		if (!empty($plant_id)) {
			$this->db->where('tbl_inward_order_data.plant_id', $plant_id);
		}

		if (!empty($date)) {

			if (strpos($date, ' to ') !== false) {

				// Case: 23-01-2026 to 30-01-2026
				$dates = explode(' to ', $date);

				if (count($dates) == 2) {
					$start_date = date('Y-m-d', strtotime(trim($dates[0])));
					$end_date = date('Y-m-d', strtotime(trim($dates[1])));

					$this->db->where('DATE(tbl_inward_order_data.created_on) >=', $start_date);
					$this->db->where('DATE(tbl_inward_order_data.created_on) <=', $end_date);
				}else if (count($dates) == 1) {
					$single_date = trim($dates[0]);
					$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
					
					if ($single) {
						$this->db->where('DATE(tbl_inward_order_data.created_on)', $single);
					}
				}
			} else {
				$single_date = date('Y-m-d', strtotime($date));
				$this->db->where('DATE(tbl_inward_order_data.created_on)', $single_date);
			}
		} else {
			$this->db->where('YEAR(tbl_inward_order_data.created_on)', date('Y'));
		}
		$this->db->group_by('MONTH(tbl_inward_order_data.created_on)');
		$this->db->order_by('MONTH(tbl_inward_order_data.created_on)', 'ASC');
		$query = $this->db->get();

		$data = array_fill(1, 12, 0);

		foreach ($query->result_array() as $row) {
			$data[(int) $row['month']] = (float) $row['total_qty'];
		}

		echo json_encode(array_values($data));
	}
	public function get_negative_stock_alert()
	{
		$plant_id = $this->input->post('plant_id');
		$raw_material_id = $this->input->post('raw_material_id');
		$date = $this->input->post('date'); 

		$this->db->select('tbl_production_schedules.*, tbl_plant_master.plant_name');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->where('DATE(tbl_production_schedules.date) >=', '2025-11-01');
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		} else if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_production_schedules.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if (!empty($date)) {
			if (strpos($date, ' to ') !== false) {
				$dates = explode(' to ', $date);
				if (count($dates) == 2) {
					$start_date = date('Y-m-d', strtotime(trim($dates[0])));
					$end_date = date('Y-m-d', strtotime(trim($dates[1])));

					$this->db->where('DATE(tbl_production_schedules.date) >=', $start_date);
					$this->db->where('DATE(tbl_production_schedules.date) <=', $end_date);
				}else if (count($dates) == 1) {
					$single_date = trim($dates[0]);
					$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
					
					if ($single) {
						$this->db->where('DATE(tbl_production_schedules.date)', $single);
					}
				}
			} else {
				$single_date = date('Y-m-d', strtotime($date));
				$this->db->where('DATE(tbl_production_schedules.date)', $single_date);
			}
		}

		if (!empty($raw_material_id)) {
			$this->db->where("FIND_IN_SET($raw_material_id, tbl_production_schedules.raw_materials) >", 0);
		}
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();

		$merged_output = [];

		if (!empty($result)) {
			foreach ($result as $row) {
				$raw_material_ids = explode(',', $row->raw_materials);
				$production_qty = !empty($row->qty) ? $row->qty : 0;
				$bom = $this->get_artical_bom($row->article_id);

				if (!empty($bom)) {
					$batch = !empty($bom->batch) ? $bom->batch : 0;
					$total_batches = ($batch > 0) ? ($production_qty / $batch) : 0;
					$weight = !empty($bom->weight) ? $bom->weight : 0;
					$total_weight = round($weight * $total_batches, 2);
					$raw_material_one = !empty($bom->raw_material_one) ? $bom->raw_material_one : 0;
					$required_qty = ceil(($raw_material_one / 100) * $total_weight);

					foreach ($raw_material_ids as $rm_id) {
						$rm_data = $this->get_raw_material_by_id($rm_id);
						if (empty($rm_data))
							continue;

						// Get stock
						$this->db->select('SUM(total_quantity) as total_quantity')
							->from('tbl_raw_material_stock_report')
							->where('raw_material_id', $rm_id)
							->where('is_deleted', '0');

						if (!empty($plant_id)) {
							$this->db->where('plant_id', $plant_id);
						} else if ($this->session->userdata('is_admin') != '1') {
							$this->_apply_assigned_plants_scope('plant_id');
						}

						$stock = $this->db->get()->row();
						$available = !empty($stock->total_quantity) ? (float) $stock->total_quantity : 0;

						// if ($required_qty < $available )
						// 	continue;

						if (!isset($merged_output[$rm_id])) {
							$merged_output[$rm_id] = [
								'rm_name' => $rm_data->rm_name,
								'available' => $available,
								'required' => $required_qty,
							];
						} else {
							$merged_output[$rm_id]['required'] += $required_qty;
							$merged_output[$rm_id]['available'] = $available;
						}
					}
				}
			}
		}

		$final_output = [];
		foreach ($merged_output as $rm) {

			$available = round((float)$rm['available'], 3);
			$required  = round((float)$rm['required'], 3);
			$delta     = round($available - $required, 3);

			$final_output[] = [
				'rm_name'   => $rm['rm_name'],
				'available' => $available,
				'required'  => $required,
				'delta'     => $delta,
			];
		}

		echo json_encode(['data' => array_values($final_output)]);
	}
	public function get_negative_stock_alert_old()
	{
		$plant_id = $this->input->post('plant_id');
		$raw_material_id = $this->input->post('raw_material_id');
		$date = $this->input->post('date'); 

		$this->db->select('tbl_production_schedules.*, tbl_plant_master.plant_name');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->where('DATE(tbl_production_schedules.date) <', '2025-11-01');
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		} else if ($this->session->userdata('is_admin') != '1') {
			$this->db->where('tbl_production_schedules.plant_id', $this->session->userdata('assign_plant_id'));
		}
		if (!empty($date)) {
			if (strpos($date, ' to ') !== false) {
				$dates = explode(' to ', $date);
				if (count($dates) == 2) {
					$start_date = date('Y-m-d', strtotime(trim($dates[0])));
					$end_date = date('Y-m-d', strtotime(trim($dates[1])));

					$this->db->where('DATE(tbl_production_schedules.date) >=', $start_date);
					$this->db->where('DATE(tbl_production_schedules.date) <=', $end_date);
				}else if (count($dates) == 1) {
					$single_date = trim($dates[0]);
					$single = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
					
					if ($single) {
						$this->db->where('DATE(tbl_production_schedules.date)', $single);
					}
				}
			} else {
				$single_date = date('Y-m-d', strtotime($date));
				$this->db->where('DATE(tbl_production_schedules.date)', $single_date);
			}
		}

		if (!empty($raw_material_id)) {
			$this->db->where("FIND_IN_SET($raw_material_id, tbl_production_schedules.raw_materials) >", 0);
		}
		$query = $this->db->get('tbl_production_schedules');
		$result = $query->result();

		$merged_output = [];

		if (!empty($result)) {
			foreach ($result as $row) {
				$raw_material_ids = explode(',', $row->raw_materials);
				$production_qty = !empty($row->qty) ? $row->qty : 0;
				$bom = $this->get_artical_bom($row->article_id);

				if (!empty($bom)) {
					$batch = !empty($bom->batch) ? $bom->batch : 0;
					$total_batches = ($batch > 0) ? ($production_qty / $batch) : 0;
					$weight = !empty($bom->weight) ? $bom->weight : 0;
					$total_weight = round($weight * $total_batches, 2);
					$raw_material_one = !empty($bom->raw_material_one) ? $bom->raw_material_one : 0;
					$required_qty = ceil(($raw_material_one / 100) * $total_weight);

					foreach ($raw_material_ids as $rm_id) {
						$rm_data = $this->get_raw_material_by_id($rm_id);
						if (empty($rm_data))
							continue;

						// Get stock
						$this->db->select('SUM(total_quantity) as total_quantity')
							->from('tbl_raw_material_stock_report')
							->where('raw_material_id', $rm_id)
							->where('is_deleted', '0');

						if (!empty($plant_id)) {
							$this->db->where('plant_id', $plant_id);
						} else if ($this->session->userdata('is_admin') != '1') {
							$this->_apply_assigned_plants_scope('plant_id');
						}

						$stock = $this->db->get()->row();
						$available = !empty($stock->total_quantity) ? (float) $stock->total_quantity : 0;

						// if ($required_qty < $available )
						// 	continue;

						if (!isset($merged_output[$rm_id])) {
							$merged_output[$rm_id] = [
								'rm_name' => $rm_data->rm_name,
								'available' => $available,
								'required' => $required_qty,
							];
						} else {
							$merged_output[$rm_id]['required'] += $required_qty;
							$merged_output[$rm_id]['available'] = $available;
						}
					}
				}
			}
		}

		$final_output = [];
		foreach ($merged_output as $rm) {

			$available = round((float)$rm['available'], 3);
			$required  = round((float)$rm['required'], 3);
			$delta     = round($available - $required, 3);

			$final_output[] = [
				'rm_name'   => $rm['rm_name'],
				'available' => $available,
				'required'  => $required,
				'delta'     => $delta,
			];
		}

		echo json_encode(['data' => array_values($final_output)]);
	}

	public function get_vendors_by_raw_material()
	{
		$raw_material_id = $this->input->post('raw_material_id');
		$plant_id = $this->input->post('plant_id');
		$date_range = $this->input->post('date') ?? '';
		$party_id = $this->input->post('party_id');
		if ($date_range) {
			$dates = explode(' to ', str_replace(' - ', ' to ', $date_range));
			if (count($dates) == 2) {
				$start_date = trim($dates[0]);
				$end_date = trim($dates[1]);

				$start = date('Y-m-d', strtotime(str_replace('/', '-', $start_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $end_date)));

			}else if (count($dates) == 1) {
				$single_date = trim($dates[0]);
				$start = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
				$end = date('Y-m-d', strtotime(str_replace('/', '-', $single_date)));
			}
		}
		$this->db->select('
			tbl_inward_order_data.id,
			tbl_inward_order_data.raw_material_id,
			tbl_inward_order_data.inward_quantity AS volume,
			tbl_inward_order_data.rate AS value,
			tbl_inward.inward_no,
			tbl_inward.inward_date,
			tbl_inward.party_id,
			tbl_customers.party_name,
			tbl_customers.address,
			tbl_customers.mobile
		');
		$this->db->from('tbl_inward_order_data');
		$this->db->join('tbl_inward', 'tbl_inward.inward_no = tbl_inward_order_data.inward_no', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_inward.party_id', 'left');
		$this->db->where('tbl_inward_order_data.is_deleted', '0');
		$this->db->where('tbl_inward.is_deleted', '0');
		if (!empty($start) && !empty($end)) {
			$this->db->where('DATE(tbl_inward_order_data.created_on) >=', $start);
			$this->db->where('DATE(tbl_inward_order_data.created_on) <=', $end);
		} else if (!empty($start)) {
			$this->db->where('DATE(tbl_inward_order_data.created_on)', $start);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_inward.plant_id', $plant_id);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_inward.party_id', $party_id);
		}
		$this->db->where('tbl_inward_order_data.raw_material_id', $raw_material_id);
		$this->db->order_by('tbl_inward.inward_date','DESC');
		$query = $this->db->get();
		$result = $query->result();

		$vendors = [];

		foreach ($result as $row) {
			$key = $row->id;

			if (!isset($vendors[$key])) {
				$vendors[$key] = [
					'party_name' => $row->party_name,
					'party_address' => $row->address,
					'mobile_number' => $row->mobile,
					'total_value' => 0,
					'total_volume' => 0,
					'inward_date' => date('d-m-Y', strtotime($row->inward_date))
				];
			}

			// Add totals
			$vendors[$key]['total_volume'] += $row->volume;
			$vendors[$key]['total_value'] += $row->value;
		}

		echo json_encode([
			"data" => array_values($vendors)
		]);
	}

	/////////////////////////////////////////////Store Dashboard//////////////////////////////////////////////////////

	public function get_store_dashboard_tables_data()
	{
		$plant_id = $this->input->post('plant_id');
		$date = $this->input->post('date') ? date('Y-m-d', strtotime($this->input->post('date'))) : date('Y-m-d');

		// 1. Get scheduled production for the day
		$this->db->select('ps.machine_id, mm.machine_name, ps.article_id, ps.raw_materials, ps.qty as scheduled_qty');
		$this->db->from('tbl_production_schedules ps');
		$this->db->join('tbl_machine_master mm', 'ps.machine_id = mm.id');
		$this->db->where('ps.is_deleted', '0');
		$this->db->where('ps.date', $date);
		if ($plant_id) {
			$this->db->where('ps.plant_id', $plant_id);
		}
		$schedules = $this->db->get()->result();
    
		$machine_requirements = [];

		foreach ($schedules as $schedule) {
			if (!isset($machine_requirements[$schedule->machine_id])) {
				$machine_requirements[$schedule->machine_id] = [
					'machine_name' => $schedule->machine_name,
					'rm' => 0,
					'mb' => 0,
					'other_rm' => 0,
				];
			}
			// Get BOM for the article
			$this->db->select('pb.id as bom_id');
			$this->db->from('tbl_production_bom pb');
			$this->db->where('pb.article_id', $schedule->article_id);
			$this->db->where('pb.is_deleted', '0');
			$bom = $this->db->get()->row();
			if ($bom) {
				$this->db->select('pbom.quantity as qty, pbom.sub_category_id, tbl_rm_master.rm_name');
				$this->db->from('tbl_particulars_bom pbom');
				$this->db->join('tbl_rm_master', 'pbom.sub_category_id = tbl_rm_master.id');
				$this->db->where('pbom.bom_id', $bom->bom_id);
				$bom_items = $this->db->get()->result();
				foreach ($bom_items as $item) {
					$required_qty = $item->qty * $schedule->scheduled_qty;
					if ($item->particulars_type == 'Raw Material') {
						$machine_requirements[$schedule->machine_id]['rm'] += $required_qty;
					} elseif ($item->particulars_type == 'Master Batch') {
						$machine_requirements[$schedule->machine_id]['mb'] += $required_qty;
					} else {
						$machine_requirements[$schedule->machine_id]['other_rm'] += $required_qty;
					}
				}
			}
		}

		echo json_encode([
			'machine_wise' => array_values($machine_requirements),
		]);
	}

	public function get_all_production_schedules_for_store_dashboard()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		$selected_date = isset($input['selected_date']) && $input['selected_date'] != '' ? date('Y-m-d', strtotime($input['selected_date'])) : date('Y-m-d');
		$plant_id = isset($input['plant_id']) && $input['plant_id'] != '' ? $input['plant_id'] : '';
		$machine_id = isset($input['machine_id']) && $input['machine_id'] != '' ? $input['machine_id'] : '';
		$this->db->select('
			tbl_production_schedules.*, 
			tbl_mould_parts.article_name, 
			tbl_group_of_article.group_of_article, 
			tbl_plant_master.plant_name, 
			tbl_machine_master.machine_name,
			GROUP_CONCAT(DISTINCT tbl_mb_master.name ORDER BY tbl_mb_master.id ASC SEPARATOR ", ") AS color_names,
			GROUP_CONCAT(DISTINCT tbl_rm_master.rm_name ORDER BY tbl_rm_master.id ASC SEPARATOR ", ") AS raw_material_names
		');
		$this->db->from('tbl_production_schedules');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id', 'left');
		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id', 'left');
		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');
		$this->db->join('tbl_mb_master', 'FIND_IN_SET(tbl_mb_master.id, tbl_production_schedules.color_id)', 'left');
		$this->db->join('tbl_rm_master', 'FIND_IN_SET(tbl_rm_master.id, tbl_production_schedules.raw_materials)', 'left');

		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->where('DATE(tbl_production_schedules.date)', $selected_date);
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		}
		$this->db->group_by('tbl_production_schedules.id');

		$result = $this->db->get()->result();
		if ($result) {
			foreach ($result as &$entry) {
				$this->db->where('is_deleted', '0');
				$this->db->where('DATE(tbl_article_production_details.production_date)', $selected_date);
				$this->db->where('tbl_article_production_details.article_id', $entry->article_id);
				$this->db->where('tbl_article_production_details.machine_id', $entry->machine_id);
				if (!empty($plant_id)) {
					$this->db->where('tbl_article_production_details.plant_id', $plant_id);
				}
				$production = $this->db->get('tbl_article_production_details')->row();
				$total_achieved_qty = 0;
				if (!empty($production)) {
					$hourly_fields = [
						'08:00-09:00' => $production->qty_eight_nine,
						'09:00-10:00' => $production->qty_nine_ten,
						'10:00-11:00' => $production->qty_ten_eleven,
						'11:00-12:00' => $production->qty_eleven_twelve,
						'12:00-13:00' => $production->qty_twelve_thirteen,
						'13:00-14:00' => $production->qty_thirteen_fourteen,
						'14:00-15:00' => $production->qty_fourteen_fifteen,
						'15:00-16:00' => $production->qty_fifteen_sixteen,
						'16:00-17:00' => $production->qty_sixteen_seventeen,
						'17:00-18:00' => $production->qty_seventeen_eighteen,
						'18:00-19:00' => $production->qty_eighteen_nineteen,
						'19:00-20:00' => $production->qty_nineteen_twenty,
						'20:00-21:00' => $production->qty_twenty_twentyone,
						'21:00-22:00' => $production->qty_twentyone_twentytwo,
						'22:00-23:00' => $production->qty_twentytwo_twentythree,
						'23:00-24:00' => $production->qty_twentythree_zero,
						'00:00-01:00' => $production->qty_zero_one,
						'01:00-02:00' => $production->qty_one_two,
						'02:00-03:00' => $production->qty_two_three,
						'03:00-04:00' => $production->qty_three_four,
						'04:00-05:00' => $production->qty_four_five,
						'05:00-06:00' => $production->qty_five_six,
						'06:00-07:00' => $production->qty_six_seven,
						'07:00-08:00' => $production->qty_seven_eight,
					];

					$start_time = $entry->production_schedule_start_time;
					$end_time = $entry->production_schedule_end_time;

					$start_int = intval(str_replace(':', '', $start_time));
					$end_int = intval(str_replace(':', '', $end_time));

					if ($end_int <= $start_int) {
						$end_int += 2400;
					}
					foreach ($hourly_fields as $range => $qty) {
						list($from, $to) = explode('-', $range);

						$from_int = intval(str_replace(':', '', $from));
						$to_int = intval(str_replace(':', '', $to));

						if ($to_int === 0) {
							$to_int = 2400;
						}

						if ($to_int <= $start_int) {
							$from_int += 2400;
							$to_int += 2400;
						}

						if ($from_int >= $start_int && $to_int <= $end_int) {
							$total_achieved_qty += (float) $qty;
						}
					}
				}
				$entry->total_achieve_qty = $total_achieved_qty;

				/* total_achieve_qty is already the hourly-slot sum for this shift window only */
			}
		}
		if (!empty($result)) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Schedules fetched successfully.',
				'data' => $result
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No schedules found for this date.',
				'data' => []
			]);
		}
	}

	public function get_production_report_sheet_overview($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select([
			'pr.id',
			'pr.production_date',
			'pr.machine_id',
			'pr.remark',
			'mm.machine_name',
			'GROUP_CONCAT(DISTINCT ga.group_of_article ORDER BY ga.id SEPARATOR ", ") AS article_group',
			'GROUP_CONCAT(DISTINCT mp.article_name ORDER BY mp.id SEPARATOR ", ") AS article_names',
			'GROUP_CONCAT(DISTINCT rm.rm_name ORDER BY rm.id SEPARATOR ", ") AS raw_material_names',
			'GROUP_CONCAT(DISTINCT mb.name ORDER BY mb.id SEPARATOR ", ") AS master_batch_names',
			'GROUP_CONCAT(DISTINCT rj.rm_name ORDER BY rj.id SEPARATOR ", ") AS rejection_names',
			'(SELECT COUNT(*) FROM tbl_production_images i WHERE i.production_id = pr.id) AS image_count'
		]);

		$this->db->from('tbl_production_report pr');
		$this->db->join('tbl_machine_master mm', 'pr.machine_id = mm.id', 'left');
		$this->db->join('tbl_group_of_article ga', 'FIND_IN_SET(ga.id, pr.article_group_id)', 'left');
		$this->db->join('tbl_mould_parts mp', 'FIND_IN_SET(mp.id, pr.article_id)', 'left');
		$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, pr.raw_material_id)', 'left');
		$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, pr.master_batch_id)', 'left');
		$this->db->join('tbl_rm_rejection rj', 'FIND_IN_SET(rj.id, pr.rejection_id)', 'left');
		$this->db->where('pr.is_deleted', '0');
		$this->db->group_by('pr.id');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}

		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}

		if (!empty($machine_id)) {
			$this->db->where('pr.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where("FIND_IN_SET(" . intval($article_id) . ", pr.article_id) > 0", null, false);
		}

		$this->db->order_by('pr.production_date', 'DESC');
		$this->db->order_by('pr.id', 'DESC');

		return $this->db->get()->result();
	}

	public function get_production_report_available_date_range($machine_id = '', $article_id = '')
	{
		$this->db->select('MIN(DATE(pr.production_date)) AS min_date, MAX(DATE(pr.production_date)) AS max_date');
		$this->db->from('tbl_production_report pr');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($machine_id)) {
			$this->db->where('pr.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where(
				"EXISTS (SELECT 1 FROM tbl_article_production_details apd WHERE apd.production_id = pr.id AND apd.article_id = " . $this->db->escape($article_id) . " AND apd.is_deleted = '0')",
				null,
				false
			);
		}

		return $this->db->get()->row();
	}

	public function get_production_report_plan_vs_actual($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$where_pr = "pr.is_deleted = '0'";
		$params_pr = [];
		if (!empty($from_date)) {
			$where_pr .= " AND DATE(pr.production_date) >= ?";
			$params_pr[] = $from_date;
		}
		if (!empty($to_date)) {
			$where_pr .= " AND DATE(pr.production_date) <= ?";
			$params_pr[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where_pr .= " AND pr.machine_id = ?";
			$params_pr[] = $machine_id;
		}
		if (!empty($article_id)) {
			// Filter by actual production entries for the selected article
			$where_pr .= " AND EXISTS (SELECT 1 FROM tbl_article_production_details apd WHERE apd.production_id = pr.id AND apd.article_id = ? AND apd.is_deleted = '0')";
			$params_pr[] = $article_id;
		}

		$where_sch = "ps.is_deleted = '0'";
		$params_sch = [];
		if (!empty($from_date)) {
			$where_sch .= " AND DATE(ps.date) >= ?";
			$params_sch[] = $from_date;
		}
		if (!empty($to_date)) {
			$where_sch .= " AND DATE(ps.date) <= ?";
			$params_sch[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where_sch .= " AND ps.machine_id = ?";
			$params_sch[] = $machine_id;
		}
		if (!empty($article_id)) {
			$where_sch .= " AND ps.article_id = ?";
			$params_sch[] = $article_id;
		}

		// Actual (good) qty is taken from hourly production entries
		$where_act = "apd.is_deleted = '0'";
		$params_act = [];
		if (!empty($from_date)) {
			$where_act .= " AND DATE(apd.production_date) >= ?";
			$params_act[] = $from_date;
		}
		if (!empty($to_date)) {
			$where_act .= " AND DATE(apd.production_date) <= ?";
			$params_act[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where_act .= " AND apd.machine_id = ?";
			$params_act[] = $machine_id;
		}
		if (!empty($article_id)) {
			$where_act .= " AND apd.article_id = ?";
			$params_act[] = $article_id;
		}

		$where_rej = "rpd.is_deleted = '0' AND pr.is_deleted = '0'";
		$params_rej = [];
		if (!empty($from_date)) {
			$where_rej .= " AND DATE(pr.production_date) >= ?";
			$params_rej[] = $from_date;
		}
		if (!empty($to_date)) {
			$where_rej .= " AND DATE(pr.production_date) <= ?";
			$params_rej[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where_rej .= " AND rpd.machine_id = ?";
			$params_rej[] = $machine_id;
		}
		if (!empty($article_id)) {
			$where_rej .= " AND EXISTS (SELECT 1 FROM tbl_article_production_details apd WHERE apd.production_id = pr.id AND apd.article_id = ? AND apd.is_deleted = '0')";
			$params_rej[] = $article_id;
		}

		// Downtime is based on red-colored hours in Production Report (qty < 30)
		$where_down = "apd.is_deleted = '0'";
		$params_down = [];
		if (!empty($from_date)) {
			$where_down .= " AND DATE(apd.production_date) >= ?";
			$params_down[] = $from_date;
		}
		if (!empty($to_date)) {
			$where_down .= " AND DATE(apd.production_date) <= ?";
			$params_down[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where_down .= " AND apd.machine_id = ?";
			$params_down[] = $machine_id;
		}
		if (!empty($article_id)) {
			$where_down .= " AND apd.article_id = ?";
			$params_down[] = $article_id;
		}

		$has_bom_std_cycle = $this->db->field_exists('std_cycle_time', 'tbl_production_bom');
		$actual_bom_join_sql = '';
		$actual_bom_cols_sql = "NULL AS std_cycle_time, NULL AS ideal_seconds";
		if ($has_bom_std_cycle) {
			$actual_bom_join_sql = "
			LEFT JOIN (
				SELECT pb1.article_id, pb1.std_cycle_time
				FROM tbl_production_bom pb1
				JOIN (
					SELECT article_id, MAX(id) AS max_id
					FROM tbl_production_bom
					WHERE is_deleted = '0'
					GROUP BY article_id
				) pb2 ON pb2.max_id = pb1.id
			) pb ON pb.article_id = apd.article_id";
			$actual_bom_cols_sql = "
				(CASE WHEN COUNT(DISTINCT apd.article_id) = 1 THEN MAX(pb.std_cycle_time) ELSE NULL END) AS std_cycle_time,
				SUM(
					COALESCE(CAST(REPLACE(NULLIF(TRIM(apd.approved_qty), ''), ',', '') AS DECIMAL(18,3)), 0)
					* COALESCE(pb.std_cycle_time, 0)
				) AS ideal_seconds";
		}

		$sql = "
			SELECT
				b.production_date,
				b.machine_id,
				mm.machine_name,
				b.operator_name,
				b.day_shift_operators,
				b.night_shift_operators,
				p.planned_qty,
				COALESCE(NULLIF(p.scheduled_minutes, 0), a.active_minutes, 0) AS scheduled_minutes,
				p.shift_start,
				p.shift_end,
				p.shift_timing,
				p.shift_detail,
				a.good_qty,
				r.rejection_qty,
				a.remarks,
				a.std_cycle_time,
				a.ideal_seconds,
				d.downtime_minutes,
				d.downtime_reasons
			FROM (
				SELECT
					DATE(pr.production_date) AS production_date,
					pr.machine_id,
					GROUP_CONCAT(DISTINCT NULLIF(TRIM(pr.operator_name), '') SEPARATOR ', ') AS operator_name,
					GROUP_CONCAT(DISTINCT NULLIF(TRIM(pr.day_shift_operators), '') SEPARATOR ', ') AS day_shift_operators,
					GROUP_CONCAT(DISTINCT NULLIF(TRIM(pr.night_shift_operators), '') SEPARATOR ', ') AS night_shift_operators
				FROM tbl_production_report pr
				WHERE {$where_pr}
				GROUP BY DATE(pr.production_date), pr.machine_id
			) b
			LEFT JOIN tbl_machine_master mm ON mm.id = b.machine_id
			LEFT JOIN (
				SELECT
					DATE(ps.date) AS production_date,
					ps.machine_id,
					(CASE WHEN COUNT(DISTINCT ps.article_id) = 1 THEN MIN(ps.article_id) ELSE NULL END) AS article_id,
					SUM(COALESCE(ps.qty, 0)) AS planned_qty,
					SUM(
						GREATEST(
							TIMESTAMPDIFF(
								MINUTE,
								CONCAT(ps.production_schedule_start_date, ' ', ps.production_schedule_start_time),
								CONCAT(ps.production_schedule_end_date, ' ', ps.production_schedule_end_time)
							),
							0
						)
					) AS scheduled_minutes,
					MIN(ps.production_schedule_start_time) AS shift_start,
					MAX(ps.production_schedule_end_time) AS shift_end,
					GROUP_CONCAT(
						DISTINCT CONCAT(
							NULLIF(TRIM(ps.production_schedule_start_time), ''),
							' - ',
							NULLIF(TRIM(ps.production_schedule_end_time), '')
						)
						ORDER BY ps.production_schedule_start_date, ps.production_schedule_start_time
						SEPARATOR ', '
					) AS shift_timing,
					GROUP_CONCAT(
						CONCAT(
							NULLIF(TRIM(ps.production_schedule_start_time), ''),
							' - ',
							NULLIF(TRIM(ps.production_schedule_end_time), ''),
							' (',
							COALESCE(ps.qty, 0),
							')'
						)
						ORDER BY ps.production_schedule_start_date, ps.production_schedule_start_time
						SEPARATOR ', '
					) AS shift_detail
				FROM tbl_production_schedules ps
				WHERE {$where_sch}
				GROUP BY DATE(ps.date), ps.machine_id
			) p ON p.production_date = b.production_date AND p.machine_id = b.machine_id
			LEFT JOIN (
				SELECT
					DATE(apd.production_date) AS production_date,
					apd.machine_id,
					SUM(COALESCE(CAST(REPLACE(NULLIF(TRIM(apd.approved_qty), ''), ',', '') AS DECIMAL(18,3)), 0)) AS good_qty,
					GROUP_CONCAT(DISTINCT NULLIF(TRIM(apd.remark), '') SEPARATOR ', ') AS remarks,
					60 * (
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_eight_nine), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_nine_ten), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_ten_eleven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_eleven_twelve), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_twelve_thirteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_thirteen_fourteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_fourteen_fifteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_fifteen_sixteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_sixteen_seventeen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_seventeen_eighteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_eighteen_nineteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_nineteen_twenty), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_twenty_twentyone), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentyone_twentytwo), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentytwo_twentythree), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentythree_zero), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_zero_one), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_one_two), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_two_three), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_three_four), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_four_five), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_five_six), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_six_seven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN SUM(CASE WHEN NULLIF(TRIM(apd.qty_seven_eight), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END)
					) AS active_minutes,
					{$actual_bom_cols_sql}
				FROM tbl_article_production_details apd
				{$actual_bom_join_sql}
				WHERE {$where_act}
				GROUP BY DATE(apd.production_date), apd.machine_id
			) a ON a.production_date = b.production_date AND a.machine_id = b.machine_id
			LEFT JOIN (
				SELECT
					DATE(pr.production_date) AS production_date,
					rpd.machine_id,
					SUM(COALESCE(rpd.pc, 0)) AS rejection_qty
				FROM tbl_rejection_article_list_production_details rpd
				JOIN tbl_production_report pr ON pr.id = rpd.production_id
				WHERE {$where_rej}
				GROUP BY DATE(pr.production_date), rpd.machine_id
			) r ON r.production_date = b.production_date AND r.machine_id = b.machine_id
			LEFT JOIN (
				SELECT
					DATE(apd.production_date) AS production_date,
					apd.machine_id,
					60 * (
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eight_nine), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eight_nine), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_nine_ten), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_nine_ten), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_ten_eleven), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_ten_eleven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eleven_twelve), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eleven_twelve), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twelve_thirteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twelve_thirteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_thirteen_fourteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_thirteen_fourteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_fourteen_fifteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_fourteen_fifteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_fifteen_sixteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_fifteen_sixteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_sixteen_seventeen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_sixteen_seventeen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_seventeen_eighteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_seventeen_eighteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eighteen_nineteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eighteen_nineteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_nineteen_twenty), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_nineteen_twenty), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twenty_twentyone), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twenty_twentyone), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentyone_twentytwo), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentyone_twentytwo), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentytwo_twentythree), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentytwo_twentythree), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentythree_zero), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentythree_zero), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_zero_one), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_zero_one), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_one_two), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_one_two), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_two_three), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_two_three), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_three_four), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_three_four), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_four_five), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_four_five), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_five_six), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_five_six), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_six_seven), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_six_seven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END) +
						(CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_seven_eight), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_seven_eight), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END)
				) AS downtime_minutes,
				CONCAT_WS('||',
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eight_nine), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eight_nine), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('08-09: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_eight_nine_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_nine_ten), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_nine_ten), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('09-10: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_nine_ten_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_ten_eleven), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_ten_eleven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('10-11: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_ten_eleven_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eleven_twelve), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eleven_twelve), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('11-12: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_eleven_twelve_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twelve_thirteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twelve_thirteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('12-13: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_twelve_thirteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_thirteen_fourteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_thirteen_fourteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('13-14: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_thirteen_fourteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_fourteen_fifteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_fourteen_fifteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('14-15: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_fourteen_fifteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_fifteen_sixteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_fifteen_sixteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('15-16: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_fifteen_sixteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_sixteen_seventeen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_sixteen_seventeen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('16-17: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_sixteen_seventeen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_seventeen_eighteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_seventeen_eighteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('17-18: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_seventeen_eighteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_eighteen_nineteen), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_eighteen_nineteen), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('18-19: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_eighteen_nineteen_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_nineteen_twenty), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_nineteen_twenty), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('19-20: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_nineteen_twenty_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twenty_twentyone), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twenty_twentyone), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('20-21: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_twenty_twentyone_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentyone_twentytwo), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentyone_twentytwo), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('21-22: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_twentyone_twentytwo_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentytwo_twentythree), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentytwo_twentythree), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('22-23: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_twentytwo_twentythree_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_twentythree_zero), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_twentythree_zero), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('23-00: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_twentythree_zero_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_zero_one), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_zero_one), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('00-01: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_zero_one_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_one_two), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_one_two), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('01-02: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_one_two_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_two_three), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_two_three), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('02-03: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_two_three_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_three_four), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_three_four), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('03-04: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_three_four_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_four_five), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_four_five), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('04-05: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_four_five_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_five_six), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_five_six), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('05-06: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_five_six_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_six_seven), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_six_seven), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('06-07: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_six_seven_remark)), ''), '-')) ELSE NULL END,
					CASE WHEN MIN(CAST(REPLACE(NULLIF(TRIM(apd.qty_seven_eight), ''), ',', '') AS DECIMAL(10,2))) < 30 AND SUM(CASE WHEN NULLIF(TRIM(apd.qty_seven_eight), '') IS NOT NULL THEN 1 ELSE 0 END) > 0 THEN CONCAT('07-08: ', COALESCE(NULLIF(TRIM(MAX(apd.qty_seven_eight_remark)), ''), '-')) ELSE NULL END
				) AS downtime_reasons
			FROM tbl_article_production_details apd
			WHERE {$where_down}
			GROUP BY DATE(apd.production_date), apd.machine_id
		) d ON d.production_date = b.production_date AND d.machine_id = b.machine_id
				ORDER BY b.production_date DESC, mm.machine_name ASC
			";

		$params = array_merge($params_pr, $params_sch, $params_act, $params_rej, $params_down);
		$results = $this->db->query($sql, $params)->result();

		// Collect all operator IDs
		$op_ids = [];
		foreach ($results as $row) {
			if (!empty($row->day_shift_operators)) {
				$op_ids = array_merge($op_ids, explode(',', $row->day_shift_operators));
			}
			if (!empty($row->night_shift_operators)) {
				$op_ids = array_merge($op_ids, explode(',', $row->night_shift_operators));
			}
		}
		$op_ids = array_values(array_unique(array_filter($op_ids)));

		$op_map = [];
		if (!empty($op_ids)) {
			$this->db->select('id, first_name');
			$this->db->where_in('id', $op_ids);
			$users = $this->db->get('user_data')->result();
			foreach ($users as $u) {
				$op_map[$u->id] = $u->first_name;
			}
		}

		foreach ($results as $row) {
			$day_names = [];
			if (!empty($row->day_shift_operators)) {
				foreach (explode(',', $row->day_shift_operators) as $oid) {
					if (isset($op_map[$oid])) {
						$day_names[] = $op_map[$oid];
					}
				}
			}
			$row->day_shift_operator_names = implode(', ', $day_names);

			$night_names = [];
			if (!empty($row->night_shift_operators)) {
				foreach (explode(',', $row->night_shift_operators) as $oid) {
					if (isset($op_map[$oid])) {
						$night_names[] = $op_map[$oid];
					}
				}
			}
			$row->night_shift_operator_names = implode(', ', $night_names);
		}

		return $results;
	}

	public function get_production_report_downtime_analysis($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$where = "apd.is_deleted = '0'";
		$params = [];
		if (!empty($from_date)) {
			$where .= " AND DATE(apd.production_date) >= ?";
			$params[] = $from_date;
		}
		if (!empty($to_date)) {
			$where .= " AND DATE(apd.production_date) <= ?";
			$params[] = $to_date;
		}
		if (!empty($machine_id)) {
			$where .= " AND apd.machine_id = ?";
			$params[] = $machine_id;
		}
		if (!empty($article_id)) {
			$where .= " AND apd.article_id = ?";
			$params[] = $article_id;
		}

		// Each hour slot per article row is one bucket.
		// Blank slot (has_entry=0) OR qty < 30 counts as 60 min downtime.
		// Category derived from remark text; blank slots show "No Entry".
		$sql = "
			SELECT
				DATE_FORMAT(x.production_date, '%d-%m-%Y') AS production_date,
				mm.id AS machine_id,
				mm.machine_name,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'breakdown'      THEN 60 ELSE 0 END) AS breakdown_min,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'mould_change'   THEN 60 ELSE 0 END) AS mould_change_min,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'material'       THEN 60 ELSE 0 END) AS material_min,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'power'          THEN 60 ELSE 0 END) AS power_min,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'minor_stoppage' THEN 60 ELSE 0 END) AS minor_stoppage_min,
				SUM(CASE WHEN x.is_down = 1 AND x.category = 'quality'        THEN 60 ELSE 0 END) AS quality_min,
				SUM(CASE WHEN x.is_down = 1 THEN 60 ELSE 0 END) AS total_downtime_min,
				GROUP_CONCAT(
					CASE WHEN x.is_down = 1
						THEN CONCAT(
							CASE x.slot
								WHEN 'eight_nine'            THEN '08-09'
								WHEN 'nine_ten'              THEN '09-10'
								WHEN 'ten_eleven'            THEN '10-11'
								WHEN 'eleven_twelve'         THEN '11-12'
								WHEN 'twelve_thirteen'       THEN '12-13'
								WHEN 'thirteen_fourteen'     THEN '13-14'
								WHEN 'fourteen_fifteen'      THEN '14-15'
								WHEN 'fifteen_sixteen'       THEN '15-16'
								WHEN 'sixteen_seventeen'     THEN '16-17'
								WHEN 'seventeen_eighteen'    THEN '17-18'
								WHEN 'eighteen_nineteen'     THEN '18-19'
								WHEN 'nineteen_twenty'       THEN '19-20'
								WHEN 'twenty_twentyone'      THEN '20-21'
								WHEN 'twentyone_twentytwo'   THEN '21-22'
								WHEN 'twentytwo_twentythree' THEN '22-23'
								WHEN 'twentythree_zero'      THEN '23-00'
								WHEN 'zero_one'              THEN '00-01'
								WHEN 'one_two'               THEN '01-02'
								WHEN 'two_three'             THEN '02-03'
								WHEN 'three_four'            THEN '03-04'
								WHEN 'four_five'             THEN '04-05'
								WHEN 'five_six'              THEN '05-06'
								WHEN 'six_seven'             THEN '06-07'
								WHEN 'seven_eight'           THEN '07-08'
								ELSE x.slot
							END,
							': ',
							CASE
								WHEN x.has_entry = 0                          THEN 'No Entry'
								WHEN NULLIF(TRIM(x.remark_txt), '') IS NOT NULL THEN x.remark_txt
								ELSE 'No Entry'
							END
						)
						ELSE NULL
					END
					ORDER BY x.slot
					SEPARATOR '||'
				) AS reason,
				GROUP_CONCAT(DISTINCT NULLIF(x.remark_txt, '') SEPARATOR ' | ') AS reason_combined
			FROM (
				SELECT
					DATE(apd.production_date) AS production_date,
					apd.machine_id,
					s.slot,
					s.qty_num,
					s.has_entry,
					s.remark_txt,
					(CASE WHEN s.has_entry = 0 OR (s.qty_num IS NOT NULL AND s.qty_num < 30) THEN 1 ELSE 0 END) AS is_down,
					(CASE
						WHEN LOWER(s.remark_txt) LIKE '%mould%change%' OR LOWER(s.remark_txt) LIKE '%mold%change%'
							OR LOWER(s.remark_txt) LIKE '%mould setting%' OR LOWER(s.remark_txt) LIKE '%mold setting%'
						THEN 'mould_change'
						WHEN LOWER(s.remark_txt) LIKE '%material%' OR LOWER(s.remark_txt) LIKE '%raw material%'
							OR LOWER(s.remark_txt) LIKE '%material shortage%' OR LOWER(s.remark_txt) LIKE '%rm%' OR LOWER(s.remark_txt) LIKE '%r.m%'
						THEN 'material'
						WHEN LOWER(s.remark_txt) LIKE '%power%' OR LOWER(s.remark_txt) LIKE '%powar%'
							OR LOWER(s.remark_txt) LIKE '%electric%' OR LOWER(s.remark_txt) LIKE '%voltage%'
							OR LOWER(s.remark_txt) LIKE '%power cut%' OR LOWER(s.remark_txt) LIKE '%power off%'
						THEN 'power'
						WHEN LOWER(s.remark_txt) LIKE '%quality%' OR LOWER(s.remark_txt) LIKE '%reject%'
							OR LOWER(s.remark_txt) LIKE '%rework%' OR LOWER(s.remark_txt) LIKE '%defect%'
							OR LOWER(s.remark_txt) LIKE '%leak%' OR LOWER(s.remark_txt) LIKE '%burr%' OR LOWER(s.remark_txt) LIKE '%flash%'
						THEN 'quality'
						WHEN LOWER(s.remark_txt) LIKE '%breakdown%' OR LOWER(s.remark_txt) LIKE '%break down%'
							OR LOWER(s.remark_txt) LIKE '%machine stop%' OR LOWER(s.remark_txt) LIKE '%machine stopped%'
							OR LOWER(s.remark_txt) LIKE '%not started%' OR LOWER(s.remark_txt) LIKE '%problem%'
							OR LOWER(s.remark_txt) LIKE '%maintenance%' OR LOWER(s.remark_txt) LIKE '%hydraulic%'
							OR LOWER(s.remark_txt) LIKE '%compressor%' OR LOWER(s.remark_txt) LIKE '%motor%'
							OR LOWER(s.remark_txt) LIKE '%heater%' OR LOWER(s.remark_txt) LIKE '%heating%'
						THEN 'breakdown'
						ELSE 'minor_stoppage'
					END) AS category
				FROM (
					SELECT apd.id, apd.production_date, apd.machine_id, 'eight_nine' AS slot,
						CAST(REPLACE(NULLIF(TRIM(apd.qty_eight_nine), ''), ',', '') AS DECIMAL(10,2)) AS qty_num,
						(CASE WHEN NULLIF(TRIM(apd.qty_eight_nine), '') IS NOT NULL THEN 1 ELSE 0 END) AS has_entry,
						NULLIF(TRIM(apd.qty_eight_nine_remark), '') AS remark_txt
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'nine_ten',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_nine_ten), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_nine_ten), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_nine_ten_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'ten_eleven',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_ten_eleven), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_ten_eleven), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_ten_eleven_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'eleven_twelve',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_eleven_twelve), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_eleven_twelve), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_eleven_twelve_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'twelve_thirteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_twelve_thirteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_twelve_thirteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_twelve_thirteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'thirteen_fourteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_thirteen_fourteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_thirteen_fourteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_thirteen_fourteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'fourteen_fifteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_fourteen_fifteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_fourteen_fifteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_fourteen_fifteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'fifteen_sixteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_fifteen_sixteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_fifteen_sixteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_fifteen_sixteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'sixteen_seventeen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_sixteen_seventeen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_sixteen_seventeen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_sixteen_seventeen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'seventeen_eighteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_seventeen_eighteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_seventeen_eighteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_seventeen_eighteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'eighteen_nineteen',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_eighteen_nineteen), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_eighteen_nineteen), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_eighteen_nineteen_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'nineteen_twenty',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_nineteen_twenty), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_nineteen_twenty), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_nineteen_twenty_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'twenty_twentyone',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_twenty_twentyone), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_twenty_twentyone), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_twenty_twentyone_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'twentyone_twentytwo',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_twentyone_twentytwo), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_twentyone_twentytwo), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_twentyone_twentytwo_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'twentytwo_twentythree',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_twentytwo_twentythree), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_twentytwo_twentythree), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_twentytwo_twentythree_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'twentythree_zero',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_twentythree_zero), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_twentythree_zero), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_twentythree_zero_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'zero_one',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_zero_one), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_zero_one), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_zero_one_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'one_two',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_one_two), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_one_two), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_one_two_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'two_three',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_two_three), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_two_three), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_two_three_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'three_four',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_three_four), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_three_four), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_three_four_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'four_five',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_four_five), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_four_five), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_four_five_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'five_six',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_five_six), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_five_six), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_five_six_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'six_seven',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_six_seven), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_six_seven), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_six_seven_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
					UNION ALL SELECT apd.id, apd.production_date, apd.machine_id, 'seven_eight',
						CAST(REPLACE(NULLIF(TRIM(apd.qty_seven_eight), ''), ',', '') AS DECIMAL(10,2)),
						(CASE WHEN NULLIF(TRIM(apd.qty_seven_eight), '') IS NOT NULL THEN 1 ELSE 0 END),
						NULLIF(TRIM(apd.qty_seven_eight_remark), '')
					FROM tbl_article_production_details apd WHERE {$where}
				) s
				JOIN tbl_article_production_details apd ON apd.id = s.id
			) x
			JOIN tbl_machine_master mm ON mm.id = x.machine_id
			WHERE x.is_down = 1
			GROUP BY x.production_date, mm.id, mm.machine_name
			ORDER BY x.production_date ASC, mm.machine_name ASC
		";

		$params_union = [];
		$union_blocks = 24;
		if (!empty($params)) {
			for ($i = 0; $i < $union_blocks; $i++) {
				foreach ($params as $p) {
					$params_union[] = $p;
				}
			}
		}

		return $this->db->query($sql, $params_union)->result();
	}

	public function get_production_report_spc_part_weight($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('
			spc.id,
			spc.production_date   AS report_date,
			spc.machine_name,
			spc.shift,
			spc.check_time,
			spc.part_article      AS article_name,
			spc.std_wt,
			spc.lsl,
			spc.usl,
			spc.s1,
			spc.s2,
			spc.s3,
			spc.s4,
			spc.s5,
			spc.avg,
			spc.status            AS spc_status,
			mm.id                 AS machine_id,
			mould.id              AS article_id
		');
		$this->db->from('tbl_spc_part_weight spc');
		$this->db->join('tbl_machine_master mm',  'mm.machine_name COLLATE utf8mb4_general_ci = spc.machine_name COLLATE utf8mb4_general_ci',  'left', false);
		$this->db->join('tbl_mould_parts mould',  'mould.article_name COLLATE utf8mb4_general_ci = spc.part_article COLLATE utf8mb4_general_ci', 'left', false);
		$this->db->where('spc.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('spc.production_date >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('spc.production_date <=', $to_date);
		}
		if (!empty($machine_id)) {
			$this->db->where('mm.id', $machine_id);
		}
		if (!empty($article_id)) {
			$this->db->where('mould.id', $article_id);
		}

		$this->db->order_by('spc.production_date', 'DESC');
		$this->db->order_by('spc.machine_name',    'ASC');
		$this->db->order_by('spc.part_article',    'ASC');

		return $this->db->get()->result();
	}

	public function get_production_report_sheet_summary($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select([
			'ps.id',
			'ps.production_id',
			'ps.machine_id',
			'ps.article_id',
			'pr.production_date',
			'mm.machine_name',
			'mp.article_name',
			'ps.approved_qty',
			'ps.average_qty',
			'ps.total_weight',
			'ps.remark',
			'ps.delta',
			'ps.status'
		]);

		$this->db->from('tbl_production_summary ps');
		$this->db->join('tbl_production_report pr', 'pr.id = ps.production_id', 'left');
		$this->db->join('tbl_machine_master mm', 'mm.id = ps.machine_id', 'left');
		$this->db->join('tbl_mould_parts mp', 'mp.id = ps.article_id', 'left');
		$this->db->where('ps.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}

		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}

		if (!empty($machine_id)) {
			$this->db->where('ps.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where('ps.article_id', $article_id);
		}

		$this->db->order_by('pr.production_date', 'DESC');
		$this->db->order_by('ps.id', 'DESC');

		return $this->db->get()->result();
	}

	public function get_production_report_sheet_details($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('apd.*, mm.machine_name, mp.article_name');
		$this->db->from('tbl_article_production_details apd');
		$this->db->join('tbl_machine_master mm', 'mm.id = apd.machine_id', 'left');
		$this->db->join('tbl_mould_parts mp', 'mp.id = apd.article_id', 'left');
		$this->db->where('apd.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(apd.production_date) >=', $from_date);
		}

		if (!empty($to_date)) {
			$this->db->where('DATE(apd.production_date) <=', $to_date);
		}

		if (!empty($machine_id)) {
			$this->db->where('apd.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where('apd.article_id', $article_id);
		}

		$this->db->order_by('apd.production_date', 'DESC');
		$this->db->order_by('apd.id', 'DESC');

		return $this->db->get()->result();
	}

	public function get_production_report_sheet_rejections($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('rpd.*, pr.production_date, mm.machine_name, rr.rm_name AS rejection_name, art.article_name');
		$this->db->from('tbl_rejection_article_list_production_details rpd');
		$this->db->join('tbl_production_report pr', 'pr.id = rpd.production_id', 'left');
		$this->db->join('tbl_machine_master mm', 'mm.id = rpd.machine_id', 'left');
		$this->db->join('tbl_rm_rejection rr', 'rr.id = rpd.rejection_id', 'left');
		$this->db->join("(
			SELECT
				apd.production_id,
				apd.machine_id,
				GROUP_CONCAT(DISTINCT mp.article_name ORDER BY mp.article_name SEPARATOR ', ') AS article_name
			FROM tbl_article_production_details apd
			LEFT JOIN tbl_mould_parts mp ON mp.id = apd.article_id
			WHERE apd.is_deleted = '0'
			GROUP BY apd.production_id, apd.machine_id
		) art", 'art.production_id = rpd.production_id AND art.machine_id = rpd.machine_id', 'left', false);
		$this->db->where('rpd.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}

		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}

		if (!empty($machine_id)) {
			$this->db->where('rpd.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where("EXISTS (SELECT 1 FROM tbl_article_production_details apd WHERE apd.production_id = pr.id AND apd.article_id = " . $this->db->escape($article_id) . ")", null, false);
		}

		$this->db->order_by('pr.production_date', 'DESC');
		$this->db->order_by('rr.id', 'ASC');

		return $this->db->get()->result();
	}

	public function get_production_report_sheet_balance($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('bq.*, pr.production_date, mm.machine_name, rm.rm_name AS raw_material_name, mb.name AS master_batch_name');
		$this->db->from('tbl_balance_quantity_production_detail bq');
		$this->db->join('tbl_production_report pr', 'pr.id = bq.production_id', 'left');
		$this->db->join('tbl_machine_master mm', 'mm.id = pr.machine_id', 'left');
		$this->db->join('tbl_rm_master rm', 'rm.id = bq.raw_material_id', 'left');
		$this->db->join('tbl_mb_master mb', 'mb.id = bq.master_batch_id', 'left');
		$this->db->where('bq.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}

		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}

		if (!empty($machine_id)) {
			$this->db->where('pr.machine_id', $machine_id);
		}

		if (!empty($article_id)) {
			$this->db->where("EXISTS (SELECT 1 FROM tbl_article_production_details apd WHERE apd.production_id = pr.id AND apd.article_id = " . $this->db->escape($article_id) . ")", null, false);
		}

		$this->db->order_by('pr.production_date', 'DESC');
		$this->db->order_by('bq.id', 'DESC');

		return $this->db->get()->result();
	}

	public function get_production_report_used_raw_materials($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('rm.rm_name AS raw_material_name, SUM(COALESCE(rmpd.total_qty,0)) AS used_qty');
		$this->db->from('tbl_raw_material_production_details rmpd');
		$this->db->join('tbl_production_report pr', 'pr.id = rmpd.production_id', 'left');
		$this->db->join('tbl_rm_master rm', 'rm.id = rmpd.raw_material_id', 'left');
		$this->db->where('rmpd.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}
		if (!empty($machine_id)) {
			$this->db->where('rmpd.machine_id', $machine_id);
		}
		if (!empty($article_id)) {
			$this->db->where('rmpd.article_id', $article_id);
		}

		$this->db->group_by('rmpd.raw_material_id');
		$this->db->order_by('raw_material_name', 'ASC');
		return $this->db->get()->result();
	}

	public function get_production_report_used_master_batches($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('mb.name AS master_batch_name, SUM(COALESCE(mbpd.total_qty,0)) AS used_qty');
		$this->db->from('tbl_master_batch_production_details mbpd');
		$this->db->join('tbl_production_report pr', 'pr.id = mbpd.production_id', 'left');
		$this->db->join('tbl_mb_master mb', 'mb.id = mbpd.master_batch_id', 'left');
		$this->db->where('mbpd.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}
		if (!empty($machine_id)) {
			$this->db->where('mbpd.machine_id', $machine_id);
		}
		if (!empty($article_id)) {
			$this->db->where('mbpd.article_id', $article_id);
		}

		$this->db->group_by('mbpd.master_batch_id');
		$this->db->order_by('master_batch_name', 'ASC');
		return $this->db->get()->result();
	}

	public function get_production_report_used_raw_materials_day_by_day($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('DATE(pr.production_date) AS prod_date, rm.rm_name AS raw_material_name, SUM(COALESCE(rmpd.total_qty,0)) AS used_qty');
		$this->db->from('tbl_raw_material_production_details rmpd');
		$this->db->join('tbl_production_report pr', 'pr.id = rmpd.production_id', 'left');
		$this->db->join('tbl_rm_master rm', 'rm.id = rmpd.raw_material_id', 'left');
		$this->db->where('rmpd.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}
		if (!empty($machine_id)) {
			$this->db->where('rmpd.machine_id', $machine_id);
		}
		if (!empty($article_id)) {
			$this->db->where('rmpd.article_id', $article_id);
		}

		$this->db->group_by('DATE(pr.production_date), rmpd.raw_material_id');
		$this->db->order_by('DATE(pr.production_date)', 'DESC');
		$this->db->order_by('raw_material_name', 'ASC');
		return $this->db->get()->result();
	}

	public function get_production_report_used_master_batches_day_by_day($from_date = null, $to_date = null, $machine_id = '', $article_id = '')
	{
		$this->db->select('DATE(pr.production_date) AS prod_date, mb.name AS master_batch_name, SUM(COALESCE(mbpd.total_qty,0)) AS used_qty');
		$this->db->from('tbl_master_batch_production_details mbpd');
		$this->db->join('tbl_production_report pr', 'pr.id = mbpd.production_id', 'left');
		$this->db->join('tbl_mb_master mb', 'mb.id = mbpd.master_batch_id', 'left');
		$this->db->where('mbpd.is_deleted', '0');
		$this->db->where('pr.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('DATE(pr.production_date) >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('DATE(pr.production_date) <=', $to_date);
		}
		if (!empty($machine_id)) {
			$this->db->where('mbpd.machine_id', $machine_id);
		}
		if (!empty($article_id)) {
			$this->db->where('mbpd.article_id', $article_id);
		}

		$this->db->group_by('DATE(pr.production_date), mbpd.master_batch_id');
		$this->db->order_by('DATE(pr.production_date)', 'DESC');
		$this->db->order_by('master_batch_name', 'ASC');
		return $this->db->get()->result();
	}

	public function get_production_report_machine_meta($machine_id = '')
	{
		if (empty($machine_id)) {
			return null;
		}

		$this->db->select('mm.machine_name, pm.plant_name');
		$this->db->from('tbl_machine_master mm');
		$this->db->join('tbl_plant_master pm', 'pm.id = mm.plant_id', 'left');
		$this->db->where('mm.id', $machine_id);
		$this->db->where('mm.is_deleted', '0');
		return $this->db->get()->row();
	}

	private function generate_jwt($jsonKey)
	{
		require_once(APPPATH . '../vendor/autoload.php');

		$now_seconds = time();
		$payload = [
			"iss" => $jsonKey['client_email'],
			"sub" => $jsonKey['client_email'],
			"aud" => "https://oauth2.googleapis.com/token",
			"iat" => $now_seconds,
			"exp" => $now_seconds + 3600,
			"scope" => "https://www.googleapis.com/auth/cloud-platform"
		];

		$privateKey = $jsonKey['private_key'];
		if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== 0) {
			throw new \Exception('Private key is not in PEM format');
		}

		if (!openssl_pkey_get_private($privateKey)) {
			throw new \Exception('OpenSSL cannot read the private key. Check format and permissions.');
		}

		$jwt = Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');

		$client = new \GuzzleHttp\Client();
		$response = $client->post('https://oauth2.googleapis.com/token', [
			'form_params' => [
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion' => $jwt
			]
		]);

		$accessToken = json_decode($response->getBody(), true);
		return $accessToken['access_token'];
	}

	public function send_task_notification_by_token($employee_id_or_department_id, $title, $description, $landing_page, $notification_according, $plant_id)
	{
		try {
		// $serviceAccountPath = 'krivishaapp-84627e698a9a.json';
		$serviceAccountPath = 'krivisha-95737-4d61c448cc9f.json';
		if (!file_exists($serviceAccountPath)) {
			log_message('error', 'FCM notification skipped: service account file not found - ' . $serviceAccountPath);
			return false;
		}

		$serviceAccountRaw = @file_get_contents($serviceAccountPath);
		if ($serviceAccountRaw === false) {
			log_message('error', 'FCM notification skipped: unable to read service account file.');
			return false;
		}

		$serviceAccount = json_decode($serviceAccountRaw, true);
		if (empty($serviceAccount) || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key']) || empty($serviceAccount['project_id'])) {
			log_message('error', 'FCM notification skipped: invalid service account JSON structure.');
			return false;
		}

		try {
			$jwt = $this->generate_jwt($serviceAccount);
		} catch (\Throwable $e) {
			log_message('error', 'FCM notification skipped: ' . $e->getMessage());
			return false;
		}

		$url = 'https://fcm.googleapis.com/v1/projects/' . $serviceAccount['project_id'] . '/messages:send';

		// âœ… Send to individual user
		if ($notification_according == '0') {
			$this->db->where('id', $employee_id_or_department_id);
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('user_data')->row();

			if ($result) {
				$push_token = $result->push_token;
				$client = new \GuzzleHttp\Client();

				if ($push_token) {
					$notification = [
						'title' => $title,
						'body' => $description
					];
					$dataPayload = [
						'type' => 'order_notification',
						'message' => $description,
						'landing_page' => $landing_page
					];

					$fcmNotification = [
						'message' => [
							'token' => $push_token,
							'notification' => $notification,
							'data' => $dataPayload
						]
					];

					$headers = [
						'Authorization' => 'Bearer ' . $jwt,
						'Content-Type' => 'application/json'
					];

					try {
						$response = $client->post($url, [
							'headers' => $headers,
							'json' => $fcmNotification,
						]);

						if ($response->getStatusCode() == 200) {
							log_message('info', 'Order notification sent successfully to token: ' . $push_token);
						} else {
							log_message('error', 'Failed to send notification: ' . $response->getBody());
						}
					} catch (\GuzzleHttp\Exception\RequestException $e) {
						$errorBody = $e->hasResponse()
							? $e->getResponse()->getBody()->getContents()
							: $e->getMessage();
						log_message('error', 'Error sending notification: ' . $errorBody);
					}
				}
			}
		} else {
			if ($employee_id_or_department_id == 13) {
				$employee_id_or_department_id = [13, 18, 25]; // Production + Maintenance
			} else if ($employee_id_or_department_id == 50) { // 50 means order creation or updated  notification
				$employee_id_or_department_id = [11, 25, 25]; // Account Order Creation && Super Admin
			} else if ($employee_id_or_department_id == 51) { // 51 means rm inward notification
				$employee_id_or_department_id = [11, 19, 25]; // Purchase + Account
			} else if ($employee_id_or_department_id == 52) { // 52 means production report notification
				$employee_id_or_department_id = [11, 23, 25]; // salsman + Account + super admin
			} else if ($employee_id_or_department_id == 53) { // 53 means article notification
				$employee_id_or_department_id = [3, 11, 24, 25]; // Production + Account + store
			} else if ($employee_id_or_department_id == 54) { // 54 means Pro Sch notification
				$employee_id_or_department_id = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 24= store Dept 25= admin Dept 
			} else if ($employee_id_or_department_id == 55) { // 55 means rm request notification
				$employee_id_or_department_id = [11, 24, 19, 25]; // 11 = acc Department , 19 = purchase Dept 24= store Dept
			} else if ($employee_id_or_department_id == 56) { // 56 means logistics notification
				$employee_id_or_department_id = [11]; // 11 = acc Department 
			} else if ($employee_id_or_department_id == 57) { // 56 means logistics notification
				$employee_id_or_department_id = [24]; // 
			}

			if (!is_array($employee_id_or_department_id)) {
				$employee_id_or_department_id = [$employee_id_or_department_id];
			}

			$this->db->where_in('department_id', $employee_id_or_department_id);
			if ($plant_id) {
				$this->db->where('plant_id', $plant_id);
			}
			$this->db->where('is_deleted', '0');
			$result = $this->db->get('user_data')->result();

			if ($result) {
				$client = new \GuzzleHttp\Client();

				foreach ($result as $user) {
					$push_token = $user->push_token;
					if (!$push_token)
						continue;

					$notification = [
						'title' => $title,
						'body' => $description
					];
					$dataPayload = [
						'type' => 'order_notification',
						'message' => $description,
						'landing_page' => $landing_page
					];

					$fcmNotification = [
						'message' => [
							'token' => $push_token,
							'notification' => $notification,
							'data' => $dataPayload
						]
					];

					$headers = [
						'Authorization' => 'Bearer ' . $jwt,
						'Content-Type' => 'application/json'
					];

					try {
						$response = $client->post($url, [
							'headers' => $headers,
							'json' => $fcmNotification,
						]);

						if ($response->getStatusCode() == 200) {
							log_message('info', 'Notification sent successfully to token: ' . $push_token);
						} else {
							log_message('error', 'Failed to send notification: ' . $response->getBody());
						}
					} catch (\GuzzleHttp\Exception\RequestException $e) {
						$errorBody = $e->hasResponse()
							? $e->getResponse()->getBody()->getContents()
							: $e->getMessage();
						log_message('error', 'Error sending notification: ' . $errorBody);
					}
				}
			}
		}
		} catch (\Throwable $e) {
			log_message('error', 'Error in send_task_notification_by_token: ' . $e->getMessage());
		}
	}



	// public function send_order_notification_by_token($notification_department, $title, $description, $landing_page)
	// {
	// 	$serviceAccountPath = 'krivishaapp-84627e698a9a.json';
	// 	$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
	// 	$jwt = $this->generate_jwt($serviceAccount);

	// 	$url = 'https://fcm.googleapis.com/v1/projects/' . $serviceAccount['project_id'] . '/messages:send';

	// 	// Get all active users from department
	// 	$this->db->where('department_id', $notification_department);
	// 	$this->db->where('is_deleted', '0');
	// 	$this->db->where('status', '1');
	// 	$result = $this->db->get('tbl_user')->result();

	// 	if ($result) {
	// 		$client = new \GuzzleHttp\Client();
	// 		foreach ($result as $user) {
	// 			$push_token = $user->push_token;

	// 			if (!$push_token)
	// 				continue;

	// 			$notification = [
	// 				'title' => $title,
	// 				'body' => $description
	// 			];

	// 			$dataPayload = [
	// 				'type' => 'order_notification',
	// 				'message' => $description,
	// 				'landing_page' => $landing_page
	// 			];

	// 			$fcmNotification = [
	// 				'message' => [
	// 					'token' => $push_token,
	// 					'notification' => $notification,
	// 					'data' => $dataPayload
	// 				]
	// 			];

	// 			$headers = [
	// 				'Authorization' => 'Bearer ' . $jwt,
	// 				'Content-Type' => 'application/json'
	// 			];

	// 			try {
	// 				$response = $client->post($url, [
	// 					'headers' => $headers,
	// 					'json' => $fcmNotification,
	// 				]);

	// 				if ($response->getStatusCode() == 200) {
	// 					log_message('info', 'Order notification sent successfully to token: ' . $push_token);
	// 				} else {
	// 					log_message('error', 'Failed to send notification: ' . $response->getBody());
	// 				}
	// 			} catch (\GuzzleHttp\Exception\RequestException $e) {
	// 				$errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
	// 				log_message('error', 'Error sending notification: ' . $errorBody);
	// 			}
	// 		}
	// 	}
	// }

	public function get_machine_wise_requirement_store_dashboard($plant_id, $date, $machine_id)
	{
		$start_date = date('Y-m-d');

		$this->db->select('tbl_production_schedules.*, tbl_machine_master.machine_name');
		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id');
		$this->db->where('tbl_production_schedules.is_deleted', '0');

		if ($plant_id) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if ($date) {
			$dates = explode(' to ', $date);
			$start_date = date('Y-m-d', strtotime($dates[0]));
			$end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
			$this->db->where('DATE(tbl_production_schedules.date) >=', $start_date);
			$this->db->where('DATE(tbl_production_schedules.date) <=', $end_date);
		} else {
			$this->db->where('DATE(tbl_production_schedules.date)', $start_date);
		}

		if ($machine_id) {
			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		}
		$this->db->order_by('tbl_production_schedules.production_schedule_start_time', 'asc');

		$schedules = $this->db->get('tbl_production_schedules')->result();

		$machine_summary = [];

		foreach ($schedules as $row) {
			$bom = $this->get_artical_bom($row->article_id);
			if (empty($bom))
				continue;

			$production_qty = $row->qty ?: 0;
			$batch = $bom->batch ?: 0;
			$weight = $bom->weight ?: 0;

			$batch = $bom->batch != "" ? $bom->batch : 0;
			$total_req_batches = $batch > 0 ? $production_qty / $batch : 0;

			$weight = $bom->weight != "" ? $bom->weight : 0;
			$total_batch_wt = $weight * $total_req_batches;

			$raw_material_one = $bom->raw_material_one != "" ? $bom->raw_material_one : 0;
			$raw_material_one_qty = ($raw_material_one / 100) * $total_batch_wt;

			$raw_material_two = $bom->raw_material_two != "" ? $bom->raw_material_two : 0;
			$raw_material_two_qty = ($raw_material_two / 100) * $total_batch_wt;

			$other_rm = $bom->other_rm != "" ? $bom->other_rm : 0;
			$other_rm_qty = ($other_rm / 100) * $total_batch_wt;

			$master_batch = $bom->master_batch != "" ? $bom->master_batch : 0;
			$master_batch_qty = ($master_batch / 100) * $total_batch_wt;

			$raw_material_qtyy = $raw_material_one_qty + $raw_material_two_qty;
			$raw_material_qty = $raw_material_one_qty + $raw_material_two_qty;

			$particulars = $this->get_artical_particaulars($row->article_id, $bom->id);
			$packing_bag_inner = 0;
			$packing_bag_outer = 0;
			$sticker = 0;
			$cap = 0;
			$can_ear = 0;
			$handle = 0;
			$rubber_bush = 0;
			$wiser = 0;



			if (!empty($particulars)) {
				foreach ($particulars as $p) {
					$qty = $row->qty / ($bom->batch ?? 0);

					$bom_qty = ceil($bom_qty);
					switch (strtolower($p->particulars_type)) {
						case 'packing bags inner':
							$packing_bag_inner = $p->quantity * $qty;
							break;
						case 'packing bags outer':
							$packing_bag_outer = $p->quantity * $qty;
							break;
						case 'sticker':
							$sticker = $p->quantity * $qty;
							break;
						case 'caps':
							$cap = $p->quantity * $qty;
							break;
						case 'can ear':
							$can_ear = $p->quantity * $qty;
							break;
						case 'handels':
							$handle = $p->quantity * $qty;
							break;
						case 'rubber bush':
							$rubber_bush = $p->quantity * $qty;
							break;
						case 'wisers':
							$wiser = $p->quantity * $qty;
							break;
					}
				}
			}
			// Group by machine
			if (!isset($machine_summary[$row->machine_id])) {
				$machine_summary[$row->machine_id] = [
					'machine_name' => $row->machine_name,
					'total_rm' => 0,
					'total_master_batch' => 0,
					'total_other_rm' => 0,
					'packing_bag_inner' => 0,
					'packing_bag_outer' => 0,
					'sticker' => 0,
					'cap' => 0,
					'can_ear' => 0,
					'handle' => 0,
					'rubber_bush' => 0,
					'wiser' => 0
				];
			}

			$machine_summary[$row->machine_id]['total_rm'] += $raw_material_qty;
			$machine_summary[$row->machine_id]['total_master_batch'] += $master_batch_qty;
			$machine_summary[$row->machine_id]['total_other_rm'] += $other_rm_qty;
			$machine_summary[$row->machine_id]['packing_bag_inner'] += $packing_bag_inner;
			$machine_summary[$row->machine_id]['packing_bag_outer'] += $packing_bag_outer;
			$machine_summary[$row->machine_id]['sticker'] += $sticker;
			$machine_summary[$row->machine_id]['cap'] += $cap;
			$machine_summary[$row->machine_id]['can_ear'] += $can_ear;
			$machine_summary[$row->machine_id]['handle'] += $handle;
			$machine_summary[$row->machine_id]['rubber_bush'] += $rubber_bush;
			$machine_summary[$row->machine_id]['wiser'] += $wiser;
		}

		// ðŸ”¹ FINAL OUTPUT FORMATTING (3 DECIMALS ONLY)
		foreach ($machine_summary as &$machine) {
			foreach ($machine as $key => $value) {
				if ($key !== 'machine_name') {
					$machine[$key] = number_format((float) $value, 3, '.', '');
				}
			}
		}

		return array_values($machine_summary);
	}


	public function get_machine_request_log_store_dashboard($length, $start, $search)
	{
		$plant_id = $this->input->post('plant_id');
		$date = $this->input->post('date');
		$machine_id = $this->input->post('machine_id');

		$this->db->select('
        rq.id,
        rq.request_no,
        rq.request_date,
        rq.plant_id,
        rq.machine_id,
        rq.employee_id,
        mm.machine_name,
        p.plant_name,
        u.first_name AS requested_by,
        rm.rm_name,
        uom.uom_name,
        rd.request_quantity,
        rd.received_qty
    ');
		$this->db->from('tbl_rm_request_qty AS rq');
		$this->db->join('tbl_machine_master AS mm', 'mm.id = rq.machine_id', 'left');
		$this->db->join('tbl_plant_master AS p', 'p.id = rq.plant_id', 'left');
		$this->db->join('user_data AS u', 'u.id = rq.employee_id', 'left');
		$this->db->join('tbl_request_rm_qty_data AS rd', 'rd.database_request_id = rq.id', 'left');
		$this->db->join('tbl_rm_master AS rm', 'rm.id = rd.raw_material_id', 'left');
		$this->db->join('tbl_uom_master AS uom', 'uom.id = rd.uom_id', 'left');
		$this->db->where('rq.is_deleted', '0');
		$this->db->where('rq.machine_id !=', null);
		if (!empty($plant_id)) {
			$this->db->where('rq.plant_id', $plant_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('rq.machine_id', $machine_id);
		}
		if ($date) {
			$dates = explode(' to ', $date);
			$start_date = date('Y-m-d', strtotime($dates[0]));
			$end_date = isset($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : $start_date;
			if ($start_date != '' && $end_date != '') {
				$this->db->where('DATE(rq.request_date) >=', $start_date);
				$this->db->where('DATE(rq.request_date) <=', $end_date);
			} else {
				$this->db->where('DATE(rq.request_date)', $start_date);
			}
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('mm.machine_name', $search);
			$this->db->or_like('p.plant_name', $search);
			$this->db->or_like('u.first_name', $search);
			$this->db->or_like('rq.request_no', $search);
			$this->db->group_end();
		}
		$this->db->order_by('rq.id', 'DESC');
		$query = $this->db->get();
		$result = $query->result();
		$this->db->from('tbl_rm_request_qty AS rq');
		$this->db->where('rq.is_deleted', '0');
		$total_count = $this->db->count_all_results();

		return [
			'data' => $result,
			'total_count' => $total_count
		];
	}

	public function get_transport_report_data($length, $start, $search)
	{
		try {
			// Cap "All" (-1) to 50000 rows — query is optimized via JOINs so this is safe
			if ($length == -1) {
				$start = 0;
				$length = 50000;
			}

			$bundleColumnSub  = $this->db->field_exists('bundle_bag_qty', 'tbl_order_sub_details')       ? 'bundle_bag_qty' : 'order_quantity';
			$bundleColumnCont = $this->db->field_exists('bundle_bag_qty', 'tbl_order_container_details') ? 'bundle_bag_qty' : 'order_quantity';

			// Pre-aggregate bundle quantities with JOINs (avoids 2 correlated subqueries per row)
			$this->db->select('
				tbl_outward_orders.id AS transport_id,
				tbl_outward_orders.order_id,
				tbl_outward_orders.created_on,
				tbl_outward_orders.vehicle,
				tbl_outward_orders.driver_mobile,
				tbl_outward_orders.driver_name,
				tbl_outward_orders.freight_amount,
				tbl_outward_orders.invoice_value,
				tbl_transport_master.transport_name,
				tbl_location_master.city AS location_name,
				tbl_customers.party_name,
				COALESCE(sub_bundle.sub_total, 0) + COALESCE(cont_bundle.cont_total, 0) AS total_bundle
			', FALSE);
			$this->db->from('tbl_outward_orders');
			$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
			$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
			$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
			$this->db->join(
				'(SELECT order_id, SUM(CAST(NULLIF(' . $bundleColumnSub . ', "") AS DECIMAL(10,2))) AS sub_total FROM tbl_order_sub_details WHERE is_deleted = 0 GROUP BY order_id) sub_bundle',
				'sub_bundle.order_id = tbl_outward_orders.order_id', 'left'
			);
			$this->db->join(
				'(SELECT order_id, SUM(CAST(NULLIF(' . $bundleColumnCont . ', "") AS DECIMAL(10,2))) AS cont_total FROM tbl_order_container_details WHERE is_deleted = 0 GROUP BY order_id) cont_bundle',
				'cont_bundle.order_id = tbl_outward_orders.order_id', 'left'
			);

			$this->db->where('tbl_outward_orders.is_deleted', '0');
			$this->db->order_by('tbl_outward_orders.created_on', 'DESC');

			// Date filter
			$search_date = $this->input->post('search_date');
			if (!empty($search_date)) {
				$exp = explode("to", $search_date);
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(tbl_outward_orders.created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
					$this->db->where('DATE(tbl_outward_orders.created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
				} else if (isset($exp[0]) && !empty(trim($exp[0]))) {
					$this->db->where('DATE(tbl_outward_orders.created_on)', date("Y-m-d", strtotime(trim($exp[0]))));
				}
			}

			// Party filter
			$party_action = $this->input->post('party_action');
			if (!empty($party_action)) {
				$this->db->where('tbl_outward_orders.party_id', $party_action);
			}

			// Location filter
			$location_action = $this->input->post('location_action');
			if (!empty($location_action)) {
				$this->db->where('tbl_outward_orders.location_id', $location_action);
			}

			// Transporter filter
			$transporter_action = $this->input->post('transporter_action');
			if (!empty($transporter_action)) {
				$this->db->where('tbl_outward_orders.transport_id', $transporter_action);
			}

			// Freight status filter
			$freight_status_action = $this->input->post('freight_status_action');
			if (!empty($freight_status_action)) {
				$this->db->where('tbl_outward_orders.freight_status', $freight_status_action);
			}

			// Division filter
			$division_action = $this->input->post('division_action');
			if (!empty($division_action)) {
				$this->db->where('tbl_outward_orders.division', $division_action);
			}

			// Search filter
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->or_like('tbl_outward_orders.order_id', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->or_like('tbl_location_master.city', $search);
				$this->db->or_like('tbl_transport_master.transport_name', $search);
				$this->db->group_end();
			}

			// Apply limit only if not showing all records (-1 means show all)
			if ($length > 0) {
				$this->db->limit($length, $start);
			}
			$query = $this->db->get();
			$result = $query->result();

			// Get total count
			$this->db->reset_query();
			$this->db->select('COUNT(tbl_outward_orders.id) as total_count');
			$this->db->from('tbl_outward_orders');
			$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
			$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
			$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
			$this->db->where('tbl_outward_orders.is_deleted', '0');

			if (!empty($search_date)) {
				$exp = explode("to", $search_date);
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(tbl_outward_orders.created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
					$this->db->where('DATE(tbl_outward_orders.created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
				} else if (isset($exp[0]) && !empty(trim($exp[0]))) {
					$this->db->where('DATE(tbl_outward_orders.created_on)', date("Y-m-d", strtotime(trim($exp[0]))));
				}
			}

			if (!empty($party_action)) {
				$this->db->where('tbl_outward_orders.party_id', $party_action);
			}

			if (!empty($location_action)) {
				$this->db->where('tbl_outward_orders.location_id', $location_action);
			}

			if (!empty($transporter_action)) {
				$this->db->where('tbl_outward_orders.transport_id', $transporter_action);
			}

			if (!empty($freight_status_action)) {
				$this->db->where('tbl_outward_orders.freight_status', $freight_status_action);
			}

			if (!empty($division_action)) {
				$this->db->where('tbl_outward_orders.division', $division_action);
			}

			if (!empty($search)) {
				$this->db->group_start();
				$this->db->or_like('tbl_outward_orders.order_id', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->or_like('tbl_location_master.city', $search);
				$this->db->or_like('tbl_transport_master.transport_name', $search);
				$this->db->group_end();
			}

			$count_query = $this->db->get();
			$total_count = $count_query->row()->total_count ?? 0;

			return [
				'data' => $result,
				'total_count' => $total_count
			];
		} catch (Exception $e) {
			error_log('get_transport_report_data Error: ' . $e->getMessage());
			return [
				'data' => array(),
				'total_count' => 0,
				'error' => $e->getMessage()
			];
		}
	}

	public function get_transport_report_summary()
	{
		try {
			$this->db->select('
				COALESCE(SUM(CAST(NULLIF(invoice_value, "") AS DECIMAL(10,2))), 0) AS total_invoice_value,
				COALESCE(SUM(CAST(NULLIF(freight_amount, "") AS DECIMAL(10,2))), 0) AS total_freight,
				COUNT(*) AS total_orders
			');
			$this->db->from('tbl_outward_orders');
			$this->db->where('is_deleted', '0');

			// Apply same filters as the data query
			$search_date = $this->input->post('search_date');
			if (!empty($search_date)) {
				$exp = explode("to", $search_date);
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
					$this->db->where('DATE(created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
				}
			}

			$party_action = $this->input->post('party_action');
			if (!empty($party_action)) {
				$this->db->where('party_id', $party_action);
			}

			$location_action = $this->input->post('location_action');
			if (!empty($location_action)) {
				$this->db->where('location_id', $location_action);
			}

			$transporter_action = $this->input->post('transporter_action');
			if (!empty($transporter_action)) {
				$this->db->where('transport_id', $transporter_action);
			}

			$freight_status_action = $this->input->post('freight_status_action');
			if (!empty($freight_status_action)) {
				$this->db->where('freight_status', $freight_status_action);
			}

			$division_action = $this->input->post('division_action');
			if (!empty($division_action)) {
				$this->db->where('division', $division_action);
			}

			// Add search filter to summary query if provided
			$search = $this->input->post('search');
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->or_like('tbl_outward_orders.order_id', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->or_like('tbl_location_master.city', $search);
				$this->db->or_like('tbl_transport_master.transport_name', $search);
				$this->db->group_end();
			}

			$query = $this->db->get();
			$result = $query->row();

			$total_invoice = (isset($result->total_invoice_value) && $result->total_invoice_value) ? floatval($result->total_invoice_value) : 0;
			$total_freight = (isset($result->total_freight) && $result->total_freight) ? floatval($result->total_freight) : 0;
			$freight_percentage = ($total_invoice > 0) ? (($total_freight / $total_invoice) * 100) : 0;


			// Aggregated data from own vehicle details
			$this->db->select('
				COALESCE(SUM(CAST(NULLIF(exact_km, "") AS DECIMAL(10,2))), 0) AS total_own_km,
				COALESCE(
					SUM(
						CAST(NULLIF(diesel_expense, "") AS DECIMAL(10,2)) + 
						CAST(NULLIF(driver_expense, "") AS DECIMAL(10,2)) + 
						CAST(NULLIF(maintenance, "") AS DECIMAL(10,2))
					), 0
				) AS total_expenses
			');
			$this->db->from('tbl_own_vehicle_details');
			$this->db->where('is_deleted', '0');

			if (!empty($search_date)) {
				$exp = explode("to", $search_date);
				if (isset($exp[0]) && isset($exp[1])) {
					$this->db->where('DATE(created_on) >=', date("Y-m-d", strtotime(trim($exp[0]))));
					$this->db->where('DATE(created_on) <=', date("Y-m-d", strtotime(trim($exp[1]))));
				}
			}
			if (!empty($party_action)) {
				$this->db->where('party_id', $party_action);
			}
			if (!empty($location_action)) {
				$this->db->where('location_id', $location_action);
			}

			$summary_own = $this->db->get()->row();

			return array(
				'status' => 'success',
				'total_invoice_value' => $total_invoice,
				'total_freight_value' => $total_freight,
				'freight_percentage' => $freight_percentage,
				'own_vehicle_km' => $summary_own->total_own_km ?? 0,
				'distance_covered' => $summary_own->total_own_km ?? 0,
				'total_freight' => $total_freight,
				'total_expenses' => $summary_own->total_expenses ?? 0
			);
		} catch (Exception $e) {
			return array(
				'status' => 'error',
				'message' => $e->getMessage(),
				'total_invoice_value' => 0,
				'total_freight_value' => 0,
				'freight_percentage' => 0,
				'own_vehicle_km' => 0,
				'distance_covered' => 0,
				'total_freight' => 0,
				'total_expenses' => 0
			);
		}
	}

	public function get_transport_order_details($transport_id)
	{
		$this->db->select('
			tbl_outward_orders.*,
			tbl_transport_master.transport_name,
			tbl_location_master.city AS location_name,
			tbl_location_master.district_name,
			tbl_location_master.state_name,
			tbl_location_master.pincode,
			tbl_customers.party_name,
			tbl_customers.mobile,
			tbl_customers.contact_name
		');
		$this->db->from('tbl_outward_orders');
		$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
		$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_outward_orders.id', $transport_id);
		$this->db->where('tbl_outward_orders.is_deleted', '0');
		$query = $this->db->get();
		return $query->row();
	}

	public function get_order_sub_details($order_id)
	{
		$this->db->select('*');
		$this->db->from('tbl_order_sub_details');
		$this->db->where('order_id', $order_id);
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		return $query->result();
	}
	public function get_single_impression_rate($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('tbl_impression_rate')->row_array();
	}

	public function get_all_impression_rates()
	{
		$this->db->select('tbl_impression_rate.*, tbl_mould_parts.article_name');
		$this->db->from('tbl_impression_rate');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_impression_rate.article_id', 'left');
		$this->db->where('tbl_impression_rate.is_deleted', '0');
		$this->db->order_by('tbl_impression_rate.id', 'DESC');
		return $this->db->get()->result();
	}

	public function set_impression_rate()
	{
		$data = array(
			'article_id' => $this->input->post('article_id', true),
			'impression_rate' => $this->input->post('impression_rate', true),
			'status' => '1',
			'is_deleted' => '0'
		);
		$id = $this->input->post('id', true);
		if (!empty($id)) {
			// Check for duplicates
			$exists = $this->db->where('article_id', $data['article_id'])->where('id !=', $id)->where('is_deleted', '0')->get('tbl_impression_rate')->row();
			if ($exists) return '3';
			$this->db->where('id', $id);
			$this->db->update('tbl_impression_rate', $data);
			return '2';
		} else {
			// Check for duplicates
			$exists = $this->db->where('article_id', $data['article_id'])->where('is_deleted', '0')->get('tbl_impression_rate')->row();
			if ($exists) return '3';
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_impression_rate', $data);
			return '1';
		}
	}

	// ─── Process Parameter Sheet ──────────────────────────────────────────────

	public function get_process_parameter_list($from_date = '', $to_date = '', $machine_name = '', $plant_id = '', $article_name = '')
	{
		$table_name = $this->_process_parameter_table_name();
		if (empty($table_name)) {
			return [];
		}

		$this->db->select('
			pp.*,
			ud.first_name  AS employee_name,
			pm.plant_name
		');
		$this->db->from($table_name . ' pp');
		$this->db->join('user_data ud',        'ud.id  = pp.employee_id', 'left');
		$this->db->join('tbl_plant_master pm', 'pm.id  = pp.plant_id',   'left');
		$this->db->where('pp.is_deleted', '0');

		if (!empty($from_date)) {
			$this->db->where('pp.production_date >=', $from_date);
		}
		if (!empty($to_date)) {
			$this->db->where('pp.production_date <=', $to_date);
		}
		if (!empty($machine_name)) {
			$this->db->like('pp.machine_name', $machine_name);
		}
		if (!empty($plant_id)) {
			$this->db->where('pp.plant_id', $plant_id);
		}
		if (!empty($article_name)) {
			$this->db->where('pp.article_name', $article_name);
		}

		$this->db->order_by('pp.id', 'DESC');
		return $this->db->get()->result();
	}

	public function get_single_process_parameter($id)
	{
		$table_name = $this->_process_parameter_table_name();
		if (empty($table_name)) {
			return null;
		}

		$this->db->select('
			pp.*,
			ud.first_name  AS employee_name,
			pm.plant_name
		');
		$this->db->from($table_name . ' pp');
		$this->db->join('user_data ud',        'ud.id  = pp.employee_id', 'left');
		$this->db->join('tbl_plant_master pm', 'pm.id  = pp.plant_id',   'left');
		$this->db->where('pp.id', (int)$id);
		$this->db->where('pp.is_deleted', '0');
		return $this->db->get()->row();
	}
}
