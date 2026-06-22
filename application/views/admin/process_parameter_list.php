<?php include('header.php'); ?>
<style>
    .page_sec { border:1px solid #ccc; border-radius:5px; padding:20px; margin-bottom:20px; }
    .badge-shift { font-size:12px; padding:4px 8px; border-radius:4px; }
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>

<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Process Parameter Sheet – Daily Report</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('message') ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Filter -->
            <div class="page_sec">
                <form method="get" id="filter_form">
                    <div class="row">
                        <div class="form-group col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <label>Date Range</label>
                            <input type="text" name="date" id="date" class="form-control daterangepicker_input"
                                autocomplete="off" readonly style="cursor:pointer; background:#fff;"
                                placeholder="Select Date Range"
                                value="<?= isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '' ?>">
                        </div>
                        <div class="form-group col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <label>Machine</label>
                            <select name="machine_name" id="machine_name" class="form-control">
                                <option value="">All Machines</option>
                                <?php if (!empty($machines)): foreach ($machines as $m): ?>
                                    <option value="<?= htmlspecialchars($m->machine_name) ?>"
                                        <?= (isset($_GET['machine_name']) && $_GET['machine_name'] == $m->machine_name) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m->machine_name) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <label>Plant</label>
                            <select name="plant_id" id="plant_id" class="form-control">
                                <option value="">All Plants</option>
                                <?php if (!empty($plants)): foreach ($plants as $p): ?>
                                    <option value="<?= $p->id ?>"
                                        <?= (isset($_GET['plant_id']) && $_GET['plant_id'] == $p->id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p->plant_name) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <label>Article</label>
                            <select name="article_name" id="article_name" class="form-control">
                                <option value="">All Articles</option>
                                <?php if (!empty($articles)): foreach ($articles as $a): ?>
                                    <option value="<?= htmlspecialchars($a->article_name) ?>"
                                        <?= (isset($_GET['article_name']) && $_GET['article_name'] == $a->article_name) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a->article_name) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-12 mt-3">
                            <button type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url('process_parameter_list') ?>" class="btn btn-sm btn-danger">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="x_panel">
                <div class="x_content">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="pp_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Machine</th>
                                    <th>Article</th>
                                    <th>Shift</th>
                                    <th>Material</th>
                                    <th>Colour (MB)</th>
                                    <th>Cycle Time (s)</th>
                                    <th>Article Wt (g)</th>
                                    <th>Plant</th>
                                    <th>Filled By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Build filter params
                                $date_range   = isset($_GET['date'])         ? trim($_GET['date'])         : '';
                                $machine_name = isset($_GET['machine_name']) ? trim($_GET['machine_name']) : '';
                                $plant_id     = isset($_GET['plant_id'])     ? trim($_GET['plant_id'])     : '';
                                $article_name = isset($_GET['article_name']) ? trim($_GET['article_name']) : '';
                                $from_date = $to_date = '';
                                if (!empty($date_range)) {
                                    $normalized = str_replace(' - ', ' to ', $date_range);
                                    $parts = explode('to', $normalized);
                                    if (!empty(trim($parts[0]))) $from_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($parts[0]))));
                                    if (!empty(trim($parts[1] ?? ''))) $to_date = date('Y-m-d', strtotime(str_replace('/', '-', trim($parts[1]))));
                                    else $to_date = $from_date;
                                }
                                $rows = $this->Admin_model->get_process_parameter_list($from_date, $to_date, $machine_name, $plant_id, $article_name);
                                ?>
                                <?php if (!empty($rows)): $i = 1; foreach ($rows as $row): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                        <td><?= htmlspecialchars($row->machine_name ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row->article_name ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row->shift ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row->material ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row->colour ?? '-') ?></td>
                                        <td><?= ($row->cycle_time !== null) ? number_format((float)$row->cycle_time, 3) : '-' ?></td>
                                        <td><?= ($row->article_wt !== null) ? number_format((float)$row->article_wt, 3) : '-' ?></td>
                                        <td><?= htmlspecialchars($row->plant_name ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row->employee_name ?? '-') ?></td>
                                        <td>
                                            <a href="<?= base_url('process_parameter_view/' . $row->id) ?>"
                                               class="btn btn-sm btn-info" title="View Full Form">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center">No records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function () {
    // Highlight sidebar
    $('#product_master').addClass('nv active');
    $('.process_parameter_list').addClass('active_cc');

    // Date range picker
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#date', {
            mode: 'range',
            dateFormat: 'd-m-Y',
            rangeSeparator: ' - ',
            clickOpens: true,
            allowInput: false,
            disableMobile: true
        });
    } else if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#date').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'DD-MM-YYYY' }
        });
        $('#date').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        });
        $('#date').on('cancel.daterangepicker', function () { $(this).val(''); });

        $('#date').on('focus click', function () {
            $(this).data('daterangepicker').show();
        });
    }

    // DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#pp_table').DataTable({ order: [[0, 'desc']], pageLength: 25 });
    }
});
</script>
