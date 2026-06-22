<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['default_controller'] = 'Welcome/index';
$route['default_controller'] = 'Welcome/login';
$route['forgot_password'] = "Welcome/forgot_password";
$route['privacy_policy'] = "Welcome/privacy_policy";




$route['dashboard'] = "admin/Admin_controller/index";
$route['dashboard-2'] = "admin/Admin_controller/dashboard_account";
$route['dashboard-3'] = "admin/Admin_controller/dashboard_owner";
$route['add_production'] = "admin/Admin_controller/add_production";
$route['add_production/(:any)'] = "admin/Admin_controller/add_production/$1";
$route['production_form_list/(:any)'] = "admin/Admin_controller/production_form_list/$1";
$route['production_report_list'] = "admin/Admin_controller/production_report_list";
$route['production_report'] = "admin/Admin_controller/production_report";
$route['production_form_list'] = "admin/Admin_controller/summary";
$route['production_form_list/(:any)/(:any)'] = "admin/Admin_controller/summary/$1";


// $route['delete/']='admin/Admin_controller/delete';
$route['delete/(:any)/(:any)'] = 'admin/Admin_controller/delete/$1';
$route['admin_logout'] = "Welcome/logout";
$route['change-current-password'] = "admin/Admin_controller/change_current_password";
$route['update-profile-image'] = "admin/Admin_controller/update_profile_image";
// Master

$route['add_brand'] = "admin/Admin_controller/add_brand";
$route['add_brand/(:any)'] = "admin/Admin_controller/add_brand/$1";
$route['add_transport'] = "admin/Admin_controller/add_transport";
$route['add_transport/(:any)'] = "admin/Admin_controller/add_transport/$1";
$route['add_location'] = "admin/Admin_controller/add_location";
$route['add_location/(:any)'] = "admin/Admin_controller/add_location/$1";
$route['add_customer'] = "admin/Admin_controller/add_customer";
$route['add_customer/(:any)'] = "admin/Admin_controller/add_customer/$1";
$route['add_article'] = "admin/Admin_controller/add_article";
$route['add_article/(:any)'] = "admin/Admin_controller/add_article/$1";
$route['add_rm'] = "admin/Admin_controller/add_rm";
$route['add_rm/(:any)/(:any)'] = "admin/Admin_controller/add_rm/$1";
$route['add_mb'] = "admin/Admin_controller/add_mb";
$route['add_mb/(:any)'] = "admin/Admin_controller/add_mb/$1";
$route['extra_payment_master'] = "admin/Admin_controller/extra_payment_master";
$route['extra_payment_master/(:any)'] = "admin/Admin_controller/extra_payment_master/$1";
$route['remark_master'] = "admin/Admin_controller/remark_master";
$route['remark_master/(:any)'] = "admin/Admin_controller/remark_master/$1";
$route['view_inward_form'] = "admin/Admin_controller/view_inward_form";
$route['view_inward_form/(:any)/(:any)'] = "admin/Admin_controller/view_inward_form/$1";


$route['brand_list'] = "admin/Admin_controller/brand_list";
$route['brand_list/(:any)'] = "admin/Admin_controller/brand_list/$1";
$route['transport_list'] = "admin/Admin_controller/transport_list";
$route['location_list'] = "admin/Admin_controller/location_list";
$route['customer_list'] = "admin/Admin_controller/customer_list";
$route['customer_list/(:any)'] = "admin/Admin_controller/customer_list/$1";
$route['article_list'] = "admin/Admin_controller/article_list";

$route['rm_list'] = "admin/Admin_controller/rm_list";
$route['rm_rejection_list'] = "admin/Admin_controller/rm_rejection_list";
$route['mb_list'] = "admin/Admin_controller/mb_list";

$route['machine_list'] = "admin/Admin_controller/machine_list";
$route['machine_list/(:any)'] = "admin/Admin_controller/machine_list/$1";

$route['impression_rate'] = "admin/Admin_controller/impression_rate";
$route['impression_rate/(:any)'] = "admin/Admin_controller/impression_rate/$1";

$route['plant_list'] = "admin/Admin_controller/plant_list";
$route['plant_list/(:any)'] = "admin/Admin_controller/plant_list/$1";

