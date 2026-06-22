<?php include('header.php'); ?>
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
        max-width: 100% !important;
    }
    .btn-info {
        margin-top: 28px;
    }
    .modelclass {
        max-width: 60%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }
    .text-center {
        text-align: center;
    }
    .table input {
        width: 100%;
        height: 42px;
        padding-left: 10px;
        border: 1px solid #ced4da;
    }

    input[name="quantity[]"]::-webkit-outer-spin-button,
    input[name="quantity[]"]::-webkit-inner-spin-button,
    input[name="bundle_qty[]"]::-webkit-outer-spin-button,
    input[name="bundle_qty[]"]::-webkit-inner-spin-button,
    input[name="quantity_new[]"]::-webkit-outer-spin-button,
    input[name="quantity_new[]"]::-webkit-inner-spin-button,
    input[name="bundle_qty_new[]"]::-webkit-outer-spin-button,
    input[name="bundle_qty_new[]"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[name="quantity[]"],
    input[name="bundle_qty[]"],
    input[name="quantity_new[]"],
    input[name="bundle_qty_new[]"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .modal-title{
        font-size: 20px;
        color: #212529;
        font-family: 'Poppins', sans-serif;
    }
    .modal-body{
        font-size: 13px;
        color: #212529;
        font-family: 'Poppins', sans-serif;
        padding: 16px ;
    }
    #save_btn{
        margin-top: 0px;
    }

    /* Custom Stock Alert Modal Styles */
    .stock-alert-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .stock-alert-overlay.show {
        display: flex;
    }

    .stock-alert-box {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        padding: 0;
        max-width: 500px;
        width: 90%;
        animation: slideIn 0.3s ease-out;
        overflow: hidden;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .stock-alert-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 20px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 3px solid #bd2130;
        position: relative;
    }

    .stock-alert-close {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        font-size: 32px;
        padding: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        line-height: 1;
    }

    .stock-alert-close:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: translateY(-50%) scale(1.1);
    }

    .stock-alert-close:active {
        transform: translateY(-50%) scale(0.95);
    }

    .stock-alert-icon {
        font-size: 28px;
        flex-shrink: 0;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .stock-alert-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stock-alert-body {
        padding: 25px 25px 30px 25px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .stock-alert-message {
        color: #333;
        font-size: 15px;
        line-height: 1.6;
        margin: 0 0 20px 0;
        font-weight: 500;
    }

    .stock-alert-details {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .stock-alert-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .stock-alert-detail-row:last-child {
        margin-bottom: 0;
    }

    .stock-alert-label {
        font-weight: 600;
        color: #664d03;
    }

    .stock-alert-value {
        color: #856404;
        font-weight: 700;
        font-size: 16px;
    }

    .status-filter-inline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-right: 12px;
        vertical-align: middle;
    }

    .status-filter-inline label {
        margin: 0;
        white-space: nowrap;
        font-weight: 500;
    }

    .status-filter-inline select {
        min-width: 180px;
    }

    #example4_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    #example4_filter > label {
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <?php if (!empty($single)) { ?>
                <h3>Update Order</h3>
            <?php } else { ?>
            <h3>Create Order</h3>
            <?php } ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <form method="post" name="add_order" id="add_order" enctype="multipart/form-data">
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <div class="modal fade" id="nextAccountModal" tabindex="-1"
                            aria-labelledby="nextAccountModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="nextAccountModalLabel">Proceed Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="modal-message">
                                        <!-- Message will be inserted here -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" style="margin-top: 11px;"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" name = "proceed" class="btn btn-primary">Proceed</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <input autocomplete="off" type="hidden" id="id"  name="id"  value="<?php if (!empty($single)) { echo $single->id; } ?>">
                            <input type="hidden" id="single_order"  name="single_order"  value="<?php if (!empty($single)) { echo $single->order_id; } ?>">
                            <input type="hidden" name="party_hidden" id="party_hidden" value="">
                            <input type="hidden" name="type_of_order_hidden" id="type_of_order_hidden" value="">
                            <input type="hidden" name="ink_type_hidden" id="ink_type_hidden" value="">
                           
                            <div class="row flex_wrap ">    
                                <input name="name" id="name" type="hidden" class="form-control" value="1" >
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Party Name<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="party" id="party" onchange="fetchBrandMaster(this.value)">
                                        <option value="">Select Party</option>
                                        <?php
                                        $party_id = $this->input->get('party_id');
                                        if (!empty($party_name)) {
                                            foreach ($party_name as $party_result) {
                                                $selected = '';
                                                if (!empty($single) && $single->party_id == $party_result->id) {
                                                    $selected = 'selected';
                                                } elseif (empty($single) && !empty($party_id) && $party_id == $party_result->id) {
                                                    $selected = 'selected';
                                                }
                                                ?>
                                                <option value="<?= $party_result->id ?>" <?= $selected ?>><?= $party_result->party_name ?></option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Type of Order<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="type_of_order" id="type_of_order" >
                                        <option value=""> Select Type of Order</option>
                                        <option value="1"<?php if(!empty($single)&& $single->type_of_order==1){?>selected<?php }?>>Household</option>
                                        <option value="2"<?php if(!empty($single)&& $single->type_of_order==2){?>selected<?php }?>>Container</option>
                                        <option value="3"<?php if(!empty($single)&& $single->type_of_order==3){?>selected<?php }?>>Both</option>

                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12 ink_type">
                                    <label>Type Of Container<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="ink_type" id="ink_type" >
                                        <!-- <option value=""> Select Type of Order</option> -->
                                        <option value="1"<?php if(!empty($single)&& $single->ink_type==1){?>selected<?php }?>>Plain</option>
                                        <option value="2"<?php if(!empty($single)&& $single->ink_type==2){?>selected<?php }?>>Printing</option>

                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12 ">
                                    <label>Article Group<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple group_of_article"
                                        name="group_of_article" id="group_of_article"  onchange="handleArticleChange('1')">
                                        <option value=""> Select Type Article Group</option>
                                        <?php if (!empty($article_group)){
                                            foreach ($article_group as $article_result) { ?>
                                                    <option value="<?= $article_result->id ?>"<?php if(!empty($single)&& $single->group_of_article_id==$article_result->id){?>selected<?php }?>><?= $article_result->group_of_article ?></option>
                                                <?php }} ?>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <table style="width:100%; margin-top:10px;" class="table_add_more table table-striped table-bordered d-none" id="example">
                                        <thead class="thead">
                                            <tr>
                                                <th>Type of Article</th>
                                                <th class="container_only d-none"> Brand Type</th>
                                                <th>Order Quantity</th>
                                                <th>Bundle / Bag</th>
                                                <th>Remark</th>
                                                <th style="width: 20px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="particular_row" id="row_1">
                                                <td>
                                                    <select class="form-control new-js-example-basic-multiple type_of_article clear_val error_article_classs" data-row-id="1"
                                                            name="article_type[]" id="article_type_1">
                                                        <option value="">Select Article Type</option>
                                                    </select>
                                                </td>
                                                <td class="container_only d-none">
                                                    <select class="form-control new-js-example-basic-multiple clear_val"
                                                            name="brand_master[]" id="brand_master_1"> 
                                                        <option value="">Select Brand</option>
                                                        
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="quantity[]" min="1" value="" id="quantity_1" class="clear_val error_qty_classs" placeholder="Enter quantity" >
                                                </td>
                                                <td>
                                                    <input type="number" name="bundle_qty[]" min="0" step="0.01" value="" id="bundle_1" class="clear_val" placeholder="Auto bundle" >
                                                </td>
                                                <td>
                                                    <input type="text" name="remark[]" value="" id="remark_1" class="clear_val" placeholder="Enter remark" > 
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <table style="width:100%; margin-top:10px;" class="edit_list table table-striped table-bordered d-none" id="example5">
                                        <thead class="thead">
                                            <tr>
                                                <th>Group of Article</th>
                                                <th>Type of Article</th>
                                                <th class="brand_type_only"> Brand Type</th>
                                                <th>Order Quantity</th>
                                                <th>Bundle / Bag</th>
                                                <th>Remark</th>
                                                <th style="width: 20px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($sub_order)) { 
                                                    $i =2;
                                                    foreach ($sub_order as $sub_order_item) { 
                                            ?>
                                                <tr data-row-id="<?= $i ?>" class="update_particular_row edit_list ">
                                                    <td>
                                                        <input type="text" name="article_group[]" value="<?php echo $sub_order_item->group_of_article; ?>" 
                                                            id="article_group_<?php echo $i; ?>" class="article_group clear_updated_val" disabled>
                                                        <input type="hidden" name="article_group_id[]" class="clear_updated_val" value="<?php echo $sub_order_item->group_of_article_id; ?>" 
                                                         id="article_group_id_<?php echo $i; ?>">
                                                         <input type="hidden" name="update_id[]" id ="update_id_<?php echo $i; ?>" value="<?php echo $sub_order_item->id; ?>">
                                                    </td>
                                                    <td>
                                                        <select class="form-control new-js-example-basic-multiple type_of_article clear_updated_val "
                                                                name="article[]" id="article_<?php echo $i; ?>">
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
                                                    </td>

                                                    <td class="brand_type_only">
                                                        <select class="form-control new-js-example-basic-multiple clear_updated_val"
                                                                name="brand[]" id="brand_<?php echo $i; ?>">
                                                            <?php if (!empty($brand)) {
                                                                foreach ($brand as $brand_result) { ?>
                                                                    <option value="<?= $brand_result->id ?>"
                                                                            <?php if (!empty($sub_order_item) && $sub_order_item->brand_type_id == $brand_result->id) { ?> selected <?php } ?>>
                                                                        <?= $brand_result->brand_name ?>
                                                                    </option>
                                                                <?php }
                                                            } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="quantity_new[]" min="1" class="clear_updated_val" value="<?php echo $sub_order_item->order_quantity; ?>" 
                                                            id="quantity_new_<?php echo $i; ?>" >
                                                    </td>
                                                    <td>
                                                        <input type="number" name="bundle_qty_new[]" min="0" step="0.01" class="clear_updated_val" value="<?php echo isset($sub_order_item->bundle_bag_qty) ? $sub_order_item->bundle_bag_qty : ''; ?>"
                                                            id="bundle_new_<?php echo $i; ?>" >
                                                    </td>
                                                    <td>
                                                        <input type="text" name="remark_new[]" class="clear_updated_val" value="<?php echo $sub_order_item->remark; ?>" 
                                                            id="remark_new_<?php echo $i; ?>" >
                                                    </td>
                                                    <td><button type="button" id="remove_row_btn_<?php echo $i; ?>" class="btn btn-danger remove_btn"><i class="fa fa-trash"></i></button></td>
                                                </tr>
                                            <?php 
                                                    $i++;
                                                }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group add_new_btn d-none">
                                        <button id="add_new_row" type="button" class="btn btn-primary">Add More</button>
                                    </div>
                                </div>
                                <div class=" complete_next_btn">
                                    <button type="button" id="process_btn"  name="process_btn"
                                        class="btn btn-primary">Complete</button>
                                    <button type="button" id="next_btn" name="next_btn" class="btn btn-primary next_btn">Save & Next</button>
                                </div>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12 mt-2" id="status_filter_container">
                                    <label>Status Filter</label>
                                    <select class="form-control" id="status_filter">
                                        <option value="">All Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Processed to Account">Processed to Account</option>
                                        <option value="Partially Dispatched">Partially Dispatched</option>
                                        <option value="Full Dispatched">Full Dispatched</option>
                                        <option value="Order Closed">Order Closed</option>
                                        <option value="Order Cancelled">Order Cancelled</option>
                                        <option value="Printing Inprocess">Printing Inprocess</option>
                                        <option value="Printing Completed">Printing Completed</option>
                                        <option value="Dispatch Inprocess">Dispatch Inprocess</option>
                                    </select>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="x_panel x_p d-none">
                <div class="x_content x_c d-none ">
                    <div class="container cc d-none">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">

                            <table style="width:100%;margin-top:30px;" class="table_complete_list table table-striped table-bordered  d-none" id="example3">
                                <div class="page-title list_title d-none">
                                    <div class="title_left">
                                        <h3>Order Details</h3>
                                    </div>
                                </div>
                                <div class="page-title next_title d-none">
                                    <div class="title_left">
                                        <h3>Selected Article Type</h3>
                                    </div>
                                </div>
                                <thead class="thead">
                                    <tr>
                                        <th>Group Of Article</th>
                                        <th>Type Of Article</th>
                                        <th class=" container_only d-none">Selected Brand</th>
                                        <th>Order Quantity</th>
                                        <th>Bundle / Bag</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="form-group process_button d-none">
                                <?php if (!empty($single)) { ?>
                                <button href="javascript:void(0);"  id="next_button" class="btn btn-primary back_btn"
                                    data-bs-toggle="modal" data-bs-target="#nextAccountModal">Process Order</button>
                                <?php } ?>
                                <button type="button" id="back_btn" class="btn btn-primary">Back</button>
                                <button type="submit" id="save_btn" value = "save_order"  class="btn btn-primary back_btn">Save</button>
                                <input type="hidden" name="proceed" id="proceed_hidden" value="save_order">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="x_panel x_pp d-none">
                <div class="x_content x_cp d-none ">
                    <div class="container ccp d-none">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">

                            <table style="width:100%;margin-top:30px;" class="party_list table table-striped table-bordered  d-none" id="example4">
                
                                <div class="page-title next_title d-none">
                                    <div class="title_left">
                                        <h3>Party Details</h3>
                                    </div>
                                </div>
                                <thead class="thead">
                                    <tr>
                                        <th class="text-center">SR NO.</th>
                                        <th class="text-center">Party Name</th>
                                        <th class="text-center">Order ID</th>
                                        <th class="text-center">Order Details</th>
                                        <th class="text-center">Order Date</th>
                                        <th class="text-center">Dispatch Date</th>
                                        <th class="text-center">Delay Days</th>
                                       <!-- <th>Pending Payment</th> -->
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id ="order-details-table">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </form>
</div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Order ID</th>
                            <th>Group Of Article</th>
                            <th>Type Of Article</th>
                            <th class="brand d-none">Selected Brand</th>
                            <th>Order Qty</th>
                            <th>Bundle / Bag</th>
                            <th class="brand d-none">Approved Qty</th>
                            <th>Pending Qty</th>
                            <th>Dispatched Qty</th>
                            <th>Dispatch Date</th>
                            <th>Remark</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody id="order-details">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="downloadOrderDetailsBtn">Download</button>
                <button type="button" class="btn btn-primary" id="printOrderDetailsBtn">Print</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>

<!-- Custom Stock Alert Modal -->
<div id="stockAlertOverlay" class="stock-alert-overlay">
    <div class="stock-alert-box">
        <div class="stock-alert-header">
            <div class="stock-alert-icon">⚠️</div>
            <h2 class="stock-alert-title">Stock Alert</h2>
            <button type="button" class="stock-alert-close" onclick="closeStockAlert()" title="Close">×</button>
        </div>
        <div class="stock-alert-body">
            <p class="stock-alert-message">
                Insufficient stock available for the selected article.
            </p>
            <div class="stock-alert-details">
                <div class="stock-alert-detail-row">
                    <span class="stock-alert-label">Article:</span>
                    <span class="stock-alert-value" id="stockAlertArticle">-</span>
                </div>
                <div class="stock-alert-detail-row">
                    <span class="stock-alert-label">Required Qty:</span>
                    <span class="stock-alert-value" id="stockAlertRequired">0</span>
                </div>
                <div class="stock-alert-detail-row">
                    <span class="stock-alert-label">Available Qty:</span>
                    <span style="color: #e74c3c; font-weight: 700; font-size: 16px;" id="stockAlertAvailable">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showOrderDetails(order, order_type) {
        if (order_type == 2) {
            order_type = 'Container';
        }
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_sub_order_details") ?>',
            type: 'POST',
            data: { 'order_id': order, 'order_type': order_type },
            dataType: 'json',
            success: function (response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#order-details').empty();
                    var tableContent = '';
                   response.forEach(function (item, index) {
                        if (item.order_status == '0') {
                            item.order_status = 'Pending';
                        } else if (item.order_status == '1') {
                            item.order_status = 'Printing Completed';
                        } else if (item.order_status == '2') {
                            item.order_status = 'Cancelled';
                        } else if (item.order_status == '3') {
                            item.order_status = 'Partially Dispatched';
                        } else if (item.order_status == '4') {
                            item.order_status = 'Fully Dispatched';
                        }else if (item.order_status == '7') {
                            item.order_status = 'Printing Inprocess';
                        }else if (item.order_status == '8') {
                            item.order_status = 'Printing Completed';
                        }else if (item.order_status == '9') {
                            item.order_status = 'Dispatch Inprocess';
                        }else{
                            item.order_status = 'Pending';
                        }
                        const approved_qty = item.approved_qty ? item.approved_qty : '0';
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.order_id}</td>`;
                        tableContent += `<td>${item.group_of_article}</td>`;
                        tableContent += `<td>${item.article_name}</td>`;
                        tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                        tableContent += `<td>${item.order_quantity}</td>`;
                        tableContent += `<td>${item.bundle_bag_qty ? item.bundle_bag_qty : ''}</td>`;
                        tableContent += `<td class = "brand d-none">${approved_qty}</td>`;
                        tableContent += `<td>${item.pending_qty}</td>`;
                        tableContent += `<td>${item.dispatch_quantity}</td>`;
                        tableContent += `<td>${item.dispatch_dates ? item.dispatch_dates : ''}</td>`;
                        tableContent += `<td>${item.remark}</td>`;
                        tableContent += `<td>${item.order_status}</td>`;
                        tableContent += `</tr>`;
                    });

                    $('#order-details').html(tableContent);

                    $('#exampleModal').modal('show');
                } else {
                    alert('No details found for this order!');
                }
                if (order_type == 'Container') {
                    $('.brand').removeClass('d-none');
                } else {
                    $('.brand').addClass('d-none');
                }

            },
        });
    }

    function getOrderDetailsTableHtmlForExport() {
        const table = document.querySelector('#exampleModal #order-details');
        if (!table) {
            return '';
        }
        return table.innerHTML;
    }

    function getOrderDetailsTableText() {
        const rows = [];
        $('#order-details tr').each(function () {
            const row = [];
            $(this).find('td').each(function () {
                row.push($(this).text().trim().replace(/\s+/g, ' '));
            });
            if (row.length) {
                rows.push(row);
            }
        });
        return rows;
    }

    $(document).on('click', '#downloadOrderDetailsBtn', function () {
        const headers = [
            'SR NO.',
            'Order ID',
            'Group Of Article',
            'Type Of Article',
            'Order Qty',
            'Bundle / Bag',
            'Pending Qty',
            'Dispatched Qty',
            'Dispatch Date',
            'Remark',
            'Order Status'
        ];

        const rows = getOrderDetailsTableText();
        if (!rows.length) {
            alert('No order details available to download.');
            return;
        }

        const csvLines = [];
        csvLines.push(headers.map(h => `"${h}"`).join(','));
        rows.forEach(function (row) {
            const escaped = row.map(function (value) {
                return `"${String(value).replace(/"/g, '""')}"`;
            });
            csvLines.push(escaped.join(','));
        });

        const csvContent = '\uFEFF' + csvLines.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        const today = new Date();
        const dateStamp = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

        link.setAttribute('href', url);
        link.setAttribute('download', 'order_details_' + dateStamp + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });

    $(document).on('click', '#printOrderDetailsBtn', function () {
        const tableBodyHtml = getOrderDetailsTableHtmlForExport();
        if (!tableBodyHtml) {
            alert('No order details available to print.');
            return;
        }

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Order Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h2 { margin-bottom: 12px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #999; padding: 8px; text-align: center; font-size: 12px; }
                        th { background: #e9eef7; }
                    </style>
                </head>
                <body>
                    <h2>Order Details</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>SR. NO.</th>
                                <th>Order ID</th>
                                <th>Group Of Article</th>
                                <th>Type Of Article</th>
                                <th>Order Qty</th>
                                <th>Bundle / Bag</th>
                                <th>Pending Qty</th>
                                <th>Dispatched Qty</th>
                                <th>Dispatch Date</th>
                                <th>Remark</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>${tableBodyHtml}</tbody>
                    </table>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () {
            printWindow.print();
        }, 300);
    });
    $(document).ready(function () {
        $('#nextAccountModal').on('show.bs.modal', function () {
            var inkType = $('#ink_type').val();
            let message = '';

            if (inkType == "1") {
                message = "Are you sure you want to process this order to Account?";
            } else {
                message = "Are you sure you want to process this order to Account?";
            }

            $('#modal-message').text(message);
        });
    });
    $(document).ready(function () {
        let partyId = $('#party').val(); 
        fetchBrandMaster(partyId, true); 
    });
    let globalBrandList = [];
    function fetchBrandMaster(partyId, isEditMode = false) {
        if (partyId === "") return;

        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_brand_by_party') ?>",
            type: "POST",
            data: { party_id: partyId },
            success: function(response) {
                globalBrandList = (typeof response === 'object') ? response : JSON.parse(response);

                if (isEditMode) {
                    $('.update_particular_row select[name="brand[]"]').each(function () {
                        let currentSelect = $(this);
                        let selectedVal = currentSelect.val(); 

                        updateBrandDropdown(currentSelect, globalBrandList, selectedVal);
                    });
                }

                updateBrandDropdown($('#brand_master_1'), globalBrandList);
            },
            error: function() {
                alert("Failed to load brand data.");
            }
        });
    }
    function updateBrandDropdown(selectElement, brandList, selectedId = "") {
        selectElement.empty();
        selectElement.append('<option value="" disabled selected>Select Brand</option>');
        
        brandList.forEach(function (brand) {
            let selected = (selectedId && selectedId == brand.id) ? 'selected' : '';
            selectElement.append('<option value="' + brand.id + '" ' + selected + '>' + brand.brand_name + '</option>');
        });
    }
    let globalArticleList = [];

    function handleArticleChange(row_id) {
        var selected_article = $('#group_of_article').val();
        if (!selected_article) return;

        // Fetch all articles for the group and auto-populate one row per article
        $.ajax({
            type: "POST",
            url: "<?= base_url('admin/Ajax_controller/get_all_article_by_group') ?>",
            data: { 'selected_article': selected_article },
            success: function (response) {
                var opts = (typeof response === 'object') ? response : $.parseJSON(response);
                if (!opts || opts.length === 0) return;

                // Cache articles for use by Add More
                globalArticleList = opts;

                var group_name = $('#group_of_article').find('option:selected').text();
                var isContainer = $("#type_of_order").val() === "2";

                // Clear all existing rows and reset to zero
                $('#example tbody').empty();

                $.each(opts, function (i, d) {
                    var rowCount = i + 1;
                    var containerCell = isContainer
                        ? `<td class="container_only"><select class="form-control new-js-example-basic-multiple" name="brand_master[]" id="brand_master_${rowCount}"><option value="">Select Brand</option></select></td>`
                        : `<td class="container_only d-none"><select class="form-control new-js-example-basic-multiple" name="brand_master[]" id="brand_master_${rowCount}"><option value="">Select Brand</option></select></td>`;

                    var newRow = `<tr class="particular_row" id="row_${rowCount}">
                        <td>
                            <select class="form-control new-js-example-basic-multiple type_of_article clear_val error_article_classs" data-row-id="${rowCount}"
                                    name="article_type[]" id="article_type_${rowCount}">
                                <option value="${d.id}" selected>${d.article_name}</option>
                            </select>
                        </td>
                        ${containerCell}
                        <td><input type="number" name="quantity[]" min="1" value="" id="quantity_${rowCount}" class="clear_val" placeholder="Enter quantity"></td>
                        <td><input type="number" name="bundle_qty[]" min="0" step="0.01" value="" id="bundle_${rowCount}" class="clear_val" placeholder="Auto bundle"></td>
                        <td><input type="text" name="remark[]" value="" id="remark_${rowCount}" class="clear_val" placeholder="Enter remark"></td>
                        <td></td>
                    </tr>`;
                    $('#example tbody').append(newRow);

                    if (isContainer) {
                        updateBrandDropdown($('#brand_master_' + rowCount), globalBrandList);
                    }
                });

                $('#example').removeClass('d-none');
                $('.add_new_btn').removeClass('d-none');
                $('.complete_next_btn').removeClass('d-none');
            }
        });
    }
    function initializeValidationForFields(validid) {

        if (validid != '1') {
            $("#article_type_1, #quantity_1 ").each(function () {
                if (!$(this).hasClass("validated")) {
                    $(this).rules("add", {
                        required: true,
                        messages: {
                            required: "",
                        },
                    });
                    $(this).addClass("validated");
                }
            });
            $(".error_article_classs").each(function () {
                if (!$(this).hasClass("validated")) {
                    $(this).rules("add", {
                        required: true,
                        messages: {
                            required: "", 
                        }
                    });

                    $(this).on('invalid', function () {
                        $(this).next('.error-message').remove(); 
                        var errorMessage = '<span class="error-message" style="color: red;">Please select article name!</span>';
                        $(this).after(errorMessage); 
                    });

                    $(this).addClass("validated");
                }
            });
            $(".error_qty_classs").each(function () {
                if (!$(this).hasClass("validated")) {
                    $(this).rules("add", {
                        required: true,
                        number: true,
                        min: 1,
                        messages: {
                            required: "",  
                            number: "",
                            min: ""
                        }
                    });

                    $(this).on('invalid', function () {
                        $(this).next('.error-message').remove();  
                        var errorMessage = '<span class="error-message" style="color: red;">Please enter quantity!</span>';
                        $(this).after(errorMessage); 
                    });

                    $(this).addClass("validated");
                }
            });

            if ($("#type_of_order").val() === "2") {
                $("select[name='brand_master[]']").each(function () {
                    if (!$(this).hasClass("validated")) {
                        $(this).rules("add", {
                            required: true,
                            messages: {
                                required: "",   
                            },
                        });

                        $(this).on('invalid', function () {
                            $(this).next('.error-message').remove();  
                            var errorMessage = '<span class="error-message" style="color: red;">Please select brand!</span>';
                            $(this).after(errorMessage); 
                        });

                        $(this).addClass("validated");
                    }
                });
                $("#brand_master_1").each(function () {
                if (!$(this).hasClass("validated")) {
                        $(this).rules("add", {
                            required: true,
                            messages: {
                                required: "",
                            },
                        });
                        $(this).addClass("validated");
                    }
                });
            }
        }
    }
    
    $(document).ready(function () {
        $('#party').select2({
            placeholder: "Please select party name"
        });
        $('#type_of_order').select2({
            placeholder: "Please select type of order"
        });
        $('#group_of_article').select2({
            placeholder: "Please select group of article"
        });
        $('#ink_type').select2({
            placeholder: "Please select type"
        });
        var edit = $('#id').val();
        if (edit !== "") {
            $('.edit_list').removeClass('d-none');
            $('#party').prop('disabled', true);
            $('#type_of_order').prop('disabled', true);
            $("#group_of_article").prop("disabled", false);
            $(".next_btn").removeClass("d-none");
            $(".table_add_more").removeClass("d-none");
            $(".add_new_btn").removeClass("d-none");
            $('#party_hidden').val($('#party').val());
            $('#type_of_order_hidden').val($('#type_of_order').val());
            
            // $(".particular_row").val('').trigger('change');
            // $(".clear_val").empty();
        } else {
            $('#example5').addClass('d-none');
            $('#party').prop('disabled', false);
            $('#type_of_order').prop('disabled', false);
            $("#group_of_article").prop("disabled", false);
            $(".edit_list").addClass("d-none");
        }
        if ($("#type_of_order").val() === "2") {
            $('.brand_type_only').removeClass('d-none');
            $(".container_only").removeClass("d-none");
            $(".ink_type").removeClass("d-none");
            $("#ink_type").prop("disabled", true);
        } else {
            $(".container_only").addClass("d-none");
            $(".brand_type_only").addClass("d-none");
            $(".ink_type").addClass("d-none");
            $("#ink_type").prop("disabled", false);
        }
        $("#group_of_article").on('change', function () {
            var selectedValue = this.value;
            if (selectedValue !== "") {
                $('#party_hidden').val($('#party').val());
                $('#type_of_order_hidden').val($('#type_of_order').val());
                $("#ink_type_hidden").val($('#ink_type').val());
                $(".particular_row").val('').trigger('change');
                $(".table_add_more").removeClass("d-none");
                $(".add_new_btn").removeClass("d-none");
                $(".process_button").addClass("d-none");
                $(".clear").empty();
            } else {
                $(".table_add_more").addClass("d-none");
            }

        });
        let table = $('#example4').DataTable({
            processing: true,
            serverSide: false,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            searching: true,
            ordering: false,
            ajax: {
                url: "<?= base_url('admin/Ajax_controller/get_party_order_details') ?>",
                dataSrc: "",
                type: "POST",
                data: function (d) {
                    d.selectedValue = $('#party').val();
                    d.status_filter = $('#status_filter').val();
                }
            },
            columns: [
                { 
                    data: null, 
                    className: "text-center",
                    render: function (data, type, row, meta) {
                        return meta.row + 1; 
                    }
                },
                { data: "party_name", className: "text-center" },
                { data: "order_id", className: "text-center" },
                { 
                    data: "order_id", 
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<button type="button" class="btn btn-info" 
                                    onclick="showOrderDetails('${row.order_id}', '${row.type_of_order}')"
                                    title="Order Details">
                                    <i class="fa fa-eye"></i>
                                </button>`;
                    }
                },
                { 
                    data: "order_date", 
                    className: "text-center",
                    render: function (data) {
                        if (!data) return "";
                        let parts = data.split("-");
                        return `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                },
                {
                    data: "dispatch_date",
                    className: "text-center",
                    render: function (data) {
                        return data ? data : "-";
                    }
                },
                {
                    data: "delay_days",
                    className: "text-center",
                    render: function (data) {
                        return data !== "" ? data : "-";
                    }
                },
                { 
                    data: "order_status_text", 
                    className: "text-center"
                }
            ]
        });

        if (!$('#example4_filter').find('#status_filter').length) {
            const $statusFilter = $('#status_filter').detach();
            const $statusLabel = $('#status_filter_container > label').first().detach();
            const $inlineStatusFilter = $('<div class="status-filter-inline"></div>');
            $inlineStatusFilter.append($statusLabel).append($statusFilter);
            $('#example4_filter').prepend($inlineStatusFilter);
            $('#status_filter_container').remove();
        }

        // Reload table on party change
        $('#party').on('change', function () {
            if ($(this).val() !== "") {
                $(".party_list, .x_pp, .x_cp, .ccp, .party_title").removeClass("d-none");
                table.ajax.reload();
            } else {
                $(".party_list, .x_pp, .x_cp, .ccp, .party_title").addClass("d-none");
            }
        });

        $('#status_filter').on('change', function () {
            table.ajax.reload();
        });
       
        let isAlreadyValidated = false;
        $("#process_btn").on('click', function () {
            var validid = '1';
            initializeValidationForFields(validid);
            // $("#group_of_article").prop("disabled", true);
            let errorShown = false;
            let tableEntry = $('#example3 tbody tr').length > 0;
            let tableEntryUpdate = $('#example5 tbody tr').length > 0;
            // Also treat already-saved update rows as valid existing entries
            let hasSavedRows = $('.update_particular_row').filter(function () {
                var qty = $(this).find('input[name="quantity_new[]"]').val();
                return qty && parseInt(qty) > 0;
            }).length > 0;

            if (!tableEntry && !tableEntryUpdate && !hasSavedRows) {
                $('.particular_row').each(function(index) {
                    var rowId = $(this).find('.type_of_article').data('row-id');
                    var articleType = $('#article_type_' + rowId).find('option:selected').text();
                    var quantity = $('#quantity_' + rowId).val();

                    if (index === 0 && (articleType === "" || quantity === "")) {
                        alert("Please select at least one article type and quantity in the first row.");
                        errorShown = true;
                        return false; 
                    }
                });
            }
           
            $('#add_order').validate().settings.rules['quantity[]'] = {};
            $('#add_order').validate().settings.rules['article_type[]'] = {};
            $('#add_order').validate().settings.rules['brand_master[]'] = {};
            $("#article_type_1, #quantity_1, #brand_master_1").each(function () {
                $(this).rules("remove");
                $(this).removeClass("validated"); 
            });
            if (!errorShown) {
                $(".table_complete_list").removeClass("d-none");
                $(".process_button").removeClass("d-none");
                $(".list_title").removeClass("d-none");
                $(".next_title").addClass("d-none");
                $(".add_new_btn").addClass("d-none");
                $(".table_add_more").addClass("d-none");
                $(".next_btn").addClass("d-none");
                $(".complete_next_btn").addClass("d-none");
                $("#group_of_article").prop("disabled", true);
            }

        });
       

        $(document).on('change input', '.error_article_classs, .error_qty_classs, select[name="brand_master[]"],input[name="quantity_new[]"]', function () {
            $(this).valid();

            if ($(this).valid()) {
                $(this).next('.error-message').remove();
            } else {
                var errorMessage = '';
                if ($(this).hasClass('error_article_classs')) {
                    errorMessage = '<span class="error-message" style="color: red;">Please select article name!</span>';
                } else if ($(this).hasClass('error_qty_classs')) {
                    errorMessage = '<span class="error-message" style="color: red;">Please enter quantity!</span>';
                } else if ($(this).is('select[name="brand_master[]"]')) {
                    errorMessage = '<span class="error-message" style="color: red;">Please select a brand!</span>';
                }else if ($(this).is('input[name="quantity_new[]"]')) {
                    errorMessage = '<span class="error-message" style="color: red;">Please enter quantity!</span>';
                }

                if ($(this).next('.error-message').length === 0) { 
                    $(this).after(errorMessage); 
                }
            }
        });

        function getAvailableArticleStock(articleId) {
            var totalStockQty = 0;
            if (!articleId) {
                return totalStockQty;
            }

            $.ajax({
                type: "POST",
                url: "<?= base_url('admin/Ajax_controller/get_article_stock_qty') ?>",
                data: { article_id: articleId },
                async: false,
                success: function (response) {
                    var parsed = {};
                    try {
                        parsed = (typeof response === 'object') ? response : JSON.parse(response);
                    } catch (e) {
                        parsed = {};
                    }

                    var stock = parseFloat(parsed.total_stock_qty);
                    totalStockQty = isNaN(stock) ? 0 : stock;
                },
                error: function () {
                    totalStockQty = 0;
                }
            });

            return totalStockQty;
        }

        function validateLiveStockForRowById(rowId, isEditRow) {
            /*
            var articleSelectId = isEditRow ? '#article_' + rowId : '#article_type_' + rowId;
            var quantityInputId = isEditRow ? '#quantity_new_' + rowId : '#quantity_' + rowId;
            var articleId = $(articleSelectId).val();
            var quantity = parseFloat($(quantityInputId).val());

            if (!articleId || isNaN(quantity) || quantity <= 0) {
                $(quantityInputId).removeClass('is-invalid');
                return true;
            }

            var availableStock = getAvailableArticleStock(articleId);
            if (quantity > availableStock) {
                var articleName = $(articleSelectId).find('option:selected').text();
                showInsufficientStockAlert(articleName, quantity, availableStock);
                $(quantityInputId).addClass('is-invalid');
                return false;
            }

            $(quantityInputId).removeClass('is-invalid');
            */
            return true;
        }

        function getRowIdFromElementId(elementId) {
            if (!elementId) {
                return '';
            }
            return elementId.split('_').pop();
        }

        function getRowIdFromRow($row) {
            var rowId = '';
            var articleSelect = $row.find('select[id^="article_type_"]');
            if (articleSelect.length && articleSelect.attr('id')) {
                rowId = getRowIdFromElementId(articleSelect.attr('id'));
                return rowId;
            }
            var qtyInput = $row.find('input[id^="quantity_"]');
            if (qtyInput.length && qtyInput.attr('id')) {
                rowId = getRowIdFromElementId(qtyInput.attr('id'));
                return rowId;
            }
            var anySelect = $row.find('select[type_of_article], select.type_of_article, select');
            if (anySelect.length && anySelect.first().attr('id')) {
                rowId = getRowIdFromElementId(anySelect.first().attr('id'));
                return rowId;
            }
            return '';
        }

        window.showInsufficientStockAlert = function (articleName, requiredQty, availableQty) {
            document.getElementById('stockAlertArticle').textContent = articleName;
            document.getElementById('stockAlertRequired').textContent = Math.round(requiredQty);
            document.getElementById('stockAlertAvailable').textContent = Math.round(availableQty);
            document.getElementById('stockAlertOverlay').classList.add('show');
        }

        window.closeStockAlert = function () {
            document.getElementById('stockAlertOverlay').classList.remove('show');
        }

        $(document).on('click', '#stockAlertOverlay', function (event) {
            if (event.target === this) {
                window.closeStockAlert();
            }
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeStockAlert();
            }
        });

        let isAppended = false;
        $("#next_btn, #process_btn").on('click', function () {
            isAppended = false;  // reset on every click so multiple Save & Next work correctly
            var clickedButton = $(this).attr('id');
            var allValid = true;
            var stockValidationFailed = false;

            // Validate: at least one row must have a quantity entered
            // Check both new rows (quantity[]) and existing saved rows (quantity_new[])
            var atLeastOneQty = false;
            $('.particular_row').each(function () {
                var qty = $(this).find('input[name="quantity[]"]').val();
                if (qty && parseInt(qty) > 0) { atLeastOneQty = true; }
            });
            if (!atLeastOneQty) {
                // Also check already-saved update rows
                $('.update_particular_row').each(function () {
                    var qty = $(this).find('input[name="quantity_new[]"]').val();
                    if (qty && parseInt(qty) > 0) { atLeastOneQty = true; }
                });
            }
            if (!atLeastOneQty) {
                allValid = false;
                // alert('Please enter quantity for at least one article.');
            }

            // Only validate rows that have a quantity entered; skip empty rows
            $('.particular_row').each(function () {
                var $row = $(this);
                var qty = $row.find('input[name="quantity[]"]').val();
                var articleSel = $row.find('select[name="article_type[]"]');
                var brandSel = $row.find('select[name="brand_master[]"]');
                var order_type = $("#type_of_order").val();

                // Clear old error messages for this row
                $row.find('.error-message').remove();

                if (!qty || parseInt(qty) <= 0) {
                    // Skip validation for rows with no quantity
                    return true;
                }

                // Row has quantity — validate article selection
                if (!articleSel.val()) {
                    allValid = false;
                    articleSel.after('<span class="error-message" style="color: red;">Please select article!</span>');
                }
                // For Container orders — validate brand
                if (order_type === "2" && !brandSel.val()) {
                    allValid = false;
                    brandSel.after('<span class="error-message" style="color: red;">Please select brand!</span>');
                }
            });

            // Validate required top-level fields only (Party, Type of Order, Article Group)
            // Do NOT use form.valid() — it breaks article selects in auto-populated rows
            var formIsValid = true;
            // Article Group is only required when adding a new row.
            // If completing an order that already has saved rows, skip the Article Group check.
            var hasSavedUpdateRows = $('.update_particular_row').filter(function () {
                var qty = $(this).find('input[name="quantity_new[]"]').val();
                return qty && parseInt(qty) > 0;
            }).length > 0;
            var isCompleting = hasSavedUpdateRows && (clickedButton === 'process_btn' || (clickedButton === 'next_btn' && !$('.particular_row input[name="quantity[]"]').filter(function(){ return $(this).val() && parseInt($(this).val()) > 0; }).length));

            if (!$('#party').val()) {
                formIsValid = false;
                alert('Please select Party Name.');
            } else if (!$('#type_of_order').val()) {
                formIsValid = false;
                alert('Please select Type of Order.');
            } else if (!isCompleting && !$('#group_of_article').val()) {
                formIsValid = false;
                alert('Please select Article Group.');
            }

            if (formIsValid && allValid) {
                if (clickedButton === "next_btn") {
                    $("#group_of_article").prop("disabled", false);
                    $("#example5").addClass("d-none");
                    var group = $('#group_of_article').val();
                    if (group !== "") {
                        var validid = '0';
                        initializeValidationForFields(validid);
                    } else if (!hasSavedUpdateRows) {
                        // Only block if no existing saved rows — new order must pick a group
                        alert('Please select group of article');
                        return;
                    }
                }
                $(".list_title").addClass("d-none");
                $(".next_title").removeClass("d-none");
                $('.particular_row').each(function (index) {
                    var rowId = getRowIdFromRow($(this));
                    if (!rowId) { 
                        return true; 
                    }
                    var articleType = $('#article_type_' + rowId).find('option:selected').text();
                    var brand = $('#brand_master_' + rowId).find('option:selected').text();
                    var quantity = $('#quantity_' + rowId).val();
                    var bundle = $('#bundle_' + rowId).val();
                    var remark = $('#remark_' + rowId).val();
                    var articleID = $('#article_type_' + rowId).val();
                    var brandID = $('#brand_master_' + rowId).val();
                    var group_of_article = $('#group_of_article').find('option:selected').text();
                    var group_id = $('#group_of_article').val();
                    var order_type = $("#type_of_order").val();
                    function areFieldsValid() {
                        if (order_type == 2) {
                            return articleType !== "" && quantity !== "" && brandID !== "";
                        }
                        return articleType !== "" && quantity !== "";
                    }
                    if (articleID !== "") {
                        $('#party').prop('disabled', true);
                        $('#type_of_order').prop('disabled', true);
                        $('#ink_type').prop('disabled', true);
                    }
                    if (areFieldsValid()) {
                        let existingRow = null;
                        let existingQuantity = 0;
                       
                        $('#example3 tbody tr').each(function () {
                            const row = $(this);
                            const cols = row.find('td');
                            const colCount = cols.length;

                            const existingGroup = cols.eq(0).text().trim();
                            const existingArticle = cols.eq(1).text().trim();
                            const existingQuantityText = (colCount === 6) ? cols.eq(4).text().trim() : cols.eq(3).text().trim();

                            const existingGroupID = row.find('input[name="group_id[]"]').val();
                            const existingArticleID = row.find('input[name="article_id[]"]').val();
                            const existingBrandID = row.find('input[name="brand_id[]"]').val();

                            const exGroupID = row.find('input[name="group_new_id[]"]').val();
                            const exArticleID = row.find('input[name="article_new_id[]"]').val();
                            const exBrandID = row.find('input[name="brand_new_id[]"]').val();

                            const isMatchingGroup = (existingGroupID === group_id) || (exGroupID === group_id);
                            const isMatchingArticle = (existingArticleID === articleID) || (exArticleID === articleID);
                            const isMatchingBrand = (existingBrandID === brandID) || (exBrandID === brandID);
                            // if (!isMatchingBrand) {
                            //     if (isMatchingGroup && isMatchingArticle) {
                            //         existingRow = row;
                            //         existingQuantity = parseFloat(existingQuantityText) || 0;
                            //         return false;
                            //     }
                            // } else {
                            //     if (isMatchingGroup && isMatchingArticle  && isMatchingBrand) {
                            //         existingRow = row;
                            //         existingQuantity = parseFloat(existingQuantityText) || 0;
                            //         return false;
                            //     }
                            // }
                            if (brandID && brandID !== '') {
                                if (isMatchingGroup && isMatchingArticle && isMatchingBrand) {
                                    existingRow = row;
                                    existingQuantity = parseFloat(existingQuantityText) || 0;
                                    return false; 
                                }
                            } else { 
                                if (isMatchingGroup && isMatchingArticle) {
                                    existingRow = row;
                                    existingQuantity = parseFloat(existingQuantityText) || 0;
                                    return false;
                                }
                            }
                        });

                        const requestedQuantity = parseFloat(quantity) || 0;
                        const requiredQuantity = existingRow ? (existingQuantity + requestedQuantity) : requestedQuantity;
                        const availableStock = getAvailableArticleStock(articleID);

                        /*
                        if (requiredQuantity > availableStock) {
                            showInsufficientStockAlert(articleType, requiredQuantity, availableStock);
                            stockValidationFailed = true;
                            return false;
                        }
                        */

                        if (existingRow) {
                            const newQuantity = requestedQuantity;
                            const updatedQuantity = existingQuantity + newQuantity;
                            const batchSize = getArticleBatchSize(articleID);
                            const updatedBundle = (batchSize > 0) ? formatBundleValue(updatedQuantity / batchSize) : '';

                            const cols = existingRow.find('td');
                            if (cols.length === 6) {
                                cols.eq(4).text(updatedQuantity);
                                cols.eq(5).text(updatedBundle);
                            } else {
                                cols.eq(3).text(updatedQuantity);
                                cols.eq(4).text(updatedBundle);
                            }

                            existingRow.find('input[name="quantity_id[]"]').val(updatedQuantity);
                            existingRow.find('input[name="bundle_id[]"]').val(updatedBundle);
                            existingRow.find('input[name="quantity_new_id[]"]').val(updatedQuantity);
                            existingRow.find('input[name="bundle_new_id[]"]').val(updatedBundle);
                        } else {
                            var newRow = '<tr>' +
                                '<td>' + group_of_article + '</td>' +
                                '<td>' + articleType + '</td>' +
                                (brandID ? '<td>' + brand + '</td>' : '') +
                                '<td>' + quantity + '</td>' +
                                '<td>' + bundle + '</td>' +
                                '<td>' + remark + '</td>' +
                                '<input type="hidden" name="article_id[]" id="article_id_' + rowId + '" value="' + articleID + '" />' +
                                '<input type="hidden" name="brand_id[]" id="brand_id_' + rowId + '" value="' + brandID + '" />' +
                                '<input type="hidden" name="quantity_id[]" id="quantity_' + rowId + '" value="' + quantity + '" />' +
                                '<input type="hidden" name="bundle_id[]" id="bundle_' + rowId + '" value="' + bundle + '" />' +
                                '<input type="hidden" name="remark_id[]" id="remark_' + rowId + '" value="' + remark + '" />' +
                                '<input type="hidden" name="group_id[]" id="group_id_' + rowId + '" value="' + group_id + '" />' +
                                '</tr>';

                            $('#example3 tbody').append(newRow);
                        }
                        $(".next_title, #example3, .table_complete_list, .x_p, .x_c, .cc").removeClass("d-none");
                        $(".process_button").removeClass("d-none");
                        $(".table_add_more, .add_new_btn, .complete_next_btn").addClass("d-none");
                        $(".clear").addClass("d-none");
                        $(this).find(".clear_val").val('');
                        $('#quantity_' + rowId).val('');
                        if (!$('.particular_row').first().is(this)) {
                            $(this).remove();
                        }
                    }

                });

                if (stockValidationFailed) {
                    return;
                }

                if (isAppended) {
                    return;  
                }
                let editValid = true;
                $('.update_particular_row').each(function () {
                    var rowId = $(this).data('row-id');
                    var new_row = rowId - 1;
                    var quantity = $('#quantity_new_' + rowId).val();
                    if ($('#quantity_new_' + rowId).val() === '') {
                        editValid = false; 
                        alert("Please enter quantity for Row ID :" + new_row);
                        return false; 
                    }
                });
                if (editValid) {
                    let editStockValidationFailed = false;
                    $('.update_particular_row').each(function () {
                        var rowId = $(this).data('row-id');
                        var group_of_article = $('#article_group_' + rowId).val();
                        var article_group_id = $('#article_group_id_' + rowId).val();
                        var update_id = $('#update_id_' + rowId).val();
                        var article = $('#article_' + rowId).find('option:selected').text();
                        var articleID = $('#article_' + rowId).val();
                        var quantity = $('#quantity_new_' + rowId).val();
                        var bundle = $('#bundle_new_' + rowId).val();
                        var remark = $('#remark_new_' + rowId).val();
                        if ($("#type_of_order").val() === "2") {
                            var brand = $('#brand_' + rowId).find('option:selected').text();
                            var brand_id = $('#brand_' + rowId).val();
                        }
                        if (articleID !== "" && quantity !== "") {
                            var availableStock = getAvailableArticleStock(articleID);
                            var requestedQuantity = parseFloat(quantity) || 0;

                            /*
                            if (requestedQuantity > availableStock) {
                                showInsufficientStockAlert(article, requestedQuantity, availableStock);
                                editStockValidationFailed = true;
                                return false;
                            }
                            */

                            var newRow = '<tr>' +
                                '<td>' + group_of_article + '</td>' +
                                '<td>' + article + '</td>' +
                                (brand_id ? '<td>' + brand + '</td>' : '') +
                                '<td>' + quantity + '</td>' +
                                '<td>' + bundle + '</td>' +
                                '<td>' + remark + '</td>' +
                                '<input type="hidden" name="group_new_id[]" id ="group_ids_' + rowId + '" value="' + article_group_id + '" />' +
                                '<input type="hidden" name="article_new_id[]" id ="article_ids_' + rowId + '" value="' + articleID + '" />' +
                                '<input type="hidden" name="brand_new_id[]" id ="brands_ids_' + rowId + '" value="' + brand_id + '" />' +
                                '<input type="hidden" name="quantity_new_id[]" id ="qty_ids_' + rowId + '" value="' + quantity + '" />' +
                                '<input type="hidden" name="bundle_new_id[]" id ="bundle_ids_' + rowId + '" value="' + bundle + '" />' +
                                '<input type="hidden" name="remark_new_id[]" id ="remark_ids_' + rowId + '" value="' + remark + '" />' +
                                '<input type="hidden" name="update_ids[]" id ="update_ids_' + rowId + '" value="' + update_id + '" />' +
                                '</tr>';
                            $('#example3 tbody').append(newRow);
                            $(".next_title, #example3, .table_complete_list, .x_p, .x_c, .cc").removeClass("d-none");
                            $(".process_button").removeClass("d-none");
                            $(".table_add_more, .add_new_btn, .complete_next_btn").addClass("d-none");
                            $(".clear").addClass("d-none");
                            $("#example5").addClass("d-none");
                            $('#article_group_' + rowId).val('');
                            $('#article_group_id_' + rowId).val('');
                            $('#update_id_' + rowId).val('');
                            $('#article_' + rowId).val('');
                            $('#brand_' + rowId).val('');
                            $('#quantity_new_' + rowId).val('');
                            $('#bundle_new_' + rowId).val('');
                            $('#remark_new_' + rowId).val('');
                        }
                    
                    });

                    if (editStockValidationFailed) {
                        return;
                    }

                    isAppended = true;
                    var group = $('#group_of_article').val();
                    var edit = $('#id').val();
                    if (group !== "" && edit !== "") {
                        var validid = '1';
                        initializeValidationForFields(validid);
                    }
                   
                }else{
                    $("#group_of_article").prop("disabled", true);
                    $("#example5").removeClass("d-none");
                }
              
            }
           
        });
        $(".hidden_class").val('');
        $("#back_btn").on('click', function () {
            $(".process_button").addClass("d-none");
            $(".next_btn").removeClass("d-none");
            $(".table_add_more").removeClass("d-none");
            $(".add_new_btn").removeClass("d-none");
            $(".complete_next_btn").removeClass("d-none");
            $(".particular_row").val('').trigger('change');
            $(".clear_updated_val").empty();
            $("#group_of_article").prop("disabled", false);
            var validid = '0';
            initializeValidationForFields(validid);
        });
       
        $("#type_of_order").on('change', function () {
            var type = $('#group_of_article').val();
            if (type != '') {
                $('#group_of_article').val('').trigger('change');
                $('#article_type_1').val(' ');
                $('#quantity_1').val(' ');
                $('#bundle_1').val(' ');
                $('#remark_1').val(' ');
                $('#brand_master_1').val(' ');
                $(".add_new_btn").addClass("d-none");
            }
            var selectedValue = this.value;
            if (selectedValue === "2") {
                $(".container_only").removeClass("d-none");
                $(".ink_type").removeClass("d-none");
            } else {
                $(".container_only").addClass("d-none");
                $(".ink_type").addClass("d-none");
            }
        });
       
        function validateRowsBeforeAdding() {
            let allValid = true;

            $('#example tbody tr').each(function () {
                const articleType = $(this).find('select[name="article_type[]"]').val();
                const quantity = $(this).find('input[name="quantity[]"]').val();
                const brandType = $(this).find('select[name="brand_master[]"]').val();
                const typeOfOrder = $("#type_of_order").val();

                if (!articleType || !quantity || (typeOfOrder === "2" && !brandType)) {
                    allValid = false;
                    return false; 
                }
            });

            if (!allValid) {
                alert('Complete previous rows before adding a new one.');
            }

            return allValid;
        }

        $('#add_new_row').click(function (e) {
            e.preventDefault();
            let allValid = true;
           
            var row = $('#example tbody tr').length;
            var rowCount = row + 1;
            var newRow = `
            <tr class="particular_row clear">
                <td>
                    <select class="form-control new-js-example-basic-multiple error_article_classs type_of_article hidden_class" name="article_type[]" data-row-id="${rowCount}"
                            id="article_type_${rowCount}">
                        <option value="">Select article type</option>
                    </select>
                </td>
                <td class="container_only d-none">
                    <select class="form-control new-js-example-basic-multiple" name="brand_master[]"
                            id="brand_master_${rowCount}"> 
                        <option value="">Select Brand</option>
                        
                    </select>
                </td>
                <td><input type="number" class="hidden_class error_qty_classs" name="quantity[]" min="1" value="" id="quantity_${rowCount}" placeholder="Enter quantity"></td> 
                <td><input type="number" class="hidden_class" name="bundle_qty[]" min="0" step="0.01" value="" id="bundle_${rowCount}" placeholder="Auto bundle"></td>
                <td><input type="text" name="remark[]" value=""  id="remark_${rowCount}" placeholder="Enter remark"></td> 
                <td><button type="button" class="btn btn-danger remove_row_btn"><i class="fa fa-trash"></i></button></td>
            </tr>
            `;
            $('#example tbody').append(newRow);
            if ($("#type_of_order").val() === "2") {
                $('#example tbody tr:last-child .container_only').removeClass("d-none");
            }
            updateBrandDropdown($('#brand_master_' + rowCount), globalBrandList);
            // Populate article options from cached list
            if (globalArticleList.length > 0) {
                var $articleSelect = $('#article_type_' + rowCount);
                globalArticleList.forEach(function (a) {
                    $articleSelect.append('<option value="' + a.id + '">' + a.article_name + '</option>');
                });
            }
            var validid = '0';
            initializeValidationForFields(validid);
        });

        function updateBrandDropdown(selectElement, brandList) {
            selectElement.empty();
            selectElement.append('<option value="">Select Brand</option>');
            brandList.forEach(function (brand) {
                selectElement.append('<option value="' + brand.id + '">' + brand.brand_name + '</option>');
            });
        }

        function formatBundleValue(value) {
            var fixed = (Math.round(value * 100) / 100).toFixed(2);
            return fixed.replace(/\.00$/, '');
        }

        var articleBatchCache = {};

        function getArticleBatchSize(articleId) {
            if (!articleId) {
                return 0;
            }

            if (Object.prototype.hasOwnProperty.call(articleBatchCache, articleId)) {
                return articleBatchCache[articleId];
            }

            var batchSize = 0;
            $.ajax({
                type: 'POST',
                url: "<?= base_url('admin/Ajax_controller/get_article_bom_data_for_store') ?>",
                data: { article_id: articleId },
                async: false,
                success: function (response) {
                    var parsed = [];
                    try {
                        parsed = (typeof response === 'object') ? response : JSON.parse(response);
                    } catch (e) {
                        parsed = [];
                    }

                    if (Array.isArray(parsed) && parsed.length > 0) {
                        var batch = parseFloat(parsed[0].batch);
                        if (!isNaN(batch) && batch > 0) {
                            batchSize = batch;
                        }
                    }
                },
                error: function () {
                    batchSize = 0;
                }
            });

            articleBatchCache[articleId] = batchSize;
            return batchSize;
        }

        function getArticleIdFromRow(element) {
            return $(element).closest('tr').find('select.type_of_article').val() || '';
        }

        function autoCalculateBundle(quantityInput) {
            var quantity = parseFloat($(quantityInput).val());
            var $row = $(quantityInput).closest('tr');
            var bundleField = $row.find('input[name="bundle_qty[]"], input[name="bundle_qty_new[]"]');

            if (isNaN(quantity) || quantity <= 0) {
                bundleField.val('');
                return;
            }

            var articleId = getArticleIdFromRow(quantityInput);
            var batchSize = getArticleBatchSize(articleId);

            if (!batchSize || batchSize <= 0) {
                bundleField.val('');
                return;
            }

            bundleField.val(formatBundleValue(quantity / batchSize));
        }

        function autoCalculateQuantity(bundleInput) {
            var bundle = parseFloat($(bundleInput).val());
            var $row = $(bundleInput).closest('tr');
            var qtyField = $row.find('input[name="quantity[]"], input[name="quantity_new[]"]');

            if (isNaN(bundle) || bundle <= 0) {
                qtyField.val('');
                return;
            }

            var articleId = getArticleIdFromRow(bundleInput);
            var batchSize = getArticleBatchSize(articleId);

            if (!batchSize || batchSize <= 0) {
                qtyField.val('');
                return;
            }

            var calculatedQty = Math.round(bundle * batchSize);
            qtyField.val(calculatedQty);

            // Trigger stock check and validation
            var qtyInputId = qtyField.attr('id') || '';
            var rowId = qtyInputId.split('_').pop();
            var isEditRow = qtyInputId.indexOf('quantity_new_') === 0;
            validateLiveStockForRowById(rowId, isEditRow);
            if (typeof qtyField.valid === 'function') {
                qtyField.valid();
            }
        }

        $(document).on('input change keyup paste', 'input[name="quantity[]"], input[name="quantity_new[]"]', function () {
            autoCalculateBundle(this);
        });

        $(document).on('input change keyup paste', 'input[name="bundle_qty[]"], input[name="bundle_qty_new[]"]', function () {
            autoCalculateQuantity(this);
        });

        // Live stock check while entering quantity (without clicking Save & Next)
        $(document).on('input change', 'input[name="quantity[]"]', function () {
            var rowId = getRowIdFromElementId($(this).attr('id'));
            validateLiveStockForRowById(rowId, false);
        });

        $(document).on('input change', 'input[name="quantity_new[]"]', function () {
            var rowId = getRowIdFromElementId($(this).attr('id'));
            validateLiveStockForRowById(rowId, true);
        });

        $('input[name="quantity[]"], input[name="quantity_new[]"]').each(function () {
            if ($(this).val() !== '') {
                autoCalculateBundle(this);
            }
        });

        $(document).on('change', 'select[name="article_type[]"], select[name="article[]"]', function () {
            var selected = $(this).val();
            var selectId = $(this).attr('id') || '';
            var rowId = selectId.split('_').pop();
            var isEditRow = selectId.indexOf('article_') === 0;

            var qtyInput = isEditRow ? $('#quantity_new_' + rowId) : $('#quantity_' + rowId);
            var bundleInput = isEditRow ? $('#bundle_new_' + rowId) : $('#bundle_' + rowId);

            if (qtyInput.val() !== '') {
                autoCalculateBundle(qtyInput);
            } else if (bundleInput.val() !== '') {
                autoCalculateQuantity(bundleInput);
            }

            if (!selected) {
                bundleInput.val('');
                qtyInput.val('');
            }

            validateLiveStockForRowById(rowId, isEditRow);
        });

        $('#add_order').on('wheel', 'input[type="number"]', function (event) {
            event.preventDefault();
        });


        $(document).on('click', '.remove_row_btn', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.remove_btn', function () {
            var rowId = $(this).closest('tr').data('row-id');
            var updateId = $('#update_id_' + rowId).val();
            var type = $('#type_of_order').val();
            var totalRows = $('table tbody tr').length;
            if (totalRows > 2) {
                if (confirm('Are you sure you want to delete this item?')) {
                    $.ajax({
                        url: '<?= base_url() ?>admin/Ajax_controller/delete_sub_order_item',
                        method: 'POST',
                        data: {
                            'update_id': updateId,
                            'type': type
                        },
                        success: function (response) {
                            $('tr[data-row-id="' + rowId + '"]').remove();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert('An error occurred while deleting the item.');
                        }
                    });
                }
            } else {
                alert('You cannot delete the last item.');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
       
        // $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_order').addClass('active_cc');
        // $('#task_management').addClass('nv active-color');
    });
    $('#save_btn').on('click', function () {
        $('#proceed_hidden').val('save_order');
        removeHiddenRowRules();
    });

    // From modal → Proceed
    $('button[name="proceed"]').on('click', function () {
        $('#proceed_hidden').val('process_order');
        removeHiddenRowRules();
        $('#add_order').submit();
    });

    function removeHiddenRowRules() {
        $("#article_type_1, #quantity_1, #brand_master_1").each(function () {
            $(this).rules("remove");
            $(this).removeClass("validated"); 
        });
        $('.error_article_classs, .error_qty_classs').each(function() {
            $(this).rules("remove");
        });
    }

    // Optional: Save & Next
    $('#next_btn').on('click', function () {
        $('#proceed_hidden').val('save_next');
    });
    // $('form').on('submit', function () {
    //     $(this).find('button[type=submit]').prop('disabled', true);
    // });
</script>
<script>
    $(document).ready(function () {
        $('#add_order').validate({
            ignore: [], 
            rules: {
                party: {
                    required: true
                },
                type_of_order: {
                    required: true
                },
                group_of_article: {
                    required: function() {
                        return $('#type_of_order_hidden').val() === '';
                    },
                },
                // ink_type: {
                //     required: true
                // },
            },
            messages: {
                party: {
                    required: "Please select a party!"
                },
                type_of_order: {
                    required: "Please select an order type!"
                },
                group_of_article: {
                    required: "Please select an article group!"
                },
                ink_type: {
                    required: "Please select an ink type!"
                },
                
            },
            
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                $(form).find('button[type=submit]').prop('disabled', true);
                $('#nextAccountModal').modal('hide');
                form.submit();
            },
            invalidHandler: function(event, validator) {
                $('#nextAccountModal').modal('hide');
                $(this).find('button[type=submit]').prop('disabled', false);
                console.log("Validation failed on:", validator.errorList);
            }
        });
        $("#party").change(function() {
            $("#party").valid();
        });
        $("#ink_type").change(function() {
            $("#ink_type").valid();
        });
        $("#type_of_order").change(function() {
            $("#type_of_order").valid();
        });
        $("#group_of_article").change(function() {
            $("#group_of_article").valid();
        });
    });
</script>
