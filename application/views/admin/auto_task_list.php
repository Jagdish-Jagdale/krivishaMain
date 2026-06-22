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
<div class="right_col">
    <h3>Auto Task List</h3>
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
                        <!-- <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Task Action</label>
                                <select class="form-control js-example-basic-multiple" name="task_action" id="task_action">
                                    <option value="" selected disabled>Select Task Action</option>
                                    <option value="1" <?php if (isset($_GET['task_action']) && $_GET['task_action'] == '1') { ?>selected="selected" <?php } ?>>Forward to other
                                        Department/Person
                                    </option>
                                    <option value="2" <?php if (isset($_GET['task_action']) && $_GET['task_action'] == '2') { ?>selected="selected" <?php } ?>>Mark as Closed
                                    </option>

                                </select>
                            </div>
                        </div> -->


                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Task Department</label>
                                <select class="form-control js-example-basic-multiple" name="department" id="department">
                                    <option value="" selected disabled>Select Task Department</option>
                                    <option value="1" <?php if (isset($_GET['department']) && $_GET['department'] == '1') { ?>selected="selected" <?php } ?>>Create Order</option>
                                    <option value="2" <?php if (isset($_GET['department']) && $_GET['department'] == '2') { ?>selected="selected" <?php } ?>>Production Schedule</option>
                                    <option value="3" <?php if (isset($_GET['department']) && $_GET['department'] == '3') { ?>selected="selected" <?php } ?>>Maintenance</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Type Of Order</label>
                                <select class="form-control js-example-basic-multiple" name="type_of_order" id="type_of_order">
                                    <option value="" selected disabled>Select Type</option>
                                    <option value="1" <?php if (isset($_GET['type_of_order']) && $_GET['type_of_order'] == '1') { ?>selected="selected" <?php } ?>>Household
                                    </option>
                                    <option value="2" <?php if (isset($_GET['type_of_order']) && $_GET['type_of_order'] == '2') { ?>selected="selected" <?php } ?>>Container
                                    </option>
                                    <option value="3" <?php if (isset($_GET['type_of_order']) && $_GET['type_of_order'] == '3') { ?>selected="selected" <?php } ?>>Both
                                    </option>

                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <
                                        <option value="0" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '0') { ?>selected="selected" <?php } ?>>Pending
                                        </option>
                                        <option value="4" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '4') { ?>selected="selected" <?php } ?>>Completed
                                        </option>
                                        <option value="7" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '7') { ?>selected="selected" <?php } ?>>Printing Inprocess
                                        </option>
                                        <option value="8" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '8') { ?>selected="selected" <?php } ?>>Printing Completed
                                        </option>
                                        <option value="9" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '9') { ?>selected="selected" <?php } ?>>Dispatch Inprocess
                                        </option>
                                        <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '3') { ?>selected="selected" <?php } ?>>Partially Dispatched
                                        </option>




                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>auto_task_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
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
                            <th>Party Name</th>
                            <th>Created By</th>
                            <th>Created Date & Time</th>
                            <th>Task Department</th>
                            <th>Details Of Task</th>
                            <th>Type OF Order</th>
                            <th>Assign To Department</th>
                            <th>Assign To</th>
                            <th>Task Action</th>
                            <th>Task Status</th>
                            <th>Details of Enquiry</th>
                            <th>Action</th>
                        </tr>
                    </thead>


                </table>
            </div>
        </div>
        <div class="modal fade" id="mwoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="MwoModalLabel">Problem Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>MWO Code</th>
                                    <th>Plant Name</th>
                                    <th>Date</th>
                                    <th>Type Of Action</th>
                                    <th>Maintenance</th>
                                    <th>Sub Category</th>
                                    <th>Problem</th>
                                </tr>
                            </thead>
                            <tbody id="maintenance-details-table">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body content_body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <!-- <th><input class="brand d-none" type="checkbox" name="select_all" id="select_all"></th> -->
                                    <th>SR. NO.</th>
                                    <th>Order ID</th>
                                    <th>Group Of Article</th>
                                    <th>Type Of Article</th>
                                    <th class="brand d-none">Selected Brand</th>
                                    <th>Order Qty</th>
                                    <th class="brand d-none">Approved Qty</th>
                                    <th>Pending Qty</th>
                                    <th>Dispatched Qty</th>
                                    <th>Remark</th>
                                    <th>Order Status</th>
                                </tr>
                            </thead>
                            <tbody id="order-details-table">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="ProductionModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ProductionModalLabel">Order Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body content_body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>SR.NO.</th>
                                    <th>Schedule Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Plant</th>
                                    <th>Machine</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="production_scheduled_data">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="exampleModal_log" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
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

<input type="hidden" name="search_task_action" id="search_task_action" value="<?php if (isset($_GET['task_action'])) {
                                                                                    echo $_GET['task_action'];
                                                                                } ?>">