$route['printing_unit_list'] = "admin/Admin_controller/add_printing_unit_list";
$route['printing_unit_list/(:any)'] = "admin/Admin_controller/add_printing_unit_list/$1";

$route['uom_list'] = "admin/Admin_controller/uom_list";
$route['uom_list/(:any)'] = "admin/Admin_controller/uom_list/$1";

$route['krivisha_department'] = "admin/Admin_controller/krivisha_department";
$route['krivisha_department/(:any)'] = "admin/Admin_controller/krivisha_department/$1";

$route['krivisha_employee'] = "admin/Admin_controller/krivisha_employee";
$route['krivisha_employee/(:any)'] = "admin/Admin_controller/krivisha_employee/$1";
$route['krivisha_employee_list'] = "admin/Admin_controller/krivisha_employee_list";


$route['printing_list'] = "admin/Admin_controller/printing_list";
$route['problems_list'] = "admin/Admin_controller/problems_list";
$route['problems_list/(:any)'] = "admin/Admin_controller/problems_list/$1";

$route['add_maintenance'] = "admin/Admin_controller/add_maintenance";
$route['add_maintenance/(:any)'] = "admin/Admin_controller/add_maintenance/$1";
$route['maintenance_list'] = "admin/Admin_controller/maintenance_list";
$route['maintenance_list/(:any)/(:any)'] = "admin/Admin_controller/maintenance_list/$1";

$route['maintaince_bom'] = "admin/Admin_controller/maintaince_bom";
$route['maintaince_bom/(:any)'] = "admin/Admin_controller/maintaince_bom/$1";

$route['production_schedule'] = "admin/Admin_controller/production_schedule";
$route['production_schedule_form'] = "admin/Admin_controller/production_schedule_form";
$route['store_rm'] = "admin/Admin_controller/store_rm";
$route['store_rm_list'] = "admin/Admin_controller/store_rm_list";
$route['production_maintenance_list'] = "admin/Admin_controller/production_maintenance_list";

// --------------------------------------------Date: 28/02/2025--------------------------------------------------------------

$route['add_task'] = "admin/Admin_controller/add_task";
$route['add_task/(:any)'] = "admin/Admin_controller/add_task/$1";
$route['update_auto_manual_task'] = "admin/Admin_controller/update_auto_manual_task";
$route['update_auto_manual_task/(:any)'] = "admin/Admin_controller/update_auto_manual_task/$1";
$route['update_auto_manual_task/(:any)/(:any)'] = "admin/Admin_controller/update_auto_manual_task/$1";
$route['update_auto_manual_task/(:any)/(:any)/(:any)'] = "admin/Admin_controller/update_auto_manual_task/$1";
$route['update_task'] = "admin/Admin_controller/update_task";
$route['task_list'] = "admin/Admin_controller/task_list";
$route['auto_task_list'] = "admin/Admin_controller/auto_task_list";

// Stock Report

$route['stock_report_inward'] = "admin/Admin_controller/stock_report_inward";
$route['stock_report_raw_material'] = "admin/Admin_controller/stock_report_raw_material";
$route['stock_ledger_report'] = "admin/Admin_controller/stock_ledger_report";
$route['migrate_stock_transactions'] = "admin/Admin_controller/migrate_stock_transactions";


$route['task_update_list'] = "admin/Admin_controller/task_update_list";
$route['add_production_bom'] = "admin/Admin_controller/add_production_bom";
$route['add_production_bom/(:any)/(:any)'] = "admin/Admin_controller/add_production_bom/$1";
$route['production_bom_list'] = "admin/Admin_controller/production_bom_list";
$route['production_bom_list/(:any)'] = "admin/Admin_controller/production_bom_list/$1";
$route['production_bom_details'] = "admin/Admin_controller/production_bom_details";

$route['order_list'] = "admin/Admin_controller/order_list";
$route['add_order'] = "admin/Admin_controller/add_order";
$route['add_order/(:any)/(:any)/(:any)'] = "admin/Admin_controller/add_order/$1";
$route['cancel_order/(:any)'] = "admin/Admin_controller/cancel_order/$1";

$route['maintenance_list_details'] = "admin/Admin_controller/maintenance_list_details";
$route['maintenance_list_details/(:any)'] = "admin/Admin_controller/maintenance_list_details/$1";
$route['maintenance_report'] = "admin/Admin_controller/maintenance_report";
$route['printing_report'] = "admin/Admin_controller/printing_report";

