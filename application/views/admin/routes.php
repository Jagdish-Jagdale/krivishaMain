<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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



// Production

$route['dashboard'] = "admin/Admin_controller/index";
$route['add_production'] = "admin/Admin_controller/add_production";
$route['add_production/(:any)'] = "admin/Admin_controller/add_production/$1";
$route['production_form_list/(:any)'] = "admin/Admin_controller/production_form_list/$1";  
$route['production_report_list'] = "admin/Admin_controller/production_report_list";
$route['production_report'] = "admin/Admin_controller/production_report";
$route['production_form_list'] = "admin/Admin_controller/summary";
$route['production_form_list/(:any)/(:any)'] = "admin/Admin_controller/summary/$1"; 
 

// $route['delete/']='admin/Admin_controller/delete';
$route['delete/(:any)/(:any)']='admin/Admin_controller/delete/$1';
$route['logout'] = "admin/Admin_controller/logout";
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
$route['update_auto_manual_task/(:any)/(:any)'] = "admin/Admin_controller/update_auto_manual_task/$1";
$route['update_task'] = "admin/Admin_controller/update_task";
$route['task_list'] = "admin/Admin_controller/task_list";
$route['auto_task_list'] = "admin/Admin_controller/auto_task_list";
$route['task_update_list'] = "admin/Admin_controller/task_update_list";


$route['add_production_bom'] = "admin/Admin_controller/add_production_bom";
$route['add_production_bom/(:any)/(:any)'] = "admin/Admin_controller/add_production_bom/$1";
$route['production_bom_list'] = "admin/Admin_controller/production_bom_list";
$route['production_bom_list/(:any)'] = "admin/Admin_controller/production_bom_list/$1";
$route['production_bom_details'] = "admin/Admin_controller/production_bom_details";

$route['order_list'] = "admin/Admin_controller/order_list";
$route['add_order'] = "admin/Admin_controller/add_order";
$route['add_order/(:any)/(:any)/(:any)'] = "admin/Admin_controller/add_order/$1";

$route['maintenance_list_details'] = "admin/Admin_controller/maintenance_list_details";
$route['maintenance_list_details/(:any)'] = "admin/Admin_controller/maintenance_list_details/$1";

$route['outward_order_list'] = "admin/Admin_controller/outward_order_list";
$route['outward_transport/(:any)/(:any)/(:any)'] = "admin/Admin_controller/add_outward_transport/$1";

$route['own_vehicle'] = "admin/Admin_controller/add_own_vehicle";
$route['own_vehicle/(:any)'] = "admin/Admin_controller/add_own_vehicle/$1";
$route['own_vehicle_list'] = "admin/Admin_controller/own_vehicle_list";
$route['printing_unit_report'] = "admin/Admin_controller/printing_unit_report";
$route['pwo_status'] = "admin/Admin_controller/pwo_status";

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






$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;




$route['admin/Ajax_controller/get_all_article_names'] = 'admin/Ajax_controller/get_all_article_names';







