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

    .modelclass {
        max-width: 60%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
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
    <h3>Manual Task List</h3>
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
                            <div class="form-group">
                                <label>Task Head</label>
                                <select class="form-control js-example-basic-multiple" name="task_head" id="task_head">
                                    <option value="" selected disabled>Select Task Head</option>
                                    <option value="1" <?php if (isset($_GET['task_head']) && $_GET['task_head'] == '1') { ?>selected="selected" <?php } ?>>Enquiry
                                    </option>
                                    <option value="2" <?php if (isset($_GET['task_head']) && $_GET['task_head'] == '2') { ?>selected="selected" <?php } ?>>Cold Call</option>
                                    <option value="3" <?php if (isset($_GET['task_head']) && $_GET['task_head'] == '3') { ?>selected="selected" <?php } ?>>Office Requirement
                                    </option>
                                    <option value="4" <?php if (isset($_GET['task_head']) && $_GET['task_head'] == '4') { ?>selected="selected" <?php } ?>>Self Task</option>
                                    <option value="5" <?php if (isset($_GET['task_head']) && $_GET['task_head'] == '5') { ?>selected="selected" <?php } ?>>Complaint
                                    </option>

                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Task Priority</label>
                                <select class="form-control js-example-basic-multiple" name="priority" id="priority">
                                    <option value="" selected disabled>Select Task Priority</option>
                                    <option value="1" <?php if (isset($_GET['priority']) && $_GET['priority'] == '1') { ?>selected="selected" <?php } ?>>High
                                    </option>
                                    <option value="2" <?php if (isset($_GET['priority']) && $_GET['priority'] == '2') { ?>selected="selected" <?php } ?>>Medium
                                    </option>
                                    <option value="3" <?php if (isset($_GET['priority']) && $_GET['priority'] == '3') { ?>selected="selected" <?php } ?>>Low
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Task Status</label>
                                <select class="form-control js-example-basic-multiple" name="status_of_work"
                                    id="status_of_work">
                                    <option value="" selected disabled>Select Status Of Task</option>
                                    <option value="2" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '2') { ?>selected="selected" <?php } ?>>Completed
                                    </option>
                                    <option value="1" <?php if (isset($_GET['status_of_work']) && $_GET['status_of_work'] == '1') { ?>selected="selected" <?php } ?>>Pending
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>task_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Task ID</th>
                            <th>Created By</th>
                            <th>Task Head</th>
                            <th>Party Name </th>
                            <th>Complete By Date</th>
                            <th>Complete By Time</th>
                            <th>Priority</th>
                            <th>
                                Additional Comments/ Updates
                            </th>
                            <th>Assign To Department</th>
                            <th>Assign To</th>
                            <th>Task Status</th>
                            <th>Task Action</th>
                            <th>Details of Enquiry</th>
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
<div class="modal fade" id="exampleModal_log" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Task History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Last Updated Date</th>
                            <th>Last Updated By</th>
                            <th>Assign To Department</th>
                            <th>Assign To</th>
                            <th>Task Action</th>
                            <th>Task Status</th>
                            <th>Details of Enquiry</th>
                        </tr>
                    </thead>
                    <tbody id="log-details-table">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
    echo $_GET['date'];
} ?>">

<input type="hidden" name="search_task_head" id="search_task_head" value="<?php if (isset($_GET['task_head'])) {
    echo $_GET['task_head'];
} ?>">
<input type="hidden" name="search_priority" id="search_priority" value="<?php if (isset($_GET['priority'])) {
    echo $_GET['priority'];
} ?>">
<input type="hidden" name="search_status_of_work" id="search_status_of_work" value="<?php if (isset($_GET['status_of_work'])) {
    echo $_GET['status_of_work'];
} ?>">
<input type="hidden" name="account" id="account" value="<?php if (isset($_GET['account'])) {
    echo $_GET['account'];
} ?>">
<input type="hidden" name="store" id="store" value="<?php if (isset($_GET['store'])) {
    echo $_GET['store'];
} ?>">
<input type="hidden" name="purchase" id="purchase" value="<?php if (isset($_GET['purchase'])) {
    echo $_GET['purchase'];
} ?>">
<input type="hidden" name="super_admin_task" id="super_admin_task" value="<?php if (isset($_GET['super_admin_task'])) {
    echo $_GET['super_admin_task'];
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
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });
</script>
<script>
    $(document).ready(function () {
        // $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.task_list').addClass('active_cc');
        // $('#task_management').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "scrollX": true,
            "cache": false,
            dom: "Blfrtip",
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'task_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                }
            }],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_manual_task_list",
                "type": "POST",
                "data": function (data) {
                    data.search_date = $('#search_date').val();
                    data.search_task_head = $('#search_task_head').val();
                    data.search_priority = $('#search_priority').val();
                    data.search_status_of_work = $('#search_status_of_work').val();
                    data.store = $('#store').val();
                    data.account = $('#account').val();
                    data.purchase = $('#purchase').val();
                    data.super_admin_task = $('#super_admin_task').val();
                },
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function showLog(task_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_all_manual_task_history") ?>',
            type: 'POST',
            data: {
                task_id: task_id
            },
            dataType: 'json',
            success: function (response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#log-details-table').empty();
                    var tableContent = '';
                    response.forEach(function (item, index) {
                        var taskAction = getTaskAction(item.task_action);
                        var taskStatus = getTaskStatus(item.task_status);
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        const date = new Date(item.created_on);
                        const istDate = new Date(date.getTime() + (4 * 60 * 60 * 1000) + (30 * 60 * 1000)); // Add 270 minutes (4 hours 30 minutes) to convert to IST
                        const formattedDate = istDate.toLocaleDateString('en-GB').replace(/\//g, '-');
                        const formattedTime = istDate.toLocaleTimeString('en-GB', {
                            hour: '2-digit',
                            minute: '2-digit',
                            // second: '2-digit',
                            hour12: true // AM/PM format
                        });

                        tableContent += `<td>${formattedDate} ${formattedTime}</td>`;
                        tableContent += `<td>${item.last_updated_name || ''}</td>`;
                        tableContent += `<td>${item.department || ''}</td>`;
                        tableContent += `<td>${item.first_name || ''}</td>`;
                        tableContent += `<td>${taskAction || ''}</td>`;
                        tableContent += `<td>${taskStatus || ''}</td>`;
                        tableContent += `<td>${item.details_of_task || ''}</td>`;
                        tableContent += `</tr>`;
                    });
                    $('#log-details-table').html(tableContent);

                    $('#exampleModal_log').modal('show');
                } else {
                    alert('No History found for this order!');
                }

            },
        });
    }

    function getTaskAction(action_id) {
        const actions = {
            1: 'Forward to other Department/Person',
            2: 'Mark as Closed',
            3: 'Create Order'
        };
        return actions[action_id] || 'Forward to other Department/Person';
    }

    function getTaskStatus(status_id) {
        const statuses = {
            1: 'Pending',
            2: 'Complete'
        };
        return statuses[status_id] || 'Pending';
    }
</script>