$route['outward_order_list'] = "admin/Admin_controller/outward_order_list";
$route['dispach_order_list'] = "admin/Admin_controller/dispach_order_list";
$route['salesman_on_fields_details'] = 'admin/Admin_controller/salesman_on_fields_details';
$route['outward_transport/(:any)/(:any)/(:any)'] = "admin/Admin_controller/add_outward_transport/$1";
$route['outward_transport_history'] = "admin/Admin_controller/outward_transport_history";
$route['outward_transport_history/(:any)/(:any)'] = "admin/Admin_controller/outward_transport_history/$1";
$route['transport_report'] = "admin/Admin_controller/transport_report";
$route['transport_report_view/(:any)'] = "admin/Admin_controller/transport_report_view/$1";

$route['own_vehicle'] = "admin/Admin_controller/add_own_vehicle";
// $route['own_vehicle/(:any)'] = "admin/Admin_controller/add_own_vehicle/$1";
// $route['own_vehicle_list'] = "admin/Admin_controller/own_vehicle_list";
$route['own_vehicle/(:any)'] = "admin/Admin_controller/add_own_vehicle/$1";
$route['own_vehicle_list'] = "admin/Admin_controller/own_vehicle_list";

// printing unit report
$route['printing_unit_report/(:any)'] = "admin/Admin_controller/printing_unit_report/$1";

$route['printing_unit_report/(:any)/(:any)/(:any)/(:any)'] = "admin/Admin_controller/printing_unit_report/$1";
$route['job_work_printing_unit_report_list'] = "admin/Admin_controller/job_work_printing_unit_report_list";
$route['job_work_printing_unit_report_list/(:any)/(:any)/(:any)/(:any)'] = "admin/Admin_controller/job_work_printing_unit_report_list/$1";

$route['material_report_printing_unit_report_list'] = "admin/Admin_controller/material_report_printing_unit_report_list";
$route['material_report_printing_unit_report_list/(:any)/(:any)/(:any)/(:any)'] = "admin/Admin_controller/material_report_printing_unit_report_list/$1";

$route['printing_order_list'] = "admin/Admin_controller/printing_order_list";





$route['purchase_sales_report'] = "admin/Admin_controller/purchase_sales_report";
$route['sales_report_list'] = "admin/Admin_controller/sales_report_list";



// --------------------------------------------Hidden Route--------------------------------------------------------------

$route['brand_type_list'] = "admin/Admin_controller/brand_type_list";
$route['brand_type_list/(:any)'] = "admin/Admin_controller/brand_type_list/$1";

$route['brand_department_list'] = "admin/Admin_controller/brand_department_list";
$route['brand_department_list/(:any)'] = "admin/Admin_controller/brand_department_list/$1";

$route['party_nature_list'] = "admin/Admin_controller/party_nature_list";
$route['party_nature_list/(:any)'] = "admin/Admin_controller/party_nature_list/$1";

$route['party_designation_list'] = "admin/Admin_controller/party_designation_list";
$route['party_designation_list/(:any)'] = "admin/Admin_controller/party_designation_list/$1";

$route['party_type_businesss_list'] = "admin/Admin_controller/party_type_businesss_list";
$route['party_type_businesss_list/(:any)'] = "admin/Admin_controller/party_type_businesss_list/$1";

$route['group_of_list'] = "admin/Admin_controller/group_of_list";
$route['group_of_list/(:any)'] = "admin/Admin_controller/group_of_list/$1";

$route['type_of_mould_list'] = "admin/Admin_controller/type_of_mould_list";
$route['type_of_mould_list/(:any)'] = "admin/Admin_controller/type_of_mould_list/$1";

$route['alanky_bolt_list'] = "admin/Admin_controller/alanky_bolt_list";
$route['alanky_bolt_list/(:any)'] = "admin/Admin_controller/alanky_bolt_list/$1";

$route['air_pin_list'] = "admin/Admin_controller/air_pin_list";
$route['air_pin_list/(:any)'] = "admin/Admin_controller/air_pin_list/$1";

$route['spring_list'] = "admin/Admin_controller/spring_list";
$route['spring_list/(:any)'] = "admin/Admin_controller/spring_list/$1";

