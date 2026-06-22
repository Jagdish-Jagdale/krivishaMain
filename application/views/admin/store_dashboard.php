<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .data-table h5 {
        font-size: 15px;
    }

    .col-md-3 {
        width: 20%;
    }

    .date-pick {
        overflow-y: scroll;
        max-height: 385px;
    }
</style>
<div class="right_col">
    <div class="filter-section">
        <div class="row">
            <form id="filterForm" method="GET" action="dashboard">
                <input type="hidden" name="dashboard_option" id="dashboard_option" value="<?= $dashboard_option ?>">
                <div class="row">
                    <!-- From Date -->
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                        <label>Date Range</label>
                        <input name="date" id="date" class="form-control datepickers" placeholder="Select Date Range"
                            value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                // Display the selected date range from the URL
                                echo $_GET['date'];
                            } ?>">
                    </div>
                    <!-- plant filter -->
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                        <label class="form-label">Plant</label>
                        <select class="form-select filter-select" id="plant_id" name="plant_id" onchange="submitForm()">
                            <option value="">Select Plant</option>
                            <?php if (!empty($plant)) {
                                foreach ($plant as $plant_result) { ?>
                                    <option value="<?= $plant_result->id ?>" <?php if (isset($_GET['plant_id']) && $_GET['plant_id'] == $plant_result->id) { ?>selected<?php } ?>>
                                        <?= $plant_result->plant_name ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                        <label class="form-label">Machine</label>
                        <select class="form-select filter-select" id="machineFilter" name="machine"
                            onchange="submitForm()">
                            <option value="">Select Machine</option>
                            <?php if (!empty($machine)) {
                                foreach ($machine as $machine_result) { ?>
                                    <option value="<?= $machine_result->id ?>" <?php if (isset($_GET['machine']) && $_GET['machine'] == $machine_result->id) { ?>selected<?php } ?>>
                                        <?= $machine_result->machine_name ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <button type="button" id="removeFiltersBtn" class="btn btn-danger btn-sm">Clear Filters</button>
            </form>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="metric-card color-5">
                <div class="metric-title">Fully Dispatched</div>
                <div class="metric-value"><?= $store_metrics['fully_dispatched_items_count'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card color-10">
                <div class="metric-title">Partially Dispatched</div>
                <div class="metric-value"><?= $store_metrics['partially_dispatched_items_count'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card color-7">
                <div class="metric-title">Pending (Nothing Dispatched)</div>
                <div class="metric-value"><?= $store_metrics['pending_dispatched_items_count'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="<?= base_url('material_artical_requistition_to_list?miscellaneous=1&order_status=1') ?>" class="metric-link"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-9">
                    <div class="metric-title">Miscellaneous Item Pending Dispatchs</div>
                    <div class="metric-value"><?= $store_metrics['miscellaneous_pending_dispatch'] ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            
            <a href="<?= base_url('task_list?date=&status_of_work=1&store=24') ?>" class="metric-link"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-4">
                    <div class="metric-title">Pending Tasks Counter</div>
                    <div class="metric-value"><?= $store_metrics['manual_pending_store_task'] ?></div>
                </div>
            </a>
        </div>
    </div>

    <!-- Machine wise requirement  -->
    <div class="data-table mt-4">
        <h5>Machine wise Requirement Snapshot</h5>

        <div class="table-container">
            <table id="machineWiseRequirement">
                <thead>
                    <tr>
                        <th>Machine</th>
                        <th>RM</th>
                        <th>Masterbatch</th>
                        <th>Other RM</th>
                        <th>Packing Bags Inner Qty</th>
                        <th>Packing Bag Outer Qty</th>
                        <th>Sticker Qty</th>
                        <th>Cap Qty</th>
                        <th>Can Ear</th>
                        <th>Handle</th>
                        <th>Rubber Bush</th>
                        <th>Wiser</th>
                    </tr>
                </thead>
                <tbody id="machineRequirementTable">

                </tbody>
                <tfoot>
                    <tr>
                        <th>Grand Total</th>
                        <th></th> <!-- RM -->
                        <th></th> <!-- Masterbatch -->
                        <th></th> <!-- Other RM -->
                        <th></th> <!-- Packing Bags Inner Qty -->
                        <th></th> <!-- Packing Bags Outer Qty -->
                        <th></th> <!-- Sticker Qty -->
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>
    <!-- inventory -->
    <div class="row ">
        <div class="col-md-6">
            <div class="data-table mt-4 ">
                <h5>Inventory Table view</h5>
                <div class="table-container">
                    <table id="inventory">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th>Available</th>
                                <th>Required</th>
                                <th>Delta</th>

                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="data-table mt-4">
                <h5>Maintenance Request Log</h5>
                <div class="table-container">
                    <table id="machine_request_log">
                        <thead>
                            <tr>
                                <th>Sr.No.</th>
                                <th>Request No.</th>
                                <th>Machine Name</th>
                                <th>Plant</th>
                                <th>Date</th>
                                <th>RM Name</th>
                                <th>Required RM</th>
                                <th>Received RM</th>

                            </tr>
                        </thead>
                        <tbody id="machine_request_log_table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row ">
        <div class="col-md-6">
            <div class="data-table mt-4 mb-5">
                <h5>BOM Table view</h5>
                <div class="table-container">
                    <table id="bom_table">
                        <thead>
                            <tr>
                                <th>BOM</th>
                                <th>Field 1</th>
                                <th>Field 2</th>
                                <th>Field 3</th>
                                <th>Field 4</th>
                                <th>Field 5</th>

                            </tr>
                        </thead>
                        <tbody id="machineTable">
                            <tr>
                                <td>1</td>
                                <td>15</td>
                                <td>20</td>
                                <td>30</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>2</td>
                                <td>12</td>
                                <td>27</td>
                                <td>23</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>3</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>4</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>5</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>6</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>7</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>8</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>9</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>
                            <tr>
                                <td>10</td>
                                <td>14</td>
                                <td>20</td>
                                <td>20</td>
                                <td>20</td>
                                <td>30</td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="col-12 col-md-6 mb-5">
            <div class="data-table mt-4">

                <div class="top-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="date-box">
                        <span id="selected-date"></span>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-12 col-xs-12 d-3 mb-3 form-group">

                        <input name="calender_date" id="calender_date" class="form-control singledatepickers mt-2"
                            placeholder="Pick Date" value="">
                    </div>


                </div>
                <div class="row date-pick">
                    <!-- Schedule cards will be dynamically inserted here -->
                </div>

            </div>
        </div>

    </div>



</div>
<input type="hidden" id="plant_id_filter" name="plant_id_filter" value="<?= $_GET['plant_id'] ?? '' ?>">
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {

        $('.filter-select').select2({
            theme: 'default',
            width: '100%'
        });

    });
</script>
<script>
    function submitForm() {
        var dashboard_option = document.getElementById('dashboard_option').value;
        if (dashboard_option == '0') {
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard";
            form.submit();
        } else {
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard?dashboard_option=store_dashboard";
            form.submit();
        }
    }
    document.getElementById('removeFiltersBtn').addEventListener('click', function () {
        var dashboard_option = document.getElementById('dashboard_option').value;
        if (dashboard_option == '0') {
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        } else {
            var url = "<?= base_url() ?>dashboard?dashboard_option=store_dashboard";
            window.location.href = url;
        }

    });
    document.addEventListener('DOMContentLoaded', function () {
        function checkFilters() {
            let date = document.querySelector('input[name="date"]').value;
            let plant_id = document.querySelector('select[name="plant_id"]').value;
            let machine = document.querySelector('select[name="machine"]').value;

            if (date || machine || plant_id) {
                document.getElementById('removeFiltersBtn').style.display = 'inline-block';
            } else {
                document.getElementById('removeFiltersBtn').style.display = 'none';
            }
        }

        checkFilters();

        document.querySelector('input[name="date"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="machine"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="plant_id"]').addEventListener('change', checkFilters);
        document.getElementById('removeFiltersBtn').addEventListener('click', function () {
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        });
    });



</script>


<script>
    flatpickr("#date", {
        mode: "range",
        dateFormat: "d-m-Y", // Format: Day-Month-Year
        locale: 'en', // Adjust to your desired locale
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                var formattedDate = selectedDates[0].toLocaleDateString('en-GB') + ' to ' + selectedDates[1]
                    .toLocaleDateString('en-GB');
                document.getElementById("date").value = formattedDate;
                submitForm();
            }
        },
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });

</script>

<script>
    $(document).ready(function () {
        var table = $('#machineWiseRequirement').DataTable({
            "lengthChange": true,
            "responsive": false,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": false,
            "cache": false,
            "order": [],
            "ordering": false,
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Machine Data List',
                filename: 'machine_wise_requirement_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                }
            }],
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_machine_wise_requirement_store_dashboard",
                type: 'POST',
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.machine_id = $('#machineFilter').val();
                    d.date = $('#date').val();
                    d.raw_material_id = $('#raw_material_id').val();
                },
                dataSrc: 'data'
            },

            // ✅ Define columns explicitly
            columns: [
                { data: "machine_name" },
                { data: "total_rm" },
                { data: "total_master_batch" },
                { data: "total_other_rm" },
                { data: "packing_bag_inner" },
                { data: "packing_bag_outer" },
                { data: "sticker" },
                { data: "cap" },
                { data: "can_ear" },
                { data: "handle" },
                { data: "rubber_bush" },
                { data: "wiser" }
            ],

            scrollCollapse: true,

            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                var intVal = function (i) {
                    return typeof i === 'string'
                        ? i.replace(/[\$,]/g, '') * 1
                        : typeof i === 'number'
                            ? i
                            : 0;
                };
                var cols = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                cols.forEach(function (colIndex) {
                    var total = api
                        .column(colIndex)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    $(api.column(colIndex).footer()).html(total.toLocaleString(undefined, { maximumFractionDigits: 2 }));
                });
            }
        });

    });
