<?php include('header.php'); ?>

<style type="text/css">
    .dataTables_length {
        margin: 0 !important;
        font-size: 13px;
        color: #000;
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        float: none !important;
        clear: both !important;
        padding: 0 !important;
    }

    .dataTables_length label {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        white-space: nowrap !important;
        font-weight: normal !important;
        gap: 5px !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 30px !important;
        line-height: 30px !important;
    }

    .dataTables_length select {
        padding: 4px 8px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        margin: 0 5px !important;
        display: inline-block !important;
        width: auto !important;
        height: 30px !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .top {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 10px !important;
        width: 100% !important;
        height: 30px !important;
    }

    .top .dataTables_length,
    .top .dataTables_filter {
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        height: 30px !important;
    }

    .dataTables_filter {
        margin: 0 !important;
        font-size: 13px;
        color: #000;
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        float: none !important;
        clear: both !important;
        padding: 0 !important;
    }

    .dataTables_filter label {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        white-space: nowrap !important;
        font-weight: normal !important;
        gap: 5px !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 30px !important;
        line-height: 30px !important;
    }

    .dataTables_filter input {
        padding: 4px 8px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        margin: 0 0 0 5px !important;
        color: #000 !important;
        width: 180px !important;
        height: 30px !important;
        display: inline-block !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .report-section table, .page_body table {
        margin-top: 0 !important;
    }

    .report-wrap {
        border: 1px solid #cfd5dd;
        border-radius: 6px;
        background: #fff;
        padding: 16px;
        margin-bottom: 20px;
        color: #000;
    }

    .report-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 12px;
        color: #0f2b5b;
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
        color: #000;
    }

    .sheet-table thead th {
        background: #eaf2ff !important;
        color: #333;
        font-weight: 700;
        font-size: 12px;
        padding: 10px 8px;
        border: 1px solid #d1d9e6;
        text-align: center;
    }

    .sheet-table td {
        border: 1px solid #d1d9e6 !important;
        padding: 8px;
        color: #000;
        font-size: 12px;
    }

    .section-head {
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        margin: 20px 0 0;
        background: #0056d0;
        color: #fff;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        text-align: left;
        letter-spacing: 0.5px;
    }

    .report-section table {
        margin-top: 0 !important;
        border-top: none !important;
    }

    .text-right {
        text-align: right;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: end;
        margin-bottom: 16px;
    }

    .filter-row .form-group {
        min-width: 260px;
        margin: 0;
    }

    .filter-row .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .page-h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }

    .action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: end;
        margin-left: auto;
    }

    /* Removed old dataTables_length styles to use flex order above */

    .dataTables_length select {
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin: 0 5px;
    }

    @media print {
        .no-print,
        .action-row,
        .filter-row,
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_length,
        .dataTables_length,
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
    <h3 class="page-h3">Maintenance Report</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="report-wrap">
                <form method="get" id="maintenance_report_filter" class="filter-row">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="all" <?= (!isset($_GET['report_type']) || $_GET['report_type'] == 'all') ? 'selected' : '' ?>>All Reports</option>
                            <option value="pm" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'pm') ? 'selected' : '' ?>>PM Report</option>
                            <option value="bm" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'bm') ? 'selected' : '' ?>>BM Report</option>
                            <option value="maintenance" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'maintenance') ? 'selected' : '' ?>>Maintenance Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="text" name="date" id="date" class="form-control" placeholder="Select Date Range"
                            value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">
                    </div>
                    <div class="form-group" id="filter_type_of_action">
                        <label>Type of Action</label>
                        <select name="type_of_action" id="type_of_action" class="form-control">
                            <option value="">All Actions</option>
                            <option value="1" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '1') ? 'selected' : '' ?>>Emergency</option>
                            <option value="2" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '2') ? 'selected' : '' ?>>Online Breakdown</option>
                            <option value="3" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '3') ? 'selected' : '' ?>>Preventive</option>
                            <option value="4" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '4') ? 'selected' : '' ?>>Outside Work</option>
                            <option value="5" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '5') ? 'selected' : '' ?>>General</option>
                            <option value="6" <?= (isset($_GET['type_of_action']) && $_GET['type_of_action'] == '6') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group" id="filter_machine">
                        <label>Machine</label>
                        <select name="machine_id" id="machine_id" class="form-control" style="display:none; width:100%;">
                            <option value="">All Machines</option>
                            <?php foreach ($machine as $m): ?>
                                <option value="<?= $m->id ?>" <?= (isset($_GET['machine_id']) && $_GET['machine_id'] == $m->id) ? 'selected' : '' ?>><?= $m->machine_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="filter_mold">
                        <label>Mold (Article)</label>
                        <select name="mold_id" id="mold_id" class="form-control" style="display:none; width:100%;">
                            <option value="">All Molds</option>
                            <?php foreach ($article as $a): ?>
                                <option value="<?= $a->id ?>" <?= (isset($_GET['mold_id']) && $_GET['mold_id'] == $a->id) ? 'selected' : '' ?>><?= $a->article_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="action-row no-print">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <a href="<?= base_url('maintenance_report') ?>" class="btn btn-danger btn-sm">Reset</a>
                        <button type="button" class="btn btn-secondary btn-sm" id="print_report_btn">Print</button>
                        <button type="button" class="btn btn-success btn-sm" id="download_report_btn">Download</button>
                    </div>
                </form>
            </div>

            <div class="report-wrap report-section" id="pm_section">
                <h2 class="section-head">Preventive Maintenance (PM) Schedule</h2>
                <table class="sheet-table" id="pm_table">
                    <thead>
                        <tr>
                            <th>DEPARTMENT</th>
                            <th>Machine</th>
                            <th>MOLD</th>
                            <th>Maintenance Activity</th>
                            <th>Frequency</th>
                            <th>Planned Date</th>
                            <th>Done Date</th>
                            <th>MEAN TIME BETWEEN FAILURE</th>
                            <th>COST INCURRED</th>
                            <th>Status</th>
                            <th>Technician</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pm_schedule)) { ?>
                            <?php foreach ($pm_schedule as $item) { ?>
                                <tr>
                                    <td><?= $item->department_name ?></td>
                                    <td><?= $item->machine_name ?></td>
                                    <td><?= $item->mold_name ?></td>
                                    <td><?= !empty($item->maintenance_activity) ? $item->maintenance_activity : '-' ?></td>
                                    <td><?= $item->frequency ?></td>
                                    <td><?= !empty($item->planned_date) ? date('d-m-Y', strtotime($item->planned_date)) : '-' ?></td>
                                    <td><?= !empty($item->done_date) ? date('d-m-Y', strtotime($item->done_date)) : '-' ?></td>
                                    <td><?= $item->mtbf_days > 0 ? $item->mtbf_days . ' Days' : '0 Days' ?></td>
                                    <td class="text-right"><?= number_format((float) $item->total_cost, 2) ?></td>
                                    <td><?= $item->work_status ?></td>
                                    <td><?= $item->technician_name ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="11" class="text-center">No preventive maintenance records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="bm_section">
                <h2 class="section-head">BREAKDOWN Maintenance (BM) Schedule</h2>
                <table class="sheet-table" id="bm_table">
                    <thead>
                        <tr>
                            <th>DEPARTMENT</th>
                            <th>Machine</th>
                            <th>MOLD</th>
                            <th>Maintenance Activity</th>
                            <th>PROBLEM DISCRIPTION</th>
                            <th>Planned Date</th>
                            <th>Done Date</th>
                            <th>MEAN TIME BETWEEN FAILURE</th>
                            <th>COST INCURRED</th>
                            <th>Status</th>
                            <th>Technician</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bm_schedule)) { ?>
                            <?php foreach ($bm_schedule as $item) { ?>
                                <tr>
                                    <td><?= $item->department_name ?></td>
                                    <td><?= $item->machine_name ?></td>
                                    <td><?= $item->mold_name ?></td>
                                    <td><?= $item->frequency ?></td>
                                    <td><?= !empty($item->maintenance_activity) ? $item->maintenance_activity : '-' ?></td>
                                    <td><?= !empty($item->planned_date) ? date('d-m-Y', strtotime($item->planned_date)) : '-' ?></td>
                                    <td><?= !empty($item->done_date) ? date('d-m-Y', strtotime($item->done_date)) : '-' ?></td>
                                    <td><?= $item->mtbf_days > 0 ? $item->mtbf_days . ' Days' : '0 Days' ?></td>
                                    <td class="text-right"><?= number_format((float) $item->total_cost, 2) ?></td>
                                    <td><?= $item->work_status ?></td>
                                    <td><?= $item->technician_name ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="11" class="text-center">No breakdown maintenance records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="maintenance_section">
                <h2 class="section-head">TOTAL COST REPORT ON MAINTAINANCE</h2>
                <table class="sheet-table" id="maintenance_table">
                    <thead>
                        <tr>
                            <th>DEPARTMENT</th>
                            <th>MACHINE</th>
                            <th>MOLD</th>
                            <th>TOTAL COST OF MATERIAL</th>
                            <th>LABOR COST</th>
                            <th>TOTAL COST</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cost_report)) { ?>
                            <?php foreach ($cost_report as $item) { ?>
                                <tr>
                                    <td><?= $item->department_name ?></td>
                                    <td><?= $item->machine_name ?></td>
                                    <td><?= $item->mold_name ?></td>
                                    <td class="text-right"><?= number_format((float) $item->total_material_cost, 2) ?></td>
                                    <td class="text-right"><?= number_format((float) $item->total_labour_cost, 2) ?></td>
                                    <td class="text-right"><?= number_format((float) $item->total_cost, 2) ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No maintenance cost records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- Hidden print meta info from PHP session -->
<span id="print_user_name" style="display:none;"><?= $this->session->userdata('name') ?></span>

<script>
    $(document).ready(function() {
        $('#maintenance').addClass('nv active');
        $('.maintenance_report').addClass('active_cc');

        function applyReportFilter(type) {
            $('.report-section').hide();

            if (type === 'pm' || type === 'bm') {
                if (type === 'pm') $('#pm_section').show();
                if (type === 'bm') $('#bm_section').show();
                
                $('#filter_type_of_action').show();
                $('#filter_machine').hide();
                $('#filter_mold').hide();
            } else if (type === 'maintenance') {
                $('#maintenance_section').show();
                
                $('#filter_type_of_action').hide();
                $('#filter_machine').show();
                $('#filter_mold').show();
            } else {
                $('.report-section').show();
                $('#filter_type_of_action').show();
                $('#filter_machine').show();
                $('#filter_mold').show();
            }
        }

        applyReportFilter($('#report_type').val());

        $('#report_type').on('change', function() {
            applyReportFilter($(this).val());
        });

        function buildExportHtml() {
            var printedBy = $('#print_user_name').text() || '-';
            var printDate = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });

            var header = '<div style="font-family:Arial,sans-serif;margin-bottom:18px;border-bottom:2px solid #0f2b5b;padding-bottom:12px;">' +
                '<h1 style="margin:0 0 4px;font-size:22px;color:#0f2b5b;">KRIVISHA PVT LTD</h1>' +
                '<h2 style="margin:0 0 10px;font-size:15px;color:#333;">MAINTENANCE REPORT</h2>' +
                '<table style="border:none;width:auto;margin:0;" cellpadding="3"><tr>' +
                '<td style="border:none;font-size:12px;"><strong>Print Date:</strong> ' + printDate + '</td>' +
                '<td style="border:none;font-size:12px;padding-left:30px;"><strong>Printed By:</strong> ' + printedBy + '</td>' +
                '</tr></table>' +
                '</div>';

            var exportSections = [];
            $('.report-section:visible').each(function() {
                var $section = $(this);
                var sectionHtml = '';

                // Get section heading
                var heading = $section.find('h2.section-head');
                if (heading.length) {
                    sectionHtml += '<h2 class="section-head" style="font-size:15px;font-weight:800;text-transform:uppercase;margin:18px 0 8px;">' + heading.text() + '</h2>';
                }

                // Get the actual table (all rows including hidden ones from DataTable pagination)
                $section.find('table').each(function() {
                    var $table = $(this);
                    var tableHtml = '<table style="width:100%;border-collapse:collapse;margin-bottom:18px;">';

                    // thead
                    var $thead = $table.find('thead');
                    if ($thead.length) {
                        tableHtml += '<thead>' + $thead.html() + '</thead>';
                    }

                    // All tbody rows (including DataTable hidden pages)
                    tableHtml += '<tbody>';
                    $table.find('tbody tr').each(function() {
                        tableHtml += '<tr>' + $(this).html() + '</tr>';
                    });
                    tableHtml += '</tbody>';

                    // tfoot if exists
                    var $tfoot = $table.find('tfoot');
                    if ($tfoot.length) {
                        tableHtml += '<tfoot>' + $tfoot.html() + '</tfoot>';
                    }

                    tableHtml += '</table>';
                    sectionHtml += tableHtml;
                });

                exportSections.push('<div style="margin-bottom:24px;">' + sectionHtml + '</div>');
            });

            var styles = 'body{font-family:Arial,sans-serif;padding:20px;color:#000;}' +
                'table{width:100%;border-collapse:collapse;margin-bottom:18px;margin-top:0 !important;}' +
                'th,td{border:1px solid #d1d9e6;padding:6px 8px;vertical-align:middle;font-size:11px;color:#000;}' +
                'th{background:#eef2f7 !important;font-weight:700;color:#333 !important;-webkit-print-color-adjust:exact;}' +
                'h2.section-head{font-size:15px;margin:20px 0 0;text-transform:uppercase;background:#0056d0;color:#fff !important;padding:10px 15px;font-weight:800;text-align:left;border-radius:4px 4px 0 0;-webkit-print-color-adjust:exact;}' +
                '.page_body, .report-section { display: flex; flex-direction: column; }' +
                '.report-section h2, .report-section h3, .section-head, .page_body h3 { order: 2; }' +
                '.report-section .dataTables_wrapper, .page_body .dataTables_wrapper { order: 3; display: flex; flex-direction: column; }' +
                '.dataTables_length { order: 1; margin: 10px 0; font-size: 13px; color: #000; }' +
                '.report-section table, .page_body table { order: 4; margin-top: 0 !important; }' +
                '.text-right{text-align:right;}' +
                '.text-center{text-align:center;}';

            return '<html><head><title>Maintenance Report - KRIVISHA PVT LTD</title><style>' + styles + '</style></head>' +
                '<body>' + header + exportSections.join('') + '</body></html>';
        }

        $('#print_report_btn').on('click', function() {
            var reportWindow = window.open('', '_blank', 'width=1200,height=800');
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
            link.download = 'maintenance_report_' + new Date().toISOString().slice(0, 10) + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        });

        if ($.fn.DataTable) {
            const tableOptions = {
                "paging": true,
                "lengthChange": true,
                "searching": false,
                "ordering": false,
                "info": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "language": {
                    "lengthMenu": "Show _MENU_ entries",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "dom": '<"top"lf>rt<"bottom"ip><"clear">'
            };

            $('#pm_table').DataTable(tableOptions);
            $('#bm_table').DataTable(tableOptions);
            $('#maintenance_table').DataTable(tableOptions);

            // Move the dataTables controls (.top wrapper containing length and filter) above the section heading (h2.section-head)
            $('.report-section').each(function() {
                var $section = $(this);
                var $top = $section.find('.top');
                var $head = $section.find('h2.section-head');
                if ($top.length && $head.length) {
                    $top.insertBefore($head);
                }
            });
        }
    });

    flatpickr('#date', {
        mode: 'range',
        dateFormat: 'd-m-Y'
    });

    // Searchable Select2 for Machine and Mold filters
    if ($.fn.select2) {
        $('#machine_id').select2({
            placeholder: 'Search Machine...',
            allowClear: true,
            width: '100%'
        });
        $('#mold_id').select2({
            placeholder: 'Search Mold...',
            allowClear: true,
            width: '100%'
        });
    }
</script>