$route['pu_nipple_list'] = "admin/Admin_controller/pu_nipple_list";
$route['pu_nipple_list'] = "admin/Admin_controller/pu_nipple_list";
$route['pu_nipple_list/(:any)'] = "admin/Admin_controller/pu_nipple_list/$1";

$route['ejector_pin_list'] = "admin/Admin_controller/ejector_pin_list";
$route['ejector_pin_list/(:any)'] = "admin/Admin_controller/ejector_pin_list/$1";

$route['i_bolt_list'] = "admin/Admin_controller/i_bolt_list";
$route['i_bolt_list/(:any)'] = "admin/Admin_controller/i_bolt_list/$1";

$route['cord_list'] = "admin/Admin_controller/cord_list";
$route['cord_list/(:any)'] = "admin/Admin_controller/cord_list/$1";

$route['o_ring_list'] = "admin/Admin_controller/o_ring_list";
$route['o_ring_list/(:any)'] = "admin/Admin_controller/o_ring_list/$1";

$route['insert_slot_list'] = "admin/Admin_controller/insert_slot_list";
$route['insert_slot_list/(:any)'] = "admin/Admin_controller/insert_slot_list/$1";

$route['core_cylender_list'] = "admin/Admin_controller/core_cylender_list";
$route['core_cylender_list/(:any)'] = "admin/Admin_controller/core_cylender_list/$1";

$route['seal_list'] = "admin/Admin_controller/seal_list";
$route['seal_list/(:any)'] = "admin/Admin_controller/seal_list/$1";

$route['hope_pipe_list'] = "admin/Admin_controller/hope_pipe_list";
$route['hope_pipe_list/(:any)'] = "admin/Admin_controller/hope_pipe_list/$1";

$route['rm_make_list'] = "admin/Admin_controller/rm_make_list";
$route['rm_make_list/(:any)'] = "admin/Admin_controller/rm_make_list/$1";

$route['rm_type_list'] = "admin/Admin_controller/rm_type_list";
$route['rm_type_list/(:any)'] = "admin/Admin_controller/rm_type_list/$1";

$route['mb_make_list'] = "admin/Admin_controller/mb_make_list";
$route['mb_make_list/(:any)'] = "admin/Admin_controller/mb_make_list/$1";

$route['machine_department_list'] = "admin/Admin_controller/machine_department_list";
$route['machine_department_list/(:any)'] = "admin/Admin_controller/machine_department_list/$1";


$route['rm_inward_form'] = "admin/Admin_controller/rm_inward_form";
$route['rm_inward_list'] = "admin/Admin_controller/rm_inward_list";

$route['mb_inward_form'] = "admin/Admin_controller/mb_inward_form";
$route['mb_inward_list'] = "admin/Admin_controller/mb_inward_list";

$route['master_batch_request_from'] = "admin/Admin_controller/master_batch_request_from";

$route['material_artical_requistition_from'] = "admin/Admin_controller/material_artical_requistition_from";
$route['view_material_artical_requistition_from/(:any)/(:any)'] = "admin/Admin_controller/view_material_artical_requistition_from/$1";
$route['material_artical_requistition_from_list'] = "admin/Admin_controller/material_artical_requistition_from_list";

$route['view_material_artical_requistition_to_list/(:any)'] = "admin/Admin_controller/view_material_artical_requistition_to_list/$1";
$route['view__artical_requistition_to_list/(:any)'] = "admin/Admin_controller/view__artical_requistition_to_list/$1";
$route['view_mb_requestion_one_plant_to_other/(:any)'] = "admin/Admin_controller/view_mb_requestion_one_plant_to_other/$1";
$route['material_artical_requistition_to_list'] = "admin/Admin_controller/material_artical_requistition_to_list";
$route['article_production_stock_report'] = "admin/Admin_controller/article_production_stock_report";

$route['rm_stock_adjustment'] = "admin/Admin_controller/rm_stock_adjustment";
$route['mb_stock_adjustment'] = "admin/Admin_controller/mb_stock_adjustment";
$route['article_stock_adjustment'] = "admin/Admin_controller/article_stock_adjustment";
$route['stock_adjustment_list'] = "admin/Admin_controller/stock_adjustment_list";
$route['total_stock_list'] = "admin/Admin_controller/total_stock_list";

$route['return_stock'] = "admin/Admin_controller/return_stock";
$route['return_stock_list'] = "admin/Admin_controller/return_stock_list";

