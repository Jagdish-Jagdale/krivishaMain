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

    .note-box {
        border: 1px dashed #7f8ea3;
        background: #f8fbff;
        padding: 10px;
        font-size: 13px;
        margin-bottom: 12px;
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
    <h3 class="page-h3">Printing Report</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="report-wrap">
                <form method="get" id="printing_report_filter" class="filter-row">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="all" <?= (!isset($_GET['report_type']) || $_GET['report_type'] == 'all') ? 'selected' : '' ?>>All Reports</option>
                            <option value="customer" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'customer') ? 'selected' : '' ?>>Customer Purchase History</option>
                            <option value="printing" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'printing') ? 'selected' : '' ?>>Printing Report</option>
                            <option value="store" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'store') ? 'selected' : '' ?>>Store Issue Report</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="text" name="date" id="date" class="form-control" placeholder="Select Date Range"
                            value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">
                    </div>
                    <div class="form-group" id="party_filter_group">
                        <label>Party Name</label>
                        <select name="party_id" id="party_id" class="form-control" style="width:100%; display:none;">
                            <option value="">All Parties</option>
                            <?php if (!empty($parties)) { foreach ($parties as $p) { ?>
                                <option value="<?= $p->id ?>" <?= (isset($_GET['party_id']) && $_GET['party_id'] == $p->id) ? 'selected' : '' ?>>
                                    <?= $p->party_name ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="form-group" id="brand_filter_group">
                        <label>Brand Name</label>
                        <select name="brand_id" id="brand_id" class="form-control" style="width:100%; display:none;">
                            <option value="">All Brands</option>
                            <?php if (!empty($brands)) { foreach ($brands as $b) { ?>
                                <option value="<?= $b->id ?>" <?= (isset($_GET['brand_id']) && $_GET['brand_id'] == $b->id) ? 'selected' : '' ?>>
                                    <?= $b->brand_name ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="form-group" id="article_filter_group">
                        <label>Container Size / Article</label>
                        <select name="article_id" id="article_id" class="form-control" style="width:100%; display:none;">
                            <option value="">All Sizes</option>
                            <?php if (!empty($articles)) { foreach ($articles as $a) { ?>
                                <option value="<?= $a->id ?>" <?= (isset($_GET['article_id']) && $_GET['article_id'] == $a->id) ? 'selected' : '' ?>>
                                    <?= $a->article_name ?>
                                </option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="action-row no-print">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                        <a href="<?= base_url('printing_report') ?>" class="btn btn-danger btn-sm">Reset</a>
                        <button type="button" class="btn btn-secondary btn-sm" id="print_report_btn">Print</button>
                        <button type="button" class="btn btn-success btn-sm" id="download_report_btn">Download</button>
                    </div>
                </form>
            </div>

            <div class="report-wrap report-section" id="customer_section">
                <h2 class="section-head">CUSTOMER PURCHASE TABLE HISTORY</h2>
                <table class="sheet-table" id="customer_purchase_table">
                    <thead>
                        <tr>
                            <th>SR NO</th>
                            <th>DATE</th>
                            <th>ORDER ID</th>
                            <th>PARTY NAME</th>
                            <th>BRAND APPLICABLE FOR CONTAINER</th>
                            <th>ARTICLE NAME</th>
                            <th>QTY</th>
                            <th>INK ASSOCIATE WITH BRAND APPLICABLE FOR CONTAINER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customer_purchase_history)) { ?>
                            <?php $index = 1; foreach ($customer_purchase_history as $row) { ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->order_date) ? date('d-m-Y', strtotime($row->order_date)) : '-' ?></td>
                                    <td><?= !empty($row->order_id) ? $row->order_id : '-' ?></td>
                                    <td><?= !empty($row->party_name) ? $row->party_name : '-' ?></td>
                                    <td><?= !empty($row->brand_name) ? $row->brand_name : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td class="text-right"><?= (int) $row->dispatch_quantity ?></td>
                                    <td><?= !empty($row->ink_associated) ? $row->ink_associated : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No customer purchase records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="printing_section">
                <h2 class="section-head">PRINTING REPORT</h2>
                <table class="sheet-table" id="printing_report_table">
                    <thead>
                        <tr>
                            <th>SR NO</th>
                            <th>DATE</th>
                            <th>ORDER ID</th>
                            <th>PARTY NAME</th>
                            <th>BRAND</th>
                            <th>ARTICLE NAME</th>
                            <th>APPROVED QTY</th>
                            <th>TOTAL IMPRESSION</th>
                            <th>IMPRESSION RATE</th>
                            <th>TOTAL IMP AMOUNT</th>
                            <th>REMARK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($printing_report_data)) { ?>
                            <?php $index = 1; foreach ($printing_report_data as $row) { ?>
                                <?php 
                                    $total_impression = (float) $row->approvd_qty * (float) $row->color_job; 
                                    $impression_rate = !empty($row->impression_rate) ? (float) $row->impression_rate : 0;
                                    $total_amount = $total_impression * $impression_rate;
                                ?>
                                <tr>
                                    <td><?= $index++ ?></td>
                                    <td><?= !empty($row->created_on) ? date('d-m-Y', strtotime($row->created_on)) : '-' ?></td>
                                    <td><?= !empty($row->order_id) ? $row->order_id : '-' ?></td>
                                    <td><?= !empty($row->party_name) ? $row->party_name : '-' ?></td>
                                    <td><?= !empty($row->brand_name) ? $row->brand_name : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td class="text-right"><?= (float) $row->approvd_qty ?></td>
                                    <td class="text-right"><?= number_format($total_impression, 2) ?></td>
                                    <td class="text-right"><?= $impression_rate > 0 ? number_format($impression_rate, 2) : '-' ?></td>
                                    <td class="text-right"><?= $total_amount > 0 ? number_format($total_amount, 2) : '-' ?></td>
                                    <td><?= !empty($row->remark) ? $row->remark : '-' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="11" class="text-center">No printing report records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="store_section">
                <h2 class="section-head">STORE ISSUE REPORT</h2>
                <!-- <p style="margin-bottom:10px; font-weight:700;">Filters required group wise</p> -->
                <table class="sheet-table" id="store_issue_table">
                    <thead>
                        <tr>
                            <th>SR NO</th>
                            <th>DATE</th>
                            <th>MATERIAL NAME</th>
                            <th>QTY</th>
                            <th>RATE</th>
                            <th>TOTAL AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($store_issue_report)) {
                            $si = 1;
                            foreach ($store_issue_report as $row) { 
                                $qty = !empty($row->total_qty) ? (float) $row->total_qty : 0;
                                $rate = !empty($row->material_rate) ? (float) $row->material_rate : 0;
                                $total_amount = $qty * $rate;
                        ?>
                                <tr>
                                    <td class="text-center"><?= $si++ ?></td>
                                    <td class="text-center"><?= !empty($row->issue_date) ? date('d-m-Y', strtotime($row->issue_date)) : '-' ?></td>
                                    <td><?= !empty($row->material_name) ? $row->material_name : '-' ?></td>
                                    <td class="text-right"><?= number_format($qty, 2) ?></td>
                                    <td class="text-right"><?= $rate > 0 ? number_format($rate, 2) : '-' ?></td>
                                    <td class="text-right"><?= $total_amount > 0 ? number_format($total_amount, 2) : '-' ?></td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="6" class="text-center">No store issue records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <span id="print_user_name" style="display:none;"><?= $this->session->userdata('name') ?></span>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#printing_unit').addClass('nv active');
        $('.printing_report').addClass('active_cc');

        function applyReportFilter(type) {
            $('.report-section').hide();

            if (type === 'customer') {
                $('#customer_section').show();
                $('#party_filter_group').show();
                $('#brand_filter_group').hide();
                $('#article_filter_group').hide();
            } else if (type === 'printing') {
                $('#printing_section').show();
                $('#party_filter_group').show();
                $('#brand_filter_group').show();
                $('#article_filter_group').show();
            } else if (type === 'store') {
                $('#store_section').show();
                $('#party_filter_group').hide();
                $('#brand_filter_group').hide();
                $('#article_filter_group').hide();
            } else {
                $('.report-section').show();
                $('#party_filter_group').show();
                $('#brand_filter_group').show();
                $('#article_filter_group').show();
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
                '<h2 style="margin:0 0 10px;font-size:15px;color:#333;">PRINTING REPORT</h2>' +
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

                // Get table
                $section.find('table').each(function() {
                    var $table = $(this);
                    
                    // If it's a DataTable, we might want all rows. 
                    // But for simple implementation, we just get what's in the DOM.
                    // Note: If you want all rows from a server-side DT, you'd need the data object.
                    // Here we assume client-side DT or just rows currently rendered.
                    
                    var tableHtml = '<table style="width:100%;border-collapse:collapse;margin-bottom:18px;">';
                    var $thead = $table.find('thead');
                    if ($thead.length) tableHtml += '<thead>' + $thead.html() + '</thead>';
                    
                    tableHtml += '<tbody>';
                    $table.find('tbody tr').each(function() {
                        tableHtml += '<tr>' + $(this).html() + '</tr>';
                    });
                    tableHtml += '</tbody>';
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

            return '<html><head><title>Printing Report - KRIVISHA PVT LTD</title><style>' + styles + '</style></head>' +
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
            link.download = 'printing_report_' + new Date().toISOString().slice(0, 10) + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        });

        // Keep each report table on DataTables, but let users change page size.
        if ($.fn.DataTable) {
            var tableOptions = {
                "paging": true,
                "lengthChange": true,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100, -1],
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

            $('#customer_purchase_table').DataTable(tableOptions);
            $('#printing_report_table').DataTable(tableOptions);
            $('#store_issue_table').DataTable(tableOptions);

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

    // Old version removed from here

    // Searchable Select2 for Party and Brand filters
    if ($.fn.select2) {
        if (!$('#party_id').hasClass('select2-hidden-accessible')) {
            $('#party_id').select2({
                placeholder: 'Search Party...',
                allowClear: true,
                width: '100%'
            });
        }
        if (!$('#brand_id').hasClass('select2-hidden-accessible')) {
            $('#brand_id').select2({
                placeholder: 'Search Brand...',
                allowClear: true,
                width: '100%'
            });
        }
        if (!$('#article_id').hasClass('select2-hidden-accessible')) {
            $('#article_id').select2({
                placeholder: 'Search Size...',
                allowClear: true,
                width: '100%'
            });
        }
    }

    // Filter Brand Name based on selected Party Name
    $('#party_id').on('change', function() {
        var party_id = $(this).val();
        var brandSelect = $('#brand_id');
        var currentBrandId = "<?= isset($_GET['brand_id']) ? $_GET['brand_id'] : '' ?>";
        if (brandSelect.val()) {
            currentBrandId = brandSelect.val(); // Remember current selection if user is actively changing
        }
        
        brandSelect.empty().append('<option value="">All Brands</option>');

        if (party_id) {
            $.ajax({
                url: "<?= base_url('admin/Ajax_controller/get_all_brand_by_party'); ?>",
                type: "POST",
                data: { party_id: party_id },
                dataType: "json",
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(index, brand) {
                            brandSelect.append('<option value="' + brand.id + '">' + brand.brand_name + '</option>');
                        });
                        // Restore previous selection if it's still available
                        if (currentBrandId && brandSelect.find("option[value='" + currentBrandId + "']").length > 0) {
                            brandSelect.val(currentBrandId);
                        }
                    }
                    brandSelect.trigger('change.select2');
                }
            });
        } else {
            brandSelect.trigger('change.select2');
        }
    });

    // Run on page load if a party is already selected
    if ($('#party_id').val()) {
        $('#party_id').trigger('change');
    }
</script>
