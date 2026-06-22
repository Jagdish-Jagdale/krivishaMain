<?php include('header.php'); ?>

<style type="text/css">
    .error {
        color: red !important;
    }

    .flex_wrap {
        display: flex;
        flex-wrap: wrap;
    }

    .select2-container {
        width: 100% !important;
    }

    th {
        text-align: center;
    }

    .table>tbody>tr>td {
        padding: 0;
        height: 40px;
        min-width: 135px;
        text-align: center;
        vertical-align: middle;
    }

    .table>tbody>tr>td input {
        width: 100%;
        height: 65%;
    }

    .table>tbody>tr>td[rowspan] input {
        padding: 15px;
    }

    .right_col {
        margin-bottom: 10px;
    }

    .red {
        background-color: rgb(249, 94, 91);
        color: black;
    }

    .green {
        background-color: rgb(98, 192, 93);
        color: black;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    input.red {
        border-color: #dc3545 !important;
    }

    input.green {
        border-color: #28a745 !important;
    }

    label.error {
        font-size: 0.85em;
        margin-top: 4px;
        display: block;
    }

    .custom-tooltip {
        position: absolute;
        background: linear-gradient(135deg, #4a90e2, #357ABD);
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
        white-space: normal;
        max-width: 250px;
        word-wrap: break-word;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        letter-spacing: 0.03em;
        font-weight: 600;
        z-index: 9999;
    }

    .custom-tooltip.show {
        opacity: 1;
        cursor: pointer;
    }

    .custom-tooltip::after {
        content: '';
        position: absolute;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 6px 6px 0 6px;
        border-color: #357ABD transparent transparent transparent;
        bottom: -6px;
        left: 20px;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">


    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Production Report</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered freezed-table"
                                id="dataTable">
                                <thead class="thead">
                                    <tr>
                                        <th rowspan="2">Article Name</th>
                                        <th rowspan="2"> Approved qty</th>
                                        <th rowspan="2"> Average weight</th>
                                        <th rowspan="2"></th>
                                        <th class="time">8-9</th>
                                        <th class="time">9-10</th>
                                        <th class="time">10-11</th>
                                        <th class="time">11-12</th>
                                        <th class="time">12-13</th>
                                        <th class="time">13-14</th>
                                        <th class="time">14-15</th>
                                        <th class="time">15-16</th>
                                        <th class="time">16-17</th>
                                        <th class="time">17-18</th>
                                        <th class="time">18-19</th>
                                        <th class="time">19-20</th>
                                        <th class="time">20-21</th>
                                        <th class="time">21-22</th>
                                        <th class="time">22-23</th>
                                        <th class="time">23-00</th>
                                        <th class="time">00-01</th>
                                        <th class="time">01-02</th>
                                        <th class="time">02-03</th>
                                        <th class="time">03-04</th>
                                        <th class="time">04-05</th>
                                        <th class="time">05-06</th>
                                        <th class="time">06-07</th>
                                        <th class="time">07-08</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>User Action</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">
                                <tbody>

                                    <?php
                                    foreach ($article_data as $index => $article) {
                                        $article_production_data = $this->Admin_model->get_article_production_details($this->uri->segment(2), $article->id);
                                        // echo"<pre>";print_r($article_production_data);exit;
                                        ?>
                                        <row>
                                            <tr>
                                                <td rowspan="2"><?= $article->article_name ?></td>

                                                <td rowspan="2" id="total_qty_<?= $article->id ?>">
                                                    <?= $article_production_data && $article_production_data->approved_qty !== null ? number_format($article_production_data->approved_qty, 3) : '' ?>
                                                </td>
                                                <!-- <td rowspan="2" id="total_qty_<?= $article->id ?>">
                                                    <?= $article_production_data ? $article_production_data->approved_qty : '' ?>
                                                </td> -->
                                                <td rowspan="2" id="total_weight_<?= $article->id ?>">
                                                    <?= $article_production_data && $article_production_data->average_qty !== null ? number_format($article_production_data->average_qty, 3) : '' ?>
                                                </td>
                                                <input type="hidden" name="total_qty_val" id="total_qty_val" value="">
                                                <input type="hidden" name="total_weight_val" id="total_weight_val" value="">
                                                <td>Qty Per Hour</td>
                                                <td>
                                                    <input type="text" name="qty_eight_nine_<?= $article->id ?>"
                                                        id="qty_eight_nine_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_eight_nine) ? $article_production_data->qty_eight_nine : '' ?>"
                                                        onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)">

                                                    <input type="hidden" name="remark_data[qty_eight_nine_remark]"
                                                        id="qty_eight_nine_remark_<?= $article->id ?>" value="<?= isset($article_production_data->qty_eight_nine_remark)
                                                              ? $article_production_data->qty_eight_nine_remark
                                                              : '' ?>" class="hidden_input" />

                                                </td>
                                                <td>
                                                    <input type="hidden" name="machine_id" id="machine_id"
                                                        value="<?= $machine->machine_id ?>">
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        value="<?= isset($article_production_data->qty_nine_ten) ? $article_production_data->qty_nine_ten : '' ?>"
                                                        name="qty_nine_ten_<?= $article->id ?>"
                                                        id="qty_nine_ten_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_nine_ten_remark_<?= $article->id ?>"
                                                        id="qty_nine_ten_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_nine_ten_remark) ? $article_production_data->qty_nine_ten_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_ten_eleven_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_ten_eleven) ? $article_production_data->qty_ten_eleven : '' ?>"
                                                        id="qty_ten_eleven_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_ten_eleven_remark_<?= $article->id ?>"
                                                        id="qty_ten_eleven_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_ten_eleven_remark) ? $article_production_data->qty_ten_eleven_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_eleven_twelve_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_eleven_twelve) ? $article_production_data->qty_eleven_twelve : '' ?>"
                                                        id="qty_eleven_twelve_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_eleven_twelve_remark_<?= $article->id ?>"
                                                        id="qty_eleven_twelve_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_eleven_twelve_remark) ? $article_production_data->qty_eleven_twelve_remark : '' ?>">
                                                </td>

                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)"
                                                        value="<?= isset($article_production_data->qty_twelve_thirteen) ? $article_production_data->qty_twelve_thirteen : '' ?>"
                                                        type="text" name="qty_twelve_thirteen_<?= $article->id ?>"
                                                        id="qty_twelve_thirteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_twelve_thirteen_remark_<?= $article->id ?>"
                                                        id="qty_twelve_thirteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twelve_thirteen_remark) ? $article_production_data->qty_twelve_thirteen_remark : '' ?>">

                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)"
                                                        value="<?= isset($article_production_data->qty_thirteen_fourteen) ? $article_production_data->qty_thirteen_fourteen : '' ?>"
                                                        type="text" name="qty_thirteen_fourteen_<?= $article->id ?>"
                                                        id="qty_thirteen_fourteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_thirteen_fourteen_remark_<?= $article->id ?>"
                                                        id="qty_thirteen_fourteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_thirteen_fourteen_remark) ? $article_production_data->qty_thirteen_fourteen_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_fourteen_fifteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_fourteen_fifteen) ? $article_production_data->qty_fourteen_fifteen : '' ?>"
                                                        id="qty_fourteen_fifteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_fourteen_fifteen_remark_<?= $article->id ?>"
                                                        id="qty_fourteen_fifteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_fourteen_fifteen_remark) ? $article_production_data->qty_fourteen_fifteen_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_fifteen_sixteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_fifteen_sixteen) ? $article_production_data->qty_fifteen_sixteen : '' ?>"
                                                        id="qty_fifteen_sixteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_fifteen_sixteen_remark_<?= $article->id ?>"
                                                        id="qty_fifteen_sixteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_fifteen_sixteen_remark) ? $article_production_data->qty_fifteen_sixteen_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_sixteen_seventeen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_sixteen_seventeen) ? $article_production_data->qty_sixteen_seventeen : '' ?>"
                                                        id="qty_sixteen_seventeen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_sixteen_seventeen_remark_<?= $article->id ?>"
                                                        id="qty_sixteen_seventeen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_sixteen_seventeen_remark) ? $article_production_data->qty_sixteen_seventeen_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_seventeen_eighteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_seventeen_eighteen) ? $article_production_data->qty_seventeen_eighteen : '' ?>"
                                                        id="qty_seventeen_eighteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_seventeen_eighteen_remark_<?= $article->id ?>"
                                                        id="qty_seventeen_eighteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_seventeen_eighteen_remark) ? $article_production_data->qty_seventeen_eighteen_remark : '' ?>">
                                                </td>
                                                <td><input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        value="<?= isset($article_production_data->qty_eighteen_nineteen) ? $article_production_data->qty_eighteen_nineteen : '' ?>"
                                                        name="qty_eighteen_nineteen_<?= $article->id ?>"
                                                        id="qty_eighteen_nineteen_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_eighteen_nineteen_remark_<?= $article->id ?>"
                                                        id="qty_eighteen_nineteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_eighteen_nineteen_remark) ? $article_production_data->qty_eighteen_nineteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)" type="text"
                                                        name="qty_nineteen_twenty_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_nineteen_twenty) && $article_production_data->qty_nineteen_twenty ? $article_production_data->qty_nineteen_twenty : '' ?>"
                                                        id="qty_nineteen_twenty_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_nineteen_twenty_remark_<?= $article->id ?>"
                                                        id="qty_nineteen_twenty_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_nineteen_twenty_remark) ? $article_production_data->qty_nineteen_twenty_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_twenty_twentyone_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twenty_twentyone) && $article_production_data->qty_twenty_twentyone ? $article_production_data->qty_twenty_twentyone : '' ?>"
                                                        id="qty_twenty_twentyone_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_twenty_twentyone_remark_<?= $article->id ?>"
                                                        id="qty_twenty_twentyone_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twenty_twentyone_remark) ? $article_production_data->qty_twenty_twentyone_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_twentyone_twentytwo_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentyone_twentytwo) && $article_production_data->qty_twentyone_twentytwo ? $article_production_data->qty_twentyone_twentytwo : '' ?>"
                                                        id="qty_twentyone_twentytwo_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_twentyone_twentytwo_remark_<?= $article->id ?>"
                                                        id="qty_twentyone_twentytwo_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentyone_twentytwo_remark) ? $article_production_data->qty_twentyone_twentytwo_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_twentytwo_twentythree_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentytwo_twentythree) && $article_production_data->qty_twentytwo_twentythree ? $article_production_data->qty_twentytwo_twentythree : '' ?>"
                                                        id="qty_twentytwo_twentythree_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_twentytwo_twentythree_remark_<?= $article->id ?>"
                                                        id="qty_twentytwo_twentythree_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentytwo_twentythree_remark) ? $article_production_data->qty_twentytwo_twentythree_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_twentythree_zero_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentythree_zero) && $article_production_data->qty_twentythree_zero ? $article_production_data->qty_twentythree_zero : '' ?>"
                                                        id="qty_twentythree_zero_<?= $article->id ?>">
                                                    <input type="hidden"
                                                        name="qty_twentythree_zero_remark_<?= $article->id ?>"
                                                        id="qty_twentythree_zero_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_twentythree_zero_remark) ? $article_production_data->qty_twentythree_zero_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_zero_one_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_zero_one) && $article_production_data->qty_zero_one ? $article_production_data->qty_zero_one : '' ?>"
                                                        id="qty_zero_one_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_zero_one_remark_<?= $article->id ?>"
                                                        id="qty_zero_one_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_zero_one_remark) ? $article_production_data->qty_zero_one_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_one_two_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_one_two) && $article_production_data->qty_one_two ? $article_production_data->qty_one_two : '' ?>"
                                                        id="qty_one_two_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_one_two_remark_<?= $article->id ?>"
                                                        id="qty_one_two_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_one_two_remark) ? $article_production_data->qty_one_two_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_two_three_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_two_three) && $article_production_data->qty_two_three ? $article_production_data->qty_two_three : '' ?>"
                                                        id="qty_two_three_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_two_three_remark_<?= $article->id ?>"
                                                        id="qty_two_three_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_two_three_remark) ? $article_production_data->qty_two_three_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_three_four_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_three_four) && $article_production_data->qty_three_four ? $article_production_data->qty_three_four : '' ?>"
                                                        id="qty_three_four_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_three_four_remark_<?= $article->id ?>"
                                                        id="qty_three_four_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_three_four_remark) ? $article_production_data->qty_three_four_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_four_five_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_four_five) && $article_production_data->qty_four_five ? $article_production_data->qty_four_five : '' ?>"
                                                        id="qty_four_five_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_four_five_remark_<?= $article->id ?>"
                                                        id="qty_four_five_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_four_five_remark) ? $article_production_data->qty_four_five_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_five_six_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_five_six) && $article_production_data->qty_five_six ? $article_production_data->qty_five_six : '' ?>"
                                                        id="qty_five_six_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_five_six_remark_<?= $article->id ?>"
                                                        id="qty_five_six_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_five_six_remark) ? $article_production_data->qty_five_six_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_six_seven_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_six_seven) && $article_production_data->qty_six_seven ? $article_production_data->qty_six_seven : '' ?>"
                                                        id="qty_six_seven_<?= $article->id ?>">
                                                    <input type="hidden" name="qty_six_seven_remark_<?= $article->id ?>"
                                                        id="qty_six_seven_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_six_seven_remark) ? $article_production_data->qty_six_seven_remark : '' ?>">
                                                </td>

                                                <td>
                                                    <input onkeyup="updateTotals(this); check_input(this)"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="qty_seven_eight_<?= $article->id ?>"
                                                        id="qty_seven_eight_<?= $article->id ?>"
                                                        value="<?= (isset($article_production_data->qty_seven_eight) && $article_production_data->qty_seven_eight) ? $article_production_data->qty_seven_eight : '' ?>">
                                                    <input type="hidden" name="qty_seven_eight_remark_<?= $article->id ?>"
                                                        id="qty_seven_eight_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->qty_seven_eight_remark) ? $article_production_data->qty_seven_eight_remark : '' ?>">
                                                </td>

                                                <td rowspan="2">

                                                    <?php
                                                    $selected_status = '';
                                                    if (
                                                        isset($article_production_data->status)
                                                        && ($article_production_data->status === '0' || $article_production_data->status === '1')
                                                    ) {
                                                        $selected_status = $article_production_data->status;
                                                    }
                                                    ?>
                                                    <select name="status_<?= $article->id ?>"
                                                        id="status_<?= $article->id ?>" class="form-control status-select">
                                                        <option value="" <?= $selected_status === '' ? 'selected' : '' ?>>
                                                            Select the Status
                                                        </option>
                                                        <option value="0" <?= $selected_status === '0' ? 'selected' : '' ?>>
                                                            Approve
                                                        </option>
                                                        <option value="1" <?= $selected_status === '1' ? 'selected' : '' ?>>
                                                            Not Approve
                                                        </option>
                                                    </select>
                                                </td>
                                                <td rowspan="2">
                                                    <textarea name="remark_<?= $article->id ?>"
                                                        id="remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->remark) ? $article_production_data->remark : '' ?>"
                                                        class="form-control"><?= $article_production_data->remark ?></textarea>

                                                </td>
                                                <td rowspan="2">
                                                    <input type="hidden" name="article_id[]" id="article_id"
                                                        value="<?= $article->id ?>">
                                                    <button class="btn btn-primary table_btn hourly-submit"
                                                        onclick="set_article_production_details(<?= $article->id ?>);">
                                                        Save
                                                    </button>
                                                </td>


                                                <td rowspan="2">
                                                    <input type="hidden" name="article_id[]" class="article-id"
                                                        value="<?= $article->id ?>">
                                                    <button class="btn btn-secondary table_btn" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal">
                                                        Log
                                                    </button>
                                                </td>


                                            </tr>
                                            <tr>
                                                <td>Weight Per Hour</td>
                                                <td><input onkeyup="updateTotals(this);"
                                                        onfocusout="remark_popup(this, true)" type="text"
                                                        name="weight_eight_nine_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eight_nine) ? $article_production_data->weight_eight_nine : '' ?>"
                                                        id="weight_eight_nine_<?= $article->id ?>">
                                                    <input type="hidden" name="weight_eight_nine_remark_<?= $article->id ?>"
                                                        id="weight_eight_nine_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eight_nine_remark) ? $article_production_data->weight_eight_nine_remark : '' ?>">
                                                </td>

                                                <td><input onkeyup="updateTotals(this);"
                                                        onfocusout="remark_popup(this, true)"
                                                        value="<?= isset($article_production_data->weight_nine_ten) ? $article_production_data->weight_nine_ten : '' ?>"
                                                        type="text" name="weight_nine_ten_<?= $article->id ?>"
                                                        id="weight_nine_ten_<?= $article->id ?>">
                                                    <input type="hidden" name="weight_nine_ten_remark_<?= $article->id ?>"
                                                        id="weight_nine_ten_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_nine_ten_remark) ? $article_production_data->weight_nine_ten_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_ten_eleven_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_ten_eleven) ? $article_production_data->weight_ten_eleven : '' ?>"
                                                        id="weight_ten_eleven_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_ten_eleven_remark_<?= $article->id ?>"
                                                        id="weight_ten_eleven_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_ten_eleven_remark) ? $article_production_data->weight_ten_eleven_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_eleven_twelve_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eleven_twelve) ? $article_production_data->weight_eleven_twelve : '' ?>"
                                                        id="weight_eleven_twelve_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_eleven_twelve_remark_<?= $article->id ?>"
                                                        id="weight_eleven_twelve_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eleven_twelve_remark) ? $article_production_data->weight_eleven_twelve_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_twelve_thirteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twelve_thirteen) ? $article_production_data->weight_twelve_thirteen : '' ?>"
                                                        id="weight_twelve_thirteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_twelve_thirteen_remark_<?= $article->id ?>"
                                                        id="weight_twelve_thirteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twelve_thirteen_remark) ? $article_production_data->weight_twelve_thirteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_thirteen_fourteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_thirteen_fourteen) ? $article_production_data->weight_thirteen_fourteen : '' ?>"
                                                        id="weight_thirteen_fourteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_thirteen_fourteen_remark_<?= $article->id ?>"
                                                        id="weight_thirteen_fourteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_thirteen_fourteen_remark) ? $article_production_data->weight_thirteen_fourteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_fourteen_fifteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_fourteen_fifteen) ? $article_production_data->weight_fourteen_fifteen : '' ?>"
                                                        id="weight_fourteen_fifteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_fourteen_fifteen_remark_<?= $article->id ?>"
                                                        id="weight_fourteen_fifteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_fourteen_fifteen_remark) ? $article_production_data->weight_fourteen_fifteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this); " type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_fifteen_sixteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_fifteen_sixteen) ? $article_production_data->weight_fifteen_sixteen : '' ?>"
                                                        id="weight_fifteen_sixteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_fifteen_sixteen_remark_<?= $article->id ?>"
                                                        id="weight_fifteen_sixteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_fifteen_sixteen_remark) ? $article_production_data->weight_fifteen_sixteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this); " type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_sixteen_seventeen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_sixteen_seventeen) ? $article_production_data->weight_sixteen_seventeen : '' ?>"
                                                        id="weight_sixteen_seventeen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_sixteen_seventeen_remark_<?= $article->id ?>"
                                                        id="weight_sixteen_seventeen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_sixteen_seventeen_remark) ? $article_production_data->weight_sixteen_seventeen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this); " type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_seventeen_eighteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_seventeen_eighteen) ? $article_production_data->weight_seventeen_eighteen : '' ?>"
                                                        id="weight_seventeen_eighteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_seventeen_eighteen_remark_<?= $article->id ?>"
                                                        id="weight_seventeen_eighteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_seventeen_eighteen_remark) ? $article_production_data->weight_seventeen_eighteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_eighteen_nineteen_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eighteen_nineteen) ? $article_production_data->weight_eighteen_nineteen : '' ?>"
                                                        id="weight_eighteen_nineteen_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_eighteen_nineteen_remark_<?= $article->id ?>"
                                                        id="weight_eighteen_nineteen_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_eighteen_nineteen_remark) ? $article_production_data->weight_eighteen_nineteen_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_nineteen_twenty_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_nineteen_twenty) ? $article_production_data->weight_nineteen_twenty : '' ?>"
                                                        id="weight_nineteen_twenty_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_nineteen_twenty_remark_<?= $article->id ?>"
                                                        id="weight_nineteen_twenty_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_nineteen_twenty_remark) ? $article_production_data->weight_nineteen_twenty_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_twenty_twentyone_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twenty_twentyone) ? $article_production_data->weight_twenty_twentyone : '' ?>"
                                                        id="weight_twenty_twentyone_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_twenty_twentyone_remark_<?= $article->id ?>"
                                                        id="weight_twenty_twentyone_remark<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twenty_twentyone_remark) ? $article_production_data->weight_twenty_twentyone_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_twentyone_twentytwo_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentyone_twentytwo) ? $article_production_data->weight_twentyone_twentytwo : '' ?>"
                                                        id="weight_twentyone_twentytwo_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_twentyone_twentytwo_remark_<?= $article->id ?>"
                                                        id="weight_twentyone_twentytwo_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentyone_twentytwo_remark) ? $article_production_data->weight_twentyone_twentytwo_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_twentytwo_twentythree_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentytwo_twentythree) ? $article_production_data->weight_twentytwo_twentythree : '' ?>"
                                                        id="weight_twentytwo_twentythree_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_twentytwo_twentythree_remark_<?= $article->id ?>"
                                                        id="weight_twentytwo_twentythree_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentytwo_twentythree_remark) ? $article_production_data->weight_twentytwo_twentythree_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_twentythree_zero_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentythree_zero) ? $article_production_data->weight_twentythree_zero : '' ?>"
                                                        id="weight_twentythree_zero_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_twentythree_zero_remark_<?= $article->id ?>"
                                                        id="weight_twentythree_zero_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_twentythree_zero_remark) ? $article_production_data->weight_twentythree_zero_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_zero_one_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_zero_one) ? $article_production_data->weight_zero_one : '' ?>"
                                                        id="weight_zero_one_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_zero_one_remark_<?= $article->id ?>"
                                                        id="weight_zero_one_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_zero_one_remark) ? $article_production_data->weight_zero_one_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_one_two_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_one_two) ? $article_production_data->weight_one_two : '' ?>"
                                                        id="weight_one_two_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_one_two_remark_<?= $article->id ?>"
                                                        id="weight_one_two_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_one_two_remark) ? $article_production_data->weight_one_two_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_two_three_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_two_three) ? $article_production_data->weight_two_three : '' ?>"
                                                        id="weight_two_three_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_two_three_remark_<?= $article->id ?>"
                                                        id="weight_two_three_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_two_three_remark) ? $article_production_data->weight_two_three_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_three_four_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_three_four) ? $article_production_data->weight_three_four : '' ?>"
                                                        id="weight_three_four_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_three_four_remark_<?= $article->id ?>"
                                                        id="weight_three_four_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_three_four_remark) ? $article_production_data->weight_three_four_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_four_five_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_four_five) ? $article_production_data->weight_four_five : '' ?>"
                                                        id="weight_four_five_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_four_five_remark_<?= $article->id ?>"
                                                        id="weight_four_five_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_four_five_remark) ? $article_production_data->weight_four_five_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_five_six_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_five_six) ? $article_production_data->weight_five_six : '' ?>"
                                                        id="weight_five_six_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_five_six_remark_<?= $article->id ?>"
                                                        id="weight_five_six_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_five_six_remark) ? $article_production_data->weight_five_six_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this);" type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_six_seven_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_six_seven) ? $article_production_data->weight_six_seven : '' ?>"
                                                        id="weight_six_seven_<?= $article->id ?>" />
                                                    <input type="hidden" name="weight_six_seven_remark_<?= $article->id ?>"
                                                        id="weight_six_seven_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_six_seven_remark) ? $article_production_data->weight_six_seven_remark : '' ?>">
                                                </td>
                                                <td>
                                                    <input onkeyup="updateTotals(this); " type="text"
                                                        onfocusout="remark_popup(this, true)"
                                                        name="weight_seven_eight_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_seven_eight) ? $article_production_data->weight_seven_eight : '' ?>"
                                                        id="weight_seven_eight_<?= $article->id ?>" />
                                                    <input type="hidden"
                                                        name="weight_seven_eight_remark_<?= $article->id ?>"
                                                        id="weight_seven_eight_remark_<?= $article->id ?>"
                                                        value="<?= isset($article_production_data->weight_seven_eight_remark) ? $article_production_data->weight_seven_eight_remark : '' ?>">
                                                </td>

                                            </tr>
                                        </row>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Raw Material  -->
    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Raw Material List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="data8-9able">
                                <thead class="thead">
                                    <tr>
                                        <!-- <th>SR NO.</th> -->
                                        <th>Raw Material</th>
                                        <th>Associated Article</th>
                                        <th>Total Qty (In KG with 3 decimal places)</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>Action</th>
                                        <!-- #region -->
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">

                                <tbody>
                                    <?php foreach ($raw_material_data as $raw_material): ?>
                                        <?php
                                        $article_ids = explode(',', $raw_material->article_id);
                                        $articleCount = count($article_ids);
                                        $firstArticle = true;
                                        ?>
                                        <?php foreach ($article_ids as $article_id): ?>
                                            <?php
                                            $article_name = $this->Admin_model->get_article_name_by_id($article_id);
                                            $raw_material_production_data = $this->Admin_model->get_raw_material_production_details($this->uri->segment(2), $raw_material->id, $article_id);

                                            ?>
                                            <tr>
                                                <?php if ($firstArticle): ?>
                                                    <td rowspan="<?= $articleCount ?>"><?= $raw_material->rm_name ?></td>
                                                    <?php $firstArticle = false; ?>
                                                <?php endif; ?>
                                                <td><?= $article_name ?></td>
                                                <td>
                                                    <input type="text"
                                                        id="row_total_qty_<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        value="<?= isset($raw_material_production_data->total_qty) ? $raw_material_production_data->total_qty : '' ?>">

                                                </td>


                                                <td>
                                                    <select
                                                        name="plant_manager_approval_status<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        id="plant_manager_approval_status<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        class="form-control status-select">
                                                        <?php
                                                        $selected_status = '';
                                                        if (isset($raw_material_production_data->plant_manager_approval_status)) {
                                                            if ($raw_material_production_data->plant_manager_approval_status === "0" || $raw_material_production_data->plant_manager_approval_status === "1") {
                                                                $selected_status = $raw_material_production_data->plant_manager_approval_status;
                                                            }
                                                        }
                                                        ?>
                                                        <option value="" disabled <?= ($selected_status === '') ? 'selected' : '' ?>>
                                                            Select the Status
                                                        </option>
                                                        <option value="0" <?= ($selected_status === "0") ? 'selected' : '' ?>>
                                                            Approve
                                                        </option>
                                                        <option value="1" <?= ($selected_status === "1") ? 'selected' : '' ?>>
                                                            Not Approve
                                                        </option>
                                                    </select>
                                                    <span class="error-message" style="display:none; color:red;"></span>
                                                </td>
                                                <td>
                                                    <!-- <input type="text" id="remark_<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        name="remark" class="last-submit"
                                                        value="<?= isset($raw_material_production_data->remark) ? $raw_material_production_data->remark : '' ?>"> -->

                                                    <textarea name="remark"
                                                        id="remark_<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        value="<?= isset($raw_material_production_data->remark) ? $raw_material_production_data->remark : '' ?>"
                                                        class="form-control"><?= $raw_material_production_data->remark ?>
                                                                                                                                                                    </textarea>

                                                </td>
                                                <td class="inline-btns">
                                                    <input type="hidden" name="raw_material_id[]"
                                                        value="<?= $raw_material->id ?>">
                                                    <input type="hidden" name="article_id[]"
                                                        id="article_id_<?= $raw_material->id ?>_<?= $article_id ?>"
                                                        value="<?= $article_id ?>">
                                                    <button class="btn btn-primary table_btn"
                                                        onclick="set_raw_material_production_details(<?= $raw_material->id ?>, <?= $article_id ?>);">
                                                        Save
                                                    </button>


                                                    <button class="btn btn-secondary table_btn check-logs-rm"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal1"
                                                        name="check_logs_rm" id="check_logs_rm">Log</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="display: none;">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td id="total_rm_qty"></td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Batch  -->
    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Master Batch List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="data8-9able">
                                <thead class="thead">
                                    <tr>
                                        <!-- <th>SR NO.</th> -->
                                        <th>Master Batch</th>
                                        <th>Associated Article</th>
                                        <th>Total Qty (In KG with 3 decimal places)</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">
                                <input type="hidden" name="machine_id" id="machine_id"
                                    value="<?= $machine->machine_id ?>">
                                <tbody>
                                    <?php foreach ($master_batch_data as $master_batch): ?>
                                        <?php

                                        $article_ids = explode(',', $master_batch->article_id);
                                        $articleCount = count($article_ids);
                                        $firstArticle = true;
                                        ?>
                                        <?php foreach ($article_ids as $article_id): ?>
                                            <?php
                                            $article_name = $this->Admin_model->get_article_name_by_id($article_id);
                                            $master_batch_production_data = $this->Admin_model->get_master_batch_production_details($this->uri->segment(2), $master_batch->id, $article_id);
                                            ?>
                                            <tr>
                                                <?php if ($firstArticle): ?>

                                                    <td rowspan="<?= $articleCount ?>"><?= $master_batch->name ?></td>
                                                    <?php $firstArticle = false; ?>
                                                <?php endif; ?>
                                                <td><?= $article_name ?></td>
                                                <td>
                                                    <input type="text"
                                                        id="row_total_qty_mb<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        value="<?= isset($master_batch_production_data->total_qty) ? $master_batch_production_data->total_qty : '' ?>">

                                                </td>

                                                <td>
                                                    <select
                                                        name="plant_manager_approval_status_<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        id="plant_manager_approval_status_mb<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        class="form-control status-select">
                                                        <?php
                                                        $selected_status = '';
                                                        if (isset($master_batch_production_data->plant_manager_approval_status)) {
                                                            if ($master_batch_production_data->plant_manager_approval_status === "0" || $master_batch_production_data->plant_manager_approval_status === "1") {
                                                                $selected_status = $master_batch_production_data->plant_manager_approval_status;
                                                            }
                                                        }
                                                        ?>
                                                        <option value="" <?= ($selected_status === '') ? 'selected' : '' ?>>
                                                            Select the Status
                                                        </option>
                                                        <option value="0" <?= ($selected_status === "0") ? 'selected' : '' ?>>
                                                            Approve
                                                        </option>
                                                        <option value="1" <?= ($selected_status === "1") ? 'selected' : '' ?>>
                                                            Not Approve
                                                        </option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <!-- <input type="text" id="remark_mb<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        name="remark" class="last-submit"
                                                        value="<?= isset($master_batch_production_data->remark) ? $master_batch_production_data->remark : '' ?>"> -->

                                                    <textarea name="remark"
                                                        id="remark_mb<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        value="<?= isset($master_batch_production_data->remark) ? $master_batch_production_data->remark : '' ?>"
                                                        class="form-control"><?= $master_batch_production_data->remark ?>
                                                                                                                                                                    </textarea>

                                                </td>

                                                <td class="inline-btns">
                                                    <input type="hidden" name="master_batch_id[]"
                                                        value="<?= $master_batch->id ?>">
                                                    <input type="hidden" name="article_id[]"
                                                        id="article_id_<?= $master_batch->id ?>_<?= $article_id ?>"
                                                        value="<?= $article_id ?>">
                                                    <button class="btn btn-primary table_btn"
                                                        onclick="set_master_batch_production_details(<?= $master_batch->id ?>, <?= $article_id ?>);">
                                                        Save
                                                    </button>

                                                    <button class="btn btn-secondary table_btn" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal2" name="check_logs_mb"
                                                        id="check_logs_mb">Log</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Raw Material -->
    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Rejection Raw Material</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="data8-9able">
                                <thead class="thead">
                                    <tr>
                                        <!-- <th>SR NO.</th> -->
                                        <th>Rejection- Raw Material</th>
                                        <th>Total Qty</th>
                                        <th>Pc</th>
                                        <th>Runner- gms</th>
                                        <th>Flash-gm</th>
                                        <th>Lumps-gm</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">
                                <tbody>
                                    <?php foreach ($rejection_data as $rejection): ?>
                                        <?php
                                        $rejection_production_data = $this->Admin_model->get_rejection_production_details($this->uri->segment(2), $rejection->id);

                                        ?>
                                        <tr>
                                            <td><?= $rejection->rm_name ?></td>
                                            <td>
                                                <input type="text" id="total_qty_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->total_qty) ? $rejection_production_data->total_qty : '' ?>">
                                            </td>
                                            <td>
                                                <input type="text" id="pc_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->pc) ? $rejection_production_data->pc : '' ?>">
                                            </td>
                                            <td>
                                                <input type="text" id="runner_gms_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->runner_gms) ? $rejection_production_data->runner_gms : '' ?>">
                                            </td>
                                            <td>
                                                <input type="text" id="flash_gm_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->flash_gm) ? $rejection_production_data->flash_gm : '' ?>">
                                            </td>
                                            <td>
                                                <input type="text" id="lumps_gm_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->lumps_gm) ? $rejection_production_data->lumps_gm : '' ?>">
                                            </td>

                                            <td>
                                                <select name="plant_manager_approval_status_<?= $rejection->id ?>"
                                                    id="plant_manager_approval_status_<?= $rejection->id ?>"
                                                    class="form-control status-select">
                                                    <?php
                                                    $selected_status = '';
                                                    if (isset($rejection_production_data->plant_manager_approval_status)) {
                                                        if ($rejection_production_data->plant_manager_approval_status === "0" || $rejection_production_data->plant_manager_approval_status === "1") {
                                                            $selected_status = $rejection_production_data->plant_manager_approval_status;
                                                        }
                                                    }
                                                    ?>
                                                    <option value="" <?= ($selected_status === '') ? 'selected' : '' ?>>
                                                        Select the Status
                                                    </option>
                                                    <option value="0" <?= ($selected_status === "0") ? 'selected' : '' ?>>
                                                        Approve
                                                    </option>
                                                    <option value="1" <?= ($selected_status === "1") ? 'selected' : '' ?>>
                                                        Not Approve
                                                    </option>
                                                </select>
                                            </td>

                                            <td>
                                                <!-- <input type="text" id="remark_<?= $rejection->id ?>" name="remark" class="last-submit"
                                                    value="<?= isset($rejection_production_data->remark) ? $rejection_production_data->remark : '' ?>"> -->

                                                <textarea name="remark" id="remark_<?= $rejection->id ?>"
                                                    value="<?= isset($rejection_production_data->remark) ? $rejection_production_data->remark : '' ?>"
                                                    class="form-control"><?= $rejection_production_data->remark ?>
                                                                                                        </textarea>
                                            </td>

                                            <td class="inline-btns">
                                                <input type="hidden" name="rejection_id[]" value="<?= $rejection->id ?>">
                                                <button class="btn btn-primary table_btn"
                                                    onclick="set_rejection_production_details(<?= $rejection->id ?>);">
                                                    Save
                                                </button>

                                                <button class="btn btn-secondary table_btn" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal3" name="check_logs_reject"
                                                    id="check_logs_reject">Log</button>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>

                                </tbody>

                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Quantity modified  -->

    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Balance Quantity</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="data-table">
                                <thead class="thead">
                                    <tr>
                                        <th>Raw Materials</th>
                                        <th>Balance Qty (In KG)</th>
                                        <th>Master Batch</th>
                                        <th>Balance Qty (In KG)</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">
                                <tbody>
                                    <?php
                                    $maxCount = max(count($raw_material_data), count($master_batch_data));

                                    for ($i = 0; $i < $maxCount; $i++):
                                        $raw_material = isset($raw_material_data[$i]) ? $raw_material_data[$i] : '';
                                        $master_batch = isset($master_batch_data[$i]) ? $master_batch_data[$i] : '';

                                        $raw_material_id = $raw_material ? $raw_material->id : '';
                                        $master_batch_id = $master_batch ? $master_batch->id : '';
                                        $balance_data = $this->Admin_model->get_balance_quantity_details($this->uri->segment(2), $raw_material_id, $master_batch_id);
                                        ?>
                                        <tr>
                                            <td><?= !empty($raw_material) ? $raw_material->rm_name : '' ?></td>

                                            <td>
                                                <?php if (!empty($raw_material)): ?>
                                                    <input type="text" id="rm_total_qty_<?= $raw_material->id ?>"
                                                        value="<?= isset($balance_data->rm_total_qty) ? $balance_data->rm_total_qty : '' ?>"
                                                        class="form-control">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= !empty($master_batch) ? $master_batch->name : '' ?></td>
                                            <td>
                                                <?php if (!empty($master_batch)): ?>
                                                    <input type="text" id="mb_total_qty_<?= $master_batch->id ?>"
                                                        value="<?= isset($balance_data->mb_total_qty) ? $balance_data->mb_total_qty : '' ?>"
                                                        class="form-control">
                                                <?php endif; ?>
                                            </td>


                                            <td>
                                                <?php if (!empty($raw_material) || !empty($master_batch)): ?>
                                                    <?php
                                                    $selected_status = '';
                                                    if (isset($balance_data->plant_manager_approval_status)) {
                                                        if ($balance_data->plant_manager_approval_status === "0" || $balance_data->plant_manager_approval_status === "1") {
                                                            $selected_status = $balance_data->plant_manager_approval_status;
                                                        }
                                                    }
                                                    ?>
                                                    <select
                                                        id="plant_manager_approval_status_<?= !empty($master_batch->id) ? $master_batch->id : 'null' ?>_<?= !empty($raw_material->id) ? $raw_material->id : 'null' ?>"
                                                        class="form-control status-select">
                                                        <option value="" <?= ($selected_status === '') ? 'selected' : '' ?>>
                                                            Select the Status
                                                        </option>
                                                        <option value="0" <?= ($selected_status === "0") ? 'selected' : '' ?>>
                                                            Approve
                                                        </option>
                                                        <option value="1" <?= ($selected_status === "1") ? 'selected' : '' ?>>
                                                            Not Approve
                                                        </option>
                                                    </select>
                                                <?php endif; ?>
                                            </td>


                                            <td>
                                                <?php if (!empty($raw_material) || !empty($master_batch)): ?>
                                                    <!-- <input type="text"
                                                        id="remark_<?= !empty($master_batch->id) ? $master_batch->id : 'null' ?>_<?= !empty($raw_material->id) ? $raw_material->id : 'null' ?>"
                                                        value="<?= isset($balance_data->remark) ? $balance_data->remark : '' ?>"
                                                        name="remark" class="form-control last-submit"> -->

                                                    <textarea name="remark"
                                                        id="remark_<?= !empty($master_batch->id) ? $master_batch->id : 'null' ?>_<?= !empty($raw_material->id) ? $raw_material->id : 'null' ?>"
                                                        value="<?= isset($balance_data->remark) ? $balance_data->remark : '' ?>"
                                                        class="form-control"><?= $balance_data->remark ?>
                                                                                                                                                                    </textarea>

                                                <?php endif; ?>
                                            </td>
                                            <td class="inline-btns">
                                                <input type="hidden" name="raw_material_id[]"
                                                    value="<?= $raw_material->id ?>">
                                                <input type="hidden" name="master_batch_id[]"
                                                    value="<?= $master_batch->id ?>">
                                                <?php if (!empty($master_batch->id) || !empty($raw_material->id)): ?>
                                                    <button class="btn btn-primary table_btn"
                                                        onclick="set_balance_quantity_production_details(<?= !empty($master_batch->id) ? $master_batch->id : 'null' ?>, 
                                                                                         <?= !empty($raw_material->id) ? $raw_material->id : '' ?>);">
                                                        Save
                                                    </button>
                                                    <button class="btn btn-secondary table_btn" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal4" name="check_logs_balance"
                                                        id="check_logs_balance">
                                                        Log
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                    <?php endfor; ?>

                                    <?php if ($maxCount == 0): ?>
                                        <tr>
                                            <td colspan="7">No data available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <!-- <tfoot style="display: none;">
                                    <tr>
                                        <td></td>
                                        <td id="total_rm_qty"></td>
                                        <td></td>
                                        <td id="total_mb_qty"></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tfoot> -->
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="table1">
        <div class="page-title">
            <div class="title_left">
                <h3>Summary</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <form method="post" name="production_form_list" id="production_form_list"
                        class="production_form_list" enctype="multipart/form-data">
                        <div class="container overflow_x">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="data8-9able">
                                <thead class="thead">
                                    <tr>
                                        <th>Article Name</th>
                                        <th>Approved qty</th>
                                        <th>Average weight</th>
                                        <th>Total Weight</th>
                                        <th>Remarks</th>
                                        <th>Delta</th>
                                    </tr>
                                </thead>
                                <input type="hidden" id="production_id" name="production_id"
                                    value="<?= $prodution_id ?>">
                                <tbody>
                                    <?php
                                    $count = 0;
                                    foreach ($article_data as $article) {
                                        $article_production_data = $this->Admin_model->get_article_production_detail($this->uri->segment(2), $article->id);
                                        $balance_data = $this->Admin_model->get_balance_quantity_details($this->uri->segment(2), $raw_material->id, $master_batch->id);
                                        $summary_data = $this->Admin_model->get_production_summary($this->uri->segment(2), $article_id);

                                        ?>
                                        <tr data-article-id="<?= $article->id ?>">
                                            <td id="article_name_<?= $article->id ?>"><?= $article->article_name ?></td>
                                            <td id="approved_qty_<?= $article->id ?>" class="approved_qty">
                                                <!-- <?= (int) ($article_production_data->approved_qty ?? 0) ?> -->
                                                <?= number_format((float) ($article_production_data->approved_qty ?? 0), 3) ?>
                                            </td>
                                            <td id="average_qty_<?= $article->id ?>" class="average_qty">
                                                <?= number_format((float) ($article_production_data->average_qty ?? 0), 3) ?>
                                            </td>

                                            <?php
                                            $approved_qty = (float) ($article_production_data->approved_qty ?? 0);
                                            $average_qty = (float) ($article_production_data->average_qty ?? 0);

                                            $approved_qty_rounded = round($approved_qty, 3);
                                            $average_qty_rounded = round($average_qty, 3);

                                            $product = $approved_qty_rounded * $average_qty_rounded;


                                            // echo "Debug - approved_qty: " . $approved_qty . "<br>";
                                            // echo "Debug - average_qty (original): " . $average_qty . "<br>";
                                            // echo "Debug - approved_qty (rounded): " . $approved_qty_rounded . "<br>";
                                            // echo "Debug - average_qty (rounded): " . $average_qty_rounded . "<br>";
                                            // echo "Debug - product (before formatting): " . $product . "<br>";
                                            ?>
                                            <td id="total_weight_<?= $article->id ?>" class="total_weight">
                                                <?= number_format($product, 3) ?>
                                            </td>
                                            <td>
                                                <!-- <input type="text" id="remark_<?= $article->id ?>" class="last-submit"
                                                    name="remark_<?= $article->id ?>"
                                                    value="<?= isset($summary_data->remark) ? $summary_data->remark : '' ?>"> -->

                                                <textarea name="remark_<?= $article->id ?>" id="remark_<?= $article->id ?>"
                                                    value="<?= isset($summary_data->remark) ? $summary_data->remark : '' ?>"
                                                    class="form-control"><?= $summary_data->remark ?>
                                                                                                            </textarea>
                                            </td>
                                            <input type="hidden" name="raw_material_id[]" value="<?= $raw_material->id ?>">
                                            <input type="hidden" name="master_batch_id[]" value="<?= $master_batch->id ?>">
                                            <input type="hidden" name="article_id[]" id="article_id_<?= $article->id ?>"
                                                value="<?= $article->id ?>">
                                            <?php if ($count == 0) { ?>
                                                <td rowspan="100%" id="delta" class="delta"></td>
                                            <?php } ?>
                                        </tr>
                                        <?php
                                        $count++;
                                    }
                                    ?>
                                </tbody>


                            </table>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- images -->

    <form id="production_form_list" name="production_form_list" class="production_form_list"
        enctype="multipart/form-data">
        <div class="row btm_section">
            <div class="form-group col-md-3">
                <label>Upload Multiple Images <span class="text-danger">*</span></label>
                <input type="hidden" id="production_id" name="production_id" value="<?= $prodution_id ?>">
                <div class="input-group">
                    <input type="file" name="image_names[]" accept="image/*" id="image_names" multiple
                        class="form-control">

                    <button type="button" class="btn btn-info"
                        onclick="view_uploaded_images($('#production_id').val())">
                        <i class="fa fa-eye"></i> View
                    </button>

                </div>
                <div id="image_error" class="text-danger mt-1"></div>
            </div>
        </div>
    </form>

    <!-- Modal to Display Images -->
    <div class="modal fade" id="viewImagesModal" tabindex="-1" aria-labelledby="viewImagesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImagesModalLabel">Uploaded Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="imageGallery" class="row">
                        <div class="col-12 text-center" id="loadingSpinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Loading images...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="col-lg-12 col-md-12">
        <div class="form-group">
            <input type="hidden" id="production_id" name="production_id" value="<?= $prodution_id ?>">
            <label for="remarks_textarea">Remark(If Any)</label>
            <textarea id="remarks_textarea" name="remarks"
                class="form-control"><?= isset($existing_remarks) ? $existing_remarks : '' ?></textarea>
        </div>
    </div>

    <div class="col-lg-12">
        <!-- <button type="submit" name="submit_btn" value="submit" class="btn btn-primary" onclick="set_all_article_summary_details(); set_production_remarks(); set_production_images();">Submit</button> -->
        <button type="button" name="submit_btn" value="submit" class="btn btn-primary"
            onclick="set_all_article_summary_details(); set_production_remarks();set_production_images(); validateForm();">
            Submit
        </button>
    </div>