$route['raw_material_reorder_level'] = "admin/Admin_controller/raw_material_reorder_level";
$route['article_reorder_level'] = "admin/Admin_controller/article_reorder_level";
$route['material_reorder_report'] = "admin/Admin_controller/material_reorder_report";
$route['required_material_report'] = "admin/Admin_controller/required_material_report";
$route['stock_return_history_report'] = "admin/Admin_controller/stock_return_history_report";
$route['article_request_from'] = "admin/Admin_controller/article_request_from";

$route['apk-upload'] = 'admin/Admin_controller/apk_upload';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;




$route['admin/Ajax_controller/get_all_article_names'] = 'admin/Ajax_controller/get_all_article_names';
$route['admin/Ajax_controller/get_all_production_report_list'] = 'admin/Ajax_controller/get_all_production_report_list';
$route['admin/Ajax_controller/set_production_operators'] = 'admin/Ajax_controller/set_production_operators';
$route['admin/Ajax_controller/get_production_images'] = 'admin/Ajax_controller/get_production_images';
$route['admin/Ajax_controller/set_remark'] = 'admin/Ajax_controller/set_remark';
$route['admin/Ajax_controller/get_transport_report_data'] = 'admin/Ajax_controller/get_transport_report_data';
$route['admin/Ajax_controller/get_transport_report_summary'] = 'admin/Ajax_controller/get_transport_report_summary';



//Privileges work on 09-05-2025
$route['add-previleges'] = "admin/Admin_controller/add_previleges";
$route['add-previleges/(:any)'] = "admin/Admin_controller/add_previleges/$1";
$route['add-submenu'] = "admin/Admin_controller/add_submenu";
$route['add-submenu/(:any)'] = "admin/Admin_controller/add_submenu/$1";
$route['manage-privilege'] = "admin/Admin_controller/manage_priviledge";
$route['manage-privilege/(:any)'] = "admin/Admin_controller/manage_priviledge/$1";


////////////////////////////  Api Routs /////////////////////////////////////////////

$route['get_app_login'] = "api/Api_controller/get_app_login";
$route['get_user_profile_api'] = "api/Api_controller/get_user_profile_api";
$route['update_user_profile_api'] = "api/Api_controller/update_user_profile_api";
$route['api/Api/(:any)'] = "api/Api_controller/$1";
$route['Api_controller/(:any)'] = "api/Api_controller/$1";
$route['get_all_department_api'] = "api/Api_controller/get_all_department_api";
$route['change_current_password_api'] = "api/Api_controller/change_current_password_api";
$route['get_all_party_details_api'] = "api/Api_controller/get_all_party_details_api";
$route['get_employee_according_department_api'] = "api/Api_controller/get_employee_according_department_api";

$route['set_manual_task_api'] = "api/Api_controller/set_manual_task_api";
$route['get_all_manual_task_list_api'] = "api/Api_controller/get_all_manual_task_list_api";
$route['get_all_auto_task_list_api'] = "api/Api_controller/get_all_auto_task_list_api";
$route['set_update_manual_task_api'] = "api/Api_controller/set_update_manual_task_api";
$route['get_all_task_history_api'] = "api/Api_controller/get_all_task_history_api";

$route['get_all_plant_api'] = "api/Api_controller/get_all_plant_api";
$route['get_subcategory_according_maintenance_api'] = "api/Api_controller/get_subcategory_according_maintenance_api";
$route['get_all_sub_types_problems_api'] = "api/Api_controller/get_all_sub_types_problems_api";
$route['set_maintenance_data_api'] = "api/Api_controller/set_maintenance_data_api";
$route['get_all_maintenance_list_api'] = "api/Api_controller/get_all_maintenance_list_api";

$route['get_all_own_vehicle_api'] = "api/Api_controller/get_all_own_vehicle_api";
$route['get_all_location_api'] = "api/Api_controller/get_all_location_api";
$route['set_own_vehicle_data_api'] = "api/Api_controller/set_own_vehicle_data_api";
$route['get_all_own_vehicle_list_api'] = "api/Api_controller/get_all_own_vehicle_list_api";

