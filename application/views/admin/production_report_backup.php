<?php include('header.php'); ?>

<style type="text/css">
    .report-wrap {
        border: 1px solid #cfd5dd;
        border-radius: 6px;
        background: #fff;
        padding: 16px;
        margin-bottom: 20px;
    }

    .sheet-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }

    .sheet-table th,
    .sheet-table td {
        border: 1px solid #222;
        padding: 8px;
        vertical-align: middle;
        font-size: 13px;
    }

    .sheet-table thead th {
        background: #f2f2f2;
        font-weight: 700;
        white-space: nowrap;
    }

    .page-h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }

    .section-head {
        font-size: 24px;
        font-weight: 800;
        text-transform: uppercase;
        margin: 0 0 10px;
        color: #11213f;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: end;
        margin-bottom: 16px;
    }

    .filter-row .form-group {
        min-width: 220px;
        margin: 0;
    }

    .action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: end;
        margin-left: auto;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .summary-card {
        border: 1px solid #d7dde5;
        border-radius: 8px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 14px 16px;
    }

    .summary-card .label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #5a6472;
        margin-bottom: 6px;
    }

    .summary-card .value {
        font-size: 22px;
        font-weight: 800;
        color: #11213f;
    }

    .summary-card .subvalue {
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }

    .report-meta {
        border: 1px dashed #9eb0c8;
        background: #f8fbff;
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 14px;
        color: #22304a;
        font-size: 13px;
    }

    .report-meta strong {
        display: inline-block;
        min-width: 120px;
    }

    .text-right {
        text-align: right;
    }

    @media print {
        .no-print,
        .action-row,
        .filter-row,
        .summary-grid,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing {
            display: none !important;
        }

        .report-wrap {
            border: none;
            padding: 0;
            margin-bottom: 14px;
        }

        .sheet-table {
            page-break-inside: auto;
        }

        .sheet-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>

<div class="right_col">
    <h3 class="page-h3">Production Report Sheet</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="report-wrap">
                <form method="get" id="production_report_filter" class="filter-row">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="all" <?= (isset($report_type) && $report_type === 'all') ? 'selected' : '' ?>>All Sections</option>
                            <option value="overview" <?= (isset($report_type) && $report_type === 'overview') ? 'selected' : '' ?>>Production Overview</option>
                            <option value="summary" <?= (isset($report_type) && $report_type === 'summary') ? 'selected' : '' ?>>Summary</option>
                            <option value="details" <?= (isset($report_type) && $report_type === 'details') ? 'selected' : '' ?>>Article Details</option>
                            <option value="rejection" <?= (isset($report_type) && $report_type === 'rejection') ? 'selected' : '' ?>>Rejection</option>
                            <option value="balance" <?= (isset($report_type) && $report_type === 'balance') ? 'selected' : '' ?>>Balance Quantity</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="text" name="date" id="date" class="form-control" placeholder="Select Date Range" value="<?= isset($selected_date) ? $selected_date : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Machine</label>
                        <select name="machine_id" id="machine_id" class="form-control">
                            <option value="">All Machines</option>
                            <?php if (!empty($machine)) { ?>
                                <?php foreach ($machine as $machine_row) { ?>
                                    <option value="<?= $machine_row->id ?>" <?= (!empty($selected_machine_id) && (string) $selected_machine_id === (string) $machine_row->id) ? 'selected' : '' ?>>
                                        <?= $machine_row->machine_name ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Article</label>
                        <select name="article_id" id="article_id" class="form-control">
                            <option value="">All Articles</option>
                            <?php if (!empty($article)) { ?>
                                <?php foreach ($article as $article_row) { ?>
                                    <option value="<?= $article_row->id ?>" <?= (!empty($selected_article_id) && (string) $selected_article_id === (string) $article_row->id) ? 'selected' : '' ?>>
                                        <?= $article_row->article_name ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="action-row no-print">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <a href="<?= base_url('production_report') ?>" class="btn btn-danger btn-sm">Reset</a>
                        <button type="button" class="btn btn-secondary btn-sm" id="print_report_btn">Print</button>
                        <button type="button" class="btn btn-success btn-sm" id="download_report_btn">Download</button>
                    </div>
                </form>
            </div>

            <div class="report-meta" id="production_meta">
                <div><strong>Report:</strong> Production Report Sheet</div>
                <div><strong>Date Filter:</strong> <?= !empty($selected_date) ? $selected_date : 'All dates' ?></div>
                <div><strong>Machine:</strong> <?= !empty($selected_machine_id) ? 'Selected' : 'All machines' ?></div>
                <div><strong>Article:</strong> <?= !empty($selected_article_id) ? 'Selected' : 'All articles' ?></div>
            </div>

            <div class="summary-grid" id="production_summary_cards">
                <div class="summary-card">
                    <span class="label">Overview Entries</span>
                    <div class="value"><?= (int) $overview_count ?></div>
                    <div class="subvalue">Production report rows</div>
                </div>
                <div class="summary-card">
                    <span class="label">Summary Qty</span>
                    <div class="value"><?= number_format((float) $summary_total_approved_qty, 2) ?></div>
                    <div class="subvalue">Approved quantity total</div>
                </div>
                <div class="summary-card">
                    <span class="label">Detail Qty</span>
                    <div class="value"><?= number_format((float) $detail_total_approved_qty, 2) ?></div>
                    <div class="subvalue">Article production qty</div>
                </div>
                <div class="summary-card">
                    <span class="label">Rejection Qty</span>
                    <div class="value"><?= number_format((float) $rejection_total_qty, 2) ?></div>
                    <div class="subvalue">Rejected material total</div>
                </div>
            </div>

            <div class="report-wrap report-section" id="overview_section">
                <h2 class="section-head">Production Overview</h2>
                <table class="sheet-table" id="overview_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Group of Article</th>
                            <th>Articles</th>
                            <th>Raw Materials</th>
                            <th>Master Batch</th>
                            <th>Rejection</th>
                            <th>Pictures</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($overview_rows)) { ?>
                            <?php $index = 1; foreach ($overview_rows as $row) { ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->article_group) ? $row->article_group : '-' ?></td>
                                    <td><?= !empty($row->article_names) ? $row->article_names : '-' ?></td>
                                    <td><?= !empty($row->raw_material_names) ? $row->raw_material_names : '-' ?></td>
                                    <td><?= !empty($row->master_batch_names) ? $row->master_batch_names : '-' ?></td>
                                    <td><?= !empty($row->rejection_names) ? $row->rejection_names : '-' ?></td>
                                    <td class="text-right"><?= (int) $row->image_count ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="text-center">No production overview records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="summary_section">
                <h2 class="section-head">Production Summary</h2>
                <table class="sheet-table" id="summary_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Article</th>
                            <th>Approved Qty</th>
                            <th>Average Qty</th>
                            <th>Total Weight</th>
                            <th>Delta</th>
                            <th>Status</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($summary_rows)) { ?>
                            <?php $index = 1; foreach ($summary_rows as $row) { ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->approved_qty), 2) ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->average_qty), 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->total_weight, 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->delta, 3) ?></td>
                                    <td><?= $row->status == '0' ? 'Approved' : 'Pending' ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="text-center">No production summary records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="details_section">
                <h2 class="section-head">Article Production Details</h2>
                <table class="sheet-table" id="details_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Article</th>
                            <th>Approved Qty</th>
                            <th>Average Qty</th>
                            <th>Total Hourly Qty</th>
                            <th>Total Hourly Weight</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($detail_rows)) { ?>
                            <?php $index = 1; foreach ($detail_rows as $row) { ?>
                                <?php
                                    $qty_fields = [
                                        $row->qty_eight_nine,
                                        $row->qty_nine_ten,
                                        $row->qty_ten_eleven,
                                        $row->qty_eleven_twelve,
                                        $row->qty_twelve_thirteen,
                                        $row->qty_thirteen_fourteen,
                                        $row->qty_fourteen_fifteen,
                                        $row->qty_fifteen_sixteen,
                                        $row->qty_sixteen_seventeen,
                                        $row->qty_seventeen_eighteen,
                                        $row->qty_eighteen_nineteen,
                                        $row->qty_nineteen_twenty,
                                        $row->qty_twenty_twentyone,
                                        $row->qty_twentyone_twentytwo,
                                        $row->qty_twentytwo_twentythree,
                                        $row->qty_twentythree_zero,
                                        $row->qty_zero_one,
                                        $row->qty_one_two,
                                        $row->qty_two_three,
                                        $row->qty_three_four,
                                        $row->qty_four_five,
                                        $row->qty_five_six,
                                        $row->qty_six_seven,
                                        $row->qty_seven_eight,
                                    ];
                                    $weight_fields = [
                                        $row->weight_eight_nine,
                                        $row->weight_nine_ten,
                                        $row->weight_ten_eleven,
                                        $row->weight_eleven_twelve,
                                        $row->weight_twelve_thirteen,
                                        $row->weight_thirteen_fourteen,
                                        $row->weight_fourteen_fifteen,
                                        $row->weight_fifteen_sixteen,
                                        $row->weight_sixteen_seventeen,
                                        $row->weight_seventeen_eighteen,
                                        $row->weight_eighteen_nineteen,
                                        $row->weight_nineteen_twenty,
                                        $row->weight_twenty_twentyone,
                                        $row->weight_twentyone_twentytwo,
                                        $row->weight_twentytwo_twentythree,
                                        $row->weight_twentythree_zero,
                                        $row->weight_zero_one,
                                        $row->weight_one_two,
                                        $row->weight_two_three,
                                        $row->weight_three_four,
                                        $row->weight_four_five,
                                        $row->weight_five_six,
                                        $row->weight_six_seven,
                                        $row->weight_seven_eight,
                                    ];
                                    $total_hourly_qty = 0;
                                    $total_hourly_weight = 0;
                                    foreach ($qty_fields as $qty_value) {
                                        $total_hourly_qty += (float) str_replace(',', '', (string) $qty_value);
                                    }
                                    foreach ($weight_fields as $weight_value) {
                                        $total_hourly_weight += (float) str_replace(',', '', (string) $weight_value);
                                    }
                                ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->approved_qty), 2) ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->average_qty), 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $total_hourly_qty, 2) ?></td>
                                    <td class="text-right"><?= number_format((float) $total_hourly_weight, 2) ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9" class="text-center">No article production detail records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="rejection_section">
                <h2 class="section-head">Rejection Report</h2>
                <table class="sheet-table" id="rejection_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Rejection</th>
                            <th>Total Qty</th>
                            <th>PC</th>
                            <th>Runner Gms</th>
                            <th>Flash Gm</th>
                            <th>Lumps Gm</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rejection_rows)) { ?>
                            <?php $index = 1; foreach ($rejection_rows as $row) { ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->rejection_name) ? $row->rejection_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) $row->total_qty, 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->pc, 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->runner_gms, 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->flash_gm, 3) ?></td>
                                    <td class="text-right"><?= number_format((float) $row->lumps_gm, 3) ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="text-center">No rejection records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="balance_section">
                <h2 class="section-head">Balance Quantity Report</h2>
                <table class="sheet-table" id="balance_table">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Raw Material</th>
                            <th>RM Total Qty</th>
                            <th>Master Batch</th>
                            <th>MB Total Qty</th>
                            <th>Approval</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($balance_rows)) { ?>
                            <?php $index = 1; foreach ($balance_rows as $row) { ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->raw_material_name) ? $row->raw_material_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->rm_total_qty), 3) ?></td>
                                    <td><?= !empty($row->master_batch_name) ? $row->master_batch_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $row->mb_total_qty), 3) ?></td>
                                    <td><?= $row->plant_manager_approval_status === '0' ? 'Approved' : 'Pending' ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="9" class="text-center">No balance quantity records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#product_master').addClass('active');
        $('.production_report').addClass('active_cc');

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#date', {
                mode: 'range',
                dateFormat: 'd-m-Y'
            });
        }

        function applyReportFilter(type) {
            $('.report-section').hide();

            if (type === 'overview') {
                $('#overview_section').show();
            } else if (type === 'summary') {
                $('#summary_section').show();
            } else if (type === 'details') {
                $('#details_section').show();
            } else if (type === 'rejection') {
                $('#rejection_section').show();
            } else if (type === 'balance') {
                $('#balance_section').show();
            } else {
                $('.report-section').show();
            }
        }

        applyReportFilter($('#report_type').val());

        $('#report_type').on('change', function() {
            applyReportFilter($(this).val());
        });

        function buildExportHtml() {
            var exportSections = [];
            $('.report-meta, .summary-grid, .report-section:visible').each(function() {
                exportSections.push($(this).prop('outerHTML'));
            });

            return '<html><head><title>Production Report Sheet</title><style>body{font-family:Arial,sans-serif;padding:20px;color:#222;} table{width:100%;border-collapse:collapse;margin-bottom:18px;} th,td{border:1px solid #222;padding:8px;vertical-align:middle;font-size:12px;} th{background:#f2f2f2;} h2.section-head{font-size:18px;margin:18px 0 10px;text-transform:uppercase;} .report-wrap{margin-bottom:20px;} .text-right{text-align:right;} .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px;} .summary-card{border:1px solid #d7dde5;border-radius:8px;padding:14px 16px;} .summary-card .label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;margin-bottom:6px;} .summary-card .value{font-size:22px;font-weight:800;}</style></head><body><h2 style="font-family:Arial,sans-serif;margin:0 0 14px;">PRODUCTION REPORT SHEET</h2>' + exportSections.join('') + '</body></html>';
        }

        $('#print_report_btn').on('click', function() {
            var reportWindow = window.open('', '_blank', 'width=1400,height=900');
            reportWindow.document.open();
            reportWindow.document.write(buildExportHtml());
            reportWindow.document.close();
            reportWindow.focus();
            reportWindow.print();
        });

        $('#download_report_btn').on('click', function() {
            var html = buildExportHtml();
            var blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
            var url = window.URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'production_report_sheet_' + new Date().toISOString().slice(0, 10) + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        });

        if ($.fn.DataTable) {
            const tableOptions = {
                pageLength: 10,
                lengthChange: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                ordering: false,
                searching: false,
                info: true
            };

            $('#overview_table').DataTable(tableOptions);
            $('#summary_table').DataTable(tableOptions);
            $('#details_table').DataTable(tableOptions);
            $('#rejection_table').DataTable(tableOptions);
            $('#balance_table').DataTable(tableOptions);
        }
    });
</script>
