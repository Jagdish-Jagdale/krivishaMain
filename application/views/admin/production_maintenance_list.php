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
        margin-left: -10px;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }
</style>
<!-- page content -->

<div class="right_col">
    <div class="page-title">
        <div class="title_left">
            <h3>Production Maintenance List</h3>
        </div>
    </div>
    <div class="main_page">
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="production_maintenance" id="production_maintenance"
                    enctype="multipart/form-data" novalidate="novalidate">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>MWO Code</label>
                            <input type="text" name="mwo_code" id="mwo_code" class="form-control"
                                placeholder="Enter MWO code" value="<?php if (isset($_GET['mwo_code']) && $_GET['mwo_code'] != '') {
                                                                        echo $_GET['mwo_code'];
                                                                    } ?>">
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Type of Action</label>
                                <select class="form-control js-example-basic-multiple" name="type_action"
                                    id="type_action">
                                    <option value="" selected disabled>Select Type of action</option>
                                    <option value="1" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '1') { ?>selected="selected" <?php } ?>>Emergency
                                    </option>
                                    <option value="2" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '2') { ?>selected="selected" <?php } ?>>Online
                                        Breakdown</option>
                                    <option value="3" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '3') { ?>selected="selected" <?php } ?>>Preventive
                                    </option>
                                    <option value="4" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '4') { ?>selected="selected" <?php } ?>>Outside Work
                                    </option>
                                    <option value="5" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '5') { ?>selected="selected" <?php } ?>>General
                                    </option>
                                    <option value="6" <?php if (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '6') { ?>selected="selected" <?php } ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Maintenance required for</label>
                                <select class="form-control js-example-basic-multiple" name="maintain_action"
                                    id="maintain_action">
                                    <option value="" selected disabled>Select maintenance required for</option>
                                    <option value="1" <?php if (isset($_GET['maintaince']) && $_GET['maintaince'] == '1') { ?>selected="selected" <?php } ?>>Machine</option>
                                    <option value="2" <?php if (isset($_GET['maintaince']) && $_GET['maintaince'] == '2') { ?>selected="selected" <?php } ?>>Mould/Article Name</option>
                                    <option value="3" <?php if (isset($_GET['maintaince']) && $_GET['maintaince'] == '3') { ?>selected="selected" <?php } ?>>Printing Unit</option>
                                    <option value="4" <?php if (isset($_GET['maintaince']) && $_GET['maintaince'] == '1') { ?>selected="selected" <?php } ?>>Plant</option>
                                    <option value="5" <?php if (isset($_GET['maintaince']) && $_GET['maintaince'] == '2') { ?>selected="selected" <?php } ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Subcategory Maintenance</label>
                            <!-- <input type="text" name="sub_category" id="sub_category" class="form-control"
                                placeholder="Enter subcategory maintenance" value="<?php if (isset($_GET['sub_category']) && $_GET['sub_category'] != '') {
                                                                                        echo $_GET['sub_category'];
                                                                                    } ?>"> -->

                            <select style="display: none;" class="form-control js-example-basic-multiple"
                                name="sub_category" id="sub_category">

                                <option value="">Please select subcategory</option>
                            </select>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>production_maintenance_list" class="btn btn-sm btn-danger"
                                id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table class="table" style="width: 100%;" id="example">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>MWO Code</th>
                            <th>Plant Name</th>
                            <th>Employee Name</th>
                            <th>Date</th>
                            <th>Type of Action</th>
                            <th>Maintenance required for</th>
                            <th>Subcategory Maintenance</th>
                            <th>Details of Maintenance</th>
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
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
                                                                    echo $_GET['date'];
                                                                } ?>">
<input type="hidden" name="search_type_action" id="search_type_action" value="<?php if (isset($_GET['type_of_action'])) {
                                                                                    echo $_GET['type_of_action'];
                                                                                } ?>">
<input type="hidden" name="search_maintain_action" id="search_maintain_action" value="<?php if (isset($_GET['maintaince'])) {
                                                                                            echo $_GET['maintaince'];
                                                                                        } ?>">
<input type="hidden" name="search_mwo_code" id="search_mwo_code" value="<?php if (isset($_GET['mwo_code'])) {
                                                                            echo $_GET['mwo_code'];
                                                                        } ?>">
<input type="hidden" name="search_sub_category" id="search_sub_category" value="<?php if (isset($_GET['sub_category'])) {
                                                                                    echo $_GET['sub_category'];
                                                                                } ?>">

<input type="hidden" name="status_of_work" id="status_of_work" value="<?php if (isset($_GET['status_of_work'])) {
                                                                            echo $_GET['status_of_work'];
                                                                        } ?>">



<?php include('footer.php'); ?>


<!-- <script type="text/javascript" src="http://localhost:81/krivisha/assets/js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="http://localhost:81/krivisha/assets/js/datepicker/daterangepicker.js"></script> -->
<script>
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
    $('#sub_category').select2({
        placeholder: "Please select sub category"
    });
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
    });

    $('#maintain_action').on('change', function() {
        var selected_master = $('#maintain_action').val();
        $('#sub_category').html('<option value="">Select Type</option>');
        $.ajax({
            type: "POST",
            url: "<?= base_url("admin/Ajax_controller/get_machine_types") ?>",
            data: {
                'selected_master': selected_master
            },
            success: function(response) {
                console.log(response);
                $("#sub_category").empty();
                $("#sub_category").empty();
                $('#sub_category').append('<option value="">Select Subcategory Maintenance</option>');
                var opts = $.parseJSON(response);
                console.log(opts);
                $.each(opts, function(i, d) {
                    if (selected_master == '1') {
                        $('#sub_category').append('<option value="' + d.type_id + '">' + d.machine_name + '</option>');
                    } else if (selected_master == '2') {
                        $('#sub_category').append('<option value="' + d.type_id + '">' + d.article_name + '</option>');
                    } else if (selected_master == '3') {
                        $('#sub_category').append('<option value="' + d.type_id + '">' + d.machine_name + '</option>');
                    } else if (selected_master == '4') {
                        $('#sub_category').append('<option value="' + d.type_id + '">' + d.plant_name + '</option>');
                    }
                });
                $('#sub_category').trigger('chosen:updated');
            }
        });

    });
</script>
<script>
    $(document).ready(function() {
        // $('#maintenance .child_menu').show();
        $('#maintenance').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.production_maintenance_list').addClass('active_cc');
        // $('#maintenance').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,
            scrollCollapse: true,
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Production Maintenance list',
                filename: 'production_maintenance_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            }],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_production_maintenance_list",
                "type": "POST",
                "data": function(d) {
                    d.search_date = $("#date").val();
                    d.search_mwo_code = $("#mwo_code").val();
                    d.search_sub_category = $("#sub_category").val();
                    d.search_type_action = $("#type_action").val();
                    d.search_maintain_action = $("#maintain_action").val();
                    d.status_of_work = $("#status_of_work").val();
                },
                "complete": function() {
                    $('[data-toggle="tooltip"]').tooltip();
                },
            }
        });

        $('#production_maintenance').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
</script>