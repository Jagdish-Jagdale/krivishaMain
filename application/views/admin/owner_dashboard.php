<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
Chart.register(ChartDataLabels);
console.log('ChartDataLabels registered:', ChartDataLabels);
// Chart.js default configuration for better visibility
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#2c343d';
    Chart.defaults.font.family = "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.font.weight = '500';
    // Disable datalabels by default, enable per chart
    Chart.defaults.plugins.datalabels.display = false;
    console.log('Chart defaults configured');
}
</script>
<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .select2-container {
        max-width: 100% !important;
    }

    /* Premium Krivisha Dashboard Card Info Tooltips */
    .card-info-icon {
        position: relative;
        cursor: pointer;
        font-size: 13px;
        color: rgba(30, 41, 59, 0.45); /* Elegant semi-transparent slate grey */
        transition: all 0.2s ease;
        margin-left: 6px;
        display: inline-flex;
        align-items: center;
        width: 16px;
        height: 16px;
        justify-content: center;
        border-radius: 50%;
        background: rgba(30, 41, 59, 0.05);
    }
    
    .card-info-icon:hover {
        color: #0f172a;
        background: rgba(30, 41, 59, 0.12);
        transform: scale(1.1);
    }

    /* Tooltip container styling */
    .card-info-icon .tooltip-text {
        visibility: hidden;
        width: 280px;
        background: rgba(15, 23, 42, 0.96); /* Premium deep slate */
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #ffffff;
        text-align: left;
        border-radius: 12px;
        padding: 14px;
        position: absolute;
        z-index: 99999;
        bottom: 135%; /* Position above the icon */
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        font-weight: 400;
        text-transform: none; /* Reset uppercase inherited from card title */
        letter-spacing: normal;
        pointer-events: none;
    }

    /* Tooltip arrow */
    .card-info-icon .tooltip-text::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -6px;
        border-width: 6px;
        border-style: solid;
        border-color: rgba(15, 23, 42, 0.96) transparent transparent transparent;
    }

    .card-info-icon:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(-6px);
    }

    .card-info-icon .tooltip-text h4 {
        margin: 0 0 6px 0;
        font-size: 12.5px;
        font-weight: 700;
        color: #38bdf8; /* Sleek sky blue accent */
        display: flex;
        align-items: center;
        gap: 6px;
        line-height: 1.2;
    }
    
    .card-info-icon .tooltip-text h4 i {
        font-size: 12px;
        color: #38bdf8;
    }

    .card-info-icon .tooltip-text p {
        margin: 0 0 8px 0;
        color: #cbd5e1;
        font-weight: 400;
    }

    .card-info-icon .tooltip-text .source-info {
        font-size: 9px;
        font-weight: 600;
        color: #94a3b8;
        background: rgba(255, 255, 255, 0.08);
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    /* Override hidden overflow on cards to allow popup tooltips to display fully */
    .metric-card {
        overflow: visible !important;
        position: relative;
    }
</style>

<div class="right_col">
    <!-- Filters -->
    <!-- <h5 class="section-title">Filters</h5> -->

    <div class="filter-section">
        <div class="row">
            <form id="filterForm" method="GET" action="dashboard">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                        <label>Date Range</label>
                        <input name="date" id="date" class="form-control datepickers" placeholder="Select Date Range"
                            value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                // Display the selected date range from the URL
                                echo $_GET['date'];
                            } ?>">
                    </div>

                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 d-3 mb-3 form-group">
                        <label class="form-label">Avg TAT for Departments</label>
                        <select class="form-select filter-select" id="departments_tat" name="departments"
                            onchange="submitForm()">
                            <option value="">Select Department</option>
                            <?php if (!empty($krivisha_department)) {
                                foreach ($krivisha_department as $make_result) { ?>
                                    <option value="<?= $make_result->id ?>" <?php if (isset($_GET['departments']) && $_GET['departments'] == $make_result->id) { ?>selected<?php } ?>>
                                        <?= $make_result->department ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>

                    <!-- Article Filter -->
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 d-3 mb-3 form-group">
                        <label class="form-label">TAT Employees Wise</label>
                        <select class="form-select filter-select" id="employees_tat" name="employees"
                            onchange="submitForm()">
                            <option value="">Select Employees</option>
                            <?php if (!empty($krivisha_employee)) {
                                foreach ($krivisha_employee as $make_result) { ?>
                                    <option value="<?= $make_result->id ?>" <?php if (isset($_GET['employees']) && $_GET['employees'] == $make_result->id) { ?>selected<?php } ?>>
                                        <?= $make_result->first_name ?>
                                    </option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 d-3 mb-3 form-group">
                        <label class="form-label">Dashboard Option</label>
                        <select class="form-select filter-select" id="dashboard_option" name="dashboard_option"
                            onchange="submitForm()">
                            <option value="">Select Dashboard</option>
                            <option value="plant_head_production">Plant Head Production</option>
                            <option value="production_supervisor">Production Supervisor</option>
                            <option value="account">Account</option>
                            <option value="purchase_dashboard">Purchase Dashboard</option>
                            <option value="store_dashboard">Store Dashboard</option>
                        </select>
                    </div>

                </div>
                <button type="button" id="removeFiltersBtn" class="btn btn-danger btn-sm">Clear Filters</button>
            </form>
        </div>
    </div>



<?php
$avg_minutes = $metrics['average_tat'];

$avg_days  = floor($avg_minutes / 1440);
$avg_hours = floor(($avg_minutes % 1440) / 60);
$avg_mins  = $avg_minutes % 60;

$avg_tat_display = '';

if ($avg_days > 0) {
    $avg_tat_display .= $avg_days . 'D  ';
}
if ($avg_hours > 0) {
    $avg_tat_display .= $avg_hours . 'H  ';
}
$avg_tat_display .= $avg_mins . 'M';
?>
    <!-- Metrics -->
    <!-- <h5 class="section-title">Key Metrics</h5> -->
    <div class="row g-3 mb-3">
        <!-- Card 1: Avg TAT -->
        <div class="col-md-3">
            <div class="metric-card color-1">
                <div class="metric-title d-flex justify-content-between align-items-center">
                    <span>Avg TAT for Departments/Employees</span>
                    <span class="card-info-icon">
                        <i class="fa fa-info-circle"></i>
                        <span class="tooltip-text">
                            <h4><i class="fa fa-clock"></i> Average Turnaround Time</h4>
                            <p>Tracks average time taken to transition/complete tasks across departments or employees (In Printing, Printing Dispatch, Awaiting Dispatch).</p>
                            <span class="source-info"><i class="fa fa-database"></i> Source: tbl_auto_task_list_history &amp; tbl_manual_task</span>
                        </span>
                    </span>
                </div>
                <div class="metric-value"><?= $avg_tat_display ?></div>
                <div class="metric-chart">
                    <canvas id="tatDonutChart"></canvas>
                </div>
                <div class="metric-legend inline-legend">
                    <span><span class="legend-color" style="background:#6f5aff"></span>Awaiting Dispatch</span>
                    <span><span class="legend-color" style="background:#73d7c9"></span>Printing Dispatch</span>
                    <span><span class="legend-color" style="background:#7cb4ff"></span>In Printing</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pending Dispatch -->
        <div class="col-md-3">
            <a href="<?= base_url('outward_order_list?date=&order_status=9') ?>"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-2">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Pending dispatch</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-truck"></i> Pending Dispatch</h4>
                                <p>Count of production orders finished and packed but currently awaiting store release and delivery vehicle loading.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_production_schedules (where order_status = 9)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value">
                        <?= $metrics['pending_dispatch_onwer'] ?>
                    </div>
                    <div class="metric-chart">
                        <canvas id="pendingDispatchChart"></canvas>
                    </div>
                    <div class="metric-legend inline-legend">
                        <span><span class="legend-color" style="background:#3ac47d"></span>Today</span>
                        <span><span class="legend-color" style="background:#ffa500"></span>1–7 Days</span>
                        <span><span class="legend-color" style="background:#ff5c5c"></span>7+ Days</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 3: Pending Printing -->
        <div class="col-md-3">
            <a href="<?= base_url('printing_order_list?date=&order_status=0') ?>"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-3">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Pending printing</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-print"></i> Pending Printing</h4>
                                <p>Orders queued in the printing department. Indicates articles molded and awaiting screen printing/labelling.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_production_report (where order_status = 0)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value">
                        <?= $metrics['pending_printing_onwer'] ?>
                    </div>
                    <div class="metric-chart">
                        <canvas id="pendingPrintingChart"></canvas>
                    </div>
                    <div class="metric-legend inline-legend">
                        <span><span class="legend-color" style="background:#3ac47d"></span>Today</span>
                        <span><span class="legend-color" style="background:#ffa500"></span>1–7 Days</span>
                        <span><span class="legend-color" style="background:#ff5c5c"></span>7+ Days</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: Admin Pending Task -->
        <div class="col-md-3">
            <a href="<?= base_url('task_list?date=&status_of_work=1&super_admin_task=1') ?>"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-13 position-relative">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Admin Pending Task</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-user-shield"></i> Admin Pending Tasks</h4>
                                <p>Critical enterprise tasks, order blocks, price escalations or overrides awaiting super-admin clearance.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_manual_task &amp; tbl_auto_task_list</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['total_peding_task_super_admin'] ?></div>
                    <div class="metric-subtitle">
                        <?php if ($metrics['total_peding_task_super_admin'] > 0): ?>
                            <span class="text-danger" style="font-weight: 600;"><i class="fa fa-exclamation-triangle"></i> Requires attention</span>
                        <?php else: ?>
                            <span class="text-success" style="font-weight: 600;"><i class="fa fa-check-circle"></i> All tasks completed</span>
                        <?php endif; ?>
                    </div>
                    <i class="fa <?= $metrics['total_peding_task_super_admin'] > 0 ? 'fa-tasks' : 'fa-check-double' ?> metric-icon-check" style="opacity: 0.4; font-size: 54px;"></i>
                </div>
            </a>
        </div>

        <!-- Card 5: Salesman on Field -->
        <div class="col-md-3">
            <a href="<?= base_url('salesman_on_fields_details') ?>" style="text-decoration:none; color: inherit;">
                <div class="metric-card color-4">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Salesman on Field</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-map-marker-alt"></i> Sales Executives on Field</h4>
                                <p>Active salesperson count currently performing physical client visits, relationship meets, or collection follows.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_coverage_reports (Active Today)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['total_on_field'] ?></div>
                    <div class="metric-icon-salesman"><i class="fas fa-cube"></i></div>
                </div>
            </a>
        </div>

        <!-- Card 6: Conversion Ratio -->
        <div class="col-md-3">
            <div class="metric-card color-5">
                <div class="metric-title d-flex justify-content-between align-items-center">
                    <span>Conversion Ratio</span>
                    <span class="card-info-icon">
                        <i class="fa fa-info-circle"></i>
                        <span class="tooltip-text">
                            <h4><i class="fa fa-percent"></i> Enquiry-to-Order Ratio</h4>
                            <p>Percentage conversion rate showing what portion of B2B enquiries successfully transition into approved orders.</p>
                            <span class="source-info"><i class="fa fa-calculator"></i> Formula: (Total Orders / Total Enquiries) * 100</span>
                        </span>
                    </span>
                </div>
                <div class="metric-value"><?= $metrics['enquiry_order_generation_ratio'] ?>%</div>
                <div class="metric-chart">
                    <canvas id="conversionGauge"></canvas>
                </div>
            </div>
        </div>

        <!-- Card 7: Order Execution (Household) -->
        <div class="col-md-3">
            <a href="<?= base_url('auto_task_list?date=&type_of_order=1') ?>" class="metric-link"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-10">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Order Execution (Household)</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-home"></i> Household Orders</h4>
                                <p>Weekly run-rate showing domestic/household-grade storage articles molded, checked, and invoiced.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_article_production_details (Household Class)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['household_order_execution'] ?></div>
                    <div class="metric-chart">
                        <canvas id="householdTrendChart"></canvas>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 8: Order Execution (Container) -->
        <div class="col-md-3">
            <a href="<?= base_url('auto_task_list?date=&type_of_order=2') ?>" class="metric-link"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-7">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Order Execution (Container)</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-box-open"></i> Container Orders</h4>
                                <p>Weekly run-rate showing industrial-grade containers and packaging molded, checked, and invoiced.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_article_production_details (Container Class)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['container_order_execution'] ?></div>
                    <div class="metric-chart">
                        <canvas id="containerTrendChart"></canvas>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 9: Active Customers -->
        <div class="col-md-3">
            <a href="<?= base_url('customer_list?party_filter=active') ?>"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-9">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Active Customers</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-smile"></i> Active B2B Customers</h4>
                                <p>Count of unique buyers, dealers, or distributors who have placed orders during the active tracking period.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_party_master (Attending Sales Status)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['customer_active'] ?></div>
                    <div class="metric-chart">
                        <canvas id="activeCustomersChart"></canvas>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 10: Inactive Customers -->
        <div class="col-md-3">
            <a href="<?= base_url('customer_list?party_filter=inactive') ?>"
                style="text-decoration:none; color: inherit;">
                <div class="metric-card color-8">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Inactive Customers</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-user-slash"></i> Inactive Customers</h4>
                                <p>Registered buyers showing zero purchase activity within the last 1, 3, or 4+ months, segmented for marketing outreach.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_party_master (Last Order Dormancy)</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['customer_inactive'] ?></div>
                    <div class="metric-chart">
                        <canvas id="inactiveCustomersChart"></canvas>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 11: Inactive Brands -->
        <div class="col-md-3">
            <a href="<?= base_url('brand_list?brand_filter=inactive') ?>" style="text-decoration:none; color: inherit;">
                <div class="metric-card color-11">
                    <div class="metric-title d-flex justify-content-between align-items-center">
                        <span>Inactive Brands</span>
                        <span class="card-info-icon">
                            <i class="fa fa-info-circle"></i>
                            <span class="tooltip-text">
                                <h4><i class="fa fa-tags"></i> Dormant Client Brands</h4>
                                <p>Registered private brands or labels that have had zero active manufacturing or labeling runs in the designated dates.</p>
                                <span class="source-info"><i class="fa fa-database"></i> Source: tbl_brand_master</span>
                            </span>
                        </span>
                    </div>
                    <div class="metric-value"><?= $metrics['inactive_brands'] ?></div>
                    <div class="metric-chart" style="min-height: 140px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="inactiveBrandsCloud"></canvas>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 12: Coverage Reports -->
        <div class="col-md-3">
            <div class="metric-card color-12" id="coverageReportsCard" style="cursor:pointer;">
                <div class="metric-title d-flex justify-content-between align-items-center">
                    <span>Coverage Reports</span>
                    <span class="card-info-icon">
                        <i class="fa fa-info-circle"></i>
                        <span class="tooltip-text">
                            <h4><i class="fa fa-file-invoice"></i> Sales Coverage Touchpoints</h4>
                            <p>Aggregated count of field activity logs including physical visits, phone follow-ups, and greetings. Click to view grid.</p>
                            <span class="source-info"><i class="fa fa-database"></i> Source: tbl_coverage_reports</span>
                        </span>
                    </span>
                </div>
                <div class="metric-value"><?= $metrics['total_visits'] ?></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="coverageReportsModal" tabindex="-1" aria-labelledby="coverageReportsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coverageReportsModalLabel">Coverage Reports Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="coverageReportsDetails">
                <!-- Data will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
<script>
    function submitForm() {
        document.getElementById('filterForm').submit();
    }
    document.getElementById('removeFiltersBtn').addEventListener('click', function () {
        let url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.origin + url.pathname;
    });
    document.addEventListener('DOMContentLoaded', function () {
        function checkFilters() {
            let department = document.querySelector('select[name="departments"]').value;
            let employee = document.querySelector('select[name="employees"]').value;

            if (department || employee) {
                document.getElementById('removeFiltersBtn').style.display = 'inline-block';
            } else {
                document.getElementById('removeFiltersBtn').style.display = 'none';
            }
        }

        checkFilters();

        document.querySelector('select[name="departments"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="employees"]').addEventListener('change', checkFilters);
        document.getElementById('removeFiltersBtn').addEventListener('click', function () {
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        });
    });
    $(document).ready(function () {
        $('.filter-select').select2({

        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#dashboard_option').change(function () {
            var selectedValue = $(this).val();
            if (selectedValue) {
                window.location.href = '<?= base_url('dashboard') ?>?dashboard_option=' + selectedValue;
            } else {
                window.location.href = '<?= base_url('dashboard') ?>';
            }
        });
    });
    $('#coverageReportsCard').on('click', function () {
        $('#coverageReportsModal').modal('show');

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/get_coverage_reports_details',
            type: 'POST',
            success: function (response) {
                let data = [];
                try {
                    data = JSON.parse(response);
                } catch (e) {
                    $('#coverageReportsDetails').html(
                        '<div class="text-danger">Error loading data.</div>');
                    return;
                }

                if (!data.length) {
                    $('#coverageReportsDetails').html(
                        '<div class="text-muted">No coverage data found.</div>');
                    return;
                }

                // Flatten data into rows
                let rows = [];
                data.forEach(row => {
                    // Map type_of_visit
                    let visitType = '';
                    if (row.type_of_visit === '1') visitType = 'Physical Visit';
                    else if (row.type_of_visit === '2') visitType = 'Telephonic Meet';
                    else if (row.type_of_visit === '3') visitType =
                        'Supervisor/Sales Head Connect';

                    // Map source_of_visit
                    let visitStatus = '';
                    if (row.source_of_visit === '1') visitStatus =
                        'Cold all- Introduction to our offerings';
                    else if (row.source_of_visit === '2') visitStatus =
                        'Planned Relationship Meet';
                    else if (row.source_of_visit === '3') visitStatus =
                        'Order/ Payment Follow Up';
                    else if (row.source_of_visit === '4') visitStatus = 'Complaint Visit';
                    else if (row.source_of_visit === '5') visitStatus =
                        'Other: Marketing or Greetings  Visit';

                    // Format date (dd-mm-yyyy)
                    let formattedDate = "";
                    if (row.date || row.created_on) {
                        let d = new Date(row.date || row.created_on);
                        let day = String(d.getDate()).padStart(2, "0");
                        let month = String(d.getMonth() + 1).padStart(2, "0");
                        let year = d.getFullYear();
                        formattedDate = `${day}-${month}-${year}`;
                    }

                    rows.push([
                        row.visit_request_id || '', // Visit Request ID
                        formattedDate,
                        row.employee_name || row.sales_person_name || row
                            .sales_person_id || '',
                        row.party_name || '',
                        visitType,
                        visitStatus,
                        row.remarks || row.remark || ''
                    ]);
                });

                // Render table container
                $('#coverageReportsDetails').html(`
                <table id="coverageReportsTable" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                          <th>Visit Request ID</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Party Name</th>
                            <th>Type</th>
                            <th>Purpose Of Visit</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                </table>
            `);

                // Initialize DataTable
                $('#coverageReportsTable').DataTable({
                    data: rows,
                    columns: [{
                        title: "Visit Request ID"
                    },
                    {
                        title: "Date"
                    },
                    {
                        title: "Employee"
                    },
                    {
                        title: "Party Name"
                    },
                    {
                        title: "Type"
                    },
                    {
                        title: "Status Of Visit"
                    },
                    {
                        title: "Remarks"
                    }
                    ],
                    pageLength: 10,
                    ordering: false,
                    responsive: true,
                    destroy: true, // important for re-init
                    order: [
                        [0, 'desc']
                    ], // latest date first
                    dom: 'Bflrtip',
                    buttons: [{
                        extend: 'excel',
                        footer: true,
                        title: 'Coverage Reports Details',
                        filename: 'coverage_reports_details',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    }]
                });
            }
        });
    });

    function getTodayDate() {
        let today = new Date();
        let dd = String(today.getDate()).padStart(2, '0');
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let yyyy = today.getFullYear();
        return dd + '-' + mm + '-' + yyyy;
    }
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
    $(function () {
        const tatCtx = document.getElementById('tatDonutChart')?.getContext('2d');
        if (tatCtx) {
            const tatBreakdown = <?php echo json_encode($metrics['tat_breakdown'] ?? ['awaiting_dispatch' => 0, 'printing_dispatch' => 0, 'in_printing' => 0]); ?>;
            new Chart(tatCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Awaiting Dispatch', 'Printing Dispatch', 'In Printing'],
                    datasets: [{
                        data: [tatBreakdown.awaiting_dispatch || 0, tatBreakdown.printing_dispatch || 0, tatBreakdown.in_printing || 0],
                        backgroundColor: ['#6f5aff', '#73d7c9', '#7cb4ff'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true },
                        datalabels: {
                            display: true,
                            color: '#000000',
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            borderRadius: 4,
                            padding: 4,
                            font: { 
                                size: 12, 
                                weight: 'bold' 
                            },
                            anchor: 'end',
                            align: 'end',
                            offset: -30,
                            formatter: (value, context) => {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(0);
                                return percentage + '%';
                            }
                        }
                    }
                }
            });
        }

        const dispatchCtx = document.getElementById('pendingDispatchChart')?.getContext('2d');
        if (dispatchCtx) {
            const dispatchAge = <?php echo json_encode($metrics['pending_dispatch_age'] ?? ['today' => 0, 'week' => 0, 'older' => 0]); ?>;
            new Chart(dispatchCtx, {
                type: 'bar',
                data: {
                    labels: ['Today', '1–7 Days', '7+ Days'],
                    datasets: [{
                        label: 'Orders',
                        data: [dispatchAge.today || 0, dispatchAge.week || 0, dispatchAge.older || 0],
                        backgroundColor: ['#3ac47d', '#ffa500', '#ff5c5c'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: { color: '#2c343d', font: { size: 10, weight: '500' } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#2c343d', font: { size: 11, weight: '600' } }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#fff',
                            font: { size: 11, weight: 'bold' },
                            anchor: 'center',
                            align: 'center',
                            formatter: v => v > 0 ? v : ''
                        }
                    }
                }
            });
        }

        const printingCtx = document.getElementById('pendingPrintingChart')?.getContext('2d');
        if (printingCtx) {
            const printingAge = <?php echo json_encode($metrics['pending_printing_age'] ?? ['today' => 0, 'week' => 0, 'older' => 0]); ?>;
            new Chart(printingCtx, {
                type: 'bar',
                data: {
                    labels: ['Today', '1–7 Days', '7+ Days'],
                    datasets: [{
                        label: 'Orders',
                        data: [printingAge.today || 0, printingAge.week || 0, printingAge.older || 0],
                        backgroundColor: ['#3ac47d', '#ffa500', '#ff5c5c'],
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#2c343d', font: { size: 11, weight: '600' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { 
                                color: '#2c343d', 
                                font: { size: 10, weight: '500' },
                                stepSize: 10
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#fff',
                            font: { size: 11, weight: 'bold' },
                            anchor: 'center',
                            align: 'center',
                            formatter: v => v > 0 ? v : ''
                        }
                    }
                }
            });
        }

        const conversionValue = <?= floatval($metrics['enquiry_order_generation_ratio']) ?>;
        const conversionCtx = document.getElementById('conversionGauge')?.getContext('2d');
        if (conversionCtx) {
            new Chart(conversionCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [conversionValue, 100 - conversionValue],
                        backgroundColor: ['#ff3d7e', '#f0f0f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '75%',
                    rotation: -90,
                    circumference: 180,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                },
                plugins: [{
                    id: 'conversion-gauge-text',
                    afterDraw(chart) {
                        const ctx = chart.ctx;
                        const x = (chart.chartArea.left + chart.chartArea.right) / 2;
                        const y = (chart.chartArea.top + chart.chartArea.bottom) / 2 + 20;
                        ctx.save();
                        ctx.font = 'bold 18px Arial';
                        ctx.fillStyle = '#333';
                        ctx.textAlign = 'center';
                        ctx.fillText(conversionValue + '%', x, y);
                        ctx.restore();
                    }
                }]
            });
        }

        const householdCtx = document.getElementById('householdTrendChart')?.getContext('2d');
        if (householdCtx) {
            new Chart(householdCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    datasets: [{
                        label: 'Household',
                        data: [5.5, 6.1, 6.5, 6.8, 6.7, 6.5],
                        borderColor: '#0b6efd',
                        backgroundColor: 'rgba(11, 110, 253, 0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#0b6efd',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        x: { 
                            display: true,
                            grid: { display: false },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        },
                        y: { 
                            display: true,
                            beginAtZero: false,
                            grid: { 
                                display: true,
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                },
                                maxTicksLimit: 5,
                                padding: 5
                            }
                        }
                    }
                }
            });
        }

        const containerCtx = document.getElementById('containerTrendChart')?.getContext('2d');
        if (containerCtx) {
            new Chart(containerCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    datasets: [{
                        label: 'Container',
                        data: [6.2, 6.4, 6.9, 6.8, 7.0, 6.6],
                        borderColor: '#ff8c00',
                        backgroundColor: 'rgba(255, 140, 0, 0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#ff8c00',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        x: { 
                            display: true,
                            grid: { display: false },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        },
                        y: { 
                            display: true,
                            beginAtZero: false,
                            grid: { 
                                display: true,
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                },
                                maxTicksLimit: 5,
                                padding: 5
                            }
                        }
                    }
                }
            });
        }

        const activeCtx = document.getElementById('activeCustomersChart')?.getContext('2d');
        if (activeCtx) {
            const activeWeekly = <?php
                $aw = $metrics['active_customers_weekly'] ?? [];
                echo json_encode([
                    'labels' => array_column($aw, 'label'),
                    'data'   => array_column($aw, 'count'),
                ]);
            ?>;
            new Chart(activeCtx, {
                type: 'line',
                data: {
                    labels: activeWeekly.labels || ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                    datasets: [{
                        label: 'Active',
                        data: activeWeekly.data || [0,0,0,0,0,0,0],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40,167,69,0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#28a745',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: { 
                        x: { 
                            display: true,
                            grid: { display: false },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        }, 
                        y: { 
                            display: true,
                            beginAtZero: true,
                            grid: { 
                                display: true,
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                },
                                maxTicksLimit: 5,
                                padding: 5
                            }
                        }
                    }
                }
            });
        }

        const inactiveCtx = document.getElementById('inactiveCustomersChart')?.getContext('2d');
        if (inactiveCtx) {
            const inactiveBreakdown = <?php echo json_encode($metrics['inactive_customers_breakdown'] ?? ['one_month' => 0, 'three_month' => 0, 'four_plus' => 0]); ?>;
            new Chart(inactiveCtx, {
                type: 'bar',
                data: {
                    labels: ['1 Month', '3 Months', '4+ Months'],
                    datasets: [{
                        label: 'Inactive',
                        data: [inactiveBreakdown.one_month || 0, inactiveBreakdown.three_month || 0, inactiveBreakdown.four_plus || 0],
                        backgroundColor: '#ff7f50'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#ffffff',
                            font: { 
                                size: 14, 
                                weight: 'bold' 
                            },
                            anchor: 'center',
                            align: 'center',
                            formatter: function(value) {
                                return value > 0 ? value : '';
                            }
                        }
                    },
                    scales: { 
                        x: { 
                            grid: { display: false },
                            ticks: { 
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        }, 
                        y: { 
                            display: true,
                            beginAtZero: true,
                            grid: { 
                                display: true,
                                color: 'rgba(0, 0, 0, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                display: true,
                                color: '#2c343d',
                                font: { 
                                    size: 11,
                                    weight: '500'
                                },
                                maxTicksLimit: 5,
                                padding: 5
                            }
                        }
                    }
                }
            });
        }

        /* const lostCtx = document.getElementById('lostCustomersChart')?.getContext('2d');
        if (lostCtx) {
            const lostBreakdown = <?php echo json_encode($metrics['customers_lost_breakdown'] ?? ['awaiting_dispatch' => 0, 'in_printing' => 0, 'others' => 0]); ?>;
            new Chart(lostCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Awaiting Dispatch', 'In Printing', 'Others'],
                    datasets: [{
                        data: [
                            lostBreakdown.awaiting_dispatch || 0,
                            lostBreakdown.in_printing       || 0,
                            lostBreakdown.others            || 0
                        ],
                        backgroundColor: ['#ff4d4f', '#ff9f43', '#ffc53d'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { 
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            color: '#000000',
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            borderRadius: 4,
                            padding: 4,
                            font: { size: 12, weight: 'bold' },
                            anchor: 'end',
                            align: 'end',
                            offset: -30,
                            formatter: (value, context) => {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                if (total === 0 || value === 0) return '';
                                return ((value / total) * 100).toFixed(0) + '%';
                            }
                        }
                    }
                }
            });
        } */

        // Inactive Brands Word Cloud
        const brandsCtx = document.getElementById('inactiveBrandsCloud')?.getContext('2d');
        if (brandsCtx) {
            // Sample brand names - replace with actual data from backend
            const brandNames = ['Brandas', 'Brandoor', 'Brazle', 'Neforpi', 'Cenoss', 'Corstek', 'Remon', 'Kranon', 'Decobees', 'Techno', 'Innovate', 'Fusion'];
            
            new Chart(brandsCtx, {
                type: 'bubble',
                data: {
                    datasets: brandNames.map((name, index) => ({
                        label: name,
                        data: [{
                            x: Math.random() * 100,
                            y: Math.random() * 100,
                            r: 0
                        }]
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: { display: false, min: 0, max: 100 },
                        y: { display: false, min: 0, max: 100 }
                    },
                    animation: false
                },
                plugins: [{
                    id: 'wordCloudDraw',
                    afterDraw(chart) {
                        const ctx = chart.ctx;
                        const chartArea = chart.chartArea;
                        const colors = ['#2563EB', '#1D4ED8', '#3B82F6', '#60A5FA', '#0EA5E9', '#0284C7'];
                        const fontSizes = [14, 16, 12, 18, 13, 15, 14, 16, 13, 15, 14, 12];
                        
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        
                        brandNames.forEach((name, idx) => {
                            const x = chartArea.left + 20 + (idx % 3) * 80 + Math.random() * 10;
                            const y = chartArea.top + 20 + Math.floor(idx / 3) * 30 + Math.random() * 8;
                            ctx.fillStyle = colors[idx % colors.length];
                            ctx.font = `${fontSizes[idx % fontSizes.length]}px Poppins, sans-serif`;
                            ctx.fillText(name, x, y);
                        });
                        
                        ctx.restore();
                    }
                }]
            });
        }
    });
</script>