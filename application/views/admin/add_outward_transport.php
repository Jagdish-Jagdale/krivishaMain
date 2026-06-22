<?php include('header.php') ?>

<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    .flex_wrap {
        display: flex;
        flex-wrap: wrap;
    }

    .select2-container {
        width: 100% !important;
    }


    .multi-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                Outward Transport Details
            </h3>

        </div>
    </div>
    <div class="clearfix"></div>

    <div class="row">
        <!-- First Panel: Form -->
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="outward_form" id="outward_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="order_id">Order ID<b class="require">*</b></label>
                                <input type="text" name="order_id" class="form-control" id="order_id"
                                    value="<?= $this->uri->segment(4) ?>" readonly>
                            </div>
                           <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="party">Party<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" disabled>
                                    <?php if (!empty($party_name)) {
                                        foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>" <?php if (!empty($order_details) && $order_details->party_id == $party_result->id) echo 'selected'; ?>>
                                                <?= $party_result->party_name ?>
                                            </option>
                                    <?php }} ?>
                                </select>
                                <input type="hidden" name="party" value="<?= isset($order_details) ? $order_details->party_id : '' ?>">
                            </div>

                           <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="division">Division<b class="require">*</b></label>
                                <?php
                                    $type_of_order = isset($order_details) ? $order_details->type_of_order : '';
                                    $division_label = '';
                                    if ($type_of_order == '2') {
                                         $division_label = 'Container';
                                    }else if ($type_of_order == '1')  {
                                        $division_label = 'Household';
                                        $type_of_order = '1';
                                    }else{
                                        $division_label = 'Both';
                                        $type_of_order = '3';
                                    }
                                ?>
                                <input type="text" class="form-control" value="<?= $division_label ?>" readonly>
                                <input type="hidden" name="division" value="<?= $type_of_order ?>">
                            </div>
                            <input type="hidden" id="id"  name="id"  value="<?php if (!empty($single)) { echo $single->id; } ?>">

                            <!-- Dispatching From Plant -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="dispatch_plant_id">Dispatching From Plant <b class="require">*</b></label>
                                <select name="dispatch_plant_id" id="dispatch_plant_id" class="form-control js-example-basic-multiple" required>
                                    <option value="">Select Plant</option>
                                    <?php
                                    $session_plant_id = $this->session->userdata('assign_plant_id');
                                    $saved_plant_id   = !empty($single) ? $single->plant_id : null;
                                    $selected_plant   = $saved_plant_id ?? $session_plant_id;
                                    if (!empty($plants)) {
                                        foreach ($plants as $plant) { ?>
                                            <option value="<?= $plant->id ?>" <?php if ($selected_plant == $plant->id) echo 'selected'; ?>>
                                                <?= $plant->plant_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <span id="dispatch_plant_id_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="dc_no">DC No<b class="require">*</b></label>
                                <input type="text" name="dc_no" class="form-control" id="dc_no" value="<?php if (!empty($single)) { echo $single->dc_no; } ?>"
                                    placeholder="Please enter dc no">
                                <span id="dc_no_error" class=""></span>
                            </div>
                           
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="invoice_no">Invoice No<b class="require">*</b></label>
                                <input type="text" name="invoice_no" class="form-control" id="invoice_no" value="<?php if (!empty($single)) { echo $single->invoice_no; } ?>"
                                    placeholder="Please enter invoice no">
                                <span id="invoice_no_error" class=""></span>
                            </div>
                            
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="invoice_value">Invoice Value<b class="require">*</b></label>
                                <input type="number" min = '0' name="invoice_value" class="form-control" id="invoice_value" value="<?php if (!empty($single)) { echo $single->invoice_value; } ?>"
                                    placeholder="Please enter invoice value">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="freight_amount">Freight Amount<b class="require">*</b></label>
                                <input type="number" min = '0' name="freight_amount" class="form-control" id="freight_amount" value="<?php if (!empty($single)) { echo $single->freight_amount; } ?>"
                                    placeholder="Please enter freight amount">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="location">Location<b class="require">*</b></label>
                                <select name="location_id" id="location_id" class="form-control js-example-basic-multiple">
                                    <option value="">Select location</option>
                                    <?php if (!empty($location)){
                                    foreach ($location as $result) { ?>
                                            <option value="<?= $result->id ?>"<?php if(!empty($single)&& $single->location_id==$result->id){?>selected<?php }?>><?= $result->city ?></option>
                                        <?php }} ?>
                                        
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="pincode">Pincode<b class="require">*</b></label>
                                <input type="number" name="pincode" class="form-control" id="pincode" value="<?php if (!empty($single)) { echo $single->pincode; } ?>"
                                    placeholder="Please enter pincode"readonly>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="transport">Transport<b class="require">*</b></label>
                                <select name="transport_id" id="transport_id" class="form-control js-example-basic-multiple">
                                    <option value="">Select transport</option>
                                    <?php if (!empty($transport)){
                                    foreach ($transport as $result) { ?>
                                            <option value="<?= $result->id ?>"<?php if(!empty($single)&& $single->transport_id==$result->id){?>selected<?php }?>><?= $result->transport_name ?></option>
                                        <?php }} ?>
                                </select>
                            </div>

                            <!-- Vehicle -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="vehicle">Vehicle Name<b class="require">*</b></label>
                                <input type="text" name="vehicle" class="form-control" id="vehicle" value="<?php if (!empty($single)) { echo $single->vehicle; } ?>"
                                    placeholder="Please enter vehicle name">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="vehicle_no">Vehicle No<b class="require">*</b></label>
                                <input type="text" name="vehicle_no" class="form-control" id="vehicle_no" value="<?php if (!empty($single)) { echo $single->vehicle_no; } ?>"
                                    placeholder="Please enter vehicle no">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="driver_name">Driver Name<b class="require">*</b></label>
                                <input type="text" name="driver_name" class="form-control" id="driver_name" value="<?php if (!empty($single)) { echo $single->driver_name; } ?>"
                                    placeholder="Please enter driver name">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="driver_mobile">Driver Mobile<b class="require">*</b></label>
                                <input type="text" name="driver_mobile" class="form-control" id="driver_mobile" value="<?php if (!empty($single)) { echo $single->driver_mobile; } ?>"
                                    placeholder="Please enter driver mobile">
                                
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="freight_status">Freight Payment Status<b class="require">*</b></label>
                                <select name="freight_status" id="freight_status" class="form-control">
                                    <option value="">Select status</option>
                                    <option value="1" <?php if(!empty($single)&& $single->freight_status==1){?>selected<?php }?>>Pay</option>
                                    <option value="2" <?php if(!empty($single)&& $single->freight_status==2){?>selected<?php }?>>Paid</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="remark">Remark</label>
                                <input type="text" name="remark" class="form-control" id="remark" value="<?php if (!empty($single)) { echo $single->remark; } ?>"
                                    placeholder="Please enter remark">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="remark">Final Order Remark</label>
                                <input type="text" name="final_order_remark" class="form-control" id="final_order_remark" value=""
                                    placeholder="Please enter final remark">
                            </div>
                            <?php $segment3 = $this->uri->segment(3); ?>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <table style="width:100%; margin-top:10px;" class="edit_list table table-striped table-bordered " id="example5">
                                    <thead class="thead">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Article</th>
                                        <?php if ($segment3 == 2): ?>
                                            <th>Brand Name</th>
                                        <?php endif; ?>
                                        <th>Order Quantity</th>
                                        <th>Bundle / Bag</th>
                                        <?php if ($segment3 == 2): ?>
                                            <th>Approved Quantity</th>
                                        <?php endif; ?>
                                        <th>Dispath Quantity</th>
                                        <th>Remaining  Quantity</th>
                                        <th>Total Dispatch Quantity</th>
                                        <th>Total Stock Quantity</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sub_order)) { 
                                                $i =0;
                                                foreach ($sub_order as $sub_order_item) { 
                                        ?>
                                            <tr data-row-id="<?= $i ?>" class="edit_list ">
                                                <td><?= $i + 1 ?></td>
                                                <td>
                            
                                                <select class="form-control" name="article[]" id="article_<?php echo $i; ?>" disabled>
                                                    <?php
                                                        $article_type = $this->Admin_model->get_article_type_group_releted($sub_order_item->group_of_article_id);
                                                        if (!empty($article_type)) {
                                                            foreach ($article_type as $article_result) { ?>
                                                                <option value="<?= $article_result->id ?>"
                                                                    <?php if (!empty($sub_order_item) && $sub_order_item->article_id == $article_result->id) { ?> selected <?php } ?>>
                                                                    <?= $article_result->article_name ?>
                                                                </option>
                                                            <?php }
                                                        }
                                                    ?>
                                                </select>
                                                <input type="hidden" name="article_ids[]" id="article_ids_<?php echo $i; ?>" value="<?= $sub_order_item->article_id ?>">
                                                <input type="hidden" name="sub_order_ids[]" id="sub_order_ids_<?php echo $i; ?>" value="<?= $sub_order_item->id ?>">
                                                </td>
                                                <?php if ($segment3 == 2): ?>
                                                <td>
                                                    <select class="form-control" name="brand[]" id="brand_<?php echo $i; ?>" disabled>
                                                        <?php
                                                            if (!empty($brand)) {
                                                                foreach ($brand as $brand_result) { ?>
                                                                    <option value="<?= $brand_result->id ?>"
                                                                        <?php if (!empty($sub_order_item) && $sub_order_item->brand_type_id == $brand_result->id) { ?> selected <?php } ?>>
                                                                        <?= $brand_result->brand_name ?>
                                                                    </option>
                                                                <?php }
                                                            }
                                                        ?>
                                                    </select>
                                                    <input type="hidden" name="brand_ids[]" id="brand_ids_<?php echo $i; ?>" value="<?= $sub_order_item->brand_type_id ?>">
                                                </td>
                                                <?php endif; ?>

                                                <?php
                                            
                                                $total_stock_qty = $this->db->select_sum('total_quantity')->where('article_id', $sub_order_item->article_id)
                                                        ->where('plant_id', $this->session->userdata('assign_plant_id'))->where('is_deleted', '0')->get('tbl_article_stock_report')->row();
                                                    
                                                $quantity_data = $this->Admin_model->get_dispatch_quantity($sub_order_item->article_id, $sub_order_item->order_id, $sub_order_item->brand_type_id);
                                                $total_dispatch_qty = (int)$quantity_data['total_dispatch_quantity'];
                                                if($total_dispatch_qty != 0 && $sub_order_item->approved_qty != 0){
                                                    $approved_qty = (int)$sub_order_item->approved_qty;
                                                    $total_remaining = $approved_qty - $total_dispatch_qty;
                                                    $check_remaining_qty = $total_remaining;
                                                }else if($total_dispatch_qty != 0){
                                                    $approved_qty = (int)$sub_order_item->order_quantity;
                                                    $total_remaining = $approved_qty - $total_dispatch_qty;
                                                    $check_remaining_qty = $total_remaining;
                                                }else if($total_dispatch_qty == 0 && $sub_order_item->approved_qty == 0){
                                                    $total_remaining = '0';
                                                    $check_remaining_qty = (int)$sub_order_item->order_quantity;
                                                }else{
                                                    $total_remaining = '0';
                                                    $check_remaining_qty = (int)$sub_order_item->approved_qty;
                                                }
                                                $is_readonly = ($total_remaining == 0 && $total_dispatch_qty != 0 || $total_stock_qty->total_quantity <= 0) ? 'readonly' : '';
                                                ?>
                                                <td>
                                                    <input type="number" name="quantity[]" class="form-control"
                                                        value="<?php echo $sub_order_item->order_quantity; ?>"
                                                        id="qty_<?php echo $i; ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="bundle_display[]" class="form-control"
                                                        value="<?php echo isset($sub_order_item->bundle_bag_qty) ? $sub_order_item->bundle_bag_qty : ''; ?>"
                                                        id="bundle_<?php echo $i; ?>" readonly>
                                                </td>
                                                <?php if ($segment3 == 2): ?>
                                                <td>
                                                    <input type="number" name="approved_quantity[]"  class="form-control"
                                                        value="<?php echo $sub_order_item->approved_qty; ?>"
                                                        id="approved_<?php echo $i; ?>" readonly>
                                                </td>
                                                <?php endif; ?>
                                                <td>
                                                    <input type="hidden" id="existing_remaining_<?php echo $i; ?>" value="<?= $check_remaining_qty ?>">
                                                    <input type="number" step="1" min="0" name="dispatch_quantity[]" class="form-control dispatch-qty"
                                                        id="dispatch_<?php echo $i; ?>" placeholder="Enter dispatch quantity"
                                                        value="" <?php echo $is_readonly; ?> oninput="updateRemainingQuantity(<?php echo $check_remaining_qty; ?>, <?php echo $i; ?>)">
                                                </td>
                                                <td>
                                                    <input type="number" name="remaining_quantity[]" class="form-control"
                                                        value="<?php echo $total_remaining ?>"
                                                        id="remaining_<?php echo $i; ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="hidden" id="existing_dispatch_<?php echo $i; ?>" value="<?= $total_dispatch_qty ?>">
                                                    <input type="number" name="total_dispatch_quantity[]" class="form-control"
                                                        value="<?php echo $total_dispatch_qty; ?>"
                                                        id="total_dispatch_quantity_<?php echo $i; ?>" readonly>
                                                </td>
                                                <td>
                                                    
                                                    <input type="number" name="total_stock_qty[]" class="form-control"
                                                        value="<?php echo $total_stock_qty->total_quantity ?? 0; ?>"
                                                        id="total_stock_qty_<?php echo $i; ?>" readonly>
                                                </td>
                                            </tr>
                                        <?php 
                                                $i++;
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" id="submit_btn" name="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php');
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dispatch-qty').forEach(function (input) {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, ''); 
            });
        });
    });
    const brand_details = '<?= $this->uri->segment(3) ?>';
    if (brand_details === '2') {
        $('.brand_details').removeClass('d-none');
       
    } else {
        $('.brand_details').addClass('d-none');
    }
    
    function updateRemainingQuantity(check_remaining_qty, row) {
        var dispatchQty = Number($('#dispatch_' + row).val()) || 0;
        var existing_remaining = Number($('#existing_remaining_' + row).val()) || 0;
        var total_dispatch = Number($('#existing_dispatch_' + row).val()) || 0;
        var newRemaining = existing_remaining - dispatchQty;
        var total_stock = Number($('#total_stock_qty_' + row).val()) || 0;
        if (dispatchQty > total_stock) {
            alert('Stock not available for this article.');
            $('#dispatch_' + row).val('');
            return;
        }
        if (dispatchQty > existing_remaining) {
            alert('Dispatch quantity cannot be more than remaining quantity ' + existing_remaining + ' for this order.');
            $('#dispatch_' + row).val('');
            return;
        }
        var newTotalDispatch = total_dispatch + dispatchQty;
        $('#remaining_' + row).val(newRemaining);
        $('#total_dispatch_quantity_' + row).val(newTotalDispatch);
    }


    $(document).ready(function () {
    //    $('#product_master .child_menu').show();
    //     $('#product_master').addClass('nv active');
    //     $('.right_col').addClass('active_right');
    //     $('.outward_order_list').addClass('active_cc');
           $('#outward_order_list').addClass('nv active-color');
    });
    $(document).ready(function() {
        $('#location_id').on('change', function() {
            var locationId = $(this).val();

            if (locationId !== '') {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_pincode_by_location',
                    method: 'POST',
                    data: { location_id: locationId },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.pincode) {
                            $('#pincode').val(response.pincode);
                        } else {
                            $('#pincode').val('');
                        }
                    },
                });
            } else {
                $('#pincode').val('');
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please select type"
        });
    });
    $(document).ready(function () {
        $('#dc_no').on('keyup', function () {
            var dc_no = $(this).val();
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/check_unique_dc_no',
                method: 'post',
                data: {
                    'dc_no': dc_no,
                    'id': '<?= $id ?>'
                },
                success: function (response) {
                    if (response == '1') {
                        $('#dc_no_error').text("This dc no is already added!");
                        $('#dc_no_error').addClass('error');
                        $('#submit_btn').prop('disabled', true);
                    } else {
                        $('#dc_no_error').text("");
                        $('#submit_btn').prop('disabled', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus, errorThrown);
                }
            });
        });
    });
    $(document).ready(function () {
        $('#invoice_no').on('keyup', function () {
            var invoice_no = $(this).val();
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/check_unique_outward_invoice_no',
                method: 'post',
                data: {
                    'invoice_no': invoice_no,
                    'id': '<?= $id ?>'
                },
                success: function (response) {
                    if (response == '1') {
                        $('#invoice_no_error').text("This invoice no is already added!");
                        $('#invoice_no_error').addClass('error');
                        $('#submit_btn').prop('disabled', true);
                    } else {
                        $('#invoice_no_error').text("");
                        $('#submit_btn').prop('disabled', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus, errorThrown);
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function () {
        $.validator.addMethod("atLeastOneDispatch", function(value, element) {
        let filled = false;
        $('input[name="dispatch_quantity[]"]').each(function() {
            if ($(this).val() !== '' && $(this).val() != 0) {
                filled = true;
                return false;
            }
        });
        return filled;
        }, "At least one dispatch quantity is required.");
        $('#outward_form').validate({
            ignore: [],
            rules: {
                dc_no: {
                    required: true,
                },
                invoice_no: {
                    required: true,
                },
                invoice_value: {
                    required: true,
                    number: true,
                },
                freight_amount: {
                    required: true,
                    number: true,
                },
                location_id: {
                    required: true,
                },
                transport: {
                    required: true,
                },
                dispatch_plant_id: {
                    required: true,
                },
                vehicle: {
                    required: true,
                },
                vehicle_no: {
                    required: true,
                },
                driver_name: {
                    required: true,
                },
                driver_mobile: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 10,
                },
                freight_status: {
                    required: true,
                },
                 dispatch_quantity: {
                    required: true,
                },
                "dispatch_quantity[]": {
                    atLeastOneDispatch: true
                }
            },
            messages: {
                dc_no: { required: "Please enter DC No!" },
                invoice_no: { required: "Please enter Invoice No!" },
                invoice_value: {
                    required: "Please enter invoice value!",
                    number: "Only numeric values allowed!",
                },
                freight_amount: {
                    required: "Please enter freight amount!",
                    number: "Only numeric values allowed!",
                },
                location_id: { required: "Please select location!" },
                transport: { required: "Please select transport!" },
                dispatch_plant_id: { required: "Please select dispatching plant!" },
                vehicle: { required: "Please enter vehicle name!" },
                vehicle_no: { required: "Please enter vehicle number!" },
                driver_name: { required: "Please enter driver name!" },
                driver_mobile: {
                    required: "Please enter driver mobile!",
                    digits: "Only numeric values allowed!",
                    minlength: "Mobile number must be 10 digits!",
                    maxlength: "Mobile number must be 10 digits!",
                },
                freight_status: { required: "Please select freight status!" },
                dispatch_quantity: { required: "Please enter dispatch quantity!" },
                "dispatch_quantity[]": {
                    atLeastOneDispatch: "Please dispatch at least one article quantity!"
                },
            },
            errorElement: 'span',
           errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');

                if (element.attr("name") === "dispatch_quantity") {
                    element.closest('td').append(error);
                } else {
                    element.closest('.form-group').append(error);
                }
            },
            
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
        $("#location_id").change(function() {
            $("#location_id").valid();
        });
        $("#freight_status").change(function() {
            $("#freight_status").valid();
        });
    });
</script>
