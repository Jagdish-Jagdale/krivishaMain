<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .filter-select {
        display: none;
    }

    .data-table h5 {
        font-size: 15px;
    }

    .select2-container {
        max-width: 100% !important;
    }

    .col-md-3 {
        width: 20%;
    }

    .date-pick {
        overflow-y: scroll;
        max-height: 385px;
    }

    .color-a {
        background: #e6b8af;

    }

    .color-b {
        background: #d9ead3;

    }

    .color-c {
        background: #00ff00;

    }

/* Modern & Premium Aesthetic Overrides for Purchase Dashboard Metric Cards */
.purchase-dashboard .metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.purchase-dashboard .metric-card {
    position: relative;
    border-radius: 16px;
    padding: 22px;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px !important;
    height: 100%;
    text-align: left;
}

/* Decorative radial glow effect in top right */
.purchase-dashboard .metric-card::before {
    content: "";
    position: absolute;
    top: -20px;
    right: -20px;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    pointer-events: none;
    transition: all 0.5s ease;
    z-index: 1;
}

.purchase-dashboard .metric-card:hover::before {
    transform: scale(1.8);
    background: rgba(255, 255, 255, 0.18);
}

.purchase-dashboard .metric-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.14), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}

/* Semantic High-End Gradients */
.purchase-dashboard .metric-card.pending-tasks {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
}
.purchase-dashboard .metric-card.rm-consumption {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
}
.purchase-dashboard .metric-card.mb-consumption {
    background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%) !important;
}

/* Card Typography Adjustment */
.purchase-dashboard .metric-card h3 {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9) !important;
    margin: 0 0 14px 0 !important;
    line-height: 1.3 !important;
    letter-spacing: 0.01em;
    max-width: 75%;
    z-index: 2;
    text-transform: uppercase;
}

.purchase-dashboard .metric-card .metric-value {
    font-size: 32px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    margin: 0 !important;
    line-height: 1 !important;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    z-index: 2;
}

/* Glass Icon styling */
.purchase-dashboard .metric-card .metric-icon {
    position: absolute !important;
    top: 18px !important;
    right: 18px !important;
    background: rgba(255, 255, 255, 0.18) !important;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 2;
}

.purchase-dashboard .metric-card .metric-icon i {
    font-size: 18px !important;
    color: #ffffff !important;
}

.purchase-dashboard .metric-card:hover .metric-icon {
    background: rgba(255, 255, 255, 0.28) !important;
    transform: rotate(8deg) scale(1.08);
}

