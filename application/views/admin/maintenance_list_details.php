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
    .modelclass {
        max-width:69%;
        width: auto;
    }
    .content_body{
        padding: 20px;
        text-align: center;
    }
    
</style>
<div class="right_col">
    <h3>Maintenance List Details</h3>
    <div class="main_page">
        <div class="page_title">

        </div>
        <div class="page_body">
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
                            <label>MWO Code</label>
                            <input type="text" name="mwo_code" id="mwo_code" class="form-control"
                                placeholder="Enter MWO code" value="<?php if (isset($_GET['mwo_code']) && $_GET['mwo_code'] != '') {
                                    echo $_GET['mwo_code'];
                                } ?>">
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Status of Work</label>
                                <select class="form-control js-example-basic-multiple" name="status_of_work"
                                    id="status_of_work">
                                    <option value="" selected disabled>Select status of work</option>
                                    <option value="1" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '1') { ?>selected="selected" <?php } ?>>Completed
                                    </option>
                                    <option value="2" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '2') { ?>selected="selected" <?php } ?>>Pending
                                    </option>
                                    <option value="3" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '3') { ?>selected="selected" <?php } ?>>Reopen</option>
                                    <option value="4" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '4') { ?>selected="selected" <?php } ?>>Out of Scope
                                    </option>
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
                        <a href="<?= base_url() ?>maintenance_list_details" class="btn btn-sm btn-danger"
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
                            <th>Status of Work</th>
                            <th>Last Update Date</th>
                            <th>Last Updated By</th>
                            <th>Material used for maintenance</th>
                            <th>Material Cost</th>
                            <th>Total Labour Hour Involved</th>
                            <th>Labour Cost Per Hour</th>
                            <th>Total Cost</th>
                            <th>Plant Manager Approval Status</th>
                            <th>Remark of Plant Manager</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Complete Histroy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <div class="container ">
                    <!-- Data Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="">
                                <tr>
                                    <th>SR NO.</th>
                                    <th>MWO Code</th>
                                    <th>Plant Name</th>
                                    <th>Status of Work</th>
                                    <th>Last Update Date</th>
                                    <th>Last Updated By</th>
                                    <th>Used material</th>
                                    <th>Material Cost</th>
                                    <th>Total Labour Hour Involved</th>
                                    <th>Labour Cost Per Hour</th>
                                    <th>Total Cost</th>
                                    <th>Approval Status</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody >
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

<input type="hidden" name="search_mwo_code" id="search_mwo_code" value="<?php if (isset($_GET['mwo_code'])) {
    echo $_GET['mwo_code'];
} ?>">
<input type="hidden" name="approve_status" id="approve_status" value="<?php if (isset($_GET['approve_status'])) {
    echo $_GET['approve_status'];
} ?>">
<input type="hidden" name="search_status_of_work" id="search_status_of_work" value="<?php if (isset($_GET['status_of_work'])) {
    echo $_GET['status_of_work'];
} ?>">
<input type="hidden" name="search_material_used_for_maintenance" id="search_material_used_for_maintenance" value="<?php if (isset($_GET['material_used_for_maintenance'])) {
    echo $_GET['material_used_for_maintenance'];
} ?>">\
<input type="hidden" name="search_maintain_action" id="search_maintain_action" value="<?php if (isset($_GET['maintaince'])) {
    echo $_GET['maintaince'];
} ?>">

<input type="hidden" name="search_sub_category" id="search_sub_category" value="<?php if (isset($_GET['sub_category'])) {
    echo $_GET['sub_category'];
} ?>">


<?php include('footer.php'); ?>

<script>
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });
    $(document).ready(function () {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
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
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });

    $('#maintain_action').on('change', function () {
        var selected_master = $('#maintain_action').val();
        $('#sub_category').html('<option value="">Select Type</option>');
        $.ajax({
            type: "POST",
            url: "<?= base_url("admin/Ajax_controller/get_machine_types") ?>",
            data: {
                'selected_master': selected_master
            },
            success: function (response) {
                console.log(response);
                $("#sub_category").empty();
                $("#sub_category").empty();
                $('#sub_category').append('<option value="">Select Subcategory Maintenance</option>');
                var opts = $.parseJSON(response);
                console.log(opts);
                $.each(opts, function (i, d) {
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
    $(document).ready(function () {
        // $('#maintenance .child_menu').show();
        $('#maintenance').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.maintenance_list_detail').addClass('active_cc');
        // $('#maintenance').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            'ordering': false,
            'scrollX':true,
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Production Maintenance list',
                filename: 'production_maintenance_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                }
            }],
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_all_maintenance_list_details",
                type: "POST",
                data: function (d) {
                    d.search_date = $("#date").val();
                    d.search_mwo_code = $("#mwo_code").val();
                    d.search_status_of_work = $("#status_of_work").val();
                    d.search_material_used_for_maintenance = $("#material_used_for_maintenance").val();
                    d.search_maintain_action = $("#maintain_action").val();
                    d.search_sub_category = $("#sub_category").val();
                    d.approve_status = $("#approve_status").val();
                },
                complete: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                cache: false
            }
        });
        $('#maintenance_list').on('submit', function (e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
</script>
<script>
    function view_history(mwo_code) {
        $('#exampleModal').modal('show');
        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_complete_maintenance_list_details_ajax') ?>",
            method: "POST",
            data: {
                mwo_code: mwo_code
            },
            dataType: "json",
            success: function(response) {
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(index, record) {
                        tbody += `<tr>
                        <td>${record.sr_no}</td>
                        <td>${record.mwo_code}</td>
                        <td>${record.plant_name}</td>
                        <td>${record.status_of_work}</td>
                        <td>${record.date}</td>
                        <td>${record.first_name}</td>
                        <td>${record.material_used}</td>
                        <td>${record.material_cost}</td>
                        <td>${record.total_labour_hours}</td>
                        <td>${record.labour_cost_per_hour}</td>
                        <td>${record.total_cost}</td>
                        <td>${record.approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='13'>No history found.</td></tr>";
                }
                $("#exampleModal table tbody").html(tbody);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }
    
</script>