$route['get_all_order_list_api'] = "api/Api_controller/get_all_order_list_api";
$route['set_create_order_data_api'] = "api/Api_controller/set_create_order_data_api";
$route['get_all_party_history_api'] = "api/Api_controller/get_all_party_history_api";
$route['get_all_article_group_api'] = "api/Api_controller/get_all_article_group_api";
$route['get_article_according_group_api'] = "api/Api_controller/get_article_according_group_api";

// Dashboad apis 
$route['get_single_day_production_schedule_api'] = "api/Api_controller/get_production_dashboard_api";
$route['get_maintenance_dashboard_api'] = "api/Api_controller/get_maintenance_dashboard_api";
$route['get_logistics_dashboard_api'] = "api/Api_controller/get_logistics_dashboard_api";
$route['get_printing_dashboard_api'] = "api/Api_controller/get_printing_dashboard_api";
$route['get_owner_dashboard_api'] = "api/Api_controller/get_owner_dashboard_api";
$route['get_all_article'] = "api/Api_controller/get_all_article";
$route['get_all_mwo_code'] = "api/Api_controller/get_all_mwo_code";
$route['get_all_partys_order_api'] = "api/Api_controller/get_all_partys_order_api";
$route['get_party_order_log_api'] = "api/Api_controller/get_party_order_log_api";
$route['get_all_partys_order_count_api'] = "api/Api_controller/get_all_partys_order_count_api";
$route['get_party_order_details_api'] = "api/Api_controller/get_party_order_details_api";
$route['get_order_list_page_api'] = "api/Api_controller/get_order_list_page_api";
$route['get_sub_order_details_api'] = "api/Api_controller/get_sub_order_details_api";
$route['get_sales_person_dashboard_api'] = "api/Api_controller/get_sales_person_dashboard_api";

//Printing Unit Apis
$route['get_printing_order_list_api'] = "api/Api_controller/get_printing_order_list_api"; 
$route['get_brands_according_party_api'] = "api/Api_controller/get_brands_according_party_api";
$route['get_other_material_ink_names_api'] = "api/Api_controller/get_other_material_ink_names_api";
$route['set_printing_report_api'] = "api/Api_controller/set_printing_report_api"; 
$route['get_printing_material_report_list_api'] = "api/Api_controller/get_printing_material_report_list_api";
 
// Logistics apis
$route['get_all_outward_order_list_api'] = "api/Api_controller/get_all_outward_order_list_api";
$route['get_all_transport_api'] = "api/Api_controller/get_all_transport_api";
$route['check_existing_dc_no_api'] = "api/Api_controller/check_existing_dc_no_api";
$route['check_existing_invoice_no_api'] = "api/Api_controller/check_existing_invoice_no_api";
$route['get_outward_order_log_details_api'] = "api/Api_controller/get_outward_order_log_details_api";
$route['set_outward_transport_api'] = "api/Api_controller/set_outward_transport_api";

// Production Apis
$route['get_all_machine_api'] = "api/Api_controller/get_all_machine_api";
$route['get_all_raw_material_api'] = "api/Api_controller/get_all_raw_material_api";
$route['get_all_master_batch_api'] = "api/Api_controller/get_all_master_batch_api";
$route['get_all_reject_material_api'] = "api/Api_controller/get_all_reject_material_api";
$route['set_production_details_api'] = "api/Api_controller/set_production_details_api";
$route['get_all_production_details_list_api'] = "api/Api_controller/get_all_production_details_list_api";
$route['set_production_remark_api'] = "api/Api_controller/set_production_remark_api";
$route['set_article_production_details_api'] = "api/Api_controller/set_article_production_details_api";
$route['set_article_slot_remark_api'] = "api/Api_controller/set_article_slot_remark_api";
$route['set_article_remark_api'] = "api/Api_controller/set_article_remark_api";
$route['get_article_according_multiple_group_api'] = "api/Api_controller/get_article_according_multiple_group_api";
$route['get_article_production_details_api'] = "api/Api_controller/get_article_production_details_api";
$route['get_all_production_details_log_api'] = "api/Api_controller/get_all_production_details_log_api";

$route['check_unique_invoice_no_api'] = "api/Api_controller/check_unique_invoice_no_api";
$route['check_unique_dc_no_api'] = "api/Api_controller/check_unique_dc_no_api";
$route['set_device_details'] = "api/Api_controller/set_device_details";
$route['logout'] = "api/Api_controller/logout";