</div>
</div>




</div>



<!-- Article Logs -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Article Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container overflow_x">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="logDataTable">
                        <thead class="thead">
                            <tr>
                                <th rowspan="2">SR NO.</th>
                                <th rowspan="2">Article Name</th>
                                <th rowspan="2">Approved qty</th>
                                <th rowspan="2">Average weight</th>
                                <th rowspan="2"></th>
                                <th class="time">8-9</th>
                                <th class="time">9-10</th>
                                <th class="time">10-11</th>
                                <th class="time">11-12</th>
                                <th class="time">12-13</th>
                                <th class="time">13-14</th>
                                <th class="time">14-15</th>
                                <th class="time">15-16</th>
                                <th class="time">16-17</th>
                                <th class="time">17-18</th>
                                <th class="time">18-19</th>
                                <th class="time">19-20</th>
                                <th class="time">20-21</th>
                                <th class="time">21-22</th>
                                <th class="time">22-23</th>
                                <th class="time">23-00</th>
                                <th class="time">00-01</th>
                                <th class="time">01-02</th>
                                <th class="time">02-03</th>
                                <th class="time">03-04</th>
                                <th class="time">04-05</th>
                                <th class="time">05-06</th>
                                <th class="time">06-07</th>
                                <th class="time">07-08</th>
                                <th>Plant Manager Approval Status</th>
                                <th>Remark of Plant Manager</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Raw Material Logs -->
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Raw Material Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container overflow_x">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="logDataTable">
                        <thead class="thead">
                            <tr>
                                <th>Raw Material</th>
                                <th>Associated Article</th>
                                <th>Total Qty (In KG with 2 decimal places)</th>
                                <th>Plant Manager Approval Status</th>
                                <th>Remark of Plant Manager</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Master Batch -->