</script>


<script>
    $(document).ready(function () {
        var table = $('#bom_tabl').DataTable({
            "lengthChange": true,
            "responsive": false,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": false,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Machine Data List',
                filename: 'machine_wise_tabular_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }],
            scrollCollapse: true,

        });
    });
</script>
<script>
    $(document).ready(function () {
        var table = $('#machine_request_log').DataTable({
            lengthChange: true,
            responsive: false,
            lengthMenu: [10, 25, 50, 100],
            searching: true,
            processing: true,
            serverSide: false,
            cache: false,
            order: [],
            ordering: false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Machine Data List',
                filename: 'machine_request_log',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            }],

            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_machine_request_log_store_dashboard",
                type: 'POST',
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.machine_id = $('#machineFilter').val();
                    d.date = $('#date').val();
                    d.raw_material_id = $('#raw_material_id').val();
                },
                dataSrc: 'data'
            },

            columns: [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; } }, // Sr.No.
                { data: "request_no" },
                { data: "machine_name" },
                { data: "plant_name" },
                { data: "request_date" },
                { data: "rm_name" },
                { data: "request_quantity" },
                { data: "received_qty" }
            ],

            scrollCollapse: true,
        });
    });

</script>


<script>
    $(document).ready(function () {
        var table = $('#bom_table').DataTable({
            "lengthChange": true,
            "responsive": false,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": false,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Machine Data List',
                filename: 'machine_wise_tabular_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }],
            scrollCollapse: true,

        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#inventory').DataTable({
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_negative_stock_alert",
                type: 'POST',
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.raw_material_id = $('#raw_material_id').val();
                },
                dataSrc: 'data'
            },
            columns: [
                { data: 'rm_name' },
                { data: 'available' },
                { data: 'required' },
                { data: 'delta' }
            ],
            createdRow: function (row, data) {
                let color = '';
                if (data.delta < 0) color = 'rgba(255,0,0,0.3)';
                else if (data.delta == 0) color = 'rgba(255,255,0,0.4)';
                else color = 'rgba(0,255,0,0.3)';
                $(row).css('background-color', color);
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        function loadSchedules(date) {
            var plant_id = $('#plant_id').val();
            var machine_id = $('#machineFilter').val();
            $.ajax({
                url: "<?= base_url() ?>admin/Ajax_controller/get_all_production_schedules_for_store_dashboard",
                type: 'POST',
                data: JSON.stringify({ selected_date: date, plant_id: plant_id, machine_id: machine_id }),
                success: function (response) {
                    let res = JSON.parse(response);
                    let container = $('.date-pick');
                    container.html('');

                    if (res.status === 'true') {
                        res.data.forEach(function (item) {
                            let progress = item.qty > 0
                                ? ((item.total_achieve_qty / item.qty) * 100).toFixed(1)
                                : 0;

                            let progressClass = progress > 0 ? 'text-success' : 'text-danger';
                            container.append(`
                            <div class="col-md-6">
                                <div class="task-card">
                                    <div class="task-header d-flex justify-content-between align-items-center">
                                        <div class="task-time"><i class="bi bi-clock"></i> ${item.production_schedule_start_time} - ${item.production_schedule_end_time}</div>
                                        <div class="task-progress ${progressClass} fw-bold">${progress}%</div>
                                    </div>
                                    <div class="task-body">
                                        <h6 class="task-title">${item.machine_name}</h6>
                                        <p class="task-subtitle">${item.article_name}</p>
                                        <div class="d-flex justify-content-between">
                                            <div class="task-box schedule-box">
                                                <small>Schedule</small>
                                                <h5>${item.qty}</h5>
                                            </div>
                                            <div class="task-box produced-box">
                                                <small>Produced</small>
                                                <h5>${item.total_achieve_qty}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                        });
                    } else {
                        container.html('<div class="col-12 text-center text-muted">No schedules found</div>');
                    }
                }
            });
        }
        loadSchedules();

        $('#calender_date').on('change', function () {
            let selectedDate = $(this).val();
            loadSchedules(selectedDate);
        });
    });
</script>