//Production Scheduled apis
$route['get_machine_according_plant_api'] = "api/Api_controller/get_machine_according_plant_api";
$route['get_all_scheduled_according_date_api'] = "api/Api_controller/get_all_scheduled_according_date_api";
$route['set_production_scheduled_api'] = "api/Api_controller/set_production_scheduled_api";
$route['get_single_production_schedule_api'] = "api/Api_controller/get_single_production_schedule_api";
$route['get_last_out_km_vehicle_details'] = "api/Api_controller/get_last_out_km_vehicle_details";

//site visit apis
$route['set_side_visit_data_api'] = "api/Api_controller/set_side_visit_data_api";
$route['get_all_side_visit_list_api'] = "api/Api_controller/get_all_side_visit_list_api";
$route['set_start_and_end_site_visit_api'] = "api/Api_controller/set_start_and_end_site_visit_api";
$route['get_all_sales_person_api'] = "api/Api_controller/get_all_sales_person_api";
$route['check_existing_party_name_api'] = "api/Api_controller/check_existing_party_name_api";
$route['check_existing_mobile_number_api'] = "api/Api_controller/check_existing_mobile_number_api";
$route['get_all_nature_of_business_api'] = "api/Api_controller/get_all_nature_of_business_api";
$route['get_all_citys_api'] = "api/Api_controller/get_all_citys_api";
$route['get_site_visit_party_api'] = "api/Api_controller/get_site_visit_party_api";
$route['get_all_brands_api'] = "api/Api_controller/get_all_brands_api";
$route['cancel_order_api'] = "api/Api_controller/cancel_order_api";
$route['get_raw_material_according_type'] = "api/Api_controller/get_raw_material_according_type";
$route['set_requested_raw_material_api'] = "api/Api_controller/set_requested_raw_material_api";
$route['get_all_material_qty_request_list_api'] = "api/Api_controller/get_all_material_qty_request_list_api";
$route['get_machine_data_for_production'] = "api/Api_controller/get_machine_data_for_production";
$route['get_all_printing_brand_api'] = "api/Api_controller/get_all_printing_brand_api";
$route['get_raw_material_according_article_bom_api'] = "api/Api_controller/get_raw_material_according_article_bom_api";
$route['get_all_machine_according_plant_api'] = "api/Api_controller/get_all_machine_according_plant_api";
$route['get_all_target_reached_visit_list_api'] = "api/Api_controller/get_all_target_reached_visit_list_api";
$route['get_all_notification_list_api'] = "api/Api_controller/get_all_notification_list_api";

// Privilege Api
$route['get_previlege_api'] = "api/Api_controller/get_previlege_api";
$route['set_spc_part_weight_api']    = "api/Api_controller/set_spc_part_weight_api";
$route['add_spc_part_weight_api']    = "api/Api_controller/add_spc_part_weight_api";
$route['update_spc_part_weight_api'] = "api/Api_controller/update_spc_part_weight_api";
$route['get_spc_part_weight_list_api'] = "api/Api_controller/get_spc_part_weight_list_api";

// Process Parameter Sheet
$route['process_parameter_list']          = "admin/Admin_controller/process_parameter_list";
$route['process_parameter_view/(:any)']   = "admin/Admin_controller/process_parameter_view/$1";

// Process Parameter APIs (Android)
$route['add_process_parameter_api']    = "api/Api_controller/add_process_parameter_api";
$route['update_process_parameter_api'] = "api/Api_controller/update_process_parameter_api";
$route['get_process_parameter_api']    = "api/Api_controller/get_process_parameter_api";

// Notification API
$route['set_notification_api'] = "api/Api_controller/set_notification_api";
$route['get_article_batch_for_bundle_api'] = "api/Api_controller/get_article_batch_for_bundle_api";
$route['operators_api'] = "api/Api_controller/operators_api";
$route['shift_staff_api'] = "api/Api_controller/shift_staff_api";

// Production Image APIs (Android)
$route['upload_production_images_api'] = "api/Api_controller/upload_production_images_api";
$route['get_production_images_api']    = "api/Api_controller/get_production_images_api";
$route['fix_prod_images_setup']        = "api/Api_controller/fix_prod_images_setup";

// Remark Master API (Android)
$route['get_all_remark_master_api'] = "api/Api_controller/get_all_remark_master_api";