<div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Master Batch Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container overflow_x">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="logDataTable">
                        <thead class="thead">
                            <tr>
                                <th>Master Batch</th>
                                <th>Associated Article</th>
                                <th>Total Qty (In KG with 2 decimal places)</th>
                                <th>Plant Manager Approval Status</th>
                                <th>Remark of Plant Manager</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--rejection-->
<div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejection Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container overflow_x">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="logDataTable">
                        <thead class="thead">
                            <tr>
                                <th>Rejection- Raw Material</th>
                                <th>Total Qty (In gms)</th>
                                <th>Pc</th>
                                <th>Runner- gms</th>
                                <th>Flash-gm</th>
                                <th>Lumps-gm</th>
                                <th>Plant Manager Approval Status</th>
                                <th>Remark of Plant Manager</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- balance quantity -->

<div class="modal fade" id="exampleModal4" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Balance Quantity Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container overflow_x">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="logDataTable">
                        <thead class="thead">
                            <tr>
                                <th>Raw Materials</th>
                                <th>Balance Qty (In KG)</th>
                                <th>Master Batch</th>
                                <th>Balance Qty (In KG)</th>
                                <th>Plant Manager Approval Status</th>
                                <th>Remark of Plant Manager</th>
                            </tr>

                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- pop up for article less than 30 -->

