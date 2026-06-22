<?php
class Api_model extends CI_model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
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
	public function get_app_login()
	{
		// Decode JSON input
		$json_input = file_get_contents('php://input');
		$request = json_decode($json_input, true);
		
		// Validate JSON was decoded properly
		if ($request === null) {
			http_response_code(400);
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Invalid JSON format';
			$json_arr['data'] = null;
			echo json_encode($json_arr);
			return;
		}
		
		// Validate required fields
		if (empty($request['mobile_number']) || empty($request['password'])) {
			http_response_code(400);
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Missing required fields: mobile_number and password';
			$json_arr['data'] = null;
			echo json_encode($json_arr);
			return;
		}
		
		$push_token = isset($request['push_token']) ? $request['push_token'] : '';
		$mobile_number = trim($request['mobile_number']);
		$password = trim($request['password']);
		
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->where('mobile_number', $mobile_number);
		$this->db->where('org_password', $password);
		$result = $this->db->get('user_data');
		$result = $result->row();
		
		if (!empty($result)) {
			$this->db->where('id', $result->id);
			$this->db->update('user_data', ['push_token' => $push_token]);
			// Re-fetch so the response includes the updated push_token
			$result = $this->db->where('id', $result->id)->get('user_data')->row();
			http_response_code(200);
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Login successful';
		} else {
			http_response_code(401);
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Invalid credentials';
		}
		
		$json_arr['data'] = $result;
		echo json_encode($json_arr);
	}
	public function cancel_order_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$this->db->where('is_deleted', '0');
		$this->db->where('order_id', $request['order_id']);
		$result = $this->db->get('tbl_order_details')->row();
		if (!empty($result)) {
			$this->db->where('order_id', $request['order_id']);
			$this->db->update('tbl_order_details', ['order_status' => '6']);
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Order cancelled successfully.';
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Order not found.';
		}
		echo json_encode($json_arr);

	}
	public function get_all_department_api()
	{
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		$department = $this->db->get('tbl_krivisha_department')->result();
		if (!empty($department)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Department retrieved successfully.';
			$json_arr['data'] = $department;

		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Department found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_all_party_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$attending_salesperson_id = $request['sales_id_for_party'];
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		if (!empty($attending_salesperson_id)) {
			$this->db->where('attending_salesperson_id', $attending_salesperson_id);
		}
		$party = $this->db->get('tbl_customers')->result();
		if (!empty($party)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'party retrieved successfully.';
			$json_arr['data'] = $party;

		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No party found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_employee_according_department_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$department_id = (int)(isset($request['department_id']) ? $request['department_id'] : 0);
		$this->db->where('is_deleted', '0');
		// Use FIND_IN_SET to support multi-department CSV values (e.g. "13,18")
		$this->db->where("FIND_IN_SET('" . $department_id . "', department_id) > 0", NULL, FALSE);
		$result = $this->db->get('user_data');
		$result = $result->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
		} else {
			$json_arr['status'] = 'false';
		}
		$json_arr['message'] = 'success';
		$json_arr['data'] = $result;
		echo json_encode($json_arr);
	}
	public function get_user_profile_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$this->db->where('is_deleted', '0');
		$this->db->where('emp_id', $request['emp_id']);
		$query = $this->db->get('user_data');
		$result = $query->result();

		if (!empty($result)) {
			foreach ($result as &$row) {
				if (!empty($row->emp_photo)) {
					$row->image_path = base_url('assets/images/' . $row->emp_photo);
				} else {
					$row->image_path = null; // or default image path
				}
			}

			$json_arr = [
				'status' => 'true',
				'message' => 'success',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No data found',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}

	public function set_manual_task_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$task_head = $request['task_head_id'];
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
		$data = array(
			'task_id' => $task_id,
			'task_head' => $request['task_head_id'],
			'employee_id' => $request['employee_id'],
			'party_id' => $request['party_id'],
			'complete_by_date' => date('Y-m-d', strtotime($request['date'])),
			'complete_by_time' => $request['time'],
			'priority' => $request['priority'],
			'details_of_task' => $request['remark'],
			'department_id' => $request['department_id'],
			'assign_to_id' => $request['team_member_id'],
			'plant_id' => $request['plant_id'],
		);
		$data['created_on'] = date("Y-m-d H:i:s");
		$this->db->insert('tbl_manual_task', $data);
		$last_insert_id = $this->db->insert_id();
		$history_data = array(
			'task_id' => $last_insert_id,
			'task_status' => '1',
			'task_action' => '1',
			'department_id' => $request['department_id'],
			'assign_to_id' => $request['team_member_id'],
			'last_updated_by' => $request['employee_id'],
			'details_of_task' => 'Task Created',
			'last_updated_date' => date('Y-m-d'),
			'created_on' => date('Y-m-d H:i:s'),
		);
		$this->db->insert('tbl_manual_task_history', $history_data);

		$this->db->where('id', $request['employee_id']);
		$result = $this->db->get('user_data')->row();

		$this->db->select('id, plant_id'); 
		$this->db->where('id', $request['team_member_id']);
		$user_data = $this->db->get('user_data')->row();

		$title = 'Task Assigned';
		$description = 'You have been assigned a task ' . $task_id . ' by ' .
			$result->first_name;
		$landing_page = 'manual_task_list';
		$notification_according = '0'; //means according employee
		$notification_data = array(
			'notification_title' => $title,
			'notification_description' => $description,
			'notification_department' => $request['department_id'],
			'landing_page' => $landing_page,
			'order_id' => $task_id,
			'plant_id' => $user_data->plant_id,
			'employee_id' => $request['team_member_id'],
			'created_on' => date('Y-m-d H:i:s')
		);
		$this->db->insert('tbl_notifications', $notification_data);
		$this->send_task_notification_by_token($request['team_member_id'], $title, $description, $landing_page, $notification_according, $user_data->plant_id);

		$json_arr['message'] = 'Order Added Successfully';

		$json_arr['status'] = 'true';
		echo json_encode($json_arr);
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

			// Extract the number at the end (e.g., from ENQ-100 → 100)
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
	// public function get_all_manual_task_list_api()
	// {
	// 	$request = json_decode(file_get_contents('php://input'), true);
	// 	$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
	// 	$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
	// 	$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
	// 	$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
	// 	$task_head = isset($request['task_head']) ? trim($request['task_head']) : '';
	// 	$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
	// 	$department_id = isset($request['department_id']) ? trim($request['department_id']) : '';
	// 	$order_status = isset($request['order_status']) ? trim($request['order_status']) : '';
	// 	$task_action = isset($request['task_action']) ? trim($request['task_action']) : '';
	// 	$employee_id = isset($request['employee_id']) ? trim($request['employee_id']) : '';
	// 	$search = isset($request['search']) ? trim($request['search']) : '';
	// 	$this->db->select('tbl_manual_task.*, tbl_customers.party_name, assign_user.first_name AS assigned_to_name, employee_user.first_name AS employee_name, tbl_krivisha_department.department');
	// 	$this->db->from('tbl_manual_task');
	// 	$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
	// 	$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
	// 	$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
	// 	$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
	// 	$this->db->where('tbl_manual_task.is_deleted', '0');
	// 	if (!empty($from_date) && !empty($to_date)) {
	// 		$this->db->where('DATE(tbl_manual_task.created_on) >=', date('Y-m-d', strtotime($from_date)));
	// 		$this->db->where('DATE(tbl_manual_task.created_on) <=', date('Y-m-d', strtotime($to_date)));
	// 	} elseif (!empty($from_date)) {
	// 		$this->db->where('DATE(tbl_manual_task.created_on)', date('Y-m-d', strtotime($from_date)));
	// 	}
	// 	if (!empty($task_head)) {
	// 		$this->db->where('tbl_manual_task.task_head', $task_head);
	// 	}
	// 	if (!empty($party_id)) {
	// 		$this->db->where('tbl_manual_task.party_id', $party_id);
	// 	}
	// 	if (!empty($order_status)) {
	// 		$this->db->where('tbl_manual_task.task_status', $order_status);
	// 	}
	// 	if (!empty($task_action)) {
	// 		$this->db->where('tbl_manual_task.task_action', $task_action);
	// 	}
	// 	if ($department_id != '25') {
	// 		$this->db->where('tbl_manual_task.department_id', $department_id);
	// 		$this->db->where('tbl_manual_task.employee_id', $employee_id);
	// 	}
	// 	if (!empty($search)) {
	// 		$this->db->group_start();
	// 		$this->db->or_like('tbl_manual_task.task_name', $search);
	// 		$this->db->or_like('tbl_customers.party_name', $search);
	// 		$this->db->or_like('assign_user.first_name', $search);
	// 		$this->db->or_like('employee_user.first_name', $search);
	// 		$this->db->or_like('tbl_krivisha_department.department', $search);
	// 		$this->db->group_end();
	// 	}

	// 	$this->db->order_by('tbl_manual_task.id', 'DESC');
	// 	$this->db->limit($limit, $offset);

	// 	$result = $this->db->get()->result();
	// 	if (!empty($result)) {
	// 		$json_arr = [
	// 			'status' => 'true',
	// 			'message' => 'Task List retrieved successfully.',
	// 			'data' => $result
	// 		];
	// 	} else {
	// 		$json_arr = [
	// 			'status' => 'false',
	// 			'message' => 'No task found.',
	// 			'data' => []
	// 		];
	// 	}
	// 	echo json_encode($json_arr);
	// }
	public function get_all_manual_task_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$task_head = isset($request['task_head']) ? trim($request['task_head']) : '';
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
		$department_id = isset($request['department_id']) ? trim($request['department_id']) : '';
		$order_status = isset($request['order_status']) ? trim($request['order_status']) : '';
		$task_action = isset($request['task_action']) ? trim($request['task_action']) : '';
		$employee_id = isset($request['employee_id']) ? trim($request['employee_id']) : '';
		$assign_to_id = isset($request['assign_to_id']) ? trim($request['assign_to_id']) : '';
		$task_priority = isset($request['task_priority']) ? trim($request['task_priority']) : '';
		$sales_dashboard_id = isset($request['sales_dashboard_id']) ? trim($request['sales_dashboard_id']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';

		// Common select and joins
		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, 
        assign_user.first_name AS assigned_to_name, 
        employee_user.first_name AS employee_name, 
        tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_manual_task.created_on) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_manual_task.created_on) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_manual_task.created_on)', date('Y-m-d', strtotime($from_date)));
		}
		if (!empty($task_head)) {
			$this->db->where('tbl_manual_task.task_head', $task_head);
		}
		if (!empty($task_priority)) {
			$this->db->where('tbl_manual_task.priority', $task_priority);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_manual_task.party_id', $party_id);
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_manual_task.task_status', $order_status);
		}
		if (!empty($task_action)) {
			$this->db->where('tbl_manual_task.task_action', $task_action);
		}
		if ($department_id != '25') {
			if ($department_id != '') {
				$this->db->where('tbl_manual_task.department_id', $department_id);
			}
			$this->db->where('tbl_manual_task.employee_id', $employee_id);
		}
		if (!empty($sales_dashboard_id)) {
			$this->db->where('tbl_manual_task.assign_to_id', $sales_dashboard_id);
		}
		// if (!empty($assign_to_id)) {
		// 	$this->db->where('tbl_manual_task.assign_to_id', $assign_to_id);
		// }
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_manual_task.task_id', $search);
			$this->db->or_like('tbl_manual_task.complete_by_date', $search);
			$this->db->or_like('tbl_manual_task.complete_by_time', $search);
			$this->db->or_like('tbl_manual_task.remark', $search);
			$this->db->or_like('tbl_manual_task.details_of_task', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_manual_task.id', 'DESC');

		$this->db->limit($limit, $offset);
		$result1 = $this->db->get()->result();

		// Second query (for assign_to_id)
		$this->db->select('tbl_manual_task.*, tbl_customers.party_name, 
        assign_user.first_name AS assigned_to_name, 
        employee_user.first_name AS employee_name, 
        tbl_krivisha_department.department');
		$this->db->from('tbl_manual_task');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_manual_task.party_id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task.department_id', 'left');
		$this->db->join('user_data AS assign_user', 'assign_user.id = tbl_manual_task.assign_to_id', 'left');
		$this->db->join('user_data AS employee_user', 'employee_user.id = tbl_manual_task.employee_id', 'left');
		$this->db->where('tbl_manual_task.is_deleted', '0');
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_manual_task.created_on) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_manual_task.created_on) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_manual_task.created_on)', date('Y-m-d', strtotime($from_date)));
		}
		if (!empty($task_head)) {
			$this->db->where('tbl_manual_task.task_head', $task_head);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_manual_task.party_id', $party_id);
		}
		if (!empty($task_priority)) {
			$this->db->where('tbl_manual_task.priority', $task_priority);
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_manual_task.task_status', $order_status);
		}
		if (!empty($task_action)) {
			$this->db->where('tbl_manual_task.task_action', $task_action);
		}
		if (!empty($sales_dashboard_id)) {
			$this->db->where('tbl_manual_task.assign_to_id', $sales_dashboard_id);
		}
		if ($department_id != '25') {
			if ($department_id != '') {
				$this->db->where('tbl_manual_task.department_id', $department_id);
			}
			$this->db->where('tbl_manual_task.assign_to_id', $employee_id);
		}
		// if (!empty($assign_to_id)) {
		// 	$this->db->where('tbl_manual_task.assign_to_id', $assign_to_id);
		// }
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_manual_task.task_id', $search);
			$this->db->or_like('tbl_manual_task.complete_by_date', $search);
			$this->db->or_like('tbl_manual_task.complete_by_time', $search);
			$this->db->or_like('tbl_manual_task.remark', $search);
			$this->db->or_like('tbl_manual_task.details_of_task', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('tbl_krivisha_department.department', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_manual_task.id', 'DESC');
		$this->db->limit($limit, $offset);
		$result2 = $this->db->get()->result();

		// Merge both
		$res = array_merge($result1, $result2);

		$grouped_result = [];
		foreach ($res as $row) {
			$grouped_result[$row->id] = $row;
		}
		$merged_result = array_values($grouped_result);
		if (!empty($merged_result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Task List retrieved successfully.',
				'data' => $merged_result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No task found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	public function get_all_auto_task_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$task_action = isset($request['task_action']) ? trim($request['task_action']) : '';
		$task_status = isset($request['task_status']) ? trim($request['task_status']) : '';
		$order_department = isset($request['order_department']) ? trim($request['order_department']) : '';
		$department_id = isset($request['department_id']) ? trim($request['department_id']) : '';
		$user_department_id = isset($request['user_department_id']) ? trim($request['user_department_id']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';

		$this->db->select('
        tbl_auto_task_list.*, 
        employee_user.first_name AS employee_name, 
        assign_user.first_name AS assigned_to_name, 
        tbl_krivisha_department.department AS d_name,
		tbl_customers.party_name
    ');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('user_data AS employee_user', 'tbl_auto_task_list.employee_id = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_auto_task_list.date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date)', date('Y-m-d', strtotime($from_date)));
		}

		if (!empty($task_action)) {
			$this->db->where('tbl_auto_task_list.task_action', $task_action);
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_auto_task_list.party_id', $party_id);
		}
		if (!empty($task_status)) {
			$this->db->where('tbl_auto_task_list.task_status', $task_status);
		}

		if (!empty($order_department)) {
			$this->db->where('tbl_auto_task_list.order_department', $order_department);
		}
		if ($user_department_id != '25') {
			if ($order_department != '3') {
				$this->db->where('tbl_auto_task_list.department_id', $user_department_id);
			}
		}
		if (!empty($department_id)) {
			$this->db->where('tbl_auto_task_list.order_status', '0');
			$this->db->where('tbl_auto_task_list.order_department', '1');
			$this->db->where('tbl_auto_task_list.department_id', $department_id);
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('employee_user.first_name', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('assign_user.first_name', $search);
			$this->db->or_like('tbl_auto_task_list.task_id', $search);
			$this->db->or_like('tbl_auto_task_list.details_of_task', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_auto_task_list.updated_on', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			foreach ($result as $key => $value) {

				$order_id = $value->task_id;
				if ($value->order_department == '1') {
					if ($value->task_action == '1') {
						$order_ststus = $this->Admin_model->get_outward_order_status($value->task_id);

						if ($value->order_status == '3') {
							$order_ststus = 'Partially Dispatched';
						} else if ($value->order_status == '4') {
							$order_ststus = 'Full Dispatched';
						} else if ($value->order_status == '5') {
							$order_ststus = 'Order Closed';
						} else if ($value->order_status == '6') {
							$order_ststus = 'Order Cancelled';
						} else if ($value->order_status == '7') {
							$order_ststus = 'Printing Inprocess';
						} else if ($value->order_status == '8') {
							$order_ststus = 'Printing Completed';
						} else if ($value->order_status == '9') {
							$order_ststus = 'Dispatch Inprocess';
						} else if ($order_ststus == 'Pending') {
							if ($value->order_department_status == '3' && $value->order_status == '2') {
								$order_ststus = 'Dispatch Inprocess';
							} else if ($value->order_department_status == '2' && $value->order_status == '0') {
								$order_ststus = 'Printing Inprocess';
							} else if ($value->order_department_status == '3' && $value->order_status == '3') {
								$order_ststus = 'Partially Dispatched';
							} else if ($value->order_department_status == '3' && $value->order_status == '0') {
								$order_ststus = 'Dispatch Inprocess';
							}
						} else if ($order_ststus == 'Processed to Account') {
							if ($value->order_status == '0' && $value->order_department_status == '2') {
								$order_ststus = 'Printing Inprocess';
							} else {
								$order_ststus = 'Pending';
							}

						} else if ($order_ststus == 'Dispatch Inprocess') {
							if ($value->order_status == '2' && $value->order_department_status == '2') {
								$order_ststus = 'Printing Completed';
							} else if ($value->order_status == '0' && $value->order_department_status == '2') {
								$order_ststus = 'Printing Inprocess';
							} else {
								$order_ststus = 'Dispatch Inprocess';
							}

						}
					} else {
						$order_ststus = 'Completed';
					}
				} else {
					if ($value->task_status == '1') {
						$order_ststus = 'Pending';
					} else {
						$order_ststus = 'Completed';
					}
				}
				$result[$key]->order_status = $order_ststus;
				$order_department = $value->order_department;
				if ($order_department == '1') {
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
						$sub_result = $this->db->get('tbl_order_sub_details');
						$value->sub_result = $sub_result->result();
					} else {
						$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
						$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
						$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
						$this->db->where('tbl_order_container_details.order_id', $order_id);
						$this->db->where('tbl_order_container_details.is_deleted', '0');
						$this->db->order_by('tbl_order_container_details.id', 'ASC');
						$sub_result = $this->db->get('tbl_order_container_details');
						$value->sub_result = $sub_result->result();
					}
				} elseif ($order_department == '3') {
					$this->db->select("
						tbl_maintenance_production.*,
						tbl_plant.plant_name,
						GROUP_CONCAT(tbl_maintaince_problems.problem ORDER BY tbl_maintaince_problems.problem SEPARATOR ', ') AS problems,
						
						CASE 
							WHEN tbl_maintenance_production.maintaince = 1 THEN tbl_machine_master.machine_name
							WHEN tbl_maintenance_production.maintaince = 2 THEN tbl_mould_parts.article_name
							WHEN tbl_maintenance_production.maintaince = 3 THEN tbl_machine_master.machine_name
							WHEN tbl_maintenance_production.maintaince = 4 THEN tbl_plant_master.plant_name
							WHEN tbl_maintenance_production.maintaince = 5 THEN 'N/A'
							ELSE 'N/A'
						END AS sub_type_maintenance
					", false);

					$this->db->join('tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
					$this->db->join('tbl_plant_master', 'tbl_maintenance_production.sub_type_id = tbl_plant_master.id', 'left');
					$this->db->join('tbl_machine_master', 'tbl_maintenance_production.sub_type_id = tbl_machine_master.id', 'left');
					$this->db->join('tbl_mould_parts', 'tbl_maintenance_production.sub_type_id = tbl_mould_parts.id', 'left');
					$this->db->join('tbl_plant_master as tbl_plant', 'tbl_maintenance_production.plant_id = tbl_plant.id', 'left');

					$this->db->where('tbl_maintenance_production.mwo_code', $order_id);
					$this->db->where('tbl_maintenance_production.is_deleted', '0');

					$sub_result = $this->db->get('tbl_maintenance_production');
					$value->sub_result = $sub_result->result();
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
					$this->db->group_by('tbl_production_schedules.id');
					$sub_result = $this->db->get();
					$value->sub_result = $sub_result->result();

				}
			}
		}

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Task List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No task found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}

	public function get_all_order_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$type_of_order = isset($request['type_of_order']) ? trim($request['type_of_order']) : '';
		$ink_type = isset($request['ink_type']) ? trim($request['ink_type']) : '';
		$order_status = isset($request['order_status']) ? trim($request['order_status']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
		$attending_salesperson_id = isset($request['sales_id_for_party']) ? trim($request['sales_id_for_party']) : '';

		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_order_details.order_date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_order_details.order_date)', date('Y-m-d', strtotime($from_date)));
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_order_details.party_id', $party_id);
		}
		if (!empty($attending_salesperson_id)) {
			$this->db->where('tbl_order_details.sales_person_id', $attending_salesperson_id);
		}
		if (!empty($type_of_order)) {
			$this->db->where('tbl_order_details.type_of_order', $type_of_order);
		}
		if (!empty($ink_type)) {
			$this->db->where('tbl_order_details.ink_type', $ink_type);
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_order_details.order_status', $order_status);
		}

		if (!empty($search)) {
			$formatted_date = false;
			if (preg_match('/^\d{2}-\d{2}-\d{2,4}$/', $search)) {
				$formatted_date = date('Y-m-d', strtotime($search));
			}

			$search_lower = strtolower(trim($search));

			$status_map = [
				'pending' => '1',
				'processed to account' => '2',
				'forwarded' => '2',
				'partially dispatched' => '3',
				'full dispatched' => '4',
				'order closed' => '5',
				'closed' => '5',
				'order cancelled' => '6',
				'close' => '6'
			];
			$type_map = [
				'household' => '1',
				'container' => '2',
				'both' => '3'
			];
			$ink_map = [
				'plain' => '1',
				'printing' => '2'
			];

			$status_value = $type_value = $ink_value = false;

			foreach ($status_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$status_value = $value;
					break;
				}
			}
			foreach ($type_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$type_value = $value;
					break;
				}
			}
			foreach ($ink_map as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$ink_value = $value;
					break;
				}
			}
			$this->db->group_start();
			$this->db->or_like('tbl_order_details.order_id', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			if ($ink_value !== false) {
				$this->db->or_where('tbl_order_details.ink_type', $ink_value);
			} else {
				$this->db->or_like('tbl_order_details.ink_type', $search);
			}

			if ($type_value !== false) {
				$this->db->or_where('tbl_order_details.type_of_order', $type_value);
			} else {
				$this->db->or_like('tbl_order_details.type_of_order', $search);
			}

			if ($formatted_date) {
				$this->db->or_like('tbl_order_details.order_date', $formatted_date);
			} else {
				$this->db->or_like('tbl_order_details.order_date', $search);
			}

			if ($status_value !== false) {
				$this->db->or_where('tbl_order_details.order_status', $status_value);
			} else {
				$this->db->or_like('tbl_order_details.order_status', $search);
			}
			$this->db->group_end();
		}

		$this->db->order_by('tbl_order_details.id', 'DESC');
		$this->db->limit($limit, $offset);
		$orders = $this->db->get()->result();
		if (!empty($orders)) {
			foreach ($orders as $key => $order) {
				$order_id = $order->order_id;
				$auto_task_order = $this->db->get_where('tbl_auto_task_list', array('task_id' => $order_id))->row();
				$orders[$key]->order_department_status = $auto_task_order->order_department_status ?? 0;
				$orders[$key]->department_id = $auto_task_order->department_id ?? 0;
				if ($order->type_of_order == '2') {
					$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
					$this->db->from('tbl_order_sub_details');
					$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
					$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
					$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
					$this->db->where('tbl_order_sub_details.order_id', $order_id);
					$this->db->where('tbl_order_sub_details.is_deleted', '0');
					$this->db->order_by('tbl_order_sub_details.id', 'ASC');
					$orders[$key]->sub_details = $this->db->get()->result();
				} else {
					$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
					$this->db->from('tbl_order_container_details');
					$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
					$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
					$this->db->where('tbl_order_container_details.order_id', $order_id);
					$this->db->where('tbl_order_container_details.is_deleted', '0');
					$this->db->order_by('tbl_order_container_details.id', 'ASC');
					$orders[$key]->sub_details = $this->db->get()->result();
				}
			}

			$json_arr = [
				'status' => 'true',
				'message' => 'Order list retrieved successfully.',
				'data' => $orders
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No orders found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}

	public function set_update_manual_task_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request['id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Task ID is required.'
			]);
			return;
		}

		$auto_or_manual = isset($request['auto_or_manual']) ? $request['auto_or_manual'] : 0;
		$task_action = isset($request['task_action']) ? $request['task_action'] : '';
		$last_updated_by = isset($request['last_updated_by']) ? $request['last_updated_by'] : '';
		$department_id = isset($request['department_id']) ? $request['department_id'] : '';
		$order_id = isset($request['order_id']) ? $request['order_id'] : '';
		if ($task_action == '2') {
			$this->db->where('is_deleted', '0');
			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_sub_details', ['order_status' => '10']);

			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_container_details', ['order_status' => '10']);

			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_details', array('order_status' => '5'));//order close manually by admin

			$this->db->where('is_deleted', '0');
			$this->db->where('task_id', $order_id);
			$this->db->update('tbl_auto_task_list', array('order_status' => '5'));

		}
		$order_department_status = '';
		if ($task_action == '2') {
			$department_id = '25';
		}
		$data = [
			'task_status' => $request['task_status'],
			'task_action' => $task_action,
			'department_id' => $department_id,
			'assign_to_id' => $request['team_member_id'],
			'details_of_task' => $request['remark'],
			'last_updated_date' => date('Y-m-d H:i:s'),

		];
		//echo"<pre>";print_r($data);exit;
		$history_data = $data;

		if ($auto_or_manual == 1) {
			$this->db->where('id', $request['id']);
			$this->db->update('tbl_manual_task', $data);

			$history_data['task_id'] = $request['id'];
			$history_data['created_on'] = date('Y-m-d H:i:s');
			$history_data['last_updated_by'] = $last_updated_by;
			$this->db->where('task_id', $request['id']);
			$this->db->order_by('id', 'DESC');
			$this->db->limit(1);
			$last_history_entry = $this->db->get('tbl_manual_task_history')->row();

			if (
				!$last_history_entry ||
				$last_history_entry->task_status != $data['task_status'] ||
				$last_history_entry->task_action != $data['task_action'] ||
				$last_history_entry->department_id != $data['department_id'] ||
				$last_history_entry->assign_to_id != $data['assign_to_id'] ||
				$last_history_entry->details_of_task != $data['details_of_task'] ||
				$last_history_entry->created_on != $data['created_on']
			) {
				$this->db->insert('tbl_manual_task_history', $history_data);
			}
		} else {
			if ($task_action == '1') {
				if ($department_id == "17") {
					$order_department_status = '2';
					$this->db->where('order_id', $order_id);
					$order_res = $this->db->get('tbl_order_details')->row();
					$department_status = '2'; // 2 means in printing process 
					if (!empty($order_res) && $order_res->type_of_order == '2') {
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
					}

					$this->db->where('order_id', $order_id);
					$this->db->update('tbl_order_details', array('order_status' => '7'));
					$this->db->where('task_id', $order_id);
					$this->db->update('tbl_auto_task_list', array('order_status' => '7', 'order_department_status' => $department_status));
				}
				if ($department_id == "11") {
					$order_department_status = '3';

					$this->db->where('order_id', $order_id);
					$sub_pri_result = $this->db->get('tbl_order_sub_details')->result();

					if (!empty($sub_pri_result)) {
						foreach ($sub_pri_result as $sub_pri) {
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
			if ($task_action == '2') {
				$this->db->where('is_deleted', '0');
				$this->db->where('order_id', $order_id);
				$this->db->update('tbl_order_sub_details', ['order_status' => '10']);
				$this->db->where('order_id', $order_id);
				$this->db->update('tbl_order_container_details', ['order_status' => '10']);
			}
			$history_data['task_id'] = $request['id'];
			$history_data['created_on'] = date('Y-m-d H:i:s');
			$history_data['last_updated_by'] = $last_updated_by;
			$this->db->where('task_id', $request['id']);
			$this->db->order_by('id', 'DESC');
			$this->db->limit(1);
			$last_history_entry = $this->db->get('tbl_auto_task_list_history')->row();

			if (
				!$last_history_entry ||
				$last_history_entry->task_status != $data['task_status'] ||
				$last_history_entry->task_action != $data['task_action'] ||
				$last_history_entry->department_id != $data['department_id'] ||
				$last_history_entry->assign_to_id != $data['assign_to_id'] ||
				$last_history_entry->details_of_task != $data['details_of_task'] ||
				$last_history_entry->created_on != $data['created_on']
			) {
				$this->db->insert('tbl_auto_task_list_history', $history_data);
			}
			$this->db->where('id', $request['id']);
			$this->db->update('tbl_auto_task_list', $data);
		}

		echo json_encode([
			'status' => 'true',
			'message' => 'Task updated successfully.'
		]);
	}
	public function get_all_plant_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$department_id = $request['department_id'];
		$asssgin_plant_id = $request['plant_id'];
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		if ($department_id != '25') {
			$this->db->where('id', $asssgin_plant_id);
		}
		$plant = $this->db->get('tbl_plant_master')->result();
		if (!empty($plant)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'plant retrieved successfully.';
			$json_arr['data'] = $plant;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No plant found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_subcategory_according_maintenance_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$maintenance = $request['maintenance_id'];
		$this->db->from('tbl_maintaince_problems');
		$this->db->where('tbl_maintaince_problems.is_deleted', '0');
		$this->db->where('tbl_maintaince_problems.maintaince', $maintenance);
		$this->db->order_by('tbl_maintaince_problems.id', 'DESC');

		switch ($maintenance) {
			case '1':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;

			case '2':
				$this->db->select('tbl_maintaince_problems.*, tbl_mould_parts.article_name');
				$this->db->join('tbl_mould_parts', 'tbl_maintaince_problems.type_id = tbl_mould_parts.id', 'left');
				$this->db->where('tbl_mould_parts.is_deleted', '0');
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;

			case '3':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				$this->db->where('tbl_machine_master.department_type', '1');
				$this->db->where('tbl_machine_master.is_deleted', '0');
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;

			case '4':
				$this->db->select('tbl_maintaince_problems.*, tbl_plant_master.plant_name');
				$this->db->join('tbl_plant_master', 'tbl_maintaince_problems.type_id = tbl_plant_master.id', 'left');
				$this->db->where('tbl_plant_master.is_deleted', '0');
				$this->db->group_by('tbl_maintaince_problems.type_id');
				break;

			case '5':
				$this->db->select('tbl_maintaince_problems.*');
				break;

			default:
				echo json_encode([
					'status' => 'false',
					'message' => 'Invalid maintenance ID',
					'data' => []
				]);
				return;
		}

		$query = $this->db->get();
		$result = $query->result();

		echo json_encode([
			'status' => !empty($result) ? 'true' : 'false',
			'message' => 'success',
			'data' => $result
		]);
	}
	public function get_all_sub_types_problems_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$maintenance = $request['maintenance_id'];
		$problem_type = $request['selected_type'];
		$this->db->from('tbl_maintaince_problems');
		$this->db->where('tbl_maintaince_problems.maintaince', $maintenance);
		$this->db->where('tbl_maintaince_problems.type_id', $problem_type);
		$this->db->where('tbl_maintaince_problems.is_deleted', '0');
		$this->db->order_by('tbl_maintaince_problems.id', 'DESC');

		switch ($maintenance) {
			case '1':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				break;

			case '2':
				$this->db->select('tbl_maintaince_problems.*, tbl_mould_parts.article_name');
				$this->db->join('tbl_mould_parts', 'tbl_maintaince_problems.type_id = tbl_mould_parts.id', 'left');
				break;

			case '3':
				$this->db->select('tbl_maintaince_problems.*, tbl_machine_master.machine_name');
				$this->db->join('tbl_machine_master', 'tbl_maintaince_problems.type_id = tbl_machine_master.id', 'left');
				break;

			case '4':
				$this->db->select('tbl_maintaince_problems.*, tbl_plant_master.plant_name');
				$this->db->join('tbl_plant_master', 'tbl_maintaince_problems.type_id = tbl_plant_master.id', 'left');
				break;

			default:
				$this->db->select('tbl_maintaince_problems.*');
				$this->db->where('tbl_maintaince_problems.maintaince', '5');
				$this->db->where('tbl_maintaince_problems.type_id', '0');
				break;
		}
		$query = $this->db->get();
		$result = $query->result();
		if (!empty($result)) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Data fetched successfully.',
				'data' => $result
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No data found.',
				'data' => []
			]);
		}
	}
	public function get_all_maintenance_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$mwo_code = isset($request['mwo_code']) ? trim($request['mwo_code']) : '';
		$maintaince = isset($request['maintain_action']) ? trim($request['maintain_action']) : '';
		$sub_category = isset($request['sub_category']) ? trim($request['sub_category']) : '';
		$type_of_action = isset($request['type_of_action']) ? trim($request['type_of_action']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
		$status_of_work = isset($request['status_of_work']) ? trim($request['status_of_work']) : '';
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';

		$this->db->select('
        tbl_maintenance_production.*,
        GROUP_CONCAT(tbl_maintaince_problems.problem ORDER BY tbl_maintaince_problems.problem SEPARATOR ", ") as problems,
        tbl_plant_master.plant_name,
        user_data.first_name
    ');
		$this->db->from('tbl_maintenance_production');
		$this->db->join('tbl_maintaince_problems', 'FIND_IN_SET(tbl_maintaince_problems.id, tbl_maintenance_production.problem_id)', 'left');
		$this->db->join('tbl_plant_master', 'tbl_maintenance_production.plant_id = tbl_plant_master.id', 'left');
		$this->db->join('user_data', 'tbl_maintenance_production.employee_id = user_data.id', 'left');
		$this->db->where('tbl_maintenance_production.is_deleted', '0');
		$this->db->group_by('tbl_maintenance_production.id');
		$this->db->order_by('tbl_maintenance_production.id', 'DESC');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_maintenance_production.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_maintenance_production.date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_maintenance_production.date)', date('Y-m-d', strtotime($from_date)));
		}

		if (!empty($plant_id)) {
			$this->db->where('tbl_maintenance_production.plant_id', $plant_id);
		}

		if (!empty($mwo_code)) {
			$this->db->where('tbl_maintenance_production.mwo_code', $mwo_code);
		}

		if (!empty($status_of_work)) {
			$this->db->where('tbl_maintenance_production.status_of_work', $status_of_work);
		}

		if (!empty($maintaince)) {
			$this->db->where('tbl_maintenance_production.maintaince', $maintaince);
		}

		if (!empty($type_of_action)) {
			$this->db->where('tbl_maintenance_production.type_of_action', $type_of_action);
		}

		if (!empty($sub_category)) {
			$this->db->where('tbl_maintenance_production.sub_type_id', $sub_category);
		}

		$this->db->group_by('tbl_maintenance_production.id');

		if (!empty($search)) {
			$this->db->group_start();

			$this->db->or_like('tbl_maintenance_production.date', $search);

			if (empty($mwo_code)) {
				$this->db->or_like('tbl_maintenance_production.mwo_code', $search);
			}

			$this->db->or_like('tbl_plant_master.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);

			if (empty($type_of_action)) {
				$this->db->or_like('tbl_maintenance_production.type_of_action', $search);
			}

			if (empty($maintaince)) {
				$this->db->or_like('tbl_maintenance_production.maintaince', $search);
			}

			$this->db->or_like('tbl_maintaince_problems.problem', $search);
			$this->db->group_end();
		}

		$this->db->order_by('tbl_maintenance_production.id', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		foreach ($result as &$row) {
			switch ($row->maintaince) {
				case 1:
					$machine = $this->Admin_model->get_type_of_machine($row->sub_type_id);
					$row->maintenance_type = 'Machine';
					$row->type_name = $machine ? $machine->machine_name : 'Unknown';
					break;
				case 2:
					$article = $this->Admin_model->get_type_of_article($row->sub_type_id);
					$row->maintenance_type = 'Mould/Article Name';
					$row->type_name = $article ? $article->article_name : 'Unknown';
					break;
				case 3:
					$printing_unit = $this->Admin_model->get_type_of_machine($row->sub_type_id);
					$row->maintenance_type = 'Printing Unit';
					$row->type_name = $printing_unit ? $printing_unit->machine_name : 'Unknown';
					break;
				case 4:
					$plant = $this->Admin_model->get_type_of_plant($row->sub_type_id);
					$row->maintenance_type = 'Plant';
					$row->type_name = $plant ? $plant->plant_name : 'Unknown';
					break;
				case 5:
				default:
					$row->maintenance_type = 'Other';
					$row->type_name = 'N/A';
					break;
			}
		}
		$json_arr = [
			'status' => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'Maintenance retrieved successfully.' : 'No Maintenance found.',
			'data' => $result
		];

		echo json_encode($json_arr);
	}
	public function set_maintenance_data_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$update_id = isset($request['update_id']) ? $request['update_id'] : '';
		$details_ids = isset($request['details']) ? implode(',', $request['details']) : '';
		$mwo_code = $this->Api_model->generate_mwo_code();
		$data = array(
			'plant_id' => $request['plant_id'],
			'date' => $request['date'],
			'employee_id' => $request['employee_id'],
			'type_of_action' => $request['maintenance_type'],
			'maintaince' => $request['maintenance_required'],
			'sub_type_id' => $request['sub_category'],
			'problem_id' => $details_ids,
		);
		if ($data['maintaince'] == '1' || $data['maintaince'] == '3') {
			$this->db->update('tbl_machine_master', array('status' => '0'), array('id' => $data['sub_type_id']));
		} else if ($data['maintaince'] == '2') {
			$this->db->update('tbl_mould_parts', array('status' => '0'), array('id' => $data['sub_type_id']));
		} else {
			$this->db->update('tbl_plant_master', array('status' => '0'), array('id' => $data['sub_type_id']));
		}
		if ($update_id == '') {
			$data['mwo_code'] = $mwo_code;
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_maintenance_production', $data);
			$process_data = array(
				'task_id' => $mwo_code,
				'order_department' => 3,
				'employee_id' => $request['employee_id'],
				'date' => date('Y-m-d'),
				'plant_id' => $request['my_plant_id'],
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
				'last_updated_by' => $request['employee_id'],
				'plant_id' => $request['my_plant_id'],
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
				'last_updated_by' => $request['employee_id'],
				'plant_id' => $request['my_plant_id'],
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);
			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();
			// Notification Work for maintenance
			$title = 'Maintenance Schedule';
			$description = 'Maintenance Schedule Created ' . $mwo_code . ' by ' .
				$employee_data->first_name;
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
				'plant_id' => $request['my_plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(13, $title, $description, $landing_page, $notification_according, $request['my_plant_id']);

			$json_arr = [
				'status' => 'true',
				'message' => 'Maintenance added Successfully'
			];
		} else {
			$this->db->where('id', $update_id);
			$this->db->update('tbl_maintenance_production', $data);
			$json_arr['message'] =
				$json_arr = [
					'status' => 'true',
					'message' => 'Maintenance updated Successfully'
				];
		}
		echo json_encode($json_arr);
	}
	public function generate_mwo_code()
	{
		$this->db->select('mwo_code');
		$this->db->from('tbl_maintenance_production');
		$this->db->order_by('mwo_code', 'DESC');
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
	//29-05-2025 Apis

	public function get_all_own_vehicle_api()
	{
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		$department = $this->db->get('tbl_vehical')->result();
		if (!empty($department)) {
			$json_arr = [
				'status' => true,
				'message' => 'data retrieved successfully',
				'data' => $department
			];
		} else {
			$json_arr = [
				'status' => true,
				'message' => 'No data successfully',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_location_api()
	{
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		$department = $this->db->get('tbl_location_master')->result();
		if (!empty($department)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Location retrieved successfully.';
			$json_arr['data'] = $department;

		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Location found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function set_own_vehicle_data_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$update_id = isset($request['update_id']) ? $request['update_id'] : '';
		$out_km = isset($request['out_km']) ? $request['out_km'] : '';
		$in_km = isset($request['in_km']) ? $request['in_km'] : '';
		$purpose_ids = isset($request['purpose']) ? implode(',', $request['purpose']) : '';
		$data = array(
			'vehical_id' => $request['vehical_id'],
			'challan_dc_no' => $request['challan_dc_no'],
			'invoice_no' => $request['invoice_no'],
			'location_id' => $request['location_id'],
			'pincode' => $request['pincode'],
			'purpose' => $purpose_ids,
			'party_id' => $request['party_id'],
			'in_km' => !empty($in_km) ? $in_km : 0,
			'out_km' => !empty($out_km) ? $out_km : 0,
			'market_freight' => $request['market_freight'],
			'diesel_topup' => $request['diesel_topup'],
			'driver_expense' => $request['driver_expense'],
			'maintenance' => $request['maintenance'],
			'invoice_value' => $request['invoice_value'],
			'exact_km' => $request['exact_km'],
			'diesel_rate' => $request['diesel_rate'],
			'diesel_expense' => $request['diesel_expense'],
			'transport_percent' => $request['transport'],
		);
		if ($update_id == '') {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_own_vehicle_details', $data);
			$json_arr = [
				'status' => 'true',
				'message' => 'Vehicle data added Successfully'
			];
		} else {
			$this->db->where('id', $update_id);
			$this->db->update('tbl_own_vehicle_details', $data);
			$json_arr = [
				'status' => 'true',
				'message' => 'Vehicle data updated Successfully'
			];
		}
		echo json_encode($json_arr);
	}
	public function get_last_out_km_vehicle_details()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Vehicle List retrieved successfully.',
				'data' => $result->out_km
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Vehicle found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_own_vehicle_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';

		$this->db->select('tbl_own_vehicle_details.*, tbl_vehical.vehical,tbl_location_master.city,tbl_customers.party_name');
		$this->db->from('tbl_own_vehicle_details');
		$this->db->join('tbl_vehical ', 'tbl_vehical.id = tbl_own_vehicle_details.vehical_id', 'left');
		$this->db->join('tbl_location_master', 'tbl_location_master.id = tbl_own_vehicle_details.location_id', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_own_vehicle_details.party_id', 'left');
		$this->db->where('tbl_own_vehicle_details.is_deleted', '0');
		$this->db->group_by('tbl_own_vehicle_details.id');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->or_like('tbl_vehical.vehical', $search);
			$this->db->or_like('tbl_own_vehicle_details.challan_dc_no', $search);
			$this->db->or_like('tbl_own_vehicle_details.invoice_no', $search);
			$this->db->or_like('tbl_location_master.city', $search);
			$this->db->or_like('tbl_own_vehicle_details.pincode', $search);
			$this->db->or_like(' tbl_own_vehicle_details.purpose', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_own_vehicle_details.in_km', $search);
			$this->db->or_like('tbl_own_vehicle_details.market_freight', $search);
			$this->db->or_like('tbl_own_vehicle_details.diesel_topup', $search);
			$this->db->or_like('tbl_own_vehicle_details.driver_expense', $search);
			$this->db->or_like('tbl_own_vehicle_details.maintenance', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_own_vehicle_details.id', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Vehicle List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Vehicle found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}

	public function get_all_party_history_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';

		if (empty($party_id)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Party ID is required.',
				'data' => []
			]);
			return;
		}
		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.party_id', $party_id);
		$this->db->where('tbl_order_details.is_deleted', '0');
		$this->db->order_by('tbl_order_details.id', 'DESC');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$orders = $query->result();

		if (!empty($orders)) {
			foreach ($orders as $key => $order) {
				$order_id = $order->order_id;

				if ($order->type_of_order == '2') {
					$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
					$this->db->from('tbl_order_sub_details');
					$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
					$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
					$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
					$this->db->where('tbl_order_sub_details.order_id', $order_id);
					$this->db->where('tbl_order_sub_details.is_deleted', '0');
					$this->db->order_by('tbl_order_sub_details.id', 'ASC');
					$sub_query = $this->db->get();
					$orders[$key]->sub_details = $sub_query->result();
				} else {
					$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
					$this->db->from('tbl_order_container_details');
					$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
					$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
					$this->db->where('tbl_order_container_details.order_id', $order_id);
					$this->db->where('tbl_order_container_details.is_deleted', '0');
					$this->db->order_by('tbl_order_container_details.id', 'ASC');
					$sub_query = $this->db->get();
					$orders[$key]->sub_details = $sub_query->result();
				}
			}
			$response = [
				'status' => 'true',
				'message' => 'Party order list retrieved successfully.',
				'data' => $orders
			];
		} else {
			$response = [
				'status' => 'false',
				'message' => 'No history found.',
				'data' => []
			];
		}

		echo json_encode($response);
	}
	public function get_all_article()
	{
		$this->db->select('tbl_mould_parts.id,tbl_mould_parts.article_name');
		$this->db->where('is_deleted', '0');
		// $this->db->where('status', '1');
		$result = $this->db->get('tbl_mould_parts')->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Article retrieved successfully.';
			$json_arr['data'] = $result;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Article found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_all_mwo_code()
	{
		$this->db->select('tbl_maintenance_production.mwo_code');
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_maintenance_production')->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'MWO Code retrieved successfully.';
			$json_arr['data'] = $result;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Code found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_all_article_group_api()
	{
		$this->db->select('*');
		$this->db->where('is_deleted', '0');
		$plant = $this->db->get('tbl_group_of_article')->result();
		if (!empty($plant)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Article group retrieved successfully.';
			$json_arr['data'] = $plant;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Group found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_article_according_group_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$this->db->select('tbl_mould_parts.id,tbl_mould_parts.article_name,tbl_mould_parts.group_of_article_id');
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$this->db->where('tbl_mould_parts.is_deleted', '0');
		$this->db->where('group_of_article_id', $request['group_of_article_id']);
		$result = $this->db->get('tbl_mould_parts');
		$result = $result->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
		} else {
			$json_arr['status'] = 'false';
		}
		$json_arr['message'] = 'success';
		$json_arr['data'] = $result;
		echo json_encode($json_arr);
	}

	public function set_create_order_data_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$save_or_process = $request['save_or_process'];
		$order_id = $request['order_id'];
		$order_status = '';
		if ($save_or_process == 'save') {
			$order_status = '1';
		} else {
			$order_status = '2';
		}
		$party_id = $request['party_id'];
		$sales_person = $this->db->select('attending_salesperson_id')->from('tbl_customers')->where('id', $party_id)->get()->row();
		$sales_person_id = $sales_person->attending_salesperson_id ?? null;

		$data = array(
			'party_id' => $request['party_id'],
			'type_of_order' => $request['type_of_order'],
			'sales_person_id' => $sales_person_id,
			'order_status' => $order_status,
			'order_date' => date('Y-m-d'),
		);
		if ($request['type_of_order'] == '2') {
			$data['ink_type'] = $request['ink_type'];
		}
		$current_time = date('Y-m-d H:i:s');
		if (empty($order_id)) {
			$order_id = $this->generate_custom_order_id();
			$data['order_id'] = $order_id;
			$data['created_on'] = $current_time;
			$data['plant_id'] = $request['plant_id'];
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
			'department_id' => $department_id,
			'order_department' => 1,
			'employee_id' => $request['employee_id'],
			'party_id' => $request['party_id'],
			'type_of_order' => $request['type_of_order'],
			'date' => date('Y-m-d'),
			'plant_id' => $request['plant_id'],
			'created_on' => $current_time
		);
		if ($order_status == '2') {
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
					'last_updated_by' => $request['employee_id'],
					'plant_id' => $request['plant_id'],
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
				'type_of_order' => $request['type_of_order'],
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $request['employee_id'],
				'plant_id' => $request['plant_id'],
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);
			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();
			// Notification Work
			$title = 'Order Update';
			$description = 'Order Created ' . $order_id . ' And Assigned to Accounts Department by ' .
				$employee_data->first_name;
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
				'plant_id' => $request['plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(50, $title, $description, $landing_page, $notification_according, $request['plant_id']);
		}
		$details = isset($request['order_details']) ? $request['order_details'] : [];

		$sub_details_data = [];
		$container_details_data = [];

		$this->db->where('order_id', $order_id);
		$this->db->delete('tbl_order_sub_details');

		$this->db->where('order_id', $order_id);
		$this->db->delete('tbl_order_container_details');

		foreach ($details as $detail) {
			$data = array(
				'group_of_article_id' => $detail['group_id'],
				'article_id' => $detail['article_id'],
				'order_quantity' => $detail['quantity'],
				'remark' => $detail['remark'],
				'order_id' => $order_id,
				'plant_id' => $request['plant_id'],
				'created_on' => $current_time,
			);

			$brand_data = array(
				'brand_type_id' => ($detail['brand_id'] !== '') ? $detail['brand_id'] : null,
				'ink_type' => $request['ink_type']
			);

			$merged_data = array_merge($data, $brand_data);

			if (in_array($brand_data['brand_type_id'], [null, 'null'], true)) {
				$container_details_data[] = $data;
			} else {
				$sub_details_data[] = $merged_data;
			}
		}

		if (!empty($sub_details_data)) {
			$this->db->insert_batch('tbl_order_sub_details', $sub_details_data);
		}

		if (!empty($container_details_data)) {
			$this->db->insert_batch('tbl_order_container_details', $container_details_data);
		}
		echo json_encode([
			'status' => 'true',
			'message' => 'Data processed successfully'
		]);
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
	public function get_article_according_multiple_group_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$group_ids_input = isset($request['group_of_article_id']) ? $request['group_of_article_id'] : [];

		if (is_array($group_ids_input)) {
			$group_ids_array = array_map('strval', $group_ids_input);
		} else {
			$group_ids_array = array_filter(array_map('trim', explode(',', $group_ids_input)));
		}

		$this->db->select('tbl_mould_parts.id, tbl_mould_parts.article_name, tbl_mould_parts.group_of_article_id');
		$this->db->where('status', '1');
		$this->db->where('is_deleted', '0');

		if (!empty($group_ids_array)) {
			$this->db->where_in('group_of_article_id', $group_ids_array);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No group IDs provided.',
				'data' => []
			]);
			return;
		}

		$result = $this->db->get('tbl_mould_parts')->result();

		$json_arr['status'] = !empty($result) ? 'true' : 'false';
		$json_arr['message'] = 'success';
		$json_arr['data'] = $result;

		echo json_encode($json_arr);
	}


	public function get_all_machine_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$plant_id = $request['plant_id'] ?? '';
		$this->db->select(['id', 'machine_name']);
		$this->db->where('is_deleted', '0');
		$this->db->where_in('department_id', ['2', '3', '6', '14']);
		$this->db->where('status', '1');
		if (!empty($plant_id)) {
		}
		$result = $this->db->get('tbl_machine_master');
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Machine retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Machine found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_machine_data_for_production()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$plant_id = $request['plant_id'] ?? '';
		$this->db->select('tbl_machine_master.*,tbl_plant_master.plant_name');
		$this->db->from('tbl_machine_master');
		$this->db->join('tbl_plant_master', 'tbl_machine_master.plant_id = tbl_plant_master.id', 'left');
		$this->db->where('tbl_machine_master.is_deleted', '0');
		$this->db->where_in('tbl_machine_master.status', ['1', '2']);
		$this->db->where_in('tbl_machine_master.department_id', ['2', '3', '6', '14']);
		if (!empty($plant_id)) {
			$this->db->where('tbl_machine_master.plant_id', $plant_id);
		}
		$result = $this->db->get();
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Machine retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Machine found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}

	public function get_all_raw_material_api()
	{
		$this->db->select('tbl_rm_master.*, tbl_rm_type.type, tbl_make.make, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_master.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_master.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$this->db->order_by('tbl_rm_master.id', 'DESC');
		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Raw Material retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Material found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}

	public function get_all_raw_material_ddl_api()
	{
		// 1. Detailed Raw Materials
		$this->db->select('tbl_rm_master.id, tbl_rm_master.rm_name, tbl_rm_master.alias, tbl_rm_master.code, tbl_rm_master.mfi, tbl_rm_type.type as rm_type, tbl_make.make, tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_master.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_make', 'tbl_rm_master.make_id = tbl_make.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		$this->db->order_by('tbl_rm_master.rm_name', 'ASC');
		$raw_materials = $this->db->get()->result();

		// 2. Master Batches
		$this->db->select('id, name');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('name', 'ASC');
		$master_batches = $this->db->get('tbl_mb_master')->result();

		// 3. RM Types (Categories)
		$this->db->select('id, type');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('type', 'ASC');
		$rm_types = $this->db->get('tbl_rm_type')->result();

		// 4. Makes
		$this->db->select('id, make');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('make', 'ASC');
		$makes = $this->db->get('tbl_make')->result();

		// 5. UOMs
		$this->db->select('id, uom_name');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('uom_name', 'ASC');
		$uoms = $this->db->get('tbl_uom_master')->result();

		// 6. Article Groups
		$this->db->select('id, group_of_article');
		$this->db->where('is_deleted', '0');
		$this->db->order_by('group_of_article', 'ASC');
		$article_groups = $this->db->get('tbl_group_of_article')->result();

		echo json_encode([
			'status'         => 'true',
			'message'        => 'DDL data fetched successfully.',
			'raw_materials'  => $raw_materials ?: [],
			'master_batches' => $master_batches ?: [],
			'rm_types'       => $rm_types ?: [],
			'makes'          => $makes ?: [],
			'uoms'           => $uoms ?: [],
			'article_groups' => $article_groups ?: []
		]);
	}
	public function get_all_master_batch_api()
	{
		$this->db->select(['id', 'name']);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_mb_master');
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Master Batch retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Master Batch found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_reject_material_api()
	{
		$this->db->select(['id', 'rm_name']);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_rm_rejection');
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Rm Rejection retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No RM Rejection found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function set_production_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$article_group_id = '';
		if (!empty($request['article_group_id'])) {
			if (is_array($request['article_group_id'])) {
				$article_group_id = implode(',', array_filter(array_map('trim', $request['article_group_id'])));
			} elseif (is_string($request['article_group_id'])) {
				$article_group_id = implode(',', array_filter(array_map('trim', explode(',', $request['article_group_id']))));
			}
		}
		$article_id = '';
		if (!empty($request['article_id'])) {
			if (is_array($request['article_id'])) {
				$article_id = implode(',', array_unique(array_map('trim', $request['article_id'])));
			} elseif (is_string($request['article_id'])) {
				$article_id = implode(',', array_unique(array_filter(array_map('trim', explode(',', $request['article_id'])))));
			}
		}

		$raw_materials = '';
		if (!empty($request['raw_material_id'])) {
			if (is_array($request['raw_material_id'])) {
				$raw_materials = implode(',', array_filter(array_map('trim', $request['raw_material_id'])));
			} elseif (is_string($request['raw_material_id'])) {
				$raw_materials = implode(',', array_filter(array_map('trim', explode(',', $request['raw_material_id']))));
			}
		}
		$master_batch_id = '';
		if (!empty($request['master_batch_id'])) {
			if (is_array($request['master_batch_id'])) {
				$master_batch_id = implode(',', array_filter(array_map('trim', $request['master_batch_id'])));
			} elseif (is_string($request['master_batch_id'])) {
				$master_batch_id = implode(',', array_filter(array_map('trim', explode(',', $request['master_batch_id']))));
			}
		}
		$rejection_id = '';
		if (!empty($request['rejection_id'])) {
			if (is_array($request['rejection_id'])) {
				$rejection_id = implode(',', array_filter(array_map('trim', $request['rejection_id'])));
			} elseif (is_string($request['rejection_id'])) {
				$rejection_id = implode(',', array_filter(array_map('trim', explode(',', $request['rejection_id']))));
			}
		}
		$day_shift_operators = '';
		if (!empty($request['day_shift_operators'])) {
			if (is_array($request['day_shift_operators'])) {
				$day_shift_operators = implode(',', array_filter(array_map('trim', $request['day_shift_operators'])));
			} elseif (is_string($request['day_shift_operators'])) {
				$day_shift_operators = implode(',', array_filter(array_map('trim', explode(',', $request['day_shift_operators']))));
			}
		}

		$night_shift_operators = '';
		if (!empty($request['night_shift_operators'])) {
			if (is_array($request['night_shift_operators'])) {
				$night_shift_operators = implode(',', array_filter(array_map('trim', $request['night_shift_operators'])));
			} elseif (is_string($request['night_shift_operators'])) {
				$night_shift_operators = implode(',', array_filter(array_map('trim', explode(',', $request['night_shift_operators']))));
			}
		}

		// Collect operator names
		$all_op_ids = array_filter(array_unique(array_merge(
			!empty($day_shift_operators) ? explode(',', $day_shift_operators) : [],
			!empty($night_shift_operators) ? explode(',', $night_shift_operators) : []
		)));
		$operator_name = '';
		if (!empty($all_op_ids)) {
			$this->db->select('first_name');
			$this->db->where_in('id', $all_op_ids);
			$op_results = $this->db->get('user_data')->result();
			$operator_name = implode(', ', array_map(function($o) { return $o->first_name; }, $op_results));
		}

		$data = [
			'production_date' => !empty($request['production_date']) ? date("Y-m-d", strtotime($request['production_date'])) : null,
			'machine_id' => isset($request['machine_id']) ? $request['machine_id'] : null,
			'article_group_id' => $article_group_id,
			'article_id' => $article_id,
			'raw_material_id' => $raw_materials,
			'master_batch_id' => $master_batch_id,
			'rejection_id' => $rejection_id,
			'day_shift_operators' => $day_shift_operators,
			'night_shift_operators' => $night_shift_operators,
			'operator_name' => $operator_name,
		];

		if (empty($request['id'])) {
			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_production_report', $data);
			$response = ['status' => true, 'message' => 'Production details added successfully'];
		} else {
			$this->db->where('id', $request['id']);
			$this->db->update('tbl_production_report', $data);
			$response = ['status' => true, 'message' => 'Production details updated successfully'];
		}

		echo json_encode($response);
	}
	public function get_all_production_details_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$machine_id = isset($request['machine_id']) ? trim($request['machine_id']) : '';
		$article_group_id = isset($request['article_group_id']) ? trim($request['article_group_id']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';


		$this->db->select("
			pr.id,
			pr.production_date,
			pr.day_shift_operators,
			pr.night_shift_operators,
			pr.operator_name,
			mm.machine_name,
			pr.remark,
			GROUP_CONCAT(DISTINCT ga.group_of_article ORDER BY FIND_IN_SET(ga.id, pr.article_group_id)) AS group_of_article,
			GROUP_CONCAT(DISTINCT ga.id ORDER BY FIND_IN_SET(ga.id, pr.article_group_id)) AS article_group_ids,

			GROUP_CONCAT(DISTINCT mp.article_name ORDER BY FIND_IN_SET(mp.id, pr.article_id)) AS articles,
			GROUP_CONCAT(DISTINCT mp.id ORDER BY FIND_IN_SET(mp.id, pr.article_id)) AS article_ids,

			GROUP_CONCAT(DISTINCT rm.rm_name ORDER BY FIND_IN_SET(rm.id, pr.raw_material_id)) AS raw_materials,
			GROUP_CONCAT(DISTINCT rm.id ORDER BY FIND_IN_SET(rm.id, pr.raw_material_id)) AS raw_material_ids,

			GROUP_CONCAT(DISTINCT mb.name ORDER BY FIND_IN_SET(mb.id, pr.master_batch_id)) AS master_batches,
			GROUP_CONCAT(DISTINCT mb.id ORDER BY FIND_IN_SET(mb.id, pr.master_batch_id)) AS master_batch_ids,

			GROUP_CONCAT(DISTINCT rj.rm_name ORDER BY FIND_IN_SET(rj.id, pr.rejection_id)) AS rejections,
			GROUP_CONCAT(DISTINCT rj.id ORDER BY FIND_IN_SET(rj.id, pr.rejection_id)) AS rejection_ids
		");

		$this->db->from('tbl_production_report pr');
		$this->db->join('tbl_machine_master mm', 'pr.machine_id = mm.id', 'left');
		$this->db->join('tbl_group_of_article ga', 'FIND_IN_SET(ga.id, pr.article_group_id)', 'left');
		$this->db->join('tbl_mould_parts mp', 'FIND_IN_SET(mp.id, pr.article_id)', 'left');
		$this->db->join('tbl_rm_master rm', 'FIND_IN_SET(rm.id, pr.raw_material_id)', 'left');
		$this->db->join('tbl_mb_master mb', 'FIND_IN_SET(mb.id, pr.master_batch_id)', 'left');
		$this->db->join('tbl_rm_rejection rj', 'FIND_IN_SET(rj.id, pr.rejection_id)', 'left');

		$this->db->where('pr.is_deleted', '0');
		if (!empty($machine_id)) {
			$this->db->where('pr.machine_id', $machine_id);
		}
		if (!empty($article_group_id)) {
			$this->db->where("FIND_IN_SET('$article_group_id', pr.article_group_id) >", 0);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('pr.production_date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('pr.production_date', $search);
			$this->db->or_like('mm.machine_name', $search);
			$this->db->or_like('mp.article_name', $search);
			$this->db->or_like('rm.rm_name', $search);
			$this->db->or_like('mb.name', $search);
			$this->db->or_like('rj.rm_name', $search);
			$this->db->or_like('ga.group_of_article', $search);
			$this->db->group_end();
		}

		$this->db->group_by('pr.id');
		$this->db->order_by('pr.id', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No data found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	public function set_production_remark_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$production_id = $request['id'];
		$data = array(
			'remark' => $request['remark'],
		);
		$this->db->where('id', $production_id);
		$this->db->update('tbl_production_report', $data);
		echo json_encode(['status' => 'success', 'message' => 'Remark saved']);
	}
	public function set_article_production_details_api()
	{
		$input = json_decode(file_get_contents("php://input"), true);

		if (!isset($input['production_id']) || !isset($input['article_id']) || !isset($input['qty_data']) || !isset($input['weight_data'])) {
			echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
			return;
		}
		$qty_data = $input['qty_data'];
		$weight_data = $input['weight_data'];

		$data = array_merge($qty_data, $weight_data);

		$data['approved_qty'] = array_sum(array_map('floatval', $qty_data));

		// Only count weight slots that are actually filled (non-empty, numeric, and > 0)
		$validWeights = array_filter($weight_data, function ($val) {
			return is_numeric(trim((string)$val)) && (float)trim((string)$val) > 0;
		});
		$data['average_qty'] = count($validWeights) > 0
			? array_sum(array_map('floatval', $validWeights)) / count($validWeights)
			: 0;

		$data['status'] = $input['status'] ?? '';
		$data['remark'] = $input['remark'] ?? '';
		$data['production_id'] = $input['production_id'];
		$data['article_id'] = $input['article_id'];
		$data['status'] = $input['status'] ?? '';

		$now = date('Y-m-d H:i:s');

		$log_data = $data;
		$log_data['created_on'] = $now;
		$this->db->insert('tbl_article_production_details_logs', $log_data);

		$this->db->where('production_id', $data['production_id']);
		$this->db->where('article_id', $data['article_id']);
		$existing = $this->db->get('tbl_article_production_details');

		$this->db->where('id', $data['production_id']);
		$existing_production = $this->db->get('tbl_production_report')->row();

		$this->db->where('id', $existing_production->machine_id);
		$existing_machine = $this->db->get('tbl_machine_master')->row();
		$data['production_date'] = $existing_production->production_date ?? null;
		$data['machine_id'] = $existing_production->machine_id;
		$data['plant_id'] = $existing_machine->plant_id;

		if ($existing->num_rows() > 0) {
			$this->db->where('id', $existing->row()->id);
			$data['updated_on'] = $now;
			$this->db->update('tbl_article_production_details', $data);
		} else {
			$data['created_on'] = $now;
			$this->db->insert('tbl_article_production_details', $data);
		}
		if ($data['status'] == '0') {
			$total_qty = $data['approved_qty'];
			$opening_stock = 0;
			$stock_data = array(
				'article_id' => $data['article_id'],
				'total_quantity' => $total_qty,
				'plant_id' => $data['plant_id'] ?? null,
			);
			$this->db->where('article_id', $data['article_id']);
			$this->db->where('plant_id', $data['plant_id']);
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
				'plant_id' => $data['plant_id'] ?? null,
				'production_id' => $data['production_id'],
				'is_inward_outward' => '5',
				'date' => date('Y-m-d'),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_raw_material_stock_report_history', $stock_history_data);
			$this->db->select('article_name');
			$this->db->where('id', $data['article_id']);
			$article_name = $this->db->get('tbl_mould_parts')->row()->article_name ?? '';

			$this->db->where('id', $input['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();
			// Notification Work when procution completed
			$title = 'Productuon Report Updates';
			$description = $article_name . ' Productuon Completed at ' . date('d-m-Y H:i:s') . ' updated by ' .
				$employee_data->first_name;
			$landing_page = 'plant_list';
			$notification_according = '1';//means according department
			$departments = [11, 25, 23]; // 11 = Accounts Department, 25 = admin  Department, 23 = sales Department
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $article_name,
				'plant_id' => $data['plant_id'] ?? null,
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(52, $title, $description, $landing_page, $notification_according, $data['plant_id'] ?? null);
		}

		echo json_encode(['status' => 'true', 'message' => 'Data saved successfully']);
	}
	public function set_article_slot_remark_api()
	{
		$input = json_decode(file_get_contents("php://input"), true);

		if (
			!isset($input['production_id']) ||
			!isset($input['article_id']) ||
			!isset($input['time_slot']) ||
			!isset($input['remark'])
		) {
			echo json_encode(['status' => 'false', 'message' => 'Missing required fields']);
			return;
		}

		$production_id = $input['production_id'];
		$article_id = $input['article_id'];
		$time_slot = $input['time_slot'];
		$remark = $input['remark'];

		$column_name = "qty_" . $time_slot . "_remark";

		$data = [
			$column_name => $remark,
			'updated_on' => date('Y-m-d H:i:s')
		];

		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$existing = $this->db->get('tbl_article_production_details');

		if ($existing->num_rows() > 0) {
			$this->db->where('id', $existing->row()->id);
			$this->db->update('tbl_article_production_details', $data);
		} else {

			$data['created_on'] = date('Y-m-d H:i:s');
			$this->db->insert('tbl_article_production_details', $data);
		}

		echo json_encode(['status' => 'true', 'message' => 'Slot remark saved']);
	}

	public function get_article_production_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$production_id = $request['production_id'];
		$article_id = $request['article_id'];
		$this->db->where('is_deleted', '0');
		$this->db->where('production_id', $production_id);
		$this->db->where('article_id', $article_id);
		$result = $this->db->get('tbl_article_production_details')->row();
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Article retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No article found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_production_details_log_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!isset($request['production_id']) || !isset($request['article_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Missing required parameters.',
				'data' => []
			]);
			return;
		}
		$production_id = $request['production_id'];
		$article_id = $request['article_id'];

		$this->db->select('tbl_article_production_details_logs.*, tbl_mould_parts.article_name');
		$this->db->from('tbl_article_production_details_logs');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_article_production_details_logs.article_id', 'left');
		$this->db->where('tbl_article_production_details_logs.production_id', $production_id);
		$this->db->where('tbl_article_production_details_logs.article_id', $article_id);
		$this->db->where('tbl_article_production_details_logs.is_deleted', '0');
		$this->db->order_by('tbl_article_production_details_logs.id', 'DESC');

		$result = $this->db->get();
		$result = $result->result();

		if (!empty($result)) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Production log retrieved successfully.',
				'data' => $result
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No production log found.',
				'data' => []
			]);
		}
	}

	public function get_machine_according_plant_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$this->db->where('is_deleted', '0');
		$this->db->where('plant_id', $request['plant_id']);
		$this->db->where('status', '1');
		$result = $this->db->get('tbl_machine_master');
		$result = $result->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
		} else {
			$json_arr['status'] = 'false';
		}
		$json_arr['message'] = 'success';
		$json_arr['data'] = $result;
		echo json_encode($json_arr);
	}
	public function get_all_scheduled_according_date_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		if (empty($request['month']) || empty($request['plant_id']) || empty($request['machine_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Required parameters missing',
				'data' => []
			]);
			return;
		}
		$date = $request['month'];
		$plant_id = $request['plant_id'];
		$machine_id = $request['machine_id'];

		if (!strtotime($date)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid date format',
				'data' => []
			]);
			return;
		}

		$year = date('Y', strtotime($date));
		$month_num = date('m', strtotime($date));

		$this->db->select('tbl_production_schedules.*, tbl_mould_parts.article_name');
		$this->db->from('tbl_production_schedules');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id', 'left');
		$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		$this->db->where('YEAR(production_schedule_start_date)', $year);
		$this->db->where('MONTH(production_schedule_start_date)', $month_num);
		$this->db->where('tbl_production_schedules.is_deleted', '0');

		$query = $this->db->get();
		$schedules = $query->result_array();

		// Group by date
		$grouped_by_date = [];
		foreach ($schedules as $schedule) {
			$date_key = $schedule['date'];
			if (!isset($grouped_by_date[$date_key])) {
				$grouped_by_date[$date_key] = [];
			}
			$grouped_by_date[$date_key][] = $schedule;
		}

		$final_data = [];

		foreach ($grouped_by_date as $date => $day_schedules) {
			$total_minutes = 0;

			// First calculate total duration
			foreach ($day_schedules as $item) {
				$start = new DateTime($item['production_schedule_start_date'] . ' ' . $item['production_schedule_start_time']);
				$end = new DateTime($item['production_schedule_end_date'] . ' ' . $item['production_schedule_end_time']);

				if ($end > $start) {
					$total_minutes += ($end->getTimestamp() - $start->getTimestamp()) / 60;
				}
			}

			// Decide backgroundColor for all items of this date
			$backgroundColor = 'red';
			if ($total_minutes >= 1439 && $total_minutes <= 1441) {
				$backgroundColor = 'green';
			} elseif ($total_minutes > 0) {
				$backgroundColor = 'yellow';
			}

			// Now assign color to each schedule item
			foreach ($day_schedules as $item) {
				$final_data[] = [
					'id' => $item['id'],
					'order_id' => $item['order_id'],
					'article_name' => $item['article_name'],
					'qty' => $item['qty'],
					'start' => $item['production_schedule_start_date'] . 'T' . $item['production_schedule_start_time'],
					'end' => $item['production_schedule_end_date'] . 'T' . $item['production_schedule_end_time'],
					'backgroundColor' => $backgroundColor,
					'date' => $item['date']
				];
			}
		}
		$json_arr = [
			'status' => !empty($final_data) ? 'true' : 'false',
			'message' => 'success',
			'data' => $final_data
		];

		echo json_encode($json_arr);
	}

	public function set_production_scheduled_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request) {
			echo json_encode(['error' => 'Invalid or missing JSON data']);
			return;
		}

		$schedule_id = $request['schedule_id'];

		$date = !empty($request['date']) ? $request['date'] : '';
		if (!empty($date)) {
			$input_date = date('Y-m-d', strtotime($date));
			$today = date('Y-m-d');

			if ($input_date < $today) {
				echo json_encode([
					'status' => false,
					'message' => 'Invalid date. Past dates are not allowed.'
				]);
				return;
			}
		}
		$color_ids = isset($request['color_id'])
			? (is_array($request['color_id']) ? implode(',', $request['color_id']) : $request['color_id']) : '';

		$raw_materials = isset($request['raw_materials'])
			? (is_array($request['raw_materials']) ? implode(',', $request['raw_materials']) : $request['raw_materials']) : '';

		$data = [];

		if (!empty($request['start_date']))
			$data['production_schedule_start_date'] = $request['start_date'];
		if (!empty($request['end_date']))
			$data['production_schedule_end_date'] = $request['end_date'];
		if (!empty($request['start_time']))
			$data['production_schedule_start_time'] = $request['start_time'];
		if (!empty($request['end_time']))
			$data['production_schedule_end_time'] = $request['end_time'];
		if (!empty($request['article_group_id']))
			$data['article_group_id'] = $request['article_group_id'];
		if (!empty($request['article_id']))
			$data['article_id'] = $request['article_id'];
		if (!empty($color_ids))
			$data['color_id'] = $color_ids;
		if (!empty($raw_materials))
			$data['raw_materials'] = $raw_materials;
		if (!empty($request['qty']))
			$data['qty'] = $request['qty'];
		if (!empty($date))
			$data['date'] = $date;
		if (!empty($request['plant_id']))
			$data['plant_id'] = $request['plant_id'];
		if (!empty($request['machine_id']))
			$data['machine_id'] = $request['machine_id'];

		$data['updated_on'] = date("Y-m-d H:i:s");


		$this->db->where('plant_id', $data['plant_id']);
		$this->db->where('machine_id', $data['machine_id']);
		$this->db->where('production_schedule_start_date', $data['production_schedule_start_date']);
		$this->db->where('production_schedule_end_date', $data['production_schedule_end_date']);
		$this->db->where('is_deleted', '0');
		if (!empty($schedule_id)) {
			$this->db->where('id !=', $schedule_id);
		}
		$existing_schedules = $this->db->get('tbl_production_schedules')->result();

		$new_start = strtotime($data['production_schedule_start_date'] . ' ' . $data['production_schedule_start_time']);
		$new_end = strtotime($data['production_schedule_end_date'] . ' ' . $data['production_schedule_end_time']);
		$is_overlapping = '0';
		foreach ($existing_schedules as $slot) {
			$exist_start = strtotime($slot->production_schedule_start_date . ' ' . $slot->production_schedule_start_time);
			$exist_end = strtotime($slot->production_schedule_end_date . ' ' . $slot->production_schedule_end_time);

			if ($new_start < $exist_end && $new_end > $exist_start) {
				$is_overlapping = '1';
				break;
			}
		}
		if ($is_overlapping === '1') {
			echo json_encode(['status' => false, 'message' => 'Overlapping schedule exists for this machine and plant']);
			return;
		}
		if (!empty($schedule_id)) {
			$this->db->where('id', $schedule_id);
			$this->db->update('tbl_production_schedules', $data);

			$this->db->where('id', $schedule_id);
			$this->db->where('is_deleted', '0');
			$res = $this->db->get('tbl_production_schedules')->row();

			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();

			// Notification Work
			$title = 'Production Schedule Updates';
			$description = 'Production Schedule Updated ' . $res->order_id . ' by ' .
				$employee_data->first_name;
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 24= store Dept 25= admin Dept 
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $res->order_id,
				'plant_id' => $request['my_plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(54, $title, $description, $landing_page, $notification_according, $request['my_plant_id']);

			$json_arr = [
				'status' => true,
				'message' => 'Schedule updated successfully'
			];
		} else {
			$data['created_on'] = $data['updated_on'];
			unset($data['updated_on']);

			$this->db->insert('tbl_production_schedules', $data);
			$schedule_insert_id = $this->db->insert_id();

			$order_id = 'PRO-' . str_pad($schedule_insert_id, 2, '0', STR_PAD_LEFT);

			$process_data = array(
				'task_id' => $order_id,
				'order_department' => 2,
				'employee_id' => $request['employee_id'] ?? null,
				'date' => date('Y-m-d'),
				'plant_id' => $request['my_plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);

			$this->db->insert('tbl_auto_task_list', $process_data);

			$last_insert_id = $this->db->insert_id();
			$department_id = 3;
			$log_data = array(
				'task_id' => $last_insert_id,
				'task_status' => '1',
				'details_of_task' => 'Production Scheduled',
				'last_updated_date' => date('Y-m-d'),
				'last_updated_by' => $request['employee_id'],
				'plant_id' => $request['my_plant_id'],
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
				'plant_id' => $request['my_plant_id'],
				'last_updated_by' => $request['employee_id'],
				'created_on' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('tbl_auto_task_list_history', $history_data);

			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();

			// Notification Work for Production Schedile
			$title = 'Production Schedule';
			$description = 'Production Schedule Created ' . $order_id . ' by ' .
				$employee_data->first_name;
			$landing_page = 'auto_order_list';
			$notification_according = '1';//means according department
			$departments = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 24= store Dept 25= admin Dept 
			$departments_str = implode(',', $departments);
			$notification_data = array(
				'notification_title' => $title,
				'notification_description' => $description,
				'notification_department' => $departments_str,
				'landing_page' => $landing_page,
				'order_id' => $order_id,
				'plant_id' => $request['my_plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(54, $title, $description, $landing_page, $notification_according, $request['my_plant_id']);

			$json_arr = [
				'status' => true,
				'message' => 'Schedule created successfully',
				'data' => [
					'id' => $schedule_insert_id
				]
			];
		}
		echo json_encode($json_arr);
	}

	public function get_single_production_schedule_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request) {
			echo json_encode(['error' => 'Invalid or missing JSON data']);
			return;
		}

		$schedule_id = isset($request['schedule_id']) ? $request['schedule_id'] : null;

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

		$this->db->where('tbl_production_schedules.id', $schedule_id);
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$this->db->group_by('tbl_production_schedules.id');

		$result = $this->db->get()->row();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Data retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	public function get_all_task_history_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['task_id']) || empty($request['task_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid or missing task_id',
				'data' => []
			]);
			return;
		}
		$task_id = $request['task_id'];
		$auto_or_manual = $request['auto_or_manual'];
		if ($auto_or_manual == '1') {
			$this->db->select('
				tbl_auto_task_list_history.*, 
				tbl_krivisha_department.department, 
				assign_to_table.first_name,
				last_updated_user.first_name as last_updated_by
			');
			$this->db->from('tbl_auto_task_list_history');
			$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list_history.department_id', 'left');
			$this->db->join('user_data as assign_to_table', 'assign_to_table.id = tbl_auto_task_list_history.assign_to_id', 'left');
			$this->db->join('user_data as last_updated_user', 'last_updated_user.id = tbl_auto_task_list_history.last_updated_by', 'left');
			$this->db->where('tbl_auto_task_list_history.is_deleted', '0');
			$this->db->where('tbl_auto_task_list_history.task_id', $task_id);
			$this->db->order_by('tbl_auto_task_list_history.id', 'DESC');

			$query = $this->db->get();
		} else {
			$this->db->select('
				tbl_manual_task_history.*, 
				tbl_krivisha_department.department, 
				assign_to_table.first_name,
				last_updated_user.first_name as last_updated_by

			');
			$this->db->from('tbl_manual_task_history');
			$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_manual_task_history.department_id', 'left');
			$this->db->join('user_data as assign_to_table', 'assign_to_table.id = tbl_manual_task_history.assign_to_id', 'left');
			$this->db->join('user_data as last_updated_user', 'last_updated_user.id = tbl_manual_task_history.last_updated_by', 'left');
			$this->db->where('tbl_manual_task_history.is_deleted', '0');
			$this->db->where('tbl_manual_task_history.task_id', $task_id);
			$this->db->order_by('tbl_manual_task_history.id', 'DESC');

			$query = $this->db->get();
		}
		if ($query->num_rows() > 0) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Task history retrieved successfully.',
				'data' => $query->result()
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No task history found.',
				'data' => []
			]);
		}
	}
	public function set_side_visit_data_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$update_id = isset($request['visit_id']) ? $request['visit_id'] : '';

		$new_or_exsist = $request['party_option_id'];
		if ($new_or_exsist != '') {
			$party_type = isset($request['party_type'])
				? (is_array($request['party_type']) ? implode(',', $request['party_type']) : $request['party_type']) : '';

			if ($new_or_exsist == '2') {
				$data = array(
					'party_name' => $request['party_name'],
					'address' => $request['address'],
					'city_id' => $request['city_id'],
					'nature_of_business_id' => $request['nature_of_business_id'],
					'attending_salesperson_id' => $request['sales_person_id'],
					'mobile' => $request['mobile'],
					'plant_id' => $request['plant_id'],
					'created_on' => date('Y-m-d H:i:s')
				);
				if (!empty($party_type))
					$data['party_type'] = $party_type;
				$this->db->insert('tbl_customers', $data);
				$last_insert_id = $this->db->insert_id();
			}
			$party_id = $new_or_exsist == '1' ? $request['party_id'] : $last_insert_id;
			$this->db->where('is_deleted', '0');
			$this->db->where('id', $request['sales_person_id']);
			$admin_result = $this->db->get('user_data')->row();

			$data = array(
				'sales_person_id' => $request['sales_person_id'],
				'party_id' => $party_id,
				'plant_id' => $admin_result->plant_id,
				'type_of_visit' => $request['type_of_site'],
				'source_of_visit' => $request['source_of_visit'],
				'meeting_priority' => $request['meeting_priority'],
				'date' => $request['date'],
				'time' => $request['time'],
				'employee_id' => $request['employee_id'],
			);
			if ($update_id == '') {
				$data['created_on'] = date('Y-m-d H:i:s');
				$data['visit_request_id'] = $this->Api_model->get_system_generated_id();
				$this->db->insert('tbl_side_visit_details', $data);
				$json_arr = [
					'status' => 'true',
					'message' => 'Data added Successfully'
				];
			} else {
				$this->db->where('id', $update_id);
				$this->db->update('tbl_side_visit_details', $data);

				$json_arr = [
					'status' => 'true',
					'message' => 'Data updated Successfully'
				];
			}
		} else {
			$recheduled_data = array(
				'reschedule_reason' => $request['reschedule_reason'],
				'customer_infromed' => $request['customer_infromed'],
				'date' => $request['reschedule_date'],
				'time' => $request['reschedule_time'],
				'updated_on' => date('Y-m-d H:i:s'),
			);
			$this->db->where('id', $update_id);
			$this->db->update('tbl_side_visit_details', $recheduled_data);
			$json_arr = [
				'status' => 'true',
				'message' => 'Data rescheduled Successfully'
			];
		}
		echo json_encode($json_arr);
	}
	private function get_system_generated_id()
	{
		$this->db->select('visit_request_id');
		$this->db->from('tbl_side_visit_details');
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		$last = $this->db->get()->row();

		if ($last && !empty($last->visit_request_id)) {
			$last_num = (int) filter_var($last->visit_request_id, FILTER_SANITIZE_NUMBER_INT);
			$new_num = $last_num + 1;
		} else {
			$new_num = 001;
		}
		return "VisitTra#" . $new_num;
	}

	public function get_all_side_visit_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$date_filter = isset($request['date']) ? trim($request['date']) : '';
		$sales_person_id = isset($request['sales_person_id']) ? trim($request['sales_person_id']) : '';
		$sales_id_for_party = isset($request['sales_id_for_party']) ? trim($request['sales_id_for_party']) : '';
		$type_of_visit_filter = isset($request['type_of_visit']) ? trim($request['type_of_visit']) : '';
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';
		$employee_id = isset($request['employee_id']) ? trim($request['employee_id']) : '';

		$this->db->where('is_deleted', '0');
		$this->db->where('id', $employee_id);
		$admin_result = $this->db->get('user_data')->row();
		if (empty($date_filter)) {
			$date_filter = date('Y-m-d');
		}
		$this->db->select('tbl_side_visit_details.*, tbl_customers.party_name,sales_person_table.first_name as sales_person , visit_created_table.first_name as visit_created_name');
		$this->db->from('tbl_side_visit_details');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_side_visit_details.party_id', 'left');
		$this->db->join('user_data as sales_person_table', 'sales_person_table.id = tbl_side_visit_details.sales_person_id', 'left');
		$this->db->join('user_data as visit_created_table', 'visit_created_table.id = tbl_side_visit_details.employee_id', 'left');
		$this->db->where('tbl_side_visit_details.is_deleted', '0');

		if (!empty($plant_id) && $admin_result->is_admin != '1') {
			// $this->db->where('tbl_side_visit_details.plant_id', $plant_id);
			$this->db->where('tbl_side_visit_details.sales_person_id', $sales_person_id);
		}
		if (!empty($sales_id_for_party)) {
			$this->db->where('tbl_side_visit_details.sales_person_id', $sales_id_for_party);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_side_visit_details.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_side_visit_details.date) <=', date('Y-m-d', strtotime($to_date)));
		} else if (!empty($from_date)) {
			$this->db->where('DATE(tbl_side_visit_details.date)', date('Y-m-d', strtotime($from_date)));
		} else {
			$this->db->where('DATE(tbl_side_visit_details.date)', $date_filter);
		}
		if (!empty($type_of_visit_filter)) {
			$this->db->where('tbl_side_visit_details.type_of_visit', $type_of_visit_filter);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_side_visit_details.party_id', $party_id);
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_of_visit = [
				'physical visit' => '1',
				'telephonic meet' => '2',
				'supervisor/ sales head connect' => '3',
			];
			$visit_type = false;
			foreach ($type_of_visit as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$visit_type = $value;
					break;
				}
			}
			$this->db->group_start();
			$this->db->like('tbl_side_visit_details.date', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_side_visit_details.time', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			if ($visit_type !== false) {
				$this->db->or_where('tbl_side_visit_details.type_of_visit', $visit_type);
			} else {
				$this->db->or_like('tbl_side_visit_details.type_of_visit', $search);
			}
			$this->db->or_like('tbl_side_visit_details.source_of_visit', $search);
			$this->db->group_end();
		}
		$this->db->order_by('tbl_side_visit_details.id', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}


	public function set_start_and_end_site_visit_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['visit_id'])) {
			echo json_encode(['status' => 'false', 'message' => 'Missing required fields']);
			return;
		}

		$update_id = isset($request['visit_id']) ? $request['visit_id'] : '';
		$images = $request['images'];

		$stored_images = [];

		if (!empty($images) && is_array($images)) {
			foreach ($images as $image_data) {
				$data = base64_decode($image_data, true); // Second argument true to check if it is valid base64

				if ($data !== false) {
					$image_info = @getimagesizefromstring($data);

					if ($image_info) {
						$ext = strtolower($image_info['mime']);
						$ext = str_replace('image/', '', $ext);

						if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
							$folder_save = "assets/images/";
							$key = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
							$stored_image_name = 'meeting_proof_' . $key . '.' . $ext;
							$file_save = $folder_save . $stored_image_name;

							if (!is_dir($folder_save)) {
								mkdir($folder_save, 0755, true);
							}

							if (file_put_contents($file_save, $data)) {
								$stored_images[] = $file_save;
							}
						}
					}
				}
			}
		}
		$meeting_proof_paths = !empty($stored_images) ? implode(',', $stored_images) : '';
		$data = array(
			'status_of_visit' => $request['status_id'],
			'latitude' => $request['latitude'],
			'longitude' => $request['longitude'],
			'meeting_proof' => $meeting_proof_paths,
			'remark' => $request['comments'],
			'updated_on' => date('Y-m-d H:i:s'),
		);
		// echo"<pre>";
		// print_r($update_id);exit;
		if (!empty($update_id)) {

			$this->db->where('id', $update_id);
			$this->db->update('tbl_side_visit_details', $data);
			$json_arr = [
				'status' => 'true',
				'message' => 'Site visit updated successfully'
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'Invalid id'
			];
		}
		echo json_encode($json_arr);
	}
	public function get_previlege_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$id = $request['id'];
		if (empty($id)) {
			echo json_encode(['status' => 'false', 'message' => 'ID is required']);
			return;
		}
		$this->db->where('id', $id);
		$check_is_admin = $this->db->get('user_data')->row();
		if ($check_is_admin) {
			if ($check_is_admin->is_admin == '1') {
				$json_arr = [
					'status' => 'true',
					'message' => 'Previlege retrieved successfully.',
					'data' => ['is_admin' => '1']
				];
				echo json_encode($json_arr);
				return;
			}
		}
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
		if (!empty($link)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Previlege retrieved successfully.',
				'data' => $link
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No previlege found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_sales_person_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$sales_person_id = $request['sales_id_for_party'];
		$this->db->select('user_data.id,user_data.first_name,user_data.emp_id,user_data.department_id,tbl_krivisha_department.department');
		$this->db->where('user_data.is_deleted', '0');
		$this->db->join('tbl_krivisha_department', 'user_data.department_id = tbl_krivisha_department.id', 'inner');
		$this->db->where('tbl_krivisha_department.department', 'SALES');
		if (!empty($sales_person_id)) {
			$this->db->where('user_data.id', $sales_person_id);
		}
		$result = $this->db->get('user_data')->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Sales person retrieved successfully.';
			$json_arr['data'] = $result;

		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No sales person found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function check_existing_party_name_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$party_name = $request['party_name'];
		$id = $request['id'];
		if (empty($party_name)) {
			echo json_encode(['status' => 'false', 'message' => 'Party Name is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('party_name', $party_name);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_customers')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Party Name already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Party Name is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function check_existing_mobile_number_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$mobile = $request['mobile'];
		$id = $request['id'];
		if (empty($mobile)) {
			echo json_encode(['status' => 'false', 'message' => 'Mobile number is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('mobile', $mobile);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_customers')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'Mobile number already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Mobile number is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_all_nature_of_business_api()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_nature_of_business');
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Data Retrived Successfully.';
			$json_arr['data'] = $result->result();
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Data Found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_all_citys_api()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_location_master');
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Data Retrived Successfully.';
			$json_arr['data'] = $result->result();
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Data Found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_site_visit_party_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$sales_person_id = $request['sales_person_id'];
		$this->db->select('tbl_customers.*');
		$this->db->where('attending_salesperson_id', $sales_person_id);
		$this->db->where('is_deleted', '0');

		$party = $this->db->get('tbl_customers')->result();
		if (!empty($party)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'party retrieved successfully.';
			$json_arr['data'] = $party;

		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No party found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}

	///////////////////////////////Printing Apis ///////////////////////////////////

	public function get_printing_order_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$article_id = isset($request['article_id']) ? trim($request['article_id']) : '';
		$group_of_article_id = isset($request['group_of_article_id']) ? trim($request['group_of_article_id']) : '';
		$brand_id = isset($request['brand_id']) ? trim($request['brand_id']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';

		$this->db->select('
        tbl_order_sub_details.*,
        tbl_group_of_article.group_of_article,
        tbl_brand_master.brand_name,
        tbl_mould_parts.article_name,
		tbl_customers.party_name
    ');
		$this->db->from('tbl_order_sub_details');
		$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_order_sub_details.party_id = tbl_customers.id', 'left');

		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		$this->db->where('tbl_order_sub_details.order_department_status', '2');
		$this->db->where('tbl_order_sub_details.order_status', '7'); // Assuming '7' is the code for 'In Printing'
		if (!empty($article_id)) {
			$this->db->where('tbl_order_sub_details.article_id', $article_id);
		}
		if (!empty($group_of_article_id)) {
			$this->db->where('tbl_order_sub_details.group_of_article_id', $group_of_article_id);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_order_sub_details.party_id', $party_id);
		}
		if (!empty($brand_id)) {
			$this->db->where('tbl_order_sub_details.brand_type_id', $brand_id);
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_order_sub_details.order_status', $order_status);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_order_sub_details.order_date) >=', date("Y-m-d", strtotime(trim($from_date))));
			$this->db->where('DATE(tbl_order_sub_details.order_date) <=', date("Y-m-d", strtotime(trim($to_date))));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_order_sub_details.order_date)', date("Y-m-d", strtotime(trim($from_date))));
		}
		$status_map = [
			'pla' => '1',
			'plain' => '1',
			'pri' => '2',
			'printing' => '2'
		];
		$order_status_map = [
			'pen' => '7',
			'pending' => '7',
			'prin' => '7',
			'printing' => '7',
			'inproc' => '7',
			'inpr' => '7',
			'Inprocess' => '7'
		];
		if (isset($order_status_map[strtolower($search)])) {
			$order_status_value = $order_status_map[strtolower($search)];
		} else {
			$order_status_value = null;
		}

		if (isset($status_map[strtolower($search)])) {
			$status_value = $status_map[strtolower($search)];
		} else {
			$status_value = null;
		}
		if (!empty($search)) {
			if ($status_value !== null) {
				$this->db->where('tbl_order_sub_details.ink_type', $status_value);
			} else if ($order_status_value !== null) {
				$this->db->where('tbl_order_sub_details.order_status', $order_status_value);
			} else {
				$this->db->group_start();
				$this->db->like('tbl_order_sub_details.order_id', $search);
				$this->db->or_like('tbl_order_sub_details.order_quantity', $search);
				$this->db->or_like('tbl_order_sub_details.order_quantity', $search);
				$this->db->or_like('tbl_order_sub_details.approved_qty', $search);
				$this->db->or_like('tbl_order_sub_details.remark', $search);
				$this->db->or_like('tbl_group_of_article.group_of_article', $search);
				$this->db->or_like('tbl_mould_parts.article_name', $search);
				$this->db->or_like('tbl_brand_master.brand_name', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->or_like('tbl_order_sub_details.order_date', $search);
				$this->db->or_like('tbl_order_sub_details.updated_on', $search);
				$this->db->group_end();
			}

		}

		$this->db->order_by('tbl_order_sub_details.id', 'DESC');
		$this->db->limit($limit, $offset);
		$result = $this->db->get()->result();

		foreach ($result as $key => $order) {

			$res = $this->Admin_model->get_order_details_according_order_id($order->order_id);
			$ink = $this->Admin_model->get_inks_according_brand($order->brand_type_id);

			$order->party_id = $res->party_id ?? null;
			$order->party_name = $res->party_name ?? null;
			$order->last_updated_date = $res->last_updated_date ?? null;
			$order->ink_names = $ink->ink_names ?? null;
		}

		$json_arr = !empty($result)
			? ['status' => 'true', 'message' => 'List retrieved successfully.', 'data' => $result]
			: ['status' => 'false', 'message' => 'No details found.', 'data' => []];

		echo json_encode($json_arr);
	}

	public function get_brands_according_party_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$party_id = $request['party_id'];

		$this->db->select('tbl_brand_master.*');
		$this->db->where('tbl_brand_master.party_name_id', $party_id);
		$this->db->order_by('id', 'DESC');
		$this->db->where('tbl_brand_master.is_deleted', '0');
		$result = $this->db->get('tbl_brand_master')->result();
		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'success';
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No details found';
		}

		$json_arr['data'] = $result;
		echo json_encode($json_arr);
	}
	public function get_all_printing_brand_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$brand_id = $request['brand_id'];
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
		if (!empty($brand)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Ink retrieved successfully.',
				'data' => $brand
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No ink found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	public function get_all_brands_api()
	{
		$this->db->where('is_deleted', '0');
		$this->db->group_by('brand_name');
		$result = $this->db->get('tbl_brand_master');

		if ($result->num_rows() > 0) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result->result(),
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
			];
		}
		echo json_encode($json_arr);
	}
	public function get_other_material_ink_names_api()
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

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Ink retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No ink found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	public function set_printing_report_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$approved_qty = $request['approvd_qty'] ?? 0;
		$sub_order_id = $request['sub_order_id'] ?? '';

		$data = [
			'order_status' => $request['order_status'] ?? '',
			'party_id' => $request['party_id'] ?? '',
			'article_id' => $request['article_id'] ?? '',
			'order_qty' => $request['order_qty'] ?? 0,
			'approvd_qty' => $approved_qty,
			'order_id' => $request['order_id'] ?? '',
			'brand_id' => $request['brand_id'] ?? '',
			'color_job' => $request['color_job'] ?? '',
			'other_material' => $request['other_material'] ?? '',
			'other_material_qty_1' => $request['other_material_qty_1'] ?? 0,
			'other_material_two' => $request['other_material_two'] ?? '',
			'other_material_qty_2' => $request['other_material_qty_2'] ?? 0,
			'remark' => $request['remark'] ?? '',
		];
		$order_status = $request['order_status'] ?? '';
		if ($order_status != '0') {
			$this->db->where([
				'order_id'   => $data['order_id'],
				'party_id'   => $data['party_id'],
				'article_id' => $data['article_id'],
				'brand_id'   => $data['brand_id'],
				'color_job'  => $data['color_job'],
				'other_material' => $data['other_material'],
				'other_material_qty_1' => $data['other_material_qty_1'],
				'order_qty'  => $data['order_qty'],
				'approvd_qty'=> $data['approvd_qty'],
				'is_deleted' => '0'
			]);

			$exists = $this->db->get('tbl_printing_material_report')->row();

			if ($exists) {
				$response = ['status' => true, 'message' => 'Material report added successfully'];
			}else{
				$current_date = date('Y-m-d');
				$this->db->where('task_id', $request['order_id']);
				$this->db->where('is_deleted', '0');
				$main_order = $this->db->get('tbl_auto_task_list')->row();
				$completed_days = (strtotime($current_date) - strtotime($main_order->last_updated_date)) / (60 * 60 * 24);
				if ($completed_days == 0) {
					$completed_days = 1;
				}
				$data['completed_days'] = $completed_days;
				$data['created_on'] = date('Y-m-d H:i:s');
				$this->db->insert('tbl_printing_material_report', $data);
				$report_id = $this->db->insert_id();
				$response = ['status' => true, 'message' => 'Material report added successfully'];
			}
		} else {
			$response = ['status' => true, 'message' => 'Material report added successfully'];
		}
		if ($order_status == '1') {
			$this->db->where('id', $sub_order_id);
			$this->db->update('tbl_order_sub_details', array('order_status' => '8', 'approved_qty' => $approved_qty));
		} else if ($order_status == '2') {
			$this->db->where('id', $sub_order_id);
			$this->db->update('tbl_order_sub_details', array('order_status' => '2', 'order_department_status' => '5'));
		}

		// if ($order_status === '1' || $order_status === '2') {
		// 	$this->db->where('id', $sub_order_id);
		// 	$this->db->update(
		// 		'tbl_order_sub_details',
		// 		[
		// 			'order_status' => $order_status,
		// 			'remark' => $request['remark'] ?? '',
		// 			'approved_qty' => $approved_qty,
		// 		]
		// 	);
		// }
		$order_id = $request['order_id'];

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
			$this->db->where('order_id', $order_id);
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
					'last_updated_by' => $request['employee_id'],
					'details_of_task' => 'Order Printing Completed Assigned to Account Department',
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $main_order_log_data);
			}
		}
		$ink_consumed = $request['ink_consumed'] ?? [];
		$ink_qty = $request['ink_qty'] ?? [];
		if (!empty($ink_consumed) && !empty($ink_qty)) {
			for ($i = 0; $i < count($ink_consumed); $i++) {
				if (!empty($ink_consumed[$i]) && !empty($ink_qty[$i])) {
					$ink_data = [
						'report_id' => $report_id,
						'sub_order_id' => $request['sub_order_id'] ?? '',
						'ink_id' => $ink_consumed[$i],
						'quantity' => $ink_qty[$i],
						'created_on' => date('Y-m-d H:i:s'),
					];
					$this->db->insert('tbl_printing_material_inks', $ink_data);
				}
			}
		}
		echo json_encode($response);
	}
	public function get_printing_material_report_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$order_status = isset($request['order_status']) ? trim($request['order_status']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$brand_id = isset($request['brand_id']) ? trim($request['brand_id']) : '';
		$article_id = isset($request['article_id']) ? trim($request['article_id']) : '';
		$this->db->select('tbl_printing_material_report.*,rm6.rm_name as six,rm7.rm_name as seven,tbl_customers.party_name,tbl_brand_master.brand_name,tbl_mould_parts.article_name');
		$this->db->from('tbl_printing_material_report');
		$this->db->join('tbl_rm_master as rm6', 'rm6.id = tbl_printing_material_report.other_material', 'left');
		$this->db->join('tbl_rm_master as rm7', 'rm7.id = tbl_printing_material_report.other_material_two', 'left');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_printing_material_report.party_id', 'left');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_printing_material_report.article_id', 'left');
		$this->db->join('tbl_brand_master', 'tbl_brand_master.id = tbl_printing_material_report.brand_id', 'left');
		$this->db->where('tbl_printing_material_report.is_deleted', '0');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_printing_material_report.order_id', $search);
			$this->db->or_like('tbl_printing_material_report.remark', $search);
			$this->db->or_like('rm6.rm_name', $search);
			$this->db->or_like('rm7.rm_name', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_1', $search);
			$this->db->or_like('tbl_printing_material_report.other_material_qty_2', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			$this->db->or_like('tbl_printing_material_report.approvd_qty', $search);
			$this->db->or_like('tbl_printing_material_report.order_qty', $search);
			$this->db->or_like('tbl_printing_material_report.completed_days', $search);
			$this->db->or_like('tbl_brand_master.brand_name', $search);
			$this->db->group_end();
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_printing_material_report.order_status', $order_status);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('tbl_printing_material_report.created_on >=', $from_date);
			$this->db->where('tbl_printing_material_report.created_on <=', $to_date);
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_printing_material_report.created_on)', date("Y-m-d", strtotime(trim($from_date))));
		}
		if (!empty($brand_id)) {
			$this->db->where('tbl_printing_material_report.brand_id', $brand_id);
		}
		if (!empty($article_id)) {
			$this->db->where('tbl_printing_material_report.article_id', $article_id);
		}
		$this->db->order_by('tbl_printing_material_report.id', 'DESC');
		$this->db->limit($limit, $offset);
		$result = $this->db->get()->result();
		foreach ($result as $key => $order) {
			$this->db->select('tbl_printing_material_inks.*, tbl_rm_master.rm_name');
			$this->db->from('tbl_printing_material_inks');
			$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_printing_material_inks.ink_id', 'left');
			$this->db->where('tbl_printing_material_inks.report_id', $order->id);
			$this->db->where('tbl_printing_material_inks.is_deleted', '0');
			$res = $this->db->get()->result();
			if ($res) {
				$order->selected_inks = $res;
			} else {
				$order->selected_inks = [];
			}
			$this->db->select('
				tbl_printing_material_report.other_material as other_material_1,tbl_printing_material_report.other_material_qty_1,tbl_printing_material_report.other_material_two as other_material_2,tbl_printing_material_report.other_material_qty_2,
				rm6.rm_name as other_material_1,
				rm7.rm_name as other_material_2
			');
			$this->db->from('tbl_printing_material_report');
			$this->db->join('tbl_rm_master as rm6', 'rm6.id = tbl_printing_material_report.other_material', 'left');
			$this->db->join('tbl_rm_master as rm7', 'rm7.id = tbl_printing_material_report.other_material_two', 'left');
			$this->db->where('tbl_printing_material_report.id', $order->id);
			$this->db->where('tbl_printing_material_report.is_deleted', '0');

			$material = $this->db->get()->result();
			if ($material) {
				$order->selected_material = $material;
			} else {
				$order->selected_material = [];
			}
		}
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}

	////////////////////////////////////Logistics Apis ///////////////////////////////////

	public function get_all_outward_order_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$final_status = isset($request['final_status']) ? $request['final_status'] : '';
		$dispatch_order_status = isset($request['order_status']) ? $request['order_status'] : '1';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$type_of_order = isset($request['type_of_order']) ? $request['type_of_order'] : '';
		$order_status = isset($request['order_status']) ? $request['order_status'] : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';
		$this->db->select('
			tbl_auto_task_list.*, 
			employee_user.first_name AS employee_name, 
			assign_user.first_name AS assigned_to_name, 
			tbl_krivisha_department.department AS d_name,
			tbl_customers.party_name
		');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('user_data AS employee_user', 'tbl_auto_task_list.employee_id = employee_user.id', 'left');
		$this->db->join('user_data AS assign_user', 'tbl_auto_task_list.assign_to_id = assign_user.id', 'left');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list.department_id', 'left');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');

		if ($final_status == "1") {
			$this->db->where('tbl_auto_task_list.department_id', '25');
			$this->db->where('tbl_auto_task_list.order_status', '4');
		} else if ($dispatch_order_status == '4') {
			$this->db->where('tbl_auto_task_list.department_id', '25');
			$this->db->where('tbl_auto_task_list.order_status', '4');
		} else {
			$this->db->where_in('tbl_auto_task_list.order_status', ['3', '9']);
			$this->db->where('tbl_auto_task_list.order_department_status', '3');
		}
		$this->db->where_in('tbl_auto_task_list.order_department', ['1', '2']);
		$this->db->where('tbl_auto_task_list.is_deleted', '0');
		$status_map = [
			'pending' => '9',
			'pending dispatch' => '9',
			'pen' => '9',
			'partial' => '3',
			'partially dispatch' => '3',
			'full' => '4',
			'fully dispatched' => '4'
		];

		if (isset($status_map[strtolower($search)])) {
			$status_value = $status_map[strtolower($search)];
		} else {
			$status_value = null;
		}
		$division_map = [
			'household' => '1',
			'house' => '1',
			'cont' => '2',
			'container' => '2',
			'both' => '3'
		];
		if (isset($division_map[strtolower($search)])) {
			$division_value = $division_map[strtolower($search)];
		} else {
			$division_value = null;
		}
		if (!empty($search)) {
			if ($status_value !== null) {
				$this->db->where('tbl_auto_task_list.order_status', $status_value);
			} else if ($division_value !== null) {
				$this->db->where('tbl_auto_task_list.type_of_order', $division_value);
			} else {
				$this->db->group_start();
				$this->db->like('employee_user.first_name', $search);
				$this->db->or_like('assign_user.first_name', $search);
				$this->db->or_like('tbl_customers.party_name', $search);
				$this->db->or_like('tbl_krivisha_department.department', $search);
				$this->db->or_like('tbl_auto_task_list.task_id', $search);
				$this->db->or_like('tbl_auto_task_list.task_status', $search);
				$this->db->or_like('tbl_auto_task_list.task_action', $search);
				$this->db->or_like('tbl_auto_task_list.details_of_task', $search);
				$this->db->group_end();
			}

		}
		if (!empty($party_id)) {
			$this->db->where('tbl_auto_task_list.party_id', $party_id);
		}
		if (!empty($type_of_order)) {
			$this->db->where('tbl_auto_task_list.type_of_order', $type_of_order);
		}
		if (!empty($order_status)) {
			$this->db->where('tbl_auto_task_list.order_status', $order_status);
		}
		// if (!empty($from_date) && !empty($to_date)) {
		// 	$this->db->where('tbl_auto_task_list.created_on >=', $from_date);
		// 	$this->db->where('tbl_auto_task_list.created_on <=', $to_date);
		// } else {
		// 	$this->db->where('tbl_auto_task_list.created_on >=', date('Y-m-d 00:00:00'));
		// }

		$this->db->order_by('tbl_auto_task_list.updated_on', 'DESC');
		$this->db->limit($limit, $offset);
		$result = $this->db->get()->result();
		foreach ($result as $key => $order) {
			$check_order_in_table = $this->db->select('order_id')->from('tbl_outward_orders')->where('order_id', $order->task_id)->where('is_deleted', '0')->get()->row();
			if (empty($check_order_in_table)) {
				$order_ststus = 'Pending Dispatch';
			} else if ($dispatch_order_status == '4') {
				$order_ststus = 'Fully Dispatched';
			} else {
				$order_ststus = 'Partially Dispatched';
			}

			$result[$key]->updated_order_ststus = $order_ststus;

			$this->db->where('order_id', $order->task_id);
			$this->db->where('is_deleted', '0');
			$check_sub = $this->db->get('tbl_order_sub_details');

			if ($check_sub->num_rows() > 0) {
				$this->db->select('tbl_order_sub_details.*, tbl_group_of_article.group_of_article, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
				$this->db->join('tbl_group_of_article', 'tbl_order_sub_details.group_of_article_id = tbl_group_of_article.id', 'left');
				$this->db->join('tbl_mould_parts', 'tbl_order_sub_details.article_id = tbl_mould_parts.id', 'left');
				$this->db->join('tbl_brand_master', 'tbl_order_sub_details.brand_type_id = tbl_brand_master.id', 'left');
				$this->db->where('tbl_order_sub_details.order_id', $order->task_id);
				$this->db->where_in('tbl_order_sub_details.order_status', ['1', '3', '4', '9']);
				$this->db->where('tbl_order_sub_details.is_deleted', '0');
				$this->db->order_by('tbl_order_sub_details.id', 'ASC');
				$res = $this->db->get('tbl_order_sub_details')->result();
				foreach ($res as $key => $sub_order) {
					$this->db->select('SUM(dispatch_quantity) AS dispatch_qty');
					$this->db->where('article_id', $sub_order->article_id);
					$this->db->where('brand_type_id', $sub_order->brand_type_id);
					$this->db->where('order_id', $sub_order->order_id);
					$this->db->where('is_deleted', '0');
					$dispatch_result = $this->db->get('tbl_dispatch_order_data')->row();
					$total_stock_qty = $this->db->select_sum('total_quantity')->where('article_id', $sub_order->article_id)
                                	 ->where('plant_id', $plant_id)->where('is_deleted', '0')
									 ->get('tbl_article_stock_report')->row();
					$stock_qty = (int)($total_stock_qty->total_quantity ?? 0);
					$sub_order->total_stock_quantity = $stock_qty;
					$dispatch_qty = (int) ($dispatch_result->dispatch_qty ?? 0);
					$approved_qty = (int) ($sub_order->approved_qty ?? 0);
					$sub_order->total_dispatch_quantity = ($dispatch_qty > $approved_qty) ? $approved_qty : $dispatch_qty;
					$sub_order->total_remaining_quantity = max(0,(int) $sub_order->approved_qty - $dispatch_qty);
				}
				$order->sub_order_details = !empty($res) ? $res : [];
			} else {
				$this->db->select('tbl_order_container_details.*, tbl_group_of_article.group_of_article, tbl_mould_parts.article_name');
				$this->db->join('tbl_group_of_article', 'tbl_order_container_details.group_of_article_id = tbl_group_of_article.id', 'left');
				$this->db->join('tbl_mould_parts', 'tbl_order_container_details.article_id = tbl_mould_parts.id', 'left');
				$this->db->where('tbl_order_container_details.order_id', $order->task_id);
				$this->db->where('tbl_order_container_details.order_status !=', '2');
				$this->db->where('tbl_order_container_details.is_deleted', '0');
				$this->db->order_by('tbl_order_container_details.id', 'ASC');
				$res = $this->db->get('tbl_order_container_details')->result();
				foreach ($res as $key => $sub_order) {
					$this->db->select('SUM(dispatch_quantity) AS dispatch_qty');
					$this->db->where('article_id', $sub_order->article_id);
					$this->db->where('order_id', $sub_order->order_id);
					$this->db->where('is_deleted', '0');
					$dispatch_result = $this->db->get('tbl_dispatch_order_data')->row();
					$total_stock_qty = $this->db->select_sum('total_quantity')->where('article_id', $sub_order->article_id)
                                	 ->where('plant_id', $plant_id)->where('is_deleted', '0')
									 ->get('tbl_article_stock_report')->row();

					$stock_qty = (int)($total_stock_qty->total_quantity ?? 0);
					$sub_order->total_stock_quantity = $stock_qty;
					$dispatch_qty = (int) ($dispatch_result->dispatch_qty ?? 0);
					$order_quantity = (int) ($sub_order->order_quantity ?? 0);
					$sub_order->total_dispatch_quantity = ($dispatch_qty > $order_quantity) ? $order_quantity : $dispatch_qty;
					$sub_order->total_remaining_quantity = max(0,(int) $sub_order->order_quantity - $dispatch_qty);
				}
				$order->sub_order_details = !empty($res) ? $res : [];
			}
		}

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Data retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_transport_api()
	{
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_transport_master')->result();
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Data found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function check_existing_dc_no_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$dc_no = $request['dc_no'];
		$id = $request['id'];
		if (empty($dc_no)) {
			echo json_encode(['status' => 'false', 'message' => 'dc no number is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('dc_no', $dc_no);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_outward_orders')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'dc no number already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'dc no number is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function check_existing_invoice_no_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$invoice_no = $request['invoice_no'];
		$id = $request['id'];
		if (empty($invoice_no)) {
			echo json_encode(['status' => 'false', 'message' => 'invoice no number is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('invoice_no', $invoice_no);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_outward_orders')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'invoice no number already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'invoice no number is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_outward_order_log_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$order_id = $request['order_id'];
		$this->db->select('
			tbl_outward_orders.*, tbl_location_master.city,tbl_customers.party_name,tbl_transport_master.transport_name
		');
		$this->db->from('tbl_outward_orders');
		$this->db->join('tbl_location_master', 'tbl_outward_orders.location_id = tbl_location_master.id', 'left');
		$this->db->join('tbl_transport_master', 'tbl_outward_orders.transport_id = tbl_transport_master.id', 'left');
		$this->db->join('tbl_customers', 'tbl_outward_orders.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_outward_orders.order_id', $order_id);
		$this->db->where('tbl_outward_orders.is_deleted', '0');
		$this->db->order_by('tbl_outward_orders.updated_on', 'DESC');
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
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$result = $query->result();
		foreach ($result as $key => $order) {
			$this->db->select('tbl_dispatch_order_data.*, tbl_brand_master.brand_name, tbl_mould_parts.article_name');
			$this->db->from('tbl_dispatch_order_data');
			$this->db->join('tbl_mould_parts', 'tbl_dispatch_order_data.article_id = tbl_mould_parts.id', 'left');
			$this->db->join('tbl_brand_master', 'tbl_dispatch_order_data.brand_type_id = tbl_brand_master.id', 'left');
			$this->db->where('tbl_dispatch_order_data.dispatch_id', $order->id);
			$this->db->where('tbl_dispatch_order_data.is_deleted', '0');
			$res = $this->db->get()->result();
			if ($res) {
				$order->dispatch_details = $res;
			} else {
				$order->dispatch_details = [];
			}
		}
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Data retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);

	}
	public function set_outward_transport_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		if ($request['order_id'] == null || $request['order_id'] == '') {
			$json_arr = [
				'status' => 'false',
				'message' => 'All fields are required.',
				'data' => []
			];
			echo json_encode($json_arr);
			return;
		}

		$division = isset($request['division']) ? $request['division'] : null;
		$final_order_remark = isset($request['final_order_remark']) ? $request['final_order_remark'] : null;
		$data = array(
			'order_id' => isset($request['order_id']) ? $request['order_id'] : null,
			'dc_no' => isset($request['dc_no']) ? $request['dc_no'] : null,
			'invoice_no' => isset($request['invoice_no']) ? $request['invoice_no'] : null,
			'division' => isset($request['division']) ? $request['division'] : null,
			'party_id' => isset($request['party_id']) ? $request['party_id'] : null,
			'invoice_value' => isset($request['invoice_value']) ? $request['invoice_value'] : null,
			'freight_amount' => isset($request['freight_amount']) ? $request['freight_amount'] : null,
			'location_id' => isset($request['location_id']) ? $request['location_id'] : null,
			'pincode' => isset($request['pincode']) ? $request['pincode'] : null,
			'transport_id' => isset($request['transport_id']) ? $request['transport_id'] : null,
			'vehicle' => isset($request['vehicle']) ? $request['vehicle'] : null,
			'vehicle_no' => isset($request['vehicle_no']) ? $request['vehicle_no'] : null,
			'driver_name' => isset($request['driver_name']) ? $request['driver_name'] : null,
			'driver_mobile' => isset($request['driver_mobile']) ? $request['driver_mobile'] : null,
			'freight_status' => isset($request['freight_status']) ? $request['freight_status'] : null,
			'remark' => isset($request['remark']) ? $request['remark'] : null,
			'sub_order_id' => isset($request['sub_order_id']) ? $request['sub_order_id'] : null,
			'updated_on' => date('Y-m-d H:i:s'),
		);

		$data['created_on'] = date('Y-m-d H:i:s');
		$this->db->insert('tbl_outward_orders', $data);
		$response = ['status' => true, 'message' => 'Material report added successfully'];
		$dispatch_id = $this->db->insert_id();
		$plant_id = $request['plant_id'];

		$article_ids = isset($request['article_ids']) ? $request['article_ids'] : [];
		$brand_ids = isset($request['brand_ids']) ? $request['brand_ids'] : [];
		$order_quantities = isset($request['quantity']) ? $request['quantity'] : [];
		$approved_quantity = isset($request['approved_quantity']) ? $request['approved_quantity'] : [];
		$dispatch_quantities = isset($request['dispatch_quantity']) ? $request['dispatch_quantity'] : [];
		$remaining_quantity = isset($request['remaining_quantity']) ? $request['remaining_quantity'] : [];
		$sub_order_ids = isset($request['updated_sub_order_ids']) ? $request['updated_sub_order_ids'] : [];
		$total_dis_quantity = isset($request['total_dispatch_quantity']) ? $request['total_dispatch_quantity'] : [];
		$order_id = isset($request['order_id']) ? $request['order_id'] : null;
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

					$brand_id = isset($brand_ids[$key]) ? (int) $brand_ids[$key] : '';
					$remaining_qty = isset($remaining_quantity[$key]) ? (int) $remaining_quantity[$key] : 0;
					$sub_or_id = isset($sub_order_ids[$key]) ? (int) $sub_order_ids[$key] : 0;
					if ($approved_qty) {
						$total_dispatch_qty = $approved_qty - $remaining_qty;
					} else {
						$total_dispatch_qty = $order_qty - $remaining_qty;
					}

					$quantity_data = $this->Admin_model->get_dispatch_quantity($article_id, $order_id, $brand_id);
                    $total_dispatch_qty_erp = (int)$quantity_data['total_dispatch_quantity'];
					$total_dispatch_quantity = $total_dispatch_qty_erp + $dispatch_qty;

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
						$ir_snapshot = $this->db->select('impression_rate')->where('article_id', $article_id)->where('is_deleted', '0')->get('tbl_impression_rate')->row();
						$current_ir = $ir_snapshot ? $ir_snapshot->impression_rate : 0;
						$dispatch_data = array(
							'order_id' => $order_id,
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
						if ($plant_id != '') {
							$this->db->where('plant_id', $plant_id);
						}
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
								'created_by' => $request['employee_id'] ?? 0,
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
					'details_of_task' => $final_order_remark,
					'last_updated_date' => date('Y-m-d H:i:s'),
					'last_updated_by' => $request['employee_id'],
					'created_on' => date('Y-m-d H:i:s'),
				);
				$this->db->insert('tbl_auto_task_list_history', $main_order_log_data);
			}

			$this->db->where('order_id', $order_id);
			$this->db->update('tbl_order_details', $order_list_data);

			// Notification Work when Final Order completed
			$party_data = $this->db->get_where('tbl_customers', array('id' => $request['party_id']))->row();
			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();
			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Full Dispatched updated by ' .
				$employee_data->first_name;
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
			$this->send_task_notification_by_token(56, $title, $description, $landing_page, $notification_according, $request['plant_id']);


			$this->db->select('id, plant_id'); 
			$this->db->where('id', $party_data->attending_salesperson_id);
			$user_data = $this->db->get('user_data')->row();

			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Full Dispatched  and updated by ' .
				$employee_data->first_name;
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
			$party_data = $this->db->get_where('tbl_customers', array('id' => $request['party_id']))->row();
			$this->db->where('id', $request['employee_id']);
			$this->db->where('is_deleted', '0');
			$employee_data = $this->db->get('user_data')->row();
			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Partially Dispatched  and updated by ' .
				$employee_data->first_name;
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
				'plant_id' => $request['plant_id'],
				'created_on' => date('Y-m-d H:i:s')
			);
			$this->db->insert('tbl_notifications', $notification_data);
			$this->send_task_notification_by_token(56, $title, $description, $landing_page, $notification_according, $request['plant_id']);

			$this->db->select('id, plant_id'); 
			$this->db->where('id', $party_data->attending_salesperson_id);
			$user_data = $this->db->get('user_data')->row();

			$title = 'Dispatch Updates';
			$description = $party_data->party_name . ' ' . 'Order ' . $order_id . ' Partially Dispatched  and updated by ' .
				$employee_data->first_name;
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
		echo json_encode($response);
	}
	public function get_production_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$date = isset($request['date']) ? $request['date'] : date('Y-m-d');
		$plant_id = isset($request['plant_id']) ? $request['plant_id'] : '';
		$employee_id = isset($request['employee_id']) ? $request['employee_id'] : '';
		// Get the start and end date of the month
		$month_start_date = date('Y-m-01', strtotime($date));  // First day of the month
		$month_end_date = date('Y-m-t', strtotime($date));  // Last day of the month

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
		$this->db->where('DATE(tbl_production_schedules.date)', $date);
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
		}
		$this->db->group_by('tbl_production_schedules.id');

		$result = $this->db->get()->result();

		// 1. Pending Tasks
		$this->db->where('assign_to_id', $employee_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('task_status', '1');
		if (!empty($date)) {
			$this->db->where('created_on', $date);
		}
		$pending_tasks = $this->db->count_all_results('tbl_manual_task');

		if (!$result || $this->is_result_empty($result)) {
			$result = [];
			$result[0] = new stdClass();
			$result[0]->total_achieved_qty_in_day = 0;
			$result[0]->pending_tasks = $pending_tasks;

			// If no production schedule found for the date, return only pending tasks

			$json_arr = [
				'status' => 'true',
				'message' => 'Data Retrived Successfully.',
				'data' => $result
			];
			echo json_encode($json_arr);
			return;
		}

		// Get all scheduled days in the given month
		$this->db->select('DISTINCT DATE(tbl_production_schedules.date) AS scheduled_date');
		$this->db->where('tbl_production_schedules.date >=', $month_start_date);
		$this->db->where('tbl_production_schedules.date <=', $month_end_date);
		if (!empty($plant_id)) {
			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
		}
		$this->db->where('tbl_production_schedules.is_deleted', '0');
		$scheduled_days = $this->db->get('tbl_production_schedules')->result();

		$total_achieved_qty_in_day = 0;
		$target_qty = 0;
		$final_achieve_qty = 0;
		$target_reached_days = 0;
		if ($result) {
			$i = 0;
			foreach ($result as $entry) {
				$this->db->where('DATE(tbl_article_production_details.production_date)', $date);
				$this->db->where('tbl_article_production_details.article_id', $entry->article_id);
				$this->db->where('tbl_article_production_details.machine_id', $entry->machine_id);
				if (!empty($plant_id)) {
					$this->db->where('tbl_article_production_details.plant_id', $plant_id);
				}
				$production = $this->db->get('tbl_article_production_details')->row();
				$target_qty += $entry->qty;
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
				if ($entry->qty == $total_achieved_qty || $entry->qty < $total_achieved_qty) {
					$target_reached_days++;
				}
				$result[$i]->total_achieve_qty = $total_achieved_qty;
				$i++;
				$total_achieved_qty_in_day += $total_achieved_qty;

			}
			$result[0]->pending_tasks = $pending_tasks;
			$result[0]->target_qty = $target_qty;
			$result[0]->total_achieved_qty_in_day = $total_achieved_qty_in_day;
			$result[0]->target_reached_days = $target_reached_days;

			$json_arr = [
				'status' => 'true',
				'message' => 'Data retrieved successfully.',
				'data' => $result,
				'target_qty' => $target_qty,
				'total_achieved_qty_in_day' => $total_achieved_qty_in_day,
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}

		echo json_encode($json_arr);
	}
	//Production Scheduled APis 
// public function get_production_dashboard_api()
// 	{
// 		$request = json_decode(file_get_contents('php://input'), true);
// 		$date = isset($request['date']) ? $request['date'] : date('Y-m-d');
// 		$plant_id = isset($request['plant_id']) ? $request['plant_id'] : '';
// 		$employee_id = isset($request['employee_id']) ? $request['employee_id'] : '';
// 		// Get the start and end date of the month
// 		$month_start_date = date('Y-m-01', strtotime($date));  // First day of the month
// 		$month_end_date = date('Y-m-t', strtotime($date));  // Last day of the month

	// 		$this->db->select('
// 			tbl_production_schedules.*, 
// 			tbl_mould_parts.article_name, 
// 			tbl_group_of_article.group_of_article, 
// 			tbl_plant_master.plant_name, 
// 			tbl_machine_master.machine_name,
// 			SUM(tbl_production_schedules.qty) AS target_qty,
// 			GROUP_CONCAT(DISTINCT tbl_mb_master.name ORDER BY tbl_mb_master.id ASC SEPARATOR ", ") AS color_names,
// 			GROUP_CONCAT(DISTINCT tbl_rm_master.rm_name ORDER BY tbl_rm_master.id ASC SEPARATOR ", ") AS raw_material_names
// 		');

	// 		$this->db->from('tbl_production_schedules');
// 		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_production_schedules.article_id', 'left');
// 		$this->db->join('tbl_group_of_article', 'tbl_group_of_article.id = tbl_production_schedules.article_group_id', 'left');
// 		$this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_production_schedules.plant_id', 'left');
// 		$this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');
// 		$this->db->join('tbl_mb_master', 'FIND_IN_SET(tbl_mb_master.id, tbl_production_schedules.color_id)', 'left');
// 		$this->db->join('tbl_rm_master', 'FIND_IN_SET(tbl_rm_master.id, tbl_production_schedules.raw_materials)', 'left');

	// 		$this->db->where('tbl_production_schedules.is_deleted', '0');
// 		$this->db->where('DATE(tbl_production_schedules.date)', $date);
// 		if (!empty($plant_id)) {
// 			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
// 		}
// 		if (!empty($machine_id)) {
// 			$this->db->where('tbl_production_schedules.machine_id', $machine_id);
// 		}
// 		$this->db->group_by('tbl_production_schedules.id');

	// 		$result = $this->db->get()->result();

	// 		// 1. Pending Tasks
// 		$this->db->where('assign_to_id', $employee_id);
// 		$this->db->where('is_deleted', '0');
// 		$this->db->where('task_status', '1');

	// 		$pending_tasks = $this->db->count_all_results('tbl_manual_task');

	// 		if (!$result || $this->is_result_empty($result)) {
// 			$result = [];
// 			$result[0] = new stdClass();
// 			$result[0]->total_achieved_qty_in_day = 0;
// 			$result[0]->pending_tasks = $pending_tasks;

	// 			// If no production schedule found for the date, return only pending tasks

	// 			$json_arr = [
// 				'status' => 'true',
// 				'message' => 'Data Retrived Successfully.',
// 				'data' => $result
// 			];
// 			echo json_encode($json_arr);
// 			return;
// 		}

	// 		// Get all scheduled days in the given month
// 		$this->db->select('DISTINCT DATE(tbl_production_schedules.date) AS scheduled_date');
// 		$this->db->where('tbl_production_schedules.date >=', $month_start_date);
// 		$this->db->where('tbl_production_schedules.date <=', $month_end_date);
// 		if (!empty($plant_id)) {
// 			$this->db->where('tbl_production_schedules.plant_id', $plant_id);
// 		}
// 		$this->db->where('tbl_production_schedules.is_deleted', '0');
// 		$scheduled_days = $this->db->get('tbl_production_schedules')->result();

	// 		// Initialize target_reached_days counter
// 		$target_reached_days = 0;

	// 		foreach ($scheduled_days as $day) {
// 			$this->db->select_sum('qty');
// 			$this->db->where('DATE(tbl_production_schedules.date)', $day->scheduled_date);
// 			if (!empty($plant_id)) {
// 				$this->db->where('tbl_production_schedules.plant_id', $plant_id);
// 			}
// 			$this->db->where('tbl_production_schedules.is_deleted', '0');
// 			$target_qty = $this->db->get('tbl_production_schedules')->row()->qty ?? 0;

	// 			$this->db->where('DATE(tbl_article_production_details.production_date)', $day->scheduled_date);
// 			$this->db->where('tbl_article_production_details.plant_id', $plant_id);
// 			$achieved_qty_in_day = $this->db->select_sum('approved_qty')->where('is_deleted', '0')->get('tbl_article_production_details')->row()->approved_qty ?? 0;

	// 			// Compare achieved_qty with target_qty for the current day
// 			if ($achieved_qty_in_day >= $target_qty) {
// 				$target_reached_days++;
// 			}
// 		}
// 		$total_achieved_qty_in_day = 0;
// 		$final_target_qty = 0;
// 		$final_achieve_qty = 0;
// 		if ($result) {
// 			$i = 0;
// 			foreach ($result as $entry) {
// 				$this->db->where('is_deleted', '0');
// 				$this->db->where('DATE(tbl_article_production_details.production_date)', $date);
// 				$this->db->where('tbl_article_production_details.article_id', $entry->article_id);
// 				$this->db->where('tbl_article_production_details.machine_id', $entry->machine_id);
// 				if (!empty($plant_id)) {
// 					$this->db->where('tbl_article_production_details.plant_id', $plant_id);
// 				}
// 				$production = $this->db->get('tbl_article_production_details')->row();
// 				$total_achieved_qty  = 0;
// 				if (!empty($production)) {
// 					$hourly_fields = [
// 						'08:00-09:00' => $production->qty_eight_nine,
// 						'09:00-10:00' => $production->qty_nine_ten,
// 						'10:00-11:00' => $production->qty_ten_eleven,
// 						'11:00-12:00' => $production->qty_eleven_twelve,
// 						'12:00-13:00' => $production->qty_twelve_thirteen,
// 						'13:00-14:00' => $production->qty_thirteen_fourteen,
// 						'14:00-15:00' => $production->qty_fourteen_fifteen,
// 						'15:00-16:00' => $production->qty_fifteen_sixteen,
// 						'16:00-17:00' => $production->qty_sixteen_seventeen,
// 						'17:00-18:00' => $production->qty_seventeen_eighteen,
// 						'18:00-19:00' => $production->qty_eighteen_nineteen,
// 						'19:00-20:00' => $production->qty_nineteen_twenty,
// 						'20:00-21:00' => $production->qty_twenty_twentyone,
// 						'21:00-22:00' => $production->qty_twentyone_twentytwo,
// 						'22:00-23:00' => $production->qty_twentytwo_twentythree,
// 						'23:00-24:00' => $production->qty_twentythree_zero,
// 						'00:00-01:00' => $production->qty_zero_one,
// 						'01:00-02:00' => $production->qty_one_two,
// 						'02:00-03:00' => $production->qty_two_three,
// 						'03:00-04:00' => $production->qty_three_four,
// 						'04:00-05:00' => $production->qty_four_five,
// 						'05:00-06:00' => $production->qty_five_six,
// 						'06:00-07:00' => $production->qty_six_seven,
// 						'07:00-08:00' => $production->qty_seven_eight,
// 					];

	// 					$start_time = $entry->production_schedule_start_time;
// 					$end_time   = $entry->production_schedule_end_time;

	// 					$start_int = intval(str_replace(':', '', $start_time));
// 					$end_int   = intval(str_replace(':', '', $end_time));

	// 					if ($end_int <= $start_int) {
// 						$end_int += 2400;
// 					}
// 					foreach ($hourly_fields as $range => $qty) {
// 						list($from, $to) = explode('-', $range);

	// 						$from_int = intval(str_replace(':', '', $from));
// 						$to_int   = intval(str_replace(':', '', $to));

	// 						if ($to_int === 0) {
// 							$to_int = 2400;
// 						}

	// 						if ($to_int <= $start_int) {
// 							$from_int += 2400;
// 							$to_int   += 2400;
// 						}

	// 						if ($from_int >= $start_int && $to_int <= $end_int) {
// 							$total_achieved_qty += (float)$qty;
// 						}
// 					}
// 				}
// 				$result[$i]->total_achieve_qty = $total_achieved_qty;
// 				$result[$i]->target_reached_days = $target_reached_days;
// 				$i++;
// 				$total_achieved_qty_in_day += $total_achieved_qty;
// 				$final_achieve_qty += $total_achieved_qty;

	// 			}

	// 			$result[0]->total_achieved_qty_in_day = $total_achieved_qty_in_day;
// 			$result[0]->pending_tasks = $pending_tasks;

	// 			echo json_encode([
// 				'status' => 'true',
// 				'message' => 'Data retrieved successfully.',
// 				'target_qty' => $final_target_qty,
// 				'total_achieve_qty' => $final_achieve_qty,
// 				'total_achieved_qty_in_day' => $total_achieved_qty_in_day,
// 				'pending_tasks' => $pending_tasks,
// 				'data' => $result
// 			]);
// 		} else {
// 			$json_arr = [
// 				'status' => 'false',
// 				'message' => 'No details found.',
// 				'data' => []
// 			];
// 		}

	// 		// echo json_encode($json_arr);
// 	}

	private function is_result_empty($result)
	{
		foreach ($result as $entry) {
			if (
				!empty($entry->id) || !empty($entry->production_schedule_start_date) ||
				!empty($entry->production_schedule_end_date)
			) {
				return false;
			}
		}
		return true;
	}
	public function get_maintenance_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$machine = isset($request['machine_id']) ? $request['machine_id'] : '';
		$plant_id = isset($request['my_plant_id']) ? $request['my_plant_id'] : '';
		$status_of_work = isset($request['status_of_maintenance']) ? $request['status_of_maintenance'] : '';
		$type_of_action = isset($request['type_of_action']) ? $request['type_of_action'] : '';
		$mwo_code = isset($request['mwo_code']) ? $request['mwo_code'] : '';
		$employee_id = isset($request['employee_id']) ? $request['employee_id'] : '';
		// echo"<pre>";print_r($plant_id);exit;
		if ($from_date && $to_date) {
			$start_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

			if ($start_date && $end_date) {
				$this->db->where('date >=', $start_date);
				$this->db->where('date <=', $end_date);
			}
		}

		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if (!empty($mwo_code)) {
			$this->db->where('mwo_code', $mwo_code);
		}
		if (!empty($status_of_work)) {
			$this->db->where('status_of_work', $status_of_work);
		}
		if (!empty($type_of_action)) {
			$this->db->where('type_of_action', $type_of_action);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}

		$this->db->where('status_of_work', '2');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$completed_maintenance = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('status_of_work', '1');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$pending_maintenance = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('type_of_action', '3');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		$this->db->where('status_of_work', '1');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$preventive_maintenance_pending = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('type_of_action', '3');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$preventive_maintenance_request = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('maintaince', '2');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$mould_maintenance_request = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('maintaince', '1');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$machine_maintenance_request = $this->db->count_all_results('tbl_maintenance_production') ?? 0;
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$material_cost = $this->db->select_sum('total_cost')->get('tbl_maintenance_list')->row()->total_cost ?? 0;

		$this->db->select_sum('total_cost', 'total');
		$this->db->where('type_of_action', '3');
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$row = $this->db->get('tbl_maintenance_list')->row();
		$preventive_material_cost = $row && $row->total !== null ? $row->total : 0;

		$completion_percentage = 0;
		if ($preventive_maintenance_request > 0) {
			$completion_percentage = ($completed_maintenance / $preventive_maintenance_request) * 100;
		}

		$this->db->select('created_on, updated_on');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if (!empty($machine)) {
			$this->db->where('sub_type_id', $machine);
		}
		$query = $this->db->get('tbl_maintenance_production');

		$total_downtime = 0;
		$total_downtime_seconds = 0;
		foreach ($query->result() as $row) {
			$created_on = new DateTime($row->created_on);
			$updated_on = new DateTime($row->updated_on);

			$interval = $created_on->diff($updated_on);

			$seconds =
				($interval->days * 24 * 60 * 60) +
				($interval->h * 60 * 60) +
				($interval->i * 60) +
				$interval->s;
			$total_downtime_seconds += $seconds;
		}
		$total_downtime_hours = round($total_downtime_seconds / 3600, 2);

		// $this->db->where('plant_id', $plant_id);
		// $total_records = $this->db->count_all_results('tbl_maintenance_production');
		// $average_down_time = $total_records > 0 ? $total_downtime / $total_records : 0;

		$this->db->where('assign_to_id', $employee_id);
		$this->db->where('task_status', '1');
		if ($start_date && $end_date) {
			$this->db->where('created_on >=', $start_date);
			$this->db->where('created_on <=', $end_date);
		}
		$pending_task = $this->db->count_all_results('tbl_manual_task') ?? 0;

		$json_arr = [
			'status' => 'true',
			'message' => 'Data retrieved successfully.',
			'data' => [
				'completed_maintenance' => $completed_maintenance,
				'pending_maintenance' => $pending_maintenance,
				'Preventive_maintenance_pending' => $preventive_maintenance_pending,
				'Preventive_maintenance_request' => $preventive_maintenance_request,
				'mould_maintenance_request' => $mould_maintenance_request,
				'machine_maintenance_request' => $machine_maintenance_request,
				'material_cost' => $material_cost,
				'preventive_material_cost' => $preventive_material_cost,
				'average_down_time' => $total_downtime_hours,
				'completion_preventive_percentage' => $completion_percentage,
				'pending_task' => $pending_task,
			]
		];
		echo json_encode($json_arr);
	}
	public function get_logistics_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$division = isset($request['division']) ? $request['division'] : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$order_status = isset($request['order_status']) ? $request['order_status'] : '';

		if ($from_date && $to_date) {
			$start_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

			if ($start_date && $end_date) {
				$this->db->where('date >=', $start_date);
				$this->db->where('date <=', $end_date);
			}
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		if (!empty($order_status)) {
			$this->db->where('order_status', $order_status);
		}
		$this->db->where('department_id', '11');
		$this->db->where('order_status', '3');
		$this->db->where('order_department', '1');
		if (!empty($division)) {
			$this->db->where('type_of_order', $division);
		}

		$this->db->where('is_deleted', '0');
		$patial_dispatched = $this->db->count_all_results('tbl_auto_task_list') ?? 0;

		$this->db->where('order_department', '1');
		$this->db->where('order_status', '4');
		if (!empty($division)) {
			$this->db->where('type_of_order', $division);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$this->db->where('is_deleted', '0');
		$completed_orders = $this->db->count_all_results('tbl_auto_task_list') ?? 0;

		$this->db->where('department_id', '11');
		$this->db->where_in('order_status', ['0', '9']);
		$this->db->where('order_department', '1');
		if (!empty($division)) {
			$this->db->where('type_of_order', $division);
		}
		$this->db->where('is_deleted', '0');
		$pending_task = $this->db->count_all_results('tbl_auto_task_list') ?? 0;

		$this->db->select('id,updated_on,task_id');
		$this->db->where('order_status', '4');
		$this->db->where('department_id', '25');
		$this->db->where('order_department_status', '3');
		if (!empty($division)) {
			$this->db->where('type_of_order', $division);
		}
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		if (!empty($party_id)) {
			$this->db->where('party_id', $party_id);
		}
		$this->db->where('is_deleted', '0');
		$query = $this->db->get('tbl_auto_task_list')->result();
		$total_time = 0;
		$total_records = 0;
		foreach ($query as $row) {
			$this->db->select('id,created_on,task_id');
			$this->db->where('department_id', '11');
			$this->db->where('task_id', $row->id);
			$this->db->where('is_deleted', '0');
			$this->db->order_by('id', 'ASC');
			$logistics_entry = $this->db->get('tbl_auto_task_list_history')->row();
			if (empty($logistics_entry)) {
				continue;
			}
			$created_on = new DateTime($logistics_entry->created_on);
			$updated_on = new DateTime($row->updated_on);

			$interval = $created_on->diff($updated_on);

			$order_time_hours =
				($interval->days * 24) +
				$interval->h +
				($interval->i / 60);

			$total_time += $order_time_hours;
			$total_records++;
		}
		$ready_to_dispatch = $total_records > 0 ? $total_time / $total_records : 0;
		$hours = round($ready_to_dispatch, 2);
		$days = floor($hours / 24);
		$remaining_hours = round($hours % 24, 1);
		$average_order_ready_to_dispatch = $days . ' Days ' . $remaining_hours . ' Hrs';
		$json_arr = [
			'status' => 'true',
			'message' => 'Data retrieved successfully.',
			'data' => [
				'patial_dispatched' => $patial_dispatched,
				'completed_orders' => $completed_orders,
				'pending_task' => $pending_task,
				'average_order_ready_to_dispatch' => $average_order_ready_to_dispatch
			]
		];
		echo json_encode($json_arr);
	}
	public function get_printing_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$division = isset($request['division']) ? $request['division'] : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$order_status = isset($request['order_status']) ? $request['order_status'] : '';

		if ($from_date && $to_date) {
			$start_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
			if ($start_date && $end_date) {
				$this->db->where('order_date >=', $start_date);
				$this->db->where('order_date <=', $end_date);
			}
		}

		if (!empty($order_status)) {
			$this->db->where('order_status', $order_status);
		}
		$this->db->where('is_deleted', '0');
		$this->db->where_in('order_status', ['0', '7']);
		$total_qty = $this->db->select_sum('order_quantity')->get('tbl_order_sub_details')->row()->order_quantity ?? 0;

		$this->db->where_in('order_status', ['0', '7']);
		$this->db->where('order_department_status', '2');
		$pending_brands = $this->db->count_all_results('tbl_order_sub_details') ?? 0;

		$this->db->select('
			tbl_mould_parts.article_name,
			tbl_order_sub_details.article_id,
			SUM(tbl_order_sub_details.order_quantity) AS pending_qty,
			COUNT(tbl_order_sub_details.brand_type_id) AS total_brands
		');
		$this->db->from('tbl_order_sub_details');
		$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_order_sub_details.article_id', 'left');
		$this->db->group_by('tbl_order_sub_details.article_id');
		$this->db->where('tbl_order_sub_details.is_deleted', '0');
		$this->db->where('tbl_order_sub_details.order_status', '7');

		$result = $this->db->get()->result();

		$json_arr = [
			'status' => 'true',
			'message' => 'Data retrieved successfully.',
			'data' => [
				'total_qty' => $total_qty,
				'pending_brands' => $pending_brands,
				'bucket_data' => $result
			]
		];
		echo json_encode($json_arr);
	}

	public function get_owner_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$division = isset($request['division']) ? $request['division'] : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$plant_id = isset($request['plant_id']) ? $request['plant_id'] : '';
		$machine_id = isset($request['machine_id']) ? $request['machine_id'] : '';
		$order_status = isset($request['order_status']) ? $request['order_status'] : '';


		if ($from_date && $to_date) {
			$start_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));

		}

		if (!empty($order_status)) {
			$this->db->where('order_status', $order_status);
		}
		if ($start_date && $end_date) {
			$this->db->where('updated_on >=', $start_date);
			$this->db->where('updated_on <=', $end_date);
		}
		$this->db->where('department_id', '11');
		$this->db->where_in('order_status', ['8', '9']);
		$this->db->where('order_department', '1');
		$pending_dispatch_onwer = $this->db->count_all_results('tbl_auto_task_list') ?? 0;

		if ($start_date && $end_date) {
			$this->db->where('updated_on >=', $start_date);
			$this->db->where('updated_on <=', $end_date);
		}
		$this->db->where_in('order_status', ['0', '7']);
		$this->db->where_in('order_department_status', ['3', '2']);
		$pending_printing_onwer = $this->db->count_all_results('tbl_order_sub_details') ?? 0;

		$this->db->select('created_on, updated_on');
		$this->db->where('status_of_work', '2');
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$maintenance_res = $this->db->get('tbl_maintenance_production');

		// foreach ($maintenance_res->result() as $row) {
		// 	$created_on = new DateTime($row->created_on);
		// 	$updated_on = new DateTime($row->updated_on);

		// 	$interval = $created_on->diff($updated_on);
		// 	$downtime_hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);

		// 	$total_downtime += $downtime_hours;
		// }
		// if ($start_date && $end_date) {
		// 	$this->db->where('date >=', $start_date);
		// 	$this->db->where('date <=', $end_date);
		// }
		// $this->db->where('status_of_work', '2');
		// $total_records = $this->db->count_all_results('tbl_maintenance_production');
		// $average_down_time = $total_records > 0 ? $total_downtime / $total_records : 0;


		$this->db->where('status_of_work', '2');
		$this->db->where('type_of_action', '3');
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$completed_maintenance = $this->db->count_all_results('tbl_maintenance_production') ?? 0;

		$this->db->where('type_of_action', '3');
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$preventive_maintenance_request = $this->db->count_all_results('tbl_maintenance_production') ?? 0;
		$completion_percentage = 0;
		if ($preventive_maintenance_request > 0) {
			$completion_percentage = ($completed_maintenance / $preventive_maintenance_request) * 100;
		}
		$date = date('Y-m-d');
		$this->db->select('COUNT(DISTINCT sales_person_id) as total');
		$this->db->from('tbl_side_visit_details');
		$this->db->where('is_deleted', '0');
		$this->db->where('DATE(date)', $date);
		$result = $this->db->get()->row();
		$total_on_field = $result ? (int) $result->total : 0;

		$this->db->select('COUNT(DISTINCT sales_person_id) as total_visits');
		$this->db->from('tbl_side_visit_details');
		$this->db->where('is_deleted', '0');
		$this->db->where('DATE(date)', $date);
		$result = $this->db->get()->row();
		$sales_person_in_field = $result ? (int) $result->total_visits : 0;
		// $sales_person_in_field = $this->db->count_all_results('tbl_side_visit_details') ?? 0;
		$this->db->select('id, order_id, created_on');
		$this->db->where('type_of_order', '1');
		$this->db->where('is_deleted', '0');
		if ($start_date && $end_date) {
			$this->db->where('order_date >=', $start_date);
			$this->db->where('order_date <=', $end_date);
		}
		$orders = $this->db->get('tbl_order_details')->result();

		$total_days = 0;
		$count = 0;

		foreach ($orders as $order) {
			$this->db->select('updated_on, task_id, order_status');
			$this->db->where('task_id', $order->order_id);
			$this->db->where('order_status', '4');
			$this->db->where('order_department', '1');
			$this->db->order_by('updated_on', 'DESC');
			$dispatch = $this->db->get('tbl_auto_task_list')->row();

			if ($dispatch && $order->created_on) {
				$order_date = new DateTime($order->created_on);
				$dispatch_date = new DateTime($dispatch->updated_on);
				$diff = $order_date->diff($dispatch_date)->days;
				$total_days += $diff;
				$count++;
			}
		}

		$household_order_execution = $count > 0 ? round($total_days / $count, 1) : 0;

		$this->db->select('id, order_id, created_on');
		$this->db->where('type_of_order', '2');
		$this->db->where('is_deleted', '0');
		if ($start_date && $end_date) {
			$this->db->where('order_date >=', $start_date);
			$this->db->where('order_date <=', $end_date);
		}
		$orders_container = $this->db->get('tbl_order_details')->result();

		$total_days_container = 0;
		$count_of_container = 0;

		foreach ($orders_container as $order) {
			$this->db->select('updated_on, task_id, order_status');
			$this->db->where('task_id', $order->order_id);
			$this->db->where('order_status', '4');
			$this->db->where('order_department', '1');
			$this->db->order_by('updated_on', 'DESC');
			$dispatch_container = $this->db->get('tbl_auto_task_list')->row();

			if ($dispatch_container && $order->created_on) {
				$order_d = new DateTime($order->created_on);
				$dispatch_d = new DateTime($dispatch_container->updated_on);
				$differ = $order_d->diff($dispatch_d)->days;
				$total_days_container += $differ;
				$count_of_container++;
			}
		}
		$container_order_execution = $count_of_container > 0 ? round($total_days_container / $count_of_container, 1) : 0;

		$this->db->like('task_id', 'ENQ', 'after');
		if ($start_date && $end_date) {
			$this->db->where('created_on >=', $start_date);
			$this->db->where('created_on <=', $end_date);
		}
		$total_enquiry_orders = $this->db->count_all_results('tbl_manual_task') ?? 0;

		$this->db->like('task_id', 'ENQ', 'after');
		$this->db->where('task_status', '2');
		if ($start_date && $end_date) {
			$this->db->where('created_on >=', $start_date);
			$this->db->where('created_on <=', $end_date);
		}
		$total_enquiry_orders_completed = $this->db->count_all_results('tbl_manual_task') ?? 0;

		$enquiry_order_generation_ratio = ($total_enquiry_orders > 0)
			? ($total_enquiry_orders_completed / $total_enquiry_orders) * 100
			: 0;

		$this->db->like('task_id', 'ENQ', 'after');
		$this->db->where('task_status', '1');
		if ($start_date && $end_date) {
			$this->db->where('created_on >=', $start_date);
			$this->db->where('created_on <=', $end_date);
		}
		$total_pending_enquiry = $this->db->count_all_results('tbl_manual_task') ?? 0;

		$this->db->select('party_id, COUNT(order_id) as order_count');
		$this->db->where('is_deleted', '0');

		if ($start_date && $end_date) {
			$this->db->where('order_date >=', $start_date);
			$this->db->where('order_date <=', $end_date);
		}
		$this->db->group_by('party_id');
		$orders = $this->db->get('tbl_order_details')->result();

		$repeat = 0;
		$new = 0;
		foreach ($orders as $order) {
			if ($order->order_count > 1) {
				$repeat++;
			} else {
				$new++;
			}
		}
		$total = $repeat + $new;
		$repeat_percent = $total > 0 ? round(($repeat / $total) * 100, 2) : 0;
		$new_percent = $total > 0 ? round(($new / $total) * 100, 2) : 0;

		$this->db->where('order_department', '1');
		if ($start_date && $end_date) {
			$this->db->where('date >=', $start_date);
			$this->db->where('date <=', $end_date);
		}
		$no_of_orders = $this->db->count_all_results('tbl_auto_task_list') ?? 0;

		$total_downtime = 0;
		
		if (!empty($from_date) && !empty($to_date)) {
			$start_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
			$end_date   = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
		} else {
			$start_date = date('Y-m-01');
			$end_date   = date('Y-m-d');
		}

		$this->db->select('id, machine_name, DATE(created_on) as created_date');
		$this->db->where('is_deleted', '0');
		$this->db->where_in('department_id', ['2','3','6','14']);
		$this->db->where('status', '1');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		if (!empty($machine_id)) {
			$this->db->where('id', $machine_id);
		}
		$machines = $this->db->get('tbl_machine_master')->result();
		
		foreach ($machines as $machine) {
			$machine_start_date = max($start_date, $machine->created_date);
			if ($machine_start_date > $end_date) {
				continue;
			}
			$days = (strtotime($end_date) - strtotime($machine_start_date)) / 86400 + 1;
    		$total_hours = max(0, $days * 24);
			$sql = "
				SELECT
				(
					SUM(qty_eight_nine < 30) +
					SUM(qty_nine_ten < 30) +
					SUM(qty_ten_eleven < 30) +
					SUM(qty_eleven_twelve < 30) +
					SUM(qty_twelve_thirteen < 30) +
					SUM(qty_thirteen_fourteen < 30) +
					SUM(qty_fourteen_fifteen < 30) +
					SUM(qty_fifteen_sixteen < 30) +
					SUM(qty_sixteen_seventeen < 30) +
					SUM(qty_seventeen_eighteen < 30) +
					SUM(qty_eighteen_nineteen < 30) +
					SUM(qty_nineteen_twenty < 30) +
					SUM(qty_twenty_twentyone < 30) +
					SUM(qty_twentyone_twentytwo < 30) +
					SUM(qty_twentytwo_twentythree < 30) +
					SUM(qty_twentythree_zero < 30) +
					SUM(qty_zero_one < 30) +
					SUM(qty_one_two < 30) +
					SUM(qty_two_three < 30) +
					SUM(qty_three_four < 30) +
					SUM(qty_four_five < 30) +
					SUM(qty_five_six < 30) +
					SUM(qty_six_seven < 30) +
					SUM(qty_seven_eight < 30)
				) AS downtime_hours,
				COUNT(*) AS total_rows
				FROM tbl_article_production_details
				WHERE is_deleted = '0'
				AND machine_id = ?
				AND production_date BETWEEN ? AND ?
			";

			$params = [$machine->id, $start_date, $end_date];

			if (!empty($plant_id)) {
				$sql .= " AND plant_id = ?";
				$params[] = $plant_id;
			}
			$row = $this->db->query($sql, $params)->row();

			/* ===== NO SCHEDULE CASE ===== */
			if ($row->total_rows == 0) {
				$machine_downtime = $total_hours;
			} else {
				$machine_downtime = (int) $row->downtime_hours;
			}
			$total_downtime += $machine_downtime;
		}
		$json_arr = [
			'status' => 'true',
			'message' => 'Data retrieved successfully.',
			'data' => [
				'pending_dispatch' => $pending_dispatch_onwer,
				'pending_printing' => $pending_printing_onwer,
				'average_down_time' => $total_downtime,
				'pm_completion_percentage' => $completion_percentage,
				'sales_person_in_field' => $sales_person_in_field,
				'total_pending_enquiry' => $total_pending_enquiry,
				'total_enquiry_orders_completed' => $total_enquiry_orders_completed,
				'enquiry_order_generation_ratio' => $enquiry_order_generation_ratio,
				'household_order_execution' => $household_order_execution,
				'container_order_execution' => $container_order_execution,
				'repeat_customer_percent' => $repeat_percent,
				'new_customer_percent' => $new_percent,
				'no_of_orders' => $no_of_orders
			]
		];
		echo json_encode($json_arr);
	}

	public function get_sales_person_dashboard_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$sales_person_id = isset($request['employee_id']) ? $request['employee_id'] : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		// $product_id = isset($request['article_id']) ? $request['article_id'] : '';

		// Date filters
		$date_filter = [];
		if ($from_date && $to_date) {
			$date_filter['from'] = date('Y-m-d', strtotime($from_date));
			$date_filter['to'] = date('Y-m-d', strtotime($to_date));
		}

		// 1. Pending Tasks
		$this->db->where('assign_to_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('task_status', '1');
		if ($from_date && $to_date) {
			$this->db->where('DATE(created_on) >=', $date_filter['from']);
			$this->db->where('DATE(created_on) <=', $date_filter['to']);
		} else if ($from_date) {
			$this->db->where('DATE(created_on)', date('Y-m-d', strtotime($from_date)));
		}
		$pending_tasks = $this->db->count_all_results('tbl_manual_task');

		// 2. Scheduled Visits
		$this->db->where('sales_person_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(date) >=', $date_filter['from']);
			$this->db->where('DATE(date) <=', $date_filter['to']);
		} else {
			$this->db->where('DATE(date)', date('Y-m-d'));
		}
		$scheduled_visits = $this->db->count_all_results('tbl_side_visit_details');


		// 3. No of Orders (Filtered)
		$this->db->select('tbl_order_details.*, tbl_customers.attending_salesperson_id, tbl_customers.id');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_customers.attending_salesperson_id', $sales_person_id);
		$this->db->where('tbl_order_details.is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', $date_filter['from']);
			$this->db->where('DATE(tbl_order_details.order_date) <=', $date_filter['to']);
		}
		$total_orders = $this->db->count_all_results();

		// dispatch pending 
		$this->db->select('tbl_auto_task_list.*, tbl_customers.attending_salesperson_id, tbl_customers.id');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_customers.attending_salesperson_id', $sales_person_id);
		$this->db->where('tbl_auto_task_list.order_status', '9');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(tbl_auto_task_list.date) >=', $date_filter['from']);
			$this->db->where('DATE(tbl_auto_task_list.date) <=', $date_filter['to']);
		}
		$pending_dispatch_count = $this->db->count_all_results();

		// 4. No of Pending Orders
		$this->db->select('tbl_order_details.*, tbl_customers.attending_salesperson_id, tbl_customers.id');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_customers.attending_salesperson_id', $sales_person_id);
		$this->db->where('tbl_order_details.order_status', '1');
		$this->db->where('tbl_order_details.is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', $date_filter['from']);
			$this->db->where('DATE(tbl_order_details.order_date) <=', $date_filter['to']);
		}
		$pending_orders = $this->db->count_all_results();


		// Step 1: Get all orders of this sales person
		$this->db->select('tbl_order_details.*, tbl_customers.attending_salesperson_id, tbl_customers.id');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_customers.attending_salesperson_id', $sales_person_id);
		$this->db->where('tbl_order_details.is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', $date_filter['from']);
			$this->db->where('DATE(tbl_order_details.order_date) <=', $date_filter['to']);
		}
		$orders = $this->db->get()->result();

		// Step 2: Collect order_ids
		$order_ids = array_column($orders, 'order_id');

		$article_totals = [];
		// echo"<pre>";print_r($order_ids);exit;
		// Step 3: Check in tbl_order_sub_details
		if (!empty($order_ids)) {
			$this->db->where_in('order_id', $order_ids);
			$this->db->where('is_deleted', '0');
			$sub_orders = $this->db->get('tbl_order_sub_details')->result();

			foreach ($sub_orders as $row) {
				$article_id = $row->article_id;
				$qty = $row->dispatch_quantity; // dispatched qty
				if (!isset($article_totals[$article_id])) {
					$article_totals[$article_id] = 0;
				}
				$article_totals[$article_id] += $qty;
			}

			// Step 4: Check in tbl_order_container_details
			$this->db->where_in('order_id', $order_ids);
			$this->db->where('is_deleted', '0');
			$container_orders = $this->db->get('tbl_order_container_details')->result();

			foreach ($container_orders as $row) {
				$article_id = $row->article_id;
				$qty = $row->dispatch_quantity; // dispatched qty
				if (!isset($article_totals[$article_id])) {
					$article_totals[$article_id] = 0;
				}
				$article_totals[$article_id] += $qty;
			}
		}

		arsort($article_totals); // high to low
		$top_products = array_slice($article_totals, 0, 5, true);

		asort($article_totals); // low to high
		$bottom_products = array_slice($article_totals, 0, 5, true);

		// Step 6: Fetch article names
		$articles = [];
		if (!empty($article_totals)) {
			$this->db->where_in('id', array_keys($article_totals));
			$article_data = $this->db->get('tbl_mould_parts')->result();
			foreach ($article_data as $row) {
				$articles[$row->id] = $row->article_name;
			}
		}

		// Step 7: Attach names
		$top_with_names = [];
		foreach ($top_products as $id => $qty) {
			$top_with_names[] = [
				'article_id' => $id,
				'article_name' => isset($articles[$id]) ? $articles[$id] : 'Unknown',
				'total_qty' => $qty
			];
		}

		$bottom_with_names = [];
		foreach ($bottom_products as $id => $qty) {
			$bottom_with_names[] = [
				'article_id' => $id,
				'article_name' => isset($articles[$id]) ? $articles[$id] : 'Unknown',
				'total_qty' => $qty
			];
		}


		// 6. New Dealers Added (party_type = 1), New Retailers Added (party_type = 2)
		$this->db->where('attending_salesperson_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('nature_of_business_id', 3); // hardcoded pass the nature of business id
		if ($from_date && $to_date) {
			$this->db->where('DATE(created_on) >=', $date_filter['from']);
			$this->db->where('DATE(created_on) <=', $date_filter['to']);
		}
		$new_dealers = $this->db->count_all_results('tbl_customers');

		$this->db->where('attending_salesperson_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('nature_of_business_id', 4); // hardcoded pass the nature of business id
		if ($from_date && $to_date) {
			$this->db->where('DATE(created_on) >=', $date_filter['from']);
			$this->db->where('DATE(created_on) <=', $date_filter['to']);
		}
		$new_retailers = $this->db->count_all_results('tbl_customers');

		// 7. Conversion Rate = (No of Orders / No of Visits) * 100
		$this->db->where('sales_person_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		if ($from_date && $to_date) {
			$this->db->where('DATE(date) >=', $date_filter['from']);
			$this->db->where('DATE(date) <=', $date_filter['to']);
		}
		$total_visits = $this->db->count_all_results('tbl_side_visit_details');
		$conversion_rate = $total_visits > 0 ? round(($total_orders / $total_visits) * 100, 2) : 0;

		// 8. Visits Target for the day/month
		$today = date('Y-m-d');
		$month = date('Y-m');
		$this->db->where('sales_person_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('DATE(date)', $today);
		$this->db->where_in('status_of_visit', ['1', '2', '3']);
		$visits_today = $this->db->count_all_results('tbl_side_visit_details');

		$this->db->where('sales_person_id', $sales_person_id);
		$this->db->where('is_deleted', '0');
		$this->db->where("DATE_FORMAT(date, '%Y-%m') = '$month'", NULL, FALSE);
		$this->db->where_in('status_of_visit', ['1', '2', '3']);
		$visits_month = $this->db->count_all_results('tbl_side_visit_details');

		// 9. Party History (if party_id provided)
		$party_history = [];
		if ($party_id) {
			$this->db->select('tbl_order_details.*, tbl_customers.party_name');
			$this->db->from('tbl_order_details');
			$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
			$this->db->where('tbl_order_details.party_id', $party_id);
			$this->db->where('tbl_order_details.is_deleted', '0');
			$this->db->order_by('tbl_order_details.id', 'DESC');
			$party_history = $this->db->get()->result();
		}

		// 10. Pending Orders List (with status and timeline)
		$pending_orders_list = [];
		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.sales_person_id', $sales_person_id);
		$this->db->where('tbl_order_details.is_deleted', '0');
		$this->db->where('tbl_order_details.order_status', '1');
		if ($from_date && $to_date) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', $date_filter['from']);
			$this->db->where('DATE(tbl_order_details.order_date) <=', $date_filter['to']);
		}
		$pending_orders_list = $this->db->get()->result();

		// 11. Scheduled Visits List (for today)
		// $scheduled_visits_list = [];
		// $this->db->select('tbl_side_visit_details.*, tbl_customers.party_name');
		// $this->db->from('tbl_side_visit_details');
		// $this->db->join('tbl_customers', 'tbl_side_visit_details.party_id = tbl_customers.id', 'left');
		// $this->db->where('tbl_side_visit_details.sales_person_id', $sales_person_id);
		// $this->db->where('tbl_side_visit_details.is_deleted', '0');
		// $this->db->where('DATE(tbl_side_visit_details.date)', $today);
		// $scheduled_visits_list = $this->db->get()->result();

		$json_arr = [
			'status' => 'true',
			'message' => 'Data retrieved successfully.',
			'data' => [
				'pending_tasks' => $pending_tasks,
				'scheduled_visits' => $scheduled_visits,
				'total_orders' => $total_orders,
				'pending_orders' => $pending_orders,
				'top_products' => $top_with_names,
				'bottom_products' => $bottom_with_names,
				'new_dealers' => $new_dealers,
				'new_retailers' => $new_retailers,
				'conversion_rate' => $conversion_rate,
				'visits_today' => $visits_today,
				'visits_month' => $visits_month,
				'visits_target_day' => 10,
				'visits_target_month' => 125,
				'party_history' => $party_history,
				'pending_orders_list' => $pending_orders_list,
				'pending_dispatch_count' => $pending_dispatch_count,
			]
		];
		echo json_encode($json_arr);
	}
	public function get_all_partys_order_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;

		$this->db->select('tbl_auto_task_list.*,tbl_customers.party_name');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_auto_task_list.order_department', '1');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_auto_task_list.date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date)', date('Y-m-d', strtotime($from_date)));
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_auto_task_list.party_id', $party_id);
		}
		$this->db->order_by('tbl_auto_task_list.id', 'DESC');
		$this->db->limit($limit, $offset);
		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Order retrieved successfully.';
			$json_arr['data'] = $result;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Order found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function get_party_order_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['party_id']) || empty($request['party_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid or missing Party ID.',
				'data' => []
			]);
			return;
		}

		$party_id = $request['party_id'];
		$status_filter = isset($request['status_filter']) ? trim((string)$request['status_filter']) : '';
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;

		$this->load->model('admin/Admin_model');

		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');
		$this->db->where('tbl_order_details.party_id', $party_id);
		$this->db->order_by('tbl_order_details.id', 'DESC');
		
		$result = $this->db->get()->result_array();
		$filtered_result = [];

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

		$paginated_result = array_slice($filtered_result, $offset, $limit);

		if (!empty($paginated_result)) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Order details retrieved successfully.',
				'data' => $paginated_result
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No orders found.',
				'data' => []
			]);
		}
	}
	public function get_order_list_page_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$type_of_order = isset($request['type_of_order']) ? trim($request['type_of_order']) : '';
		$order_status_filter = isset($request['order_status']) ? trim($request['order_status']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';

		$this->load->model('admin/Admin_model');

		$this->db->select('tbl_order_details.*, tbl_customers.party_name');
		$this->db->from('tbl_order_details');
		$this->db->join('tbl_customers', 'tbl_order_details.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_order_details.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_order_details.order_date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_order_details.order_date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_order_details.order_date)', date('Y-m-d', strtotime($from_date)));
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_order_details.party_id', $party_id);
		}
		if (!empty($type_of_order)) {
			$this->db->where('tbl_order_details.type_of_order', $type_of_order);
		}
		if (!empty($order_status_filter)) {
			$this->db->where('tbl_order_details.order_status', $order_status_filter);
		}

		if (!empty($search)) {
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

			$status_val = $type_val = false;
			foreach ($status_map as $label => $val) {
				if (strpos($label, $search_lower) !== false) {
					$status_val = $val;
					break;
				}
			}
			foreach ($type_map as $label => $val) {
				if (strpos($label, $search_lower) !== false) {
					$type_val = $val;
					break;
				}
			}

			$this->db->group_start();
			$this->db->or_like('tbl_order_details.order_id', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			if ($status_val !== false) {
				$this->db->or_where('tbl_order_details.order_status', $status_val);
			}
			if ($type_val !== false) {
				$this->db->or_where('tbl_order_details.type_of_order', $type_val);
			}
			if ($formatted_date) {
				$this->db->or_like('tbl_order_details.order_date', $formatted_date);
			} else {
				$this->db->or_like('tbl_order_details.order_date', $search);
			}
			$this->db->group_end();
		}

		$this->db->order_by('tbl_order_details.id', 'DESC');
		$this->db->limit($limit, $offset);
		$result = $this->db->get()->result_array();

		$formatted_result = [];

		foreach ($result as $row) {
			$type_text = 'Both';
			if ($row['type_of_order'] == '1') {
				$type_text = 'Household';
			} elseif ($row['type_of_order'] == '2') {
				$type_text = 'Container';
			}

			$ink_text = 'N/A';
			if ($row['ink_type'] == '1') {
				$ink_text = 'Plain';
			} elseif ($row['ink_type'] == '2') {
				$ink_text = 'Printing';
			}

			$order_status = $this->Admin_model->get_outward_order_status($row['order_id']);
			$auto_task_order = $this->db->get_where('tbl_auto_task_list', array('task_id' => $row['order_id']))->row();
			
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

			$dispatch_dates = '';
			$this->db->select("GROUP_CONCAT(DISTINCT DATE_FORMAT(created_on, '%d-%m-%Y') ORDER BY created_on DESC) as dispatch_dates");
			$this->db->from('tbl_dispatch_order_data');
			$this->db->where('order_id', $row['order_id']);
			$dispatch_result = $this->db->get()->row();
			if ($dispatch_result && $dispatch_result->dispatch_dates) {
				$dispatch_dates = $dispatch_result->dispatch_dates;
			}

			$can_edit = ($order_status == 'Pending');
			$can_cancel = ($order_status == 'Pending');
			$can_proceed_to_account = ($order_status == 'Pending');

			$formatted_result[] = [
				'id' => $row['id'],
				'order_id' => $row['order_id'],
				'order_date' => date('d-m-Y', strtotime($row['order_date'])),
				'party_name' => $row['party_name'],
				'party_id' => $row['party_id'],
				'selected_order_type' => $type_text,
				'container_type' => $ink_text,
				'dispatch_dates' => $dispatch_dates,
				'order_status_text' => $order_status,
				'action_states' => [
					'can_edit' => $can_edit,
					'can_cancel' => $can_cancel,
					'can_proceed_to_account' => $can_proceed_to_account,
					'order_stage' => $row['order_status']
				]
			];
		}

		if (!empty($formatted_result)) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Order list data retrieved successfully.',
				'data' => $formatted_result
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No orders matching filters.',
				'data' => []
			]);
		}
	}
	public function get_sub_order_details_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['order_id']) || !isset($request['order_type'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Missing order_id or order_type.',
				'data' => []
			]);
			return;
		}

		$order_id = $request['order_id'];
		$order_type = $request['order_type'];
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

				if (empty($dispatch_row) || empty($dispatch_row->dispatch_dates)) {
					$this->db->select('GROUP_CONCAT(DISTINCT DATE_FORMAT(created_on, "%d-%m-%Y") ORDER BY created_on ASC SEPARATOR ", ") as dispatch_dates', false);
					$this->db->from('tbl_dispatch_order_data');
					$this->db->where('is_deleted', '0');
					$this->db->where('order_id', $item['order_id']);
					$this->db->where('article_id', $item['article_id']);
					$dispatch_row = $this->db->get()->row();
				}

				$item['dispatch_dates'] = (!empty($dispatch_row) && !empty($dispatch_row->dispatch_dates)) ? $dispatch_row->dispatch_dates : '';

				if (!isset($item['bundle_bag_qty']) || $item['bundle_bag_qty'] === '' || $item['bundle_bag_qty'] === null) {
					$qty = floatval($item['order_quantity'] ?? 0);
					if ($qty > 0) {
						$item['bundle_bag_qty'] = rtrim(rtrim(number_format($qty / 120, 2, '.', ''), '0'), '.');
					} else {
						$item['bundle_bag_qty'] = '';
					}
				}
				
				$status_val = $item['order_status'] ?? '0';
				$status_text = 'Pending';
				if ($status_val == '1') {
					$status_text = 'Printing Completed';
				} else if ($status_val == '2') {
					$status_text = 'Cancelled';
				} else if ($status_val == '3') {
					$status_text = 'Partially Dispatched';
				} else if ($status_val == '4') {
					$status_text = 'Fully Dispatched';
				} else if ($status_val == '7') {
					$status_text = 'Printing Inprocess';
				} else if ($status_val == '8') {
					$status_text = 'Printing Completed';
				} else if ($status_val == '9') {
					$status_text = 'Dispatch Inprocess';
				} else if ($status_val == '10') {
					$status_text = 'Manually Closed';
				}
				$item['order_status_text'] = $status_text;
			}

			echo json_encode([
				'status' => 'true',
				'message' => 'Sub order details retrieved successfully.',
				'data' => $details
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No details found for this order.',
				'data' => []
			]);
		}
	}
	public function get_party_order_log_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['order_id']) || empty($request['order_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid or missing Order ID.',
				'data' => []
			]);
			return;
		}

		$order_id = $request['order_id'];
		$this->db->select('
			tbl_auto_task_list_history.*, 
			tbl_krivisha_department.department, 
			assign_to_table.first_name,
			last_updated_user.first_name as last_updated_name
		');
		$this->db->from('tbl_auto_task_list_history');
		$this->db->join('tbl_krivisha_department', 'tbl_krivisha_department.id = tbl_auto_task_list_history.department_id', 'left');
		$this->db->join('user_data as assign_to_table', 'assign_to_table.id = tbl_auto_task_list_history.assign_to_id', 'left');
		$this->db->join('user_data as last_updated_user', 'last_updated_user.id = tbl_auto_task_list_history.last_updated_by', 'left');
		$this->db->where('tbl_auto_task_list_history.is_deleted', '0');
		$this->db->where('tbl_auto_task_list_history.task_id', $order_id);
		$this->db->order_by('tbl_auto_task_list_history.id', 'DESC');

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			echo json_encode([
				'status' => 'true',
				'message' => 'Task history retrieved successfully.',
				'data' => $query->result()
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'No task history found.',
				'data' => []
			]);
		}
	}
	public function get_all_partys_order_count_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$party_id = isset($request['party_id']) ? $request['party_id'] : '';
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;

		$this->db->select('tbl_auto_task_list.party_id, tbl_customers.party_name, COUNT(tbl_auto_task_list.task_id) as task_count');
		$this->db->from('tbl_auto_task_list');
		$this->db->join('tbl_customers', 'tbl_auto_task_list.party_id = tbl_customers.id', 'left');
		$this->db->where('tbl_auto_task_list.order_department', '1');
		$this->db->where('tbl_auto_task_list.is_deleted', '0');

		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_auto_task_list.date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_auto_task_list.date)', date('Y-m-d', strtotime($from_date)));
		}

		if (!empty($party_id)) {
			$this->db->where('tbl_auto_task_list.party_id', $party_id);
		}

		$this->db->group_by('tbl_auto_task_list.party_id');

		$this->db->order_by('tbl_auto_task_list.party_id', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'Order retrieved successfully.';
			$json_arr['data'] = $result;
		} else {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'No Order found.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function update_user_profile_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!$request || !isset($request['user_id']) || empty($request['user_id'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid or missing User ID.',
			]);
			return;
		}

		$user_id = $request['user_id'];
		$image = isset($request['profile_images']) ? $request['profile_images'] : null;
		$first_name = isset($request['first_name']) ? $request['first_name'] : null;

		$data = [];
		$update_image = false;
		$image_filename = null;

		if ($image) {
			$data_decoded = base64_decode($image, true);

			if ($data_decoded !== false) {
				// Validate image
				$image_info = @getimagesizefromstring($data_decoded);
				if ($image_info) {
					$extension = image_type_to_extension($image_info[2]);
					$filename = 'profile_' . $user_id . $extension;
					$file_path = FCPATH . 'assets/images/' . $filename;

					// Save image to server
					if (file_put_contents($file_path, $data_decoded)) {
						$data['emp_photo'] = $filename;
						$image_filename = $filename;
						$update_image = true;
					} else {
						echo json_encode([
							'status' => 'false',
							'message' => 'Failed to save the profile image.',
						]);
						return;
					}
				} else {
					echo json_encode([
						'status' => 'false',
						'message' => 'Invalid image data.',
					]);
					return;
				}
			} else {
				echo json_encode([
					'status' => 'false',
					'message' => 'Failed to decode the base64 image.',
				]);
				return;
			}
		}

		if ($first_name) {
			$data['first_name'] = $first_name;
		}

		if (empty($data)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'No update data provided.',
			]);
			return;
		}

		$data['updated_on'] = date('Y-m-d H:i:s');

		$this->db->where('emp_id', $user_id);
		if ($this->db->update('user_data', $data)) {
			$image_path = $image_filename ? base_url('assets/images/' . $image_filename) : null;

			echo json_encode([
				'status' => 'true',
				'message' => 'Profile updated successfully.',
				'image_name' => $image_filename,
				'image_path' => $image_path
			]);
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'Failed to update profile in the database.',
			]);
		}
	}

	public function change_current_password_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (!isset($request['user_id']) || empty($request['user_id']) || !isset($request['new_password'])) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Missing required fields (user_id or new_password).',
			]);
			return;
		}
		$user_id = $request['user_id'];
		$new_password = $request['new_password'];

		$this->db->where('is_deleted', '0');
		$this->db->where('emp_id', $user_id);
		$user = $this->db->get('user_data')->row();

		if (!empty($user)) {
			$data = array(
				'org_password' => $new_password,
				'updated_on' => date('Y-m-d H:i:s'),
			);

			$this->db->where('id', $user->id);
			if ($this->db->update('user_data', $data)) {
				echo json_encode([
					'status' => 'true',
					'message' => 'Password updated successfully.',
				]);
			} else {
				echo json_encode([
					'status' => 'false',
					'message' => 'Failed to update password.',
				]);
			}
		} else {
			echo json_encode([
				'status' => 'false',
				'message' => 'User not found or deleted.',
			]);
		}
	}

	public function set_requested_raw_material_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$my_plant_id = isset($request['my_plant_id']) ? trim($request['my_plant_id']) : '';
		$employee_id = isset($request['employee_id']) ? trim($request['employee_id']) : '';
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';
		$machine_id = isset($request['machine_id']) ? trim($request['machine_id']) : '';
		$article_id = isset($request['article_id']) ? trim($request['article_id']) : '';
		$request_type = isset($request['request_type']) ? trim($request['request_type']) : '0';
		$request_date = date('Y-m-d');

		if (empty($request['sub_details'])) {
			$json_arr = [
				'status' => 'false',
				'message' => 'Material ID, Quantity, and Requested By are required.',
			];
			echo json_encode($json_arr);
			return;
		}
		$request_no = $this->generate_request_no();
		$data = array(
			'request_no' => $request_no,
			'request_date' => $request_date,
			'plant_id' => $plant_id,
			'machine_id' => $machine_id,
			'article_id' => $article_id,
			'request_type' => $request_type,
			'is_article_or_rm_material' => '0',
			'my_plant_id' => $my_plant_id,
			'employee_id' => $employee_id,
			'created_on' => date('Y-m-d H:i:s'),
		);

		$this->db->insert('tbl_rm_request_qty', $data);
		$database_request_id = $this->db->insert_id();
		if (!$database_request_id) {
			$json_arr = [
				'status' => 'false',
				'message' => 'Failed to create raw material request.',
			];
			echo json_encode($json_arr);
			return;
		}
		// Handle sub_details (array of raw materials)
		if (!empty($request['sub_details']) && is_array($request['sub_details'])) {
			foreach ($request['sub_details'] as $sub) {
				$raw_material_id = isset($sub['raw_material_id']) ? (int) $sub['raw_material_id'] : 0;
				$article_id = isset($sub['article_id']) ? (int) $sub['article_id'] : 0;
				$is_article_or_rm_material = isset($sub['is_article_or_rm_material']) ? trim($sub['is_article_or_rm_material']) : '0';
				$uom_id = isset($sub['uom_id']) ? (int) $sub['uom_id'] : 0;
				$request_quantity = isset($sub['qty']) ? (int) $sub['qty'] : 0;
				$remark = isset($sub['remark']) ? $sub['remark'] : '';

				if (($raw_material_id > 0 || $article_id > 0) && $request_quantity > 0) {
					$request_data = array(
						'request_no' => $request_no,
						'database_request_id' => $database_request_id,
						'raw_material_id' => $raw_material_id,
						'article_id' => $article_id,
						'uom_id' => $uom_id,
						'is_article_or_rm_material' => $is_article_or_rm_material,
						'request_quantity' => $request_quantity,
						'remark' => $remark,
						'plant_id' => $plant_id,
						'my_plant_id' => $my_plant_id,
						'created_on' => date('Y-m-d H:i:s'),
					);
					$this->db->insert('tbl_request_rm_qty_data', $request_data);
				}
			}
		}

		$json_arr = [
			'status' => 'true',
			'message' => 'Raw material request submitted successfully.',
			'request_no' => $request_no
		];


		echo json_encode($json_arr);
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

	public function get_all_material_qty_request_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';
		$request_status = isset($request['request_status']) ? trim($request['request_status']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
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
		// if ($according_plant == '1') {
		// 	$this->db->where('tbl_rm_request_qty.plant_id', $this->session->userdata('assign_plant_id'));
		// } else {
		// 	$this->db->where('tbl_rm_request_qty.my_plant_id', $this->session->userdata('assign_plant_id'));
		// }
		if (!empty($plant_id)) {
			$this->db->where('(tbl_rm_request_qty.plant_id = ' . $this->db->escape($plant_id) . ' OR tbl_rm_request_qty.my_plant_id = ' . $this->db->escape($plant_id) . ')');
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('tbl_rm_request_qty.request_no', $search);
			$this->db->or_like('plant1.plant_name', $search);
			$this->db->or_like('plant2.plant_name', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->group_end();
		}
		// Date filter
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_rm_request_qty.request_date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_rm_request_qty.request_date) <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('DATE(tbl_rm_request_qty.request_date)', date('Y-m-d', strtotime($from_date)));
		}

		if ($request_status != "") {
			$this->db->where('tbl_rm_request_qty.request_status', $request_status);
		}
		$this->db->order_by('tbl_rm_request_qty.id', 'DESC');
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$result = $query->result();
		if ($result) {
			foreach ($result as $key => $val) {
				$request_id = $val->id;
				$this->db->select('tbl_request_rm_qty_data.*, IF(tbl_request_rm_qty_data.is_article_or_rm_material = "1", tbl_mould_parts.article_name, tbl_rm_master.rm_name) as rm_name, tbl_uom_master.uom_name, user_data.first_name AS approved_by_name');
				$this->db->from('tbl_request_rm_qty_data');
				$this->db->join('tbl_rm_master', 'tbl_rm_master.id = tbl_request_rm_qty_data.raw_material_id', 'left');
				$this->db->join('tbl_uom_master', 'tbl_uom_master.id = tbl_request_rm_qty_data.uom_id', 'left');
				$this->db->join('tbl_mb_master', 'tbl_mb_master.id = tbl_request_rm_qty_data.master_batch_id', 'left');
				$this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_request_rm_qty_data.article_id', 'left');
				$this->db->join('user_data', 'user_data.id = tbl_request_rm_qty_data.approved_by', 'left');
				$this->db->where('tbl_request_rm_qty_data.database_request_id', $request_id);
				$this->db->where('tbl_request_rm_qty_data.is_deleted', '0');

				$sub_query = $this->db->get();
				$result_sub = $sub_query->result();
				$result[$key]->sub_details = $result_sub;
			}
		}
		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Request List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Request found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_raw_material_according_type()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$material_type = isset($request['material_type']) ? trim($request['material_type']) : '';
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';
		if (empty($material_type)) {
			$json_arr = [
				'status' => 'false',
				'message' => 'Material type is required.',
				'data' => []
			];
			echo json_encode($json_arr);
			return;
		}
		if ($material_type == '1') {
			$material_type = [10, 11, 12, 13, 14, 15, 16];
		} elseif ($material_type == '2') {
			$material_type = [18, 19, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32];
		} elseif ($material_type == '4') {
			$material_type = [17, 20];
		} else {
			$material_type = [5, 8];
		}
		$this->db->select('tbl_rm_master.*, tbl_rm_type.type,tbl_uom_master.uom_name');
		$this->db->from('tbl_rm_master');
		$this->db->join('tbl_rm_type', 'tbl_rm_master.type_id = tbl_rm_type.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_rm_master.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_rm_master.is_deleted', '0');
		if (!empty($material_type)) {
			$this->db->where_in('tbl_rm_master.type_id', $material_type);
		}
		if (!empty($plant_id)) {
			$this->db->where('tbl_rm_master.plant_id', $plant_id);
		}
		$result = $this->db->get();
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Raw Material retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Material found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_machine_according_plant_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$plant_id = isset($request['plant_id']) ? trim($request['plant_id']) : '';

		$this->db->select(['id', 'machine_name']);
		$this->db->where('is_deleted', '0');
		// $this->db->where_in('department_id', ['2', '3', '6', '14']);
		$this->db->where('status', '1');
		if (!empty($plant_id)) {
			$this->db->where('plant_id', $plant_id);
		}
		$result = $this->db->get('tbl_machine_master');
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'Machine retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No Machine found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_raw_material_according_article_bom_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$article_id = isset($request['article_id']) ? trim($request['article_id']) : '';

		$this->db->select('tbl_particulars_bom.*, tbl_rm_master.rm_name, tbl_uom_master.uom_name,tbl_particulars_bom.sub_category_id as raw_material_id');
		$this->db->from('tbl_particulars_bom');
		$this->db->join('tbl_rm_master', 'tbl_particulars_bom.sub_category_id = tbl_rm_master.id', 'left');
		$this->db->join('tbl_uom_master', 'tbl_particulars_bom.uom_id = tbl_uom_master.id', 'left');
		$this->db->where('tbl_particulars_bom.is_deleted', '0');
		$this->db->where('tbl_particulars_bom.article_id', $article_id);
		$result = $this->db->get();
		$result = $result->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'RM retrieved successfully according to article.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No RM found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
	}
	public function get_all_target_reached_visit_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$search = isset($request['search']) ? trim($request['search']) : '';
		$sales_id_for_party = isset($request['sales_id_for_party']) ? trim($request['sales_id_for_party']) : '';
		$type_of_visit_filter = isset($request['type_of_visit']) ? trim($request['type_of_visit']) : '';
		$party_id = isset($request['party_id']) ? trim($request['party_id']) : '';
		$search = isset($request['search']) ? trim($request['search']) : '';
		$from_date = isset($request['from_date']) ? trim($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? trim($request['to_date']) : '';

		$this->db->select('tbl_side_visit_details.*, tbl_customers.party_name,sales_person_table.first_name as sales_person , visit_created_table.first_name as visit_created_name');
		$this->db->from('tbl_side_visit_details');
		$this->db->join('tbl_customers', 'tbl_customers.id = tbl_side_visit_details.party_id', 'left');
		$this->db->join('user_data as sales_person_table', 'sales_person_table.id = tbl_side_visit_details.sales_person_id', 'left');
		$this->db->join('user_data as visit_created_table', 'visit_created_table.id = tbl_side_visit_details.employee_id', 'left');
		$this->db->where('tbl_side_visit_details.is_deleted', '0');
		$this->db->where_in('tbl_side_visit_details.status_of_visit', ['1', '2', '3', '4']);

		if (!empty($sales_id_for_party)) {
			$this->db->where('tbl_side_visit_details.sales_person_id', $sales_id_for_party);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('DATE(tbl_side_visit_details.date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(tbl_side_visit_details.date) <=', date('Y-m-d', strtotime($to_date)));
		} else if (!empty($from_date)) {
			$this->db->where('DATE(tbl_side_visit_details.date)', date('Y-m-d', strtotime($from_date)));
		} else {
			// ✅ Get current month records if from/to date not provided
			$first_day = date('Y-m-01'); // First day of current month
			$last_day = date('Y-m-t');  // Last day of current month
			$this->db->where('DATE(tbl_side_visit_details.date) >=', $first_day);
			$this->db->where('DATE(tbl_side_visit_details.date) <=', $last_day);
		}
		if (!empty($type_of_visit_filter)) {
			$this->db->where('tbl_side_visit_details.type_of_visit', $type_of_visit_filter);
		}
		if (!empty($party_id)) {
			$this->db->where('tbl_side_visit_details.party_id', $party_id);
		}
		if (!empty($search)) {
			$search_lower = strtolower(trim($search));
			$type_of_visit = [
				'physical visit' => '1',
				'telephonic meet' => '2',
				'supervisor/ sales head connect' => '3',
			];
			$visit_type = false;
			foreach ($type_of_visit as $label => $value) {
				if (strpos($label, $search_lower) !== false) {
					$visit_type = $value;
					break;
				}
			}
			$this->db->group_start();
			$this->db->like('tbl_side_visit_details.date', $search);
			$this->db->or_like('user_data.first_name', $search);
			$this->db->or_like('tbl_side_visit_details.time', $search);
			$this->db->or_like('tbl_customers.party_name', $search);
			if ($visit_type !== false) {
				$this->db->or_where('tbl_side_visit_details.type_of_visit', $visit_type);
			} else {
				$this->db->or_like('tbl_side_visit_details.type_of_visit', $search);
			}
			$this->db->or_like('tbl_side_visit_details.source_of_visit', $search);
			$this->db->group_end();
		}
		// Group by salesperson & date
		$this->db->group_by(['tbl_side_visit_details.sales_person_id', 'DATE(tbl_side_visit_details.date)']);

		$this->db->having('COUNT(tbl_side_visit_details.id) >=', 10);

		$this->db->order_by('tbl_side_visit_details.date', 'DESC');
		$this->db->limit($limit, $offset);

		$result = $this->db->get()->result();

		if (!empty($result)) {
			$json_arr = [
				'status' => 'true',
				'message' => 'List retrieved successfully.',
				'data' => $result
			];
		} else {
			$json_arr = [
				'status' => 'false',
				'message' => 'No details found.',
				'data' => []
			];
		}
		echo json_encode($json_arr);
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
				log_message('error', 'Service account file not found: ' . $serviceAccountPath);
				return;
			}
			$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
			$jwt = $this->generate_jwt($serviceAccount);

			$url = 'https://fcm.googleapis.com/v1/projects/' . $serviceAccount['project_id'] . '/messages:send';

			// ✅ Send to individual user
			if ($notification_according == '0') {
				$this->db->where('id', $employee_id_or_department_id);
				$this->db->where('is_deleted', '0');
				if ($plant_id) {
					$this->db->where('plant_id', $plant_id);
				}
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
			}
			// ✅ Send to all users in department
			else {
				if ($employee_id_or_department_id == 13) {
					$employee_id_or_department_id = [13, 18, 25]; // Production + Maintenance
				} else if ($employee_id_or_department_id == 50) { // 50 means order creation or updated  notification
					$employee_id_or_department_id = [11, 25]; // Account Order Creation && Super Admin
				} else if ($employee_id_or_department_id == 52) { // 52 means production report notification
					$employee_id_or_department_id = [11, 23, 25]; // salsman + Account + super admin
				} else if ($employee_id_or_department_id == 54) { // 54 means Pro Sch notification
					$employee_id_or_department_id = [11, 13, 24, 25]; // 13 = Maintenance Department, 11 = acc Department 24= store Dept 25= admin Dept 
				} else if ($employee_id_or_department_id == 56) { // 56 means logistics notification
					$employee_id_or_department_id = [11]; // 11 = acc Department 
				}

				if (!is_array($employee_id_or_department_id)) {
					$employee_id_or_department_id = [$employee_id_or_department_id];
				}

				$this->db->where_in('department_id', $employee_id_or_department_id);
				$this->db->where('is_deleted', '0');
				if ($plant_id) {
					$this->db->where('plant_id', $plant_id);
				}
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

	public function get_all_notification_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		$limit = isset($request['limit']) ? (int) $request['limit'] : 10;
		$offset = isset($request['offset']) ? (int) $request['offset'] : 0;
		$department_id = isset($request['department_id']) ? $request['department_id'] : '';
		$employee_id = isset($request['employee_id']) ? $request['employee_id'] : '';
		$plant_id = isset($request['plant_id']) ? $request['plant_id'] : '';

		
		$this->db->from('tbl_notifications');
		$this->db->where('is_deleted', '0');
		if ($department_id != 25) {
			if (!empty($department_id)) {
				$this->db->where("FIND_IN_SET(" . $this->db->escape($department_id) . ", notification_department) >", 0);
			}
			if (!empty($plant_id)) {
				$this->db->where('plant_id', $plant_id);
			}
		}
		$this->db->order_by('id', 'DESC');
		$all_notifications = $this->db->get()->result();

		$final_notifications = [];
		foreach ($all_notifications as $notification) {
			if (empty($notification->employee_id)) {
				$final_notifications[] = $notification;
			} elseif (!empty($employee_id) && $notification->employee_id == $employee_id) {
				$final_notifications[] = $notification;
			}
		}
		$total = count($final_notifications);
    	$paged_notifications = array_slice($final_notifications, $offset, $limit);
		if (empty($paged_notifications)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'No notifications found',
				'result' => []
			]);
		} else {
			echo json_encode([
				'status' => 'true',
				'message' => 'Notification list fetched successfully',
				'result' => $paged_notifications
			]);
		}
	}

	// 	public function get_all_notification_list_api()