/* Reset internal absolute position rules from layout styles */
.purchase-dashboard .metric-card a.metric-link {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    height: 100%;
    width: 100%;
    text-decoration: none !important;
    color: #ffffff !important;
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
                        <label class="form-label">Vendor Name</label>
                        <select class="form-select filter-select" id="party_id" name="party_id" onchange="submitForm()">
                            <option value="">Select Vendor</option>
                            <?php if (!empty($party)) {
                                foreach ($party as $party_result) { ?>
                                    <option value="<?= $party_result->id ?>" <?php if (isset($_GET['party_id']) && $_GET['party_id'] == $party_result->id) { ?>selected<?php } ?>>
                                        <?= $party_result->party_name ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                        <label class="form-label">Raw Material</label>
                        <select class="form-select filter-select" id="raw_material_id" name="raw_material_id"
                            onchange="submitForm()">
                            <option value="">Select Raw Material</option>
                            <?php if (!empty($raw_material)) {
                                foreach ($raw_material as $rm_result) { ?>
                                    <option value="<?= $rm_result->id ?>" <?php if (isset($_GET['raw_material_id']) && $_GET['raw_material_id'] == $rm_result->id) { ?>selected<?php } ?>>
                                        <?= $rm_result->rm_name ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <!-- <div class="col-lg-4 col-md-6 col-sm-12 d-3 mb-3 form-group">
                        <label class="form-label">Total Value Of Purchase </label>
                        <input name="number" class="form-control " placeholder="Enter value" value="">

                    </div> -->
                </div>
                <button type="button" id="removeFiltersBtn" class="btn btn-danger btn-sm">Clear Filters</button>
            </form>
        </div>
    </div>
    <div class="dashboard purchase-dashboard">
        <div class="metrics-row">
            <div class="metric-card pending-tasks">
                <a href="<?= base_url('task_list?date=&status_of_work=1&purchase=19') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-tasks"></i></div>
                    <h3>Pending Tasks Counter</h3>
                    <div class="metric-value"><?= $purchase_metrics['pending_tasks'] ?? 0 ?></div>
                </a>
            </div>

            <div class="metric-card rm-consumption">
                <a href="<?= base_url('material_reorder_report?type=1&material_id=') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-layer-group"></i></div>
                    <h3>RM Stock Level</h3>
                    <div class="metric-value"><?= $purchase_metrics['rm_stock_level'] ?? 0 ?></div>
                </a>
            </div>

            <div class="metric-card mb-consumption">
                <a href="<?= base_url('material_reorder_report?type=0&material_id=') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-boxes"></i></div>
                    <h3>Article Stock Level</h3>
                    <div class="metric-value"><?= $purchase_metrics['article_stock_level'] ?? 0 ?></div>
                </a>
            </div>
        </div>
    </div>
    <!-- Item Purchase -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="data-table mt-4 ">
                <h5>Item Purchase Table</h5>
                <div class="table-container">
                    <table id="item_purchaseTable">
                        <thead>
                            <tr>
                                <th>Top 10 RM</th>
                                <th>Qty</th>
                                <th>Bottom 10 RM</th>
                                <th>Qty</th>

                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="data-table mt-4">
                <h5> Fast-moving vs Over-purchased items</h5>
                <div class="table-container">
                    <table id="fast_and_over_moving_table">
                        <thead>
                            <tr>
                                <th>Max Purchase RM</th>
                                <th>Qty</th>
                                <th>Over Purchase RM </th>
                                <th>Qty</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end -->

    <!-- vendor wise requirement  -->
    <div class="data-table mt-4">
        <h5> Vendors by volume/value </h5>
        <div class="table-container">
            <table id="example">
                <thead>
                    <tr>
                        <th>Party Name</th>
                        <th>Party Address</th>
                        <th>Mobile Number </th>
                        <th>Value</th>
                        <th>Volumn</th>
                        <th>Date of Inward</th>

                    </tr>
                </thead>
                <tbody id="VendorTable">

                </tbody>
            </table>
        </div>
    </div>
    <!-- end -->


    <!-- negative or short fall of stock alert-->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="data-table mt-4 ">
                <h5>Negative / Shortfall Stock Alert (From 1 Nov 2025)</h5>
                <div class="table-container">
                    <table id="item_purchase" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th>Available</th>
                                <th>Required</th>
                                <th>Delta</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="data-table mt-4 ">
                <h5>Negative / Shortfall Stock Alert (Before 1 Nov 2025)</h5>
                <div class="table-container">
                    <table id="item_purchase_old" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th>Available</th>
                                <th>Required</th>
                                <th>Delta</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<div class="row mb-3">
    <div class="col-md-12">
            <div class="data-table mt-4">
                <h5>Raw Material Purchase Trend Graph</h5>
                <canvas id="purchaseTrendChart" height="120"></canvas>
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
            form.action = "<?= base_url() ?>dashboard?dashboard_option=purchase_dashboard";
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
            var url = "<?= base_url() ?>dashboard?dashboard_option=purchase_dashboard";
            window.location.href = url;
        }

    });
    document.addEventListener('DOMContentLoaded', function () {
        function checkFilters() {
            let date = document.querySelector('input[name="date"]').value;
            let machine = document.querySelector('select[name="machine"]').value;

            if (date || machine) {
                document.getElementById('removeFiltersBtn').style.display = 'inline-block';
            } else {
                document.getElementById('removeFiltersBtn').style.display = 'none';
            }
        }

        checkFilters();

        document.querySelector('input[name="date"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="machine"]').addEventListener('change', checkFilters);
        document.getElementById('removeFiltersBtn').addEventListener('click', function () {
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        });
    });



</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const ctx = document.getElementById('purchaseTrendChart').getContext('2d');

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Purchase Quantity',
                    data: Array(12).fill(0),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true },
                    x: {}
                }
            }
        });

        function loadChartData(raw_material_id, plant_id) {
            $.ajax({
                url: "<?= base_url('admin/Ajax_controller/get_monthly_purchase_data') ?>",
                type: "POST",
                data: {
                    raw_material_id: raw_material_id,
                    plant_id: plant_id,
                    date: $('#date').val()   // ✅ date filter
                },
                dataType: "json",
                success: function (response) {
                    chart.data.datasets[0].data = response;
                    chart.update();
                }
            });
        }

        function refreshChart() {
            const raw_material_id = $('#raw_material_id').val();
            const plant_id = $('#plant_id').val();

            loadChartData(raw_material_id, plant_id);
        }

        $('#raw_material_id, #plant_id, #date').on('change', refreshChart);

        refreshChart(); // initial load
    });