<div class="modal fade" id="add_remark" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Remark</h5>
                <button type="button" id="closeRemarkBtn" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="remark" class="form-control" rows="3" placeholder="Enter your remark here…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" id="submitForm" class="btn btn-primary save_remark" disabled>
                    Save Remark
                </button>
            </div>
        </div>
    </div>
</div>



<?php include('footer.php'); ?>

<script>
    function view_uploaded_images(production_id) {
        if (!production_id) {
            alert("No production ID provided");
            return;
        }

        $('#loadingSpinner').show();
        $('#imageGallery').empty();

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/get_production_images",
            type: "POST",
            data: { production_id },
            success: function (response) {
                $('#loadingSpinner').hide();

                let json;
                try {
                    json = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    $('#imageGallery').html('<p class="text-danger">Invalid server response.</p>');
                    return $('#viewImagesModal').modal('show');
                }

                if (json.status === "success" && json.data.length) {
                    const galleryHtml = json.data.map(img =>
                        `<div class="col-md-4 mb-3">
                            <img 
                            class="img-fluid rounded"
                            style="max-height:200px"
                            data-src="<?= base_url('assets/images/production/') ?>${img.image_names}"
                            >
                        </div>`
                    ).join('');

                    $('#imageGallery').html(galleryHtml);

                    // Handle missing images in JS
                    $('#imageGallery img').each(function () {
                        this.onerror = function () {
                            this.onerror = null; // prevent infinite loop
                            this.src = '<?= base_url('assets/images/no-image.png') ?>';
                        };
                        this.src = $(this).data('src'); // trigger image load
                    });

                } else {
                    $('#imageGallery').html('<p>No images found for this production.</p>');
                }

                $('#viewImagesModal').modal('show');
            },
            error: function (xhr, status, error) {
                $('#loadingSpinner').hide();
                console.error("AJAX Error:", status, error);
                $('#imageGallery').html('<p class="text-danger">Failed to load images.</p>');
                $('#viewImagesModal').modal('show');
            }
        });
    }
