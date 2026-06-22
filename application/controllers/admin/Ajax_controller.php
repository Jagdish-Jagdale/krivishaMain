<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Ajax_controller extends CI_Controller
{
    public function check_current_password_match_or_not()
    {
        $this->Admin_model->check_current_password_match_or_not();
    }
    public function check_unique_mb_name()
    {
        $this->Admin_model->check_unique_mb_name();
    }
    public function check_unique_challan_dc_no()
    {
        $this->Admin_model->check_unique_challan_dc_no();
    }
    public function check_unique_invoice_no()
    {
        $this->Admin_model->check_unique_invoice_no();
    }
    public function check_unique_dc_no()
    {
        $this->Admin_model->check_unique_dc_no();
    }
    public function check_unique_outward_invoice_no()
    {
        $this->Admin_model->check_unique_outward_invoice_no();
    }
    public function test_schema_dump() {
        $out = "";
        
        $sql = "SELECT t.task_id, 
                (
                    (SELECT COALESCE(SUM(order_quantity), 0) FROM tbl_order_sub_details WHERE order_id = t.task_id AND is_deleted = 0)
                    + 
                    (SELECT COALESCE(SUM(order_quantity), 0) FROM tbl_order_container_details WHERE order_id = t.task_id AND is_deleted = 0)
                ) AS total_bundle
                FROM tbl_auto_task_list t
                WHERE t.task_id = 'ORD-2246'";
        $res = $this->db->query($sql);
        $out .= "Query:\n" . $sql . "\n\nQuery Result:\n" . print_r($res->result_array(), true);
        
        file_put_contents(FCPATH . 'schema_dump.txt', $out);
        echo "Done";
    }
    public function check_unique_mb_alias()
    {
        $this->Admin_model->check_unique_mb_alias();
    }
    public function check_unique_mb_base()
    {
        $this->Admin_model->check_unique_mb_base();
    }
    public function check_unique_article_name()
    {
        $this->Admin_model->check_unique_article_name();
    }
    public function check_unique_plant_name()
    {
        $this->Admin_model->check_unique_plant_name();
    }
    public function get_all_mb_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_mb_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->name;
                $sub_array[] = $member->alias;
                $sub_array[] = $member->base;
                $sub_array[] = $member->make;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_mb/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_mb_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_krivisha_employee_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_krivisha_employee_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->first_name;
                $sub_array[] = $member->emp_id;
                $sub_array[] = $member->email;
                $sub_array[] = $member->mobile_number;
                $sub_array[] = $member->department;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->designation;
                $sub_array[] = date('d-m-Y', strtotime($member->date_of_joininig));
                $sub_array[] = $member->org_password;

                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('krivisha_employee/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_krivisha_employee_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_rm_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_rm_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->rm_name;
                $sub_array[] = $member->mfi;
                $sub_array[] = $member->alias;
                $sub_array[] = $member->code;
                $sub_array[] = $member->make;
                $sub_array[] = $member->type;
                $sub_array[] = $member->uom_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_rm/' . $member->id) . '/' . $member->type_of_rm . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_rm_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_rm_rejection_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_rm_rejection_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->rm_name;
                $sub_array[] = $member->mfi;
                $sub_array[] = $member->alias;
                $sub_array[] = $member->code;
                $sub_array[] = $member->make;
                $sub_array[] = $member->type;
                $sub_array[] = $member->uom_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_rm/' . $member->id) . '/' . $member->type_of_rm . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_rm_rejection_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function check_unique_rm_name()
    {
        $this->Admin_model->check_unique_rm_name();
    }
    public function get_single_uom_name()
    {
        $this->Admin_model->get_single_uom_name();
    }
    public function get_master_batch_stock_qty()
    {
        $this->Admin_model->get_master_batch_stock_qty();
    }
    public function get_article_stock_qty()
    {
        $this->Admin_model->get_article_stock_qty();
    }
    public function check_unique_rm_mfi()
    {
        $this->Admin_model->check_unique_rm_mfi();
    }
    public function check_unique_rm_alias()
    {
        $this->Admin_model->check_unique_rm_alias();
    }
    public function set_new_option()
    {
        $this->Admin_model->set_new_option();
    }
    public function get_all_type_of_mould()
    {
        $this->Admin_model->get_all_type_of_mould();
    }
    public function get_all_air_pin()
    {
        $this->Admin_model->get_all_air_pin();
    }
    public function get_all_air_spring()
    {
        $this->Admin_model->get_all_air_spring();
    }
    public function get_all_pu_nipples()
    {
        $this->Admin_model->get_all_pu_nipples();
    }
    public function get_all_ejector_pin()
    {
        $this->Admin_model->get_all_ejector_pin();
    }
    public function get_all_i_bolt()
    {
        $this->Admin_model->get_all_i_bolt();
    }
    public function get_all_cord()
    {
        $this->Admin_model->get_all_cord();
    }
    public function get_all_o_ring()
    {
        $this->Admin_model->get_all_o_ring();
    }
    public function get_all_insert_slot_plate()
    {
        $this->Admin_model->get_all_insert_slot_plate();
    }
    public function get_all_core_cylinder_seal()
    {
        $this->Admin_model->get_all_core_cylinder_seal();
    }
    public function get_all_seal()
    {
        $this->Admin_model->get_all_seal();
    }
    public function get_all_hose_pipe()
    {
        $this->Admin_model->get_all_hose_pipe();
    }
    public function get_all_alankey_bolt()
    {
        $this->Admin_model->get_all_alankey_bolt();
    }
    public function get_all_group_of_article()
    {
        $this->Admin_model->get_all_group_of_article();
    }
    public function check_unique_uom_name()
    {
        $this->Admin_model->check_unique_uom_name();
    }
    public function check_unique_department_name()
    {
        $this->Admin_model->check_unique_department_name();
    }
    public function check_unique_extra_payment_name()
    {
        $this->Admin_model->check_unique_extra_payment_name();
    }
    public function check_unique_remark_name()
    {
        $this->Admin_model->check_unique_remark_name();
    }
    public function get_all_type_of_maintenance()
    {
        $this->Admin_model->get_all_type_of_maintenance();
    }
    public function get_all_article_by_group()
    {
        $this->Admin_model->get_all_article_by_group();
    }
    public function get_all_article_by_group_production()
    {
        $this->Admin_model->get_all_article_by_group_production();
    }
    public function get_all_brand_by_party()
    {
        $this->Admin_model->get_all_brand_by_party();
    }
    public function get_selected_party_details()
    {
        $this->Admin_model->get_selected_party_details();
    }
    public function get_all_maintance_bom_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_maintance_bom_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $size_of_parts = [];
                $size_of_parts = $member->size_of_parts_id;
                $part_of_mould = [];
                $part_of_mould = $member->part_of_mould;
                switch ($size_of_parts) {
                    case $part_of_mould == 'TYPE OF MOULD':
                        $alankey_bolt = $this->Admin_model->get_type_of_mould($size_of_parts);
                        $size_of_parts = $alankey_bolt->type_of_mould;
                        break;
                    case $part_of_mould == 'ALANKEY BOLT':
                        $alankey_bolt = $this->Admin_model->get_alankey_bolt($size_of_parts);
                        $size_of_parts = $alankey_bolt->alankey_bolt;
                        break;
                    case $part_of_mould == 'SPRING':
                        $spring = $this->Admin_model->get_air_spring($size_of_parts);
                        $size_of_parts = $spring->spring;
                        break;
                    case $part_of_mould == 'PU NIPPLES':
                        $pu_nipples = $this->Admin_model->get_air_pu_nipples($size_of_parts);
                        $size_of_parts = $pu_nipples->pu_nipples;
                        break;
                    case $part_of_mould == 'EJECTOR PIN':
                        $ejector_pin = $this->Admin_model->get_air_ejector_pin($size_of_parts);
                        $size_of_parts = $ejector_pin->ejector_pin;
                        break;
                    case $part_of_mould == 'I BOLT':
                        $i_bolt = $this->Admin_model->get_air_i_bolt($size_of_parts);
                        $size_of_parts = $i_bolt->i_bolt;
                        break;
                    case $part_of_mould == 'CORD':
                        $cord = $this->Admin_model->get_air_cord($size_of_parts);
                        $size_of_parts = $cord->cord;
                        break;
                    case $part_of_mould == 'O RING':
                        $o_ring = $this->Admin_model->get_air_o_ring($size_of_parts);
                        $size_of_parts = $o_ring->o_ring;
                        break;
                    case $part_of_mould == 'INSERT SLOT PLATE':
                        $insert_slot_plate = $this->Admin_model->get_air_insert_slot_plate($size_of_parts);
                        $size_of_parts = $insert_slot_plate->insert_slot_plate;
                        break;
                    case $part_of_mould == 'CORE CYLINDER SEAL':
                        $core_cylinder_seal = $this->Admin_model->get_air_core_cylinder_seal($size_of_parts);
                        $size_of_parts = $core_cylinder_seal->core_cylinder_seal;
                        break;
                    case $part_of_mould == 'SEAL':
                        $seal = $this->Admin_model->get_air_seal($size_of_parts);
                        $size_of_parts = $seal->seal;
                        break;
                    case $part_of_mould == 'HOSE PIPE':
                        $hose_pipe = $this->Admin_model->get_air_hose_pipe($size_of_parts);
                        $size_of_parts = $hose_pipe->hose_pipe;
                        break;
                    case $part_of_mould == 'AIR PIN':
                        $air_pin = $this->Admin_model->get_air_pin($size_of_parts);
                        $size_of_parts = $air_pin->air_pin;
                        break;
                    default:
                        $size_of_parts = $member->size_of_mould_id;
                        break;
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->article_name;
                $sub_array[] = $member->group_of_article;
                $sub_array[] = $size_of_parts;
                $sub_array[] = $member->part_of_mould;
                $sub_array[] = $member->uom_name;
                $sub_array[] = $member->quantity;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_article/' . $member->article_id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_maintance_bom_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_uom_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_uom_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->uom_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('uom_list/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_uom_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_krivisha_department_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_krivisha_department_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->department;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('krivisha_department/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_krivisha_department_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_extra_payment_option_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_extra_payment_option_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->extra_payment_option;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('extra_payment_master/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_extra_payment_option_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_remark_master_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_remark_master_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->remark_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('remark_master/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a class="btn btn-danger" href="' . base_url('delete/' . $member->id . '/tbl_remark_master') . '" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');"><i class="fa-solid fa-trash"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_remark_master_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_printing_unit_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_printing_unit_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->printing_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('printing_unit_list/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_printing_unit_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_plant_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_plant_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->plant_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('plant_list/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';

                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_plant_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_machine_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_machine_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->machine_name;
                $sub_array[] = $member->department;
                $sub_array[] = $member->plant_name;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('machine_list/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_machine_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function check_unique_printing_unit()
    {
        $this->Admin_model->check_unique_printing_unit();
    }
    public function check_unique_machine_name()
    {
        $this->Admin_model->check_unique_machine_name();
    }
    public function set_new_type_option()
    {
        $this->Admin_model->set_new_type_option();
    }
    public function get_all_rm_type()
    {
        $this->Admin_model->get_all_rm_type();
    }
    public function get_all_rm_make()
    {
        $this->Admin_model->get_all_rm_make();
    }
    public function get_all_rm_ink()
    {
        $this->Admin_model->get_all_rm_ink();
    }
    public function get_all_location_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_location_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->city;
                $sub_array[] = $member->district_name;
                $sub_array[] = $member->state_name;
                $sub_array[] = $member->pincode;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_location/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_location_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function check_unique_city_name()
    {
        $this->Admin_model->check_unique_city_name();
    }
    public function check_unique_pincode_name()
    {
        $this->Admin_model->check_unique_pincode_name();
    }
    public function check_unique_party_name()
    {
        $this->Admin_model->check_unique_party_name();
    }
    public function check_unique_mobile()
    {
        $this->Admin_model->check_unique_mobile();
    }
    public function check_unique_gst_pan()
    {
        $this->Admin_model->check_unique_gst_pan();
    }
    public function set_new_party_option()
    {
        $this->Admin_model->set_new_party_option();
    }
    public function get_all_designation()
    {
        $this->Admin_model->get_all_designation();
    }
    public function get_all_division()
    {
        $this->Admin_model->get_all_division();
    }
    public function get_all_nature_of_business()
    {
        $this->Admin_model->get_all_nature_of_business();
    }
    public function get_all_type_of_business()
    {
        $this->Admin_model->get_all_type_of_business();
    }
    public function get_all_parties_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_parties_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $party_type = explode(',', $member->party_type);
                $division_type = explode(',', $member->division_ids);
                $party_type_display = '';
                if (in_array(1, $party_type))
                    $party_type_display .= 'Customer';
                if (in_array(2, $party_type))
                    $party_type_display .= ($party_type_display ? ', ' : '') . 'Supplier';
                $division_type_display = '';
                if (in_array(1, $division_type))
                    $division_type_display .= 'Container';
                if (in_array(2, $division_type))
                    $division_type_display .= ($division_type_display ? ', ' : '') . 'Household';
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->party_name;
                $sub_array[] = $party_type_display;
                $sub_array[] = $member->mobile;
                $sub_array[] = $member->gst_pan;
                $sub_array[] = $member->address;
                $sub_array[] = $member->city;
                $sub_array[] = $member->contact_name;
                $sub_array[] = $member->designation;
                $sub_array[] = $member->sec_contact;
                $sub_array[] = $member->designation_two;
                $sub_array[] = $division_type_display;
                $sub_array[] = $member->first_name;
                $sub_array[] = $member->nature_of_business;
                $sub_array[] = $member->type_of_business;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_customer/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_parties_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function set_new_brand_option()
    {
        $this->Admin_model->set_new_brand_option();
    }
    public function set_new_department()
    {
        $this->Admin_model->set_new_department();
    }
    public function get_all_brand_type()
    {
        $this->Admin_model->get_all_brand_type();
    }
    public function get_all_department()
    {
        $this->Admin_model->get_all_department();
    }
    public function get_all_machine_department()
    {
        $this->Admin_model->get_all_machine_department();
    }
    public function check_unique_brand_name()
    {
        $this->Admin_model->check_unique_brand_name();
    }
    public function get_all_brands_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_brands_list($length, $start, $search);
        // echo"<pre>";print_r($list_data);exit;
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->brand_name;
                $sub_array[] = $member->brand_type;
                $sub_array[] = $member->party_name;
                $sub_array[] = $member->department;
                $sub_array[] = $member->ink_names;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_brand/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_brands_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_transport_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_transport_list($length, $start, $search);
        // echo"<pre>";print_r($list_data);exit;
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->transport_name;
                $sub_array[] = $member->mobile_one;
                $sub_array[] = $member->mobile_two;
                $sub_array[] = $member->transport_id;
                $sub_array[] = $member->cities;
                $sub_array[] = $member->transport_rating;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_transport/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_transport_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_machine_master()
    {
        $this->Admin_model->get_machine_master();
    }
    public function get_printing_unit()
    {
        $this->Admin_model->get_printing_unit();
    }
    public function get_article_master()
    {
        $this->Admin_model->get_article_master();
    }
    public function get_plant_master()
    {
        $this->Admin_model->get_plant_master();
    }
    public function delete_sub_order_item()
    {
        $this->Admin_model->delete_sub_order_item();
    }
    public function get_all_problems_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_problems_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $maintaince = '';
                $type = '';
                $maintaince_res = $member->maintaince;
                switch ($member->id) {
                    case $maintaince_res == '1':
                        $maintaince = 'Machine';
                        $type_name = $member->type_id;
                        $machine_name = $this->Admin_model->get_type_of_machine($type_name);
                        $type = $machine_name->machine_name;
                        break;
                    case $maintaince_res == '2':
                        $maintaince = 'Mould/Article Name';
                        $type_name = $member->type_id;
                        $article_name = $this->Admin_model->get_type_of_article($type_name);
                        $type = $article_name->article_name;
                        break;
                    case $maintaince_res == '3':
                        $maintaince = 'Printing Unit';
                        $type_name = $member->type_id;
                        $printing_unit_name = $this->Admin_model->get_type_of_machine($type_name);
                        $type = $printing_unit_name->machine_name;
                        break;
                    case $maintaince_res == '4':
                        $maintaince = 'Plant';
                        $type_name = $member->type_id;
                        $plant_name = $this->Admin_model->get_type_of_plant($type_name);
                        $type = $plant_name->plant_name;
                        break;
                    case $maintaince_res == '5':
                        $maintaince = 'Other';
                        $type = 'N/A';
                        break;
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $maintaince;
                $sub_array[] = $type;
                $sub_array[] = $member->problem;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('problems_list/' . $member->id) . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_problems_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_machine_types()
    {
        $this->Admin_model->get_machine_types();
    }
    public function get_all_task_list()
    {
        $this->Admin_model->get_all_task_list();
    }
    public function get_all_sub_types()
    {
        $this->Admin_model->get_all_sub_types();
    }
    public function get_idle_state_details()
    {
        $this->Admin_model->get_idle_state_details();
    }
    public function get_coverage_reports_details()
    {
        $this->Admin_model->get_coverage_reports_details();
    }
    public function get_salesman_on_fields_details()
    {
        $this->load->model('admin/Admin_model');
        $result = $this->Admin_model->get_salesman_on_fields_details();
        echo json_encode($result);
    }
    public function get_all_production_maintenance_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_production_maintenance_list($length, $start, $search);
        // echo"<pre>";print_r($list_data);exit;
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $maintaince = '';
                $type = '';
                $maintaince_res = $member->maintaince;
                switch ($member->id) {
                    case $maintaince_res == '1':
                        $maintaince = 'Machine';
                        $type_name = $member->sub_type_id;
                        $machine_name = $this->Admin_model->get_type_of_machine($type_name);
                        $type = $machine_name->machine_name;
                        break;
                    case $maintaince_res == '2':
                        $maintaince = 'Mould/Article Name';
                        $type_name = $member->sub_type_id;
                        $article_name = $this->Admin_model->get_type_of_article($type_name);
                        $type = $article_name->article_name;
                        break;
                    case $maintaince_res == '3':
                        $maintaince = 'Printing Unit';
                        $type_name = $member->sub_type_id;
                        $printing_unit_name = $this->Admin_model->get_type_of_machine($type_name);
                        $type = $printing_unit_name->machine_name;
                        break;
                    case $maintaince_res == '4':
                        $maintaince = 'Plant';
                        $type_name = $member->sub_type_id;
                        $plant_name = $this->Admin_model->get_type_of_plant($type_name);
                        $type = $plant_name->plant_name;
                        break;
                    case $maintaince_res == '5':
                        $maintaince = 'Other';
                        $type = 'N/A';
                        break;
                }
                $type_of_action = '';
                $type_id = $member->type_of_action;
                switch ($member->id) {
                    case $type_id == '1':
                        $type_of_action = 'Emergency';
                        break;
                    case $type_id == '2':
                        $type_of_action = 'Online Breakdown';
                        break;
                    case $type_id == '3':
                        $type_of_action = 'Preventive';
                        break;
                    case $type_id == '4':
                        $type_of_action = 'Outside Work';
                        break;
                    case $type_id == '5':
                        $type_of_action = 'General';
                        break;
                    case $type_id == '6':
                        $type_of_action = 'Other';
                        break;
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->mwo_code;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->first_name;
                $sub_array[] = date('d-m-Y', strtotime($member->date));
                $sub_array[] = $type_of_action;
                $sub_array[] = $maintaince;
                $sub_array[] = $type;
                $sub_array[] = $member->problems;
                $action_buttons = '<td>';

                if ($member->plant_manager_approval_status != '1') {
                    $action_buttons .= '
                        <a class="icon_link" href="' . base_url('maintenance_list/' . $member->id . '/' . $member->mwo_code) . '">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a><br>
                        <a class="icon_link" href="' . base_url('add_maintenance/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a><br>';
                } else {
                    $action_buttons .= 'Completed<br>';
                }

                $action_buttons .= '</td>';


                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_total_production_list_count();
        $filteredRecords = $this->Admin_model->get_all_production_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_task_type_of_machine()
    {
        $this->Admin_model->get_task_type_of_machine();
    }
    public function get_task_type_of_article()
    {
        $this->Admin_model->get_task_type_of_article();
    }
    public function get_task_type_of_plant()
    {
        $this->Admin_model->get_task_type_of_plant();
    }
    public function set_new_particular()
    {
        $this->Admin_model->set_new_particular();
    }

    public function get_all_particular()
    {
        $this->Admin_model->get_all_particular();
    }
    public function set_new_sub_type()
    {
        $this->Admin_model->set_new_sub_type();
    }
    public function get_all_sub_category()
    {
        $this->Admin_model->get_all_sub_category();
    }
    public function get_all_production_bom_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_production_bom_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->article_name;
                $sub_array[] = $member->batch;
                $sub_array[] = $member->weight;
                $sub_array[] = $member->raw_material_one;
                $sub_array[] = $member->raw_material_two;
                $sub_array[] = $member->other_rm;
                $sub_array[] = $member->master_batch;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('add_production_bom/' . $member->article_id) . '/' . $member->id . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_bom_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_order_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search_post = $this->input->post("search");
        $search = (is_array($search_post) && isset($search_post['value'])) ? $search_post['value'] : '';
        $response = $this->Admin_model->get_all_order_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                switch ($member->type_of_order) {
                    case $member->type_of_order == '1':
                        $type_of_order = 'Household';
                        break;
                    case $member->type_of_order == '2':
                        $type_of_order = 'Container';
                        break;
                    default:
                        $type_of_order = 'Both';
                        break;
                }
                $order_ststus = $this->Admin_model->get_outward_order_status($member->order_id);
                $auto_task_order = $this->db->get_where('tbl_auto_task_list', array('task_id' => $member->order_id))->row();
                // if ($member->order_status == '1') {
                //     $order_ststus = 'Pending';
                // } else if ($member->order_status == '2') {
                //     $order_ststus = 'Processed to Account';
                // } else if ($member->order_status == '3') {
                if ($member->order_status == '3') {
                    $order_ststus = 'Partially Dispatched';
                } else if ($member->order_status == '4') {
                    $order_ststus = 'Full Dispatched';
                } else if ($member->order_status == '5') {
                    $order_ststus = 'Order Closed';
                } else if ($member->order_status == '6') {
                    $order_ststus = 'Order Cancelled';
                } else if ($member->order_status == '7') {
                    $order_ststus = 'Printing Inprocess';
                } else if ($member->order_status == '8') {
                    $order_ststus = 'Printing Completed';
                } else if ($member->order_status == '9') {
                    $order_ststus = 'Dispatch Inprocess';
                } else if ($order_ststus == 'Pending') {
                    if ($member->order_status == '3') {
                        $order_ststus = 'Partially Dispatched';
                    } else if ($member->order_status == '2') {
                        if ($auto_task_order && $auto_task_order->order_department_status == '3') {
                            $order_ststus = 'Dispatch Inprocess';
                        } else if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
                            $order_ststus = 'Printing Inprocess';
                        } else {
                            $order_ststus = 'Processed to Account';
                        }
                    } else if ($member->order_status == '6') {
                        $order_ststus = 'Order Cancelled';
                    } else if ($member->order_status == '5') {
                        $order_ststus = 'Order Closed';
                    } else {
                        $order_ststus = 'Pending';
                    }
                } else if ($order_ststus == 'Processed to Account') {
                    if ($member->order_status == '5') {
                        $order_ststus = 'Order Closed';
                    } else if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
                        $order_ststus = 'Printing Inprocess';
                    } else {
                        $order_ststus = 'Processed to Account';
                    }
                } else if ($order_ststus == 'Partially Dispatched') {
                    if ($member->order_status == '5') {
                        $order_ststus = 'Order Closed';
                    } else {
                        $order_ststus = 'Partially Dispatched';
                    }
                } else if ($order_ststus == 'Dispatch Inprocess') {
                    if ($auto_task_order && $auto_task_order->order_status == '0' && $auto_task_order->order_department_status == '2') {
                        $order_ststus = 'Printing Inprocess';
                    } else if ($auto_task_order && $auto_task_order->order_status == '2' && $auto_task_order->order_department_status == '2') {
                        $order_ststus = 'Printing Completed';
                    } else {
                        $order_ststus = 'Dispatch Inprocess';
                    }
                }

                // Get dispatch dates for this order
                $dispatch_dates = '';
                $this->db->select("GROUP_CONCAT(DISTINCT DATE_FORMAT(created_on, '%d-%m-%Y') ORDER BY created_on DESC) as dispatch_dates");
                $this->db->from('tbl_dispatch_order_data');
                $this->db->where('order_id', $member->order_id);
                $dispatch_result = $this->db->get()->row();
                if ($dispatch_result && $dispatch_result->dispatch_dates) {
                    $dispatch_dates = $dispatch_result->dispatch_dates;
                }

                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->order_id;
                $sub_array[] = date('d-m-Y', strtotime($member->order_date));
                $sub_array[] = $member->party_name;
                $sub_array[] = $type_of_order;
                $sub_array[] = $member->id;

                $action_buttons = '<td>';

                if ($order_ststus == 'Pending' || $order_ststus == 'Processed to Account') {
                    $action_buttons .= '
                        <a class="btn btn-success" href="' . base_url('add_order/' . $member->id . '/' . $member->type_of_order . '/' . $member->order_id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>';
                    if ($order_ststus == 'Processed to Account') {
                        $action_buttons .= '
                            <a class="btn btn-primary" data-bs-toggle="modal" onclick="proceed_Logistics(' . $member->id . ', ' . $member->ink_type . ', ' . $member->party_id . ', ' . $member->type_of_order . ')" data-bs-target="#nextPrintingModal" title="Proceed to Logistics">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>';
                    } else {
                        $action_buttons .= '
                            <a class="btn btn-primary" data-bs-toggle="modal" onclick="proceed_Account(' . $member->id . ', ' . $member->ink_type . ', ' . $member->party_id . ', ' . $member->type_of_order . ')" data-bs-target="#nextPrintingModal" title="Proceed to Account">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>';
                    }
                    $action_buttons .= '
                        <a class="btn btn-danger" href="' . base_url('cancel_order/' . $member->id) . '" 
                        title="Cancel Order" 
                        onclick="return confirm(\'Are you sure you want to cancel this order?\')">
                            <i class="fa-solid fa-times"></i>
                        </a>';
                } else if ($member->order_status == '6') {
                    $action_buttons .= '<span class="badge bg-danger"><i class="fa-solid fa-lock"></i> Closed</span>';
                } else if ($member->order_status == '5') {
                    $action_buttons .= '<span class="badge bg-danger"><i class="fa-solid fa-lock"></i> Manually Closed</span>';
                } else {
                    $action_buttons .= '<span class="badge rounded-pill bg-info" data-bs-toggle="tooltip" title="This order has been forwarded">
                            <i class="fa-solid fa-share-from-square"></i> Forwarded
                        </span>';
                }
                $action_buttons .= '</td>';
                switch ($member->ink_type) {
                    case '1':
                        $member->ink_type = 'Plain';
                        break;
                    case '2':
                        $member->ink_type = 'Printing';
                        break;
                    default:
                        $member->ink_type = 'N/A';
                        break;
                }

                $sub_array[] = $member->ink_type;
                $sub_array[] = $dispatch_dates;
                $sub_array[] = $order_ststus;
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_order_details()
    {
        $this->Admin_model->get_sub_order_details();
    }
    public function get_extra_charges_details()
    {
        $this->Admin_model->get_extra_charges_details();
    }
    public function get_employees_by_department()
    {
        $this->Admin_model->get_employees_by_department();
    }
    public function get_party_order_details()
    {
        $this->Admin_model->get_party_order_details();
    }
    public function get_sub_order_task_details()
    {
        $this->Admin_model->get_sub_order_task_details();
    }
    public function get_sub_task_details()
    {
        $this->Admin_model->get_sub_task_details();
    }


    public function save_schedule()
    {
        $this->Admin_model->save_schedule();
    }
    public function get_schedule_data()
    {
        $this->Admin_model->get_schedule_data();
    }
    public function get_month_schedule_data()
    {
        $month = $this->input->post('month');
        $plant_id = $this->input->post('plant_id');
        $machine_id = $this->input->post('machine_id');

        $schedules = $this->Admin_model->get_month_schedule_data($month, $plant_id, $machine_id);

        echo json_encode($schedules);
    }
    public function set_production_bom_status_ajx()
    {
        $this->Admin_model->set_production_bom_status_ajx();
    }
    public function get_production_schedule_data()
    {
        $this->Admin_model->get_production_schedule_data();
    }
    public function get_return_stock_production_schedule_data()
    {
        $this->Admin_model->get_return_stock_production_schedule_data();
    }
    public function check_unique_employee_id()
    {
        $this->Admin_model->check_unique_employee_id();
    }

    public function get_article_bom_data_for_store()
    {
        $this->Admin_model->get_article_bom_data_for_store();
    }
    public function get_all_machines()
    {
        $this->Admin_model->get_all_machines();
    }
    public function get_pincode_by_location()
    {
        $this->Admin_model->get_pincode_by_location();
    }
    public function get_plants_by_department()
    {
        $this->Admin_model->get_plants_by_department();
    }
    public function set_order_status()
    {
        $this->Admin_model->set_order_status();
    }
    public function set_order_status_logistics()
    {
        $this->Admin_model->set_order_status_logistics();
    }
    public function get_all_task_history()
    {
        $this->Admin_model->get_all_task_history();
    }
    public function get_all_manual_task_history()
    {
        $this->Admin_model->get_all_manual_task_history();
    }
    public function get_outward_sub_order_details()
    {
        $this->Admin_model->get_outward_sub_order_details();
    }
    public function get_outward_dispatch_details()
    {
        $this->Admin_model->get_outward_dispatch_details();
    }

    public function get_all_manual_task_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_manual_task_list($length, $start, $search);
        $profile = $this->Admin_model->get_user_profile();
        $access = $this->Admin_model->get_staff_priiliges();
        $is_admin = (!empty($profile) && $profile->is_admin == '1');
        $can_reply_manual = $is_admin || in_array('manual_task_reply', $access);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $task_heads = [
                    '1' => 'Enquiry',
                    '2' => 'Cold Call',
                    '3' => 'Office Requirement',
                    '4' => 'Self Task',
                ];
                $member->task_head = $task_heads[$member->task_head] ?? 'Complaint';
                $priorities = [
                    '1' => 'High',
                    '2' => 'Medium',
                ];
                $member->priority = $priorities[$member->priority] ?? 'Low';
                $task_statuses = [
                    '1' => 'Pending',
                ];
                $member->task_status = $task_statuses[$member->task_status] ?? 'Completed';
                $task_actions = [
                    '1' => 'Forward to other Department/Person',
                    '2' => 'Mark as Closed',
                    '3' => 'Create Order',
                ];
                if ($member->party_id) {
                    $party_id = $member->party_id;
                } else {
                    $party_id = 0;
                }
                $member->task_action = $task_actions[$member->task_action] ?? '-';
                $sub_array = [
                    $offset++,
                    $member->task_id,
                    $member->employee_name,
                    $member->task_head,
                    $member->party_name,
                    date('d-m-Y', strtotime($member->complete_by_date)),
                    date("h:i A", strtotime($member->complete_by_time)),
                    $member->priority,
                    $member->remark,
                    $member->department,
                    $member->assigned_to_name,
                    $member->task_status,
                    $member->task_action,
                    $member->details_of_task
                ];
                $enq = $member->task_head;
                if ($enq == 'Enquiry') {
                    $enq = 'Enquiry';
                } else {
                    $enq = 'Complaint';
                }
                if ($member->task_status != 'Completed' && ($is_admin || $member->assign_to_id == $this->session->userdata('id')) && $can_reply_manual) {
                    $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('update_auto_manual_task/' . $member->id) . '/' . $enq . '/' . $party_id . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-secondary" data-bs-toggle="modal" onclick="showLog(' . $member->id . ')">
                            Log
                        </a>
                    </td>';
                } else {
                    $action_buttons = '
                    <td>
                     <a class="btn btn-secondary" data-bs-toggle="modal" onclick="showLog(' . $member->id . ')">
                            Log
                        </a>
                    </td>';
                }
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_auto_task_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_auto_task_list($length, $start, $search);
        $profile = $this->Admin_model->get_user_profile();
        $access = $this->Admin_model->get_staff_priiliges();
        $is_admin = (!empty($profile) && $profile->is_admin == '1');
        $can_reply_auto = $is_admin || in_array('auto_task_reply', $access);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                if ($member->order_department == '1') {
                    if ($member->task_action == '1') {
                        $order_ststus = $this->Admin_model->get_outward_order_status($member->task_id);

                        if ($member->order_status == '3') {
                            $order_ststus = 'Partially Dispatched';
                        } else if ($member->order_status == '4') {
                            $order_ststus = 'Full Dispatched';
                        } else if ($member->order_status == '5') {
                            $order_ststus = 'Order Closed';
                        } else if ($member->order_status == '6') {
                            $order_ststus = 'Order Cancelled';
                        } else if ($member->order_status == '7') {
                            $order_ststus = 'Printing Inprocess';
                        } else if ($member->order_status == '8') {
                            $order_ststus = 'Printing Completed';
                        } else if ($member->order_status == '9') {
                            $order_ststus = 'Dispatch Inprocess';
                        } else if ($order_ststus == 'Pending') {
                            if ($member->order_department_status == '3' && $member->order_status == '2') {
                                $order_ststus = 'Dispatch Inprocess';
                            } else if ($member->order_department_status == '2' && $member->order_status == '0') {
                                $order_ststus = 'Printing Inprocess';
                            } else if ($member->order_department_status == '3' && $member->order_status == '3') {
                                $order_ststus = 'Partially Dispatched';
                            } else if ($member->order_department_status == '3' && $member->order_status == '0') {
                                $order_ststus = 'Dispatch Inprocess';
                            }
                        } else if ($order_ststus == 'Processed to Account') {
                            if ($member->order_status == '0' && $member->order_department_status == '2') {
                                $order_ststus = 'Printing Inprocess';
                            } else {
                                $order_ststus = 'Pending';
                            }

                        } else if ($order_ststus == 'Dispatch Inprocess') {
                            if ($member->order_status == '2' && $member->order_department_status == '2') {
                                $order_ststus = 'Printing Completed';
                            } else if ($member->order_status == '0' && $member->order_department_status == '2') {
                                $order_ststus = 'Printing Inprocess';
                            } else {
                                $order_ststus = 'Dispatch Inprocess';
                            }

                        }
                    } else {
                        $order_ststus = 'Completed';
                    }
                } else {
                    if ($member->task_status == '1') {
                        $order_ststus = 'Pending';
                    } else {
                        $order_ststus = 'Completed';
                    }
                }

                $order_department = [
                    '1' => 'Create Order',
                    '2' => 'Production Schedule',
                ];
                $member->order_department = $order_department[$member->order_department] ?? 'Maintenance';
                $task_actions = [
                    '1' => 'Forward to other Department/Person',
                    '2' => 'Mark as Closed',
                    '3' => 'Create Order',
                ];
                $member->task_action = $task_actions[$member->task_action] ?? '-';
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);

                $sub_array = [
                    $offset++,
                    $member->task_id,
                    $member->party_name,
                    $member->employee_name,
                    $datetime->format('d M, Y h:i A'),
                    $member->order_department,
                    $member->priority,
                    $member->type_of_order == '1' ? 'Household' : ($member->type_of_order == '2' ? 'Container' : ($member->type_of_order == '3' ? 'Both' : '-')),
                    $member->d_name,
                    $member->assigned_to_name,
                    $member->task_action,
                    $order_ststus,
                    $member->details_of_task
                ];
                // || $order_ststus == 'Dispatch Inprocess' || $order_ststus == 'Printing Inprocess' || $order_ststus == 'Partially Dispatched
                if ($member->task_action == 'Mark as Closed' || !$can_reply_auto) {
                    $action_buttons = '
                    <td>
                        <a class="btn btn-secondary" data-bs-toggle="modal" onclick="showLog(' . $member->id . ', \'' . $order_ststus . '\')">
                            Log
                        </a>
                    </td>';
                } else {
                    $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('update_auto_manual_task/' . $member->id) . '/143/-" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-secondary" data-bs-toggle="modal" onclick="showLog(' . $member->id . ', \'' . $order_ststus . '\')">
                            Log
                        </a>  
                    </td>';
                }
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }


    public function get_all_stock_report_inward_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_stock_report_inward_list($length, $start, $search);
        // echo"<pre>";print_r($response);exit;
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->rm_name,
                    $member->uom_name,
                    $datetime->format('d M, Y h:i A'),
                    $member->opening_stock,
                    $member->rate,
                    $member->inward_qty,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_master_batch_stock_inward_report()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_master_batch_stock_inward_report($length, $start, $search);
        // echo"<pre>";print_r($response);exit;
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->mb_name,
                    $datetime->format('d M, Y h:i A'),
                    $member->opening_stock,
                    $member->rate,
                    $member->inward_qty,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }



    public function get_all_stock_report_raw_material_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_stock_report_raw_material_list($length, $start, $search);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->rm_name,
                    $member->uom_name,
                    $datetime->format('d M, Y h:i A'),
                    $member->opening_stock,
                    $member->inward_qty,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_stock_ledger_list()
    {
        $draw   = intval($this->input->post("draw"));
        $start  = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $type   = $this->input->post('search_report_type');

        $production_rows = [];

        // For article/mould: fetch ALL records (no SQL LIMIT) so we can merge
        // production rows, sort, get accurate total, then slice for the page.
        if ($type === 'article' || $type === 'mould') {
            $response = $this->Admin_model->get_stock_ledger_list(-1, 0, $search);
            $production_rows = $this->Admin_model->get_article_production_ledger_rows($type);

            // Merge production rows that are not already in history
            if (!empty($production_rows)) {
                $existing_keys = [];
                foreach ($response['data'] as $row) {
                    $existing_keys[] = ($row->production_id ?? '') . '_' . ($row->article_id ?? '');
                }
                foreach ($production_rows as $row) {
                    $row_key = ($row->production_id ?? '') . '_' . ($row->article_id ?? '');
                    if (!in_array($row_key, $existing_keys, true)) {
                        $response['data'][] = $row;
                    }
                }
            }

            // Sort merged result newest-first
            if (!empty($response['data'])) {
                usort($response['data'], function ($a, $b) {
                    $a_time = strtotime($a->date ?? $a->created_on ?? '1970-01-01 00:00:00') ?: 0;
                    $b_time = strtotime($b->date ?? $b->created_on ?? '1970-01-01 00:00:00') ?: 0;
                    if ($a_time === $b_time) {
                        return (int)($b->id ?? 0) <=> (int)($a->id ?? 0);
                    }
                    return $b_time <=> $a_time;
                });
            }

            // True total count after merge
            $response['total_count'] = count($response['data']);

            // Apply pagination slice
            if ($length > 0) {
                $response['data'] = array_slice($response['data'], $start, $length);
            }
        } else {
            // raw_material / color: SQL LIMIT applied in model — no merge needed
            $response = $this->Admin_model->get_stock_ledger_list($length, $start, $search);
        }

        $data   = [];
        $offset = $start + 1;

        $summary = $this->Admin_model->get_stock_ledger_summary($type, $search);

        if (!empty($response['data'])) {

            // ── NEW TABLE PATH: balance already stored — output directly ──────
            if (($response['source'] ?? 'legacy') === 'new') {
                foreach ($response['data'] as $row) {
                    $date_str = !empty($row->date) ? date('d-m-Y', strtotime($row->date)) : 'N/A';
                    $data[] = [
                        $offset++,
                        $date_str,
                        $row->plant_name ?? '-',
                        $row->transaction_type ?? '-',
                        $row->reference_no    ?? '-',
                        $row->order_no ?? $row->order_id ?? '-', // New Order ID column
                        $row->item_name ?? 'N/A',
                        round(floatval($row->inward_qty  ?? 0), 3),
                        round(floatval($row->outward_qty ?? 0), 3),
                        round(floatval($row->total_quantity ?? 0), 3),
                    ];
                }

            } else {
                // ── LEGACY PATH: PHP balance computation ───────────────────────
                $raw_list = [];
                foreach ($response['data'] as $member) {
                    $flag = $member->is_inward_outward ?? '0';
                    $production_inward_qty = floatval($member->approved_qty ?? 0);
                    $is_article_production_row = ($type === 'article' || $type === 'mould') && $production_inward_qty > 0;

                    // Inward quantity
                    $inward_qty = 0;
                    $inward_flags = ['0', '1', '3', '6', '7'];
                    // If it's an article/mould report, production (5) increases stock (INWARD from Article Production Stock Report)
                    if ($type === 'article' || $type === 'mould') {
                        $inward_flags[] = '5';
                    }
                    if ($is_article_production_row || in_array($flag, $inward_flags)) {
                        if ($is_article_production_row || $flag === '5') {
                            // Production/approved_qty from Article Production Stock Report - INWARD quantity
                            $inward_qty = $production_inward_qty;
                        } elseif ($flag === '3') {
                            $inward_qty = floatval($member->adjusted_qty ?? 0);
                        } elseif ($flag === '6') {
                            $inward_qty = floatval($member->return_stock_qty ?? 0);
                        } else {
                            $inward_qty = floatval($member->inward_qty ?? 0);
                        }
                    }

                    // Outward quantity
                    $outward_qty = 0;
                    $outward_flags = ['2', '4'];
                    // If it's raw material/master batch, production (5) decreases stock
                    if ($type !== 'article' && $type !== 'mould') {
                        $outward_flags[] = '5';
                    }
                    if (in_array($flag, $outward_flags)) {
                        if ($flag === '4') {
                            $outward_qty = floatval($member->adjusted_qty ?? 0);
                        } elseif ($flag === '5') {
                            $outward_qty = floatval($member->approved_qty ?? 0);
                        } else {
                            if ($type === 'article' && isset($member->dispatch_quantity_override)) {
                                $outward_qty = floatval($member->dispatch_quantity_override);
                            } else {
                                $outward_qty = floatval($member->outward_qty ?? 0);
                            }
                        }
                    }

                    // Fetch associated dynamic order IDs smoothly using direct lookups per paginated item
                    $created_on = $member->created_on ?? null;
                    if ($type === 'article' && !empty($member->article_id) && $created_on) {
                        $this->db->select('order_id, dispatch_id, dispatch_quantity');
                        $this->db->where('article_id', $member->article_id);
                        $this->db->where('created_on', $created_on);
                        $dp = $this->db->get('tbl_dispatch_order_data')->row();
                        $member->dispatch_order_id = $dp->order_id ?? null;
                        if (!empty($dp->dispatch_quantity)) {
                            $member->dispatch_quantity_override = $dp->dispatch_quantity;
                        }
                        
                        if (!empty($dp->dispatch_id) && $dp->dispatch_id != '0') {
                            $this->db->select('tbl_customers.party_name');
                            $this->db->join('tbl_customers', 'tbl_customers.id = tbl_outward_orders.party_id', 'left');
                            $this->db->where('tbl_outward_orders.id', $dp->dispatch_id);
                            $dp_party = $this->db->get('tbl_outward_orders')->row();
                            $member->dispatch_party_name = $dp_party->party_name ?? null;
                        } elseif (!empty($dp->order_id)) {
                            $numeric_order_id = str_replace('ORD-', '', $dp->order_id);
                            $this->db->select('tbl_customers.party_name');
                            $this->db->join('tbl_customers', 'tbl_customers.id = tbl_order_details.party_id', 'left');
                            $this->db->where('tbl_order_details.id', $numeric_order_id);
                            $ord_party = $this->db->get('tbl_order_details')->row();
                            $member->dispatch_party_name = $ord_party->party_name ?? null;
                        }

                        // Determine if this is actually a plant-to-plant transfer via twin row timestamps
                        if ($flag === '2' && empty($member->dispatch_party_name)) {
                            $this->db->select('tbl_plant_master.plant_name');
                            $this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
                            $this->db->where('tbl_raw_material_stock_report_history.article_id', $member->article_id);
                            $this->db->where('tbl_raw_material_stock_report_history.created_on', $created_on);
                            $this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '1');
                            $rcv = $this->db->get('tbl_raw_material_stock_report_history')->row();
                            if (!empty($rcv->plant_name)) {
                                $member->receiver_plant_name = $rcv->plant_name;
                                $member->dispatch_order_id = 'TransferOut';
                            }
                        }

                        if (($flag === '0' || $flag === '1') && empty($member->supplier_party_name)) {
                            $this->db->select('tbl_plant_master.plant_name');
                            $this->db->join('tbl_plant_master', 'tbl_plant_master.id = tbl_raw_material_stock_report_history.plant_id', 'left');
                            $this->db->where('tbl_raw_material_stock_report_history.article_id', $member->article_id);
                            $this->db->where('tbl_raw_material_stock_report_history.created_on', $created_on);
                            $this->db->where('tbl_raw_material_stock_report_history.is_inward_outward', '2');
                            $snd = $this->db->get('tbl_raw_material_stock_report_history')->row();
                            if (!empty($snd->plant_name)) {
                                $member->supplier_party_name = 'From: ' . $snd->plant_name;
                                $member->order_id_fallback = 'TransferIn';
                            }
                        }
                    }
                    if ($type === 'raw_material' && !empty($member->raw_material_id) && $created_on) {
                        $this->db->select('request_no');
                        $this->db->where('raw_material_id', $member->raw_material_id);
                        $this->db->where('created_on', $created_on);
                        $rq = $this->db->get('tbl_received_rm_qty_data')->row();
                        $member->rm_request_no = $rq->request_no ?? null;

                        $this->db->select('schedule_id');
                        $this->db->where('item_table_id', $member->raw_material_id);
                        $this->db->where('created_on', $created_on);
                        $sch = $this->db->get('tbl_production_schedules_rm_details')->row();
                        if (!empty($sch->schedule_id)) {
                            $member->rm_schedule_id = $sch->schedule_id;

                            // Fetch machine name for schedule issue
                            $this->db->select('tbl_machine_master.machine_name');
                            $this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');
                            $this->db->where('tbl_production_schedules.id', $sch->schedule_id);
                            $sch_machine = $this->db->get('tbl_production_schedules')->row();
                            if (!empty($sch_machine->machine_name)) {
                                $member->machine_name = $sch_machine->machine_name;
                            }
                        }
                    }
                    
                    if ($type === 'color' && (!empty($member->master_bd_id) || !empty($member->master_batch_id)) && $created_on) {
                        $mb_id = !empty($member->master_bd_id) ? $member->master_bd_id : ($member->master_batch_id ?? 0);
                        
                        $this->db->select('request_no');
                        $this->db->where('master_batch_id', $mb_id);
                        $this->db->where('created_on', $created_on);
                        $rq = $this->db->get('tbl_received_rm_qty_data')->row();
                        $member->rm_request_no = $rq->request_no ?? null;

                        $this->db->select('schedule_id');
                        $this->db->where('item_table_id', $mb_id);
                        $this->db->where('item_type', '2');
                        $this->db->where('created_on', $created_on);
                        $sch = $this->db->get('tbl_production_schedules_rm_details')->row();
                        if (!empty($sch->schedule_id)) {
                            $member->rm_schedule_id = $sch->schedule_id;

                            $this->db->select('tbl_machine_master.machine_name');
                            $this->db->join('tbl_machine_master', 'tbl_machine_master.id = tbl_production_schedules.machine_id', 'left');
                            $this->db->where('tbl_production_schedules.id', $sch->schedule_id);
                            $sch_machine = $this->db->get('tbl_production_schedules')->row();
                            if (!empty($sch_machine->machine_name)) {
                                $member->machine_name = $sch_machine->machine_name;
                            }
                        }
                    }
                    
                    if ($flag === '3' || $flag === '4') {
                        $this->db->select('id, remark');
                        $this->db->where('plant_id', $member->plant_id);
                        $this->db->where('DATE(created_on)', date('Y-m-d', strtotime($created_on)));
                        
                        if ($type === 'raw_material' && !empty($member->raw_material_id)) {
                            $this->db->where('stock_adjusted_for', '1')->where('raw_material_id', $member->raw_material_id);
                        } elseif ($type === 'color' && (!empty($member->master_bd_id) || !empty($member->master_batch_id))) {
                            $mb_id = !empty($member->master_bd_id) ? $member->master_bd_id : ($member->master_batch_id ?? 0);
                            $this->db->where('stock_adjusted_for', '2');
                            $this->db->where("TRIM(`master_batch_id`) = '$mb_id'", null, false);
                        } elseif (($type === 'article' || $type === 'mould') && !empty($member->article_id)) {
                            $this->db->where('stock_adjusted_for', '3')->where('article_id', $member->article_id);
                        }
                        
                        $adj_row = $this->db->order_by('id', 'DESC')->limit(1)->get('tbl_stock_adjustment')->row();
                        if (!empty($adj_row)) {
                            $member->adj_remark = !empty($adj_row->remark) ? $adj_row->remark : 'Manual Adjustment';
                            $member->adj_id = $adj_row->id ?? '';
                        }
                    }

                    // Voucher type label
                    $vch_map = [
                        '0' => 'Inward (Supplier)', '1' => 'Inward (Supplier)',
                        '2' => 'Store Issue', '3' => 'Stock Adj. +',
                        '4' => 'Stock Adj. -', '5' => 'Production',
                        '6' => 'Return to Store', '7' => 'Stock Transfer IN',
                    ];
                    $vch_type = $is_article_production_row ? 'Production' : ($vch_map[$flag] ?? 'Other');
                    if ($type === 'article' || $type === 'mould') {
                        if ($flag === '2') {
                            // If a dispatch record was found in tbl_dispatch_order_data → it's a customer Dispatch
                            // If no dispatch record was found → it's a plant-to-plant Store Issue (internal transfer)
                            if (!empty($member->dispatch_order_id) && $member->dispatch_order_id !== 'TransferOut') {
                                $vch_type = 'Dispatch';
                            } else {
                                $vch_type = 'Store Issue';
                            }
                        } elseif ($flag === '0' || $flag === '1') {
                            // Check if this is a plant-to-plant transfer inward or a real supplier inward
                            if (!empty($member->order_id_fallback) && $member->order_id_fallback === 'TransferIn') {
                                $vch_type = 'Store Inward (Transfer)';
                            } else {
                                $vch_type = 'Inward / Stock Transfer';
                            }
                        }
                    }

                    // Particulars
                    $particulars = $member->rm_name ?? $member->mb_name ?? $member->article_name ?? 'N/A';
                    if ($type === 'color') {
                        $particulars = $member->mb_name ?? 'N/A';
                    }

                    // Reference - for articles, show machine name for production records
                    $ref = $member->inward_no ?? $member->machine_name ?? null;
                    if ($flag === '2' && !empty($member->dispatch_party_name)) {
                        $ref = $member->dispatch_party_name;
                    } elseif (!empty($member->supplier_party_name)) {
                        $ref = $member->supplier_party_name;
                    }
                    if (!$ref && !empty($member->receiver_plant_name)) {
                        $ref = 'To: ' . $member->receiver_plant_name;
                    }
                    if (($flag === '3' || $flag === '4') && !empty($member->adj_remark)) {
                        $ref = $member->adj_remark;
                    }
                    if (!$ref) {
                        $ref = '-';
                    }

                    if ($flag === '5' && !empty($member->production_id)) {
                        $final_order_id = 'PROD-' . $member->production_id;
                    } elseif (!empty($member->rm_schedule_id)) {
                        $final_order_id = 'SCH-' . $member->rm_schedule_id;
                    } elseif (($flag === '3' || $flag === '4') && !empty($member->adj_id)) {
                        $final_order_id = 'ADJ-' . $member->adj_id;
                    } else {
                        $final_order_id = $member->rm_request_no ?? $member->dispatch_order_id ?? $member->order_no ?? $member->order_id_fallback ?? $member->order_id ?? $member->inward_no ?? '-';
                    }

                    $raw_list[] = [
                        'id'             => $member->id ?? 0,
                        'date'           => $member->date ?? null,
                        'particulars'    => $particulars,
                        'vch_type'       => $vch_type,
                        'reference_no'   => $ref ?? '-',
                        'order_id'       => $final_order_id,
                        'inward'         => $inward_qty,
                        'outward'        => $outward_qty,
                        'stored_balance' => floatval($member->total_quantity ?? 0),
                        'plant_name'     => $member->plant_name ?? '-',
                    ];
                }

                // If article or mould, calculate full history dynamic balances
                $full_history_balances = [];
                if ($type === 'article' || $type === 'mould') {
                    $this->db->select('tbl_raw_material_stock_report_history.id, tbl_raw_material_stock_report_history.plant_id, tbl_raw_material_stock_report_history.article_id, tbl_raw_material_stock_report_history.is_inward_outward, tbl_raw_material_stock_report_history.inward_qty, tbl_raw_material_stock_report_history.adjusted_qty, tbl_raw_material_stock_report_history.return_stock_qty, tbl_raw_material_stock_report_history.approved_qty, tbl_raw_material_stock_report_history.outward_qty, tbl_raw_material_stock_report_history.opening_stock');
                    $this->db->from('tbl_raw_material_stock_report_history');
                    $this->db->where('tbl_raw_material_stock_report_history.is_deleted', '0');
                    if ($type === 'article' && $this->input->post('article_id') != '') {
                        $this->db->where('tbl_raw_material_stock_report_history.article_id', $this->input->post('article_id'));
                    } else if ($type === 'mould') {
                        if ($this->input->post('article_id') != '') {
                            $this->db->where('tbl_raw_material_stock_report_history.article_id', $this->input->post('article_id'));
                        } else if ($this->input->post('mould_id') != '') {
                            $this->db->join('tbl_mould_parts', 'tbl_mould_parts.id = tbl_raw_material_stock_report_history.article_id', 'left');
                            $this->db->where('tbl_mould_parts.type_of_mould_id', $this->input->post('mould_id'));
                        }
                    }
                    if ($this->input->post('plant_id') != "") {
                        $this->db->where('tbl_raw_material_stock_report_history.plant_id', $this->input->post('plant_id'));
                    }
                    $this->db->order_by('tbl_raw_material_stock_report_history.date', 'ASC');
                    $this->db->order_by('tbl_raw_material_stock_report_history.id', 'ASC');
                    $full_history = $this->db->get()->result();

                    foreach ($production_rows as $production_row) {
                        $full_history[] = $production_row;
                    }

                    usort($full_history, function ($a, $b) {
                        $a_time = strtotime($a->date ?? $a->created_on ?? '1970-01-01 00:00:00') ?: 0;
                        $b_time = strtotime($b->date ?? $b->created_on ?? '1970-01-01 00:00:00') ?: 0;
                        if ($a_time === $b_time) {
                            return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
                        }
                        return $a_time <=> $b_time;
                    });
                    
                    $running_bal = [];
                    foreach ($full_history as $hist) {
                        $key = $hist->article_id . '_' . $hist->plant_id;
                        if (!isset($running_bal[$key])) {
                            $running_bal[$key] = floatval($hist->opening_stock ?? 0);
                        }
                        
                        $flag = $hist->is_inward_outward ?? '0';
                                                $production_inward_qty = floatval($hist->approved_qty ?? 0);
                                                $is_article_production_row = ($type === 'article' || $type === 'mould') && $production_inward_qty > 0;
                        $in = 0;
                                                if ($is_article_production_row || in_array($flag, ['0', '1', '3', '5', '6', '7'])) {
                                                    if ($is_article_production_row || $flag === '5') {
                                                        $in = $production_inward_qty;
                            } elseif ($flag === '3') {
                                $in = floatval($hist->adjusted_qty ?? 0);
                            } elseif ($flag === '6') {
                                $in = floatval($hist->return_stock_qty ?? 0);
                            } else {
                                $in = floatval($hist->inward_qty ?? 0);
                            }
                        }
                        $out = 0;
                        if (in_array($flag, ['2', '4'])) {
                            if ($flag === '4') {
                                $out = floatval($hist->adjusted_qty ?? 0);
                            } else {
                                if ($type === 'article' && $flag === '2' && !empty($hist->created_on)) {
                                    $this->db->select('dispatch_quantity');
                                    $this->db->where('article_id', $hist->article_id);
                                    $this->db->where('created_on', $hist->created_on);
                                    $dp_hist = $this->db->get('tbl_dispatch_order_data')->row();
                                    if (!empty($dp_hist->dispatch_quantity)) {
                                        $out = floatval($dp_hist->dispatch_quantity);
                                    } else {
                                        $out = floatval($hist->outward_qty ?? 0);
                                    }
                                } else {
                                    $out = floatval($hist->outward_qty ?? 0);
                                }
                            }
                        }
                        $running_bal[$key] = $running_bal[$key] + $in - $out;
                        $full_history_balances[$hist->id] = $running_bal[$key];
                    }
                }

                // Pass 2: Output rows using dynamic or stored balance
                for ($i = 0; $i < count($raw_list); $i++) {
                    $raw_date       = $raw_list[$i]['date'];
                    $formatted_date = (!empty($raw_date) && strtotime($raw_date) !== false)
                        ? date('d-m-Y', strtotime($raw_date)) : 'N/A';

                    if ($type === 'article' || $type === 'mould') {
                        $current_balance = isset($full_history_balances[$raw_list[$i]['id']]) ? $full_history_balances[$raw_list[$i]['id']] : 0;
                    } else {
                        $current_balance = $raw_list[$i]['stored_balance'];
                    }

                    $data[] = [
                        $offset++,
                        $formatted_date,
                        $raw_list[$i]['plant_name'],
                        $raw_list[$i]['vch_type'],
                        $raw_list[$i]['reference_no'],
                        $raw_list[$i]['order_id'],
                        $raw_list[$i]['particulars'],
                        round($raw_list[$i]['inward'], 3),
                        round($raw_list[$i]['outward'], 3),
                        round($current_balance, 3),
                    ];
                }
            }
            
            if ($type === 'article' || $type === 'mould') {
                // If it's article/mould, override closing_stock in summary with the true running balance of the newest record
                if (isset($data[0])) {
                    $summary['closing_stock'] = $data[0][8];
                    $summary['opening_stock'] = $summary['closing_stock'] - $summary['total_inward'] + $summary['total_outward'];
                }
            }
        }

        echo json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $response['total_count'],
            'recordsFiltered' => $response['total_count'],
            'data'            => $data,
            'summary_opening_stock' => round($summary['opening_stock'] ?? 0, 3),
            'summary_total_inward' => round($summary['total_inward'] ?? 0, 3),
            'summary_total_outward' => round($summary['total_outward'] ?? 0, 3),
            'summary_closing_stock' => round($summary['closing_stock'] ?? 0, 3),

        ]);
        exit();
    }




    public function get_all_article_report_one_plant_to_other_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $production_qty = '0';
        $response = $this->Admin_model->get_all_article_report_list($length, $start, $search, $production_qty);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                if ($member->is_inward_outward == '5') {
                    continue;
                }
                switch ($member->is_inward_outward) {
                    case '1':
                        $type = 'In';
                        $in_out_qty = $member->inward_qty;
                        break;
                    case '2':
                        $type = 'Out';
                        $in_out_qty = $member->outward_qty;
                        break;
                    case '3':
                        $type = 'Increasing Adjustment';
                        break;
                    default:
                        $type = 'Decreasing Adjustment';
                        break;
                }
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),

                    $member->plant_name,
                    $type,
                    $member->article_name,
                    $member->opening_stock ? $member->opening_stock : 0,
                    $in_out_qty,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_article_report_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $production_qty = '1';
        $response = $this->Admin_model->get_all_article_report_list($length, $start, $search, $production_qty);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                if ($member->is_inward_outward != '5') {
                    continue;
                }
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $opening_stock = floatval($member->opening_stock ?? 0);
                $approved_qty = floatval($member->approved_qty ?? 0);
                $total_qty = $opening_stock + $approved_qty;
                
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $member->article_name,
                    $opening_stock,
                    $approved_qty,
                    $total_qty,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_return_stock_raw_material_report_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_return_stock_raw_material_report_list($length, $start, $search);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $member->rm_name,
                    $member->uom_name,
                    number_format($member->opening_stock ?? 0, 3),
                    number_format($member->return_stock_qty ?? 0, 3),
                    number_format($member->total_quantity ?? 0, 3),
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }


    public function get_all_purchase_sales_report_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_purchase_sales_report_details($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = [
                    $offset++,
                    $member->date,
                    $member->supplier,
                    $member->supplier_address,
                    $member->consignee,
                    $member->plant_name,
                    $member->supplier_invoice_no,
                    $member->supplier_invoice_date,
                    $member->gstin_uin,
                    $member->pan_no,
                    $member->order_no_and_date,
                    $member->terms_of_payment,
                    $member->receipt_no_and_date,
                    $member->receipt_doc_lr_no,
                    $member->despatch_through,
                    $member->destination,
                    $member->article_name,
                    $member->rate,
                    $member->value,
                    $member->addl_cost,
                    $member->taxes_gst,
                    $member->gross_total,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_sales_report_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_sales_report_details($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = [
                    $offset++,
                    $member->date,
                    $member->buyer,
                    $member->buyer_address,
                    $member->consignee_address,
                    $member->voucher_type,
                    $member->voucher_no,
                    $member->voucher_ref_no,
                    $member->gstin_uin,
                    $member->pan_no,
                    $member->narration,
                    $member->order_no_and_date,
                    $member->terms_of_payment,
                    $member->other_references,
                    $member->terms_of_delivery,
                    $member->delivery_note_no_and_date,
                    $member->despatch_doc_no,
                    $member->despatch_through,
                    $member->destination,
                    $member->particulars,
                    $member->quantity,
                    $member->rate,
                    $member->value,
                    $member->gst,
                    $member->discounts,
                    $member->other_charges,
                    $member->gross_total,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }


    //------------------------------------hidden function-------------------------------------//

    public function get_sub_brand_type_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_brand_type_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->brand_type;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('brand_type_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_brand_type" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_brand_type_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_department_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_department_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->department;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('brand_department_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_department" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_department_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_party_designation_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_party_designation_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->designation;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('party_designation_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_designation" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_designation_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_party_nature_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_party_nature_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->nature_of_business;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('party_nature_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_nature_of_business" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_party_nature_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_party_type_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_party_type_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->type_of_business;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('party_type_businesss_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_type_of_business" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_party_type_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_group_of_article()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_group_of_article($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->group_of_article;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('group_of_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_group_of_article" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_group_of_article_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_alankey_bolt_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_alankey_bolt_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->alankey_bolt;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('alanky_bolt_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_alankey_bolt" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_alankey_bolt_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_type_of_mould_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_sub_type_of_mould_list($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->type_of_mould;
                $action_buttons = '
                    <td>
                        <a class="btn btn-success" href="' . base_url('type_of_mould_list/' . $member->id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-danger" " href="' . base_url() . 'delete/' . $member->id . '/tbl_type_of_mould" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_sub_type_of_mould_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_sub_air_pin_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_air_pin_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->air_pin;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('air_pin_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_air_pin" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_spring_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_spring_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->spring;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('spring_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_spring_master" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_pu_nipples_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_pu_nipples_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->pu_nipples;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('pu_nipple_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_pu_nipples_master" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_ejector_pin_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_ejector_pin_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->ejector_pin;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('ejector_pin_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_ejector_pin_master" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_i_bolt_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_i_bolt_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->i_bolt;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('i_bolt_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_i_bolt" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_cord_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_cord_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->cord;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('cord_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_cord" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_o_ring_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_o_ring_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->o_ring;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('o_ring_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_o_ring" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_insert_slot_plate_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_insert_slot_plate_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->insert_slot_plate;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('insert_slot_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_insert_slot_plate" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_core_cylender_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_core_cylender_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->core_cylinder_seal;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('core_cylender_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_core_Cylinder_seal" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_sub_seal_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_seal_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->seal;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('seal_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_seal" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_sub_hose_pipe_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_sub_hose_pipe_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->hose_pipe;
                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('hope_pipe_list/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <a class="btn btn-danger" href="' . base_url() . 'delete/' . $member->id . '/tbl_hose_pipe" title="Delete" onclick="return confirm(\'Are you sure you want to delete this item?\');">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }


    public function get_all_production_report_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        
        $search_array = $this->input->post("search");
        $search = (is_array($search_array) && isset($search_array['value'])) ? $search_array['value'] : '';
        
        $operator_id = $this->input->post('operator_id');
        $operator_name = trim((string)$this->input->post('operator_name'));
        
        $list_data = $this->Admin_model->get_all_production_report_list($length, $start, $search, $operator_id, $operator_name);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                // If the time is all zeros, just show the date. Otherwise, show date and time.
                $prod_date_time = strtotime($member->production_date);
                if (date('H:i:s', $prod_date_time) === '00:00:00') {
                    $sub_array[] = date('d-m-Y', $prod_date_time);
                } else {
                    $sub_array[] = date('d-m-Y H:i', $prod_date_time);
                }
                $sub_array[] = !empty($member->created_on) ? date('d-m-Y H:i:s', strtotime($member->created_on)) : '-';
                
                // Operator Column
                $day_ops_csv = isset($member->day_shift_operators) ? (string)$member->day_shift_operators : '';
                $night_ops_csv = isset($member->night_shift_operators) ? (string)$member->night_shift_operators : '';
                
                $day_count = count(array_filter(explode(',', $day_ops_csv)));
                $night_count = count(array_filter(explode(',', $night_ops_csv)));
                
                $labels = [];
                if($day_count > 0) $labels[] = '(D' . $day_count . ')';
                if($night_count > 0) $labels[] = '(N' . $night_count . ')';
                if(empty($labels)) $labels[] = '-';
                
                $op_html = '<div class="text-center">
                    <button type="button" class="btn btn-sm" onclick="show_operator_modal(' . $member->id . ')" title="Assign Operators">
                        <i class="fa fa-user-plus" style="color: #0056d0;"></i>
                    </button>
                    <div id="op_display_' . $member->id . '" style="font-size: 10px; color: #666;">' . implode(' ', $labels) . '</div>
                    <input type="hidden" id="day_operators_' . $member->id . '" value="' . $day_ops_csv . '">
                    <input type="hidden" id="night_operators_' . $member->id . '" value="' . $night_ops_csv . '">
                </div>';
                $sub_array[] = $op_html;

                $sub_array[] = $member->machine_name;
                $sub_array[] = $member->article_group;
                $sub_array[] = $member->article_names;
                $sub_array[] = $member->raw_material_names;
                $sub_array[] = $member->master_batch_names;
                $sub_array[] = $member->rejection_names;
                if ($member->image_count > 0) {
                    $sub_array[] = '<div class="text-center">
                    <button id="plus_icon" type="button" class="btn" onclick="get_all_production_images(' . $member->id . ')">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>';
                } else {
                    $sub_array[] = '<div class="text-center">
                    <button id="plus_icon" type="button" class="btn" onclick="get_all_production_images(' . $member->id . ')">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>';
                }
                if ($member->remark != '') {
                    $sub_array[] = '<div class="text-center">
                    <button type="button" class="btn" onclick="add_remark_modal(' . $member->id . ')">
                        ' . $member->remark . '<br>
                        <i class="fa fa-pen"></i>
                    </button>
                </div>';
                } else {
                    $sub_array[] = '<div class="text-center">
                    <button type="button" class="btn" onclick="add_remark_modal(' . $member->id . ')">
                        <i class="fa fa-pen"></i>
                    </button>
                </div>';
                }
                $action_buttons = '<div class="text-center">
                                <a class="icon_link" href="' . base_url('production_form_list/' . $member->id) . '"><i class="fa-solid fa-arrow-right"></i></a><br>
                                <a class="icon_link" href="' . base_url('add_production/' . $member->id) . '" title="Edit"><i class="fa-solid fa-arrow-left"></i></a>
                            </div>';

                $sub_array[] = $action_buttons;

                $data[] = $sub_array;
            }
        }

        $totalCount = $this->Admin_model->get_all_production_report_list_count($search, $operator_id, $operator_name);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_article_names()
    {
        $data = $this->Admin_model->get_production_article_data();
        if (!empty($data)) {
            $result['data'] = $data;
            $result['status'] = "success";
            $result['message'] = "Data Found";
        } else {
            $result['data'] = $data;
            $result['status'] = "failed";
            $result['message'] = "Data Not Found";
        }
        echo json_encode($result);
        exit;
    }
    public function get_all_article_images()
    {
        $id = $this->input->post('id');
        $data = $this->Admin_model->get_production_article_images_data($id);
        if (count($data)) {
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 'failed',
                'message' => 'No articles found'
            ]);
        }
        exit;
    }
    public function upload_images()
    {
        $production_report_id = $this->input->post('productionn_report_id');

        $upload_path = 'assets/images/production/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $uploaded_files = [];

        foreach ($_FILES as $input_name => $file_data) {
            if (!empty($file_data['name'][0])) {

                if (count($file_data['name']) > 2) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'You can upload a maximum of 2 images per request.'
                    ]);
                    exit;
                }

                foreach ($file_data['name'] as $index => $filename) {
                    $_FILES['file']['name'] = $filename;
                    $_FILES['file']['type'] = $file_data['type'][$index];
                    $_FILES['file']['tmp_name'] = $file_data['tmp_name'][$index];
                    $_FILES['file']['error'] = $file_data['error'][$index];
                    $_FILES['file']['size'] = $file_data['size'][$index];

                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf';
                    $config['max_size'] = 2048;
                    $config['file_name'] = 'production_' . rand(1000, 9999) . round(microtime(true) * 1000);

                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);

                    if ($this->upload->do_upload('file')) {
                        $id = end(explode('_', $input_name));
                        $uploaded_files = [
                            'production_id' => $production_report_id,
                            'article_id' => $id,
                            'image_names' => $this->upload->data('file_name'),
                        ];
                        $result = $this->Admin_model->set_uploded_production_images($uploaded_files);
                        $uploaded_files = [];
                    } else {
                        echo $this->upload->display_errors();
                        exit;
                    }
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Files uploaded successfully',
            'uploaded_files' => $uploaded_files
        ]);
        exit;
    }
    public function update_article_images()
    {
        $article_id = $this->input->post('article_id');
        $production_id = $this->input->post('production_id');

        if (empty($article_id) || empty($production_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Missing article_id or production_id'
            ]);
            return;
        }
        $upload_path = FCPATH . "assets/images/production/";
        if (!is_dir($upload_path))
            mkdir($upload_path, 0755, true);
        if (!is_writable($upload_path)) {
            echo json_encode(['status' => 'error', 'message' => 'Upload dir not writable']);
            return;
        }
        if (empty($_FILES['images']['name'][0])) {
            return;
        }
        $this->load->library('upload');
        $uploaded = [];
        foreach ($_FILES['images']['name'] as $i => $orig) {
            if (!$orig)
                continue;

            $_FILES['single'] = [
                'name' => $orig,
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];

            $this->upload->initialize([
                'upload_path' => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'file_name' => 'production_' . time() . '_' . rand(1000, 9999),
                'max_size' => 2048,
            ]);
            if (!$this->upload->do_upload('single')) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $this->upload->display_errors()
                ]);
                return;
            }
            $uploaded[] = $this->upload->data('file_name');
        }

        if (empty($uploaded)) {
            echo json_encode(['status' => 'error', 'message' => 'No images uploaded']);
            return;
        }


        $this->db
            ->where('production_id', $production_id)
            ->where('article_id', $article_id)
            ->delete('tbl_production_images');

        foreach ($uploaded as $fn) {
            $this->db->insert('tbl_production_images', [
                'production_id' => $production_id,
                'article_id' => $article_id,
                'image_names' => $fn
            ]);
        }

        echo json_encode([
            'status' => 'success',
            'message' => count($uploaded) . ' image(s) saved (replaced existing).'
        ]);
    }
    public function set_remark()
    {
        $result = $this->Admin_model->set_remark();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Remark updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Remark not updated!"]);
        }
        exit;
    }
    public function set_article_remark()
    {
        $result = $this->Admin_model->set_article_remark();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Remark updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Remark not updated!"]);
        }
        exit;
    }
    public function set_article_production_details()
    {
        $result = $this->Admin_model->set_article_production_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }
    public function set_raw_material_production_details()
    {
        $result = $this->Admin_model->set_raw_material_production_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }
    public function set_master_batch_production_details()
    {
        $result = $this->Admin_model->set_master_batch_production_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }
    public function set_rejection_production_details()
    {
        $result = $this->Admin_model->set_rejection_production_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }

    /**
     * Lightweight endpoint: update only total_qty (Converted Scrap kg) for a rejection row by its ID.
     * Used by the inline-edit input in the Production Report's Rejection & Scrap Log table.
     */
    public function update_rejection_scrap_qty()
    {
        $id        = (int) $this->input->post('id');
        $total_qty = $this->input->post('total_qty');

        if ($id <= 0 || $total_qty === false || $total_qty === '') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
            exit;
        }

        $this->db->where('id', $id);
        $this->db->update('tbl_rejection_article_list_production_details', [
            'total_qty'  => (float) $total_qty,
            'updated_on' => date('Y-m-d H:i:s'),
        ]);

        if ($this->db->affected_rows() >= 0) {
            echo json_encode(['status' => 'success', 'message' => 'Converted scrap updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
        exit;
    }

    public function get_article_production_summary_json()
    {
        $this->Admin_model->get_article_production_summary_json();
    }
    public function set_balance_quantity_production_details()
    {
        $result = $this->Admin_model->set_balance_quantity_production_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }

    public function set_all_article_summary_details()
    {
        $result = $this->Admin_model->set_all_article_summary_details();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }

    public function set_production_remarks()
    {
        $result = $this->Admin_model->set_production_remarks();
        if ($result == '1') {
            echo json_encode(["status" => "success", "message" => "Data updated successfully!"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data added successfully!"]);
        }
        exit;
    }
    public function get_all_maintenance_list_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_maintenance_list_details($length, $start, $search);
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {

                $status_of_work = '';
                $status_of_work = $member->status_of_work;
                switch ($member->id) {
                    case $status_of_work == '1':
                        $status_of_work = 'Completed';
                        break;
                    case $status_of_work == '2':
                        $status_of_work = 'Pending';
                        break;
                    case $status_of_work == '3':
                        $status_of_work = 'Reopen';
                        break;
                    case $status_of_work == '4':
                        $status_of_work = 'Out of scope';
                        break;
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->mwo_code;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $status_of_work;
                $sub_array[] = date('d-m-Y', strtotime($member->date));
                $sub_array[] = $member->first_name;
                $sub_array[] = $member->material_used_for_maintenance;
                $sub_array[] = $member->material_cost;
                $sub_array[] = $member->total_labour_hour_involved;
                $sub_array[] = $member->labour_cost_per_hour;
                $sub_array[] = $member->total_cost;

                if ($member->plant_manager_approval_status === "0") {
                    $sub_array[] = "Approved";
                } elseif ($member->plant_manager_approval_status === "1") {
                    $sub_array[] = "Disapprove";
                } else {
                    $sub_array[] = "Pending Approval";
                }

                $sub_array[] = $member->remark_of_plant_manager;
                // add log buttton
                $action_buttons = '<td class="text-center">
                                <button class="btn btn-secondary table_btn" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="view_history(' . "'" . $member->mwo_code . "'" . ')">Log</button>
                            </td>';
                $sub_array[] = $action_buttons;

                $data[] = $sub_array;
            }
        }

        $totalCount = $this->Admin_model->get_total_maintenance_list_details_count();
        $filteredRecords = $this->Admin_model->get_all_maintenance_list_details_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_complete_maintenance_list_details_ajax()
    {
        $mwo_code = $this->input->post('mwo_code');
        $data = $this->Admin_model->get_all_complete_maintenance_list_details($mwo_code);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $key => $item) {
                $result[] = array(
                    'sr_no' => $key + 1,
                    'mwo_code' => $item->mwo_code,
                    'plant_name' => $item->plant_name,
                    'status_of_work' => $this->get_status_text($item->status_of_work),
                    'date' => date('d-m-Y', strtotime($item->date)),
                    'first_name' => $item->first_name,
                    'material_used' => $item->material_used_for_maintenance,
                    'material_cost' => $item->material_cost,
                    'total_labour_hours' => $item->total_labour_hour_involved,
                    'labour_cost_per_hour' => $item->labour_cost_per_hour,
                    'total_cost' => $item->total_cost,
                    'approval_status' => ($item->plant_manager_approval_status === "0") ? "Approved" : (($item->plant_manager_approval_status === "1") ? "Disapprove" : ""),

                    'remark' => $item->remark_of_plant_manager
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }
    private function get_status_text($status)
    {
        $status_map = array(
            '1' => 'Completed',
            '2' => 'Pending',
            '3' => 'Reopen',
            '4' => 'Out of Scope'
        );
        return $status_map[$status] ?? 'Unknown';
    }
    public function get_all_article_production_details_logs_ajax()
    {
        $production_id = $this->input->post('production_id');
        $article_id = $this->input->post('article_id');
        $data = $this->Admin_model->get_all_article_production_details_logs($production_id, $article_id);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $key => $item) {
                $result[] = array(
                    'sr_no' => $key + 1,
                    // 'production_id' => $item->production_id,
                    'article_name' => $item->article_name,
                    'approved_qty' => $item->approved_qty,
                    'average_qty' => $item->average_qty,
                    // 'approved_weight' => $item-> ,
                    'qty_eight_nine' => $item->qty_eight_nine,
                    'qty_nine_ten' => $item->qty_nine_ten,
                    'qty_ten_eleven' => $item->qty_ten_eleven,
                    'qty_eleven_twelve' => $item->qty_eleven_twelve,
                    'qty_twelve_thirteen' => $item->qty_twelve_thirteen,
                    'qty_thirteen_fourteen' => $item->qty_thirteen_fourteen,
                    'qty_fourteen_fifteen' => $item->qty_fourteen_fifteen,
                    'qty_fifteen_sixteen' => $item->qty_fifteen_sixteen,
                    'qty_sixteen_seventeen' => $item->qty_sixteen_seventeen,
                    'qty_seventeen_eighteen' => $item->qty_seventeen_eighteen,
                    'qty_eighteen_nineteen' => $item->qty_eighteen_nineteen,
                    'qty_nineteen_twenty' => $item->qty_nineteen_twenty,
                    'qty_twenty_twentyone' => $item->qty_twenty_twentyone,
                    'qty_twentyone_twentytwo' => $item->qty_twentyone_twentytwo,
                    'qty_twentytwo_twentythree' => $item->qty_twentytwo_twentythree,
                    'qty_twentythree_zero' => $item->qty_twentythree_zero,
                    'qty_zero_one' => $item->qty_zero_one,
                    'qty_one_two' => $item->qty_one_two,
                    'qty_two_three' => $item->qty_two_three,
                    'qty_three_four' => $item->qty_three_four,
                    'qty_four_five' => $item->qty_four_five,
                    'qty_five_six' => $item->qty_five_six,
                    'qty_six_seven' => $item->qty_six_seven,
                    'qty_seven_eight' => $item->qty_seven_eight,
                    'weight_eight_nine' => $item->weight_eight_nine,
                    'weight_nine_ten' => $item->weight_nine_ten,
                    'weight_ten_eleven' => $item->weight_ten_eleven,
                    'weight_eleven_twelve' => $item->weight_eleven_twelve,
                    'weight_twelve_thirteen' => $item->weight_twelve_thirteen,
                    'weight_thirteen_fourteen' => $item->weight_thirteen_fourteen,
                    'weight_fourteen_fifteen' => $item->weight_fourteen_fifteen,
                    'weight_fifteen_sixteen' => $item->weight_fifteen_sixteen,
                    'weight_sixteen_seventeen' => $item->weight_sixteen_seventeen,
                    'weight_seventeen_eighteen' => $item->weight_seventeen_eighteen,
                    'weight_eighteen_nineteen' => $item->weight_eighteen_nineteen,
                    'weight_nineteen_twenty' => $item->weight_nineteen_twenty,
                    'weight_twenty_twentyone' => $item->weight_twenty_twentyone,
                    'weight_twentyone_twentytwo' => $item->weight_twentyone_twentytwo,
                    'weight_twentytwo_twentythree' => $item->weight_twentytwo_twentythree,
                    'weight_twentythree_zero' => $item->weight_twentythree_zero,
                    'weight_zero_one' => $item->weight_zero_one,
                    'weight_one_two' => $item->weight_one_two,
                    'weight_two_three' => $item->weight_two_three,
                    'weight_three_four' => $item->weight_three_four,
                    'weight_four_five' => $item->weight_four_five,
                    'weight_five_six' => $item->weight_five_six,
                    'weight_six_seven' => $item->weight_six_seven,
                    'weight_seven_eight' => $item->weight_seven_eight,
                    'remark' => $item->remark,
                    'status' => (!isset($item->status) || $item->status === '')
                        ? ''
                        : ($item->status === '0'
                            ? 'Approved'
                            : 'Disapprove'
                        ),
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }

    public function get_all_raw_material_production_details_logs_ajax()
    {
        $production_id = $this->input->post('production_id');
        $raw_material_id = $this->input->post('raw_material_id');
        $article_id = $this->input->post('article_id');
        $data = $this->Admin_model->get_all_raw_material_production_details_logs($production_id, $raw_material_id, $article_id);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $item) {
                $result[] = array(
                    'rm_name' => $item->rm_name,
                    'article_name' => $item->article_name,
                    'total_qty' => $item->total_qty,
                    'plant_manager_approval_status' => ($item->plant_manager_approval_status === "0") ? "Approved" : (($item->plant_manager_approval_status === "1") ? "Disapprove" : ""),
                    'remark' => $item->remark
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }
    public function get_all_master_batch_production_details_logs_ajax()
    {
        $production_id = $this->input->post('production_id');
        $master_batch_id = $this->input->post('master_batch_id');
        $article_id = $this->input->post('article_id');
        $data = $this->Admin_model->get_all_master_batch_production_details_logs_ajax($production_id, $master_batch_id, $article_id);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $item) {
                $result[] = array(
                    'name' => $item->name,
                    'article_name' => $item->article_name,
                    'total_qty' => $item->total_qty,
                    'plant_manager_approval_status' => ($item->plant_manager_approval_status === "0") ? "Approved" : (($item->plant_manager_approval_status === "1") ? "Disapprove" : ""),

                    'remark' => $item->remark
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }
    public function get_all_rejection_production_details_logs_ajax()
    {
        $production_id = $this->input->post('production_id');
        $rejection_id = $this->input->post('rejection_id');
        $data = $this->Admin_model->get_all_rejection_production_details_logs_ajax($production_id, $rejection_id);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $item) {
                $result[] = array(
                    'rm_name' => $item->rm_name,
                    'total_qty' => $item->total_qty,
                    'pc' => $item->pc,
                    'runner_gms' => $item->runner_gms,
                    'flash_gm' => $item->flash_gm,
                    'lumps_gm' => $item->lumps_gm,
                    'plant_manager_approval_status' => ($item->plant_manager_approval_status === "0") ? "Approved" : (($item->plant_manager_approval_status === "1") ? "Disapprove" : ""),
                    'remark' => $item->remark
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }
    public function get_all_balance_quantity_production_details_logs_ajax()
    {
        $production_id = $this->input->post('production_id');
        $master_batch_id = $this->input->post('master_batch_id');
        $raw_material_id = $this->input->post('raw_material_id');
        $data = $this->Admin_model->get_all_balance_quantity_production_details_logs_ajax($production_id, $master_batch_id, $raw_material_id);
        $result = array();
        if (!empty($data)) {
            foreach ($data as $item) {
                $result[] = array(
                    'rm_name' => !empty($item->rm_name) ? $item->rm_name : '-',
                    'rm_total_qty' => !empty($item->rm_total_qty) ? $item->rm_total_qty : '-',
                    'name' => !empty($item->name) ? $item->name : '-',
                    'mb_total_qty' => !empty($item->mb_total_qty) ? $item->mb_total_qty : '-',
                    'plant_manager_approval_status' => ($item->plant_manager_approval_status === "0") ? "Approved" : (($item->plant_manager_approval_status === "1") ? "Disapprove" : ""),

                    'remark' => isset($item->remark) ? $item->remark : '',
                );
            }
        }
        echo json_encode(array('data' => $result));
        exit();
    }

    public function get_date_statuses()
    {
        $start = $this->input->post('start');
        $end = $this->input->post('end');

        $this->db->select('DATE(start_date) as date, 
                         SUM(TIMESTAMPDIFF(SECOND, 
                             GREATEST(start_date, "' . $start . '"), 
                             LEAST(end_date, "' . $end . '")
                         )) as seconds');
        $this->db->where('start_date <', $end);
        $this->db->where('end_date >', $start);
        $result = $this->db->get('production_schedule')->result_array();

        $statusMap = [];
        foreach ($result as $row) {
            $hours = $row['seconds'] / 3600;
            $date = $row['date'];

            if ($hours >= 24) {
                $statusMap[$date] = 'full-plan';
            } elseif ($hours > 0) {
                $statusMap[$date] = 'partial-plan';
            } else {
                $statusMap[$date] = 'no-plan';
            }
        }

        echo json_encode($statusMap);
    }
    public function set_new_vehical()
    {
        $this->Admin_model->set_new_vehical();
    }
    public function get_all_vehical()
    {
        $result = $this->Admin_model->get_all_vehical();
        echo json_encode($result);
    }
    public function get_all_vehical_list_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_vehical_list_details($length, $start, $search);
        $data = array();
        $purposeMap = [
            '1' => 'Delivery',
            '2' => 'Pickup',
            '3' => 'Others',
        ];
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $purposeIds = explode(',', $member->purpose);
                $purposeNames = [];
                foreach ($purposeIds as $pid) {
                    if (isset($purposeMap[$pid])) {
                        $purposeNames[] = $purposeMap[$pid];
                    }
                }
                $purposeString = implode(', ', $purposeNames);

                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = !empty($member->vehical) ? $member->vehical : '-';
                $sub_array[] = !empty($member->challan_dc_no) ? $member->challan_dc_no : '0';
                $sub_array[] = !empty($member->invoice_no) ? $member->invoice_no : '0';
                $sub_array[] = number_format((float)$member->invoice_value, 2, '.', '');
                $sub_array[] = !empty($member->city) ? $member->city : '-';
                $sub_array[] = !empty($member->pincode) ? $member->pincode : '-';
                $sub_array[] = !empty($purposeString) ? $purposeString : '-';
                $sub_array[] = !empty($member->party_name) ? $member->party_name : '-';
                $in_val = (float)$member->in_km;
                
                // Use actual_out_km derived from previous trip if available, otherwise fallback to stored out_km
                $out_val = (float)($member->actual_out_km !== null ? $member->actual_out_km : $member->out_km);

                // Re-calculate Exact KM
                $calculated_exact_km = (float)$member->exact_km; 
                if ($calculated_exact_km == 0 && $in_val > 0 && $out_val > 0 && $in_val != $out_val) {
                    $calculated_exact_km = abs($in_val - $out_val);
                }

                $diesel_rate = (float)$member->diesel_rate;
                $calculated_diesel_expense = (float)$member->diesel_expense;
                if ($calculated_diesel_expense == 0 && $calculated_exact_km > 0 && $diesel_rate > 0) {
                    // Formula derived from Excel: Exact KM / 9 * Diesel Rate
                    $calculated_diesel_expense = ($calculated_exact_km / 9) * $diesel_rate;
                }

                $driver_exp = (float)$member->driver_expense;
                $maint_exp = (float)$member->maintenance;
                $invoice_val = (float)$member->invoice_value;
                $calculated_transport_percent = (float)$member->transport_percent;
                
                if ($calculated_transport_percent == 0 && $invoice_val > 0) {
                    // Formula derived from Excel JS: (Diesel Exp + Driver Exp) / Invoice Value * 100
                    $calculated_transport_percent = (($calculated_diesel_expense + $driver_exp) / $invoice_val) * 100;
                }

                $sub_array[] = (float)$member->in_km ?: '0';
                $sub_array[] = $out_val ?: '0'; // Replaces broken out_km display with correct historical one
                $sub_array[] = number_format($calculated_exact_km, 2, '.', '');
                $sub_array[] = number_format((float)$member->market_freight, 2, '.', '');
                $sub_array[] = (float)$member->diesel_topup ?: '0';
                $sub_array[] = number_format($diesel_rate, 2, '.', '');
                $sub_array[] = number_format($calculated_diesel_expense, 2, '.', '');
                $sub_array[] = number_format($driver_exp, 2, '.', '');

                $sub_array[] = number_format($maint_exp, 2, '.', '');
                $sub_array[] = number_format($calculated_transport_percent, 2, '.', '');

                $action_buttons = '
                <td>
                    <a class="btn btn-success" href="' . base_url('own_vehicle/' . $member->id) . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </td>';

                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }

        $totalCount = $this->Admin_model->get_total_vehical_list_details_count();
        $filteredRecords = $this->Admin_model->get_all_vehical_list_details_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $filteredRecords,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function set_production_operators()
    {
        $result = $this->Admin_model->set_production_operators();
        echo json_encode($result);
        exit;
    }
    public function get_production_images()
    {
        $production_id = $this->input->post('production_id');
        $response = ['status' => 'error', 'message' => 'No images found', 'data' => []];

        if (!empty($production_id)) {
            $images = [];

            // ── Source 1: web-uploaded images from tbl_production_form_image ──
            $this->db->select('image_names');
            $this->db->from('tbl_production_form_image');
            $this->db->where('production_id', $production_id);
            $this->db->where('is_deleted', '0');
            $this->db->where('status', '1');
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    $images[] = (object)['image_names' => $row->image_names, 'source' => 'web'];
                }
            }

            // ── Source 2: Android-uploaded images from tbl_production_report ──
            $this->db->select('production_images');
            $this->db->where('id', $production_id);
            $prod = $this->db->get('tbl_production_report')->row();

            if (!empty($prod) && !empty($prod->production_images)) {
                $android_paths = array_values(array_filter(explode(',', $prod->production_images)));
                foreach ($android_paths as $path) {
                    // path is stored as "assets/images/production/filename.jpg"
                    // extract just the filename to keep consistent with web images
                    $filename = basename(trim($path));
                    $images[] = (object)['image_names' => $filename, 'source' => 'android'];
                }
            }

            if (!empty($images)) {
                $response = [
                    'status'  => 'success',
                    'message' => 'Images retrieved successfully',
                    'data'    => $images,
                ];
            } else {
                log_message('error', "No images found for production_id: $production_id");
            }
        } else {
            log_message('error', 'No production_id provided in get_production_images');
        }

        echo json_encode($response);
    }
    public function get_articles_by_group()
    {
        $group_id = $this->input->post('group_id');
        $articles = $this->Admin_model->get_article_data($group_id);
        echo json_encode($articles);
    }
    public function delete_article_image()
    {

        $production_id = $this->input->post('production_id');
        $article_id = $this->input->post('article_id');
        $image_name = $this->input->post('image_name');

        if (empty($production_id) || empty($article_id) || empty($image_name)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Missing required parameters.'
                ]));
        }
        $this->db
            ->where('production_id', $production_id)
            ->where('article_id', $article_id)
            ->where('image_names', $image_name)
            ->delete('tbl_production_images');

        if ($this->db->affected_rows() === 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Image record not found.'
                ]));
        }


        $filePath = FCPATH . 'assets/images/production/' . $image_name;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }


        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Image deleted successfully.'
            ]));
    }
    public function get_unique_name_ajax()
    {
        $this->Admin_model->get_unique_name_ajax();
    }

    public function get_all_outward_order_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_outward_order_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                $parseDateValue = function ($value) {
                    $value = trim((string)$value);
                    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
                        return null;
                    }

                    $formats = ['Y-m-d H:i:s', 'Y-m-d', 'd-m-Y H:i:s', 'd-m-Y'];
                    foreach ($formats as $format) {
                        $dt = DateTime::createFromFormat($format, $value);
                        if ($dt instanceof DateTime) {
                            return $dt;
                        }
                    }

                    $timestamp = strtotime($value);
                    return ($timestamp !== false) ? (new DateTime())->setTimestamp($timestamp) : null;
                };

                // Check tbl_order_sub_details using the SAME filter as the modal popup
                // Modal uses order_status IN (1,3,4,9) — we must match that filter exactly
                $relevant_total = (int) $this->db
                    ->where('order_id', $member->task_id)
                    ->where('is_deleted', '0')
                    ->where_in('order_status', ['0', '1', '3', '4', '9'])
                    ->count_all_results('tbl_order_sub_details');

                if ($relevant_total > 0) {
                    // Count how many of those relevant items are Fully Dispatched (status=4)
                    $fully_dispatched_count = (int) $this->db
                        ->where('order_id', $member->task_id)
                        ->where('is_deleted', '0')
                        ->where('order_status', '4')
                        ->count_all_results('tbl_order_sub_details');

                    $partially_dispatched_count = (int) $this->db
                        ->where('order_id', $member->task_id)
                        ->where('is_deleted', '0')
                        ->where('order_status', '3')
                        ->count_all_results('tbl_order_sub_details');

                    if ($fully_dispatched_count === $relevant_total) {
                        // All items shown in the modal are Fully Dispatched
                        $order_ststus = 'Fully Dispatched';
                    } elseif ($fully_dispatched_count > 0 || $partially_dispatched_count > 0) {
                        $order_ststus = 'Partially Dispatched';
                    } else {
                        $check_order_in_table = $this->db->select('order_id')->from('tbl_outward_orders')
                            ->where('order_id', $member->task_id)->where('is_deleted', '0')->get()->row();
                        $order_ststus = empty($check_order_in_table) ? 'Pending Dispatch' : 'Partially Dispatched';
                    }
                } else {
                    // No sub-items in tbl_order_sub_details — check container details
                    $container_total = (int) $this->db
                        ->where('order_id', $member->task_id)
                        ->where('is_deleted', '0')
                        ->where_in('order_status', ['0', '1', '3', '4', '9'])
                        ->count_all_results('tbl_order_container_details');

                    $container_dispatched = ($container_total > 0) ? (int) $this->db
                        ->where('order_id', $member->task_id)
                        ->where('is_deleted', '0')
                        ->where('order_status', '4')
                        ->count_all_results('tbl_order_container_details') : 0;

                    $container_partially = ($container_total > 0) ? (int) $this->db
                        ->where('order_id', $member->task_id)
                        ->where('is_deleted', '0')
                        ->where('order_status', '3')
                        ->count_all_results('tbl_order_container_details') : 0;

                    if ($container_total > 0 && $container_dispatched === $container_total) {
                        $order_ststus = 'Fully Dispatched';
                    } elseif ($container_dispatched > 0 || $container_partially > 0) {
                        $order_ststus = 'Partially Dispatched';
                    } else {
                        $check_order_in_table = $this->db->select('order_id')->from('tbl_outward_orders')
                            ->where('order_id', $member->task_id)->where('is_deleted', '0')->get()->row();
                        $order_ststus = empty($check_order_in_table) ? 'Pending Dispatch' : 'Partially Dispatched';
                    }
                }

                $order_date_obj = $parseDateValue($member->date ?? '');
                $forwarded_date_val = (!empty($member->last_updated_date) && $member->last_updated_date != '0000-00-00') ? $member->last_updated_date : ($member->created_on ?? '');
                $forwarded_date_obj = $parseDateValue($forwarded_date_val);

                $delay_days = '-';
                if ($order_date_obj instanceof DateTime && $forwarded_date_obj instanceof DateTime) {
                    $delay_days = (string)$order_date_obj->diff($forwarded_date_obj)->days;
                } elseif ($order_date_obj instanceof DateTime) {
                    $delay_end_obj = new DateTime();
                    if ($this->input->post('final_status') == "1") {
                        $dispatch_date_obj = $parseDateValue($member->updated_on ?? '');
                        if ($dispatch_date_obj instanceof DateTime) {
                            $delay_end_obj = $dispatch_date_obj;
                        }
                    }
                    $delay_days = (string)$order_date_obj->diff($delay_end_obj)->days;
                }

                $order_date_display = ($order_date_obj instanceof DateTime) ? $order_date_obj->format('d-m-Y') : '-';
                $forwarded_date_display = ($forwarded_date_obj instanceof DateTime) ? $forwarded_date_obj->format('d-m-Y') : '-';

                if ($this->input->post('final_status') == "1") {
                    $order_ststus = 'Full Dispatched';
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->task_id;
                $sub_array[] = $member->party_name;
                $sub_array[] = $member->employee_name;
                $sub_array[] = $order_date_display;
                $sub_array[] = $forwarded_date_display;
                $sub_array[] = $delay_days;
                $sub_array[] = !empty($member->transport_name) ? $member->transport_name : '-';
                $sub_array[] = !empty($member->total_bundle) ? $member->total_bundle : '0';
                $sub_array[] = $member->type_of_order;
                $sub_array[] = $order_ststus;
                
                $remark = !empty($member->remark) ? htmlspecialchars($member->remark) : '';
                $sub_array[] = '<input type="text" class="form-control update-task-remark" data-id="' . $member->id . '" value="' . $remark . '" placeholder="Enter remark" style="min-width: 150px;">';

                $action_buttons = '';

                if ($order_ststus != 'Full Dispatched') {
                    $action_buttons .= '
                        <a class="btn btn-success" href="' . base_url('outward_transport/' . $member->id . '/' . $member->type_of_order . '/' . $member->task_id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a class="btn btn-secondary" href="' . base_url('outward_transport_history/' . $member->id . '/' . $member->task_id) . '">
                            <i class="fa-solid fa-clock"></i> Log
                        </a><br>';
                } else {
                    $action_buttons .= '
                       <a class="btn btn-secondary" href="' . base_url('outward_transport_history/' . $member->id . '/' . $member->task_id) . '">
                            <i class="fa-solid fa-clock"></i> Log
                        </a><br>';
                }
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function update_task_remark()
    {
        $id = $this->input->post('id');
        $remark = $this->input->post('remark');
        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('tbl_auto_task_list', array('remark' => $remark));
        }
        echo json_encode(array('status' => 'success'));
        exit();
    }
    public function test_db_query() {
        $result = $this->db->query("SELECT id, task_id, date, last_updated_date, created_on, department_id, order_department_status FROM tbl_auto_task_list WHERE task_id = 'ORD-2473'")->result_array();
        echo "<pre>"; print_r($result); echo "</pre>";
        exit();
    }
    public function get_outward_order_log_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_outward_order_log_details($length, $start, $search);
        //echo"<pre>";print_r($response);exit;
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $order_ststus = $this->Admin_model->get_outward_order_status($member->task_id);

                $division = [
                    '1' => 'Household',
                    '2' => 'Container',
                ];
                $division = $division[$member->division] ?? 'Both';
                $freight_status = [
                    '1' => 'Pay',
                ];
                $freight_status = $freight_status[$member->freight_status] ?? 'Paid';
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->order_id;
                $sub_array[] = $datetime->format('d M, Y h:i A');
                $sub_array[] = $member->party_name;
                $sub_array[] = $division;
                $sub_array[] = $member->dc_no;
                $sub_array[] = $member->invoice_no;
                $sub_array[] = $member->invoice_value;
                $sub_array[] = $member->freight_amount;
                $sub_array[] = $member->city;
                $sub_array[] = $member->pincode;
                $sub_array[] = $member->transport_name;
                $sub_array[] = $member->id;
                $sub_array[] = !empty($member->total_bundle) ? $member->total_bundle : '0';
                $sub_array[] = $member->vehicle;
                $sub_array[] = $member->vehicle_no;
                $sub_array[] = $member->driver_name;
                $sub_array[] = $member->driver_mobile;
                $sub_array[] = $freight_status;
                $sub_array[] = $member->remark;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_printing_order_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_printing_order_list($length, $start, $search);
        //echo"<pre>";print_r($response);exit;
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $order_status = '';
                switch ($member->order_status) {
                    case '0':
                        $order_status = 'Pending';
                        break;
                    case '1':
                        $order_status = 'Printing Completed';
                        break;
                    case '5':
                        $order_status = 'Pending';
                        break;
                    case '7':
                        $order_status = 'Printing Inprocess';
                        break;
                    default:
                        $order_status = 'Cancelled';
                        break;
                }
                $order_id = $member->order_id;

                $main_order = $this->Admin_model->get_order_details_according_order_id($order_id);
                $ink = $this->Admin_model->get_inks_according_brand($member->brand_type_id);
                if ($order_status == 'Pending' || $order_status == 'Printing Inprocess') {
                    $current_date = date('Y-m-d');
                } else {
                    $current_date = date('Y-m-d', strtotime($member->updated_on));
                }

                $delay_days = (strtotime($current_date) - strtotime($main_order->last_updated_date)) / (60 * 60 * 24);
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->order_id;
                $sub_array[] = date('d-m-Y', strtotime($main_order->date));
                $sub_array[] = date('d-m-Y', strtotime($main_order->last_updated_date));
                $sub_array[] = $main_order->party_name;
                $sub_array[] = $member->article_name;
                $sub_array[] = $member->brand_name;
                $sub_array[] = $ink->ink_names;
                $sub_array[] = $member->order_quantity;

                // remark: entered at order creation time (from tbl_order_sub_details)
                $sub_array[] = !empty($member->remark) ? $member->remark : '';

                // Last remark: latest details_of_task from task history for this order
                $last_remark_row = $this->db
                    ->select('details_of_task')
                    ->from('tbl_auto_task_list_history')
                    ->where('task_id', $main_order->id)
                    ->where('is_deleted', '0')
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get()->row();
                $sub_array[] = (!empty($last_remark_row) && !empty($last_remark_row->details_of_task))
                    ? $last_remark_row->details_of_task
                    : '';

                $sub_array[] = $order_status;
                $sub_array[] = (int) $delay_days;

                $action_buttons = '';

                if ($member->order_status == '0' || $member->order_status == '5' || $member->order_status == '7') {
                    $action_buttons .= '
                        <a class="btn btn-success" href="' . base_url('printing_unit_report/' . $member->id . '/' . $main_order->party_id . '/' . $member->order_id . '/' . $member->brand_type_id) . '" title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>';
                } else {
                    $action_buttons .= '
                        <a class="btn btn-primary" href="#" onclick="alert(\'This Order Already Printed.\'); return false;">
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>';
                }
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        // echo"<pre>";print_r($data);exit;
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_salesman_on_fields_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_salesman_on_fields_details($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $source_of_visit = '';
                if ($member->source_of_visit == '1') {
                    $source_of_visit = 'Cold all- Introduction to our offerings';
                } else if ($member->source_of_visit == '2') {
                    $source_of_visit = 'Planned Relationship Meet';
                } else if ($member->source_of_visit == '3') {
                    $source_of_visit = 'Order/ Payment Follow Up';
                } else if ($member->source_of_visit == '4') {
                    $source_of_visit = 'Complaint Visit';
                } else if ($member->source_of_visit == '5') {
                    $source_of_visit = 'Other: Marketing or Greetings  Visit';
                }
                $status_of_visit = '';
                if ($member->status_of_visit == '1') {
                    $status_of_visit = 'Meering Done with known follow up date, time & Reason';
                } else if ($member->status_of_visit == '2') {
                    $status_of_visit = 'Meeting Done with order confirmation';
                } else if ($member->status_of_visit == '3') {
                    $status_of_visit = 'Meeting Done with quotation requested by customer';
                } else {
                    $status_of_visit = ' Visit not completed yet';
                }

                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->visit_request_id;
                $sub_array[] = date('d-m-Y', strtotime($member->date)) . ' ' . date('h:i A', strtotime($member->time));
                $sub_array[] = $member->salesman_name;
                $sub_array[] = $member->party_name;
                $sub_array[] = $member->mobile;
                $sub_array[] = $member->city;
                $sub_array[] = $member->pincode;
                $sub_array[] = $member->state_name;
                $sub_array[] = $source_of_visit;
                if (!empty($member->meeting_proof)) {
                    $img_url = base_url($member->meeting_proof);
                    $sub_array[] = '<img src="' . $img_url . '" alt="Meeting Proof" 
                                    width="60" height="60" 
                                    style="object-fit:cover;border-radius:6px;cursor:pointer;" 
                                    class="meeting-proof-thumb" 
                                    data-img="' . $img_url . '">';
                } else {
                    $sub_array[] = '<span class="text-muted">No Proof</span>';
                }
                $sub_array[] = $member->latitude;
                $sub_array[] = $member->longitude;
                $sub_array[] = $member->address;
                $sub_array[] = $status_of_visit;

                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_ink_data_by_order_id()
    {
        $this->Admin_model->get_ink_data_by_order_id();
    }
    public function get_other_material_order_id()
    {
        $this->Admin_model->get_other_material_order_id();
    }
    public function get_all_printing_material_details()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $this->Admin_model->get_all_printing_material_details($length, $start, $search);
    }


    public function get_machine_production_month()
    {
        $this->Admin_model->get_machine_production_month();
    }
    public function get_prouction_planed_actual()
    {
        $this->Admin_model->get_prouction_planed_actual();
    }



    /////////////////////////////////Stock Management////////////////////////////
    public function get_all_inward_form_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $inward_for = $this->input->post('inward_for');
        $response = $this->Admin_model->get_all_inward_form_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->inward_no;
                $sub_array[] = $datetime->format('d M, Y h:i A');
                $sub_array[] = $member->party_name;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->gate_entry_no;
                $sub_array[] = !empty($member->gate_entry_date) ? date('d-m-Y', strtotime($member->gate_entry_date)) : '';
                $sub_array[] = $member->id;
                $action_buttons = '
                <td>
                    <a class="btn btn-primary btn-sm" href="' . base_url('view_inward_form/' . $member->id . '/' . $inward_for) . '" title="View">
                    View
                </a>
                </td>';
                $sub_array[] = $action_buttons;

                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_inward_form_data_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $list_data = $this->Admin_model->get_all_inward_form_data_list($length, $start, $search);
        $inward_for = $this->input->post('inward_for');
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $party_name = $this->Admin_model->get_inward_supplier_name($member->database_inward_id);
                if ($inward_for == '0') {
                    $material_name = $member->rm_name;
                } else {
                    $material_name = $member->mb_name;
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->inward_no;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $party_name;
                $sub_array[] = $material_name;
                if ($inward_for == '0') {
                    $sub_array[] = $member->uom_name;
                }
                $sub_array[] = $member->rate;
                $sub_array[] = $member->inward_quantity;
                if ($inward_for == '0') {
                    $action_buttons = '
                    <a href="' . base_url('rm_inward_list') . '" class="btn btn-primary" title="Back">
                    <i class="fa fa-long-arrow-left"></i>
                </a>';
                } else {
                    $action_buttons = '
                    <a href="' . base_url('mb_inward_list') . '" class="btn btn-primary" title="Back">
                    <i class="fa fa-long-arrow-left"></i>
                </a>';
                }

                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_inward_form_data_list_count($search);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_material_qty_request_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $according_plant = '0'; //dont apply filters according plant
        $response = $this->Admin_model->get_all_material_qty_request_list($length, $start, $search, $according_plant);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->request_no;
                $sub_array[] = $member->request_by;
                if ($member->is_article_or_rm_material == '0') {
                    $sub_array[] = 'Raw Material';
                } elseif ($member->is_article_or_rm_material == '1') {
                    $sub_array[] = 'Article';
                } else {
                    $sub_array[] = 'Master Batch (Color)';
                }
                $sub_array[] = $datetime->format('d M, Y h:i A');
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->my_plant_name;
                $sub_array[] = $member->request_status == '1' ? 'Pending' : ($member->request_status == '2' ? 'Completed' : ($member->request_status == '3' ? 'Partially Completed' : 'Cancelled'));
                $action_buttons = '
                <td>
                    <a class="btn btn-primary btn-sm" href="' . base_url('view_material_artical_requistition_from/' . $member->id . '/' . $member->is_article_or_rm_material) . '" title="View">View </a>
                </td>';

                $sub_array[] = $action_buttons;

                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_material_qty_request_one_plant_to_other_plant_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $according_plant = '1';//1 means apply filter for login user plant 
        $response = $this->Admin_model->get_all_material_qty_request_list($length, $start, $search, $according_plant);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->request_no;
                $sub_array[] = $member->request_by;
                if ($member->is_article_or_rm_material == '0') {
                    $sub_array[] = 'Raw Material';
                } elseif ($member->is_article_or_rm_material == '1') {
                    $sub_array[] = 'Article';
                } else {
                    $sub_array[] = 'Master Batch (Color)';
                }
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array[] = $datetime->format('d M, Y h:i A');
                $sub_array[] = $member->my_plant_name;
                $sub_array[] = $member->request_status == '1' ? 'Pending' : ($member->request_status == '2' ? 'Completed' : ($member->request_status == '3' ? 'Partially Completed' : 'Cancelled'));
                if ($member->is_article_or_rm_material == '1') {
                    $action_buttons = '
                <td>
                    <a class="btn btn-primary btn-sm" href="' . base_url('view__artical_requistition_to_list/' . $member->id) . '" title="View">View </a>
                </td>';
                } else if ($member->is_article_or_rm_material == '0') {
                    $action_buttons = '
                <td>
                    <a class="btn btn-primary btn-sm" href="' . base_url('view_material_artical_requistition_to_list/' . $member->id) . '" title="View">View </a>
                </td>';
                } else {
                    $action_buttons = '
                <td>
                    <a class="btn btn-primary btn-sm" href="' . base_url('view_mb_requestion_one_plant_to_other/' . $member->id) . '" title="View">View </a>
                </td>';
                }

                $sub_array[] = $action_buttons;

                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_all_material_qty_request_data_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $is_article_or_rm_material = $this->input->post('raw_material_or_article');
        $list_data = $this->Admin_model->get_all_material_qty_request_data_list();
        $data = array();
        if (!empty($list_data)) {
            $offset = $start + 1;
            foreach ($list_data as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->request_no;
                $sub_array[] = $member->approved_by_name;
                if ($is_article_or_rm_material == '0') {
                    $sub_array[] = $member->rm_name;
                    $sub_array[] = $member->uom_name;
                } else if ($is_article_or_rm_material == '1') {
                    $sub_array[] = $member->article_name;
                } else {
                    $sub_array[] = $member->mb_name;
                }

                $sub_array[] = $member->request_quantity;
                $sub_array[] = $member->received_qty;
                $sub_array[] = $member->request_status == '1' ? 'Pending' : ($member->request_status == '2' ? 'Completed' : ($member->request_status == '3' ? 'Partially Completed' : 'Cancelled'));
                $action_buttons = '
                    <a href="' . base_url('material_artical_requistition_from_list') . '" class="btn btn-primary" title="Back">
                    <i class="fa fa-long-arrow-left"></i>
                </a>';
                $sub_array[] = $action_buttons;
                $data[] = $sub_array;
            }
        }
        $totalCount = $this->Admin_model->get_all_material_qty_request_data_list_count();
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_raw_material_report_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_raw_material_report_list($length, $start, $search);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                switch ($member->is_inward_outward) {
                    case '1':
                        $type = 'In';
                        $in_out_qty = $member->inward_qty;
                        break;
                    case '2':
                        $type = 'Out';
                        $in_out_qty = $member->outward_qty;
                        break;
                    case '3':
                        $type = 'Increasing Adjustment';
                        break;
                    case '4':
                        $type = 'Decreasing Adjustment';
                        break;
                    case '6':
                        $type = 'In (Stock Return)';
                        $in_out_qty = $member->return_stock_qty;
                        break;
                    default:
                        $type = 'Decreasing Adjustment';
                        break;
                }
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $type,
                    $member->rm_name,
                    $member->uom_name,
                    number_format((float)($member->opening_stock ?? 0), 3, '.', ''),
                    number_format((float)($in_out_qty ?? 0), 3, '.', ''),
                    number_format((float)($member->total_quantity ?? 0), 3, '.', ''),
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_mb_stock_report_one_plant_to_other_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_mb_stock_report_one_plant_to_other_list($length, $start, $search);

        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                switch ($member->is_inward_outward) {
                    case '1':
                        $type = 'In';
                        $in_out_qty = $member->inward_qty;
                        break;
                    case '2':
                        $type = 'Out';
                        $in_out_qty = $member->outward_qty;
                        break;
                    case '3':
                        $type = 'Increasing Adjustment';
                        break;
                    case '4':
                        $type = 'Decreasing Adjustment';
                        break;
                    case '6':
                        $type = 'In (Stock Return)';
                        $in_out_qty = $member->return_stock_qty;
                        break;
                    default:
                        $type = 'Decreasing Adjustment';
                        break;
                }
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $type,
                    $member->mb_name,
                    number_format((float)($member->opening_stock ?? 0), 3, '.', ''),
                    number_format((float)($in_out_qty ?? 0), 3, '.', ''),
                    number_format((float)($member->total_quantity ?? 0), 3, '.', ''),
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }

    public function get_rm_stock_adjustment_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_stock_adjustment_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $member->adjustment_type == '1' ? 'Increasing' : 'Decreasing',
                    $member->rm_name,
                    $member->uom_name,
                    $member->opening_stock ? $member->opening_stock : 0,
                    $member->quantity,
                    $member->total_quantity,
                    $member->remark
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_total_rm_stock_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $stock_type = 'raw_material';
        $response = $this->Admin_model->get_total_stock_list($length, $start, $search, $stock_type);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->rm_name,
                    $member->uom_name,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_total_mb_stock_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $stock_type = 'master_batch';
        $response = $this->Admin_model->get_total_stock_list($length, $start, $search, $stock_type);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->mb_name,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_total_article_stock_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $stock_type = 'article';
        $response = $this->Admin_model->get_total_stock_list($length, $start, $search, $stock_type);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {

                $sub_array = [
                    $offset++,
                    $member->plant_name,
                    $member->article_name,
                    $member->total_quantity,
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_mb_stock_adjustment_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_stock_adjustment_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $member->adjustment_type == '1' ? 'Increasing' : 'Decreasing',
                    $member->mb_name,
                    $member->opening_stock ? $member->opening_stock : 0,
                    $member->quantity,
                    $member->total_quantity,
                    $member->remark
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_article_stock_adjustment_list()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_stock_adjustment_list($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $server_time = $member->created_on; // stored in server timezone
                $server_timezone = new DateTimeZone('Europe/Berlin'); // example server timezone
                $local_timezone = new DateTimeZone('Asia/Kolkata');

                $datetime = new DateTime($server_time, $server_timezone);
                $datetime->setTimezone($local_timezone);
                $sub_array = [
                    $offset++,
                    $datetime->format('d M, Y h:i A'),
                    $member->plant_name,
                    $member->adjustment_type == '1' ? 'Increasing' : 'Decreasing',
                    $member->article_name,
                    $member->opening_stock ? $member->opening_stock : 0,
                    $member->quantity,
                    $member->total_quantity,
                    $member->remark
                ];
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_raw_material_reorder_level()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_raw_material_reorder_level($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->rm_name;
                $sub_array[] = $member->uom_name;
                $sub_array[] = $member->rm_reorder_level;
                $sub_array[] = $member->total_quantity;
                $data[] = $sub_array;
            }
        }
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_all_article_reorder_level()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];
        $response = $this->Admin_model->get_all_article_reorder_level($length, $start, $search);
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                if ($member->total_quantity >= $member->reorder_level) {
                    continue; 
                }
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = $member->plant_name;
                $sub_array[] = $member->article_name;
                $sub_array[] = $member->article_reorder_level;
                $sub_array[] = max(0, $member->total_quantity);
                $data[] = $sub_array;
            }
        }
        $filteredCount = count($data);
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $filteredCount,
            "recordsFiltered" => $filteredCount,
            "data" => $data,
        );
        echo json_encode($output);
        exit();
    }
    public function get_required_stock_list_data()
    {
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        $search = $this->input->post("search")['value'];

        // Get filtered data from the model
        $response = $this->Admin_model->get_required_stock_list_data($length, $start, $search);

        $data = [];
        $filtered_count = 0;
        if (!empty($response['final_output'])) {
            $offset = $start + 1;
            foreach ($response['final_output'] as $row) {
                $raw_material_qty = isset($row['raw_material_one_qty']) ? ceil($row['raw_material_one_qty']) : 0;
                $stock_qty = isset($row['stock_qty']) ? $row['stock_qty'] : 0;
                $need_to_inward = $raw_material_qty - $stock_qty;

                if ($need_to_inward <= 0) {
                    continue;
                }

                $sub_array = [
                    $offset++,
                    $row['plant_name'] ?? '',
                    $row['rm_name'] ?? '',
                    $raw_material_qty,
                    $stock_qty,
                    ceil($need_to_inward),
                ];
                $data[] = $sub_array;
            }
        }

        $filtered_count = count($data);

        $output = [
            "draw" => $draw,
            "recordsTotal" => $filtered_count,
            "recordsFiltered" => $filtered_count,
            "data" => $data,
        ];

        echo json_encode($output);
        exit();
    }

    public function upload_images_updated()
    {

        $production_id = $this->input->post('production_id');
        $files = $_FILES['image_names'];

        if (empty($files['name'][0])) {
            return;
        }

        $config['upload_path'] = FCPATH . 'assets/images/production/';
        $config['allowed_types'] = '*';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        if (!is_writable($config['upload_path'])) {
            echo json_encode(['status' => 'error', 'message' => 'Upload path is not writable.']);
            return;
        }

        $this->load->library('upload');

        $uploaded_files = [];
        $errors = [];


        foreach ($files['name'] as $key => $name) {
            $_FILES['userfile']['name'] = $files['name'][$key];
            $_FILES['userfile']['type'] = $files['type'][$key];
            $_FILES['userfile']['tmp_name'] = $files['tmp_name'][$key];
            $_FILES['userfile']['error'] = $files['error'][$key];
            $_FILES['userfile']['size'] = $files['size'][$key];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('userfile')) {
                $upload_data = $this->upload->data();
                $uploaded_files[] = $upload_data['file_name'];
            } else {
                $errors[] = $this->upload->display_errors('', '');
            }
        }

        if (!empty($uploaded_files)) {
            foreach ($uploaded_files as $file_name) {
                $data = [
                    'production_id' => $production_id,
                    'image_names' => $file_name,
                    'created_on' => date('Y-m-d H:i:s'),
                    'updated_on' => date('Y-m-d H:i:s')
                ];
                $this->Admin_model->insert_image($data);
            }
        }


        if (empty($errors)) {
            echo json_encode(['status' => 'success', 'message' => 'Images uploaded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => implode(', ', $errors)]);
        }
    }

    public function get_store_dashboard_tables_data()
    {
        $this->Admin_model->get_store_dashboard_tables_data();
    }
    public function get_fast_and_over_moving_list()
    {
        $this->Admin_model->get_fast_and_over_moving_list();
    }
    public function get_monthly_purchase_data()
    {
        $this->Admin_model->get_monthly_purchase_data();
    }
    public function get_negative_stock_alert()
    {
        $this->Admin_model->get_negative_stock_alert();
    }
    public function get_negative_stock_alert_old()
    {
        $this->Admin_model->get_negative_stock_alert_old();
    }
    public function get_vendors_by_raw_material()
    {
        $this->Admin_model->get_vendors_by_raw_material();
    }
    public function get_all_production_schedules_for_store_dashboard()
    {
        $this->Admin_model->get_all_production_schedules_for_store_dashboard();
    }

    public function get_rm_item_stock_purchase_list()
    {
        $response = $this->Admin_model->get_rm_item_stock_purchase_list();
        // echo"<pre>"; print_r($response); echo"</pre>"; exit;
        $data = [];
        $count = max(count($response['top_items']), count($response['bottom_items']));

        for ($i = 0; $i < $count; $i++) {
            $top = $response['top_items'][$i] ?? ['rm_name' => '-', 'total_qty' => '-'];
            $bottom = $response['bottom_items'][$i] ?? ['rm_name' => '-', 'total_qty' => '-'];

            $data[] = [
                $top['rm_name'],
                $top['total_qty'],
                $bottom['rm_name'],
                $bottom['total_qty']
            ];
        }

        $output = [
            "data" => $data
        ];
        echo json_encode($output);
        exit();
    }


    public function get_machine_wise_requirement_store_dashboard()
    {
        $plant_id = $this->input->post('plant_id');
        $date = $this->input->post('date');
        $machine_id = $this->input->post('machine_id');

        $result = $this->Admin_model->get_machine_wise_requirement_store_dashboard($plant_id, $date, $machine_id);

        echo json_encode(['data' => $result]);
    }
  
    public function get_machine_request_log_store_dashboard()
{
    $draw = intval($this->input->post("draw"));
    $start = intval($this->input->post("start"));
    $length = intval($this->input->post("length"));
    $search = $this->input->post("search")['value'];

    $response = $this->Admin_model->get_machine_request_log_store_dashboard($length, $start, $search);
    $data = array();
    if (!empty($response['data'])) {
        $offset = $start + 1;
        foreach ($response['data'] as $member) {
            $data[] = array(
                'sr_no'            => $offset++,
                'request_no'       => $member->request_no,
                'machine_name'     => $member->machine_name,
                'plant_name'       => $member->plant_name,
                'request_date'     => date('d-m-Y', strtotime($member->request_date)),
                'rm_name'          => $member->rm_name,
                'request_quantity' => $member->request_quantity,
                'received_qty'     => $member->received_qty,
                
            );
        }
    }

    $filteredCount = count($data);
    $output = array(
        "draw" => $draw,
        "recordsTotal" => $filteredCount,
        "recordsFiltered" => $filteredCount,
        "data" => $data,
    );

    echo json_encode($output);
    exit();
}

public function get_transport_report_data()
{
    try {
        error_log('Transport Report AJAX Called');
        
        $draw = intval($this->input->post("draw"));
        $start = intval($this->input->post("start"));
        $length = intval($this->input->post("length"));
        
        // Safely get search value
        $search_obj = $this->input->post("search");
        $search = (is_array($search_obj) && isset($search_obj['value'])) ? $search_obj['value'] : '';
        
        error_log('Params - draw:' . $draw . ' start:' . $start . ' length:' . $length . ' search:' . $search);
        
        $response = $this->Admin_model->get_transport_report_data($length, $start, $search);
        
        error_log('Model returned - total_count:' . ($response['total_count'] ?? 0) . ' data_count:' . count($response['data'] ?? array()));
        
        $data = array();
        if (!empty($response['data'])) {
            $offset = $start + 1;
            foreach ($response['data'] as $member) {
                $sub_array = array();
                $sub_array[] = $offset++;
                $sub_array[] = !empty($member->created_on) ? date('d-m-Y', strtotime($member->created_on)) : '-';
                $sub_array[] = $member->order_id;
                $sub_array[] = $member->party_name;
                $sub_array[] = $member->location_name;
                $sub_array[] = $member->transport_name;
                
                $bundle_total = !empty($member->total_bundle) ? floatval($member->total_bundle) : 0;
                if ($bundle_total <= 0) {
                    $dispatch_items = $this->Admin_model->get_outward_dispatch_details_array($member->transport_id);
                    if (!empty($dispatch_items) && !isset($dispatch_items['error'])) {
                        foreach ($dispatch_items as $item) {
                            $bundle_total += floatval($item['bundle_bag_qty'] ?? 0);
                        }
                    }
                }
                $sub_array[] = number_format($bundle_total, 2);

                $sub_array[] = $member->vehicle;
                $sub_array[] = !empty($member->freight_amount) ? '₹' . number_format($member->freight_amount, 2) : '₹0.00';
                $sub_array[] = !empty($member->driver_mobile) ? $member->driver_mobile : '-';
                
                $action_buttons = '<a class="btn btn-sm btn-info" href="' . base_url('transport_report_view/' . $member->transport_id) . '" title="View Details">
                                    <i class="fa fa-eye"></i>
                                  </a>';
                $sub_array[] = $action_buttons;
                
                $data[] = $sub_array;
            }
        }
        
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $response['total_count'],
            "recordsFiltered" => $response['total_count'],
            "data" => $data,
        );
        
        error_log('Response - ' . json_encode($output));
        
        header('Content-Type: application/json');
        echo json_encode($output);
        exit();
    } catch (Exception $e) {
        error_log('Transport Report Error: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(array(
            "draw" => 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => array(),
            "error" => $e->getMessage()
        ));
        exit();
    }
}

public function get_transport_report_summary()
{
    try {
        $response = $this->Admin_model->get_transport_report_summary();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 'error',
            'message' => $e->getMessage()
        ));
        exit();
    }
}

    public function test_stock_output()
    {
        $this->db->select('*');
        $this->db->from('tbl_raw_material_stock_report_history');
        $this->db->where('is_inward_outward', '5');
        $this->db->where('article_id = (SELECT id FROM tbl_mould_parts WHERE article_name = "CONSTRO 17+" LIMIT 1)', null, false);
        $this->db->order_by('date', 'DESC');
        $this->db->limit(5);
        $res = $this->db->get()->result_array();
        echo "<pre>"; print_r($res); echo "</pre>";
        exit;
    }
}