// {
//     $request = json_decode(file_get_contents('php://input'), true);

	//     $limit = isset($request['limit']) ? (int) $request['limit'] : 10;
//     $offset = isset($request['offset']) ? (int) $request['offset'] : 0;
//     $department_id = isset($request['department_id']) ? $request['department_id'] : '';
//     $employee_id = isset($request['employee_id']) ? $request['employee_id'] : '';
//     $plant_id = isset($request['plant_id']) ? $request['plant_id'] : '';

	//     // Base
//     $this->db->from('tbl_notifications');
//     $this->db->where('is_deleted', '0');

	//     // If plant provided, filter by plant
//     if (!empty($plant_id)) {
//         $this->db->where('plant_id', $plant_id);
//     }

	//     // If department_id is 25 (special case in your code) -> show all notifications for the plant
//     // Otherwise show notifications that are either:
//     //  - Assigned to this employee (employee_id matches) OR
//     //  - Contain this department in notification_department (FIND_IN_SET)
//     if ($department_id != 25) {
//         // Only add the OR group if we have either employee_id or department_id set
//         $this->db->group_start();

	//         if (!empty($employee_id)) {
//             // notifications specifically for this employee
//             $this->db->or_where('employee_id', $employee_id);
//         }

	//         if (!empty($department_id)) {