</script>
<script>
    $(document).ready(function () {
        $('.status-select').on('change', function () {
            var $select = $(this);
            var id = $select.attr('id');
            var value = $select.val();

            var isApproved = value === "0"; // Approve
            var isNotApproved = value === "1"; // Not Approve

            // ================== ARTICLE ==================
            if (/^status_\d+$/.test(id)) {
                var articleId = id.split('_')[1];

                var $inputs = $([
                    'input[id^="qty_"][id$="_' + articleId + '"]',
                    'input[id^="weight_"][id$="_' + articleId + '"]'
                ].join(','));

                var $rm_inputs = $('input[id^="row_total_qty_"][id$="_' + articleId + '"]');

                if (isApproved) {
                    $inputs.prop('readonly', true);
                    $rm_inputs.prop('readonly', true);
                } else {
                    $inputs.prop('readonly', false);
                    $rm_inputs.prop('readonly', false);
                }
            }

            // ================== RAW MATERIAL ==================
            if (/^plant_manager_approval_status\d+_\d+$/.test(id)) {
                var parts = id.replace("plant_manager_approval_status", "").split("_");
                var rmId = parts[0];
                var articleId = parts[1];

                var $rm_input = $('#row_total_qty_' + rmId + '_' + articleId);

                if (isApproved) {
                    $rm_input.prop('readonly', true);
                } else {
                    $rm_input.prop('readonly', false);
                }
            }

            // ================== MASTER BATCH ==================
            if (/^plant_manager_approval_status_mb\d+_\d+$/.test(id)) {
                var parts = id.replace("plant_manager_approval_status_mb", "").split("_");
                var mbId = parts[0];
                var articleId = parts[1];

                var $mb_input = $('#row_total_qty_mb' + mbId + '_' + articleId);

                if (isApproved) {
                    $mb_input.prop('readonly', true);
                } else {
                    $mb_input.prop('readonly', false);
                }
            }
            // ================== REJECTION RAW MATERIAL ==================
            if (/^plant_manager_approval_status_\d+$/.test(id)) {
                var rejectionId = id.replace("plant_manager_approval_status_", "");

                var $rej_inputs = $([
                    '#total_qty_' + rejectionId,
                    '#pc_' + rejectionId,
                    '#runner_gms_' + rejectionId,
                    '#flash_gm_' + rejectionId,
                    '#lumps_gm_' + rejectionId
                ].join(','));

                if (isApproved) {
                    $rej_inputs.prop('readonly', true);
                } else {
                    $rej_inputs.prop('readonly', false);
                }
            }
            // ================== BALANCE QTY (RM + MB) ==================
            // ID pattern: plant_manager_approval_status_{mbId}_{rmId}
            if (/^plant_manager_approval_status_/.test(id)) {
                var parts = id.replace("plant_manager_approval_status_", "").split("_");
                var mbId = parts[0] !== "null" ? parts[0] : null;
                var rmId = parts[1] !== "null" ? parts[1] : null;

                var selectors = [];
                if (rmId) selectors.push('#rm_total_qty_' + rmId);
                if (mbId) selectors.push('#mb_total_qty_' + mbId);

                var $inputs = $(selectors.join(','));

                if (isApproved) {
                    $inputs.prop('readonly', true);
                } else {
                    $inputs.prop('readonly', false);
                }
            }


        });

        // On page load, set readonly if already approved
        $(document).ready(function () {
            $('.status-select').each(function () {
                var $select = $(this);
                var id = $select.attr('id');
                var isApproved = $select.val() === "0"; // Approved

                // ================== ARTICLE ==================
                if (/^status_\d+$/.test(id)) {
                    var articleId = id.split('_')[1];
                    var $inputs = $([
                        'input[id^="qty_"][id$="_' + articleId + '"]',
                        'input[id^="weight_"][id$="_' + articleId + '"]'
                    ].join(','));
                    if (isApproved) {
                        $inputs.prop('readonly', true);
                    }
                }

                // ================== RAW MATERIAL ==================
                if (/^plant_manager_approval_status\d+_\d+$/.test(id)) {
                    var parts = id.replace("plant_manager_approval_status", "").split("_");
                    var rmId = parts[0];
                    var articleId = parts[1];
                    var $rm_input = $('#row_total_qty_' + rmId + '_' + articleId);
                    if (isApproved) {
                        $rm_input.prop('readonly', true);
                    }
                }

                // ================== MASTER BATCH ==================
                if (/^plant_manager_approval_status_mb\d+_\d+$/.test(id)) {
                    var parts = id.replace("plant_manager_approval_status_mb", "").split("_");
                    var mbId = parts[0];
                    var articleId = parts[1];
                    var $mb_input = $('#row_total_qty_mb' + mbId + '_' + articleId);
                    if (isApproved) {
                        $mb_input.prop('readonly', true);
                    }
                }

                // ================== REJECTION RAW MATERIAL ==================
                if (/^plant_manager_approval_status_\d+$/.test(id)) {
                    var rejectionId = id.replace("plant_manager_approval_status_", "");
                    var $rej_inputs = $([
                        '#total_qty_' + rejectionId,
                        '#pc_' + rejectionId,
                        '#runner_gms_' + rejectionId,
                        '#flash_gm_' + rejectionId,
                        '#lumps_gm_' + rejectionId
                    ].join(','));
                    if (isApproved) {
                        $rej_inputs.prop('readonly', true);
                    }
                }

                // ================== BALANCE QTY (RM + MB) ==================
                if (/^plant_manager_approval_status_/.test(id)) {
                    var parts = id.replace("plant_manager_approval_status_", "").split("_");
                    var mbId = parts[0] !== "null" ? parts[0] : null;
                    var rmId = parts[1] !== "null" ? parts[1] : null;

                    var selectors = [];
                    if (rmId) selectors.push('#rm_total_qty_' + rmId);
                    if (mbId) selectors.push('#mb_total_qty_' + mbId);

                    var $inputs = $(selectors.join(','));
                    if (isApproved) {
                        $inputs.prop('readonly', true);
                    }
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#total_qty').val($('#total_qty_val').val());
        $('#total_weight').val($('#total_weight_val').val());
        $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.production_report_list').addClass('active_cc');
    });

    $(document).ready(function () {
        $('.remark_track').change(function () {
            // getting id of this
            var id = $(this).attr('article');
            var value = $(this).val();
            const timeSlots = [
                "eight_nine", "nine_ten", "ten_eleven", "eleven_twelve", "twelve_thirteen", "thirteen_fourteen", "fourteen_fifteen", "fifteen_sixteen",
                "sixteen_seventeen", "seventeen_eighteen", "eighteen_nineteen", "nineteen_twenty", "twenty_twentyone", "twentyone_twentytwo", "twentytwo_twentythree", "twentythree_zero",
                "zero_one", "one_two", "two_three", "three_four", "four_five", "five_six", "six_seven", "seven_eight"
            ];
            timeSlots.forEach(slot => {
                $('#qty_' + slot + '_remark_' + id).val(value);
                $('#weight_' + slot + '_remark_' + id).val(value);

            });
        })
        $('#submitRemarkBtn').click(function () {
            const articleId = $('#article_id').val();
            add_article_remark(articleId);
        });
    });
</script>
<script>
    function updateTotals(input) {

        var inputId = input.id;
        var parts = inputId.split('_');
        var articleId = parts[parts.length - 1];

        var qtyInputs = document.querySelectorAll('input[id^="qty_"][id$="_' + articleId + '"]');
        var totalQty = Array.from(qtyInputs).reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);


        document.getElementById('total_qty_' + articleId).textContent = totalQty;


        var weightInputs = document.querySelectorAll('input[id^="weight_"][id$="_' + articleId + '"]');
        var totalWeight = 0;
        var weightCount = 0;

        weightInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value)) {
                totalWeight += value;
                weightCount++;
            }
        });


        var averageWeight = weightCount > 0 ? totalWeight / weightCount : 0;


        document.getElementById('total_weight_' + articleId).textContent = averageWeight.toFixed(3);
    }
</script>
<script>
    function check_input(element) {
        const $el = $(element);
        const valStr = $el.val().trim();
        const value = parseFloat(valStr);

        $el.removeClass('error red green');
        $el.next('label.error').hide();

        if (valStr !== '' && !isNaN(value) && value < 0) {
            $el.val('').addClass('error');
            if ($el.next('label.error').length === 0) {
                $('<label class="error">Negative values are not allowed</label>')
                    .insertAfter($el);
            } else {
                $el.next('label.error').show();
            }
            return;
        }

        if (!isNaN(value)) {
            if (value < 30) $el.addClass('red');
            else $el.addClass('green');
        }
    }
    $(function () {
        $('input[id^="qty_"]').each(function () {
            check_input(this);
        });
    });

    // working
    function remark_popup(el, showModal) {
        if (!showModal) return;

        const $el = $(el);
        const fieldId = $el.attr('id');

        if (!/^qty_/.test(fieldId)) return;

        const val = parseFloat($el.val());
        if (isNaN(val) || val >= 30) return;

        const parts = fieldId.split('_');
        const suffix = parts.pop();
        const prefix = parts.join('_'); // "qty"

        const existingRemark = $(`#${prefix}_remark_${suffix}`).val() || '';
        $('#remark').val(existingRemark);

        $('#add_remark')
            .data('triggerField', {
                $el,
                prefix,
                suffix
            })
            .modal('show');
    }
    $('#add_remark').on('shown.bs.modal', function () {
        const $modal = $(this);
        const $saveBtn = $modal.find('#submitForm');
        const $closeBtn = $modal.find('#closeRemarkBtn'); // new close btn
        const $textarea = $modal.find('#remark');

        // initialize
        $textarea.removeClass('is-invalid');
        const hasText = $textarea.val().trim().length > 0;
        $saveBtn.prop('disabled', !hasText);
        $closeBtn.toggle(hasText); // show/hide close button

        $textarea.off('input').on('input', () => {
            const hasText = $textarea.val().trim().length > 0;
            $textarea.toggleClass('is-invalid', !hasText);
            $saveBtn.prop('disabled', !hasText);
            $closeBtn.toggle(hasText); // toggle close button
        });

        $saveBtn.off('click').on('click', () => {
            const remark = $textarea.val().trim();
            if (!remark) {
                $textarea.addClass('is-invalid').focus();
                return;
            }

            const info = $modal.data('triggerField');
            $(`#${info.prefix}_remark_${info.suffix}`).val(remark);

            $modal.modal('hide');
        });
    });

    // $('#add_remark').on('shown.bs.modal', function () {
    //     const $modal = $(this);
    //     const $saveBtn = $modal.find('#submitForm');
    //     const $textarea = $modal.find('#remark');

    //     // initialize
    //     $textarea.removeClass('is-invalid');
    //     $saveBtn.prop('disabled', !$textarea.val().trim());

    //     $textarea.off('input').on('input', () => {
    //         const hasText = $textarea.val().trim().length > 0;
    //         $textarea.toggleClass('is-invalid', !hasText);
    //         $saveBtn.prop('disabled', !hasText);
    //     });

    //     $saveBtn.off('click').on('click', () => {
    //         const remark = $textarea.val().trim();
    //         if (!remark) {
    //             $textarea.addClass('is-invalid').focus();
    //             return;
    //         }

    //         const info = $modal.data('triggerField');
    //         $(`#${info.prefix}_remark_${info.suffix}`).val(remark);

    //         $modal.modal('hide');
    //     });
    // });
    $(function () {
        let $tooltip = $('<div class="custom-tooltip"></div>').appendTo('body');

        $('[id^="qty_"]').on('mouseenter', function (e) {
            const $el = $(this);
            const val = parseFloat($el.val());
            if (isNaN(val) || val >= 30) {
                $tooltip.removeClass('show');
                return;
            }

            const fieldId = $el.attr('id');
            const parts = fieldId.split('_');
            const suffix = parts.pop();
            const prefix = parts.join('_');

            const remarkFieldId = `${prefix}_remark_${suffix}`;
            const remark = $(`#${remarkFieldId}`).val();

            if (remark && remark.trim() !== '') {
                $tooltip.text(`Remark: ${remark}`).addClass('show');
                positionTooltip(e);
            }
        }).on('mousemove', function (e) {
            positionTooltip(e);
        }).on('mouseleave', function () {
            $tooltip.removeClass('show');
        });

        function positionTooltip(e) {
            const tooltipWidth = $tooltip.outerWidth();
            const tooltipHeight = $tooltip.outerHeight();
            let left = e.pageX + 15; // 15px right from cursor
            let top = e.pageY + 15;  // 15px below cursor

            // Adjust if tooltip goes beyond window width
            if (left + tooltipWidth > $(window).scrollLeft() + $(window).width()) {
                left = e.pageX - tooltipWidth - 15;
            }

            // Adjust if tooltip goes beyond window height
            if (top + tooltipHeight > $(window).scrollTop() + $(window).height()) {
                top = e.pageY - tooltipHeight - 15;
            }

            $tooltip.css({ left: left + 'px', top: top + 'px' });
        }
    });



    function handleZero(input) {
        if (input.value === "0" || input.value === "") {
            input.value = "";
        }
    }
    $(document).ready(function () {
        $.validator.addClassRules("hourly", {

            // number: true,
            // min: 0,
            // messages: {

            //     number: "Please enter a valid number",
            //     min: "Value must be greater than 0"
            // }
        });

        $('input[name^="qty_"], input[name^="weight_"]').addClass("hourly");

        var validator = $("#production_form_list").validate({
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            success: function (label) {
                label.hide();
                label.prev('input').removeClass('error');
            },
            onkeyup: function (element) {
                $(element).valid(); // triggers validation on keyup
            },
            onfocusout: function (element) {
                $(element).valid(); // triggers validation when focus is lost
            }
        });

        $('input.hourly').on('input change', function () {
            const value = $(this).val().trim();

            if (value !== '' && parseFloat(value) < 0) {
                $(this).val('');
            }

            $(this).valid();
        });

        $('input.hourly').on('keypress', function (e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            var inputVal = $(this).val();

            if (charCode >= 48 && charCode <= 57) {
                return true;
            }
            if (charCode == 46) {
                if (inputVal.indexOf('.') === -1) {
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            }
            e.preventDefault();
            return false;
        });

        $(".hourly-submit").click(function (e) {
            e.preventDefault();


            let emptyFields = false;
            $('input.hourly').each(function () {
                if ($(this).val().trim() === '') {
                    $(this).addClass('error');
                    emptyFields = true;
                }
            });




            if (!validator.form()) return false;

            var id = $(this).data("article-id");

            set_article_production_details(id);

            return false;
        });


        $('input.hourly').on('keydown', function (e) {
            if (e.key === '-' || e.keyCode === 189) {
                e.preventDefault();
                return false;
            }
        });
    });


    ////////////////////////////////////////////// article name //////////////////////////////////////////////////////////////////////////////

    function set_article_production_details(id) {
        let formData = {
            production_id: $('#production_id').val(),
            machine_id: $('#machine_id').val(),
            article_id: id,
            status: $('#status_' + id).val(),
            remark: $('#remark_' + id).val(),
            qty_data: {},
            weight_data: {}
        };

        const timeSlots = [
            "eight_nine", "nine_ten", "ten_eleven", "eleven_twelve", "twelve_thirteen", "thirteen_fourteen", "fourteen_fifteen", "fifteen_sixteen",
            "sixteen_seventeen", "seventeen_eighteen", "eighteen_nineteen", "nineteen_twenty", "twenty_twentyone", "twentyone_twentytwo", "twentytwo_twentythree", "twentythree_zero",
            "zero_one", "one_two", "two_three", "three_four", "four_five", "five_six", "six_seven", "seven_eight"
        ];

        let hasQtyFilled = false;
        let hasWeightFilled = false;
        let isIncompleteSequence = false;
        let shouldPreventSubmit = false;

        timeSlots.forEach(slot => {
            let qtyInput = $(`#qty_${slot}_${id}`)[0];
            let weightInput = $(`#weight_${slot}_${id}`)[0];
            let qtyValue = $(qtyInput).val();
            let weightValue = $(weightInput).val();

            formData.qty_data[`qty_${slot}`] = qtyValue ? qtyValue.trim() : '';
            formData.weight_data[`weight_${slot}`] = weightValue ? weightValue.trim() : '';

            if (qtyValue && qtyValue.trim() !== '') {
                hasQtyFilled = true;
                if (!weightValue || weightValue.trim() === '') {
                    isIncompleteSequence = true;
                }
            }

            if (weightValue && weightValue.trim() !== '') {
                hasWeightFilled = true;
            }


            if (weightInput) {
                weightInput.addEventListener('input', function () {
                    this.style.color = 'black';
                    this.style.fontWeight = '400';
                    if (this.classList.contains('error')) {
                        this.classList.remove('error');
                    };

                });
            }
        });

        if (hasQtyFilled && !hasWeightFilled) {

            shouldPreventSubmit = true;
        }

        if (isIncompleteSequence) {
            alert('Please fill the corresponding weight for each entered quantity.');
            shouldPreventSubmit = true;
        }

        if (shouldPreventSubmit) {
            return;
        }

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_article_production_details",
            type: "POST",
            data: formData,

            success: function (response) {
                try {
                    const result = JSON.parse(response);
                    alert(result.message || 'Article added successfully!');
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Article added successfully!');
                }
                location.reload();
            },
            error: function (xhr) {
                error
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }

    $('#add_remark').on('shown.bs.modal', function () {
        $('#submitForm').off('click').on('click', () => {
            const info = $('#add_remark').data('triggerField');
            const remark = $('#remark').val().trim();

            $(`#${info.prefix}_remark_${info.suffix}`).val(remark);

            const payload = {
                production_id: $('#production_id').val(),
                article_id: info.suffix,
                remark_data: {}
            };
            payload.remark_data[info.prefix + '_remark'] = remark;

            $.ajax({
                url: '<?= base_url("admin/Ajax_controller/set_article_remark") ?>',
                type: 'POST',
                dataType: 'json',
                data: payload,
                success(response) {
                    console.log("saved!", response);
                },
                error(xhr, status, err) {
                    console.error("save failed", err);
                }
            });

            $('#add_remark').modal('hide');
        });
    });

    ////////////////////////////////////////////// raw material //////////////////////////////////////////////////////////////////////////////


    function set_raw_material_production_details(raw_material_id, article_id) {
        let formData = {
            production_id: $('#production_id').val(),
            machine_id: $('#machine_id').val(),
            raw_material_id: raw_material_id,
            article_id: $('#article_id_' + raw_material_id + '_' + article_id).val(),

            plant_manager_approval_status: $('#plant_manager_approval_status' + raw_material_id + '_' + article_id).val(),
            remark: $('#remark_' + raw_material_id + '_' + article_id).val(),
            total_qty: $('#row_total_qty_' + raw_material_id + '_' + article_id).val()

        };


        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_raw_material_production_details",
            type: "POST",
            data: formData,
            success: function (response) {
                alert('Raw Material successfully!');
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                    location.reload();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    location.reload();
                }
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }


    ////////////////////////////////////////////// master batch //////////////////////////////////////////////////////////////////////////////


    function set_master_batch_production_details(master_batch_id, article_id) {
        let formData = {
            production_id: $('#production_id').val(),
            machine_id: $('#machine_id').val(),
            master_batch_id: master_batch_id,
            article_id: $('#article_id_' + master_batch_id + '_' + article_id).val(),

            plant_manager_approval_status: $('#plant_manager_approval_status_mb' + master_batch_id + '_' + article_id).val(),
            remark: $('#remark_mb' + master_batch_id + '_' + article_id).val(),
            total_qty: $('#row_total_qty_mb' + master_batch_id + '_' + article_id).val()

        };
        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_master_batch_production_details",
            type: "POST",
            data: formData,
            success: function (response) {
                alert('Master Batch successfully!');
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                    location.reload();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    location.reload();
                }
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }


    ////////////////////////////////////////////// rejection //////////////////////////////////////////////////////////////////////////////

    function set_rejection_production_details(rejection_id) {
        let formData = {
            production_id: $('#production_id').val(),
            machine_id: $('#machine_id').val(),
            rejection_id: rejection_id,
            total_qty: $('#total_qty_' + rejection_id).val(),
            pc: $('#pc_' + rejection_id).val(),
            runner_gms: $('#runner_gms_' + rejection_id).val(),
            flash_gm: $('#flash_gm_' + rejection_id).val(),
            lumps_gm: $('#lumps_gm_' + rejection_id).val(),
            plant_manager_approval_status: $('#plant_manager_approval_status_' + rejection_id).val(),
            remark: $('#remark_' + rejection_id).val()
        };
        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_rejection_production_details",
            type: "POST",
            data: formData,
            success: function (response) {
                alert('Rejection raw material added successfully!');
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                    location.reload();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    location.reload();
                }
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }

    ////////////////////////////////////////////// balance quantity  //////////////////////////////////////////////////////////////////////////////



    function set_balance_quantity_production_details(master_batch_id, raw_material_id) {
        const production_id = "<?= $this->uri->segment(2) ?>";
        const new_master_batch_id = master_batch_id === 'null' ? null : master_batch_id;
        const new_raw_material_id = raw_material_id === 'null' ? null : raw_material_id;
        const rm_total_qty = document.getElementById(`rm_total_qty_${raw_material_id}`)?.value || '';
        const mb_total_qty = document.getElementById(`mb_total_qty_${master_batch_id}`)?.value || '';
        const plant_manager_approval_status = document.getElementById(`plant_manager_approval_status_${new_master_batch_id}_${new_raw_material_id}`)?.value || '';
        const remark = document.getElementById(`remark_${master_batch_id}_${raw_material_id}`)?.value || '';

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_balance_quantity_production_details",
            type: "POST",
            data: {
                production_id: production_id,
                raw_material_id: raw_material_id,
                master_batch_id: master_batch_id,
                rm_total_qty: rm_total_qty,
                mb_total_qty: mb_total_qty,
                plant_manager_approval_status: plant_manager_approval_status,
                remark: remark
            },
            success: function (res) {
                try {
                    const result = JSON.parse(res);
                    alert(result.message || 'Balance Quantity added successfully!');
                } catch (e) {
                    console.warn('Unexpected response, reloading anyway.');
                    alert('Balance Quantity added successfully!');
                }
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                alert("Something went wrong!");
            }
        });
    }


    ///////////////////////////////////////////////////////// Summary ////////////////////////////////////////////////////////////////




    function set_all_article_summary_details() {
        let allData = [];

        $('#data8-9able tbody tr').each(function () {
            let row = $(this);
            let article_id = row.data('article-id');

            let rowData = {
                production_id: $('#production_id').val(),
                machine_id: $('#machine_id').val(),
                article_id: article_id,
                approved_qty: row.find('#approved_qty_' + article_id).text().trim(),
                average_qty: row.find('#average_qty_' + article_id).text().trim(),
                total_weight: row.find('#total_weight_' + article_id).text().trim(),
                delta: $('#delta').text().trim(),
                remark: row.find('#remark_' + article_id).val()
            };


            allData.push(rowData);
        });

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_all_article_summary_details",
            type: "POST",
            data: {
                data: allData
            },

            success: function (response) {
                // alert('Summary added successfully!');
                // console.log("Raw response:", response);
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            },

            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }

    function set_production_remarks() {
        let formData = {
            production_id: $('#production_id').val(),
            remarks: $('#remarks_textarea').val()
        };
        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_production_remarks",
            type: "POST",
            data: formData,
            success: function (response) {
                // alert('Data added successfully!');
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            },
            error: function (xhr) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error saving data');
            }
        });
    }