<input type="hidden" name="search_status_of_work" id="search_status_of_work" value="<?php if (isset($_GET['order_status'])) {
                                                                                        echo $_GET['order_status'];
                                                                                    } ?>">
<input type="hidden" name="search_maintain_action" id="search_maintain_action" value="<?php if (isset($_GET['department'])) {
                                                                                            echo $_GET['department'];
                                                                                        } ?>">
<input type="hidden" name="account_pending_task" id="account_pending_task" value="<?php if (isset($_GET['account_pending_task'])) {
                                                                                        echo $_GET['account_pending_task'];
                                                                                    } ?>">
<input type="hidden" name="type_of_task" id="type_of_task" value="<?php if (isset($_GET['type_of_order'])) {
                                                                        echo $_GET['type_of_order'];
                                                                    } ?>">
<input type="hidden" name="order_id" id="order_id" value="<?php if (isset($_GET['order_id'])) {
                                                                echo $_GET['order_id'];
                                                            } ?>">
<input type="hidden" name="production_pending_task" id="production_pending_task" value="<?php if (isset($_GET['production_pending_task'])) {
                                                                                            echo $_GET['production_pending_task'];
                                                                                        } ?>">
<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        // $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.auto_task_list').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
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
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "scrollX": true,
            "cache": false,
            dom: "lfrtip",
            ordering: false,
            scrollCollapse: true,
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'auto_task_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_auto_task_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.search_task_id = $('#search_task_action').val();
                    data.search_status_of_work = $('#search_status_of_work').val();
                    data.search_maintain_action = $('#search_maintain_action').val();
                    data.account_pending_task = $('#account_pending_task').val();
                    data.production_pending_task = $('#production_pending_task').val();
                    data.type_of_task = $('#type_of_task').val();
                    data.order_id = $('#order_id').val();
                },
            },
            "createdRow": function(row, data, dataIndex) {
                var memberId = data[6];
                var order = data[1];

                var order_type = data[5];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${order}', '${order_type}')" title="Group Of Article">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(6).html(eyeButton);

            },

            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function showOrderDetails(order, order_type) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_sub_order_task_details") ?>',
            type: 'POST',
            data: {
                'order_id': order,
                'order_type': order_type
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (order_type == 'Create Order') {
                    if (Array.isArray(response) && response.length > 0) {

                        $('#order-details-table').empty();
                        var tableContent = '';
                        var hasBrand = false;
                        response.forEach(function(item, index) {
                            if (item.brand_name && item.brand_name.trim() !== '') {
                                hasBrand = true;
                            }
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
                            } else if (item.order_status == '7') {
                                item.order_status = 'Printing Inprocess';
                            } else if (item.order_status == '8') {
                                item.order_status = 'Printing Completed';
                            } else if (item.order_status == '9') {
                                item.order_status = 'Dispatch Inprocess';
                            } else if (item.order_status == '10') {
                                item.order_status = 'Manually Closed';
                            } else {
                                item.order_status = 'Pending';
                            }
                            const approved_qty = item.approved_qty ? item.approved_qty : '0';
                            tableContent += `<tr>`;
                            // tableContent += `<td> <input class="brand d-none" type="checkbox" name="select_${index}" id="select-${index}"></td>`;
                            tableContent += `<td>${index + 1}</td>`;
                            tableContent += `<td>${item.order_id}</td>`;
                            tableContent += `<td>${item.group_of_article}</td>`;
                            tableContent += `<td>${item.article_name}</td>`;
                            tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                            tableContent += `<td>${item.order_quantity}</td>`;
                            tableContent += `<td class = "brand d-none">${approved_qty}</td>`;
                            tableContent += `<td>${item.pending_qty}</td>`;
                            tableContent += `<td>${item.dispatch_quantity}</td>`;
                            tableContent += `<td>${item.remark}</td>`;
                            tableContent += `<td>${item.order_status}</td>`;
                            tableContent += `</tr>`;
                        });

                        $('#order-details-table').html(tableContent);

                        $('#exampleModal').modal('show');
                    } else {
                        alert('No details found for this order!');
                    }
                    if (hasBrand) {
                        $('.brand').removeClass('d-none');
                    } else {
                        $('.brand').addClass('d-none');
                    }
                } else if (order_type == 'Production Schedule') {
                    if (Array.isArray(response) && response.length > 0) {

                        $('#production_scheduled_data').empty();
                        var tableContent = '';
                        response.forEach(function(item, index) {
                            tableContent += `<tr>`;
                            tableContent += `<td>${index + 1}</td>`;
                            tableContent += `<td>${item.date}</td>`;
                            tableContent += `<td>${item.production_schedule_start_time}</td>`;
                            tableContent += `<td>${item.production_schedule_end_time}</td>`;
                            tableContent += `<td>${item.plant_name}</td>`;
                            tableContent += `<td>${item.machine_name}</td>`;
                            tableContent += `<td>${item.qty}</td>`;
                            tableContent += `</tr>`;
                        });

                        $('#production_scheduled_data').html(tableContent);

                        $('#ProductionModal').modal('show');
                    } else {
                        alert('No details found for this order!');
                    }

                } else if (order_type == 'Maintenance') {

                    $('#maintenance-details-table').empty();

                    var tableContent = '';
                    var ajaxRequests = [];

                    response.forEach(function(item) {
                        const actionTypes = {
                            1: 'Emergency',
                            2: 'Online Breakdown',
                            3: 'Preventive',
                            4: 'Outside Work',
                            5: 'General',
                            6: 'Other'
                        };

                        const maintenanceTypes = {
                            1: 'Machine',
                            2: 'Mould/Article Name',
                            3: 'Printing Unit',
                            4: 'Plant',
                            5: 'Other'
                        };

                        const actionType = actionTypes[item.type_of_action] || '';
                        const maintaince = maintenanceTypes[item.maintaince] || '';
                        let maintaincee = item.maintaince;
                        let type = '';
                        if (maintaincee == 1) {
                            let ajaxRequest = $.ajax({
                                url: 'admin/Ajax_controller/get_task_type_of_machine',
                                method: 'POST',
                                data: {
                                    sub_type_id: item.sub_type_id,
                                    type: maintaincee
                                },
                                success: function(response) {
                                    var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                                    type = parsedResponse.machine_name;
                                    addRowToTable();
                                }
                            });
                            ajaxRequests.push(ajaxRequest);

                        } else if (maintaincee == 2) {
                            let ajaxRequest = $.ajax({
                                url: 'admin/Ajax_controller/get_task_type_of_article',
                                method: 'POST',
                                data: {
                                    sub_type_id: item.sub_type_id
                                },
                                success: function(response) {
                                    var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                                    type = parsedResponse.article_name;
                                    addRowToTable();
                                }
                            });
                            ajaxRequests.push(ajaxRequest);
                        } else if (maintaincee == 3) {
                            let ajaxRequest = $.ajax({
                                url: 'admin/Ajax_controller/get_task_type_of_machine',
                                method: 'POST',
                                data: {
                                    sub_type_id: item.sub_type_id,
                                    type: maintaincee
                                },
                                success: function(response) {
                                    var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                                    type = parsedResponse.machine_name;
                                    addRowToTable();
                                }
                            });
                            ajaxRequests.push(ajaxRequest);
                        } else if (maintaincee == 4) {
                            let ajaxRequest = $.ajax({
                                url: 'admin/Ajax_controller/get_task_type_of_plant',
                                method: 'POST',
                                data: {
                                    sub_type_id: item.sub_type_id
                                },
                                success: function(response) {
                                    var parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
                                    type = parsedResponse.plant_name;
                                    addRowToTable();
                                }
                            });
                            ajaxRequests.push(ajaxRequest);
                        } else if (maintaincee == 5) {
                            type = 'N/A'
                            addRowToTable();
                        }

                        function addRowToTable() {
                            tableContent += `<tr>`;
                            tableContent += `<td>${item.mwo_code}</td>`;
                            tableContent += `<td>${item.plant_name}</td>`;
                            tableContent += `<td>${item.date}</td>`;
                            tableContent += `<td>${actionType}</td>`;
                            tableContent += `<td>${maintaince}</td>`;
                            tableContent += `<td>${type}</td>`;
                            tableContent += `<td>${item.problems}</td>`;
                            tableContent += `</tr>`;
                        }
                    });

                    $.when.apply($, ajaxRequests).done(function() {
                        $('#maintenance-details-table').html(tableContent);
                        $('#mwoModal').modal('show');
                    });
                }
            },
        });
    }

    function showLog(task_id, order_ststus) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_all_task_history") ?>',
            type: 'POST',
            data: {
                task_id: task_id
            },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#log-details-table').empty();
                    var tableContent = '';
                    response.forEach(function(item, index) {
                        var taskAction = getTaskAction(item.task_action);
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
                        tableContent += `<td>${item.task_status==1 ? 'Pending' : 'Completed'}</td>`;
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
</script>

<script>
    $(document).ready(function() {
        $('#select_all').on('change', function() {
            $('input[type="checkbox"]').prop('checked', this.checked);
        });
    });

    $('#update_btn').on('click', function() {
        if ($('input[type="checkbox"]:checked').length === 0) {
            alert('Please select at least one checkbox.');
        } else {
            window.location.href = '<?= base_url() ?>update_auto_manual_task';
        }
    });
</script>
