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

    .modelclass {
        max-width: 40%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }

    .right_col .page_title,
    .right_col .page_body {
        padding: -1px 8px;
    }

    .page_sec {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        height: auto;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Material Requistition To Other Plant List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="plant">Plant</label>
                            <select class="form-control js-example-basic-multiple" name="plant" id="plant">
                                <option value="" selected disabled>Select Plant</option>
                                <?php if (!empty($plant)) : ?>
                                    <?php foreach ($plant as $plant_result) : ?>
                                        <option value="<?= $plant_result->id ?>"
                                            <?= (isset($_GET['plant']) && $_GET['plant'] == $plant_result->id) ? 'selected' : '' ?>>
                                            <?= $plant_result->plant_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>


                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Request For</label>
                                <select class="form-control js-example-basic-multiple" name="request" id="request">
                                    <option value="" selected disabled>Request For</option>
                                    <option value="0" <?php if (isset($_GET['request']) && $_GET['request'] == '0') { ?>selected="selected" <?php } ?>>Raw Material
                                    </option>
                                    <option value="1" <?php if (isset($_GET['request']) && $_GET['request'] == '1') { ?>selected="selected" <?php } ?>>Article
                                    </option>
                                    <option value="2" <?php if (isset($_GET['request']) && $_GET['request'] == '2') { ?>selected="selected" <?php } ?>>Master Batch (Color)
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '1') { ?>selected="selected" <?php } ?>>Pending
                                    </option>

                                    <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '3') { ?>selected="selected" <?php } ?>>Partially Completed
                                    </option>
                                    <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '2') { ?>selected="selected" <?php } ?>>Completed
                                    </option>
                                </select>
                            </div>
                        </div>



                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>material_artical_requistition_to_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Request Number</th>
                                    <th>Request By</th>
                                    <th>Request For</th>
                                    <th>Request Date & Time</th>
                                    <th>To Plant (Receiver)</th>
                                    <!-- <th>To Plant</th> -->
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
                                                                    echo $_GET['date'];
                                                                } ?>">

<input type="hidden" name="request_for" id="request_for" value="<?php if (isset($_GET['request'])) {
                                                                    echo $_GET['request'];
                                                                } ?>">

<input type="hidden" name="request_order_status" id="request_order_status" value="<?php if (isset($_GET['order_status'])) {
                                                                                        echo $_GET['order_status'];
                                                                                    } ?>">
                                                                                    
<input type="hidden" name="plant_id" id="plant_id" value="<?php if (isset($_GET['plant'])) {
                                                                echo $_GET['plant'];
                                                            } ?>">

<input type="hidden" name="request_type" id="request_type" value="<?php if (isset($_GET['miscellaneous'])) {
                                                                                        echo $_GET['miscellaneous'];
                                                                                    } ?>">

<?php include('footer.php');
?>
<script>
        $(document).ready(function() {
        
        $('#stock_management').addClass('nv active');
       
        $('.material_artical_requistition_to_list').addClass('active_cc');
       
    });
</script>
<script>
   
    $('.select2-select').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });
        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
    $(".datepickers").flatpickr({
        mode: "range",
        dateFormat: "d-m-Y",
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "cache": false,
            "order": [],
            "ordering": false,
            "scrollX": true,
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Material Requisition To Other Plant List',
                filename: 'material_requisition_to_other_plant_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_material_qty_request_one_plant_to_other_plant_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.request_order_status = $('#request_order_status').val();
                    data.plant_id = $('#plant_id').val();
                    data.request_for = $('#request_for').val();
                    data.request_type = $('#request_type').val();
                },
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>