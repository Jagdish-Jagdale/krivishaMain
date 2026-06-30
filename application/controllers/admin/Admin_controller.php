<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Class Admin_controller
 *
 * @property Admin_model $Admin_model
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Upload $upload
 * @property CI_Form_validation $form_validation
 * @property CI_DB_query_builder $db
 */
class Admin_controller extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->is_login();
		$profile = $this->Admin_model->get_user_profile();
		if(!empty($profile)){
			if($profile->is_admin != "1"){
				$this->check_access(); 
			}
		}else{
			redirect(base_url());
		}
	}
	public function check_access(){
        $link = $this->Admin_model->get_staff_priiliges();
        if($this->uri->segment(1) != 'dashboard'){
			$common_access = array('dashboard','view_material_artical_requistition_to_list','view_mb_requestion_one_plant_to_other','view__artical_requistition_to_list','view_material_artical_requistition_from','view_inward_form','view_inward_form');
			if(in_array($this->uri->segment(1),$common_access)){
    
			}else{
				$current_route = $this->uri->segment(1);
				$has_access = in_array($current_route, $link);

				// Backward-compatible alias mapping for task privileges.
				if ($current_route == 'task_list' && !$has_access) {
					$has_access = in_array('manual_task_access', $link);
				}

				if ($current_route == 'auto_task_list' && !$has_access) {
					$has_access = in_array('auto_task_access', $link);
				}

				if ($current_route == 'update_auto_manual_task' && !$has_access) {
					$has_access = in_array('manual_task_reply', $link) || in_array('auto_task_reply', $link);
				}

				if ($current_route == 'printing_report' && !$has_access) {
					$has_access = in_array('printing_unit_report', $link);
				}

				if ($current_route == 'production_report' && !$has_access) {
					$has_access = in_array('production_report_list', $link);
				}

				if (!$has_access){
					$this->session->set_flashdata('message','Sorry! You don’t have access to this module!');
					redirect('dashboard');
				}
			}
        }
    }
	public function is_login()
	{
		if ($this->session->userdata('id') == "") {
			$this->session->set_flashdata('message', 'Please login to continue');
			redirect(base_url());
		}
	}
	
	public function index()
	{
		$data['party'] = $this->Admin_model->get_all_supplier_party_master();
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['machine'] = $this->Admin_model->get_all_machine_master();
		$data['article'] = $this->Admin_model->get_all_article();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$profile = $this->Admin_model->get_user_profile();
		
		$dashboard_option = $this->input->get('dashboard_option');
		$data['dashboard_option'] = $dashboard_option;
		
		// 1. Determine which view to load
		$view = 'admin/account_default_dashborad'; // fallback
		if ($dashboard_option == 'plant_head_production') {
			$view = 'admin/dashboard';
		} else if ($dashboard_option == 'production_supervisor') {
			$view = 'admin/dashboard-2';
		} else if ($dashboard_option == 'account') {
			$view = 'admin/account_default_dashborad';
		} else if ($dashboard_option == 'store_dashboard') {
			$view = 'admin/store_dashboard';
		} else if ($dashboard_option == 'purchase_dashboard') {
			$view = 'admin/purchase_dashboard';
		} else {
			if (!empty($profile)) {
				if ($profile->is_admin == "1") {
					$view = 'admin/owner_dashboard';
				} else if ($profile->designation == "PLANT MANAGER" && $profile->department_id == '3') {
					$view = 'admin/dashboard';
				} else if ($profile->designation == "PRODUCTION SUPERVISOR" && $profile->department_id == '3') {
					$view = 'admin/dashboard-2';
				} else if ($profile->department_id == '24') {
					$view = 'admin/store_dashboard';
				} else if ($profile->department_id == '19') {
					$view = 'admin/purchase_dashboard';
				}
			}
		}

		// 2. Load heavy data conditionally
		if ($view === 'admin/dashboard' || $view === 'admin/dashboard-2' || $view === 'admin/owner_dashboard' || $view === 'admin/account_default_dashborad') {
			$data['metrics'] = $this->Admin_model->get_dashboard_metrics();
			$data['maintenance_approve_pending'] = $this->Admin_model->get_maintenance_approve_pending_count();
			
			if ($view !== 'admin/account_default_dashborad') {
			    $data['machine_production'] = $this->Admin_model->get_all_machines_production();
			    $data['article_level'] = $this->Admin_model->get_all_article_level_analysis();
			}
		}
		
		if ($view === 'admin/store_dashboard') {
			$data['store_metrics'] = $this->Admin_model->get_store_dashboard_metrics();
		}
		
		if ($view === 'admin/purchase_dashboard') {
			$data['purchase_metrics'] = $this->Admin_model->get_purchase_dashboard_metrics();
		}
		
		// These seem unused in standard dashboards, but we'll load them for owner just in case
		if ($view === 'admin/owner_dashboard' || $view === 'admin/account_default_dashborad') {
			$data['task_tat'] = $this->Admin_model->get_all_task_tat_analysis();
			$data['krivisha_department'] = $this->Admin_model->get_all_kri_department_dashboard();
			$data['krivisha_employee'] = $this->Admin_model->get_all_kri_employee_dashboard();
		}

		$this->load->view($view, $data);
	}
	
	public function delete()
	{
		$result = $this->Admin_model->delete();
		if ($result == '1') {
			$this->session->set_flashdata('success', 'Record deleted successfully');
		} else {
			$this->session->set_flashdata('message', ' failed to delete the record');
		}
		redirect($_SERVER['HTTP_REFERER']);
	}
	public function change_current_password()
	{
		$result = $this->Admin_model->change_current_password();
		if ($result == '2') {
			$this->session->set_flashdata('success', 'Password updated successfully!');
			redirect('dashboard');
		} else {
			$this->session->set_flashdata('message', 'Failed to update Password!');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	public function update_profile_image()
	{
		if (!empty($_FILES['profile_image']['name'])) {
			$config['upload_path'] = 'assets/images';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['max_size'] = 2048;
			$config['encrypt_name'] = TRUE;
			$this->load->library('upload');
			$this->upload->initialize($config);
			
			if (!$this->upload->do_upload('profile_image')) {
				$this->session->set_flashdata('message', $this->upload->display_errors('', ''));
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$upload_data = $this->upload->data();
				$image_name = $upload_data['file_name'];

				$result = $this->Admin_model->update_profile_image($image_name);
				if ($result) {
					$this->session->set_flashdata('success', 'Profile updated successfully!');
					redirect('dashboard');
				} else {
					$this->session->set_flashdata('message', 'Failed to update profile!');
					redirect($_SERVER['HTTP_REFERER']);
				}
			}
		} else {
			$this->session->set_flashdata('message', 'No image selected!');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	public function add_production()
	{
		$id = $this->input->post('id');

		$this->form_validation->set_rules('production_date', 'Production date', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['machine_data'] = $this->Admin_model->get_machine_data();
			$data['single'] = $this->Admin_model->get_single_production_report();
			$data['article_data'] = $this->Admin_model->get_article_data($data['single']->article_group_id ?? null);
			// echo"<pre>";print_r($data);exit;
			$data['raw_material_data'] = $this->Admin_model->get_raw_material_data();
			$data['master_batch_data'] = $this->Admin_model->get_master_batch_data();
			$data['rejection_data'] = $this->Admin_model->get_rejection_data();
			$data['group_of_article'] = $this->Admin_model->get_single_group_of_article();
			$this->load->view('admin/add_production', $data);
		} else {
			$result = $this->Admin_model->set_production_report();
			if ($result === 'inserted') {
				$this->session->set_flashdata('success', 'Data added successfully!');
				redirect('production_report_list');
			} else {
				$this->session->set_flashdata('success', 'Data updated successfully!');
				redirect('production_report_list');
			}
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
    public function production_form_list()
    {
        $production_id = $this->uri->segment(2);
        $data['prodution_id'] = $production_id;
        $data['machine'] = $this->Admin_model->get_machine_data_by_id();
        $data['article_data'] = $this->Admin_model->get_article_data_by_id();
        $data['raw_material_data'] = $this->Admin_model->get_raw_material_data_by_id();
        $data['master_batch_data'] = $this->Admin_model->get_master_batch_data_by_id();
        $data['rejection_data'] = $this->Admin_model->get_rejection_data_by_id();
        $data['production_report_data'] = $this->Admin_model->get_single_entity_active('tbl_production_report');

        if ($production_id) {
            $remarks_data = $this->Admin_model->get_production_remarks($production_id);
            $data['existing_remarks'] = $remarks_data ? $remarks_data->remarks : '';
        } else {
            $data['existing_remarks'] = '';
        }
        $data['remark_master_list'] = $this->Admin_model->get_all_remark_master();
        $data['operators'] = $this->Admin_model->get_all_operators();
		
        $this->load->view('admin/production_form_list', $data);
    }

	/**
	 * Handles image uploads.
	 *
	 * @param string $input_name
	 * @param string $upload_path
	 * @return string|null
	 */
	private function handle_image_upload(string $input_name, string $upload_path): ?string
	{
		$config['upload_path'] = $upload_path;
		$config['allowed_types'] = '*';
		$config['max_size'] = 2048;
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($input_name)) {
			return NULL;
		} else {
			return $this->upload->data('file_name');
		}
	}

	public function summary()
	{
		$production_id = $this->uri->segment(2);
		$data['prodution_id'] = $production_id;
		$data['machine'] = $this->Admin_model->get_machine_data_by_id();
		$data['article_data'] = $this->Admin_model->get_article_production_summary($production_id);
		$data['raw_material_data'] = $this->Admin_model->get_raw_material_data_by_id();
		$data['master_batch_data'] = $this->Admin_model->get_master_batch_data_by_id();
		$data['rejection_data'] = $this->Admin_model->get_rejection_data_by_id();
		$data['production_report_data'] = $this->Admin_model->get_single_entity_active('tbl_production_report');

		if ($production_id) {
			$remarks_data = $this->Admin_model->get_production_remarks($production_id);
			$data['existing_remarks'] = $remarks_data ? $remarks_data->remarks : '';
		} else {
			$data['existing_remarks'] = '';
		}
		$data['remark_master_list'] = $this->Admin_model->get_all_remark_master();
		$data['operators'] = $this->Admin_model->get_all_operators();
		$this->load->view('admin/production_form_list', $data);
	}


	public function article_production_details_logs()
	{
		$this->form_validation->set_rules("qty_8_9", "enter the quantity", "required|numeric");
		if ($this->form_validation->run() == FALSE) {
			$data['prodution_id'] = $this->uri->segment(2);
			$data['article_data'] = $this->Admin_model->get_article_data_by_id();
			$data['remark_master_list'] = $this->Admin_model->get_all_remark_master();

			$ids = array_map(fn($article) => $article->id, $data['article_data']);
			$idString = implode(",", $ids);

			$this->load->view('admin/production_form_list', $data);
		}
	}
	public function production_report_list()
	{
		$data['article'] = $this->Admin_model->get_all_article();
		$data['color'] = $this->Admin_model->get_all_colors();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();	
		$data['operators'] = $this->Admin_model->get_all_operators();	
		$this->load->view('admin/production_report_list',$data);
	}
	public function salesman_on_fields_details()
	{
		$data['party'] = $this->Admin_model->get_all_party_master();
		$data['sales_officer'] = $this->Admin_model->get_all_sales_officer();
		$this->load->view('admin/salesman_on_fields_details', $data);
	}
	public function add_location()
	{
		$this->form_validation->set_rules('city', 'Enter city', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_location_master();
			$this->load->view('admin/add_location', $data);
		} else {
			$result = $this->Admin_model->set_location();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function location_list()
	{
		$this->load->view('admin/location_list');
	}
	public function add_brand()
	{
		$this->form_validation->set_rules('brand_name', 'Enter Brand Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_brand_master();
			$data['party_name'] = $this->Admin_model->get_all_party_master();
			$data['brand_type'] = $this->Admin_model->get_single_brand_type();
			$data['department'] = $this->Admin_model->get_single_department();
			$data['ink'] = $this->Admin_model->get_all_rm_ink_master();
			$this->load->view('admin/add_brand', $data);
		} else {
			$ink_ids = $this->input->post('ink');
			if (!empty($ink_ids) && is_array($ink_ids)) {
				$ink_ids_str = implode(',', $ink_ids);
			} else {
				$ink_ids_str = '';
			}
			$data = array(
				'brand_name' => $this->input->post('brand_name'),
				'brand_type_id' => $this->input->post('brand_type'),
				'party_name_id' => $this->input->post('party_name'),
				'department_id' => $this->input->post('department'),
				'ink_ids' => $ink_ids_str,
				'created_on' => date('Y-m-d H:i:s'),
			);
			$result = $this->Admin_model->set_brand($data);
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function brand_list()
	{
		$this->load->view('admin/brand_list');
	}
	public function add_transport()
	{
		$this->form_validation->set_rules('transport_name', 'Enter transport Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_transport_master();
			$data['city'] = $this->Admin_model->get_all_city_master();
			$this->load->view('admin/add_transport', $data);
		} else {
			$data = array(
				'transport_name' => $this->input->post('transport_name'),
				'mobile_one' => $this->input->post('mobile_one'),
				'mobile_two' => $this->input->post('mobile_two'),
				'transport_id' => $this->input->post('transport_id'),
				'city_id' => str_replace(' ', '', implode(', ', $this->input->post('city'))),
				'transport_rating' => $this->input->post('transport_rating'),
				'created_on' => date('Y-m-d H:i:s'),
			);
			$result = $this->Admin_model->set_transport($data);
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function transport_list()
	{
		$this->load->view('admin/transport_list');
	}
	public function add_customer()
	{
		$this->form_validation->set_rules('party_name', 'Enter Party Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_party_master();
			$data['city'] = $this->Admin_model->get_all_city_master();
			$data['designation'] = $this->Admin_model->get_single_designation_master();
			$data['nature_of_business'] = $this->Admin_model->get_single_nature_of_business();
			$data['type_of_business'] = $this->Admin_model->get_single_type_of_business();
			$data['employee'] = $this->Admin_model->get_all_employee_master();
			$this->load->view('admin/add_customer', $data);
		} else {
			$party_type_ids = $this->input->post('party_type');
			if (is_string($party_type_ids)) {
				$party_type_ids = array($party_type_ids);
			}
			$party_type_ids_str = implode(',', $party_type_ids);
			$division_ids = $this->input->post('division');
			if (is_string($division_ids)) {
				$division_ids = array($division_ids);
			}
			$division_ids_str = !empty($division_ids) ? implode(',', $division_ids) : '';
			$data = array(
				'party_name' => $this->input->post('party_name'),
				'party_type' => $party_type_ids_str,
				'mobile' => $this->input->post('mobile'),
				'gst_pan' => $this->input->post('gst_pan'),
				'address' => $this->input->post('address'),
				'city_id' => $this->input->post('city'),
				'contact_name' => $this->input->post('contact_name'),
				'designation_id' => $this->input->post('designation'),
				'sec_contact' => $this->input->post('sec_contact'),
				'designation_two_id' => $this->input->post('designation_two'),
				'division_ids' => $division_ids_str,
				'attending_salesperson_id' => $this->input->post('attending'),
				'nature_of_business_id' => $this->input->post('nature_of_business'),
				'type_of_business_id' => $this->input->post('type_of_business'),
				'plant_id' => $this->session->userdata("assign_plant_id"),
				// 4 Sales Hierarchy Layers
				'dg_id' => $this->input->post('dg_id') ?: NULL,
				'asm_id' => $this->input->post('asm_id') ?: NULL,
				'state_head_id' => $this->input->post('state_head_id') ?: NULL,
				'telecaller_id' => $this->input->post('telecaller_id') ?: NULL,
				'created_on' => date('Y-m-d H:i:s')
			);
			$result = $this->Admin_model->set_party($data);
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function customer_list()
	{
		$this->load->view('admin/customer_list');
	}
	public function add_article()
	{
		$this->form_validation->set_rules('article_name', 'Enter Article Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_article_master();
			$data['type_of_mould'] = $this->Admin_model->get_single_type_of_mould();
			$data['air_pin'] = $this->Admin_model->get_rm_by_category_name('AIR PIN', 'air_pin');
			$data['spring'] = $this->Admin_model->get_rm_by_category_name('SPRING', 'spring');
			$data['pu_nipples'] = $this->Admin_model->get_rm_by_category_name('PU NIPPLES', 'pu_nipples');
			$data['ejector_pin'] = $this->Admin_model->get_rm_by_category_name('EJECTOR PIN', 'ejector_pin');
			$data['i_bolt'] = $this->Admin_model->get_rm_by_category_name('I BOLT', 'i_bolt');
			$data['cord'] = $this->Admin_model->get_rm_by_category_name('CORD', 'cord');
			$data['o_ring'] = $this->Admin_model->get_rm_by_category_name('O RING', 'o_ring');
			$data['insert_slot_plate'] = $this->Admin_model->get_rm_by_category_name('INSERT SLOT PLATE', 'insert_slot_plate');
			$data['core_cylinder_seal'] = $this->Admin_model->get_rm_by_category_name('CORE CYLINDER SEAL', 'core_cylinder_seal');
			$data['seal'] = $this->Admin_model->get_rm_by_category_name('SEAL', 'seal');
			$data['hose_pipe'] = $this->Admin_model->get_rm_by_category_name('HOSE PIPE', 'hose_pipe');
			$data['alankey_bolt'] = $this->Admin_model->get_rm_by_category_name('ALANKEY BOLT', 'alankey_bolt');
			$data['group_of_article'] = $this->Admin_model->get_single_group_of_article();
			// P1: Load RM materials (type_id IN [10-16]) and Maintenance materials (type_id IN [17,20]) for Article linking
			$data['rm_materials'] = $this->Admin_model->get_rm_materials_for_article('rm');
			$data['maintenance_materials'] = $this->Admin_model->get_rm_materials_for_article('maintenance');
			$this->load->view('admin/add_article', $data);
		} else {
			$reorder_level_raw = $this->input->post('reorder_level');
			$reorder_level_val = is_numeric($reorder_level_raw) ? (float) $reorder_level_raw : 0.0;
			$bundle_bag_qty = ($reorder_level_val >= 0) ? round($reorder_level_val / 120, 2) : 0.0;

			$air_pin_ids = $this->input->post('air_pin');
			$pu_nipples_ids = $this->input->post('pu_nipples');
			$alankey_bolt_ids = $this->input->post('alankey_bolt');
			if (!empty($air_pin_ids) && is_array($air_pin_ids)) {
				$air_pin_ids_str = implode(',', $air_pin_ids);
			} else {
				$air_pin_ids_str = '';
			}
			if (!empty($pu_nipples_ids) && is_array($pu_nipples_ids)) {
				$pu_nipples_ids_str = implode(',', $pu_nipples_ids);
			} else {
				$pu_nipples_ids_str = '';
			}

			if (!empty($alankey_bolt_ids) && is_array($alankey_bolt_ids)) {
				$alankey_bolt_ids_str = implode(',', $alankey_bolt_ids);
			} else {
				$alankey_bolt_ids_str = '';
			}
			// P1: Handle multi-select RM & Maintenance material IDs
			$rm_material_ids_arr = $this->input->post('rm_material_ids');
			$rm_material_ids_str = (!empty($rm_material_ids_arr) && is_array($rm_material_ids_arr))
				? implode(',', $rm_material_ids_arr) : '';
			$maintenance_material_ids_arr = $this->input->post('maintenance_material_ids');
			$maintenance_material_ids_str = (!empty($maintenance_material_ids_arr) && is_array($maintenance_material_ids_arr))
				? implode(',', $maintenance_material_ids_arr) : '';

			$insert_data = array(
				'article_name' => $this->input->post('article_name'),
				'group_of_article_id' => $this->input->post('group_of_article'),
				'type_of_mould_id' => $this->input->post('type_of_mould'),
				'air_pin_id' => $air_pin_ids_str,
				'spring_id' => $this->input->post('spring'),
				'pu_nipples_id' => $pu_nipples_ids_str,
				'ejector_pin_id' => $this->input->post('ejector_pin'),
				'i_bolt_id' => $this->input->post('i_bolt'),
				'cord_id' => $this->input->post('cord'),
				'o_ring_id' => $this->input->post('o_ring'),
				'insert_slot_plate_id' => $this->input->post('insert_slot_plate'),
				'core_cylinder_seal_id' => $this->input->post('core_cylinder_seal'),
				'seal_id' => $this->input->post('seal'),
				'hose_pipe_id' => $this->input->post('hose_pipe'),
				'reorder_level' => $reorder_level_val,
				'alankey_bolt_id' => $alankey_bolt_ids_str,
				'bundle_bag_qty' => $bundle_bag_qty,
				// P1: Linked material IDs (CSV)
				'rm_material_ids' => $rm_material_ids_str,
				'maintenance_material_ids' => $maintenance_material_ids_str,
				'created_on' => date('Y-m-d H:i:s'),
			);
			$result = $this->Admin_model->set_article_master($insert_data);
			if ($result != '') {
				redirect('maintaince_bom/' . $result);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function article_list()
	{
		$this->load->view('admin/article_list');
	}
	public function extra_payment_master()
	{
		$this->form_validation->set_rules('extra_payment_id', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_extra_payment_master();
			$this->load->view('admin/extra_payment_master', $data);
		} else {
			$result = $this->Admin_model->set_extra_payment_master();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function remark_master()
	{
		$this->form_validation->set_rules('remark_name', 'Enter Remark', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_remark_master();
			$this->load->view('admin/remark_master', $data);
		} else {
			$result = $this->Admin_model->set_remark_master();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function add_rm()
	{
		$this->form_validation->set_rules('rm_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_rm_master();
			$data['type'] = $this->Admin_model->get_all_rm_type_master();
			$data['make'] = $this->Admin_model->get_all_rm_make_master();
			$data['ink'] = $this->Admin_model->get_all_rm_ink_master();
			$data['uom'] = $this->Admin_model->get_all_type_of_uom();
			$this->load->view('admin/add_rm', $data);
		} else {
			$result = $this->Admin_model->set_rm_master();
			if ($result == '0') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			}else if ($result == '1') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			}else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function rm_list()
	{
		$this->load->view('admin/rm_list');
	}
	public function rm_rejection_list()
	{
		$this->load->view('admin/rm_rejection_list');
	}
	public function add_mb()
	{
		$this->form_validation->set_rules('name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_mb_master();
			$data['make'] = $this->Admin_model->get_all_rm_make_master();
			$this->load->view('admin/add_mb', $data);
		} else {
			$result = $this->Admin_model->set_mb_master();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function mb_list()
	{
		$this->load->view('admin/mb_list');
	}
	public function maintaince_bom()
	{
		if (empty($this->uri->segment(2))) {
			$this->session->set_flashdata('message', 'Invalid Article ID! Please select an article first.');
			redirect('article_list');
		}

		$this->form_validation->set_rules('quantity[]', 'Enter quantity', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single_article'] = $this->Admin_model->get_all_single_articale();
			$data['single'] = $this->Admin_model->get_single_bom_master();
			$data['uom'] = $this->Admin_model->get_all_type_of_uom();
			$this->load->view('admin/maintaince_bom', $data);
		} else {
			$article_ids = $this->input->post('article_id');
			$group_of_articles = $this->input->post('group_of_article_id');
			$sizes_of_mould = $this->input->post('size_of_mould');
			$types_of_parts = $this->input->post('type_of_mould');
			$uoms = $this->input->post('uom');
			$quantities = $this->input->post('quantity');
			$bom_data = [];
			if (is_array($sizes_of_mould) && is_array($types_of_parts) && is_array($uoms) && is_array($quantities)) {
				if (count($sizes_of_mould) == count($types_of_parts) && count($types_of_parts) == count($uoms) && count($uoms) == count($quantities)) {
					for ($i = 0; $i < count($sizes_of_mould); $i++) {
					$bom_data[] = [
						'article_id' => $article_ids,
						'group_of_article_id' => $group_of_articles,
						'size_of_mould_id' => $sizes_of_mould[$i],
						'type_of_mould' => $types_of_parts[$i],
						'uom_id' => $uoms[$i],
						'quantity' => $quantities[$i]
					];
				}
			}
			}
			$result = $this->Admin_model->set_maintaince_bom($bom_data);
			if ($result != '') {
				redirect('add_production_bom/' . $result);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function production_schedule()
	{
		$data['plant'] = $this->Admin_model->get_all_plant_for_production();
		$this->load->view('admin/production_schedule', $data);
	}

	public function production_schedule_form()
	{
		$this->form_validation->set_rules('qty', 'Enter Qty', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_production_schedule();
			$data['article_group'] = $this->Admin_model->get_all_article_gruop();
			$data['color'] = $this->Admin_model->get_all_colors();
			$data['master_batch_data'] = $this->Admin_model->get_master_batch_data();
			$data['raw_material_data'] = $this->Admin_model->get_raw_material_data();
			$this->load->view('admin/production_schedule_form', $data);
		} else {

			$res = $this->Admin_model->set_production_schedule_form();
			if ($res == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('production_schedule');
			} else if ($res == '0') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('production_schedule');
			}else if ($res == '2') {
				$this->session->set_flashdata('message', 'Overlapping schedule exists for this machine and plant!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}

	public function store_rm()
	{
		$data['plant'] = $this->Admin_model->get_all_plant_for_production();
		$this->load->view('admin/store_rm', $data);
	}
	public function store_rm_list()
	{
		$this->load->view('admin/store_rm_list');
	}
	public function add_maintenance()
	{
		if ($this->input->post('submit_btn') == "submit_btn") {
			$problem_ids = $this->input->post('details_maintainance');
			if (is_string($problem_ids)) {
				$problem_ids = array($problem_ids);
			}
			$problem_ids_str = implode(',', $problem_ids);
			$mwo_code = $this->Admin_model->generate_mwo_code();
			$data = array(

				'plant_id' => $this->input->post('plant'),
				'employee_id' => $this->session->userdata("id"),
				'date' => date('Y-m-d', strtotime($this->input->post('date'))),
				'type_of_action' => $this->input->post('type_action'),
				'maintaince' => $this->input->post('maintain_action'),
				'sub_type_id' => $this->input->post('sub_type'),
				'problem_id' => $problem_ids_str,
				'updated_on' => date('Y-m-d H:i:s')
			);
			$result = $this->Admin_model->set_maintenance($data, $mwo_code);
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('production_maintenance_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('production_maintenance_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['single'] = $this->Admin_model->get_single_maintenance();
			$data['employee'] = $this->Admin_model->get_all_employee_master();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$this->load->view('admin/add_maintenance', $data);
		}
	}
	public function maintenance_list()
	{
		$this->form_validation->set_rules("status_of_work", "Material Cost", "required");
		if ($this->form_validation->run() === FALSE) {
			$data['single'] = $this->Admin_model->get_single_maintenance_list();
			
			$data['maintenance'] = $this->Admin_model->get_single_maintenance_list_show();
			$data['last'] = $this->Admin_model->get_last_maintenance_list();
			$this->load->view('admin/maintenance_list', $data);
		} else {
			$result = $this->Admin_model->set_maintenance_list();
			if ($result == '1') {
				$this->session->set_flashdata("success", "Record added successfully!");
				redirect('maintenance_list_details');
			} else if ($result == '2') {
				$this->session->set_flashdata("success", "Record updated successfully!");
				redirect('maintenance_list_details');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function maintenance_list_details()
	{
		$this->load->view("admin/maintenance_list_details");
	}

	public function maintenance_report()
	{
		$date_range = $this->input->get('date', true);
		$type_of_action = $this->input->get('type_of_action', true);
		$machine_id = $this->input->get('machine_id', true);
		$mold_id = $this->input->get('mold_id', true);
		$from_date = null;
		$to_date = null;

		if (!empty($date_range)) {
			$parts = explode('to', $date_range);
			$first = isset($parts[0]) ? trim($parts[0]) : '';
			$second = isset($parts[1]) ? trim($parts[1]) : '';

			if (!empty($first)) {
				$from_date = date('Y-m-d', strtotime($first));
			}

			if (!empty($second)) {
				$to_date = date('Y-m-d', strtotime($second));
			} else {
				$to_date = $from_date;
			}
		}

		$data['machine'] = $this->Admin_model->get_all_machine_master();
		$data['article'] = $this->Admin_model->get_all_article();

		$data['pm_schedule'] = $this->Admin_model->get_preventive_maintenance_report($from_date, $to_date, $type_of_action, $machine_id, $mold_id);
		$data['bm_schedule'] = $this->Admin_model->get_breakdown_maintenance_report($from_date, $to_date, $type_of_action, $machine_id, $mold_id);
		$data['cost_report'] = $this->Admin_model->get_maintenance_cost_report($from_date, $to_date, $type_of_action, $machine_id, $mold_id);
		$this->load->view('admin/maintenance_report', $data);
	}

	public function printing_report()
	{
		// Automatically populate realistic default rates in tbl_impression_rate for testing/demo if not configured
		$ir_count = $this->db->where('is_deleted', '0')->count_all_results('tbl_impression_rate');
		if ($ir_count < 5) {
			$articles_list = $this->db->where('is_deleted', '0')->get('tbl_mould_parts')->result();
			foreach ($articles_list as $art) {
				$exists = $this->db->where('article_id', $art->id)->where('is_deleted', '0')->get('tbl_impression_rate')->row();
				if (!$exists) {
					$name = strtoupper($art->article_name);
					$rate = 2.00; // Standard fallback
					if (strpos($name, '1LTR') !== false) {
						$rate = 1.20;
					} elseif (strpos($name, '4LTR') !== false) {
						$rate = 2.50;
					} elseif (strpos($name, '10LTR') !== false) {
						$rate = 4.00;
					} elseif (strpos($name, '20LTR') !== false) {
						$rate = 6.50;
					}
					$this->db->insert('tbl_impression_rate', array(
						'article_id' => $art->id,
						'impression_rate' => $rate,
						'status' => '1',
						'is_deleted' => '0',
						'created_on' => date('Y-m-d H:i:s'),
					));
				}
			}
		}

		$date_range = $this->input->get('date', true);
		$party_id   = $this->input->get('party_id', true);
		$brand_id   = $this->input->get('brand_id', true);
		$article_id = $this->input->get('article_id', true);
		$from_date  = null;
		$to_date    = null;

		if (!empty($date_range)) {
			$parts  = explode('to', $date_range);
			$first  = isset($parts[0]) ? trim($parts[0]) : '';
			$second = isset($parts[1]) ? trim($parts[1]) : '';

			if (!empty($first)) {
				$from_date = date('Y-m-d', strtotime($first));
			}

			if (!empty($second)) {
				$to_date = date('Y-m-d', strtotime($second));
			} else {
				$to_date = $from_date;
			}
		}

		$data['parties']                  = $this->Admin_model->get_all_entities('tbl_customers');
		$data['brands']                   = $this->Admin_model->get_all_entities('tbl_brand_master');
		$data['articles']                 = $this->Admin_model->get_all_container_articles();
		$data['customer_purchase_history'] = $this->Admin_model->get_printing_customer_purchase_history($from_date, $to_date, $party_id);
		$data['printing_report_data']     = $this->Admin_model->get_printing_impression_report($from_date, $to_date, $party_id, $brand_id, $article_id);
		$data['store_issue_report']       = $this->Admin_model->get_printing_store_issue_report($from_date, $to_date, $party_id);
		$this->load->view('admin/printing_report', $data);
	}

	public function production_report()
	{
		$date_raw = $this->input->get('date', true);
		$machine_id = $this->input->get('machine_id', true);
		$article_id = $this->input->get('article_id', true);
		$report_type = $this->input->get('report_type', true);
		
		$is_form_submit = ($date_raw !== null || $machine_id !== null || $article_id !== null);
		$date_range = $date_raw !== null ? (string)$date_raw : '';
		
		$from_date = null;
		$to_date = null;

		if (!empty($date_range)) {
			$normalized_range = str_replace(' - ', ' to ', $date_range);
			$parts = explode('to', $normalized_range);
			$first = isset($parts[0]) ? trim($parts[0]) : '';
			$second = isset($parts[1]) ? trim($parts[1]) : '';

			if (!empty($first)) {
				$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $first)));
			}

			if (!empty($second)) {
				$to_date = date('Y-m-d', strtotime(str_replace('/', '-', $second)));
			} else {
				$to_date = $from_date;
			}
		}

		$data['report_type'] = !empty($report_type) ? $report_type : '';
		$data['selected_date'] = $date_range;
		$data['selected_machine_id'] = $machine_id;
		$data['selected_article_id'] = $article_id;
		$data['machine'] = $this->Admin_model->get_all_machine_master();
		$data['article'] = $this->Admin_model->get_all_article();

		$data['selected_article_name'] = 'All';
		if (!empty($article_id) && !empty($data['article'])) {
			foreach ($data['article'] as $a) {
				if (isset($a->id) && (string) $a->id === (string) $article_id) {
					$data['selected_article_name'] = !empty($a->article_name) ? $a->article_name : 'All';
					break;
				}
			}
		}

		$bom = null;
		if (!empty($article_id)) {
			$bom = $this->Admin_model->get_artical_bom($article_id);
		}
		$data['std_cycle_time'] = (!empty($bom) && isset($bom->std_cycle_time)) ? (float) $bom->std_cycle_time : null;

		// Only fetch report data when a date is actually selected to prevent massive database queries
		$has_filters = !empty($from_date);

		$data['overview_rows']  = $has_filters ? $this->Admin_model->get_production_report_plan_vs_actual($from_date, $to_date, $machine_id, $article_id) : [];
		$data['downtime_rows']  = $has_filters ? $this->Admin_model->get_production_report_downtime_analysis($from_date, $to_date, $machine_id, $article_id) : [];
		$data['spc_rows']       = $has_filters ? $this->Admin_model->get_production_report_spc_part_weight($from_date, $to_date, $machine_id, $article_id) : [];
		$data['summary_rows']   = $has_filters ? $this->Admin_model->get_production_report_sheet_summary($from_date, $to_date, $machine_id, $article_id) : [];
		$data['detail_rows']    = $has_filters ? $this->Admin_model->get_production_report_sheet_details($from_date, $to_date, $machine_id, $article_id) : [];
		$data['rejection_rows'] = $has_filters ? $this->Admin_model->get_production_report_sheet_rejections($from_date, $to_date, $machine_id, $article_id) : [];
		$data['balance_rows']   = $has_filters ? $this->Admin_model->get_production_report_sheet_balance($from_date, $to_date, $machine_id, $article_id) : [];
		$data['used_rm_rows']   = $has_filters ? $this->Admin_model->get_production_report_used_raw_materials($from_date, $to_date, $machine_id, $article_id) : [];
		$data['used_mb_rows']   = $has_filters ? $this->Admin_model->get_production_report_used_master_batches($from_date, $to_date, $machine_id, $article_id) : [];
		$data['sheet_meta']     = $this->Admin_model->get_production_report_machine_meta($machine_id);
		$data['has_filters']    = $has_filters;

		$unique_dates = [];
		if (!empty($data['overview_rows'])) {
			foreach ($data['overview_rows'] as $r) {
				if (!empty($r->production_date)) {
					$unique_dates[] = date('Y-m-d', strtotime($r->production_date));
				}
			}
		}
		$unique_dates = array_values(array_unique($unique_dates));
		sort($unique_dates);

		$data['day_by_day_reports'] = [];
		if ($has_filters && !empty($unique_dates)) {
			// Fetch pre-grouped data for RM and MB just once for the whole date range
			$all_rm_day_by_day = $this->Admin_model->get_production_report_used_raw_materials_day_by_day($from_date, $to_date, $machine_id, $article_id);
			$all_mb_day_by_day = $this->Admin_model->get_production_report_used_master_batches_day_by_day($from_date, $to_date, $machine_id, $article_id);

			foreach ($unique_dates as $udate) {
				$day_overview = [];
				$day_summary = [];
				$day_rejection = [];
				$day_balance = [];
				$day_used_rm = [];
				$day_used_mb = [];

				if (!empty($data['overview_rows'])) {
					foreach ($data['overview_rows'] as $r) {
						if (!empty($r->production_date) && date('Y-m-d', strtotime($r->production_date)) === $udate) {
							$day_overview[] = $r;
						}
					}
				}

				if (!empty($data['summary_rows'])) {
					foreach ($data['summary_rows'] as $r) {
						if (!empty($r->production_date) && date('Y-m-d', strtotime($r->production_date)) === $udate) {
							$day_summary[] = $r;
						}
					}
				}

				if (!empty($data['rejection_rows'])) {
					foreach ($data['rejection_rows'] as $r) {
						if (!empty($r->production_date) && date('Y-m-d', strtotime($r->production_date)) === $udate) {
							$day_rejection[] = $r;
						}
					}
				}

				if (!empty($data['balance_rows'])) {
					foreach ($data['balance_rows'] as $r) {
						if (!empty($r->production_date) && date('Y-m-d', strtotime($r->production_date)) === $udate) {
							$day_balance[] = $r;
						}
					}
				}

				if (!empty($all_rm_day_by_day)) {
					foreach ($all_rm_day_by_day as $r) {
						if (!empty($r->prod_date) && $r->prod_date === $udate) {
							$day_used_rm[] = $r;
						}
					}
				}

				if (!empty($all_mb_day_by_day)) {
					foreach ($all_mb_day_by_day as $r) {
						if (!empty($r->prod_date) && $r->prod_date === $udate) {
							$day_used_mb[] = $r;
						}
					}
				}

				$data['day_by_day_reports'][] = [
					'date' => date('d-m-Y', strtotime($udate)),
					'overview_rows' => $day_overview,
					'summary_rows' => $day_summary,
					'rejection_rows' => $day_rejection,
					'balance_rows' => $day_balance,
					'used_rm_rows' => $day_used_rm,
					'used_mb_rows' => $day_used_mb,
				];
			}
		}

		// For shareable sheet
		$data['sheet_total_actual_qty'] = 0;
		$data['sheet_total_operating_hrs'] = 0;
		$data['sheet_total_ideal_seconds'] = 0;
		if (!empty($data['overview_rows'])) {
			foreach ($data['overview_rows'] as $r) {
				$good_qty = (float) ($r->good_qty ?? 0);
				$rej_qty = (float) ($r->rejection_qty ?? 0);
				$actual_qty = $good_qty + $rej_qty;
				$scheduled_minutes = (float) ($r->scheduled_minutes ?? 0);
				$downtime_minutes = (float) ($r->downtime_minutes ?? 0);
				$operating_minutes = max($scheduled_minutes - $downtime_minutes, 0);
				$data['sheet_total_actual_qty'] += $actual_qty;
				$data['sheet_total_operating_hrs'] += ($operating_minutes / 60);
				$data['sheet_total_ideal_seconds'] += (float) ($r->ideal_seconds ?? 0);
			}
		}
		$data['sheet_avg_qty_per_hr'] = ($data['sheet_total_operating_hrs'] > 0)
			? ($data['sheet_total_actual_qty'] / $data['sheet_total_operating_hrs'])
			: 0;
		$data['sheet_avg_cycle_time_sec'] = ($data['sheet_total_actual_qty'] > 0 && $data['sheet_total_ideal_seconds'] > 0)
			? ($data['sheet_total_ideal_seconds'] / $data['sheet_total_actual_qty'])
			: null;

		$data['overview_count'] = is_array($data['overview_rows']) ? count($data['overview_rows']) : 0;
		$data['downtime_count'] = is_array($data['downtime_rows']) ? count($data['downtime_rows']) : 0;
		$data['spc_count'] = is_array($data['spc_rows']) ? count($data['spc_rows']) : 0;
		$data['summary_count'] = is_array($data['summary_rows']) ? count($data['summary_rows']) : 0;
		$data['detail_count'] = is_array($data['detail_rows']) ? count($data['detail_rows']) : 0;
		$data['rejection_count'] = is_array($data['rejection_rows']) ? count($data['rejection_rows']) : 0;
		$data['balance_count'] = is_array($data['balance_rows']) ? count($data['balance_rows']) : 0;

		$data['summary_total_approved_qty'] = 0;
		$data['summary_total_weight'] = 0;
		foreach ($data['summary_rows'] as $row) {
			$data['summary_total_approved_qty'] += (float) str_replace(',', '', (string) $row->approved_qty);
			$data['summary_total_weight'] += (float) str_replace(',', '', (string) $row->total_weight);
		}

		$data['detail_total_approved_qty'] = 0;
		$data['detail_total_hourly_qty'] = 0;
		foreach ($data['detail_rows'] as $row) {
			$data['detail_total_approved_qty'] += (float) str_replace(',', '', (string) $row->approved_qty);
			$hourly_fields = [
				$row->qty_eight_nine,
				$row->qty_nine_ten,
				$row->qty_ten_eleven,
				$row->qty_eleven_twelve,
				$row->qty_twelve_thirteen,
				$row->qty_thirteen_fourteen,
				$row->qty_fourteen_fifteen,
				$row->qty_fifteen_sixteen,
				$row->qty_sixteen_seventeen,
				$row->qty_seventeen_eighteen,
				$row->qty_eighteen_nineteen,
				$row->qty_nineteen_twenty,
				$row->qty_twenty_twentyone,
				$row->qty_twentyone_twentytwo,
				$row->qty_twentytwo_twentythree,
				$row->qty_twentythree_zero,
				$row->qty_zero_one,
				$row->qty_one_two,
				$row->qty_two_three,
				$row->qty_three_four,
				$row->qty_four_five,
				$row->qty_five_six,
				$row->qty_six_seven,
				$row->qty_seven_eight,
			];

			foreach ($hourly_fields as $hourly_value) {
				$data['detail_total_hourly_qty'] += (float) str_replace(',', '', (string) $hourly_value);
			}
		}

		$data['rejection_total_qty'] = 0;
		foreach ($data['rejection_rows'] as $row) {
			$data['rejection_total_qty'] += (float) str_replace(',', '', (string) $row->total_qty);
		}

		$data['balance_total_rm_qty'] = 0;
		$data['balance_total_mb_qty'] = 0;
		foreach ($data['balance_rows'] as $row) {
			$data['balance_total_rm_qty'] += (float) str_replace(',', '', (string) $row->rm_total_qty);
			$data['balance_total_mb_qty'] += (float) str_replace(',', '', (string) $row->mb_total_qty);
		}

		$this->load->view('admin/production_report', $data);
	}
	public function plant_list()
	{
		$this->form_validation->set_rules('plant_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_plant_master();
			$this->load->view('admin/plant_list', $data);
		} else {
			$result = $this->Admin_model->set_plant_list();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('plant_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('plant_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function add_printing_unit_list()
	{
		$this->form_validation->set_rules('printing_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_printing_unit_master();
			$this->load->view('admin/printing_unit_list', $data);
		} else {
			$result = $this->Admin_model->set_printing_unit();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('printing_unit_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('printing_unit_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function machine_list()
	{
		$this->form_validation->set_rules('machine_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_machine_master();
			$data['department'] = $this->Admin_model->get_all_department_master();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$this->load->view('admin/machine_list', $data);
		} else {
			$result = $this->Admin_model->set_machine_master();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function uom_list()
	{
		$this->form_validation->set_rules('uom_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_uom_master();
			$this->load->view('admin/uom_list', $data);
		} else {
			$result = $this->Admin_model->set_uom_master();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function krivisha_department()
	{
		$this->form_validation->set_rules('department', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_kri_department_master();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$this->load->view('admin/krivisha_department', $data);
		} else {
			$result = $this->Admin_model->set_krivisha_department();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function krivisha_employee()
	{
		$this->form_validation->set_rules('employee_name', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_employee_master();
			$data['krivisha_department'] = $this->Admin_model->get_all_kri_department();
			$data['plant'] = $this->Admin_model->get_all_plant();

			$this->load->view('admin/krivisha_employee', $data);
		} else {
			$result = $this->Admin_model->set_krivisha_employee();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function krivisha_employee_list()
	{
		$this->load->view('admin/krivisha_employee_list');
	}
	public function add_outward_transport()
	{
		$this->form_validation->set_rules('dc_no', 'Enter Name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['order_details'] = $this->Admin_model->get_order_outward_details();
			$data['brand'] = $this->Admin_model->get_all_brands();
			$data['party_name'] = $this->Admin_model->get_all_party_master();
			$data['sub_order'] = $this->Admin_model->get_all_sub_order_details();
			$data['transport'] = $this->Admin_model->get_all_transport();
			$data['location'] = $this->Admin_model->get_all_location();
			$data['plants']   = $this->Admin_model->get_all_plant();

			$this->load->view('admin/add_outward_transport', $data);
		} else {
			$result = $this->Admin_model->set_outward_transport();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('outward_order_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('outward_order_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function outward_order_list()
	{
		$data['party'] = $this->Admin_model->get_all_party_master();
		$this->load->view('admin/outward_order_list',$data);
	}
	public function dispach_order_list()
	{
		$data['party'] = $this->Admin_model->get_all_party_master();
		$data['salesman'] = $this->Admin_model->get_employee_by_department(23);
		$this->load->view('admin/dispach_order_list',$data);
	}
	public function outward_transport_history()
	{
		$data['transport'] = $this->Admin_model->get_all_transport();
		$this->load->view('admin/outward_transport_history',$data);
	}
	public function transport_report()
	{
		$data['party'] = $this->Admin_model->get_all_party_master();
		$data['transport'] = $this->Admin_model->get_all_transport();
		$data['location'] = $this->Admin_model->get_all_location();
		$this->load->view('admin/transport_report',$data);
	}

	public function transport_report_view($transport_id = null)
	{
		if (empty($transport_id)) {
			show_404();
		}

		// Get transport order details
		$data['order'] = $this->Admin_model->get_transport_order_details($transport_id);
		if (empty($data['order'])) {
			show_404();
		}

		// Get dispatch items for this specific transport
		$items = $this->Admin_model->get_outward_dispatch_details_array($transport_id);
		
		// Map array to objects for the view and fix quantity field names
		$data['sub_details'] = array();
		$total_order_qty = 0;
		$total_dispatch_qty = 0;
		
		if (!empty($items) && !isset($items['error'])) {
			foreach ($items as $item) {
				$obj = (object)$item;
				$obj->order_qty = $item['order_quantity'] ?? 0;
				$obj->bill_qty = $item['dispatch_quantity'] ?? 0;
				$obj->article_code = $item['article_id'] ?? '-';
				$obj->unit_price = $item['unit_price'] ?? 0;
				$obj->total_amount = floatval($obj->unit_price) * floatval($obj->bill_qty);
				
				$data['sub_details'][] = $obj;
				
				$total_order_qty += floatval($obj->order_qty);
				$total_dispatch_qty += floatval($obj->bill_qty);
			}
		}

		// Fill in header totals if they are missing
		if (empty($data['order']->order_quantity) || $data['order']->order_quantity == 0) {
			$data['order']->order_quantity = $total_order_qty;
		}
		if (empty($data['order']->dispatch_quantity) || $data['order']->dispatch_quantity == 0) {
			$data['order']->dispatch_quantity = $total_dispatch_qty;
		}
		
		$this->load->view('admin/transport_report_view', $data);
	}

	public function add_own_vehicle()
	{
		$this->form_validation->set_rules('challan_dc_no', '', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_own_vehicle_details();
			$data['out_km'] = $this->Admin_model->get_last_out_km_vehicle_details();
			$data['party_name'] = $this->Admin_model->get_all_party_master();
			$data['location'] = $this->Admin_model->get_all_location();
			$data['vehical'] = $this->Admin_model->get_all_vehical();
			//echo"<pre>";print_r($data);exit;
			$this->load->view('admin/add_own_vehicle', $data);
		} else {
			$result = $this->Admin_model->set_own_vehicle_details();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('own_vehicle_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('own_vehicle_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function own_vehicle_list()
	{
		$data['metrics'] = $this->Admin_model->get_own_vehicle_metrics();
		$data['location'] = $this->Admin_model->get_all_location();
		$data['vehical'] = $this->Admin_model->get_all_vehical();
		$data['party'] = $this->Admin_model->get_all_party_master();
		$this->load->view('admin/own_vehicle_list',$data);
	}
	public function printing_order_list()
	{
		$data['brand'] = $this->Admin_model->get_all_brands();
		// echo"<pre>";print_r($data);exit;
		$data['article'] = $this->Admin_model->get_all_article();
		$this->load->view('admin/printing_order_list',$data);
	}
	
    public function printing_unit_report()
    {
        if ($this->input->post('submit_btn') == "submit_btn") {
            $result = $this->Admin_model->set_printing_unit_report();
            if ($result) {
                $this->session->set_flashdata('success', 'Record added successfully!');
				redirect('printing_order_list');
            }else {
                $this->session->set_flashdata('message', 'Failed to Add!');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
			$data['single'] = $this->Admin_model->get_single_printing_material_report();
            $data['raw_material'] = $this->Admin_model->get_single_printing_inks();
            $data['brand'] = $this->Admin_model->get_all_brands_according_party();
			// echo"<pre>";print_r($data);exit;
            $data['raw_matertal'] = $this->Admin_model->get_other_material_rm_names();
            $data['ink'] = $this->Admin_model->get_other_ink_rm_names();
            $this->load->view('admin/printing_unit_report', $data);
        }
    }
	public function material_report_printing_unit_report_list()
	{
		$data['brand'] = $this->Admin_model->get_all_brands();
		$data['article'] = $this->Admin_model->get_all_article();
		$data['party'] = $this->Admin_model->get_all_party_master();
		$this->load->view('admin/material_report_printing_unit_report_list',$data);
	}
	
	public function purchase_sales_report()
	{
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_purchase_sales_report();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('purchase_sales_report');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data = array();
			$this->load->view('admin/purchase_sales_report', $data);
		}
	}
	public function sales_report_list()
	{
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_sales_report();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('sales_report_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data = array();
			$this->load->view('admin/sales_report_list', $data);
		}
	}
	public function problems_list()
	{
		$this->form_validation->set_rules('problem', 'Enter problem', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_problems_master();
			$this->load->view('admin/problems_list', $data);
		} else {
			$result = $this->Admin_model->set_problem();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function add_task()
	{
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_task();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('task_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('auto_task_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['single'] = $this->Admin_model->get_single_problems_master();
			$data['party_name'] = $this->Admin_model->get_all_party_master();
			$data['krivisha_department'] = $this->Admin_model->get_all_kri_department();
			$data['krivisha_employee'] = $this->Admin_model->get_all_kri_employee();
			$this->load->view('admin/add_task', $data);
		}
	}
	public function update_auto_manual_task()
	{
		$profile = $this->Admin_model->get_user_profile();
		$access = $this->Admin_model->get_staff_priiliges();
		$is_auto_task = ($this->uri->segment(3) == "143");

		if (!empty($profile) && $profile->is_admin != "1") {
			if ($is_auto_task) {
				$has_auto_access = in_array('auto_task_list', $access) || in_array('auto_task_access', $access);
				$can_reply_auto = in_array('update_auto_manual_task', $access) || in_array('auto_task_reply', $access);
				if (!($has_auto_access && $can_reply_auto)) {
					$this->session->set_flashdata('message', 'Sorry! You don’t have reply access for auto tasks!');
					redirect('dashboard');
				}
			} else {
				$has_manual_access = in_array('task_list', $access) || in_array('manual_task_access', $access);
				$can_reply_manual = in_array('update_auto_manual_task', $access) || in_array('manual_task_reply', $access);
				if (!($has_manual_access && $can_reply_manual)) {
					$this->session->set_flashdata('message', 'Sorry! You don’t have reply access for manual tasks!');
					redirect('dashboard');
				}
			}
		}
		
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_update_task();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('auto_task_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('task_list');
			} else if($result == '3') {
				$id = $this->uri->segment(2);
				$this->db->where('id', $id);
				$task_data = $this->db->get('tbl_manual_task')->row();
				redirect('add_order' . '?party_id=' . $this->input->post('party_id') .'&sales_person_id=' . $task_data->assign_to_id);
			} else if($result == '4') {
				// Logistics order - cannot close manually
				$this->session->set_flashdata('message', 'Logistics orders cannot be closed manually. Please use the dispatch workflow with transport assignment.');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['single'] = $this->Admin_model->get_single_update_task();
			$data['krivisha_department'] = $this->Admin_model->get_all_kri_department();
			$data['krivisha_employee'] = $this->Admin_model->get_all_kri_employee();
			$this->load->view('admin/update_auto_manual_task', $data);
		}
	}
	public function update_task()
	{
		$this->load->view('admin/update_task');
	}
	public function task_list()
	{
		$this->load->view('admin/task_list');
	}
	public function auto_task_list()
	{
		$this->load->view('admin/auto_task_list');
	}
	public function task_update_list()
	{
		$this->load->view('admin/task_update_list');
	}
	public function add_production_bom()
	{
		$this->form_validation->set_rules('batch', 'Enter batch', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_production_bom();
			$data['bom'] = $this->Admin_model->get_single_bom();
			$data['uom'] = $this->Admin_model->get_all_type_of_uom();
			$data['particulars'] = $this->Admin_model->get_all_particulars_type();
			// $data['sub_category'] = $this->Admin_model->get_all_sub_category_type();
			$data['sub_categories'] = [];
			if (!empty($data['particulars'])) {
				foreach ($data['particulars'] as $particular) {
					$data['sub_categories'][$particular->id] =
						$this->Admin_model->get_all_sub_category_type($particular->particulars_type);
					// echo "<pre>";print_r($data['particulars']);
				}
			}
			// echo "<pre>";print_r($data['sub_categories']);
			// exit;
			$data['article'] = $this->Admin_model->get_all_article();
			$data['make'] = $this->Admin_model->get_all_rm_make_master();
			$data['ink'] = $this->Admin_model->get_all_rm_ink_master();
			$this->load->view('admin/add_production_bom', $data);
		} else {
			$result = $this->Admin_model->set_production_bom();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('production_bom_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('production_bom_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function production_bom_list()
	{
		$this->load->view('admin/production_bom_list');
	}
	public function production_bom_details()
	{
		$this->load->view('admin/production_bom_details');
	}
	public function production_maintenance_list()
	{
		$data['single'] = $this->Admin_model->get_single_maintenance();
		$this->load->view("admin/production_maintenance_list", $data);
	}
	public function add_order()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			error_log(print_r($_POST, true), 3, 'c:/xampp/htdocs/krivisha/post_debug.log');
		}
		$this->form_validation->set_rules('name', 'Enter name', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_order_details();
			$data['sub_order'] = $this->Admin_model->get_all_sub_order_details_add_order();
			//echo "<pre>";print_r($data['sub_order']);exit;
			$data['party_name'] = $this->Admin_model->get_all_party_master();
			$data['article_group'] = $this->Admin_model->get_all_article_gruop();
			$data['brand'] = $this->Admin_model->get_all_brands();
			$this->load->view('admin/add_order', $data);
		} else {
			$result = $this->Admin_model->set_order_details();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('order_list');
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('order_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function cancel_order() {
		$order_id = $this->uri->segment(2);
        $this->db->where('id', $order_id);
        $this->db->update('tbl_order_details', ['order_status' => 6]);

        $this->session->set_flashdata('success', 'Order cancelled successfully!');
		redirect($_SERVER['HTTP_REFERER']);
    }
	public function order_list()
	{
		$data['party'] = $this->Admin_model->get_all_party_master();
		$this->load->view('admin/order_list', $data);
	}

	//----------------------------------Hidden Functions----------------------------------

	public function brand_type_list()
	{
		$this->form_validation->set_rules('brand_type', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_brand_type();

			$this->load->view('admin/brand_type_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_brand_type();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function brand_department_list()
	{
		$this->form_validation->set_rules('department', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_department();
			$this->load->view('admin/brand_department_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_department();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function party_designation_list()
	{
		$this->form_validation->set_rules('designation', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_designation();
			$this->load->view('admin/party_designation_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_designation();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('party_designation_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function party_nature_list()
	{
		$this->form_validation->set_rules('nature_of_business', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_nature_of_business();
			$this->load->view('admin/party_nature_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_nature_of_business();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('party_nature_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function party_type_businesss_list()
	{
		$this->form_validation->set_rules('type_of_business', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_type_of_business();
			$this->load->view('admin/party_type_businesss_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_type_of_business();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('party_type_businesss_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function group_of_list()
	{
		$this->form_validation->set_rules('group_of_article', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_group_of_list();
			$this->load->view('admin/group_of_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_group_of_list();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('group_of_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function alanky_bolt_list()
	{
		$this->form_validation->set_rules('alankey_bolt', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_alanky_bolt();
			$this->load->view('admin/alanky_bolt_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_alanky_bolt();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('alanky_bolt_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function type_of_mould_list()
	{
		$this->form_validation->set_rules('type_of_mould', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_type_of_mould();
			$this->load->view('admin/type_of_mould_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_type_of_mould();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('type_of_mould_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function air_pin_list()
	{
		$this->form_validation->set_rules('air_pin', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_air_pin();
			$this->load->view('admin/air_pin_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_air_pin();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('air_pin_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function spring_list()
	{
		$this->form_validation->set_rules('spring', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_spring();
			$this->load->view('admin/spring_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_sprin();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('spring_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function pu_nipple_list()
	{
		$this->form_validation->set_rules('pu_nipples', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_pu_nipples();
			$this->load->view('admin/pu_nipple_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_pu_nipples();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('pu_nipple_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function ejector_pin_list()
	{
		$this->form_validation->set_rules('ejector_pin', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_ejector_pin();
			$this->load->view('admin/ejector_pin_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_ejector_pin();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('ejector_pin_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function i_bolt_list()
	{
		$this->form_validation->set_rules('i_bolt', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_i_bolt();
			$this->load->view('admin/i_bolt_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_i_bolt();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('i_bolt_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function cord_list()
	{
		$this->form_validation->set_rules('cord', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_cord();
			$this->load->view('admin/cord_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_cord();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('cord_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function o_ring_list()
	{
		$this->form_validation->set_rules('o_ring', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_o_ring();
			$this->load->view('admin/o_ring_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_o_ring();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('o_ring_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function insert_slot_list()
	{
		$this->form_validation->set_rules('insert_slot_plate', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_insert_slot_plate();
			$this->load->view('admin/insert_slot_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_insert_slot_plate();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('insert_slot_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function core_cylender_list()
	{
		$this->form_validation->set_rules('core_cylinder_seal', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_core_cylinder_seal();
			$this->load->view('admin/core_cylender_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_core_cylinder_seal();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('core_cylender_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function seal_list()
	{
		$this->form_validation->set_rules('seal', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_seal();
			$this->load->view('admin/seal_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_seal();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('seal_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function hope_pipe_list()
	{
		$this->form_validation->set_rules('hose_pipe', 'Enter type', 'required');
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_sub_hope_pipe();
			$this->load->view('admin/hope_pipe_list', $data);
		} else {
			$result = $this->Admin_model->set_sub_hope_pipe();
			if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect('hope_pipe_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}
	public function rm_make_list()
	{
		$this->load->view('admin/rm_make_list');
	}
	public function rm_type_list()
	{
		$this->load->view('admin/rm_type_list');
	}
	public function mb_make_list()
	{
		$this->load->view('admin/mb_make_list');
	}
	public function machine_department_list()
	{
		$this->load->view('admin/machine_department_list');
	}
	
	public function add_previleges(){
		$this->form_validation->set_rules('previleges','previleges','required');
		if($this->form_validation->run() === FALSE){
			$data['previleges'] = $this->Admin_model->get_all_entities('tbl_previleges');
			$data['single'] = $this->Admin_model->get_single_entity_active('tbl_previleges');
			$this->load->view('admin/add_previleges',$data);
		}else{
			$result = $this->Admin_model->add_previleges();
			if($result == 1){
				$this->session->set_flashdata('success','Previlege successfully.');
			}else{
				$this->session->set_flashdata('success','Previlege updated successfully.');
			}	
			redirect('add-previleges');
		}
	}
	public function add_submenu(){
		$this->form_validation->set_rules('previleges','previleges','required');
		if($this->form_validation->run() === FALSE){
			$data['previleges'] = $this->Admin_model->get_all_entities('tbl_previleges');
			$data['submenu'] = $this->Admin_model->get_subprevilege();
			$data['single'] = $this->Admin_model->get_single_entity_active('tbl_subprevilege');
			$this->load->view('admin/add_submenu',$data);
		}else{
			$result = $this->Admin_model->add_submenu();
			if($result == 1){
				$this->session->set_flashdata('success','Subprevilege successfully.');
			}else{
				$this->session->set_flashdata('success','Subprevilege updated successfully.');
			}	
			redirect('add-submenu');
		}
	}
	public function manage_priviledge(){	
		if($this->input->post('form_submitted') != "1" ){
			$this->Admin_model->ensure_task_detail_privilege_links();
			$data['employee'] = $this->Admin_model->get_active_staff();
			$data['privilege'] = $this->Admin_model->get_active_privileges();
			$data['single_employee'] =$this->Admin_model->get_single_privilege_staff();
			$data['user_privilege'] = $this->Admin_model->get_staff_added_privilege();
			$this->load->view("admin/manage_previlege",$data);	
		}else{
			$staff_id = (int)$this->input->post('employee');
			if (empty($staff_id)) {
				$staff_id = (int)$this->uri->segment(2);
			}

			if (empty($staff_id)) {
				$this->session->set_flashdata("message", "Please select an employee.");
				redirect("manage-privilege");
				return;
			}

			// Safety: if no checkboxes selected, post('link') is null
			$links  = $this->input->post('link');
			$values = (!empty($links) && is_array($links)) ? implode(',', $links) : '';

			$data = [
				'staff_id'   => $staff_id,
				'previleges' => $values,
				'is_deleted' => '0',
				'status'     => '1',
			];

			// Check for existing record
			$existing = $this->db->where('staff_id', $staff_id)
			                     ->get('tbl_assign_previlege')->result();

			if (empty($existing)) {
				$data['created_on'] = date('Y-m-d H:i:s');
				$this->db->insert('tbl_assign_previlege', $data);
				$this->session->set_flashdata("success", "Privileges Assigned Successfully!");
			} else {
				// Remove duplicates — keep first row only
				$keep_id = $existing[0]->id;
				if (count($existing) > 1) {
					$dup_ids = array_slice(array_column($existing, 'id'), 1);
					$this->db->where_in('id', $dup_ids)->delete('tbl_assign_previlege');
				}
				$this->db->where('id', $keep_id)->update('tbl_assign_previlege', $data);
				$this->session->set_flashdata("success", "Assigned Privilege Updated Successfully!");
			}

			redirect("manage-privilege/" . $staff_id);
		}
	}
	public function mb_inward_form() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_mb_inward_form();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('mb_inward_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['color'] = $this->Admin_model->get_all_colors();
			$data['party_name'] = $this->Admin_model->get_all_supplier_party_master();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$data['extra_payment_option'] = $this->Admin_model->get_all_extra_payment_option();
			$this->load->view('admin/mb_inward_form', $data);
		}
    }
	public function mb_inward_list() {
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['party'] = $this->Admin_model->get_all_supplier_party_master();
        $this->load->view('admin/mb_inward_list',$data);
    }

	public function rm_inward_form() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_rm_inward_form();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('rm_inward_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['raw_material'] = $this->Admin_model->get_all_raw_material();
			$data['party_name'] = $this->Admin_model->get_all_supplier_party_master();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$data['extra_payment_option'] = $this->Admin_model->get_all_extra_payment_option();
			$this->load->view('admin/rm_inward_form', $data);
		}
    }
	public function view_inward_form() {
        $this->load->view('admin/view_inward_form',);
    }

    public function rm_inward_list() {
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['party'] = $this->Admin_model->get_all_supplier_party_master();
        $this->load->view('admin/rm_inward_list',$data);
    }
	

    public function material_artical_requistition_from() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_material_artical_requistion_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_from_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['raw_material'] = $this->Admin_model->get_all_raw_material();
			$data['plant'] = $this->Admin_model->get_all_plant_for_request_material();
			$this->load->view('admin/material_artical_requistition_from',$data);
		}
    }
	public function view_material_artical_requistition_from() {
        $this->load->view('admin/view_material_artical_requistition_from');
    }
	
	public function article_production_stock_report() {
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['article'] = $this->Admin_model->get_all_article();
        $this->load->view('admin/article_production_stock_report',$data);
    }
    public function material_artical_requistition_from_list() {
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/material_artical_requistition_from_list',$data);
    }

    public function view_material_artical_requistition_to_list() {

		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_material_requistion_qty_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_to_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['material_requistion_list'] = $this->Admin_model->get_material_request_to_list();
			// echo"<pre>";print_r($data['material_requistion_list']);exit;
        	$this->load->view('admin/view_material_artical_requistition_to_list',$data);
		}
		
    }
	public function view__artical_requistition_to_list() {

		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_article_requistion_qty_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_to_list');
			}else if ($result == '2') {
				$this->session->set_flashdata('message', 'Failed to add qty not available in stock!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['article_requistion_list'] = $this->Admin_model->get_material_request_to_list();
        $this->load->view('admin/view__artical_requistition_to_list',$data);
		}
		
    }
	public function view_mb_requestion_one_plant_to_other() {

		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_mb_requistion_qty_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_to_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['master_batch_list'] = $this->Admin_model->get_material_request_to_list();
        $this->load->view('admin/view_mb_requestion_one_plant_to_other',$data);
		}
		
    }

    public function material_artical_requistition_to_list() {
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/material_artical_requistition_to_list',$data);
    }

    public function rm_stock_adjustment() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			
			$result = $this->Admin_model->set_rm_stock_adjustment_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('stock_adjustment_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['raw_material'] = $this->Admin_model->get_all_raw_material();	
			$data['plant'] = $this->Admin_model->get_all_plant();
			
			$this->load->view('admin/rm_stock_adjustment',$data);
		}
    }
	public function mb_stock_adjustment() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			
			$result = $this->Admin_model->set_mb_stock_adjustment_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('stock_adjustment_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['color'] = $this->Admin_model->get_all_colors();
			$data['plant'] = $this->Admin_model->get_all_plant();
			$this->load->view('admin/mb_stock_adjustment',$data);
		}
    }
	public function article_stock_adjustment() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			
			$result = $this->Admin_model->set_article_stock_adjustment_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('stock_adjustment_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['article'] = $this->Admin_model->get_all_article();
			$data['plant'] = $this->Admin_model->get_all_plant();
			
			
			$this->load->view('admin/article_stock_adjustment',$data);
		}
    }

    public function stock_adjustment_list() {
		$data['article'] = $this->Admin_model->get_all_article();
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['color'] = $this->Admin_model->get_all_colors();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();	
        $this->load->view('admin/stock_adjustment_list',$data);
    }
	public function total_stock_list() {
		$data['article'] = $this->Admin_model->get_all_article();
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['color'] = $this->Admin_model->get_all_colors();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();	
        $this->load->view('admin/total_stock_list',$data);
    }
	public function required_material_report() {
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/required_material_report', $data);
    }
	public function stock_return_history_report() {
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/stock_return_history_report', $data);
    }
    public function return_stock() {
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/return_stock_to_store',$data);
    }

    public function return_stock_list() {
        $this->load->view('admin/return_stock_list');
    }

	public function raw_material_reorder_level() {
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['plant'] = $this->Admin_model->get_all_plant();
        $this->load->view('admin/raw_material_reorder_level', $data);
    }
	public function material_reorder_report() {
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['article'] = $this->Admin_model->get_all_article();
        $this->load->view('admin/material_reorder_report', $data);
    }

    public function article_reorder_level() {
		$data['plant'] = $this->Admin_model->get_all_plant();
        $data['article'] = $this->Admin_model->get_all_article();
        $this->load->view('admin/article_reorder_level',$data);
    }
	public function stock_report_inward()
	{
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['color'] = $this->Admin_model->get_all_colors();
		$this->load->view('admin/stock_report_inward', $data);
	}


	public function stock_report_raw_material()
	{
		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['article'] = $this->Admin_model->get_all_article();
		$data['color'] = $this->Admin_model->get_all_colors();
		$this->load->view('admin/stock_report_raw_material', $data);
	}

	public function stock_ledger_report()
	{
		// Ensure production_id column exists on master batch history table
		if (!$this->db->field_exists('production_id', 'tbl_master_batch_stock_report_history')) {
			$this->db->query("ALTER TABLE tbl_master_batch_stock_report_history ADD COLUMN production_id INT(11) DEFAULT NULL AFTER master_batch_id");
		}

		// Phase 1: Auto-create the unified stock transactions table if it doesn't exist
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `tbl_stock_transactions` (
				`id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				`transaction_date` DATE         NOT NULL,
				`created_on`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`item_type`        ENUM('raw_material','master_batch','article','mould') NOT NULL,
				`item_id`          INT UNSIGNED NOT NULL,
				`item_name`        VARCHAR(255) DEFAULT NULL,
				`plant_id`         INT UNSIGNED DEFAULT NULL,
				`plant_name`       VARCHAR(100) DEFAULT NULL,
				`transaction_type` VARCHAR(60)  NOT NULL,
				`movement_type`    ENUM('IN','OUT') NOT NULL,
				`qty`              DECIMAL(12,3) NOT NULL DEFAULT 0,
				`uom_id`           INT UNSIGNED DEFAULT NULL,
				`reference_no`     VARCHAR(100) DEFAULT NULL,
				`reference_source` VARCHAR(60)  DEFAULT NULL,
				`balance_qty`      DECIMAL(12,3) NOT NULL DEFAULT 0,
				`created_by`       INT UNSIGNED DEFAULT NULL,
				`legacy_source`    VARCHAR(30)  DEFAULT NULL,
				`legacy_id`        INT UNSIGNED DEFAULT NULL,
				`is_deleted`       TINYINT(1)   NOT NULL DEFAULT 0,
				INDEX `idx_item`     (`item_type`, `item_id`),
				INDEX `idx_plant`    (`plant_id`),
				INDEX `idx_date`     (`transaction_date`),
				INDEX `idx_txn_type` (`transaction_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		");

		$data['plant'] = $this->Admin_model->get_all_plant();
		$data['raw_material'] = $this->Admin_model->get_all_raw_material();
		$data['color'] = $this->Admin_model->get_all_colors();
		$data['article'] = $this->Admin_model->get_all_article();
		$data['mould'] = $this->Admin_model->get_single_type_of_mould();
		$this->load->view('admin/stock_ledger_report', $data);
	}

	public function article_request_from() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_material_artical_requistion_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_from_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['article'] = $this->Admin_model->get_all_article();
			$data['plant'] = $this->Admin_model->get_all_plant_for_request_material();
			$this->load->view('admin/article_request_from',$data);
		}
    }
	public function master_batch_request_from() {
		if ($this->input->post('submit_btn') == "submit_btn") {
			$result = $this->Admin_model->set_master_batch_requistion_data();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect('material_artical_requistition_from_list');
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} else {
			$data['master_batch'] = $this->Admin_model->get_all_colors();
			$data['plant'] = $this->Admin_model->get_all_plant_for_request_material();
			$this->load->view('admin/master_batch_request_from',$data);
		}
    }

	public function apk_upload()
	{
		if ($this->input->post('submit_btn_apk') == "submit_btn_apk") {
			$config['upload_path'] = 'assets/uploads/apk';
			$config['allowed_types'] = '*';
			$config['max_size']      = 512000; 
			$config['encrypt_name']  = TRUE;
			
			if (!is_dir($config['upload_path'])) {
				mkdir($config['upload_path'], 0777, true);
			}

			$this->load->library('upload');
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('apk_file')) {
				$this->session->set_flashdata('message', $this->upload->display_errors());
			} else {
				$files = glob($config['upload_path'] . '*.apk');
				foreach ($files as $file) {
					@unlink($file);
				}
				$upload_data = $this->upload->data();
				$version     = $this->input->post('apk_version');

				// Save versioned file
				$new_name = 'version-' . $version . '.apk';
				rename($upload_data['full_path'], $config['upload_path'] . $new_name);

				// Also update "latest.apk"
				copy($config['upload_path'] . $new_name, $config['upload_path'] . 'latest.apk');

				$this->session->set_flashdata('success', 'APK (v' . $version . ') uploaded successfully!');
			}
		}
		redirect($_SERVER['HTTP_REFERER']);
			
	}

	public function impression_rate($id = '')
	{
		// Ensure transaction tables have the impression_rate column for snapshotting
		if (!$this->db->field_exists('impression_rate', 'tbl_printing_material_report')) {
			$this->db->query("ALTER TABLE tbl_printing_material_report ADD COLUMN impression_rate DECIMAL(10,2) DEFAULT 0 AFTER approvd_qty");
			// One-time sync for existing records
			$this->db->query("UPDATE tbl_printing_material_report r JOIN tbl_impression_rate ir ON r.article_id = ir.article_id SET r.impression_rate = ir.impression_rate WHERE ir.is_deleted = '0'");
		}
		if (!$this->db->field_exists('impression_rate', 'tbl_dispatch_order_data')) {
			$this->db->query("ALTER TABLE tbl_dispatch_order_data ADD COLUMN impression_rate DECIMAL(10,2) DEFAULT 0 AFTER dispatch_quantity");
			// One-time sync for existing records
			$this->db->query("UPDATE tbl_dispatch_order_data d JOIN tbl_impression_rate ir ON d.article_id = ir.article_id SET d.impression_rate = ir.impression_rate WHERE ir.is_deleted = '0'");
		}

		$this->form_validation->set_rules('impression_rate', 'Enter Rate', 'required');
		$this->form_validation->set_rules('article_id', 'Select Article', 'required');
		
		if ($this->form_validation->run() == FALSE) {
			$data['single'] = $this->Admin_model->get_single_impression_rate($id);
			$data['articles'] = $this->Admin_model->get_all_article();
			$data['rates'] = $this->Admin_model->get_all_impression_rates();
			$this->load->view('admin/impression_rate', $data);
		} else {
			$result = $this->Admin_model->set_impression_rate();
			if ($result == '1') {
				$this->session->set_flashdata('success', 'Record added successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '2') {
				$this->session->set_flashdata('success', 'Record updated successfully!');
				redirect($_SERVER['HTTP_REFERER']);
			} else if ($result == '3') {
				$this->session->set_flashdata('message', 'Record already exists!');
				redirect($_SERVER['HTTP_REFERER']);
			} else {
				$this->session->set_flashdata('message', 'Failed to Add!');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	}

	// ─── Stock Transactions Migration ─────────────────────────────────────────

	/**
	 * migrate_stock_transactions()
	 *
	 * One-time backfill: copies all existing rows from
	 * tbl_raw_material_stock_report_history and tbl_master_batch_stock_report_history
	 * into the new tbl_stock_transactions table.
	 *
	 * Access via: http://localhost/krivisha/migrate_stock_transactions
	 * Safe to run multiple times — uses legacy_source + legacy_id to skip duplicates.
	 */
	public function migrate_stock_transactions()
	{
		// Admin-only guard
		if ($this->session->userdata('is_admin') != '1') {
			show_error('Unauthorized', 403);
			return;
		}

		set_time_limit(300);
		$inserted = 0;
		$skipped  = 0;

		// ── 1. Migrate tbl_raw_material_stock_report_history ──────────────────
		$rm_rows = $this->db
			->select('h.*, rm.rm_name, p.plant_name, rm.uom_id as rm_uom_id')
			->from('tbl_raw_material_stock_report_history h')
			->join('tbl_rm_master rm', 'rm.id = h.raw_material_id', 'left')
			->join('tbl_mould_parts mp', 'mp.id = h.article_id', 'left')
			->join('tbl_plant_master p', 'p.id = h.plant_id', 'left')
			->where('h.is_deleted', '0')
			->order_by('h.date ASC, h.id ASC')
			->get()->result();

		foreach ($rm_rows as $row) {
			// Skip if already migrated
			$exists = $this->db->where('legacy_source', 'rm_history')->where('legacy_id', $row->id)->count_all_results('tbl_stock_transactions');
			if ($exists) { $skipped++; continue; }

			// Determine item_type
			$item_type = 'raw_material';
			$item_id   = (int)($row->raw_material_id ?? 0);
			$item_name = $row->rm_name ?? null;
			if (empty($item_id) && !empty($row->article_id)) {
				$item_type = 'article';
				$item_id   = (int)$row->article_id;
				$item_name = null; // fetched by helper
			}
			if (empty($item_id)) { $skipped++; continue; }

			// Map flag → transaction type
			$flag = (string)($row->is_inward_outward ?? '0');
			$map  = _stock_map_flag_to_type($flag, (array)$row, $item_type);

			// Qty
			$qty = 0;
			if ($map['movement_type'] === 'IN') {
				$qty = abs(floatval($row->inward_qty ?? $row->adjusted_qty ?? $row->return_stock_qty ?? $row->approved_qty ?? 0));
			} else {
				$qty = abs(floatval($row->outward_qty ?? $row->adjusted_qty ?? $row->approved_qty ?? 0));
			}
			if ($qty == 0) { $skipped++; continue; }

			// Reference
			$ref_no = null;
			if (!empty($row->production_id)) $ref_no = 'PROD-' . $row->production_id;
			elseif (!empty($row->schedule_id)) $ref_no = 'SCH-' . $row->schedule_id;

			log_stock_transaction([
				'item_type'        => $item_type,
				'item_id'          => $item_id,
				'item_name'        => $item_name,
				'plant_id'         => (int)($row->plant_id ?? 0),
				'plant_name'       => $row->plant_name ?? null,
				'transaction_type' => $map['transaction_type'],
				'movement_type'    => $map['movement_type'],
				'qty'              => $qty,
				'uom_id'           => $row->uom_id ?? $row->rm_uom_id ?? null,
				'reference_no'     => $ref_no,
				'reference_source' => 'rm_history',
				'balance_qty'      => floatval($row->total_quantity ?? 0),
				'transaction_date' => !empty($row->date) ? $row->date : date('Y-m-d', strtotime($row->created_on ?? 'now')),
				'legacy_source'    => 'rm_history',
				'legacy_id'        => (int)$row->id,
			]);
			$inserted++;
		}

		// ── 2. Migrate tbl_master_batch_stock_report_history ──────────────────
		$mb_rows = $this->db
			->select('h.*, mb.name as mb_name, p.plant_name')
			->from('tbl_master_batch_stock_report_history h')
			->join('tbl_mb_master mb', 'mb.id = h.master_batch_id', 'left')
			->join('tbl_plant_master p', 'p.id = h.plant_id', 'left')
			->where('h.is_deleted', '0')
			->order_by('h.date ASC, h.id ASC')
			->get()->result();

		foreach ($mb_rows as $row) {
			$exists = $this->db->where('legacy_source', 'mb_history')->where('legacy_id', $row->id)->count_all_results('tbl_stock_transactions');
			if ($exists) { $skipped++; continue; }

			$item_id = (int)($row->master_batch_id ?? 0);
			if (empty($item_id)) { $skipped++; continue; }

			$flag = (string)($row->is_inward_outward ?? '0');
			$map  = _stock_map_flag_to_type($flag, (array)$row, 'master_batch');

			$qty = 0;
			if ($map['movement_type'] === 'IN') {
				$qty = abs(floatval($row->inward_qty ?? $row->adjusted_qty ?? $row->return_stock_qty ?? 0));
			} else {
				$qty = abs(floatval($row->outward_qty ?? $row->adjusted_qty ?? 0));
			}
			if ($qty == 0) { $skipped++; continue; }

			log_stock_transaction([
				'item_type'        => 'master_batch',
				'item_id'          => $item_id,
				'item_name'        => $row->mb_name ?? null,
				'plant_id'         => (int)($row->plant_id ?? 0),
				'plant_name'       => $row->plant_name ?? null,
				'transaction_type' => $map['transaction_type'],
				'movement_type'    => $map['movement_type'],
				'qty'              => $qty,
				'reference_source' => 'mb_history',
				'balance_qty'      => floatval($row->total_quantity ?? 0),
				'transaction_date' => !empty($row->date) ? $row->date : date('Y-m-d', strtotime($row->created_on ?? 'now')),
				'legacy_source'    => 'mb_history',
				'legacy_id'        => (int)$row->id,
			]);
			$inserted++;
		}

		echo '<h2>Migration Complete</h2>';
		echo '<p><b>Inserted:</b> ' . $inserted . ' rows</p>';
		echo '<p><b>Skipped</b> (already migrated or zero qty): ' . $skipped . ' rows</p>';
		echo '<p><a href="' . base_url('stock_ledger_report') . '">Go to Stock Ledger Report</a></p>';
	}

	// ─── Process Parameter Sheet ──────────────────────────────────────────────

	public function process_parameter_list()
	{
		$data['machines'] = $this->Admin_model->get_all_machine_master();
		$data['plants']   = $this->Admin_model->get_all_plant();
		$data['articles'] = $this->Admin_model->get_all_article();
		$this->load->view('admin/process_parameter_list', $data);
	}

	public function process_parameter_view($id = '')
	{
		$data['record'] = $this->Admin_model->get_single_process_parameter($id);
		if (empty($data['record'])) {
			$this->session->set_flashdata('message', 'Record not found.');
			redirect('process_parameter_list');
		}
		$this->load->view('admin/process_parameter_view', $data);
	}
}
