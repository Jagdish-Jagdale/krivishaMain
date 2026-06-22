<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Api_controller extends CI_Controller
{
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
}

