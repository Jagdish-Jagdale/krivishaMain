<?php include('header.php'); ?>

<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<div class="right_col">


    <div class="dashboard">

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
                            <select class="form-select filter-select" id="entryStatus" name="article"
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
        <!-- Top Metrics -->
        <div class="metrics-row">
            <div class="metric-card mb-card color-8">
                <a href="<?= base_url('auto_task_list?date=&production_pending_task=1') ?>" class="metric-link"
                    style="text-decoration:none; color: inherit;">
                    <h3>Pending Tasks </h3>
                    <div class="metric-value"><?= $metrics['pending_task'] ?></div>
                </a>
            </div>
            <div class="metric-card color-9">
                <h3>Total Consumption of RM</h3>
                <div class="metric-value"><?= number_format($metrics['total_rm']) ?></div>
            </div>
            <div class="metric-card color-9">
                <h3>Total Consumption of MB</h3>
                <div class="metric-value"><?= number_format($metrics['total_mb']) ?></div>
            </div>
            <div class="metric-card color-6">
                <h3>Rejection %</h3>
                <div class="metric-value"><?= $metrics['rejection_percent'] ?></div>
            </div>
            <div class="metric-card mb-card color-5">
                <h3>MB %</h3>
                <div class="metric-value"><?= $metrics['mb_percent'] ?></div>
            </div>
            <div class="metric-card mb-card color-3">
                <a href="<?= base_url('production_maintenance_list?status_of_work=1') ?>" class="metric-link"
                    style="text-decoration:none; color: inherit;">
                    <h3>Maintenance Pending</h3>
                    <div class="metric-value"><?= $metrics['pending_maintenance'] ?></div>
                </a>
            </div>

        </div>
        <!-- Main Content -->
        <div class="data-table">
            <h5>Machine wise tabular view</h5>
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
        <br>
        <h5>Machine production Utilization (Monthwise)</h5>
        <div class="chart-container">
            <canvas id="lineChart" width="400" height="200"></canvas>

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


        // Responsive chart resize
        $(window).on('resize', function () {
            if (lineChart) {
                lineChart.resize();
            }
        });
    });
</script>
<script>
    function submitForm() {
        var dashboard_option = document.getElementById('dashboard_option').value;
        if(dashboard_option == '0'){
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard";
            form.submit();
        }else{
            var form = document.getElementById('filterForm');
            form.action = "<?= base_url() ?>dashboard?dashboard_option=production_supervisor";
            form.submit();
        }
    }
    document.getElementById('removeFiltersBtn').addEventListener('click', function () {
        var dashboard_option = document.getElementById('dashboard_option').value;
        if(dashboard_option == '0'){
            let url = new URL(window.location.href);
            url.search = '';
            window.location.href = url.origin + url.pathname;
        }else{
            var url = "<?= base_url() ?>dashboard?dashboard_option=production_supervisor";
            window.location.href = url;
        }
       
    });
    document.addEventListener('DOMContentLoaded', function () {
        function checkFilters() {
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
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
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
</script>
<script>
    $(document).ready(function () {
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