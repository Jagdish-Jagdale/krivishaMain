<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<style>
/* Modern & Premium Aesthetic Overrides for Plant Head Dashboard Metric Cards */
.plant-head-dashboard .metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.plant-head-dashboard .metric-card {
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
.plant-head-dashboard .metric-card::before {
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

.plant-head-dashboard .metric-card:hover::before {
    transform: scale(1.8);
    background: rgba(255, 255, 255, 0.18);
}

.plant-head-dashboard .metric-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.14), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}

/* Semantic High-End Gradients */
.plant-head-dashboard .metric-card.pending-tasks {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
}
.plant-head-dashboard .metric-card.rm-consumption {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
}
.plant-head-dashboard .metric-card.mb-consumption {
    background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%) !important;
}
.plant-head-dashboard .metric-card.rejection-pct {
    background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%) !important;
}
.plant-head-dashboard .metric-card.mb-pct {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
}
.plant-head-dashboard .metric-card.maint-pending {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
}
.plant-head-dashboard .metric-card.appr-production {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
}
.plant-head-dashboard .metric-card.appr-maint {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
}
.plant-head-dashboard .metric-card.idle-states {
    background: linear-gradient(135deg, #f97316 0%, #c2410c 100%) !important;
}

/* Card Typography Adjustment */
.plant-head-dashboard .metric-card h3 {
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

.plant-head-dashboard .metric-card .metric-value {
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
.plant-head-dashboard .metric-card .metric-icon {
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

.plant-head-dashboard .metric-card .metric-icon i {
    font-size: 18px !important;
    color: #ffffff !important;
}

.plant-head-dashboard .metric-card:hover .metric-icon {
    background: rgba(255, 255, 255, 0.28) !important;
    transform: rotate(8deg) scale(1.08);
}

/* Reset internal absolute position rules from layout styles */
.plant-head-dashboard .metric-card a.metric-link {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    height: 100%;
    width: 100%;
    text-decoration: none !important;
    color: #ffffff !important;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Chart.js default configuration for better visibility
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#2c343d';
    Chart.defaults.font.family = "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 11;
}
</script>



<div class="right_col">


    <div class="dashboard plant-head-dashboard">
        <!-- Header Filters -->

        <div class="filter-section">
            <div class="row">
                <form id="filterForm" method="GET" action="dashboard">
                    <input type="hidden" name="dashboard_option" id="dashboard_option" value="<?= $dashboard_option ?>">
                    <div class="row">
                      
                        <!-- From Date -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label>Date Range</label>
                            <input name="date" id="date" class="form-control datepickers"
                                placeholder="Select Date Range" value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                    // Display the selected date range from the URL
                                    echo $_GET['date'];
                                } ?>">
                        </div>

                        <!-- plant filter -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label class="form-label">Plant</label>
                            <select class="form-select filter-select" id="plant_id" name="plant_id"
                                onchange="submitForm()">
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

                        <!-- Article Filter -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label class="form-label">Article</label>
                            <select class="form-select filter-select" id="articleFilter" name="article"
                                onchange="submitForm()">
                                <option value="">Select Article</option>
                                <?php if (!empty($article)) {
                                    foreach ($article as $article_result) { ?>
                                        <option value="<?= $article_result->id ?>" <?php if (isset($_GET['article']) && $_GET['article'] == $article_result->id) { ?>selected<?php } ?>>
                                            <?= $article_result->article_name ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>

                        <!-- Raw Material Filter -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label class="form-label">Raw Material</label>
                            <select class="form-select filter-select" id="rawMaterialWise" name="raw_material"
                                onchange="submitForm()">
                                <option value="">Select Raw Material</option>
                                <?php if (!empty($raw_material)) {
                                    foreach ($raw_material as $raw_material_result) { ?>
                                        <option value="<?= $raw_material_result->id ?>" <?php if (isset($_GET['raw_material']) && $_GET['raw_material'] == $raw_material_result->id) { ?>selected<?php } ?>>
                                            <?= $raw_material_result->rm_name ?>
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
        <!-- <button id="removeFiltersBtn" class="btn btn-danger btn-sm">Remove Filters</button> -->
        <!-- Top Metrics -->
        <div class="metrics-row">
            <div class="metric-card pending-tasks">
                <a href="<?= base_url('auto_task_list?date=&production_pending_task=1') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-tasks"></i></div>
                    <h3>Pending Tasks</h3>
                    <div class="metric-value"><?= $metrics['pending_task'] ?></div>
                </a>
            </div>
            
            <div class="metric-card rm-consumption">
                <div class="metric-icon"><i class="fas fa-cogs"></i></div>
                <h3>Total Consumption of RM</h3>
                <div class="metric-value"><?= number_format($metrics['total_rm']) ?></div>
            </div>
            
            <div class="metric-card mb-consumption">
                <div class="metric-icon"><i class="fas fa-wrench"></i></div>
                <h3>Total Consumption of MB</h3>
                <div class="metric-value"><?= number_format($metrics['total_mb']) ?></div>
            </div>
            
            <div class="metric-card rejection-pct">
                <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3>Rejection %</h3>
                <div class="metric-value"><?= $metrics['rejection_percent'] ?>%</div>
            </div>
            
            <div class="metric-card mb-pct">
                <div class="metric-icon"><i class="fas fa-chart-line"></i></div>
                <h3>MB %</h3>
                <div class="metric-value"><?= $metrics['mb_percent'] ?>%</div>
            </div>

            <div class="metric-card maint-pending">
                <a href="<?= base_url('production_maintenance_list?status_of_work=1') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-tools"></i></div>
                    <h3>Maintenance Pending</h3>
                    <div class="metric-value"><?= $metrics['pending_maintenance'] ?></div>
                </a>
            </div>

            <div class="metric-card appr-production">
                <a href="<?= base_url('production_report_list?pending_approved=0') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-clock"></i></div>
                    <h3>Pending Approval (Production)</h3>
                    <div class="metric-value"><?= $metrics['pending_approved'] ?></div>
                </a>
            </div>

            <div class="metric-card appr-maint">
                <a href="<?= base_url('maintenance_list_details?approve_status=2') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
                    <h3>Pending Approval (Maintenance)</h3>
                    <div class="metric-value"><?= $maintenance_approve_pending ?></div>
                </a>
            </div>

            <div class="metric-card idle-states" id="idleStateCard" style="cursor:pointer;">
                <div class="metric-icon"><i class="fas fa-pause"></i></div>
                <h3>Idle State Instances</h3>
                <div class="metric-value"></div>
            </div>
        </div>
        <div class="mb-4">
            <h5>Machine wise tabular view</h5>
            <div class="data-table">
                <div class="table-container">
                    <table id="example">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Total Consumption Of RM</th>
                                <th>MB %</th>
                                <th>Rejection %</th>
                                <th>Variance</th>
                            </tr>
                        </thead>
                        <tbody id="machineTable">
                            <?php foreach ($machine_production as $machine):
                                $other_data = $this->Admin_model->get_other_rm_mb_rejection_data($machine->id);
                                ?>
                                <tr>
                                    <td><?php echo $machine->machine_name; ?></td>
                                    <td><?php echo number_format($other_data['total_rm'], 3); ?></td>
                                    <td><?php echo number_format($other_data['mb_percent'], 3); ?></td>
                                    <td><?php echo number_format($other_data['rejection_percent'], 3); ?></td>
                                    <td><?php echo number_format($other_data['delta'], 3); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <h5>Machine production Utilization (Monthwise)</h5>
        <div class="chart-container">
            <canvas id="lineChart" width="400" height="200"></canvas>
        </div>
        <br>
        <h5>Production Planed vs Actual </h5>
        <div class="chart-container">
            <canvas id="productionChart" width="400" height="200"></canvas>
        </div>
        <br>

        <!-- New Charts Row -->
        <div class="charts-row">
            <div class="chart-item">
                <h5>Machine-wise RM Consumption</h5>
                <div class="chart-container">
                    <canvas id="rmPieChart" width="300" height="300"></canvas>
                </div>
            </div>
            <div class="chart-item">
                <h5>Rejection Percentage Gauge</h5>
                <div class="chart-container">
                    <canvas id="rejectionGauge" width="200" height="150"></canvas>
                </div>
            </div>
            <div class="chart-item">
                <h5>MB Percentage Gauge</h5>
                <div class="chart-container">
                    <canvas id="mbGauge" width="200" height="150"></canvas>
                </div>
            </div>
        </div>
        <br>

        <div class="charts-row">
            <div class="chart-item full-width">
                <h5>Monthly Production Trends</h5>
                <div class="chart-container">
                    <canvas id="monthlyBarChart" width="600" height="300"></canvas>
                </div>
            </div>
        </div>
        <br>


        <div class="mb-4">
            <h5>Article Level Analysis</h5>
            <div class="data-table">
                <div class="table-container">
                    <table id="example-1">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Machine</th>
                                <th>Article Name</th>
                                <th>Article Weight</th>
                                <th>Final Qty</th>
                            </tr>
                        </thead>
                        <tbody id="article_table">
                            <?php foreach ($article_level as $article): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $timestamp = strtotime($article->created_on) ?: time();
                                        echo date('d-m-Y', $timestamp);
                                        ?>
                                    </td>
                                    <td><?php echo $article->machine_name; ?></td>
                                    <td><?php echo $article->article_name; ?></td>
                                    <td><?php echo number_format($article->total_approved_weight, 3); ?></td>
                                    <td><?php echo $article->total_approved_qty; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="idleStateModal" tabindex="-1" aria-labelledby="idleStateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="idleStateModalLabel">Idle State Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="idleStateDetails">
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="plant_id_filter" name="plant_id_filter" value="<?= $_GET['plant_id'] ?? '' ?>">
<?php include('footer.php'); ?>
<script>
    function formatDateToYMD(dateStr) {
        let parts = dateStr.split('/');
        if (parts.length !== 3) return dateStr; // fallback
        return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
    }

    function checkIdleState() {
        var date_range = $('#date').val();
        if (!date_range) {
            let today = new Date();
            let yyyy = today.getFullYear();
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let dd = String(today.getDate()).padStart(2, '0');
            date_range = `${yyyy}-${mm}-${dd}`;
        }

        var dates = date_range.split(' to ');
        var start_date = formatDateToYMD(dates[0]);
        var end_date = formatDateToYMD(dates[1] || dates[0]);

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/get_idle_state_details',
            type: 'POST',
            data: {
                plant_id: $('#plant_id_filter').val(),
                start_date: start_date,
                end_date: end_date,
                machine: $('#machineFilter').val()
            },
            success: function (response) {
                let data = [];
                try {
                    data = JSON.parse(response);
                } catch (e) {
                    $('#idleStateCard .metric-value').text('0');
                    return;
                }

                const hourSlots = [
                    { qty: 'qty_eight_nine' }, { qty: 'qty_nine_ten' }, { qty: 'qty_ten_eleven' },
                    { qty: 'qty_eleven_twelve' }, { qty: 'qty_twelve_thirteen' }, { qty: 'qty_thirteen_fourteen' },
                    { qty: 'qty_fourteen_fifteen' }, { qty: 'qty_fifteen_sixteen' }, { qty: 'qty_sixteen_seventeen' },
                    { qty: 'qty_seventeen_eighteen' }, { qty: 'qty_eighteen_nineteen' }, { qty: 'qty_nineteen_twenty' },
                    { qty: 'qty_twenty_twentyone' }, { qty: 'qty_twentyone_twentytwo' }, { qty: 'qty_twentytwo_twentythree' },
                    { qty: 'qty_twentythree_zero' }, { qty: 'qty_zero_one' }, { qty: 'qty_one_two' },
                    { qty: 'qty_two_three' }, { qty: 'qty_three_four' }, { qty: 'qty_four_five' },
                    { qty: 'qty_five_six' }, { qty: 'qty_six_seven' }, { qty: 'qty_seven_eight' }
                ];

                let rows = [];
                data.forEach(row => {
                    hourSlots.forEach(slot => {
                        const qty = row[slot.qty];
                        if (qty >= 0 && qty < 30 && qty != 'null' && qty != '') {
                            rows.push(qty);
                        }
                    });
                });

                let totalCount = rows.length;
                document.querySelector("#idleStateCard .metric-value").innerText = totalCount;
            }
        });
    }

    checkIdleState();

    $('#date, #plant_id_filter, #machineFilter').on('change', function () {
        checkIdleState();
    });
    $('#idleStateCard').on('click', function () {
        $('#idleStateModal').modal('show');

        var date_range = $('#date').val();

        if (!date_range) {
            let today = new Date();
            let yyyy = today.getFullYear();
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let dd = String(today.getDate()).padStart(2, '0');
            date_range = `${yyyy}-${mm}-${dd}`;
        }

        var dates = date_range.split(' to ');
        var start_date = dates[0];
        var end_date = dates[1] || dates[0];

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/get_idle_state_details',
            type: 'POST',
            data: {
                plant_id: $('#plant_id_filter').val(),
                start_date: start_date,
                end_date: end_date,
                machine: $('#machineFilter').val()
            },
            success: function (response) {
                let data = [];
                try {
                    data = JSON.parse(response);
                } catch (e) {
                    $('#idleStateDetails').html('<div class="text-danger">Error loading data.</div>');
                    return;
                }

                if (!data.length) {
                    $('#idleStateDetails').html('<div class="text-muted">No idle state data found.</div>');
                    return;
                }

                // Define hour slots and their corresponding fields
                const hourSlots = [
                    { qty: 'qty_eight_nine', remark: 'qty_eight_nine_remark', label: '08-09 AM' },
                    { qty: 'qty_nine_ten', remark: 'qty_nine_ten_remark', label: '09-10 AM' },
                    { qty: 'qty_ten_eleven', remark: 'qty_ten_eleven_remark', label: '10-11 AM' },
                    { qty: 'qty_eleven_twelve', remark: 'qty_eleven_twelve_remark', label: '11-12 PM' },
                    { qty: 'qty_twelve_thirteen', remark: 'qty_twelve_thirteen_remark', label: '12-01 PM' },
                    { qty: 'qty_thirteen_fourteen', remark: 'qty_thirteen_fourteen_remark', label: '01-02 PM' },
                    { qty: 'qty_fourteen_fifteen', remark: 'qty_fourteen_fifteen_remark', label: '02-03 PM' },
                    { qty: 'qty_fifteen_sixteen', remark: 'qty_fifteen_sixteen_remark', label: '03-04 PM' },
                    { qty: 'qty_sixteen_seventeen', remark: 'qty_sixteen_seventeen_remark', label: '04-05 PM' },
                    { qty: 'qty_seventeen_eighteen', remark: 'qty_seventeen_eighteen_remark', label: '05-06 PM' },
                    { qty: 'qty_eighteen_nineteen', remark: 'qty_eighteen_nineteen_remark', label: '06-07 PM' },
                    { qty: 'qty_nineteen_twenty', remark: 'qty_nineteen_twenty_remark', label: '07-08 PM' },
                    { qty: 'qty_twenty_twentyone', remark: 'qty_twenty_twentyone_remark', label: '08-09 PM' },
                    { qty: 'qty_twentyone_twentytwo', remark: 'qty_twentyone_twentytwo_remark', label: '09-10 PM' },
                    { qty: 'qty_twentytwo_twentythree', remark: 'qty_twentytwo_twentythree_remark', label: '10-11 PM' },
                    { qty: 'qty_twentythree_zero', remark: 'qty_twentythree_zero_remark', label: '11-12 AM' },
                    { qty: 'qty_zero_one', remark: 'qty_zero_one_remark', label: '12-01 AM' },
                    { qty: 'qty_one_two', remark: 'qty_one_two_remark', label: '01-02 AM' },
                    { qty: 'qty_two_three', remark: 'qty_two_three_remark', label: '02-03 AM' },
                    { qty: 'qty_three_four', remark: 'qty_three_four_remark', label: '03-04 AM' },
                    { qty: 'qty_four_five', remark: 'qty_four_five_remark', label: '04-05 AM' },
                    { qty: 'qty_five_six', remark: 'qty_five_six_remark', label: '05-06 AM' },
                    { qty: 'qty_six_seven', remark: 'qty_six_seven_remark', label: '06-07 AM' },
                    { qty: 'qty_seven_eight', remark: 'qty_seven_eight_remark', label: '07-08 AM' }
                ];

                // Flatten data into rows
                let rows = [];
                data.forEach(row => {
                    hourSlots.forEach(slot => {
                        const qty = row[slot.qty];
                        const remark = row[slot.remark] || '';
                        console.log(qty);
                        if (qty >= 0 && qty < 30 && qty != 'null' && qty != '') {
                            let formattedDate = "";
                            if (row.production_date) {
                                let d = new Date(row.production_date);
                                let day = String(d.getDate()).padStart(2, "0");
                                let month = String(d.getMonth() + 1).padStart(2, "0");
                                let year = d.getFullYear();
                                formattedDate = `${day}-${month}-${year}`;
                            }
                            rows.push([
                                formattedDate,
                                row.machine_name || row.machine || '',
                                slot.label,
                                remark,
                                qty
                            ]);
                        }
                    });
                });

                // Render table container
                $('#idleStateDetails').html(`
                <table id="idleStateTable" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Production Date</th>
                            <th>Machine Name</th>
                            <th>Idle Hour</th>
                            <th>Reason</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                </table>
            `);

                // Initialize DataTable
                $('#idleStateTable').DataTable({
                    data: rows,
                    columns: [
                        { title: "Production Date" },
                        { title: "Machine Name" },
                        { title: "Idle Hour" },
                        { title: "Reason" },
                        { title: "Qty" }
                    ],
                    pageLength: 10,
                    responsive: true,
                    destroy: true, // important for re-init
                    order: [[0, 'asc']]
                });
            }
        });
    });
    function submitForm() {
        var dashboard_option = document.getElementById('dashboard_option').value;
        if (dashboard_option == '0') {
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard";
            form.submit();
        } else {
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard?dashboard_option=plant_head_production";
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
            let url = "<?= base_url() ?>dashboard?dashboard_option=plant_head_production";
            window.location.href = url;
        }

    });
    document.addEventListener('DOMContentLoaded', function () {
        function checkFilters() {
            // let plant_id = document.querySelector('input[name="plant_id"]').value;
            let date = document.querySelector('input[name="date"]').value;
            let machine = document.querySelector('select[name="machine"]').value;
            let article = document.querySelector('select[name="article"]').value;
            let rawMaterial = document.querySelector('select[name="raw_material"]').value;

            if (date || machine || article || rawMaterial) {
                document.getElementById('removeFiltersBtn').style.display = 'inline-block';
            } else {
                document.getElementById('removeFiltersBtn').style.display = 'none';
            }
        }

        checkFilters();
        // document.querySelector('input[name="plant_id"]').addEventListener('change', checkFilters);
        document.querySelector('input[name="date"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="machine"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="article"]').addEventListener('change', checkFilters);
        document.querySelector('select[name="raw_material"]').addEventListener('change', checkFilters);
        document.getElementById('removeFiltersBtn').addEventListener('click', function () {
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        });
    });



</script>
<script>
    function submitForm() {
        document.getElementById('filterForm').submit();
    }
    $(function () {
        const ctx = document.getElementById('productionChart').getContext('2d');
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        const machineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [
                    {
                        label: 'Planned',
                        data: [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        yAxisID: 'y',
                        fill: false
                    },
                    {
                        label: 'Actual',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        yAxisID: 'y1',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Production Planned vs Actual'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Planned Quantity'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Actual Quantity'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        function updateActualChart(filters) {
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/get_prouction_planed_actual',
                type: 'POST',
                data: {
                    date: $('#date').val(),
                    machine: $('#machineFilter').val(),
                    article_id: $('#articleFilter').val(),
                    plant_id: $('#plant_id_filter').val()
                },
                dataType: 'json',
                success: function (response) {
                    machineChart.data.datasets = response.datasets;
                    machineChart.update();
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            updateActualChart();
        });

        updateActualChart();
    });


    $(function () {
        const ctx = document.getElementById('lineChart').getContext('2d');
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Initialize Chart
        const machineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: []
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Machine Production Utilization (Monthwise)'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });

        function updateChart(filters) {
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/get_machine_production_month',
                type: 'POST',
                data: {
                    date: $('#date').val(),
                    machine: $('#machineFilter').val(),
                    plant_id: $('#plant_id_filter').val()
                },
                dataType: 'json',
                success: function (response) {
                    if (response.datasets && response.datasets.length > 0) {
                        machineChart.data.datasets = response.datasets;
                        machineChart.update();
                    } else {
                        $('#errorMsg').text('No data available for the selected filters.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            const filters = {
                year: $('#year').val(),
                machine: $('#machine').val()
            };
            updateChart(filters);
        });

        updateChart({ year: '2025', machine: 'all' });
    });

    // New Charts
    $(function () {
        // Pie Chart for Machine-wise RM Consumption
        const rmCtx = document.getElementById('rmPieChart').getContext('2d');
        const machineData = [];
        const machineLabels = [];
        <?php foreach ($machine_production as $machine): ?>
            <?php $other_data = $this->Admin_model->get_other_rm_mb_rejection_data($machine->id); ?>
            machineLabels.push('<?php echo addslashes($machine->machine_name); ?>');
            machineData.push(<?php echo $other_data['total_rm']; ?>);
        <?php endforeach; ?>

        const rmPieChart = new Chart(rmCtx, {
            type: 'pie',
            data: {
                labels: machineLabels,
                datasets: [{
                    data: machineData,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    hoverBackgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + Number(context.raw).toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    });

    // Gauge Charts
    $(function () {
        // Rejection Gauge
        const rejectionCtx = document.getElementById('rejectionGauge').getContext('2d');
        const rejectionValue = <?php echo $metrics['rejection_percent']; ?>;
        const rejectionGauge = new Chart(rejectionCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [rejectionValue, 100 - rejectionValue],
                    backgroundColor: ['#FF6384', '#E0E0E0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                rotation: -90,
                circumference: 180,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            },
            plugins: [{
                id: 'rejection-gauge-text',
                afterDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                    const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2 + 20;
                    ctx.save();
                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = '#333';
                    ctx.textAlign = 'center';
                    ctx.fillText(rejectionValue + '%', centerX, centerY);
                    ctx.font = '14px Arial';
                    ctx.fillText('Rejection', centerX, centerY + 20);
                    ctx.restore();
                }
            }]
        });
    });

    $(function () {
        // MB Gauge
        const mbCtx = document.getElementById('mbGauge').getContext('2d');
        const mbValue = <?php echo $metrics['mb_percent']; ?>;
        const mbGauge = new Chart(mbCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [mbValue, 100 - mbValue],
                    backgroundColor: ['#FFCE56', '#E0E0E0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                rotation: -90,
                circumference: 180,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true
                }
            },
            plugins: [{
                id: 'mb-gauge-text',
                afterDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                    const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2 + 20;
                    ctx.save();
                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = '#333';
                    ctx.textAlign = 'center';
                    ctx.fillText(mbValue + '%', centerX, centerY);
                    ctx.font = '14px Arial';
                    ctx.fillText('MB', centerX, centerY + 20);
                    ctx.restore();
                }
            }]
        });
    });

    // Monthly Bar Chart
    $(function () {
        const barCtx = document.getElementById('monthlyBarChart').getContext('2d');
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        // Sample data - in real app, fetch from server
        const barData = [1200, 1500, 1800, 2000, 2200, 2500, 2800, 2600, 2400, 2100, 1900, 1700];

        const monthlyBarChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Monthly Production',
                    data: barData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        ticks: {
                            display: true,
                            color: '#6e7a89',
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });

</script>
<script>
    $(document).ready(function () {
        $('.filter-select').select2({
            theme: 'default',
            width: '100%'
        });

        var table = $('#example').DataTable({
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
            buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    title: 'Machine Data List',
                    filename: 'machine_wise_tabular_list',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                }
            ],
            scrollCollapse: true,

        });


        var table = $('#example-1').DataTable({
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
            buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    title: 'Article Analysis List',
                    filename: 'article_analysis_list',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                }
            ],
            scrollCollapse: true,

        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    flatpickr("#date", {
        mode: "range",  // Enable range selection
        dateFormat: "d-m-Y",  // Format: Day-Month-Year
        locale: 'en',  // Adjust to your desired locale
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                var formattedDate = selectedDates[0].toLocaleDateString('en-GB') + ' to ' + selectedDates[1].toLocaleDateString('en-GB');
                document.getElementById("date").value = formattedDate;
                submitForm();
            }
        },
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });
</script>