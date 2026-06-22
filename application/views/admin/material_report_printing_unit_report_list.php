<?php include('header.php'); ?>

<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    .flex_wrap {
        display: flex;
        flex-wrap: wrap;
    }

    .select2-container {
        max-width: 100% !important;
    }

    .right_col .page_title,
    .right_col .page_body {
        padding: -1px 8px;
    }

    .page_sec {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        height: auto;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }

    .modelclass {
        max-width: 35%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }
</style>
<div class="right_col">
    <h3>Material Report List</h3>
    <div class="main_page">
        <div class="page_title">

        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Party Name</label>
                            <select class="form-control js-example-basic-multiple" name="party" id="party">
                                <option value="" selected disabled>Select Party</option>
                                <?php if (!empty($party)) : ?>
                                    <?php foreach ($party as $party_result) : ?>
                                        <option value="<?= $party_result->id ?>"
                                            <?= (isset($_GET['party']) && $_GET['party'] == $party_result->id) ? 'selected' : '' ?>>
                                            <?= $party_result->party_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Brand Name</label>
                            <select class="form-control js-example-basic-multiple" name="brand" id="brand">
                                <option value="" selected disabled>Select Brand</option>
                                <?php if (!empty($brand)) : ?>
                                    <?php foreach ($brand as $brand_result) : ?>
                                        <option value="<?= $brand_result->id ?>"
                                            <?= (isset($_GET['brand']) && $_GET['brand'] == $brand_result->id) ? 'selected' : '' ?>>
                                            <?= $brand_result->brand_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="article">Bucket Size</label>
                            <select class="form-control js-example-basic-multiple" name="article" id="article">
                                <option value="" selected disabled>Select Bucket Size</option>
                                <?php if (!empty($article)) : ?>
                                    <?php foreach ($article as $article_result) : ?>
                                        <option value="<?= $article_result->id ?>"
                                            <?= (isset($_GET['article']) && $_GET['article'] == $article_result->id) ? 'selected' : '' ?>>
                                            <?= $article_result->article_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <option value="0" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '0') { ?>selected="selected" <?php } ?>>Pending
                                    </option>
                                    <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '1') { ?>selected="selected" <?php } ?>>Completed
                                    </option>
                                    <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '2') { ?>selected="selected" <?php } ?>>Cancelled
                                    </option>
                                </select>
                            </div>
                        </div>


                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>material_report_printing_unit_report_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="x_panel">
                <table class="table" style="width: 100%;" id="example">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Created Date & Time</th>
                            <th>Order Status</th>
                            <th>Order ID</th>
                            <th>Party Name</th>
                            <th>Completed Days</th>
                            <th>Bucket Size</th>
                            <th>Order Qty</th>
                            <th>Approved Qty</th>
                            <th>Brand Name</th>
                            <th>Color Job</th>
                            <th>No of Inpression</th>
                            <th>Selected Inks</th>
                            <th>Selected Material</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Selected Inks</th>
                            <th>Quantitiy</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-table">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalOther" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel_other">Order Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Material 1</th>
                            <th>Quantitiy</th>
                            <th>Material 2</th>
                            <th>Quantitiy</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-table-order">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
                                                                    echo $_GET['date'];
                                                                } ?>">

<input type="hidden" name="brand_action" id="brand_action" value="<?php if (isset($_GET['brand'])) {
                                                                        echo $_GET['brand'];
                                                                    } ?>">
<input type="hidden" name="article_action" id="article_action" value="<?php if (isset($_GET['article'])) {
                                                                            echo $_GET['article'];
                                                                        } ?>">
<input type="hidden" name="order_status_action" id="order_status_action" value="<?php if (isset($_GET['order_status'])) {
                                                                                    echo $_GET['order_status'];
                                                                                } ?>">
<input type="hidden" name="party_action" id="party_action" value="<?php if (isset($_GET['party'])) {
                                                                        echo $_GET['party'];
                                                                    } ?>">

<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
       
        // $('#printing_unit .child_menu').show();
        $('#printing_unit').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.material_report_printing_unit_report_list').addClass('active_cc');
        // $('#printing_unit').addClass('nv active-color');
    });
</script>
<script>
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });
        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
    $(".datepickers").flatpickr({
        mode: "range",
        dateFormat: "d-m-Y",
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "scrollX": true,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                title: 'Material Report List',
                footer: true,
                filename: 'Material_repot_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8,9,10, 11,14]
                }
            }],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            //scrollX: true, 
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_printing_material_details",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.brand_action = $('#brand_action').val();
                    data.article_action = $('#article_action').val();
                    data.order_status_action = $('#order_status_action').val();
                    data.party_id = $('#party_action').val();
                },
            },
            "createdRow": function(row, data, dataIndex) {
                var memberId = data[15];
                var eyeButton = `
                    <button type="button" class="btn btn-info" onclick="showOrderDetails('${memberId}')" title="Selected INK">
                        <i class="fa fa-eye"></i>
                    </button>
                `;
                $('td', row).eq(12).html(eyeButton);
                var eyeButtonn = `
                    <button type="button" class="btn btn-info" onclick="showOrderMaterialDetails('${memberId}')" title="Other Material">
                        <i class="fa fa-eye"></i>
                    </button>
                `;
                $('td', row).eq(13).html(eyeButtonn);
            },
            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function showOrderDetails(report_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_ink_data_by_order_id") ?>',
            type: 'POST',
            data: {
                'report_id': report_id
            },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#order-details-table').empty();
                    var tableContent = '';
                    response.forEach(function(item, index) {
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.rm_name}</td>`;
                        tableContent += `<td>${item.quantity}</td>`;
                        tableContent += `</tr>`;
                    });
                    $('#order-details-table').html(tableContent);
                    $('#exampleModal').modal('show');
                } else {
                    alert('No Ink Found!');
                }
            },
        });
    }

    function showOrderMaterialDetails(report_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_other_material_order_id") ?>',
            type: 'POST',
            data: {
                'report_id': report_id
            },
            dataType: 'json',
            success: function(response) {
                if (response && !response.error) {
                    $('#order-details-table-order').empty();

                    function handleEmpty(value) {
                        return value ? value : '-';
                    }
                    var tableContent = '';
                    tableContent += `<tr>`;
                    tableContent += `<td>1</td>`;
                    tableContent += `<td>${handleEmpty(response.six)}</td>`;
                    tableContent += `<td>${handleEmpty(response.other_material_qty_1)}</td>`;
                    tableContent += `<td>${handleEmpty(response.seven)}</td>`;
                    tableContent += `<td>${handleEmpty(response.other_material_qty_2)}</td>`;
                    tableContent += `</tr>`;

                    $('#order-details-table-order').html(tableContent);
                    $('#exampleModalOther').modal('show');
                } else {
                    alert('No Material Found!');
                }
            },
            error: function() {
                alert('An error occurred while fetching data.');
            }
        });
    }
</script>