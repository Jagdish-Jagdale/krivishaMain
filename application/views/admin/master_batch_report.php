<?php include('header.php'); ?>

<style type="text/css">


    .report-wrap {
        border: 1px solid #cfd5dd;
        border-radius: 6px;
        background: #fff;
        padding: 16px;
        margin-bottom: 20px;
        max-width: 100%;
        overflow-x: hidden;
        color: #000;
    }

    /* Keep tables inside the page without horizontal scrolling */
    .dataTables_wrapper {
        max-width: 100%;
        overflow-x: hidden;
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
    }

    #overview_table thead th {
        padding: 6px 6px !important;
        font-size: 11px;
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
        border: 1px solid #d1d9e6;
        padding: 8px;
        color: #000;
        font-size: 12px;
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
        color: #fff;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        text-align: left;
        letter-spacing: 0.5px;
    }

    .report-section table, .x_panel table {
        margin-top: 0 !important;
        border-top: none !important;
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
        background: #f8fbff;
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
        color: #000;
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

    .share-sheet {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        background: #fff;
        margin-bottom: 14px;
    }

    .share-sheet th,
    .share-sheet td {
        border: 1px solid #c5d5f5;
        padding: 8px 10px;
        font-size: 12px;
        vertical-align: top;
        background: #fff;
        word-wrap: break-word;
        color: #000;
    }

    .share-sheet th {
        background: #e8f0fd !important;
        font-weight: 700;
        color: #0056d0;
    }

    .share-sheet tr:first-child th[colspan],
    .share-sheet .title {
        font-size: 16px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.8px;
        background: #0056d0 !important;
        color: #fff !important;
        text-transform: uppercase;
    }

    .share-sheet .label {
        font-weight: 800;
        width: 180px;
        background: #e8f0fd !important;
        color: #0056d0;
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

    /* Removed old dataTables_length styles to use flex order above */

    .dataTables_length select {
        padding: 4px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin: 0 5px;
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
</style>

<div class="right_col">
    <h3 class="page-h3">Production Report</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="report-wrap">
                <form method="get" id="production_report_filter" class="filter-row">
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="all" <?= (isset($report_type) && $report_type === 'all') ? 'selected' : '' ?>>All Reports</option>
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

            <div class="summary-grid" id="production_summary_cards">
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

            <div class="report-wrap report-section" id="sheet_section">
                <h2 class="section-head">Production Report</h2>

                <table class="share-sheet">
                    <tr>
                        <td class="label">DATE</td>
                        <td colspan="3"><?= !empty($selected_date) ? $selected_date : '-' ?></td>
                        <td class="label">PLANT</td>
                        <td colspan="3"><?= !empty($sheet_meta) && !empty($sheet_meta->plant_name) ? $sheet_meta->plant_name : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="label">MACHINE NAME</td>
                        <td colspan="3"><?= !empty($sheet_meta) && !empty($sheet_meta->machine_name) ? $sheet_meta->machine_name : '-' ?></td>
                        <td class="label">ARTICLE</td>
                        <td colspan="3"><?= !empty($selected_article_name) ? $selected_article_name : 'All' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Operators Name</td>
                        <td colspan="7">-</td>
                    </tr>
                </table>

                <div class="share-grid">
                    <div>
                        <table class="share-sheet">
                            <tr>
                                <th colspan="2">USED RM WITH ALL LIST</th>
                            </tr>
                            <tr>
                                <th>Raw Material</th>
                                <th class="text-right">Qty</th>
                            </tr>
                            <?php if (!empty($used_rm_rows)) { ?>
                                <?php foreach ($used_rm_rows as $u) { ?>
                                    <tr>
                                        <td><?= !empty($u->raw_material_name) ? $u->raw_material_name : '-' ?></td>
                                        <td class="text-right"><?= number_format((float) $u->used_qty, 3) ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="2" class="text-center">No records</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                    <div>
                        <table class="share-sheet">
                            <tr>
                                <th colspan="2">FORMULA RAW MATERIAL LIST - BALANCE MATERIAL<br><span style="font-weight:600;">(Only 1st 2 columns required)</span></th>
                            </tr>
                            <tr>
                                <th>Raw Material</th>
                                <th class="text-right">Balance Qty</th>
                            </tr>
                            <?php
                            $has_rm_bal = false;
                            if (!empty($balance_rows)) {
                                foreach ($balance_rows as $b) {
                                    if (!empty($b->raw_material_name)) {
                                        $has_rm_bal = true;
                            ?>
                                        <tr>
                                            <td><?= $b->raw_material_name ?></td>
                                            <td class="text-right"><?= number_format((float) str_replace(',', '', (string) ($b->rm_total_qty ?? 0)), 3) ?></td>
                                        </tr>
                                    <?php }
                                }
                            }
                            if (!$has_rm_bal) { ?>
                                <tr>
                                    <td colspan="2" class="text-center">No records</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>

                <div class="share-grid">
                    <div>
                        <table class="share-sheet">
                            <tr>
                                <th colspan="2">USED MASTER BATCH WITH ALL LIST</th>
                            </tr>
                            <tr>
                                <th>Master Batch</th>
                                <th class="text-right">Qty</th>
                            </tr>
                            <?php if (!empty($used_mb_rows)) { ?>
                                <?php foreach ($used_mb_rows as $u) { ?>
                                    <tr>
                                        <td><?= !empty($u->master_batch_name) ? $u->master_batch_name : '-' ?></td>
                                        <td class="text-right"><?= number_format((float) $u->used_qty, 3) ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="2" class="text-center">No records</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                    <div>
                        <table class="share-sheet">
                            <tr>
                                <th colspan="2">FORMULA MASTER BATCH - BALANCE MASTER BATCH<br><span style="font-weight:600;">(Only 1st 2 columns required)</span></th>
                            </tr>
                            <tr>
                                <th>Master Batch</th>
                                <th class="text-right">Balance Qty</th>
                            </tr>
                            <?php
                            $has_mb_bal = false;
                            if (!empty($balance_rows)) {
                                foreach ($balance_rows as $b) {
                                    if (!empty($b->master_batch_name)) {
                                        $has_mb_bal = true;
                            ?>
                                        <tr>
                                            <td><?= $b->master_batch_name ?></td>
                                            <td class="text-right"><?= number_format((float) str_replace(',', '', (string) ($b->mb_total_qty ?? 0)), 3) ?></td>
                                        </tr>
                                    <?php }
                                }
                            }
                            if (!$has_mb_bal) { ?>
                                <tr>
                                    <td colspan="2" class="text-center">No records</td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>

                <table class="share-sheet">
                    <tr>
                        <th colspan="6">LIST OF REJECTION RM TABLE FROM PRODUCTION LIST<br><span style="font-weight:600;">(Only 1st 2 columns required)</span></th>
                    </tr>
                    <tr>
                        <th style="width:60px;">Sr</th>
                        <th>Defect Type</th>
                        <th class="text-right" style="width:160px;">Qty (Nos)</th>
                        <th class="text-right" style="width:180px;">Total Rejected Weight</th>
                        <th>Reason</th>
                        <th>Immediate Action</th>
                    </tr>
                    <?php if (!empty($rejection_rows)) { ?>
                        <?php $i = 1;
                        foreach ($rejection_rows as $r) { ?>
                            <?php $reason = trim((string) ($r->remark ?? '')); ?>
                            <tr>
                                <td class="text-right"><?= $i++ ?></td>
                                <td><?= !empty($r->rejection_name) ? $r->rejection_name : '-' ?></td>
                                <td class="text-right"><?= ($r->pc !== null && $r->pc !== '') ? number_format((float) $r->pc, 0) : '0' ?></td>
                                <td class="text-right"><?= ($r->total_qty !== null && $r->total_qty !== '') ? number_format((float) $r->total_qty, 3) : '0.000' ?></td>
                                <td><?= $reason !== '' ? $reason : '-' ?></td>
                                <td><?= $reason !== '' ? $reason : '-' ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="text-center">No records</td>
                        </tr>
                    <?php } ?>
                </table>



                <table class="share-sheet">
                    <tr>
                        <th colspan="6">SUMMARY</th>
                    </tr>
                    <tr>
                        <th>Article Name</th>
                        <th class="text-right">Approved qty</th>
                        <th class="text-right">Average weight</th>
                        <th class="text-right">Total Weight</th>
                        <th>Remarks</th>
                        <th class="text-right">Delta</th>
                    </tr>
                    <?php if (!empty($summary_rows)) { ?>
                        <?php foreach ($summary_rows as $sr) { ?>
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
                </table>

                <table class="share-sheet">
                    <tr>
                        <th colspan="8">ARTICLE WISE PLANNED VS ACTUAL (Light color coding)</th>
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

                            $ideal_seconds = (float) ($row->ideal_seconds ?? 0);
                            $performance_pct = ($operating_minutes > 0 && $ideal_seconds > 0)
                                ? (($ideal_seconds / ($operating_minutes * 60)) * 100)
                                : 0;
                            ?>
                            <tr>
                                <td><?= !empty($row->production_date) ? date('d-m-Y', strtotime($row->production_date)) : '-' ?></td>
                                <td><?= !empty($row->machine_name) ? $row->machine_name : '-' ?></td>
                                <td class="text-right light-cell"><?= number_format($planned_qty, 0) ?></td>
                                <td class="text-right light-cell"><?= number_format($actual_qty, 0) ?></td>
                                <td class="text-right light-cell"><?= number_format($plan_ach, 2) ?></td>
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
                                <td class="text-right light-cell"><?= ($ideal_seconds > 0) ? number_format($performance_pct, 2) : '-' ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8" class="text-center">No records</td>
                        </tr>
                    <?php } ?>
                </table>
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
                                    $operator_raw = trim((string) ($row->operator_name ?? ''));
                                    $operator_list = [];
                                    if ($operator_raw !== '') {
                                        $operator_list = array_values(array_filter(array_map('trim', preg_split('/[,;|+&\/]+/', $operator_raw))));
                                    }
                                    $operator_unique = !empty($operator_list) ? array_values(array_unique($operator_list)) : [];
                                    $operator_same_diff = empty($operator_unique)
                                        ? '-'
                                        : ((count($operator_unique) > 1) ? 'Different' : 'Same');

                                    if (!empty($shift_entries)) {
                                        $op_lines = [];
                                        if (count($operator_list) === count($shift_entries)) {
                                            $op_lines = $operator_list;
                                        } elseif (count($operator_list) === 1) {
                                            $op_lines = array_fill(0, count($shift_entries), $operator_list[0]);
                                        } elseif (count($operator_list) > 1) {
                                            $joined = implode(', ', $operator_list);
                                            $op_lines = array_fill(0, count($shift_entries), $joined);
                                        } else {
                                            $op_lines = array_fill(0, count($shift_entries), '-');
                                        }

                                        echo '<div class="shift-breakdown">';
                                        foreach ($op_lines as $ol) {
                                            echo '<div class="line">' . htmlspecialchars((string) $ol) . '</div>';
                                        }
                                        echo '<div class="total">' . htmlspecialchars($operator_same_diff) . '</div>';
                                        echo '</div>';
                                    } else {
                                        echo $operator_raw !== '' ? htmlspecialchars($operator_raw) : '-';
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
                                    <td class="text-right"><?= ($ideal_seconds > 0 || $std_cycle_time_row > 0) ? number_format($performance_pct, 2) : '-' ?></td>
                                    <td class="text-right"><?= ($ideal_seconds > 0 || $std_cycle_time_row > 0) ? number_format($oee_pct, 2) : '-' ?></td>
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
                                    <td class="text-right">-</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td></td>
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
                            <th class="text-right">Qty (Nos)</th>
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
                                    <td class="text-right"><?= ($row->pc !== null && $row->pc !== '') ? number_format((float) $row->pc, 0) : '0' ?></td>
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
                                <td colspan="9" class="text-center">No rejection records found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function initProductionReport($) {
            $('#product_master').addClass('active');
            $('.production_report').addClass('active_cc');

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
                } else {
                    // All Reports (4 reports)
                    $('#overview_section, #downtime_section, #spc_section, #rejection_section').show();
                    $('#production_summary_cards').show();
                }

                // When tables are shown/hidden, DataTables needs a width recalculation
                if ($.fn.DataTable) {
                    setTimeout(function() {
                        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                    }, 50);
                }
            }

            applyReportFilter($('#report_type').val());

            $('#report_type').on('change', function() {
                applyReportFilter($(this).val());
            });

            function buildExportHtml() {
                var exportSections = [];
                $('.summary-grid:visible, .report-section:visible').each(function() {
                    exportSections.push($(this).prop('outerHTML'));
                });

                return '<html><head><title>Production Report</title><style>body{font-family:Arial,sans-serif;padding:20px;color:#000;} table{width:100%;border-collapse:collapse;margin-bottom:18px;} th,td{border:1px solid #d1d9e6;padding:8px;vertical-align:middle;font-size:12px;color:#000;} th{background:#eef2f7 !important;color:#333;font-weight:700;} h2.section-head{font-size:15px;margin:20px 0 0;text-transform:uppercase;background:#0056d0;color:#fff;padding:10px 15px;font-weight:800;text-align:left;border-radius:4px 4px 0 0;} .report-wrap{margin-bottom:20px;} .text-right{text-align:right;} .summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px;} .summary-card{border:1px solid #d7dde5;border-radius:8px;padding:14px 16px;background:#f8fbff;} .summary-card .label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;margin-bottom:6px;color:#5a6472;} .summary-card .value{font-size:22px;font-weight:800;color:#000;}' +

                    '/* Share-sheet formatting */' +
                    '.share-sheet{width:100%;border-collapse:collapse;background:#fff;margin-bottom:12px;}' +
                    '.share-sheet th,.share-sheet td{border:1px solid #d1d9e6;padding:6px 8px;font-size:10px;background:#fff;color:#000;}' +
                    '.share-sheet th{background:#eef2f7 !important;font-weight:700;color:#333 !important;}' +
                    '.share-sheet tr:first-child th[colspan]{background:#0056d0 !important;color:#fff !important;font-weight:800;}' +
                    '.share-sheet .label{font-weight:800;width:180px;background:#eef2f7 !important;}' +
                    '.light-cell{background:#eaf4ff !important;}' +
                    '.share-grid{display:table;width:100%;border-collapse:separate;border-spacing:12px 0;margin-bottom:14px;}' +
                    '.share-grid>div{display:table-cell;vertical-align:top;width:50%;}' +

                    '</style></head><body><h2 style="font-family:Arial,sans-serif;margin:0 0 14px;color:#0056d0;text-align:center;text-transform:uppercase;">MASTER BATCH REPORT</h2>' + exportSections.join('') + '</body></html>';
            }

            // Re-bind safely (in case of partial reload)
            $('#print_report_btn').off('click').on('click', function() {
                var reportWindow = window.open('', '_blank', 'width=1400,height=900');
                if (!reportWindow) {
                    alert('Popup blocked. Please allow popups for this site to print the report.');
                    return;
                }
                reportWindow.document.open();
                reportWindow.document.write(buildExportHtml());
                reportWindow.document.close();
                reportWindow.focus();
                reportWindow.print();
            });

            $('#download_report_btn').off('click').on('click', function() {
                var html = buildExportHtml();
                var blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
                var url = window.URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'production_report_' + new Date().toISOString().slice(0, 10) + '.xls';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
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
                    searching: false,
                    info: true,
                    autoWidth: false,
                    "initComplete": function(settings, json) {
                        var $wrapper = $(this).closest('.dataTables_wrapper');
                        var $length = $wrapper.find('.dataTables_length');
                        var $heading = $wrapper.prev('h3, .section-head, h2');
                        if ($length.length && $heading.length) {
                            $length.insertBefore($heading);
                        }
                    }
                };
                const tableOptionsNoAuto = {
                    pageLength: 10,
                    lengthChange: true,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    ordering: false,
                    searching: false,
                    info: true,
                    "initComplete": function(settings, json) {
                        var $wrapper = $(this).closest('.dataTables_wrapper');
                        var $length = $wrapper.find('.dataTables_length');
                        var $heading = $wrapper.prev('h3, .section-head, h2');
                        if ($length.length && $heading.length) {
                            $length.insertBefore($heading);
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