</script>
<script>
    var fileInput = document.getElementById('image_names');
    var errorEl = document.getElementById('image_error');

    fileInput.addEventListener('change', function () {
        errorEl.innerText = '';
    });
    function set_production_images() {
        var production_id = document.getElementById('production_id').value;
        var files = fileInput.files;
        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/get_production_images",
            type: "POST",
            data: { production_id: production_id },
            success: function (response) {
                try {
                    var res = JSON.parse(response);
                    var file = 0;
                    if ((res.data && res.data.length > 0) || files.length !== 0) {
                        file = 1;
                    }
                    uploadImages(file);
                } catch (e) {
                    errorEl.innerText = 'Error parsing server response.';
                }
            },
        });
    }
    function uploadImages(file) {
        var production_id = document.getElementById('production_id').value;
        var files = fileInput.files;
        errorEl.innerText = '';
        if (file === 0) {
            errorEl.innerText = 'Please select at least one image.';
            return;
        }
        var formData = new FormData();
        formData.append('production_id', production_id);
        for (var i = 0; i < files.length; i++) {
            formData.append('image_names[]', files[i]);
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= base_url('admin/Ajax_controller/upload_images_updated') ?>', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    fileInput.value = '';
                    errorEl.innerText = '';
                    alert(response.message);
                } else {
                    errorEl.innerText = response.message;
                }
            } else {
                errorEl.innerText = 'Error uploading images.';
            }
        };
        xhr.send(formData);
    }

</script>

