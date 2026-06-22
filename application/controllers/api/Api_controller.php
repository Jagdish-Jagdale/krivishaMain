<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Api_controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Set JSON response headers for all API endpoints
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    public function get_app_login()
    {
        $this->Api_model->get_app_login();
    }
    public function get_user_profile_api()
    {
        $this->Api_model->get_user_profile_api();
    }
    public function get_all_department_api()
    {
        $this->Api_model->get_all_department_api();
    }
    public function change_current_password_api()
    {
        $this->Api_model->change_current_password_api();
    }
    public function cancel_order_api()
    {
        $this->Api_model->cancel_order_api();
    }
    public function update_user_profile_api()
    {
        $this->Api_model->update_user_profile_api();
    }
    public function get_all_party_details_api()
    {
        $this->Api_model->get_all_party_details_api();
    }
    public function get_employee_according_department_api()
    {
        $this->Api_model->get_employee_according_department_api();
    }
    public function set_manual_task_api()
    {
        $this->Api_model->set_manual_task_api();
    }
    public function get_all_order_list_api()
    {
        $this->Api_model->get_all_order_list_api();
    }
    public function get_all_manual_task_list_api()
    {
        $this->Api_model->get_all_manual_task_list_api();
    }
    public function get_all_auto_task_list_api()
    {
        $this->Api_model->get_all_auto_task_list_api();
    }
    public function get_all_task_history_api()
    {
        $this->Api_model->get_all_task_history_api();
    }
    public function set_update_manual_task_api()
    {
        $this->Api_model->set_update_manual_task_api();
    }
    public function get_all_plant_api()
    {
        $this->Api_model->get_all_plant_api();
    }
    public function get_subcategory_according_maintenance_api()
    {
        $this->Api_model->get_subcategory_according_maintenance_api();
    }
    public function get_all_sub_types_problems_api()
    {
        $this->Api_model->get_all_sub_types_problems_api();
    }
    public function set_maintenance_data_api()
    {
        $this->Api_model->set_maintenance_data_api();
    }
    public function get_all_maintenance_list_api()
    {
        $this->Api_model->get_all_maintenance_list_api();
    }
    public function get_all_own_vehicle_api()
    {
        $this->Api_model->get_all_own_vehicle_api();
    }
    public function get_all_location_api()
    {
        $this->Api_model->get_all_location_api();
    }
    public function set_own_vehicle_data_api()
    {
        $this->Api_model->set_own_vehicle_data_api();
    }
    public function get_all_own_vehicle_list_api()
    {
        $this->Api_model->get_all_own_vehicle_list_api();
    }
    public function get_all_party_history_api()
    {
        $this->Api_model->get_all_party_history_api();
    }
    public function get_all_article_group_api()
    {
        $this->Api_model->get_all_article_group_api();
    }
    public function get_article_according_group_api()
    {
        $this->Api_model->get_article_according_group_api();
    }
    public function get_brands_according_party_api()
    {
        $this->Api_model->get_brands_according_party_api();
    }
    public function set_create_order_data_api()
    {
        $this->Api_model->set_create_order_data_api();
    }
    public function get_other_material_ink_names_api()
    {
        $this->Api_model->get_other_material_ink_names_api();
    }
    public function set_printing_report_api()
    {
        $this->Api_model->set_printing_report_api();
    }
    public function get_all_machine_api()
    {
        $this->Api_model->get_all_machine_api();
    }
    public function get_all_raw_material_api()
    {
        $this->Api_model->get_all_raw_material_api();
    }
    public function get_all_master_batch_api()
    {
        $this->Api_model->get_all_master_batch_api();
    }
    public function get_all_reject_material_api()
    {
        $this->Api_model->get_all_reject_material_api();
    }
    public function set_production_details_api()
    {
        $this->Api_model->set_production_details_api();
    }
    public function get_all_production_details_list_api()
    {
        $this->Api_model->get_all_production_details_list_api();
    }
    public function set_production_remark_api()
    {
        $this->Api_model->set_production_remark_api();
    }
    public function set_article_production_details_api()
    {
        $this->Api_model->set_article_production_details_api();
    }
    public function set_article_remark_api()
    {
        $this->Api_model->set_article_remark_api();
    }
    public function set_article_slot_remark_api()
    {
        $this->Api_model->set_article_slot_remark_api();
    }
    public function get_article_according_multiple_group_api()
    {
        $this->Api_model->get_article_according_multiple_group_api();
    }
    public function get_article_production_details_api()
    {
        $this->Api_model->get_article_production_details_api();
    }
    public function get_all_production_details_log_api()
    {
        $this->Api_model->get_all_production_details_log_api();
    }
    public function get_machine_according_plant_api()
    {
        $this->Api_model->get_machine_according_plant_api();
    }
    public function get_printing_order_list_api()
    {
        $this->Api_model->get_printing_order_list_api();
    }
    public function get_all_scheduled_according_date_api()
    {
        $this->Api_model->get_all_scheduled_according_date_api();
    }
    public function set_production_scheduled_api()
    {
        $this->Api_model->set_production_scheduled_api();
    }
    public function get_single_production_schedule_api()
    {
        $this->Api_model->get_single_production_schedule_api();
    }
    public function set_side_visit_data_api()
    {
        $this->Api_model->set_side_visit_data_api();
    }
    public function get_all_side_visit_list_api()
    {
        $this->Api_model->get_all_side_visit_list_api();
    }
    public function set_start_and_end_site_visit_api()
    {
        $this->Api_model->set_start_and_end_site_visit_api();
    }
    public function get_previlege_api()
    {
        $this->Api_model->get_previlege_api();
    }
    public function get_all_sales_person_api()
    {
        $this->Api_model->get_all_sales_person_api();
    }
    public function check_existing_party_name_api()
    {
        $this->Api_model->check_existing_party_name_api();
    }
    public function check_existing_mobile_number_api()
    {
        $this->Api_model->check_existing_mobile_number_api();
    }
    public function get_all_nature_of_business_api()
    {
        $this->Api_model->get_all_nature_of_business_api();
    }
    public function get_all_citys_api()
    {
        $this->Api_model->get_all_citys_api();
    }
    public function get_site_visit_party_api()
    {
        $this->Api_model->get_site_visit_party_api();
    }
    public function get_printing_material_report_list_api()
    {
        $this->Api_model->get_printing_material_report_list_api();
    }
    public function get_all_outward_order_list_api()
    {
        $this->Api_model->get_all_outward_order_list_api();
    }
    public function get_all_transport_api()
    {
        $this->Api_model->get_all_transport_api();
    }
    public function check_existing_dc_no_api()
    {
        $this->Api_model->check_existing_dc_no_api();
    }
    public function check_existing_invoice_no_api()
    {
        $this->Api_model->check_existing_invoice_no_api();
    }
    public function get_outward_order_log_details_api()
    {
        $this->Api_model->get_outward_order_log_details_api();
    }
    public function set_outward_transport_api()
    {
        $this->Api_model->set_outward_transport_api();
    }
    public function get_production_dashboard_api()
    {
        $this->Api_model->get_production_dashboard_api();
    }

    // Alias — app calls this URL via the catch-all route
    public function get_single_day_production_schedule_api()
    {
        $this->Api_model->get_production_dashboard_api();
    }
    public function get_maintenance_dashboard_api()
    {
        $this->Api_model->get_maintenance_dashboard_api();
    }
    public function get_all_article()
    {
        $this->Api_model->get_all_article();
    }
    public function get_logistics_dashboard_api()
    {
        $this->Api_model->get_logistics_dashboard_api();
    }
    public function get_printing_dashboard_api()
    {
        $this->Api_model->get_printing_dashboard_api();
    }
    public function get_owner_dashboard_api()
    {
        $this->Api_model->get_owner_dashboard_api();
    }
    public function get_all_mwo_code()
    {
        $this->Api_model->get_all_mwo_code();
    }
    public function get_all_partys_order_api()
    {
        $this->Api_model->get_all_partys_order_api();
    }
    public function get_party_order_details_api()
    {
        $this->Api_model->get_party_order_details_api();
    }
    public function get_order_list_page_api()
    {
        $this->Api_model->get_order_list_page_api();
    }
    public function get_sub_order_details_api()
    {
        $this->Api_model->get_sub_order_details_api();
    }
    public function get_party_order_log_api()
    {
        $this->Api_model->get_party_order_log_api();
    }
    public function get_all_partys_order_count_api()
    {
        $this->Api_model->get_all_partys_order_count_api();
    }
    public function get_sales_person_dashboard_api()
    {
        $this->Api_model->get_sales_person_dashboard_api();
    }
    public function get_raw_material_according_type()
    {
        $this->Api_model->get_raw_material_according_type();
    }
    public function get_raw_material_according_article_bom_api()
    {
        $this->Api_model->get_raw_material_according_article_bom_api();
    }
    public function get_all_machine_according_plant_api()
    {
        $this->Api_model->get_all_machine_according_plant_api();
    }
    public function set_requested_raw_material_api()
    {
        $this->Api_model->set_requested_raw_material_api();
    }
    public function get_all_material_qty_request_list_api()
    {
        $this->Api_model->get_all_material_qty_request_list_api();
    }
    public function get_machine_data_for_production()
    {
        $this->Api_model->get_machine_data_for_production();
    }
    public function get_all_printing_brand_api()
    {
        $this->Api_model->get_all_printing_brand_api();
    }
    public function get_all_brands_api()
    {
        $this->Api_model->get_all_brands_api();
    }
    public function get_all_target_reached_visit_list_api()
    {
        $this->Api_model->get_all_target_reached_visit_list_api();
    }
    public function get_all_notification_list_api()
    {
        $this->Api_model->get_all_notification_list_api();
    }
    public function get_last_out_km_vehicle_details()
    {
        $this->Api_model->get_last_out_km_vehicle_details();
    }
    public function check_unique_invoice_no_api()
    {
        $this->Api_model->check_unique_invoice_no_api();
    }
    public function check_unique_dc_no_api()
    {
        $this->Api_model->check_unique_dc_no_api();
    }
    public function set_device_details()
    {
        $this->Api_model->set_device_details();
    }
    public function logout()
    {
        $this->Api_model->logout();
    }

    // =============================================
    // P1 - New Android API Endpoints (v2)
    // =============================================

    /**
     * GET: Returns all employees with their multi-plant and multi-department assignments.
     * Endpoint: /api/Api_controller/get_all_employees_v2_api
     */
    public function get_all_employees_v2_api()
    {
        $this->Api_model->get_all_employees_v2_api();
    }

    /**
     * GET: Returns all party records including 4-layer sales hierarchy (dg, asm, state_head, telecaller).
     * Endpoint: /api/Api_controller/get_all_party_v2_api
     */
    public function get_all_party_v2_api()
    {
        $this->Api_model->get_all_party_v2_api();
    }

    /**
     * GET: Returns Production BOM records including std_cycle_time and std_weight for a given article_id.
     * Endpoint: /api/Api_controller/get_bom_specs_api?article_id={id}
     */
    public function get_bom_specs_api()
    {
        $this->Api_model->get_bom_specs_api();
    }

    /**
     * GET: Returns all plants and departments master for mobile dropdowns.
     * Endpoint: /api/Api_controller/get_masters_for_employee_api
     */
    public function get_masters_for_employee_api()
    {
        $this->Api_model->get_masters_for_employee_api();
    }

    public function set_spc_part_weight_api()
    {
        $this->Api_model->set_spc_part_weight_api();
    }

    /**
     * PUT: Add a new SPC Part Weight record.
     * Endpoint: /add_spc_part_weight_api
     */
    public function add_spc_part_weight_api()
    {
        $this->Api_model->add_spc_part_weight_api();
    }

    /**
     * PUT: Update an existing SPC Part Weight record by id.
     * Endpoint: /update_spc_part_weight_api
     */
    public function update_spc_part_weight_api()
    {
        $this->Api_model->update_spc_part_weight_api();
    }

    /**
     * GET: Retrieve SPC Part Weight list with filters and pagination.
     * Endpoint: /get_spc_part_weight_list_api
     */
    public function get_spc_part_weight_list_api()
    {
        $this->Api_model->get_spc_part_weight_list_api();
    }

    // ─── Process Parameter Sheet APIs ────────────────────────────────────────

    /**
     * PUT: Add a new Process Parameter Sheet record.
     * Endpoint: /add_process_parameter_api
     */
    public function add_process_parameter_api()
    {
        $this->Api_model->add_process_parameter_api();
    }

    /**
     * PUT: Update an existing Process Parameter Sheet record.
     * Endpoint: /update_process_parameter_api
     */
    public function update_process_parameter_api()
    {
        $this->Api_model->update_process_parameter_api();
    }

    /**
     * POST: Get Process Parameter Sheet list / single record.
     * Endpoint: /get_process_parameter_api
     */
    public function get_process_parameter_api()
    {
        $this->Api_model->get_process_parameter_api();
    }

    /**
     * GET/POST/PUT: Operator management API
     * GET    /operators_api              → fetch all operators (filter: plant_id, search)
     * POST   /operators_api              → add new operator
     * PUT    /operators_api              → update existing operator
     */
    public function operators_api()
    {
        $this->Api_model->operators_api();
    }

    /**
     * Alias for operators_api to bypass server WAF/ModSecurity blocks on the word "operators".
     */
    public function shift_staff_api()
    {
        $this->Api_model->operators_api();
    }

    /**
     * POST: Get batch info for one or multiple articles for bundle calculation.
     * Endpoint: /get_article_batch_for_bundle_api
     * Body (JSON): { "article_ids": [1, 2, 3] }   — array of article IDs
     *           OR { "article_id": 5 }              — single article ID
     * Response per article:
     *   article_id, article_name, batch (pcs per bag/bundle),
     *   weight, std_cycle_time, std_weight,
     *   raw_material_one, raw_material_two, other_rm, master_batch
     */
    public function get_article_batch_for_bundle_api()
    {
        $this->Api_model->get_article_batch_for_bundle_api();
    }

    // ─── Production Image Upload APIs ────────────────────────────────────────

    /**
     * POST: Upload one or more images for a production report entry.
     * Endpoint: /api/Api_controller/upload_production_images_api
     * Body (JSON):
     *   {
     *     "production_id": 123,
     *     "images": ["<base64_string>", "<base64_string>"]   // one or more base64-encoded images
     *   }
     * Response:
     *   { "status": "true", "message": "...", "data": { "image_urls": [...] } }
     */
    public function upload_production_images_api()
    {
        $this->Api_model->upload_production_images_api();
    }

    /**
     * POST: Retrieve all uploaded images for a production report entry.
     * Endpoint: /api/Api_controller/get_production_images_api
     * Body (JSON): { "production_id": 123 }
     * Response:
     *   { "status": "true", "message": "...", "data": { "image_urls": [...] } }
     */
    public function get_production_images_api()
    {
        $this->Api_model->get_production_images_api();
    }

    /**
     * GET: One-time fix — adds production_images column and cleans bad image records.
     * Endpoint: /fix_prod_images_setup?pid=1252
     * DELETE this method after use.
     */
    /**
     * GET: Returns all active Production Idle State Reasons (remark master).
     * Endpoint: /api/Api_controller/get_all_remark_master_api
     */
    public function get_all_remark_master_api()
    {
        $this->Api_model->get_all_remark_master_api();
    }

    public function fix_prod_images_setup()
    {
        // Safety token — change or remove after use
        $token = $this->input->get('token');
        if ($token !== 'krivisha_fix_2026') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $pid = (int)($this->input->get('pid') ?? 0);
        $output = [];

        // 1. Add production_images column if missing
        $col = $this->db->query("SHOW COLUMNS FROM tbl_production_report LIKE 'production_images'");
        if ($col->num_rows() === 0) {
            $this->db->query("ALTER TABLE tbl_production_report ADD COLUMN production_images TEXT NULL DEFAULT NULL");
            $output[] = '✓ production_images column ADDED';
        } else {
            $output[] = '✓ production_images column already EXISTS';
        }

        // 2. Show tbl_production_form_image records for given pid
        if ($pid > 0) {
            $rows = $this->db->where('production_id', $pid)->get('tbl_production_form_image')->result();
            $output[] = "tbl_production_form_image rows for pid=$pid:";
            foreach ($rows as $r) {
                $output[] = "  id={$r->id}  image_names={$r->image_names}";
            }

            // 3. Show production_images column value
            $prod = $this->db->select('production_images')->where('id', $pid)->get('tbl_production_report')->row();
            $output[] = "tbl_production_report.production_images for pid=$pid: " . ($prod ? ($prod->production_images ?? 'NULL') : 'RECORD NOT FOUND');

            // 4. Check if Android image files actually exist on disk
            if ($prod && !empty($prod->production_images)) {
                $paths = array_filter(explode(',', $prod->production_images));
                $output[] = "Android image file check:";
                foreach ($paths as $path) {
                    $path = trim($path);
                    $full = FCPATH . $path;
                    $exists = file_exists($full) ? 'EXISTS ✓' : 'MISSING ✗ (file not on disk)';
                    $output[] = "  $path → $exists";
                }
            }

            // 5. Check web image files on disk
            $output[] = "Web image file check:";
            $rows2 = $this->db->where('production_id', $pid)->get('tbl_production_form_image')->result();
            foreach ($rows2 as $r) {
                $full = FCPATH . 'assets/images/production/' . $r->image_names;
                $exists = file_exists($full) ? 'EXISTS ✓' : 'MISSING ✗';
                $output[] = "  {$r->image_names} → $exists";
            }

            // 6. Delete bad record if requested
            $del_id = (int)($this->input->get('del_id') ?? 0);
            if ($del_id > 0) {
                $this->db->where('id', $del_id)->where('production_id', $pid)->delete('tbl_production_form_image');
                $output[] = "✓ Deleted tbl_production_form_image id=$del_id";
            }

            // 7. Clear android images if requested
            if ($this->input->get('clear_android') === '1') {
                $this->db->where('id', $pid)->update('tbl_production_report', ['production_images' => null]);
                $output[] = "✓ Cleared production_images for pid=$pid";
            }
        }

        header('Content-Type: text/plain');
        echo implode("\n", $output);
    }
    public function get_article_stock_api()
    {
        $this->Api_model->get_article_stock_api();
    }
    public function get_all_raw_material_ddl_api()
    {
        $this->Api_model->get_all_raw_material_ddl_api();
    }
}