</script>
<script>
    flatpickr("#date", {
        mode: "range",
        dateFormat: "d-m-Y",
        locale: 'en',
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

    $(document).ready(function () {
        $('.filter-select').select2({

        });
    });
</script>


<script>
    $(document).ready(function () {
        var table = $('#item_purchaseTable').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,   // 🔥 important
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_rm_item_stock_purchase_list",
                type: "POST",
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.date = $('#date').val();
                    d.raw_material_id = $('#raw_material_id').val();
                },
                dataSrc: 'data'
            },
            columns: [
                { title: "Top 10 RM" },
                { title: "Qty" },
                { title: "Bottom 10 RM" },
                { title: "Qty" }
            ],
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                title: 'Item Purchase List',
                filename: 'item_purchase_tabular_list'
            }]
        });

        // 🔁 reload on filter change
        $('#plant_id, #raw_material_id').on('change', function () {
            table.ajax.reload();
        });
    });



    $(document).ready(function () {
        $('#item_purchase').DataTable({
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_negative_stock_alert",
                type: 'POST',
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.date = $('#date').val();
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
    $(document).ready(function () {
        $('#item_purchase_old').DataTable({
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_negative_stock_alert_old",
                type: 'POST',
                data: function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.date = $('#date').val();
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
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "lengthMenu": [10, 25, 50, 100],
            "searching": true,
            "processing": true,
            "serverSide": false,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Vendors by volume/value',
                filename: 'vendors_by_volume_value',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_vendors_by_raw_material",
                "type": "POST",
                "data": function (d) {
                    d.plant_id = $('#plant_id').val();
                    d.raw_material_id = $('#raw_material_id').val();
                    d.date = $('#date').val();
                    d.party_id = $('#party_id').val();
                },
                "dataSrc": "data"
            },

            "columns": [
                { "data": "party_name" },
                { "data": "party_address" },
                { "data": "mobile_number" },
                { "data": "total_value" },
                { "data": "total_volume" },
                { "data": "inward_date" }
            ],
        });

        // 🔄 Reload table dynamically when filters change
        $('#plant_id, #raw_material_id').on('change', function () {
            table.ajax.reload();
        });
    });

    $(document).ready(function () {
        var table = $('#fast_and_over_moving_table').DataTable({
            "processing": true,
            "serverSide": false,
            "ordering": false,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_fast_and_over_moving_list",
                "type": "POST",
                "data": function (d) {
                    d.date = $('#date').val();
                    d.plant_id = $('#plant_id').val();
                    d.raw_material_id = $('#raw_material_id').val();

                }
            },
            "columns": [
                { "data": "fast_rm" },
                { "data": "fast_qty" },
                { "data": "over_rm" },
                { "data": "over_qty" }
            ]
        });
    });
</script>




<script>
    $(document).ready(function () {
        const $dateSpan = $("#selected-date");
        const today = new Date();
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        };
        $dateSpan.text(today.toLocaleDateString('en-GB', options));



    });

</script>