<script>
    function fetchLogs(productionId, articleId) {
        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_article_production_details_logs_ajax') ?>",
            method: "POST",
            data: {
                production_id: productionId,
                article_id: articleId
            },
            dataType: "json",
            success: function (response) {
                let tbody = "";

                if (response.data && response.data.length) {
                    response.data.forEach(record => {
                        tbody += `
              <tr>
                <td rowspan="2">${record.sr_no}</td>
                <td rowspan="2">${record.article_name}</td>
                <td rowspan="2">${record.approved_qty}</td>
                <td rowspan="2">${record.average_qty !== null ? parseFloat(record.average_qty).toFixed(3) : ''}</td>
                <td>Qty Per Hour</td>
                ${[
                                'qty_eight_nine', 'qty_nine_ten', 'qty_ten_eleven', 'qty_eleven_twelve',
                                'qty_twelve_thirteen', 'qty_thirteen_fourteen', 'qty_fourteen_fifteen',
                                'qty_fifteen_sixteen', 'qty_sixteen_seventeen', 'qty_seventeen_eighteen',
                                'qty_eighteen_nineteen', 'qty_nineteen_twenty', 'qty_twenty_twentyone',
                                'qty_twentyone_twentytwo', 'qty_twentytwo_twentythree', 'qty_twentythree_zero',
                                'qty_zero_one', 'qty_one_two', 'qty_two_three', 'qty_three_four',
                                'qty_four_five', 'qty_five_six', 'qty_six_seven', 'qty_seven_eight'
                            ].map(h => `<td>${record[h]}</td>`).join('')}
                <td rowspan="2">${record.status}</td>
                <td rowspan="2">${record.remark}</td>
              </tr>
              <tr>
                <td>Weight Per Hour</td>
                ${[
                                'weight_eight_nine', 'weight_nine_ten', 'weight_ten_eleven', 'weight_eleven_twelve',
                                'weight_twelve_thirteen', 'weight_thirteen_fourteen', 'weight_fourteen_fifteen',
                                'weight_fifteen_sixteen', 'weight_sixteen_seventeen', 'weight_seventeen_eighteen',
                                'weight_eighteen_nineteen', 'weight_nineteen_twenty', 'weight_twenty_twentyone',
                                'weight_twentyone_twentytwo', 'weight_twentytwo_twentythree', 'weight_twentythree_zero',
                                'weight_zero_one', 'weight_one_two', 'weight_two_three', 'weight_three_four',
                                'weight_four_five', 'weight_five_six', 'weight_six_seven', 'weight_seven_eight'
                            ].map(h => `<td>${record[h]}</td>`).join('')}
              </tr>
            `;
                    });
                } else {
                    tbody = `<tr><td colspan="31">No Log found.</td></tr>`;
                }
                $("#exampleModal table tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    $(document).on('click', '.table_btn', function (e) {
        e.preventDefault();
        const productionId = $('#production_id').val();
        const articleId = $(this).closest('td').find('.article-id').val();

        fetchLogs(productionId, articleId);
    });
</script>


<script>
    $(document).on('click', '#check_logs_rm', function (e) {
        e.preventDefault();
        var production_id = $('#production_id').val();
        var row = $(this).closest('tr');
        var raw_material_id = row.find('input[name="raw_material_id[]"]').val();
        var article_id = row.find('input[name="article_id[]"]').val();
        openRemarkModal

        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_raw_material_production_details_logs_ajax') ?>",
            method: "POST",
            data: {
                production_id: production_id,
                raw_material_id: raw_material_id,
                article_id: article_id
            },
            dataType: "json",
            success: function (response) {
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, record) {
                        tbody += `<tr>
                        <td>${record.rm_name}</td>
                        <td>${record.article_name}</td>
                        <td>${record.total_qty}</td>
                        <td>${record.plant_manager_approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='5'>No Log found.</td></tr>";
                }
                $("#logDataTable tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
</script>

<script>
    $(document).on('click', '#check_logs_mb', function (e) {
        e.preventDefault();
        var production_id = $('#production_id').val();
        var row = $(this).closest('tr');
        var master_batch_id = row.find('input[name="master_batch_id[]"]').val();
        var article_id = row.find('input[name="article_id[]"]').val();

        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_master_batch_production_details_logs_ajax') ?>",
            method: "POST",
            data: {
                production_id: production_id,
                master_batch_id: master_batch_id,
                article_id: article_id
            },
            dataType: "json",
            success: function (response) {
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, record) {
                        tbody += `<tr>
                        <td>${record.name}</td>
                        <td>${record.article_name}</td>
                        <td>${record.total_qty}</td>
                        <td>${record.plant_manager_approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='5'>No Log found.</td></tr>";
                }
                $("#logDataTable tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
</script>

<script>
    $(document).on('click', '#check_logs_reject', function (e) {
        e.preventDefault();
        var production_id = $('#production_id').val();
        var row = $(this).closest('tr');
        var rejection_id = row.find('input[name="rejection_id[]"]').val();

        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_rejection_production_details_logs_ajax') ?>",
            method: "POST",
            data: {
                production_id: production_id,
                rejection_id: rejection_id
            },
            dataType: "json",
            success: function (response) {
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, record) {
                        tbody += `<tr>
                        <td>${record.rm_name}</td>
                        <td>${record.total_qty}</td>
                        <td>${record.pc}</td>
                        <td>${record.runner_gms}</td>
                        <td>${record.flash_gm}</td>
                        <td>${record.lumps_gm}</td>
                        <td>${record.plant_manager_approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='8'>No Log found.</td></tr>";
                }
                $("#logDataTable tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
</script>

<script>
    $(document).on('click', '#check_logs_balance', function (e) {
        e.preventDefault();
        var production_id = $('#production_id').val();
        var row = $(this).closest('tr');
        var raw_material_id = row.find('input[name="raw_material_id[]"]').val();
        var master_batch_id = row.find('input[name="master_batch_id[]"]').val();
        null
        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_balance_quantity_production_details_logs_ajax') ?>",
            method: "POST",
            data: {
                production_id: production_id,
                master_batch_id: master_batch_id,
                raw_material_id: raw_material_id
            },
            dataType: "json",
            success: function (response) {
                console.log(response);
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, record) {
                        tbody += `<tr>
                        <td>${record.rm_name}</td>
                        <td>${record.rm_total_qty}</td>
                        <td>${record.name}</td>
                        <td>${record.mb_total_qty}</td>
                        <td>${record.plant_manager_approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='6'>No Log found.</td></tr>";
                }
                $("#logDataTable tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });

    });
</script>

<!-- /////////////////////////  Jayesh 16-5-25   /////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- for delta calculation -->

<script>
    function getTableByH3(text) {
        const div = Array.from(document.querySelectorAll('.table1')).find(div => {
            const h3 = div.querySelector('h3');
            return h3 && h3.textContent.trim() === text;
        });
        return div ? div.querySelector('table') : null;
    }

    const rawMaterialTable = getTableByH3('Raw Material List');
    const masterBatchTable = getTableByH3('Master Batch List');
    const balanceQuantityTable = getTableByH3('Balance Quantity');
    const rejectionTable = getTableByH3('Rejection Raw Material');

    const rmInputs = rawMaterialTable ? rawMaterialTable.querySelectorAll('tbody input[id^="row_total_qty_"]') : [];
    const mbInputs = masterBatchTable ? masterBatchTable.querySelectorAll('tbody input[id^="row_total_qty_mb"]') : [];
    const balanceInputs = balanceQuantityTable ? balanceQuantityTable.querySelectorAll('tbody input[id^="rm_total_qty_"], tbody input[id^="mb_total_qty_"]') : [];
    const rejectionInputs = rejectionTable ? rejectionTable.querySelectorAll('tbody input[id^="total_qty_"]') : [];

    function sumInputValues(inputs) {
        return Array.from(inputs).reduce((sum, input) => {
            const value = parseFloat(input.value.trim());
            return sum + (isNaN(value) ? 0 : value);
        }, 0);
    }

    function calculateDelta() {
        const totalRM = sumInputValues(rmInputs);
        const totalMB = sumInputValues(mbInputs);
        const totalBalance = sumInputValues(balanceInputs);
        const totalRejection_in_kg = sumInputValues(rejectionInputs);
        const original_delta = (totalRM + totalMB - totalBalance) - totalRejection_in_kg;
        const productTds = document.querySelectorAll('.total_weight');
        let totalProduct_in_kg = 0;
        productTds.forEach(td => {
            const valueStr = td.textContent.trim().replace(/,/g, '');
            const value = parseFloat(valueStr);
            if (!isNaN(value)) {
                totalProduct_in_kg += value;
            }
        });
        const final_delta = original_delta - totalProduct_in_kg;
        const deltaCell = document.getElementById('delta');
        if (deltaCell) {
            deltaCell.textContent = final_delta.toFixed(3);
        }
    }

    const allInputs = [...rmInputs, ...mbInputs, ...balanceInputs, ...rejectionInputs];
    allInputs.forEach(input => {
        input.addEventListener('input', calculateDelta);
    });
    calculateDelta();
</script>


<!-- validation -->
<script>
    $(document).ready(function () {
        $('input[id^="row_total_qty_"], input[id^="total_qty_"], input[id^="pc_"], input[id^="runner_gms_"], input[id^="flash_gm"], input[id^="lumps_gm"], input[id^="rm_total_qty_"], input[id^="mb_total_qty_"]').each(function () {
            $(this)
                .attr('name', this.id)
                .addClass('totalQty');
        });

        $.validator.addMethod("nonNegative", function (value, element) {
            return this.optional(element) || parseFloat(value) >= 0;
        }, "Value should not be negative");

        $.validator.addClassRules("totalQty", {
            // required: true,
            number: true,
            nonNegative: true
        });

        var validator = $("#production_form_list").validate({
            errorClass: "text-danger",
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            messages: {
                number: "Please enter a valid number"
                // required: "This field is required"
            }
        });

        $('input.totalQty').on('input change', function () {
            $(this).valid();
        });

        $('input.totalQty').on('keypress', function (e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            var inputVal = $(this).val();

            if (charCode >= 48 && charCode <= 57) {
                return true;
            }
            if (charCode == 46) {
                if (inputVal.indexOf('.') === -1) {
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            }
            e.preventDefault();
            return false;
        });

        $('input.totalQty').on('paste', function (e) {
            var clipboardData = e.originalEvent.clipboardData.getData('text');
            if (clipboardData.includes('-') || isNaN(clipboardData)) {
                e.preventDefault();
            }
            if ((clipboardData.match(/\./g) || []).length > 1) {
                e.preventDefault();
            }
        });
    });

    function openRemarkModal(articleId) {
        $('#article_id').val(articleId);
        $('#add_remark').modal('show');
    }
    $(document).ready(function () {
        function validateDropdown(rawMaterialId, articleId) {
            let dropdownId = "plant_manager_approval_status" + rawMaterialId + "_" + articleId;
            let totalQtId = "row_total_qty_" + rawMaterialId + "_" + articleId;

            let dropdown = $("#" + dropdownId);
            let qttotal = $("#" + totalQtId);

            let errorSpanDropdown = dropdown.next(".error-message");
            if (errorSpanDropdown.length === 0) {
                errorSpanDropdown = $('<span class="error-message" style="color:red;"></span>').insertAfter(dropdown);
            }
            let errorSpanTotalQt = qttotal.next(".error-message");
            if (errorSpanTotalQt.length === 0) {
                errorSpanTotalQt = $('<span class="error-message" style="color:red;"></span>').insertAfter(qttotal);
            }
            let isValid = true;
            errorSpanDropdown.hide();
            dropdown.css("border", "");
            // }

            if (qttotal.val().trim() === "") {
                errorSpanTotalQt.text("Enter the value").show();
                qttotal.css("border", "1px solid red");
                isValid = false;
            } else {
                errorSpanTotalQt.hide();
                qttotal.css("border", "");
            }

            return isValid;
        }

        $(".table_btn").click(function (e) {
            let onclickAttr = $(this).attr("onclick");
            let match = onclickAttr.match(/set_raw_material_production_details\((\d+),\s*(\d+)\)/);

            if (match) {
                let rawMaterialId = match[1];
                let articleId = match[2];

                if (!validateDropdown(rawMaterialId, articleId)) {
                    e.preventDefault();
                    return false;
                }
            }
        });


        $(".status-select").change(function () {
            $(this).css("border", "");
            $(this).next(".error-message").hide();
        });

        $(document).on('input', '[id^=row_total_qty_]', function () {
            let $this = $(this);
            if ($this.val().trim() !== "") {
                $this.css("border", "");
                $this.next(".error-message").hide();
            }
        });

        let originalFunction = window.set_raw_material_production_details || function () { };
        window.set_raw_material_production_details = function (rawMaterialId, articleId) {
            if (validateDropdown(rawMaterialId, articleId)) {
                return originalFunction(rawMaterialId, articleId);
            }
            return false;
        };

    });
    $(document).ready(function () {
        function validateMasterBatchDropdown(masterBatchId, articleId) {
            let dropdownId = "plant_manager_approval_status_mb" + masterBatchId + "_" + articleId;
            let totalQtId = "row_total_qty_mb" + masterBatchId + "_" + articleId;
            let dropdown = $("#" + dropdownId);
            let qttotal = $("#" + totalQtId);

            let errorSpanDropdown = dropdown.next(".error-message");
            let errorSpanTotalQt = qttotal.next(".error-message");

            let isValid = true;
            errorSpanDropdown.hide();
            dropdown.css("border", "");
            // }

            if (qttotal.val() === null || qttotal.val().trim() === "") {
                if (errorSpanTotalQt.length === 0) {
                    errorSpanTotalQt = $('<span class="error-message" style="color:red;"></span>').insertAfter(qttotal);
                }
                errorSpanTotalQt.text("Enter the value").show();
                qttotal.css("border", "1px solid red");
                isValid = false;
            } else {
                errorSpanTotalQt.hide();
                qttotal.css("border", "");
            }

            return isValid;
        }

        $(".table_btn").click(function (e) {
            let onclickAttr = $(this).attr("onclick");
            let match = onclickAttr.match(/set_master_batch_production_details\((\d+),\s*(\d+)\)/);

            if (match) {
                let masterBatchId = match[1];
                let articleId = match[2];

                if (!validateMasterBatchDropdown(masterBatchId, articleId)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        $(".status-select").change(function () {
            $(this).css("border", "");
            $(this).next(".error-message").hide();
        });

        $(document).on('input', '[id^=row_total_qty_mb]', function () {
            let $this = $(this);
            if ($this.val().trim() !== "") {
                $this.css("border", "");
                $this.next(".error-message").hide();
            }
        });

        let originalFunction = window.set_master_batch_production_details || function () { };
        window.set_master_batch_production_details = function (masterBatchId, articleId) {
            if (validateMasterBatchDropdown(masterBatchId, articleId)) {
                return originalFunction(masterBatchId, articleId);
            }
            return false;
        };
    });


    // for reject and balance 

    $(document).ready(function () {
        $(".status-select").each(function () {
            if ($(this).siblings(".error-message").length === 0) {
                $(this).after('<span class="error-message" style="display:none; color:red;"></span>');
            }
        });

        function validateStatusDropdown(elementId) {
            let dropdown = $("#" + elementId);
            let errorSpan = dropdown.siblings(".error-message");
            errorSpan.hide();
            dropdown.css("border", "");
            return true;
        }

        function validateQuantityInput(elementId, maxDecimals = null) {
            let input = $("#" + elementId);
            let value = input.val().trim();
            let errorSpan = input.siblings(".error-message");
            if (!errorSpan.length) {
                errorSpan = $('<span class="error-message" style="display:none; color:red;"></span>').insertAfter(input);
            }
            if (value === "" || isNaN(value) || parseFloat(value) < 0) {
                errorSpan.text("Enter the value").show();
                input.css("border", "1px solid red");
                return false;
            } else if (maxDecimals !== null) {
                let decimalPlaces = (value.split('.')[1] || []).length;
                if (decimalPlaces > maxDecimals) {
                    errorSpan.text(`Max ${maxDecimals} decimal places allowed`).show();
                    input.css("border", "1px solid red");
                    return false;
                }
            }
            errorSpan.hide();
            input.css("border", "");
            return true;
        }
        $(".status-select").on("change", function () {
            $(this).css("border", "");
            $(this).siblings(".error-message").hide();
        });
        $(document).on("input", "[id^=row_total_qty_mb], [id^=row_total_qty_rm], [id^=total_qty_], [id^=pc_], [id^=runner_gms_], [id^=flash_gm_], [id^=lumps_gm_], [id^=rm_total_qty_], [id^=mb_total_qty_]", function () {
            let $this = $(this);
            let value = $this.val().trim();
            let id = $this.attr('id');
            let maxDecimals = id.startsWith('row_total_qty_mb') || id.startsWith('row_total_qty_rm') ? 3 : null;

            if (value !== "" && !isNaN(value) && parseFloat(value) >= 0) {
                if (maxDecimals !== null) {
                    let decimalPlaces = (value.split('.')[1] || []).length;
                    if (decimalPlaces <= maxDecimals) {
                        $this.css("border", "");
                        $this.siblings(".error-message").hide();
                    }
                } else {
                    $this.css("border", "");
                    $this.siblings(".error-message").hide();
                }
            }
        });


        if (typeof set_rejection_production_details === 'function') {
            let originalRejectionFn = window.set_rejection_production_details;
            window.set_rejection_production_details = function (rejectionId) {
                let dropdownId = "plant_manager_approval_status_" + rejectionId;
                let fields = ["total_qty_", "pc_", "runner_gms_", "flash_gm_", "lumps_gm_"];
                let isValid = validateStatusDropdown(dropdownId);
                fields.forEach(field => {
                    let inputId = field + rejectionId;
                    isValid = validateQuantityInput(inputId) && isValid;
                });
                if (isValid) {
                    return originalRejectionFn(rejectionId);
                }
                return false;
            };
        }

        $(".table_btn").on("click", function (e) {
            let onclickAttr = $(this).attr("onclick") || "";
            let isValid = true;



            let rejectionMatch = onclickAttr.match(/set_rejection_production_details\((\d+)\)/);
            if (rejectionMatch) {
                let rejectionId = rejectionMatch[1];
                let dropdownId = "plant_manager_approval_status_" + rejectionId;
                let fields = ["total_qty_", "pc_", "runner_gms_", "flash_gm_", "lumps_gm_"];
                isValid = validateStatusDropdown(dropdownId);
                fields.forEach(field => {
                    let inputId = field + rejectionId;
                    isValid = validateQuantityInput(inputId) && isValid;
                });
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });
    });
    $(document).ready(function () {
        $(".table_btn").click(function (e) {
            let onclickAttr = $(this).attr("onclick") || "";
            let submitMatch = onclickAttr.match(/set_article_production_details\((\d+)\)/);

            if (submitMatch) {
                let articleId = submitMatch[1];
            }
        });

        if (typeof window.set_article_production_details === 'function') {
            let originalSetArticleProductionDetails = window.set_article_production_details;
            window.set_article_production_details = function (articleId) {
                return originalSetArticleProductionDetails(articleId);
            };
        }

        $(".status-select").change(function () { });
    });
    $(function () {
        function validateField(selector, type) {
            const $el = $(selector);
            const val = ($el.val() || '').toString().trim();
            let $err = $el.siblings('.error-message');
            if (!$err.length) {
                $err = $('<span class="error-message" style="display:none;color:red"></span>')
                    .insertAfter($el);
            }
            if (type === 'quantity') {
                if (!val || isNaN(val) || parseFloat(val) < 0) {
                    $err.text('Enter the value').show();
                    $el.addClass('is-invalid');
                    return false;
                }
            }
            $err.hide();
            $el.removeClass('is-invalid');
            return true;
        }

        function validateRow(mbId, rmId) {
            let ok = true;
            if (mbId && mbId !== 'null') {
                ok = validateField(`#mb_total_qty_${mbId}`, 'quantity') && ok;
            }
            if (rmId && rmId !== 'null') {
                ok = validateField(`#rm_total_qty_${rmId}`, 'quantity') && ok;
            }
            // Status validation REMOVED
            return ok;
        }

        if (typeof set_balance_quantity_production_details === 'function') {
            const orig = window.set_balance_quantity_production_details;
            window.set_balance_quantity_production_details = function (mbId, rmId) {
                if (validateRow(mbId, rmId)) {
                    return orig(mbId, rmId);
                }
                return false;
            };
        }
        $(document).on('change input',
            'select.status-select, input[id^="rm_total_qty_"], input[id^="mb_total_qty_"]',
            function () {
                $(this).removeClass('is-invalid')
                    .siblings('.error-message').hide();
            });
    });

    //    jayesh 6-5-2025

    document.addEventListener('DOMContentLoaded', function () {
        const timeSlots = [
            'eight_nine', 'nine_ten', 'ten_eleven', 'eleven_twelve', 'twelve_thirteen',
            'thirteen_fourteen', 'fourteen_fifteen', 'fifteen_sixteen', 'sixteen_seventeen',
            'seventeen_eighteen', 'eighteen_nineteen', 'nineteen_twenty', 'twenty_twentyone',
            'twentyone_twentytwo', 'twentytwo_twentythree', 'twentythree_zero', 'zero_one',
            'one_two', 'two_three', 'three_four', 'four_five', 'five_six', 'six_seven', 'seven_eight'
        ];
        const notificationDiv = document.createElement('div');
        notificationDiv.id = 'sequenceNotification';
        notificationDiv.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 1000;
        padding: 15px; background-color: #f8d7da; color: #721c24;
        border: 1px solid #f5c6cb; border-radius: 4px; display: none;
        max-width: 300px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    `;
        document.body.appendChild(notificationDiv);

        function showNotification(message) {
            notificationDiv.textContent = message;
            notificationDiv.style.display = 'block';
            setTimeout(() => {
                notificationDiv.style.display = 'none';
            }, 3000);
        }

        function getFieldSequence(articleId) {
            const sequence = [];
            timeSlots.forEach(slot => {
                sequence.push(`qty_${slot}_${articleId}`);
                sequence.push(`weight_${slot}_${articleId}`);
            });
            return sequence;
        }

        function isFieldFilled(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) {
                console.warn(`Field ${fieldId} not found`);
                return false;
            }
            const value = field.value.trim();
            const isValid = value !== '' && !isNaN(value) && parseFloat(value) >= 0;
            if (!isValid) {
                console.log(`Field ${fieldId} is invalid: value="${value}"`);
            }
            return isValid;
        }

        function getNumericTimeSlot(slot) {
            const numberMap = {
                'zero': '00',
                'one': '01',
                'two': '02',
                'three': '03',
                'four': '04',
                'five': '05',
                'six': '06',
                'seven': '07',
                'eight': '08',
                'nine': '09',
                'ten': '10',
                'eleven': '11',
                'twelve': '12',
                'thirteen': '13',
                'fourteen': '14',
                'fifteen': '15',
                'sixteen': '16',
                'seventeen': '17',
                'eighteen': '18',
                'nineteen': '19',
                'twenty': '20',
                'twentyone': '21',
                'twentytwo': '22',
                'twentythree': '23'
            };
            const [start, end] = slot.split('_');
            const startNum = numberMap[start] || start;
            const endNum = numberMap[end] || end;
            return `${startNum} - ${endNum}`;
        }
        const timeSlotSequence = [
            'eight_nine', 'nine_ten', 'ten_eleven', 'eleven_twelve', 'twelve_thirteen',
            'thirteen_fourteen', 'fourteen_fifteen', 'fifteen_sixteen', 'sixteen_seventeen',
            'seventeen_eighteen', 'eighteen_nineteen', 'nineteen_twenty', 'twenty_twentyone',
            'twentyone_twentytwo', 'twentytwo_twentythree', 'twentythree_zero', 'zero_one',
            'one_two', 'two_three', 'three_four', 'four_five', 'five_six', 'six_seven', 'seven_eight'
        ];

        function getPrefixFromId(id) {
            if (id.startsWith('qty_')) return 'qty';
            if (id.startsWith('weight_')) return 'weight';
            return null;
        }

        function getTimeSlotFromId(id) {
            const parts = id.split('_');
            if (parts.length < 3) return null;
            return parts.slice(1, -1).join('_');
        }

        // Get the article ID from the input ID (e.g., "123" from "qty_eight_nine_123")
        function getArticleIdFromId(id) {
            const parts = id.split('_');
            if (parts.length < 3) return null;
            return parts[parts.length - 1];
        }

        // Find all inputs for a specific type (qty or weight) in a time slot
        function getInputsForTimeSlot(prefix, timeSlot) {
            return document.querySelectorAll(`input[id^="${prefix}_${timeSlot}_"]`);
        }

        // Check if a time slot has at least one filled input for qty or weight
        function isTimeSlotFilled(prefix, timeSlot) {
            const inputs = getInputsForTimeSlot(prefix, timeSlot);
            return Array.from(inputs).some(input => input.value.trim() !== '');
        }

        // Make sure all previous time slots are filled for this type
        function arePreviousTimeSlotsFilled(prefix, currentTimeSlot) {
            const currentIndex = timeSlotSequence.indexOf(currentTimeSlot);
            for (let i = 0; i < currentIndex; i++) {
                if (!isTimeSlotFilled(prefix, timeSlotSequence[i])) {
                    return false;
                }
            }
            return true;
        }

        // Hook up all qty and weight inputs with a click listener
        const inputs = document.querySelectorAll('input[id^="qty_"], input[id^="weight_"]');
        inputs.forEach(input => {
            input.addEventListener('click', function (e) {
                const prefix = getPrefixFromId(this.id);
                const timeSlot = getTimeSlotFromId(this.id);
                const articleId = getArticleIdFromId(this.id);

                if (!prefix || !timeSlot || !articleId) return;

                // ✅ Check only current time slot
                const inputsInSameSlot = getInputsForTimeSlot(prefix, timeSlot);
                const allInputsInThisSlot = [
                    ...getInputsForTimeSlot('qty', timeSlot),
                    ...getInputsForTimeSlot('weight', timeSlot)
                ];

                let differentArticleFound = false;

                for (const inp of allInputsInThisSlot) {
                    if (inp.value.trim() !== '') {
                        const filledArticleId = getArticleIdFromId(inp.id);
                        if (filledArticleId !== articleId) {
                            differentArticleFound = true;
                            break;
                        }
                    }
                }

                if (differentArticleFound) {
                    alert(`This time slot is already in use by another article. Please complete that first.`);
                    e.preventDefault();
                    this.value = '';
                    this.blur();
                    return;
                }

                // ✅ Time slot per type check (optional but included)
                const otherFilledInputs = Array.from(inputsInSameSlot).filter(input => {
                    const inputArticleId = getArticleIdFromId(input.id);
                    return input !== this && input.value.trim() !== '' && inputArticleId !== articleId;
                });

                if (otherFilledInputs.length > 0) {
                    alert(`Time slot already used. Only one article allowed`);
                    e.preventDefault();
                    this.value = '';
                    this.blur();
                    return;
                }

                // ✅ Earlier time slot logic remains as-is
                if (!arePreviousTimeSlotsFilled(prefix, timeSlot)) {
                    alert(`Please fill at least one ${prefix} field in all earlier time slots first.`);
                    e.preventDefault();
                    this.value = '';
                    this.blur();
                    return;
                }
            });
        });
    });
</script>
<!-- save button -->
<script>
    $(function () {
        $('#data8-9able tbody tr').each(function () {
            const $tr = $(this);
            const $saveBtn = $tr.find('button.table_btn').filter(function () {
                return $(this).text().trim().toLowerCase().startsWith('save');
            });
            $saveBtn.prop('disabled', true);

            $tr.find('input[id^="row_total_qty_"], select[id^="plant_manager_approval_status"], input[id^="remark_"]')
                .on('input change', function () {
                    const anyFilled = $tr.find('input[id^="row_total_qty_"]').filter(function () {
                        return $(this).val().trim() !== '';
                    }).length ||
                        $tr.find('select[id^="plant_manager_approval_status"]').filter(function () {
                            return $(this).val() !== '';
                        }).length ||
                        $tr.find('input[id^="remark_"]').filter(function () {
                            return $(this).val().trim() !== '';
                        }).length;

                    $saveBtn.prop('disabled', !anyFilled);
                });
        });
    });
</script>
<!-- for rejection -->
<script>
    $(function () {
        $('#data8-9able tbody tr').each(function () {
            const $tr = $(this);
            const $saveBtn = $tr.find('button.table_btn').filter(function () {
                return $(this).text().trim().toLowerCase() === 'save';
            });
            $saveBtn.prop('disabled', true);

            const $fields = $tr.find([
                'input[id^="total_qty_"]',
                'input[id^="pc_"]',
                'input[id^="runner_gms_"]',
                'input[id^="flash_gm_"]',
                'input[id^="lumps_gm_"]',
                'select[id^="plant_manager_approval_status_"]',
                'textarea[id^="remark_"]'
            ].join(', '));

            $fields.on('input change', function () {
                const anyFilled = $fields.toArray().some(el => {
                    const $el = $(el);
                    const val = $el.val();
                    return val !== '' && val !== null && val !== undefined;
                });

                $saveBtn.prop('disabled', !anyFilled);
            });
        });
    });
</script>
<!-- balance Quantity -->
<script>
    $(function () {
        $('#data-table tbody tr').each(function () {
            const $tr = $(this);
            const $saveBtn = $tr.find('button.table_btn').filter(function () {
                return $(this).text().trim().toLowerCase() === 'save';
            });
            $saveBtn.prop('disabled', true);

            const $fields = $tr.find([
                'input[id^="rm_total_qty_"]',
                'input[id^="mb_total_qty_"]',
                'select[id^="plant_manager_approval_status_"]',
                'textarea[id^="remark_"]'
            ].join(', '));

            $fields.on('input change', function () {
                const anyFilled = $fields.toArray().some(el => {
                    const v = $(el).val();
                    return v !== '' && v !== null && v !== undefined;
                });
                $saveBtn.prop('disabled', !anyFilled);
            });
        });
    });
</script>
<!-- production report list -->
<script>
    $(function () {
        $('#dataTable button.hourly-submit').prop('disabled', true);

        function toggleSubmitBtn(articleId) {
            const selector = '[id$="_' + articleId + '"]';
            const $fields = $('#dataTable').find(selector).filter('input, select, textarea');
            const anyFilled = $fields.toArray().some(el => {
                const v = $(el).val();
                return v !== '' && v !== null && v !== undefined;
            });
            const $btn = $('#dataTable button.hourly-submit').filter(function () {
                return $(this).attr('onclick').includes('(' + articleId + ')');
            });
            $btn.prop('disabled', !anyFilled);
        }
        $('#dataTable').on('input change', 'input, select, textarea', function () {
            const parts = this.id.split('_');
            const articleId = parts[parts.length - 1];
            if (articleId) toggleSubmitBtn(articleId);
        });
    });
</script>

<script>
    // function validateForm() {
    //     const forms = document.getElementsByClassName('production_form_list');
    //     let allFormsValid = true;
    //     const fileInput = document.getElementById('image_names');
    //     const files = fileInput.files;
    //     const hasAnyFile = files && files.length > 0;

    //     Array.from(forms).forEach(form => {
    //         const inputs = form.querySelectorAll('input[type="text"], input[type="file"]');
    //         let allFilled = true;

    //         inputs.forEach(input => {
    //             if (input.type === 'file' && hasAnyFile) {
    //                 return;
    //             }
    //             if (!input.value.trim()) {
    //                 allFilled = false;
    //             }
    //         });

    //         if (!allFilled) {
    //             allFormsValid = false;
    //         }
    //     });

    //     if (allFormsValid) {
    //         alert('Summary added successfully!');
    //         Array.from(forms).forEach(form => form.submit());
    //     } else {
    //         alert('Please fill in all the required fields.');
    //     }
    // }
    function validateForm() {
    const forms = document.getElementsByClassName('production_form_list');
    let allFormsValid = true;

    const fileInput = document.getElementById('image_names');
    const files = fileInput.files;
    const hasAnyFile = files && files.length > 0;
    const production_id = document.getElementById('production_id').value;

    // 🔹 Step 1: Validate all form text fields
    Array.from(forms).forEach(form => {
        const inputs = Array.from(form.querySelectorAll('input[type="text"]'))
            .filter(input => {
                return !(input.id.startsWith('qty_') || input.id.startsWith('weight_'));
            });

        let allFilled = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            allFormsValid = false;
        }
    });

    // 🔹 Step 2: If form fields are okay, now check image condition
    if (!allFormsValid) {
        alert('Please fill in all the required fields.');
        return false;
    }

    let imageCheckPassed = false;

    $.ajax({
        url: "<?= base_url() ?>admin/Ajax_controller/get_production_images",
        type: "POST",
        data: { production_id: production_id },
        async: false, // ⛔ Important: make it synchronous for validation
        success: function (response) {
            try {
                const res = JSON.parse(response);
                const hasExistingImages = res.data && res.data.length > 0;

                // ✅ If existing OR new images → pass
                if (hasExistingImages || hasAnyFile) {
                    imageCheckPassed = true;
                }
            } catch (e) {
                alert('Error checking existing images.');
            }
        },
        error: function () {
            alert('Server error while checking images.');
        }
    });

    if (!imageCheckPassed) {
        alert('Please upload at least one image.');
        return false;
    }
    alert('Record added successfully!');
}


</script>