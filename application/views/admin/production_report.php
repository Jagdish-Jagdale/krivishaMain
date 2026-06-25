<?php include('header.php'); ?>

<!-- Show loading overlay immediately if navigation was triggered (before any content renders) -->
<script>
(function() {
    if (sessionStorage.getItem('report_loading') === '1') {
        sessionStorage.removeItem('report_loading');
        document.documentElement.style.visibility = 'hidden';
    }
})();
</script>

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
        max-width: 100%;
        overflow-x: hidden;
        color: #000; /* Dark font color */
    }

    /* Keep tables inside the page without horizontal scrolling */
    .dataTables_wrapper {
        max-width: 100%;
        overflow-x: auto;
    }
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        display: block !important;
        visibility: visible !important;
        margin-top: 10px !important;
        margin-bottom: 10px !important;
        color: #000 !important; /* Dark text for DataTables controls */
    }

    #overview_table {
        width: 100% !important;
        table-layout: fixed;
    }

    /* Better header readability */
    #overview_table thead th {
        white-space: normal !important;
        text-align: center;
        line-height: 1.15;
        vertical-align: middle;
    }

    #overview_table td {
        white-space: normal;
        color: #000;
    }

    .sheet-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
    }

    .sheet-table td {
        word-break: break-word;
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
        color: #333 !important;
        font-weight: 700;
        font-size: 12px;
        padding: 10px 8px;
        border: 1px solid #d1d9e6 !important;
        text-align: center;
    }

    .sheet-table td {
        border: 1px solid #d1d9e6 !important;
        padding: 8px;
        color: #000 !important;
        font-size: 12px;
    }

    .sheet-table tbody tr:nth-child(even) td {
        background: #f5f8ff !important;
    }

    .sheet-table tbody tr:hover td {
        background: #eaf0fd !important;
    }

    .shift-breakdown .line {
        padding: 2px 0;
        border-bottom: 1px solid #222;
    }

    .shift-breakdown .line:last-child {
        border-bottom: 0;
    }

    .shift-breakdown .total {
        margin-top: 2px;
        padding-top: 2px;
        border-top: 1px solid #222;
        font-weight: 700;
    }

    .downtime-reasons {
        margin-top: 4px;
        text-align: left;
        font-size: 10px;
        color: #c0392b;
    }

    .dt-reason-line {
        padding: 1px 0;
        border-top: 1px dashed #e0b9b9;
        line-height: 1.3;
    }

    .dt-reason-line:first-child {
        border-top: none;
    }

    /* Make the Plan vs Actual table compact so it fits without horizontal scroll */
    #overview_table th,
    #overview_table td {
        padding: 4px 6px !important;
        font-size: 11px;
        color: #000 !important;
    }

    #overview_table thead th {
        padding: 6px 6px !important;
        font-size: 11px;
    }

    .page-h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }

    .section-head {
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        margin: 20px 0 0;
        background: #0056d0;
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        text-align: left;
        letter-spacing: 0.5px;
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
        color: #000; /* Darker label */
        margin-bottom: 6px;
    }

    .summary-card .value {
        font-size: 22px;
        font-weight: 800;
        color: #11213f;
    }

    .summary-card .subvalue {
        margin-top: 4px;
        color: #333; /* Darker subvalue */
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

    /* ── Sheet Report Tables ─────────────────────────────── */
    .share-sheet {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        margin-bottom: 18px;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,86,208,0.08);
    }

    .share-sheet th,
    .share-sheet td {
        border: 1px solid #dce3f0;
        padding: 9px 12px;
        font-size: 12px;
        vertical-align: middle;
        word-wrap: break-word;
    }

    /* Section title row */
    .share-sheet thead tr:first-child th,
    .share-sheet tr:first-child th[colspan] {
        background: #0056d0 !important;
        color: #fff !important;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-color: #0046b0;
    }

    /* Column header row */
    .share-sheet thead tr:not(:first-child) th,
    .share-sheet tr:nth-child(2) th {
        background: #e8f0fd !important;
        color: #0056d0;
        font-weight: 700;
        border-color: #c5d5f5;
    }

    /* Data rows alternating */
    .share-sheet tbody tr:nth-child(odd) td {
        background: #fff;
    }
    .share-sheet tbody tr:nth-child(even) td {
        background: #f5f8ff;
    }
    .share-sheet tbody tr:hover td {
        background: #eaf0fd;
    }

    /* Label cells in meta table */
    .share-sheet .label,
    .share-sheet td.label {
        font-weight: 700;
        background: #e8f0fd !important;
        color: #0056d0;
        width: 200px;
    }

    /* Bootstrap table override for sheet section */
    .sheet-section .table thead tr:first-child th {
        background: #0056d0 !important;
        color: #fff !important;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-color: #0046b0;
    }
    .sheet-section .table thead tr:not(:first-child) th {
        background: #e8f0fd !important;
        color: #0056d0;
        font-weight: 700;
        border-color: #c5d5f5;
    }
    .sheet-section .table tbody tr:nth-child(even) td {
        background: #f5f8ff;
    }
    .sheet-section .table tbody tr:hover td {
        background: #eaf0fd;
    }
    .sheet-section .table {
        box-shadow: 0 1px 4px rgba(0,86,208,0.08);
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .share-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }

    @media (max-width: 900px) {
        .share-grid {
            grid-template-columns: 1fr;
        }
    }

    .light-cell {
        background: #eaf4ff !important;
    }

    .text-right {
        text-align: right;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .right_col {
            padding: 0 !important;
        }

        .report-wrap {
            border: none;
            padding: 0;
        }

        .sheet-table {
            page-break-inside: auto;
        }

        .sheet-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
    
    /* Dark font color for report */
    .report-wrap, 
    .report-wrap table, 
    .report-wrap td, 
    .report-wrap th,
    .report-wrap label, 
    .report-wrap .summary-card .label,
    .report-wrap .summary-card .value,
    .report-wrap .summary-card .subvalue {
        color: #000 !important;
    }
    
    .filter-row .form-control {
        color: #000 !important;
        border-color: #777 !important;
    }
</style>

<div class="right_col">
    <!-- Full-page loading overlay -->
    <div id="report_loading_overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; z-index:9999; align-items:center; justify-content:center; flex-direction:column;">
        <div style="text-align:center;">
            <div style="width:52px; height:52px; border:5px solid #e0e0e0; border-top-color:#0056d0; border-radius:50%; animation:report-spin 0.8s linear infinite; margin:0 auto 16px;"></div>
            <div style="font-size:16px; color:#0056d0; font-weight:600;">Loading Report...</div>
        </div>
    </div>
<!-- Hidden print meta info from PHP session -->
<span id="print_user_name" style="display:none;"><?= $this->session->userdata('name') ?></span>
    <style>@keyframes report-spin { to { transform: rotate(360deg); } }</style>
    <h3 class="page-h3">Production Report</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="report-wrap">
                <form method="get" id="production_report_filter" class="filter-row">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="" <?= (empty($report_type)) ? 'selected' : '' ?>>Select Report Type</option>
                            <option value="overview" <?= (isset($report_type) && $report_type === 'overview') ? 'selected' : '' ?>>Daily Production Plan vs Actual</option>
                            <option value="downtime" <?= (isset($report_type) && $report_type === 'downtime') ? 'selected' : '' ?>>Downtime Analysis</option>
                            <option value="spc" <?= (isset($report_type) && $report_type === 'spc') ? 'selected' : '' ?>>SPC – Part Weight (Subgroup=5)</option>
                            <option value="rejection" <?= (isset($report_type) && $report_type === 'rejection') ? 'selected' : '' ?>>Rejection &amp; Scrap Log</option>
                            <option value="sheet" <?= (isset($report_type) && $report_type === 'sheet') ? 'selected' : '' ?>>Production Report Sheet (Share)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="text" name="date" id="date" class="form-control" placeholder="Select Date Range" value="<?= isset($selected_date) ? $selected_date : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Machine</label>
                        <select name="machine_id" id="machine_id" class="form-control" style="width:100%; display:none;">
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
                        <select name="article_id" id="article_id" class="form-control" style="width:100%; display:none;">
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

            <div class="summary-grid" id="production_summary_cards" <?php if (empty($has_filters) || empty($report_type)): ?>style="display:none;"<?php endif; ?>>
                <div class="summary-card">
                    <span class="label">Overview Entries</span>
                    <div class="value"><?= (int) $overview_count ?></div>
                    <div class="subvalue">Daily plan vs actual rows</div>
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
            <div class="report-section" id="sheet_section">
            <?php
            $reports_to_render = [];
            if (!empty($day_by_day_reports)) {
                $reports_to_render = $day_by_day_reports;
            } else {
                $reports_to_render[] = [
                    'date' => !empty($selected_date) ? $selected_date : '-',
                    'overview_rows' => $overview_rows ?? [],
                    'summary_rows' => $summary_rows ?? [],
                    'rejection_rows' => $rejection_rows ?? [],
                    'balance_rows' => $balance_rows ?? [],
                    'used_rm_rows' => $used_rm_rows ?? [],
                    'used_mb_rows' => $used_mb_rows ?? [],
                ];
            }

            foreach ($reports_to_render as $report) {
                $current_date = $report['date'];
                $current_overview_rows = $report['overview_rows'];
                $current_summary_rows = $report['summary_rows'];
                $current_rejection_rows = $report['rejection_rows'];
                $current_balance_rows = $report['balance_rows'];
                $current_used_rm_rows = $report['used_rm_rows'];
                $current_used_mb_rows = $report['used_mb_rows'];

                $current_used_rm_map = [];
                if (!empty($current_used_rm_rows)) {
                    foreach ($current_used_rm_rows as $u) {
                        $current_used_rm_map[$u->raw_material_name] = (float)($u->used_qty ?? 0);
                    }
                }
                $current_used_mb_map = [];
                if (!empty($current_used_mb_rows)) {
                    foreach ($current_used_mb_rows as $u) {
                        $current_used_mb_map[$u->master_batch_name] = (float)($u->used_qty ?? 0);
                    }
                }
            ?>
             <div class="report-wrap sheet-section" style="margin-bottom: 30px;">
                <h2 class="section-head">Production Report - <?= $current_date ?></h2>

                <table class="table table-striped table-bordered" style="width:100%; margin-bottom:18px;">
                    <tbody>
                    <tr>
                        <th style="background:#e8f0fd; color:#0056d0; font-weight:700; width:180px;">DATE</th>
                        <td colspan="3"><?= $current_date ?></td>
                        <th style="background:#e8f0fd; color:#0056d0; font-weight:700; width:180px;">PLANT</th>
                        <td colspan="3"><?= !empty($sheet_meta) && !empty($sheet_meta->plant_name) ? $sheet_meta->plant_name : '-' ?></td>
                    </tr>
                    <tr>
                        <th style="background:#e8f0fd; color:#0056d0; font-weight:700;">MACHINE NAME</th>
                        <td colspan="3"><?= !empty($sheet_meta) && !empty($sheet_meta->machine_name) ? $sheet_meta->machine_name : '-' ?></td>
                        <th style="background:#e8f0fd; color:#0056d0; font-weight:700;">ARTICLE</th>
                        <td colspan="3"><?php
                            if (!empty($selected_article_name) && $selected_article_name !== 'All') {
                                echo htmlspecialchars($selected_article_name);
                            } elseif (!empty($current_summary_rows)) {
                                $article_names = array_unique(array_filter(array_map(function($r) {
                                    return !empty($r->article_name) ? trim($r->article_name) : '';
                                }, $current_summary_rows)));
                                echo !empty($article_names) ? htmlspecialchars(implode(', ', $article_names)) : 'All';
                            } else {
                                echo 'All';
                            }
                        ?></td>
                    </tr>
                    <tr>
                        <th style="background:#e8f0fd; color:#0056d0; font-weight:700;">Operators Name</th>
                        <td colspan="7"><?php
                            $day_ops = [];
                            $night_ops = [];
                            if (!empty($current_overview_rows)) {
                                foreach ($current_overview_rows as $row) {
                                    if (!empty($row->day_shift_operator_names)) {
                                        $day_ops = array_merge($day_ops, array_filter(array_map('trim', explode(',', $row->day_shift_operator_names))));
                                    }
                                    if (!empty($row->night_shift_operator_names)) {
                                        $night_ops = array_merge($night_ops, array_filter(array_map('trim', explode(',', $row->night_shift_operator_names))));
                                    }
                                }
                            }
                            $unique_day = array_unique($day_ops);
                            $unique_night = array_unique($night_ops);
                            
                            $display_parts = [];
                            if (!empty($unique_day)) {
                                $display_parts[] = 'Day Shift: ' . implode(', ', $unique_day);
                            }
                            if (!empty($unique_night)) {
                                $display_parts[] = 'Night Shift: ' . implode(', ', $unique_night);
                            }
                            
                            echo !empty($display_parts) ? htmlspecialchars(implode(' | ', $display_parts)) : '-';
                        ?></td>
                    </tr>
                    </tbody>
                </table>

                <table class="table table-striped table-bordered" style="width:100%; margin-bottom:18px;">
                    <thead>
                        <tr>
                            <th colspan="8" style="background:#0056d0; color:#fff; font-weight:800; text-transform:uppercase;">ARTICLE WISE PLANNED VS ACTUAL</th>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th class="text-right">Planned Qty</th>
                            <th class="text-right">Approved qty</th>
                            <th class="text-right">Plan Ach %</th>
                            <th class="text-right">Downtime (min)</th>
                            <th class="text-right">Operating Hrs</th>
                            <th class="text-right">Performance %</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($current_overview_rows)) { ?>
                        <?php foreach ($current_overview_rows as $row) { ?>
                            <?php
                            $planned_qty = (float) ($row->planned_qty ?? 0);
                            $good_qty = (float) ($row->good_qty ?? 0);
                            $rejection_qty = (float) ($row->rejection_qty ?? 0);
                            $actual_qty = $good_qty;

                            $scheduled_minutes = (float) ($row->scheduled_minutes ?? 0);
                            $downtime_minutes = (float) ($row->downtime_minutes ?? 0);
                            $operating_minutes = max($scheduled_minutes - $downtime_minutes, 0);
                            $operating_hrs = $operating_minutes / 60;
                            $plan_ach = ($planned_qty > 0) ? (($actual_qty / $planned_qty) * 100) : 0;

                            $ideal_seconds = (float) ($row->ideal_seconds ?? 0);
                            $performance_pct = ($operating_minutes > 0 && $ideal_seconds > 0)
                                ? (($ideal_seconds / ($operating_minutes * 60)) * 100)
                                : 0;
                            ?>
                            <tr>
                                <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                <td class="text-right"><?= number_format($planned_qty, 0) ?></td>
                                <td class="text-right"><?= number_format($actual_qty, 0) ?></td>
                                <td class="text-right"><?= number_format($plan_ach, 2) ?></td>
                                <td class="text-right">
                                    <?= number_format($downtime_minutes, 0) ?>
                                    <?php if ($downtime_minutes > 0 && !empty($row->downtime_reasons)) {
                                        $reason_parts2 = array_filter(array_map("trim", explode("||", (string)$row->downtime_reasons)));
                                        if (!empty($reason_parts2)) { ?>
                                        <div class="downtime-reasons">
                                            <?php foreach ($reason_parts2 as $rp2) { ?>
                                                <?php
                                                    $rp2_text = $rp2;
                                                    if (preg_match('/:\s*-?$/', $rp2_text)) {
                                                        $rp2_text = preg_replace('/:\s*-?$/', ': Not Sheduled', $rp2_text);
                                                    }
                                                ?>
                                                <div class="dt-reason-line"><?= htmlspecialchars($rp2_text) ?></div>
                                            <?php } ?>
                                        </div>
                                    <?php } } ?>
                                </td>
                                <td class="text-right"><?= number_format($operating_hrs, 2) ?></td>
                                <td class="text-right"><?= number_format($performance_pct, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="8" class="text-center">No records</td></tr>
                    <?php } ?>
                    </tbody>
                </table>

                <div style="margin-bottom:18px;">
                    <table class="table table-striped table-bordered" style="width:100%; margin-bottom:18px;">
                            <thead>
                                <tr>
                                    <th colspan="3" style="background:#0056d0; color:#fff; font-weight:800; text-transform:uppercase;">FORMULA RAW MATERIAL LIST - BALANCE MATERIAL</th>
                                </tr>
                                <tr>
                                    <th>Raw Material</th>
                                    <th class="text-right">Consumed Qty</th>
                                    <th class="text-right">Balance Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $has_rm_bal = false;
                            if (!empty($current_balance_rows)) {
                                foreach ($current_balance_rows as $b) {
                                    if (!empty($b->raw_material_name)) {
                                        $has_rm_bal = true;
                                        $used_qty = $current_used_rm_map[$b->raw_material_name] ?? 0;
                                        $balance_qty = (float) str_replace(',', '', (string) ($b->rm_total_qty ?? 0));
                                        $consumed_qty = max($used_qty - $balance_qty, 0);
                            ?>
                                        <tr>
                                            <td><?= htmlspecialchars($b->raw_material_name) ?></td>
                                            <td class="text-right"><?= number_format($consumed_qty, 3) ?></td>
                                            <td class="text-right"><?= number_format($balance_qty, 3) ?></td>
                                        </tr>
                                    <?php }
                                }
                            }
                            if (!$has_rm_bal) { ?>
                                <tr><td colspan="3" class="text-center">No records</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>

                        <table class="table table-striped table-bordered" style="width:100%; margin-bottom:0;">
                            <thead>
                                <tr>
                                    <th colspan="3" style="background:#0056d0; color:#fff; font-weight:800; text-transform:uppercase;">FORMULA MASTER BATCH - BALANCE MASTER BATCH</th>
                                </tr>
                                <tr>
                                    <th>Master Batch</th>
                                    <th class="text-right">Consumed Qty</th>
                                    <th class="text-right">Balance Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $has_mb_bal = false;
                            if (!empty($current_balance_rows)) {
                                foreach ($current_balance_rows as $b) {
                                    if (!empty($b->master_batch_name)) {
                                        $has_mb_bal = true;
                                        $used_qty = $current_used_mb_map[$b->master_batch_name] ?? 0;
                                        $balance_qty = (float) str_replace(',', '', (string) ($b->mb_total_qty ?? 0));
                                        $consumed_qty = max($used_qty - $balance_qty, 0);
                            ?>
                                        <tr>
                                            <td><?= htmlspecialchars($b->master_batch_name) ?></td>
                                            <td class="text-right"><?= number_format($consumed_qty, 3) ?></td>
                                            <td class="text-right"><?= number_format($balance_qty, 3) ?></td>
                                        </tr>
                                    <?php }
                                }
                            }
                            if (!$has_mb_bal) { ?>
                                <tr><td colspan="3" class="text-center">No records</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                </div>

                <table class="table table-striped table-bordered" style="width:100%; margin-top:18px; margin-bottom:18px;">
                    <thead>
                        <tr>
                            <th colspan="5" style="background:#0056d0; color:#fff; font-weight:800; text-transform:uppercase;">LIST OF REJECTION RM TABLE FROM PRODUCTION LIST</th>
                        </tr>
                        <tr>
                            <th style="width:60px;">Sr</th>
                            <th>Defect Type</th>
                            <th class="text-right" style="width:160px;">Qty</th>
                            <th class="text-right" style="width:180px;">Total Rejected Weight</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($current_rejection_rows)) { ?>
                        <?php $i = 1;
                        foreach ($current_rejection_rows as $r) { ?>
                            <?php $reason = trim((string) ($r->remark ?? '')); ?>
                            <tr>
                                <td class="text-right"><?= $i++ ?></td>
                                <td><?= !empty($r->rejection_name) ? $r->rejection_name : '-' ?></td>
                                <td class="text-right"><?= ($r->pc !== null && $r->pc !== '') ? number_format((float) $r->pc, 0) : '0' ?></td>
                                <td class="text-right"><?= ($r->total_qty !== null && $r->total_qty !== '') ? number_format((float) $r->total_qty, 3) : '0.000' ?></td>
                                <td><?= $reason !== '' ? $reason : '-' ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="5" class="text-center">No records</td></tr>
                    <?php } ?>
                    </tbody>
                </table>



                <table class="table table-striped table-bordered" style="width:100%; margin-bottom:18px;">
                    <thead class="thead">
                        <tr>
                            <th colspan="6" style="background:#0056d0; color:#fff; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">SUMMARY</th>
                        </tr>
                        <tr>
                            <th>Article Name</th>
                            <th class="text-right">Approved qty</th>
                            <th class="text-right">Average weight</th>
                            <th class="text-right">Total Weight</th>
                            <th>Remarks</th>
                            <th class="text-right">Delta</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($current_summary_rows)) { ?>
                        <?php foreach ($current_summary_rows as $sr) { ?>
                            <tr>
                                <td><?= !empty($sr->article_name) ? htmlspecialchars($sr->article_name) : '-' ?></td>
                                <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $sr->approved_qty), 3) ?></td>
                                <td class="text-right"><?= number_format((float) str_replace(',', '', (string) $sr->average_qty), 3) ?></td>
                                <td class="text-right"><?= number_format((float) $sr->total_weight, 3) ?></td>
                                <td><?= !empty($sr->remark) ? htmlspecialchars($sr->remark) : '-' ?></td>
                                <td class="text-right"><?= number_format((float) $sr->delta, 3) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="6" class="text-center">No summary records</td></tr>
                    <?php } ?>
                    </tbody>
                </table>

            </div>
            <?php } ?>
            </div>

            <div class="report-wrap report-section" id="overview_section">
                <h2 class="section-head">Daily Production Plan vs Actual</h2>
                <table class="sheet-table" id="overview_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Shift</th>
                            <th>Operator Name</th>
                            <th class="text-right">Planned Qty (Nos)</th>
                            <th class="text-right">Approved qty (Nos)</th>
                            <th class="text-right">Rejection Qty (Nos)</th>
                            <th class="text-right">Good Qty</th>
                            <th class="text-right">Plan Achievement %</th>
                            <th class="text-right">Rejection %</th>
                            <th>Remarks</th>
                            <th class="text-right">Downtime (min)</th>
                            <th class="text-right">Operating Hrs</th>
                            <th class="text-right">Performance %<br>(Actual/Std)</th>
                            <th class="text-right">Machine Efficiency<br>Index</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($overview_rows)) { ?>
                            <?php foreach ($overview_rows as $row) { ?>
                                <?php
                                $planned_qty = (float) ($row->planned_qty ?? 0);
                                $good_qty = (float) ($row->good_qty ?? 0);
                                $rejection_qty = (float) ($row->rejection_qty ?? 0);
                                $actual_qty = $good_qty; /* Approved qty only */

                                $scheduled_minutes = (float) ($row->scheduled_minutes ?? 0);
                                $downtime_minutes = (float) ($row->downtime_minutes ?? 0);
                                $operating_minutes = max($scheduled_minutes - $downtime_minutes, 0);
                                $operating_hrs = $operating_minutes / 60;

                                $plan_ach = ($planned_qty > 0) ? (($actual_qty / $planned_qty) * 100) : 0;
                                $rej_pct = ($actual_qty > 0) ? (($rejection_qty / $actual_qty) * 100) : 0;

                                $std_cycle_time_row = (float) ($row->std_cycle_time ?? ($std_cycle_time ?? 0));
                                $ideal_seconds = (float) ($row->ideal_seconds ?? 0);

                                $performance_ratio = 0;
                                if ($operating_minutes > 0) {
                                    $run_seconds = $operating_minutes * 60;

                                    if ($ideal_seconds > 0) {
                                        $performance_ratio = ($run_seconds > 0) ? ($ideal_seconds / $run_seconds) : 0;
                                    } elseif ($std_cycle_time_row > 0 && $actual_qty > 0) {
                                        $ideal_seconds = $std_cycle_time_row * $actual_qty;
                                        $performance_ratio = ($run_seconds > 0) ? ($ideal_seconds / $run_seconds) : 0;
                                    }
                                }

                                $performance_pct = $performance_ratio * 100;

                                $availability_ratio = ($scheduled_minutes > 0) ? ($operating_minutes / $scheduled_minutes) : 0;
                                $quality_ratio = ($actual_qty > 0) ? ($good_qty / $actual_qty) : 0;
                                $oee_pct = ($availability_ratio * $performance_ratio * $quality_ratio) * 100;

                                $shift = '-';
                                $shift_entries = [];
                                if (!empty($row->shift_detail)) {
                                    $parts = array_filter(array_map('trim', explode(',', (string) $row->shift_detail)));
                                    foreach ($parts as $p) {
                                        $qty = null;
                                        if (preg_match('/\(([^\)]*)\)\s*$/', $p, $m)) {
                                            $qty = trim((string) $m[1]);
                                            $p = trim(preg_replace('/\(([^\)]*)\)\s*$/', '', $p));
                                        }
                                        $pair = array_map('trim', explode('-', $p, 2));
                                        $st = $pair[0] ?? '';
                                        $en = $pair[1] ?? '';
                                        $st_ts = $st !== '' ? strtotime($st) : false;
                                        $en_ts = $en !== '' ? strtotime($en) : false;
                                        $st_out = ($st_ts !== false) ? date('H:i', $st_ts) : $st;
                                        $en_out = ($en_ts !== false) ? date('H:i', $en_ts) : $en;
                                        $shift_entries[] = [
                                            'time' => trim($st_out . ' - ' . $en_out),
                                            'qty' => $qty
                                        ];
                                    }
                                } elseif (!empty($row->shift_timing)) {
                                    $parts = array_filter(array_map('trim', explode(',', (string) $row->shift_timing)));
                                    foreach ($parts as $p) {
                                        $pair = array_map('trim', explode('-', $p, 2));
                                        $st = $pair[0] ?? '';
                                        $en = $pair[1] ?? '';
                                        $st_ts = $st !== '' ? strtotime($st) : false;
                                        $en_ts = $en !== '' ? strtotime($en) : false;
                                        $st_out = ($st_ts !== false) ? date('H:i', $st_ts) : $st;
                                        $en_out = ($en_ts !== false) ? date('H:i', $en_ts) : $en;
                                        $shift_entries[] = [
                                            'time' => trim($st_out . ' - ' . $en_out),
                                            'qty' => null
                                        ];
                                    }
                                } elseif (!empty($row->shift_start) && !empty($row->shift_end)) {
                                    $shift_entries[] = [
                                        'time' => date('H:i', strtotime($row->shift_start)) . ' - ' . date('H:i', strtotime($row->shift_end)),
                                        'qty' => null
                                    ];
                                }
                                if (!empty($shift_entries)) {
                                    $shift = '<div class="shift-breakdown">';
                                    foreach ($shift_entries as $e) {
                                        $shift .= '<div class="line">' . htmlspecialchars((string) ($e['time'] ?? '-')) . '</div>';
                                    }
                                    $shift .= '</div>';
                                }
                                ?>
                                <tr>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= $shift ?></td>
                                <td>
                                    <?php
                                    $display_parts = [];
                                    if (!empty($row->day_shift_operator_names)) {
                                        $display_parts[] = '<strong>Day:</strong> ' . htmlspecialchars($row->day_shift_operator_names);
                                    }
                                    if (!empty($row->night_shift_operator_names)) {
                                        $display_parts[] = '<strong>Night:</strong> ' . htmlspecialchars($row->night_shift_operator_names);
                                    }
                                    if (!empty($display_parts)) {
                                        echo implode('<br>', $display_parts);
                                    } else {
                                        echo htmlspecialchars($row->operator_name ?: '-');
                                    }
                                    ?>
                                </td>
                                        <td class="text-right">
                                        <?php if (!empty($shift_entries)) { ?>
                                            <div class="shift-breakdown">
                                                <?php foreach ($shift_entries as $e) { ?>
                                                    <?php
                                                    $qty_val = $e['qty'] ?? null;
                                                    $qty_num = ($qty_val !== null && $qty_val !== '') ? (float) str_replace(',', '', (string) $qty_val) : null;
                                                ?>
                                                    <div class="line"><?= $qty_num !== null ? number_format($qty_num, 0) : '-' ?></div>
                                                <?php } ?>
                                                <div class="total">Total: <?= number_format($planned_qty, 0) ?></div>
                                            </div>
                                        <?php } else { ?>
                                            <?= number_format($planned_qty, 0) ?>
                                        <?php } ?>
                                        </td>
                                    <td class="text-right"><?= number_format($actual_qty, 0) ?></td>
                                    <td class="text-right"><?= number_format($rejection_qty, 0) ?></td>
                                    <td class="text-right"><?= number_format($good_qty, 0) ?></td>
                                    <td class="text-right"><?= number_format($plan_ach, 2) ?></td>
                                    <td class="text-right"><?= number_format($rej_pct, 2) ?></td>
                                    <td><?= !empty($row->remarks) ? $row->remarks : '-' ?></td>
                                    <td class="text-right">
                                        <?= number_format($downtime_minutes, 0) ?>
                                        <?php if ($downtime_minutes > 0 && !empty($row->downtime_reasons)) {
                                            $reason_parts = array_filter(array_map("trim", explode("||", (string)$row->downtime_reasons)));
                                            if (!empty($reason_parts)) { ?>
                                            <div class="downtime-reasons">
                                                <?php foreach ($reason_parts as $rp) { ?>
                                                    <?php
                                                        $rp_text = $rp;
                                                        if (preg_match('/:\s*-?$/', $rp_text)) {
                                                            $rp_text = preg_replace('/:\s*-?$/', ': Not Sheduled', $rp_text);
                                                        }
                                                    ?>
                                                    <div class="dt-reason-line"><?= htmlspecialchars($rp_text) ?></div>
                                                <?php } ?>
                                            </div>
                                        <?php } } ?>
                                    </td>
                                    <td class="text-right"><?= number_format($operating_hrs, 2) ?></td>
                                    <td class="text-right"><?= number_format($performance_pct, 2) ?></td>
                                    <td class="text-right"><?= number_format($oee_pct, 2) ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="15" class="text-center">No records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="downtime_section">
                <h2 class="section-head">Downtime Analysis</h2>
                <table class="sheet-table" id="downtime_table">
                    <thead>
            
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th class="text-right">Breakdown<br>(Min)</th>
                            <th class="text-right">Mould Change<br>(Min)</th>
                            <th class="text-right">Material<br>(Min)</th>
                            <th class="text-right">Power<br>(Min)</th>
                            <th class="text-right">Minor Stoppage<br>(Min)</th>
                            <th class="text-right">Quality<br>(Min)</th>
                            <th class="text-right">Total Downtime<br>(Min)</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($downtime_rows)) { ?>
                            <?php foreach ($downtime_rows as $row) { ?>
                                <tr>
                                    <td><?= !empty($row->production_date) ? $row->production_date : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->breakdown_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->mould_change_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->material_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->power_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->minor_stoppage_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->quality_min ?? 0), 0) ?></td>
                                    <td class="text-right"><?= number_format((float) ($row->total_downtime_min ?? 0), 0) ?></td>
                                    <td>
                                        <?php
                                        $reason_raw = trim((string)($row->reason ?? ""));
                                        if (!empty($reason_raw)) {
                                            $reason_items = array_filter(array_map("trim", explode("||", $reason_raw)));
                                        } else {
                                            $reason_combined = trim((string)($row->reason_combined ?? ""));
                                            $reason_items = !empty($reason_combined) ? [$reason_combined] : [];
                                        }
                                        if (!empty($reason_items)) { ?>
                                            <div class="downtime-reasons" style="text-align:left;">
                                                <?php foreach ($reason_items as $ri) { ?>
                                                    <?php
                                                        $ri_text = $ri;
                                                        if (preg_match('/:\s*-?$/', $ri_text)) {
                                                            $ri_text = preg_replace('/:\s*-?$/', ': Not Sheduled', $ri_text);
                                                        }
                                                    ?>
                                                    <div class="dt-reason-line"><?= htmlspecialchars($ri_text) ?></div>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>-<?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="text-center">No downtime records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="report-wrap report-section" id="spc_section">
                <h2 class="section-head">SPC – Part Weight (Subgroup=5)</h2>
                <table class="sheet-table" id="spc_table">
                    <thead>
                        <tr>
                            <th colspan="15" class="text-center">FORM FILLED BY OPEARTOR / SUPERVISOR</th>
                        </tr>
                        
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>Shift</th>
                            <th>Check Time</th>
                            <th>Part/Article</th>
                            <th class="text-right">Std Wt (g)</th>
                            <th class="text-right">LSL (g)</th>
                            <th class="text-right">USL (g)</th>
                            <th class="text-right">S1</th>
                            <th class="text-right">S2</th>
                            <th class="text-right">S3</th>
                            <th class="text-right">S4</th>
                            <th class="text-right">S5</th>
                            <th class="text-right">Avg</th>
                            <th>Status</th>
                        </tr>

                    </thead>
                    <tbody>
                        <?php if (!empty($spc_rows)) { ?>
                            <?php foreach ($spc_rows as $row) { ?>
                                <tr>
                                    <td><?= !empty($row->report_date) ? date('d-m-Y', strtotime($row->report_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->shift) ? $row->shift : '-' ?></td>
                                    <td><?= !empty($row->check_time) ? $row->check_time : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td class="text-right"><?= ($row->std_wt !== null && $row->std_wt !== '') ? number_format((float) $row->std_wt, 3) : '-' ?></td>
                                    <td class="text-right"><?= ($row->lsl !== null && $row->lsl !== '') ? number_format((float) $row->lsl, 3) : '-' ?></td>
                                    <td class="text-right"><?= ($row->usl !== null && $row->usl !== '') ? number_format((float) $row->usl, 3) : '-' ?></td>
                                    <td class="text-right"><?= ($row->s1 !== null && $row->s1 !== '') ? number_format((float) $row->s1, 3) : '' ?></td>
                                    <td class="text-right"><?= ($row->s2 !== null && $row->s2 !== '') ? number_format((float) $row->s2, 3) : '' ?></td>
                                    <td class="text-right"><?= ($row->s3 !== null && $row->s3 !== '') ? number_format((float) $row->s3, 3) : '' ?></td>
                                    <td class="text-right"><?= ($row->s4 !== null && $row->s4 !== '') ? number_format((float) $row->s4, 3) : '' ?></td>
                                    <td class="text-right"><?= ($row->s5 !== null && $row->s5 !== '') ? number_format((float) $row->s5, 3) : '' ?></td>
                                    <td class="text-right"><?= ($row->avg !== null && $row->avg !== '') ? number_format((float) $row->avg, 3) : '' ?></td>
                                    <td><?= !empty($row->spc_status) ? $row->spc_status : '' ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="15" class="text-center">No records found.</td>
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
                            <?php $index = 1;
                            foreach ($summary_rows as $row) { ?>
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
                            <?php $index = 1;
                            foreach ($detail_rows as $row) { ?>
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
                <h2 class="section-head">Rejection &amp; Scrap Log</h2>
                
                <table class="sheet-table" id="rejection_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Machine</th>
                            <th>ARTICLE NAME</th>
                            <th>Shift</th>
                            <th>Defect Type</th>
                            <th class="text-right">Total Rejected Weight</th>
                            <th>Reason</th>
                            <th>Immediate Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rejection_rows)) { ?>
                            <?php foreach ($rejection_rows as $row) {
                                $shift = '-';
                                if (!empty($row->created_on)) {
                                    $ts = strtotime($row->created_on);
                                    if ($ts !== false) {
                                        $h = (int) date('H', $ts);
                                        if ($h >= 6 && $h < 14) {
                                            $shift = 'A';
                                        } elseif ($h >= 14 && $h < 22) {
                                            $shift = 'B';
                                        } else {
                                            $shift = 'C';
                                        }
                                    }
                                }
                                $reason = trim((string) ($row->remark ?? ''));
                            ?>
                                <tr>
                                    <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                    <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                    <td><?= !empty($row->article_name) ? $row->article_name : '-' ?></td>
                                    <td><?= $shift ?></td>
                                    <td><?= !empty($row->rejection_name) ? $row->rejection_name : '-' ?></td>

                                    <?php
                                        $scrap_val = ($row->total_qty !== null && $row->total_qty !== '') ? (float) $row->total_qty : 0;
                                        $scrap_display = number_format($scrap_val, 3);
                                    ?>
                                    <td class="text-right">
                                        <?= htmlspecialchars($scrap_display) ?>
                                    </td>

                                    <td><?= $reason !== '' ? $reason : '-' ?></td>
                                    <td><?= $reason !== '' ? $reason : '-' ?></td>
                                </tr>

                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No rejection records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    (function() {
        function initProductionReport($) {
            $('#product_master').addClass('active');
            $('.production_report').addClass('active_cc');

            // Searchable Select2 for Machine and Article filters
            if ($.fn.select2) {
                if (!$('#machine_id').hasClass('select2-hidden-accessible')) {
                    $('#machine_id').select2({
                        placeholder: 'All Machines',
                        allowClear: true,
                        width: '100%'
                    });
                }
                if (!$('#article_id').hasClass('select2-hidden-accessible')) {
                    $('#article_id').select2({
                        placeholder: 'All Articles',
                        allowClear: true,
                        width: '100%'
                    });
                }
            }

            // Flatpickr is loaded in footer; wait until it is available
            (function initFlatpickr(tries) {
                tries = tries || 0;
                if (typeof flatpickr === 'undefined') {
                    if (tries < 200) setTimeout(function() { initFlatpickr(tries + 1); }, 50);
                    return;
                }
                flatpickr('#date', {
                    mode: 'range',
                    dateFormat: 'd-m-Y'
                });
            })();

            function applyReportFilter(type) {
                $('.report-section').hide();
                $('#production_summary_cards').hide();

                if (!type || type === '') {
                    return;
                }

                if (type === 'overview') {
                    $('#overview_section').show();
                    $('#production_summary_cards').show();
                } else if (type === 'downtime') {
                    $('#downtime_section').show();
                    $('#production_summary_cards').show();
                } else if (type === 'spc') {
                    $('#spc_section').show();
                    $('#production_summary_cards').show();
                } else if (type === 'rejection') {
                    $('#rejection_section').show();
                    $('#production_summary_cards').show();
                } else if (type === 'sheet') {
                    $('#sheet_section').show();
                    $('#production_summary_cards').hide();
                }

                // When tables are shown/hidden, DataTables needs a width recalculation
                if ($.fn.DataTable) {
                    setTimeout(function() {
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                    }, 50);
                }
            }

            <?php if (!empty($has_filters) && !empty($report_type)): ?>
            applyReportFilter('<?= htmlspecialchars($report_type) ?>');
            <?php else: ?>
            $('.report-section').hide();
            $('#production_summary_cards').hide();
            <?php endif; ?>

            // Show loading overlay on Search button click and form submit
            $('#production_report_filter').on('submit', function() {
                sessionStorage.setItem('report_loading', '1');
                $('#report_loading_overlay').css('display', 'flex');
            });

            $('#report_type').on('change', function() {
                $('#production_report_filter').submit();
            });

            // Reveal page content once fully loaded (handles the sessionStorage hide)
            document.documentElement.style.visibility = 'visible';
            $('#report_loading_overlay').hide();

            function buildExportSections() {
                var exportSections = [];
                $('.summary-grid:visible, .report-section:visible').each(function() {
                    var $clone = $(this).clone();
                    $clone.find('table, thead, tbody, tr, th, td').each(function() {
                        $(this).css('width', '');
                        $(this).attr('width', '');
                    });
                    exportSections.push($clone.prop('outerHTML'));
                });
                return exportSections;
            }

            function buildExportStyles(orientation) {
                return '<style>' +
                    '@page{size:' + (orientation === 'portrait' ? 'A4 portrait' : 'A4 landscape') + ';margin:12mm 10mm;}' +
                    'body{font-family:Arial,sans-serif;margin:0;padding:0;color:#222;background:#fff;-webkit-print-color-adjust:exact;}' +
                    '.export-container{width:100%;box-sizing:border-box;padding:0 2px 0 0;padding-bottom:0 !important;}' +
                    'table{width:100%;border-collapse:collapse;margin-bottom:14px;page-break-inside:auto;border:1px solid #bbb;outline:1px solid #bbb;outline-offset:-1px;box-shadow: inset -1px 0 0 #bbb;}' +
                    'tr{page-break-inside:avoid;page-break-after:auto;}' +
                    'thead{display:table-header-group;}' +
                    'th,td{border:1px solid #bbb;padding:6px 8px;vertical-align:middle;font-size:11px;word-break:break-word;}' +
                    'th:last-child,td:last-child{border-right:1px solid #bbb !important;}' +
                    'th{background:#e8f0fd !important;font-weight:bold;color:#0056d0;-webkit-print-color-adjust:exact;}' +
                    'h2.section-head{font-size:16px;margin:14px 0 8px;text-transform:uppercase;color:#0056d0;font-weight:800;text-align:center;}' +
                    '.report-wrap{margin-bottom:16px;page-break-inside:avoid;}' +
                    '.text-right{text-align:right;}' +
                    '.summary-grid{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;width:100%;}' +
                    '.summary-card{flex:1;min-width:180px;border:1px solid #d7dde5;border-radius:6px;background:#f8fbff;padding:10px 14px;box-sizing:border-box;}' +
                    '.summary-card .label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;color:#5a6472;margin-bottom:4px;}' +
                    '.summary-card .value{font-size:18px;font-weight:800;color:#11213f;}' +
                    '.summary-card .subvalue{margin-top:3px;color:#6b7280;font-size:10px;}' +
                    '.downtime-reasons{margin-top:3px;font-size:9px;color:#c0392b;}' +
                    '.dt-reason-line{padding:1px 0;line-height:1.3;}' +
                    '.shift-breakdown .line{padding:1px 0;border-bottom:1px solid #ccc;}' +
                    '.shift-breakdown .line:last-child{border-bottom:0;}' +
                    '.shift-breakdown .total{margin-top:2px;padding-top:2px;border-top:1px solid #ccc;font-weight:700;}' +
                    '#overview_table th,#overview_table td{padding:3px 4px !important;font-size:9px !important;}' +
                    '.sheet-table{width:100%;border-collapse:collapse;margin-bottom:14px;}' +
                    '.sheet-table th,.sheet-table td{border:1px solid #bbb;padding:5px 7px;font-size:10px;}' +
                    '.sheet-table thead th{background:#f2f4f7 !important;font-weight:700;}' +
                    '.share-sheet{width:100%;border-collapse:collapse;background:#fff;margin-bottom:12px;}' +
                    '.share-sheet th,.share-sheet td{border:1px solid #c5d5f5;padding:6px 8px;font-size:10px;background:#fff;}' +
                    '.share-sheet th{background:#e8f0fd !important;font-weight:700;color:#0056d0;}' +
                    '.share-sheet tr:first-child th[colspan]{background:#0056d0 !important;color:#fff !important;font-weight:800;}' +
                    '.share-sheet tbody tr:nth-child(even) td{background:#f5f8ff;}' +
                    '.share-grid{display:flex;gap:10px;margin-bottom:12px;width:100%;}' +
                    '.share-grid>div{flex:1;min-width:0;}' +
                    '.light-cell{background:#eaf4ff !important;}' +
                        '.export-container > :last-child{margin-bottom:0 !important;}' +
                        '.export-container .report-wrap:last-child{margin-bottom:0 !important;}' +
                        '.export-container table:last-child{margin-bottom:0 !important;}' +
                    '.dataTables_length,.dataTables_filter,.dataTables_info,.dataTables_paginate,.scrap-edit-btn,.scrap-edit-wrap,.no-print{display:none !important;}' +
                    '</style>';
            }

            function buildExportBody(orientation) {
                var exportSections = buildExportSections();
                return '<div class="export-container">' + exportSections.join('') + '</div>';
            }

            function buildExportHtml(orientation) {
                return '<html><head><title>Production Report</title>' +
                    buildExportStyles(orientation) +
                    '</head><body>' +
                    buildExportBody(orientation) +
                    '</body></html>';
            }

            $('#print_report_btn').off('click').on('click', function() {
                var reportWindow = window.open('', '_blank', 'width=1400,height=900');
                if (!reportWindow) {
                    alert('Popup blocked. Please allow popups for this site to print the report.');
                    return;
                }
                reportWindow.document.open();
                reportWindow.document.write(buildExportHtml('landscape'));
                reportWindow.document.close();
                reportWindow.focus();
                reportWindow.print();
            });

            $('#download_report_btn').off('click').on('click', function() {
                // Determine orientation based on report type
                var reportType = $('#report_type').val();
                var orientation = (reportType === 'sheet') ? 'portrait' : 'landscape';

                var element = document.createElement('div');
                element.innerHTML = buildExportStyles(orientation) + buildExportBody(orientation);

                var opt = {
                    margin:      [12, 10, 12, 10],
                    filename:    'production_report_' + new Date().toISOString().slice(0, 10) + '.pdf',
                    image:       { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, letterRendering: true, logging: false },
                    jsPDF:       { unit: 'mm', format: 'a4', orientation: orientation },
                    pagebreak:   { mode: ['css', 'legacy'], before: '.page-break-before', avoid: ['tr', '.report-wrap'] }
                };

                var printedBy = $('#print_user_name').text() || '-';
                var printDate = new Date().toLocaleString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                var footerText = 'Printed: ' + printDate + ' | By: ' + printedBy;

                html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
                    var pageCount = pdf.internal.getNumberOfPages();
                    var pageSize = pdf.internal.pageSize;
                    var pageWidth = pageSize.getWidth();
                    var pageHeight = pageSize.getHeight();
                    pdf.setFontSize(8);
                    pdf.setTextColor(85, 85, 85);
                    pdf.setPage(pageCount);
                    pdf.text(footerText, pageWidth - 10, pageHeight - 6, { align: 'right' });
                }).save();
            });

            // DataTables is loaded in footer; wait until it is available
            (function initDataTables(tries) {
                tries = tries || 0;
                if (!$.fn.DataTable) {
                    if (tries < 200) setTimeout(function() { initDataTables(tries + 1); }, 50);
                    return;
                }

                const tableOptions = {
                    pageLength: 10,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    ordering: false,
                    info: true,
                    paging: true,
                    searching: true,
                    autoWidth: false,
                    dom: '<"top"lf>rt<"bottom"ip><"clear">',
                    initComplete: function(settings, json) {
                        var $wrapper = $(this).closest('.dataTables_wrapper');
                        var $top = $wrapper.find('.top');
                        var $parent = $wrapper.parent();
                        var $heading = $parent.find('h2.section-head, h3, .section-head').first();
                        if ($top.length && $heading.length) {
                            $top.insertBefore($heading);
                        }
                    }
                };
                const tableOptionsNoAuto = {
                    pageLength: 10,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    ordering: false,
                    info: true,
                    paging: true,
                    searching: true,
                    dom: '<"top"lf>rt<"bottom"ip><"clear">',
                    initComplete: function(settings, json) {
                        var $wrapper = $(this).closest('.dataTables_wrapper');
                        var $top = $wrapper.find('.top');
                        var $parent = $wrapper.parent();
                        var $heading = $parent.find('h2.section-head, h3, .section-head').first();
                        if ($top.length && $heading.length) {
                            $top.insertBefore($heading);
                        }
                    }
                };

                $('#overview_table').DataTable(tableOptions);
                $('#downtime_table').DataTable(tableOptions);
                $('#spc_table').DataTable(tableOptions);
                $('#summary_table').DataTable(tableOptionsNoAuto);
                $('#details_table').DataTable(tableOptionsNoAuto);
                $('#rejection_table').DataTable(tableOptionsNoAuto);
            })();
        }

        // jQuery is loaded in footer.php; wait until it is available
        (function waitForJQuery() {
            if (typeof window.jQuery === 'undefined') {
                setTimeout(waitForJQuery, 50);
                return;
            }
            window.jQuery(function($) {
                initProductionReport($);
            });
        })();
    })();
</script>


<style>
/* ── Inline Scrap Edit Styles ── */
.scrap-cell {
    min-width: 130px;
    vertical-align: middle !important;
}
.scrap-blank .scrap-display-val {
    color: #bbb;
}
.scrap-blank {
    background: #fff8e1 !important; /* soft amber highlight for missing values */
}
.scrap-display-wrap {
    display: flex;
    align-items: center;
    gap: 5px;
    justify-content: flex-end;
}
.scrap-edit-btn {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 1px 4px;
    font-size: 11px;
    line-height: 1;
    border-radius: 3px;
    transition: color 0.15s;
}
.scrap-edit-btn:hover { color: #0056d0; }
.scrap-blank .scrap-edit-btn {
    color: #e67e22;
    font-weight: bold;
}
.scrap-edit-wrap {
    display: flex;
    align-items: center;
    gap: 3px;
}
.scrap-input {
    width: 80px !important;
    padding: 2px 4px !important;
    font-size: 11px !important;
    height: 26px !important;
}
.scrap-save-btn, .scrap-cancel-btn {
    padding: 2px 6px !important;
    font-size: 11px !important;
    height: 26px !important;
    line-height: 1 !important;
}
.scrap-saving { opacity: 0.5; pointer-events: none; }
</style>

<script>
(function() {
    function initScrapEdit($) {
        var BASE_URL = '<?= base_url() ?>';

        // Open edit mode
        $(document).on('click', '.scrap-edit-btn', function() {
            var $cell = $(this).closest('.scrap-cell');
            $cell.find('.scrap-display-wrap').hide();
            $cell.find('.scrap-edit-wrap').show();
            $cell.find('.scrap-input').focus().select();
        });

        // Cancel
        $(document).on('click', '.scrap-cancel-btn', function() {
            var $cell = $(this).closest('.scrap-cell');
            $cell.find('.scrap-edit-wrap').hide();
            $cell.find('.scrap-display-wrap').show();
        });

        // Save on button click
        $(document).on('click', '.scrap-save-btn', function() {
            saveScrap($(this).closest('.scrap-cell'));
        });

        // Save on Enter key
        $(document).on('keydown', '.scrap-input', function(e) {
            if (e.key === 'Enter') {
                saveScrap($(this).closest('.scrap-cell'));
            } else if (e.key === 'Escape') {
                $(this).closest('.scrap-cell').find('.scrap-cancel-btn').trigger('click');
            }
        });

        function saveScrap($cell) {
            var id        = $cell.data('row-id');
            var total_qty = $cell.find('.scrap-input').val().trim();

            if (total_qty === '' || isNaN(parseFloat(total_qty))) {
                $cell.find('.scrap-input').addClass('is-invalid').focus();
                return;
            }
            $cell.find('.scrap-input').removeClass('is-invalid');
            $cell.addClass('scrap-saving');

            $.ajax({
                url:  BASE_URL + 'admin/Ajax_controller/update_rejection_scrap_qty',
                type: 'POST',
                dataType: 'json',
                data: { id: id, total_qty: total_qty },
                success: function(res) {
                    $cell.removeClass('scrap-saving');
                    if (res.status === 'success') {
                        var formatted = parseFloat(total_qty).toFixed(3);
                        $cell.find('.scrap-display-val').html(formatted);
                        $cell.removeClass('scrap-blank');
                        $cell.find('.scrap-edit-wrap').hide();
                        $cell.find('.scrap-display-wrap').show();
                    } else {
                        alert(res.message || 'Save failed. Please try again.');
                    }
                },
                error: function() {
                    $cell.removeClass('scrap-saving');
                    alert('Network error. Please try again.');
                }
            });
        }
    }

    // Wait for jQuery (loaded in footer.php)
    (function waitForJQuery() {
        if (typeof window.jQuery === 'undefined') {
            setTimeout(waitForJQuery, 50);
            return;
        }
        initScrapEdit(window.jQuery);
    })();
})();
</script>

<?php include('footer.php'); ?>