//             // notifications for this department (use raw where and FALSE to avoid CI escaping)
//             // This produces: FIND_IN_SET('24', notification_department) > 0
//             $dept = $this->db->escape_str($department_id);
//             $this->db->or_where("FIND_IN_SET('{$dept}', notification_department) > 0", null, FALSE);
//         }

	//         $this->db->group_end();
//     }

	//     $this->db->order_by('id', 'DESC');
//     $this->db->limit($limit, $offset);

	//     $result = $this->db->get()->result();

	//     if (empty($result)) {
//         echo json_encode([
//             'status' => 'false',
//             'message' => 'No notifications found',
//             'result' => []
//         ]);
//     } else {
//         echo json_encode([
//             'status' => 'true',
//             'message' => 'Notification list fetched successfully',
//             'result' => $result
//         ]);
//     }
// }
	public function check_unique_dc_no_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$challan_dc_no = $request['challan_dc_no'];
		$id = $request['id'];
		if (empty($challan_dc_no)) {
			echo json_encode(['status' => 'false', 'message' => 'challan_dc_no  is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('challan_dc_no', $challan_dc_no);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'challan dc no already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'challan dc no is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function check_unique_invoice_no_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$id = $request['id'];
		$invoice_no = $request['invoice_no'];
		if (empty($invoice_no)) {
			echo json_encode(['status' => 'false', 'message' => 'invoice_no is required']);
			return;
		}
		$this->db->where('is_deleted', '0');
		$this->db->where('invoice_no', $invoice_no);
		if ($id != "0") {
			$this->db->where('id !=', $id);
		}
		$result = $this->db->get('tbl_own_vehicle_details')->row();
		if (!empty($result)) {
			$json_arr['status'] = 'false';
			$json_arr['message'] = 'invoice no already exists.';
			$json_arr['data'] = [];
		} else {
			$json_arr['status'] = 'true';
			$json_arr['message'] = 'invoice no is available.';
			$json_arr['data'] = [];
		}
		echo json_encode($json_arr);
	}
	public function set_device_details()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'Invalid request'
			]);
			return;
		}

		$user_id = isset($request['user_id']) ? $request['user_id'] : '';
		$app_version = isset($request['app_version']) ? $request['app_version'] : '';
		$fcm_token = isset($request['fcm_token']) ? $request['fcm_token'] : '';
		$device_id = isset($request['device_id']) ? $request['device_id'] : '';

		$device_details = isset($request['device_details']) ? json_encode($request['device_details']) : null;
		$permission_details = isset($request['permission_details']) ? json_encode($request['permission_details']) : null;

		if (empty($user_id) || empty($device_id)) {
			echo json_encode([
				'status' => 'false',
				'message' => 'User ID and Device ID are required'
			]);
			return;
		}

		$this->db->where('id', $user_id);
		$existing = $this->db->get('user_data')->row();

		$data = [
			'app_version' => $app_version,
			'device_id' => $device_id,
			'device_details' => $device_details,
			'push_token' => $fcm_token,
			'updated_on' => date('Y-m-d H:i:s')
		];

		$this->db->where('id', $existing->id);
		$this->db->update('user_data', $data);
		$message = 'Device details updated successfully';

		echo json_encode([
			'status' => 'true',
			'message' => $message
		]);
	}
	public function logout(){
        try {
            $request = json_decode(file_get_contents('php://input'), true);

            if (empty($request)) {
                $response = [
                    'status'  => false,
                    'error'   => 'Invalid request payload',
                    'message' => 'Request not found. Please try again.',
                    'data'    => null
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode($response))
                    ->_display();
                exit;
            }

            if (empty($request['device_id']) || empty($request['user_id'])) {
                $response = [
                    'status'  => false,
                    'error'   => 'Missing parameters',
                    'message' => 'App User ID, Device ID are required.',
                    'data'    => null
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode($response))
                    ->_display();
                exit;
            }

            $device_id = $request['device_id'];
            $app_panel_user_id = $request['user_id'];

            $this->db->where('id', $app_panel_user_id);
            $this->db->where('device_id', $device_id);
            $this->db->where('is_deleted', '0');
            $exist = $this->db->get('user_data')->row();

            if (empty($exist)) {
                $response = [
                    'status'  => false,
                    'error'   => 'Invalid logout request',
                    'message' => 'Login device not found',
                    'data'    => null
                ];

                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(404)
                    ->set_output(json_encode($response))
                    ->_display();
                exit;
            }else{
			$this->db->where('id', $exist->id);
            $this->db->update('user_data', ['is_active_login' => '0', 'push_token' =>'', 'last_logout_on' => date('Y-m-d H:i:s')]);

            $response = [
                'status'  => true,
                'error'   => null,
                'message' => 'Successfully logged out from device',
                'data'    => null
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response))
                ->_display();
			} 

        } catch (Exception $e) {

            $response = [
                'status'  => false,
                'error'   => $e->getMessage(),
                'message' => 'Failed to logout user',
                'data'    => null
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_status_header(500)
                ->set_output(json_encode($response))
                ->_display();
        }

        exit;
    }



	// =============================================
	// P1 - New Android API Model Methods
	// =============================================

	/**
	 * GET all employees with their multi-plant and multi-department CSV values.
	 * Android uses this to show assigned plant/dept info per employee.
	 */
	public function get_all_employees_v2_api()
	{
		$this->db->select('id, first_name, email, mobile_number, department_id, plant_id, designation, date_of_joininig');
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('user_data')->result();
		echo json_encode([
			'status'  => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'Employees fetched successfully.' : 'No employees found.',
			'data'    => $result ?: []
		]);
	}

	/**
	 * GET all party/customer records including full 4-layer sales hierarchy.
	 * Includes dg_id, asm_id, state_head_id, telecaller_id and their names.
	 */
	public function get_all_party_v2_api()
	{
		$this->db->select('
			tbl_customers.*,
			dg_emp.first_name   AS dg_name,
			asm_emp.first_name  AS asm_name,
			sh_emp.first_name   AS state_head_name,
			tc_emp.first_name   AS telecaller_name
		');
		$this->db->from('tbl_customers');
		$this->db->join('user_data AS dg_emp',  'dg_emp.id  = tbl_customers.dg_id',         'left');
		$this->db->join('user_data AS asm_emp', 'asm_emp.id = tbl_customers.asm_id',         'left');
		$this->db->join('user_data AS sh_emp',  'sh_emp.id  = tbl_customers.state_head_id',  'left');
		$this->db->join('user_data AS tc_emp',  'tc_emp.id  = tbl_customers.telecaller_id',  'left');
		$this->db->where('tbl_customers.is_deleted', '0');
		$result = $this->db->get()->result();
		echo json_encode([
			'status'  => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'Party list fetched successfully.' : 'No parties found.',
			'data'    => $result ?: []
		]);
	}

	/**
	 * GET Production BOM including std_cycle_time and std_weight for a given article_id.
	 * Accepts POST/GET: article_id
	 */
	public function get_bom_specs_api()
	{
		$request    = json_decode(file_get_contents('php://input'), true);
		$article_id = isset($request['article_id']) ? trim($request['article_id']) : $this->input->get('article_id');

		if (empty($article_id)) {
			echo json_encode(['status' => 'false', 'message' => 'article_id is required.', 'data' => []]);
			return;
		}

		$this->db->where('article_id', $article_id);
		$this->db->where('is_deleted', '0');
		$result = $this->db->get('tbl_production_bom')->result();

		echo json_encode([
			'status'  => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'BOM specs fetched successfully.' : 'No BOM found.',
			'data'    => $result ?: []
		]);
	}

	/**
	 * GET all plant and department master data for mobile employee form dropdowns.
	 */
	public function get_masters_for_employee_api()
	{
		$this->db->where('is_deleted', '0');
		$plants = $this->db->get('tbl_plant_master')->result();

		$this->db->where('is_deleted', '0');
		$departments = $this->db->get('tbl_krivisha_department')->result();

		echo json_encode([
			'status'      => 'true',
			'message'     => 'Master data fetched successfully.',
			'plants'      => $plants ?: [],
			'departments' => $departments ?: []
		]);
	}

	public function set_spc_part_weight_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		if (empty($request)) {
			echo json_encode(['status' => 'false', 'message' => 'No data received']);
			return;
		}

		$data = [
			'production_id'   => $request['production_id'] ?? null,
			'department_id'   => $request['department_id'] ?? null,
			'machine_name'    => $request['machine_name'] ?? '',
			'production_date' => !empty($request['production_date']) ? date('Y-m-d', strtotime($request['production_date'])) : null,
			'shift'           => $request['shift'] ?? '',
			'check_time'      => $request['check_time'] ?? '',
			'part_article'    => $request['part_article'] ?? '',
			'std_wt'          => $request['std_wt'] ?? null,
			'lsl'             => $request['lsl'] ?? null,
			'usl'             => $request['usl'] ?? null,
			's1'              => $request['s1'] ?? null,
			's2'              => $request['s2'] ?? null,
			's3'              => $request['s3'] ?? null,
			's4'              => $request['s4'] ?? null,
			's5'              => $request['s5'] ?? null,
			'avg'             => $request['avg'] ?? null,
			'status'          => $request['status'] ?? '',
			'employee_id'     => $request['employee_id'] ?? null,
			'plant_id'        => $request['plant_id'] ?? null,
			'created_on'      => date('Y-m-d H:i:s'),
		];

		$this->db->insert('tbl_spc_part_weight', $data);
		echo json_encode(['status' => 'true', 'message' => 'SPC data saved successfully']);
	}

	// =============================================
	// SPC Part Weight – Get List (GET/POST)
	// Filters: production_id, department_id, machine_name, plant_id,
	//          employee_id, shift, from_date, to_date, search
	// Supports pagination via limit / offset.
	// Results are grouped by production session (production_id + machine + date + shift)
	// with articles nested as an array inside each group.
	// =============================================
	public function get_spc_part_weight_list_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		if (empty($request)) {
			$request = [];
		}

		$limit         = isset($request['limit'])         ? (int)$request['limit']              : 10;
		$offset        = isset($request['offset'])        ? (int)$request['offset']             : 0;
		$production_id = isset($request['production_id']) ? trim($request['production_id'])     : '';
		$department_id = isset($request['department_id']) ? trim($request['department_id'])     : '';
		$machine_name  = isset($request['machine_name'])  ? trim($request['machine_name'])      : '';
		$plant_id      = isset($request['plant_id'])      ? trim($request['plant_id'])          : '';
		$employee_id   = isset($request['employee_id'])   ? trim($request['employee_id'])       : '';
		$shift         = isset($request['shift'])         ? trim($request['shift'])             : '';
		$from_date     = isset($request['from_date'])     ? trim($request['from_date'])         : '';
		$to_date       = isset($request['to_date'])       ? trim($request['to_date'])           : '';
		$search        = isset($request['search'])        ? trim($request['search'])            : '';

		$this->db->select('
			spc.*,
			ud.first_name  AS employee_name,
			pm.plant_name
		');
		$this->db->from('tbl_spc_part_weight spc');
		$this->db->join('user_data ud',       'ud.id  = spc.employee_id', 'left');
		$this->db->join('tbl_plant_master pm', 'pm.id = spc.plant_id',    'left');
		$this->db->where('spc.is_deleted', '0');

		if (!empty($production_id)) {
			$this->db->where('spc.production_id', $production_id);
		}
		if (!empty($department_id)) {
			$this->db->where('spc.department_id', $department_id);
		}
		if (!empty($machine_name)) {
			$this->db->like('spc.machine_name', $machine_name);
		}
		if (!empty($plant_id)) {
			$this->db->where('spc.plant_id', $plant_id);
		}
		if (!empty($employee_id)) {
			$this->db->where('spc.employee_id', $employee_id);
		}
		if (!empty($shift)) {
			$this->db->where('spc.shift', $shift);
		}
		if (!empty($from_date) && !empty($to_date)) {
			$this->db->where('spc.production_date >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('spc.production_date <=', date('Y-m-d', strtotime($to_date)));
		} elseif (!empty($from_date)) {
			$this->db->where('spc.production_date', date('Y-m-d', strtotime($from_date)));
		}
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('spc.machine_name',  $search);
			$this->db->or_like('spc.part_article', $search);
			$this->db->or_like('spc.shift',        $search);
			$this->db->or_like('spc.check_time',   $search);
			$this->db->or_like('spc.production_date', $search);
			$this->db->or_like('ud.first_name',    $search);
			$this->db->or_like('pm.plant_name',    $search);
			$this->db->group_end();
		}

		$this->db->order_by('spc.id', 'DESC');
		$this->db->limit($limit, $offset);

		$rows = $this->db->get()->result();

		// ── Group flat rows into sessions ──────────────────────────────────────
		// Key: production_id + machine_name + production_date + shift + check_time
		$sessions = [];
		foreach ($rows as $row) {
			$key = $row->production_id . '|' . $row->machine_name . '|'
			     . $row->production_date . '|' . $row->shift . '|' . $row->check_time;

			if (!isset($sessions[$key])) {
				$sessions[$key] = [
					'production_id'   => $row->production_id,
					'department_id'   => $row->department_id,
					'machine_name'    => $row->machine_name,
					'production_date' => $row->production_date,
					'shift'           => $row->shift,
					'check_time'      => $row->check_time,
					'employee_id'     => $row->employee_id,
					'employee_name'   => $row->employee_name,
					'plant_id'        => $row->plant_id,
					'plant_name'      => $row->plant_name,
					'articles'        => []
				];
			}

			$sessions[$key]['articles'][] = [
				'id'          => $row->id,
				'part_article'=> $row->part_article,
				'std_wt'      => $row->std_wt,
				'lsl'         => $row->lsl,
				'usl'         => $row->usl,
				's1'          => $row->s1,
				's2'          => $row->s2,
				's3'          => $row->s3,
				's4'          => $row->s4,
				's5'          => $row->s5,
				'avg'         => $row->avg,
				'status'      => $row->status,
				'created_on'  => $row->created_on,
			];
		}

		$result = array_values($sessions);

		if (!empty($result)) {
			http_response_code(200);
			echo json_encode([
				'status'  => 'true',
				'message' => 'SPC Part Weight list retrieved successfully.',
				'count'   => count($result),
				'data'    => $result
			]);
		} else {
			http_response_code(200);
			echo json_encode([
				'status'  => 'false',
				'message' => 'No records found.',
				'count'   => 0,
				'data'    => []
			]);
		}
	}

	// =============================================
	// SPC Part Weight – Add (PUT)
	// Accepts an articles[] array — inserts one row per article.
	// Articles with all empty weight fields are skipped silently.
	// =============================================
	public function add_spc_part_weight_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'No data received']);
			return;
		}

		// Validate header-level required fields
		$required_header = ['production_id', 'department_id', 'machine_name',
		                    'production_date', 'shift', 'check_time', 'employee_id', 'plant_id'];
		foreach ($required_header as $field) {
			if (empty($request[$field])) {
				http_response_code(400);
				echo json_encode(['status' => 'false', 'message' => "Missing required field: $field"]);
				return;
			}
		}

		if (empty($request['articles']) || !is_array($request['articles'])) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'Missing or invalid articles array']);
			return;
		}

		$production_date = date('Y-m-d', strtotime($request['production_date']));
		$now             = date('Y-m-d H:i:s');
		$inserted_ids    = [];
		$skipped         = [];

		foreach ($request['articles'] as $article) {
			// Skip articles where all measurement fields are empty
			$weight_fields = ['std_wt', 'lsl', 'usl', 's1', 's2', 's3', 's4', 's5'];
			$all_empty = true;
			foreach ($weight_fields as $wf) {
				if (isset($article[$wf]) && $article[$wf] !== '') {
					$all_empty = false;
					break;
				}
			}
			if ($all_empty) {
				$skipped[] = $article['article_name'] ?? 'unknown';
				continue;
			}

			$data = [
				'production_id'   => $request['production_id'],
				'department_id'   => $request['department_id'],
				'machine_name'    => $request['machine_name'],
				'production_date' => $production_date,
				'shift'           => $request['shift'],
				'check_time'      => $request['check_time'],
				'part_article'    => $article['article_name'] ?? '',
				'std_wt'          => ($article['std_wt'] !== '') ? $article['std_wt'] : null,
				'lsl'             => ($article['lsl']    !== '') ? $article['lsl']    : null,
				'usl'             => ($article['usl']    !== '') ? $article['usl']    : null,
				's1'              => ($article['s1']     !== '') ? $article['s1']     : null,
				's2'              => ($article['s2']     !== '') ? $article['s2']     : null,
				's3'              => ($article['s3']     !== '') ? $article['s3']     : null,
				's4'              => ($article['s4']     !== '') ? $article['s4']     : null,
				's5'              => ($article['s5']     !== '') ? $article['s5']     : null,
				'avg'             => ($article['avg']    !== '') ? $article['avg']    : null,
				'status'          => $article['status']  ?? '',
				'employee_id'     => $request['employee_id'],
				'plant_id'        => $request['plant_id'],
				'is_deleted'      => '0',
				'created_on'      => $now,
			];

			$this->db->insert('tbl_spc_part_weight', $data);
			$inserted_ids[] = $this->db->insert_id();
		}

		if (empty($inserted_ids)) {
			http_response_code(400);
			echo json_encode([
				'status'  => 'false',
				'message' => 'No articles with data were submitted.',
				'skipped' => $skipped
			]);
			return;
		}

		http_response_code(201);
		echo json_encode([
			'status'      => 'true',
			'message'     => 'SPC Part Weight records added successfully.',
			'inserted_ids' => $inserted_ids,
			'skipped'     => $skipped
		]);
	}

	// =============================================
	// SPC Part Weight – Update (PUT)
	// Accepts an articles[] array — each item must have a record_id.
	// =============================================
	public function update_spc_part_weight_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'No data received']);
			return;
		}

		if (empty($request['articles']) || !is_array($request['articles'])) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'Missing or invalid articles array']);
			return;
		}

		$updated  = [];
		$skipped  = [];
		$not_found = [];

		foreach ($request['articles'] as $article) {
			// Skip articles with no record_id (new records should use add API)
			if (empty($article['record_id']) || $article['record_id'] === 'NEW') {
				$skipped[] = $article['article_name'] ?? 'unknown';
				continue;
			}

			// Verify record exists
			$this->db->where('id', (int)$article['record_id']);
			$this->db->where('is_deleted', '0');
			$existing = $this->db->get('tbl_spc_part_weight')->row();

			if (empty($existing)) {
				$not_found[] = $article['record_id'];
				continue;
			}

			// Build update payload
			$updatable = ['std_wt', 'lsl', 'usl', 's1', 's2', 's3', 's4', 's5', 'avg', 'status', 'part_article'];
			$data = [];
			foreach ($updatable as $field) {
				if (array_key_exists($field, $article)) {
					$data[$field] = ($article[$field] !== '') ? $article[$field] : null;
				}
			}

			// Allow updating header fields per-row if provided
			$header_fields = ['production_id', 'department_id', 'machine_name',
			                  'shift', 'check_time', 'employee_id', 'plant_id'];
			foreach ($header_fields as $field) {
				if (!empty($request[$field])) {
					$data[$field] = $request[$field];
				}
			}
			if (!empty($request['production_date'])) {
				$data['production_date'] = date('Y-m-d', strtotime($request['production_date']));
			}

			if (empty($data)) {
				$skipped[] = $article['article_name'] ?? 'unknown';
				continue;
			}

			$this->db->where('id', (int)$article['record_id']);
			$this->db->update('tbl_spc_part_weight', $data);
			$updated[] = (int)$article['record_id'];
		}

		if (empty($updated)) {
			http_response_code(400);
			echo json_encode([
				'status'    => 'false',
				'message'   => 'No records were updated.',
				'skipped'   => $skipped,
				'not_found' => $not_found
			]);
			return;
		}

		http_response_code(200);
		echo json_encode([
			'status'    => 'true',
			'message'   => 'SPC Part Weight records updated successfully.',
			'updated'   => $updated,
			'skipped'   => $skipped,
			'not_found' => $not_found
		]);
	}

	// =============================================
	// Process Parameter Sheet – Add (PUT)
	// =============================================
	public function add_process_parameter_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$table_name = $this->_process_parameter_table_name();

		if (empty($request)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'No data received']);
			return;
		}

		if (empty($table_name)) {
			http_response_code(500);
			echo json_encode(['status' => 'false', 'message' => 'Process parameter table is missing.']);
			return;
		}

		$required = ['schedule_id', 'machine_name', 'article_name', 'production_date', 'employee_id', 'plant_id'];
		foreach ($required as $field) {
			if (empty($request[$field])) {
				http_response_code(400);
				echo json_encode(['status' => 'false', 'message' => "Missing required field: $field"]);
				return;
			}
		}

		$data = $this->_build_process_parameter_data($request);
		$data['is_deleted'] = '0';
		$data['created_on'] = date('Y-m-d H:i:s');

		$this->db->insert($table_name, $data);
		$insert_id = $this->db->insert_id();

		http_response_code(201);
		echo json_encode([
			'status'  => 'true',
			'message' => 'Process Parameter Sheet saved successfully.',
			'id'      => $insert_id
		]);
	}

	// =============================================
	// Process Parameter Sheet – Update (PUT)
	// =============================================
	public function update_process_parameter_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$table_name = $this->_process_parameter_table_name();

		if (empty($request) || empty($request['id'])) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'Missing required field: id']);
			return;
		}

		if (empty($table_name)) {
			http_response_code(500);
			echo json_encode(['status' => 'false', 'message' => 'Process parameter table is missing.']);
			return;
		}

		$this->db->where('id', (int)$request['id'])->where('is_deleted', '0');
		$existing = $this->db->get($table_name)->row();
		if (empty($existing)) {
			http_response_code(404);
			echo json_encode(['status' => 'false', 'message' => 'Record not found.']);
			return;
		}

		$data = $this->_build_process_parameter_data($request);
		if (empty($data)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'No fields to update.']);
			return;
		}

		$this->db->where('id', (int)$request['id']);
		$this->db->update($table_name, $data);

		http_response_code(200);
		echo json_encode(['status' => 'true', 'message' => 'Process Parameter Sheet updated successfully.']);
	}

	// =============================================
	// Process Parameter Sheet – Get (POST)
	// Filters: id (single), schedule_id, machine_name,
	//          plant_id, employee_id, from_date, to_date
	// =============================================
	public function get_process_parameter_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$table_name = $this->_process_parameter_table_name();
		if (empty($request)) $request = [];

		if (empty($table_name)) {
			http_response_code(500);
			echo json_encode(['status' => 'false', 'message' => 'Process parameter table is missing.', 'data' => []]);
			return;
		}

		// Single record fetch
		if (!empty($request['id'])) {
			$this->db->select('pp.*, ud.first_name AS employee_name, pm.plant_name');
			$this->db->from($table_name . ' pp');
			$this->db->join('user_data ud',        'ud.id = pp.employee_id', 'left');
			$this->db->join('tbl_plant_master pm', 'pm.id = pp.plant_id',   'left');
			$this->db->where('pp.id', (int)$request['id']);
			$this->db->where('pp.is_deleted', '0');
			$row = $this->db->get()->row();
			if ($row) {
				http_response_code(200);
				echo json_encode(['status' => 'true', 'message' => 'Record found.', 'data' => $row]);
			} else {
				http_response_code(404);
				echo json_encode(['status' => 'false', 'message' => 'Record not found.', 'data' => null]);
			}
			return;
		}

		// List fetch
		$limit       = isset($request['limit'])       ? (int)$request['limit']          : 20;
		$offset      = isset($request['offset'])      ? (int)$request['offset']         : 0;
		$schedule_id = isset($request['schedule_id']) ? trim($request['schedule_id'])   : '';
		$machine     = isset($request['machine_name'])? trim($request['machine_name'])  : '';
		$plant_id    = isset($request['plant_id'])    ? trim($request['plant_id'])      : '';
		$employee_id = isset($request['employee_id']) ? trim($request['employee_id'])   : '';
		$from_date   = isset($request['from_date'])   ? trim($request['from_date'])     : '';
		$to_date     = isset($request['to_date'])     ? trim($request['to_date'])       : '';

		$this->db->select('pp.*, ud.first_name AS employee_name, pm.plant_name');
		$this->db->from($table_name . ' pp');
		$this->db->join('user_data ud',        'ud.id = pp.employee_id', 'left');
		$this->db->join('tbl_plant_master pm', 'pm.id = pp.plant_id',   'left');
		$this->db->where('pp.is_deleted', '0');

		if (!empty($schedule_id)) $this->db->where('pp.schedule_id', $schedule_id);
		if (!empty($machine))     $this->db->like('pp.machine_name', $machine);
		if (!empty($plant_id))    $this->db->where('pp.plant_id', $plant_id);
		if (!empty($employee_id)) $this->db->where('pp.employee_id', $employee_id);
		if (!empty($from_date))   $this->db->where('pp.production_date >=', date('Y-m-d', strtotime($from_date)));
		if (!empty($to_date))     $this->db->where('pp.production_date <=', date('Y-m-d', strtotime($to_date)));

		$this->db->order_by('pp.id', 'DESC');
		$this->db->limit($limit, $offset);
		$rows = $this->db->get()->result();

		http_response_code(200);
		echo json_encode([
			'status'  => !empty($rows) ? 'true' : 'false',
			'message' => !empty($rows) ? 'Records found.' : 'No records found.',
			'count'   => count($rows),
			'data'    => $rows
		]);
	}

	// =============================================
	// Set Notification API
	// Called by Android app after any action.
	// Saves to tbl_notifications and pushes FCM.
	// Payload: plant_id, notification_title, notification_description,
	//          notification_department, landing_page, order_id,
	//          status, employee_id (empty string = send to all in dept)
	// =============================================
	public function set_notification_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'No data received']);
			return;
		}

		$title          = $request['notification_title']       ?? '';
		$description    = $request['notification_description'] ?? '';
		$department     = $request['notification_department']  ?? '';
		$landing_page   = $request['landing_page']             ?? '';
		$order_id       = $request['order_id']                 ?? '';
		$plant_id       = $request['plant_id']                 ?? null;
		$employee_id    = $request['employee_id']              ?? '';  // '' = ALL in dept

		if (empty($title)) {
			http_response_code(400);
			echo json_encode(['status' => 'false', 'message' => 'notification_title is required']);
			return;
		}

		// ── Save to tbl_notifications ──────────────────────────────────────
		$notification_data = [
			'notification_title'       => $title,
			'notification_description' => $description,
			'notification_department'  => $department,
			'landing_page'             => $landing_page,
			'order_id'                 => $order_id,
			'plant_id'                 => $plant_id,
			'created_on'               => date('Y-m-d H:i:s'),
		];

		// If a specific employee is targeted, store it
		if (!empty($employee_id)) {
			$notification_data['employee_id'] = $employee_id;
		}

		$this->db->insert('tbl_notifications', $notification_data);

		// ── Push FCM ───────────────────────────────────────────────────────
		if (!empty($employee_id)) {
			// Send to specific employee
			$this->send_task_notification_by_token(
				(int)$employee_id, $title, $description, $landing_page, '0', $plant_id
			);
		} else {
			// Send to all employees in the department
			// Use department id as the broadcast key (notification_according = '1')
			$dept_id = !empty($department) ? (int)$department : 25;
			$this->send_task_notification_by_token(
				$dept_id, $title, $description, $landing_page, '1', $plant_id
			);
		}

		http_response_code(200);
		echo json_encode([
			'status'  => 'true',
			'message' => 'Notification sent successfully.'
		]);
	}

	// ─── Operator APIs ───────────────────────────────────────────────────────

	/**
	 * GET: Fetch all operators (designation = OPERATOR)
	 * POST: Add a new operator
	 * PUT: Update an existing operator
	 * Endpoint: /operators_api
	 */
	public function operators_api()
	{
		$method = $_SERVER['REQUEST_METHOD'];

		// ── GET: fetch all operators ──────────────────────────────────────
		if ($method === 'GET') {
			$plant_id  = $this->input->get('plant_id');
			$search    = $this->input->get('search');

			$this->db->select('id, emp_id, first_name, email, mobile_number, department_id, plant_id, designation, date_of_joininig, created_on');
			$this->db->where('is_deleted', '0');
			$this->db->where('UPPER(designation)', 'OPERATOR');

			if (!empty($plant_id)) {
				$this->db->where('plant_id', $plant_id);
			}
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('first_name', $search);
				$this->db->or_like('mobile_number', $search);
				$this->db->or_like('emp_id', $search);
				$this->db->group_end();
			}

			$this->db->order_by('first_name', 'ASC');
			$result = $this->db->get('user_data')->result();

			echo json_encode([
				'status'  => !empty($result) ? 'true' : 'false',
				'message' => !empty($result) ? 'Operators fetched successfully.' : 'No operators found.',
				'data'    => $result ?: []
			]);
			return;
		}

		$request = json_decode(file_get_contents('php://input'), true);
		if (empty($request)) {
			$request = $_POST;
		}

		// ── POST: add new operator ────────────────────────────────────────
		if ($method === 'POST') {
			$first_name    = isset($request['first_name'])    ? trim($request['first_name'])    : '';
			$mobile_number = isset($request['mobile_number']) ? trim($request['mobile_number']) : '';
			$email         = isset($request['email'])         ? trim($request['email'])         : '';
			$plant_id      = isset($request['plant_id'])      ? trim($request['plant_id'])      : '';
			$department_id = isset($request['department_id']) ? trim($request['department_id']) : '';
			$joining_date  = isset($request['joining_date'])  ? trim($request['joining_date'])  : '';

			if (empty($first_name)) {
				echo json_encode(['status' => 'false', 'message' => 'first_name is required.']);
				return;
			}
			if (empty($mobile_number)) {
				echo json_encode(['status' => 'false', 'message' => 'mobile_number is required.']);
				return;
			}

			// Check duplicate mobile
			$exists = $this->db->where('mobile_number', $mobile_number)
				->where('is_deleted', '0')
				->get('user_data')->row();
			if ($exists) {
				echo json_encode(['status' => 'false', 'message' => 'Mobile number already registered.']);
				return;
			}

			// Generate emp_id
			$last = $this->db->select('emp_id')->order_by('id', 'DESC')->limit(1)->get('user_data')->row();
			$last_num = 0;
			if ($last && preg_match('/(\d+)/', $last->emp_id, $m)) {
				$last_num = intval($m[1]);
			}
			$emp_id = 'EMP-' . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);

			$data = [
				'emp_id'          => $emp_id,
				'first_name'      => $first_name,
				'mobile_number'   => $mobile_number,
				'email'           => $email,
				'plant_id'        => $plant_id,
				'department_id'   => $department_id,
				'designation'     => 'OPERATOR',
				'date_of_joininig'=> !empty($joining_date) ? date('Y-m-d', strtotime($joining_date)) : null,
				'is_admin'        => '0',
				'is_deleted'      => '0',
				'created_on'      => date('Y-m-d H:i:s'),
				'updated_on'      => date('Y-m-d H:i:s'),
			];

			$this->db->insert('user_data', $data);
			$new_id = $this->db->insert_id();

			echo json_encode([
				'status'  => 'true',
				'message' => 'Operator added successfully.',
				'data'    => ['id' => $new_id, 'emp_id' => $emp_id]
			]);
			return;
		}

		// ── PUT: update existing operator ─────────────────────────────────
		if ($method === 'PUT') {
			$id = isset($request['id']) ? intval($request['id']) : 0;

			if ($id <= 0) {
				echo json_encode(['status' => 'false', 'message' => 'id is required.']);
				return;
			}

			// Check operator exists
			$operator = $this->db->where('id', $id)
				->where('is_deleted', '0')
				->where('UPPER(designation)', 'OPERATOR')
				->get('user_data')->row();

			if (!$operator) {
				echo json_encode(['status' => 'false', 'message' => 'Operator not found.']);
				return;
			}

			$data = ['updated_on' => date('Y-m-d H:i:s')];

			if (isset($request['first_name'])    && $request['first_name']    !== '') $data['first_name']       = trim($request['first_name']);
			if (isset($request['mobile_number']) && $request['mobile_number'] !== '') $data['mobile_number']    = trim($request['mobile_number']);
			if (isset($request['email']))                                              $data['email']            = trim($request['email']);
			if (isset($request['plant_id']))                                           $data['plant_id']         = trim($request['plant_id']);
			if (isset($request['department_id']))                                      $data['department_id']    = trim($request['department_id']);
			if (isset($request['joining_date'])  && $request['joining_date']  !== '') $data['date_of_joininig'] = date('Y-m-d', strtotime($request['joining_date']));

			// Check duplicate mobile if being changed
			if (isset($data['mobile_number'])) {
				$dup = $this->db->where('mobile_number', $data['mobile_number'])
					->where('id !=', $id)
					->where('is_deleted', '0')
					->get('user_data')->row();
				if ($dup) {
					echo json_encode(['status' => 'false', 'message' => 'Mobile number already used by another employee.']);
					return;
				}
			}

			$this->db->where('id', $id);
			$this->db->update('user_data', $data);

			echo json_encode([
				'status'  => 'true',
				'message' => 'Operator updated successfully.',
				'data'    => ['id' => $id]
			]);
			return;
		}

		echo json_encode(['status' => 'false', 'message' => 'Method not allowed. Use GET, POST or PUT.']);
	}

	// ─── Get article batch info for bundle calculation ───────────────────────
	/**
	 * POST: Returns batch size and BOM details for one or multiple articles.
	 * Used by Android to calculate bundle/bag quantity.
	 *
	 * Request (JSON):
	 *   { "article_ids": [1, 2, 3] }   — multiple articles
	 *   { "article_id": 5 }            — single article
	 *
	 * Response:
	 * {
	 *   "status": "true",
	 *   "message": "...",
	 *   "data": [
	 *     {
	 *       "article_id": 5,
	 *       "article_name": "CONTAINER LID",
	 *       "batch": 120,              // pcs per bag — use this to calculate bundles
	 *       "weight": "0.250",         // weight per piece (kg)
	 *       "std_cycle_time": 30,      // seconds per cycle (nullable)
	 *       "std_weight": "0.250",     // std weight from BOM (nullable)
	 *       "raw_material_one": "...",
	 *       "raw_material_two": "...",
	 *       "other_rm": "...",
	 *       "master_batch": "...",
	 *       "bundle_formula": "quantity / batch"
	 *     }
	 *   ]
	 * }
	 *
	 * Bundle calculation on Android:
	 *   bundle_qty = ceil(order_quantity / batch)
	 */
	public function get_article_batch_for_bundle_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		// Accept either article_id (single) or article_ids (array)
		if (!empty($request['article_ids']) && is_array($request['article_ids'])) {
			$article_ids = array_map('intval', $request['article_ids']);
		} elseif (!empty($request['article_id'])) {
			$article_ids = [intval($request['article_id'])];
		} else {
			echo json_encode([
				'status'  => 'false',
				'message' => 'article_id or article_ids is required.',
				'data'    => []
			]);
			return;
		}

		// Remove invalid IDs
		$article_ids = array_values(array_filter($article_ids, function($id) { return $id > 0; }));
		if (empty($article_ids)) {
			echo json_encode([
				'status'  => 'false',
				'message' => 'No valid article IDs provided.',
				'data'    => []
			]);
			return;
		}

		$result = [];

		// Fetch BOM for each article using the same logic as get_artical_bom
		foreach ($article_ids as $article_id) {
			// Get article master data (for name and fallback batch)
			$article = $this->db->where('id', $article_id)
				->get('tbl_mould_parts')
				->row();

			// Get latest BOM for this article
			$this->db->select('bom.*');
			$this->db->from('tbl_production_bom bom');
			$this->db->where('bom.article_id', $article_id);
			$this->db->where('(bom.is_deleted = \'0\' OR bom.is_deleted = 0 OR bom.is_deleted = \'\' OR bom.is_deleted IS NULL)', null, false);
			$this->db->order_by('bom.id', 'DESC');
			$this->db->limit(1);
			$bom = $this->db->get()->row();

			if ($bom) {
				// BOM exists
				$is_batch_configured = (isset($bom->batch) && $bom->batch !== null && floatval($bom->batch) > 0);
				$final_batch = $is_batch_configured ? floatval($bom->batch) : 120.0;

				$row = (object)[
					'article_id'      => $article_id,
					'article_name'    => $article ? $article->article_name : '',
					'batch'           => $final_batch,
					'weight'          => isset($bom->weight) && $bom->weight !== null ? floatval($bom->weight) : null,
					'raw_material_one'=> isset($bom->raw_material_one) ? $bom->raw_material_one : null,
					'raw_material_two'=> isset($bom->raw_material_two) ? $bom->raw_material_two : null,
					'other_rm'        => isset($bom->other_rm) ? $bom->other_rm : null,
					'master_batch'    => isset($bom->master_batch) ? $bom->master_batch : null,
					'std_cycle_time'  => isset($bom->std_cycle_time) && $bom->std_cycle_time !== null ? floatval($bom->std_cycle_time) : null,
					'std_weight'      => isset($bom->std_weight) && $bom->std_weight !== null ? floatval($bom->std_weight) : null,
					'bundle_formula'  => 'quantity / batch',
					'is_batch_configured' => $is_batch_configured,
					'source'          => $is_batch_configured ? 'production_bom' : 'default_fallback'
				];
			} else {
				// No BOM found - try fallback to article master
				$fallback_batch = ($article && isset($article->bundle_bag_qty) && $article->bundle_bag_qty !== null) ? floatval($article->bundle_bag_qty) : null;
				$is_batch_configured = ($fallback_batch !== null && $fallback_batch > 0);
				$final_batch = $is_batch_configured ? $fallback_batch : 120.0;
				
				$row = (object)[
					'article_id'      => $article_id,
					'article_name'    => $article ? $article->article_name : '',
					'batch'           => $final_batch,
					'weight'          => null,
					'raw_material_one'=> null,
					'raw_material_two'=> null,
					'other_rm'        => null,
					'master_batch'    => null,
					'std_cycle_time'  => null,
					'std_weight'      => null,
					'bundle_formula'  => 'quantity / batch',
					'is_batch_configured' => $is_batch_configured,
					'source'          => $is_batch_configured ? 'article_master' : 'default_fallback'
				];
			}

			$result[] = $row;
		}

		echo json_encode([
			'status'  => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'Batch info fetched successfully.' : 'No articles found.',
			'data'    => $result
		]);
	}

	public function get_article_stock_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);
		$article_id = isset($request['article_id']) ? intval($request['article_id']) : intval($this->input->get('article_id'));
		$plant_id = isset($request['plant_id']) ? intval($request['plant_id']) : intval($this->input->get('plant_id'));

		if (empty($article_id)) {
			echo json_encode([
				'status'  => 'false',
				'message' => 'article_id is required.',
				'data'    => []
			]);
			return;
		}

		$this->db->select('total_quantity, plant_id');
		$this->db->from('tbl_article_stock_report');
		$this->db->where('article_id', $article_id);
		if ($plant_id > 0) {
			$this->db->where('plant_id', $plant_id);
		}
		$this->db->where('is_deleted', '0');
		$result = $this->db->get()->result();

		$total_qty = 0;
		foreach ($result as $row) {
			$total_qty += floatval($row->total_quantity);
		}

		echo json_encode([
			'status'  => 'true',
			'message' => 'Stock fetched successfully.',
			'data'    => [
				'article_id'      => $article_id,
				'total_stock_qty' => $total_qty,
				'breakdown'       => $result
			]
		]);
	}

	// ─── Private helper: map request fields → DB columns ─────────────────────
	private function _build_process_parameter_data($req)
	{		$fields = [
			'schedule_id','machine_name','article_name','shift','employee_id','plant_id',
			'material','cycle_time','article_wt','colour','no_of_cavities','runner_wt','mb_percent',
			'temp_nozzle_set','temp_zone1_set','temp_zone2_set','temp_zone3_set',
			'temp_zone4_set','temp_zone5_set','temp_zone6_set',
			'cooling_time','fill_time','xfer_pos_mm',
			'fill_profile_pos_1','fill_profile_pos_2','fill_profile_pos_3','fill_profile_pos_4','fill_profile_no',
			'pack_pos_1','pack_pos_2','pack_spd_1','pack_spd_2',
			'pack_time_1','pack_time_2','pack_prs_1','pack_prs_2',
			'pack_prs_s_1','pack_prs_s_2','pack_spd_mm_s_1','pack_spd_mm_s_2',
			'manual_back_prs','suck_back_profile','suck_back_pos_mm',
			'shot_size_pos_mm','refill_spd_rpm','refill_back_prs',
			'mold_safety','tonnage',
			'close_profile_no',
			'close_pos_1','close_pos_2','close_pos_3','close_pos_4',
			'close_spd_1','close_spd_2','close_spd_3','close_spd_4',
			'close_prs_1','close_prs_2','close_prs_3','close_prs_4',
			'close_ton_time','auto_tonnage','open_limit',
			'open_profile_no','open_brk_away',
			'open_pos_1','open_pos_2','open_pos_3',
			'open_spd_1','open_spd_2','open_spd_3','set_tonnage',
			'hydr_fwd_prs','hydr_fwd_limit_1','hydr_fwd_limit_2',
			'hydr_fwd_pos_mm','hydr_fwd_spd_mm_s','hydr_fwd_prs_bar',
			'air_ej_start_pos','air_ej_pulse_limit','air_ej_dly_time','air_ej_on_time',
			'air_ej_pos_mm_1','air_ej_pos_mm_2','air_ej_prs_1','air_ej_prs_2',
			'retract_limit_1','retract_limit_2','retract_pulse_limit',
			'retract_pos_mm','retract_spd_mm_s','retract_prs_bar',
		];

		$data = [];
		foreach ($fields as $f) {
			if (array_key_exists($f, $req)) {
				$data[$f] = ($req[$f] !== '') ? $req[$f] : null;
			}
		}

		if (!empty($req['production_date'])) {
			$data['production_date'] = date('Y-m-d', strtotime($req['production_date']));
		}

		return $data;
	}

	// =========================================================================
	// Production Image Upload APIs
	// =========================================================================

	/**
	 * POST upload_production_images_api
	 *
	 * Accepts one or more base64-encoded images and attaches them to a
	 * production report record.
	 *
	 * Request body (JSON):
	 *   {
	 *     "production_id": 123,
	 *     "images": ["<base64>", "<base64>", ...]
	 *   }
	 *
	 * On success the new image URLs are appended to any already stored for
	 * that production record (comma-separated in the `production_images`
	 * column of tbl_production_report).
	 */
	public function upload_production_images_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		// ── Validate required fields ──────────────────────────────────────────
		if (empty($request['production_id'])) {
			http_response_code(400);
			echo json_encode([
				'status'  => 'false',
				'message' => 'production_id is required',
				'data'    => null,
			]);
			return;
		}

		if (empty($request['images']) || !is_array($request['images'])) {
			http_response_code(400);
			echo json_encode([
				'status'  => 'false',
				'message' => 'images array is required and must not be empty',
				'data'    => null,
			]);
			return;
		}

		$production_id = (int) $request['production_id'];

		// ── Verify the production record exists ───────────────────────────────
		$this->db->where('id', $production_id);
		$production = $this->db->get('tbl_production_report')->row();

		if (empty($production)) {
			http_response_code(404);
			echo json_encode([
				'status'  => 'false',
				'message' => 'Production record not found',
				'data'    => null,
			]);
			return;
		}

		// ── Save each base64 image to disk ────────────────────────────────────
		$upload_dir = FCPATH . 'assets/images/production/';
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}

		$saved_paths  = [];
		$failed_count = 0;

		foreach ($request['images'] as $index => $image_data) {
			// Strip optional data-URI prefix (data:image/jpeg;base64,...)
			if (strpos($image_data, ',') !== false) {
				$image_data = substr($image_data, strpos($image_data, ',') + 1);
			}

			$binary = base64_decode($image_data, true);

			if ($binary === false) {
				$failed_count++;
				continue;
			}

			$image_info = @getimagesizefromstring($binary);

			if (!$image_info) {
				$failed_count++;
				continue;
			}

			$mime = strtolower($image_info['mime']); // e.g. image/jpeg
			$ext  = str_replace('image/', '', $mime);

			if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
				$failed_count++;
				continue;
			}

			$filename  = 'prod_' . $production_id . '_' . time() . '_' . str_pad($index, 3, '0', STR_PAD_LEFT) . '.' . $ext;
			$full_path = $upload_dir . $filename;

			if (file_put_contents($full_path, $binary) !== false) {
				// Store relative path so it works with base_url()
				$saved_paths[] = 'assets/images/production/' . $filename;
			} else {
				$failed_count++;
			}
		}

		if (empty($saved_paths)) {
			http_response_code(422);
			echo json_encode([
				'status'  => 'false',
				'message' => 'No valid images could be saved. Ensure images are valid base64-encoded JPG/PNG/GIF/WEBP.',
				'data'    => null,
			]);
			return;
		}

		// ── Merge with any previously stored images ───────────────────────────
		$existing_images = !empty($production->production_images)
			? array_filter(explode(',', $production->production_images))
			: [];

		$all_images = array_merge($existing_images, $saved_paths);

		$this->db->where('id', $production_id);
		$this->db->update('tbl_production_report', [
			'production_images' => implode(',', $all_images),
		]);

		// ── Build public URLs for the response ────────────────────────────────
		$image_urls = array_map(function ($path) {
			return base_url($path);
		}, $saved_paths);

		$message = count($saved_paths) . ' image(s) uploaded successfully';
		if ($failed_count > 0) {
			$message .= '; ' . $failed_count . ' image(s) skipped (invalid format or decode error)';
		}

		http_response_code(200);
		echo json_encode([
			'status'  => 'true',
			'message' => $message,
			'data'    => [
				'production_id' => $production_id,
				'image_urls'    => $image_urls,
			],
		]);
	}

	/**
	 * POST get_production_images_api
	 *
	 * Returns all image URLs stored for a production report record.
	 *
	 * Request body (JSON):
	 *   { "production_id": 123 }
	 */
	public function get_production_images_api()
	{
		$request = json_decode(file_get_contents('php://input'), true);

		if (empty($request['production_id'])) {
			http_response_code(400);
			echo json_encode([
				'status'  => 'false',
				'message' => 'production_id is required',
				'data'    => null,
			]);
			return;
		}

		$production_id = (int) $request['production_id'];

		$this->db->select('id, production_images');
		$this->db->where('id', $production_id);
		$production = $this->db->get('tbl_production_report')->row();

		if (empty($production)) {
			http_response_code(404);
			echo json_encode([
				'status'  => 'false',
				'message' => 'Production record not found',
				'data'    => null,
			]);
			return;
		}

		$paths = !empty($production->production_images)
			? array_values(array_filter(explode(',', $production->production_images)))
			: [];

		$image_urls = array_map(function ($path) {
			return base_url(trim($path));
		}, $paths);

		// Fetch web-uploaded images
		$this->db->select('image_names');
		$this->db->from('tbl_production_form_image');
		$this->db->where('production_id', $production_id);
		$this->db->where('is_deleted', '0');
		$this->db->where('status', '1');
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$image_urls[] = base_url('assets/images/production/' . trim($row->image_names));
			}
		}

		http_response_code(200);
		echo json_encode([
			'status'  => 'true',
			'message' => empty($image_urls) ? 'No images found for this production record' : 'Images retrieved successfully',
			'data'    => [
				'production_id' => $production_id,
				'image_urls'    => $image_urls,
			],
		]);
	}

	/**
	 * GET: Returns all active Production Idle State Reasons from tbl_remark_master.
	 * Endpoint: /api/Api_controller/get_all_remark_master_api
	 */
	public function get_all_remark_master_api()
	{
		$this->db->where('is_deleted', '0');
		$this->db->order_by('remark_name', 'ASC');
		$result = $this->db->get('tbl_remark_master')->result();

		echo json_encode([
			'status'  => !empty($result) ? 'true' : 'false',
			'message' => !empty($result) ? 'Reasons fetched successfully.' : 'No reasons found.',
			'data'    => $result ?: []
		]);
	}
}

