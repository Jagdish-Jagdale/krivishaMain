<?php include('header.php'); ?>
<style>
    .pp-wrap {
        max-width: 960px;
        margin: 0 auto;
        font-size: 12px;
        font-family: Arial, sans-serif;
        color: #111;
    }
    .pp-outer {
        border: 2px solid #333;
        padding: 0;
    }
    .pp-company {
        text-align: center;
        padding: 8px 4px 4px;
        border-bottom: 1px solid #333;
    }
    .pp-company h5 { margin: 0; font-size: 14px; font-weight: 700; }
    .pp-company p  { margin: 0; font-size: 12px; }
    .pp-company h6 { margin: 4px 0 0; font-size: 13px; font-weight: 700; letter-spacing: 1px; }

    /* Generic section table */
    .pp-tbl {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .pp-tbl td, .pp-tbl th {
        border: 1px solid #555;
        padding: 3px 5px;
        vertical-align: middle;
        word-break: break-word;
        overflow: hidden;
    }
    .pp-tbl th {
        background: #e8e8e8;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
    }
    .pp-tbl td.lbl {
        background: #f5f5f5;
        font-weight: 600;
        font-size: 11px;
        color: #333;
        white-space: nowrap;
    }
    .pp-tbl td.val {
        font-weight: 700;
        color: #1a1a6e;
    }
    .sec-title {
        background: #d0d8e8;
        font-weight: 700;
        font-size: 12px;
        padding: 3px 6px;
        border-top: 1px solid #555;
        border-bottom: 1px solid #555;
    }
    .pp-footer {
        border-top: 1px solid #555;
        font-size: 11px;
    }
    .pp-footer td { padding: 3px 6px; border: 1px solid #555; }

    @media print {
        .no-print { display: none !important; }
        .pp-wrap   { max-width: 100%; }
        body       { background: #fff; }
        .right_col { margin: 0 !important; padding: 0 !important; }
    }

    /* helper */
    .d { color: #1a1a6e; font-weight: 700; }
</style>

<div class="right_col" role="main">
    <div class="table">
        <div class="page-title no-print" style="display:flex; align-items:center; justify-content:space-between; padding:10px 15px;">
            <h3 style="margin:0;">Process Parameter Sheet</h3>
            <div style="display:flex; gap:8px;">
                <a href="<?= base_url('process_parameter_list') ?>" class="btn btn-sm btn-secondary" style="background:#6c757d; color:#fff; border:none; padding:5px 12px; border-radius:4px; text-decoration:none;">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="clearfix"></div>

        <?php $r = $record; ?>

        <div class="pp-wrap">
        <div class="pp-outer">

            <!-- ── COMPANY HEADER ── -->
            <div class="pp-company">
                <h5>KRIVISHA INDUSTRIES PVT LTD</h5>
                <p>J-92, Kupwad MIDC.</p>
                <h6>PROCESS PARAMETER SHEET</h6>
            </div>

            <!-- ── ROW 1: M/C | Article Name | Date | Shift ── -->
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:7%"><col style="width:10%">
                    <col style="width:10%"><col style="width:22%">
                    <col style="width:7%"><col style="width:12%">
                    <col style="width:7%"><col style="width:10%">
                    <col style="width:7%"><col style="width:8%">
                </colgroup>
                <tr>
                    <td class="lbl">M/C</td>
                    <td class="val d"><?= htmlspecialchars($r->machine_name ?? '-') ?></td>
                    <td class="lbl">Article Name</td>
                    <td class="val d" colspan="3"><?= htmlspecialchars($r->article_name ?? '-') ?></td>
                    <td class="lbl">Date</td>
                    <td class="val d"><?= !empty($r->production_date) ? date('d-m-Y', strtotime($r->production_date)) : '-' ?></td>
                    <td class="lbl">Shift</td>
                    <td class="val d"><?= htmlspecialchars($r->shift ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="lbl">Material</td>
                    <td class="val d"><?= htmlspecialchars($r->material ?? '-') ?></td>
                    <td class="lbl">Cycle Time (s)</td>
                    <td class="val d"><?= $r->cycle_time !== null ? number_format((float)$r->cycle_time, 3) : '-' ?></td>
                    <td class="lbl" colspan="2">Article Wt (g)</td>
                    <td class="val d" colspan="2"><?= $r->article_wt !== null ? number_format((float)$r->article_wt, 3) : '-' ?></td>
                    <td class="lbl">Plant</td>
                    <td class="val d"><?= htmlspecialchars($r->plant_name ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="lbl">Colour (MB)</td>
                    <td class="val d"><?= htmlspecialchars($r->colour ?? '-') ?></td>
                    <td class="lbl">No. of Cavities</td>
                    <td class="val d"><?= $r->no_of_cavities ?? '-' ?></td>
                    <td class="lbl" colspan="2">Runner Wt (g)</td>
                    <td class="val d" colspan="2"><?= $r->runner_wt !== null ? number_format((float)$r->runner_wt, 3) : '-' ?></td>
                    <td class="lbl">MB %</td>
                    <td class="val d"><?= $r->mb_percent !== null ? number_format((float)$r->mb_percent, 3) : '-' ?></td>
                </tr>
            </table>

            <!-- ── BARREL TEMP ── -->
            <div class="sec-title">Barrel Temp.</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:8%"><col style="width:13%"><col style="width:13%">
                    <col style="width:13%"><col style="width:13%"><col style="width:13%">
                    <col style="width:13%"><col style="width:14%">
                </colgroup>
                <tr>
                    <th>Temp.</th><th>Nozzle</th><th>Zone 1</th><th>Zone 2</th>
                    <th>Zone 3</th><th>Zone 4</th><th>Zone 5</th><th>Zone 6</th>
                </tr>
                <tr>
                    <td class="lbl">Set</td>
                    <td class="val d"><?= $r->temp_nozzle_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone1_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone2_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone3_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone4_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone5_set ?? '-' ?></td>
                    <td class="val d"><?= $r->temp_zone6_set ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── COOLING / FILL TIME / XFER ── -->
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:14%"><col style="width:12%">
                    <col style="width:12%"><col style="width:12%">
                    <col style="width:20%"><col style="width:30%">
                </colgroup>
                <tr>
                    <td class="lbl">Cooling Time</td>
                    <td class="val d"><?= $r->cooling_time ?? '-' ?></td>
                    <td class="lbl">Fill Time</td>
                    <td class="val d"><?= $r->fill_time ?? '-' ?></td>
                    <td class="lbl">X'fer Pos (mm)</td>
                    <td class="val d"><?= $r->xfer_pos_mm ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── INJECTION – Fill Profile ── -->
            <div class="sec-title">INJECTION – Fill Profile</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:15%"><col style="width:21%"><col style="width:21%">
                    <col style="width:21%"><col style="width:22%">
                </colgroup>
                <tr>
                    <th>Profile No.</th><th>Pos 4 (mm)</th><th>Pos 3 (mm)</th>
                    <th>Pos 2 (mm)</th><th>Pos 1 (mm)</th>
                </tr>
                <tr>
                    <td class="val d"><?= htmlspecialchars($r->fill_profile_no ?? '-') ?></td>
                    <td class="val d"><?= $r->fill_profile_pos_4 ?? '-' ?></td>
                    <td class="val d"><?= $r->fill_profile_pos_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->fill_profile_pos_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->fill_profile_pos_1 ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── PACK / HOLD ── -->
            <div class="sec-title">PACK / HOLD</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:17%"><col style="width:17%"><col style="width:17%">
                    <col style="width:17%"><col style="width:16%"><col style="width:16%">
                </colgroup>
                <tr>
                    <th>Pos (mm)</th><th>Spd (mm/s)</th><th>Time (s)</th>
                    <th>Prs (bar)</th><th>Prs (s)</th><th>Spd (mm/s)</th>
                </tr>
                <tr>
                    <td class="val d"><?= $r->pack_pos_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_spd_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_time_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_prs_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_prs_s_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_spd_mm_s_1 ?? '-' ?></td>
                </tr>
                <tr>
                    <td class="val d"><?= $r->pack_pos_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_spd_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_time_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_prs_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_prs_s_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->pack_spd_mm_s_2 ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── SUCK BACK / REFILL ── -->
            <div class="sec-title">SUCK BACK / REFILL</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:16%"><col style="width:14%"><col style="width:16%">
                    <col style="width:16%"><col style="width:14%"><col style="width:24%">
                </colgroup>
                <tr>
                    <th>Manual Back Prs (bar)</th><th>Suck Back Profile</th>
                    <th>Suck Back Pos (mm)</th><th>Shot Size Pos (mm)</th>
                    <th>Refill Spd (rpm)</th><th>Refill Back Prs (bar)</th>
                </tr>
                <tr>
                    <td class="val d"><?= $r->manual_back_prs ?? '-' ?></td>
                    <td class="val d"><?= htmlspecialchars($r->suck_back_profile ?? '-') ?></td>
                    <td class="val d"><?= $r->suck_back_pos_mm ?? '-' ?></td>
                    <td class="val d"><?= $r->shot_size_pos_mm ?? '-' ?></td>
                    <td class="val d"><?= $r->refill_spd_rpm ?? '-' ?></td>
                    <td class="val d"><?= $r->refill_back_prs ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── MOLD SAFETY / TONNAGE ── -->
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:20%"><col style="width:30%">
                    <col style="width:20%"><col style="width:30%">
                </colgroup>
                <tr>
                    <td class="lbl">Mold Safety</td>
                    <td class="val d"><?= $r->mold_safety ?? '-' ?></td>
                    <td class="lbl">Tonnage</td>
                    <td class="val d"><?= $r->tonnage ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── CLOSE Profile ── -->
            <div class="sec-title">CLOSE Profile</div>
            <table class="pp-tbl" style="font-size:11px;">
                <colgroup>
                    <col style="width:7%">
                    <col style="width:6%"><col style="width:6%"><col style="width:6%"><col style="width:6%">
                    <col style="width:5%"><col style="width:5%"><col style="width:5%"><col style="width:5%">
                    <col style="width:5%"><col style="width:5%"><col style="width:5%"><col style="width:5%">
                    <col style="width:8%"><col style="width:9%"><col style="width:7%">
                </colgroup>
                <tr>
                    <th>Profile No.</th>
                    <th>Pos 1</th><th>Pos 2</th><th>Pos 3</th><th>Pos 4</th>
                    <th>Spd 1</th><th>Spd 2</th><th>Spd 3</th><th>Spd 4</th>
                    <th>Prs 1</th><th>Prs 2</th><th>Prs 3</th><th>Prs 4</th>
                    <th>Ton Time (s)</th><th>Auto Tonnage</th><th>Open Limit</th>
                </tr>
                <tr>
                    <td class="val d"><?= htmlspecialchars($r->close_profile_no ?? '-') ?></td>
                    <td class="val d"><?= $r->close_pos_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_pos_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_pos_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_pos_4 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_spd_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_spd_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_spd_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_spd_4 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_prs_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_prs_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_prs_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_prs_4 ?? '-' ?></td>
                    <td class="val d"><?= $r->close_ton_time ?? '-' ?></td>
                    <td class="val d"><?= $r->auto_tonnage ?? '-' ?></td>
                    <td class="val d"><?= $r->open_limit ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── OPEN Profile ── -->
            <div class="sec-title">OPEN Profile</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:11%"><col style="width:11%">
                    <col style="width:11%"><col style="width:11%"><col style="width:11%">
                    <col style="width:11%"><col style="width:11%"><col style="width:11%">
                    <col style="width:12%">
                </colgroup>
                <tr>
                    <th>Profile No.</th><th>Brk Away</th>
                    <th>Pos 3 (mm)</th><th>Pos 2 (mm)</th><th>Pos 1 (mm)</th>
                    <th>Spd 3</th><th>Spd 2</th><th>Spd 1</th>
                    <th>Set Tonnage</th>
                </tr>
                <tr>
                    <td class="val d"><?= htmlspecialchars($r->open_profile_no ?? '-') ?></td>
                    <td class="val d"><?= $r->open_brk_away ?? '-' ?></td>
                    <td class="val d"><?= $r->open_pos_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->open_pos_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->open_pos_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->open_spd_3 ?? '-' ?></td>
                    <td class="val d"><?= $r->open_spd_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->open_spd_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->set_tonnage ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── HYDRAULIC EJECT ── -->
            <div class="sec-title">HYDRAULIC EJECT – Forward</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:14%"><col style="width:14%"><col style="width:14%">
                    <col style="width:16%"><col style="width:16%"><col style="width:26%">
                </colgroup>
                <tr>
                    <th>Prs (bar)</th><th>Limit 1</th><th>Limit 2</th>
                    <th>Pos (mm)</th><th>Spd (mm/s)</th><th>Prs (bar)</th>
                </tr>
                <tr>
                    <td class="val d"><?= $r->hydr_fwd_prs ?? '-' ?></td>
                    <td class="val d"><?= $r->hydr_fwd_limit_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->hydr_fwd_limit_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->hydr_fwd_pos_mm ?? '-' ?></td>
                    <td class="val d"><?= $r->hydr_fwd_spd_mm_s ?? '-' ?></td>
                    <td class="val d"><?= $r->hydr_fwd_prs_bar ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── AIR EJECTION ── -->
            <div class="sec-title">AIR EJECTION</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:11%"><col style="width:11%"><col style="width:11%">
                    <col style="width:11%"><col style="width:11%"><col style="width:11%">
                    <col style="width:12%"><col style="width:12%">
                </colgroup>
                <tr>
                    <th>Start Pos</th><th>Pulse Limit</th><th>Dly Time (s)</th>
                    <th>On Time (s)</th><th>Pos 1 (mm)</th><th>Pos 2 (mm)</th>
                    <th>Prs 1 (bar)</th><th>Prs 2 (bar)</th>
                </tr>
                <tr>
                    <td class="val d"><?= $r->air_ej_start_pos ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_pulse_limit ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_dly_time ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_on_time ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_pos_mm_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_pos_mm_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_prs_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->air_ej_prs_2 ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── RETRACT ── -->
            <div class="sec-title">RETRACT</div>
            <table class="pp-tbl">
                <colgroup>
                    <col style="width:14%"><col style="width:14%"><col style="width:14%">
                    <col style="width:16%"><col style="width:16%"><col style="width:26%">
                </colgroup>
                <tr>
                    <th>Limit 1</th><th>Limit 2</th><th>Pulse Limit</th>
                    <th>Pos (mm)</th><th>Spd (mm/s)</th><th>Prs (bar)</th>
                </tr>
                <tr>
                    <td class="val d"><?= $r->retract_limit_1 ?? '-' ?></td>
                    <td class="val d"><?= $r->retract_limit_2 ?? '-' ?></td>
                    <td class="val d"><?= $r->retract_pulse_limit ?? '-' ?></td>
                    <td class="val d"><?= $r->retract_pos_mm ?? '-' ?></td>
                    <td class="val d"><?= $r->retract_spd_mm_s ?? '-' ?></td>
                    <td class="val d"><?= $r->retract_prs_bar ?? '-' ?></td>
                </tr>
            </table>

            <!-- ── FOOTER ── -->
            <table class="pp-tbl pp-footer">
                <tr>
                    <td colspan="3" style="font-size:11px; color:#444; border-bottom:1px solid #555;">
                        Controlled parameters, Tolerance on temperature: ±10°, Pressure &amp; speed: ±10 and cycle time: ±5 Sec.
                    </td>
                </tr>
                <tr>
                    <td style="width:33%"><strong>Prepared By:</strong> Mr. Harshad Patil</td>
                    <td style="width:34%"><strong>Reviewed By:</strong> SHIFT SUPERVISOR</td>
                    <td style="width:33%"><strong>Approved By:</strong> Mr. Mohan Shete</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <strong>Filled By (App):</strong> <?= htmlspecialchars($r->employee_name ?? '-') ?>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <strong>Submitted On:</strong> <?= !empty($r->created_on) ? date('d-m-Y H:i', strtotime($r->created_on)) : '-' ?>
                    </td>
                </tr>
            </table>

        </div><!-- /.pp-outer -->
        </div><!-- /.pp-wrap -->

    </div>
</div>

<?php include('footer.php'); ?>
