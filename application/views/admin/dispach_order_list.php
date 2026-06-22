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

    .modelclass {
        max-width: 60%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }
</style>
<div class="right_col">
    <h3>Dispatched Orders</h3>
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
                            <label>Salesman</label>
                            <select class="form-control js-example-basic-multiple" name="salesman" id="salesman">
                                <option value="" selected disabled>Select Salesman</option>
                                <?php if (!empty($salesman)) : ?>
                                    <?php foreach ($salesman as $salesman_result) : ?>
                                        <option value="<?= $salesman_result->id ?>"
                                            <?= (isset($_GET['salesman']) && $_GET['salesman'] == $salesman_result->id) ? 'selected' : '' ?>>
                                            <?= $salesman_result->first_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Order ID</label>
                            <input type="text" name="order_id" id="order_id" class="form-control" placeholder="Enter Order ID"
                                value="<?php if (isset($_GET['order_id']) && $_GET['order_id'] != '') { echo $_GET['order_id']; } ?>">
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Division</label>
                                <select class="form-control js-example-basic-multiple" name="division" id="division">
                                    <option value="" selected disabled>Select Division</option>
                                    <option value="1" <?php if (isset($_GET['division']) && $_GET['division'] == '1') { ?>selected="selected" <?php } ?>>Household
                                    </option>
                                    <option value="2" <?php if (isset($_GET['division']) && $_GET['division'] == '2') { ?>selected="selected" <?php } ?>>Container
                                    </option>
                                    <option value="3" <?php if (isset($_GET['division']) && $_GET['division'] == '3') { ?>selected="selected" <?php } ?>>Both
                                    </option>

                                </select>
                            </div>
                        </div>



                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>dispach_order_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Order ID</th>
                            <th>Party Name</th>
                            <th>Salesman</th>
                            <th>Order Date</th>
                            <th>Forwarded Date</th>
                            <th>Delay Days</th>
                            <th>Transport Name</th>
                            <th>Total Bundel</th>
                            <th>Details Of Task</th>
                            <th>Task Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>


                </table>
            </div>
        </div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
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
                                    <th>Order ID</th>
                                    <th>Group Of Article</th>
                                    <th>Type Of Article</th>
                                    <th class="brand d-none">Selected Brand</th>
                                    <th>Order Qty</th>
                                    <th class="brand d-none">Approved Qty</th>
                                    <th>Pending Qty</th>
                                    <th>Dispatched Qty</th>
                                    <th>Remark</th>
                                    <th>Order Status</th>
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
    </div>
</div>
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
                                                                    echo $_GET['date'];
                                                                } ?>">

<input type="hidden" name="party_action" id="party_action" value="<?php if (isset($_GET['party'])) {
                                                                        echo $_GET['party'];
                                                                    } ?>">
<input type="hidden" name="salesman_action" id="salesman_action" value="<?php if (isset($_GET['salesman'])) {
                                                                            echo $_GET['salesman'];
                                                                        } ?>">
<input type="hidden" name="order_id_action" id="order_id_action" value="<?php if (isset($_GET['order_id'])) {
                                                                            echo $_GET['order_id'];
                                                                        } ?>">
<input type="hidden" name="order_status_action" id="order_status_action" value="<?php if (isset($_GET['order_status'])) {
                                                                                    echo $_GET['order_status'];
                                                                                } ?>">
<input type="hidden" name="division_action" id="division_action" value="<?php if (isset($_GET['division'])) {
                                                                            echo $_GET['division'];
                                                                        } ?>">

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#logistics').addClass('nv active');
        $('.dispach_order_list').addClass('active_cc');
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
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            dom: "lfrtip",
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'auto_task_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 10]
                }
            }],
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 }, // Salesman
                { data: 4 }, // Order Date
                { data: 5 }, // Forwarded Date
                { data: 6 }, // Delay Days
                { data: 7 }, // Transport Name
                { data: 8 }, // Total Bundel
                { data: null, orderable: false, searchable: false, defaultContent: '' }, // Details Of Task
                { data: 10 }, // Task Status
                { data: 12, orderable: false, searchable: false } // Action
            ],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_outward_order_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.party_action = $('#party_action').val();
                    data.salesman_action = $('#salesman_action').val();
                    data.order_id_action = $('#order_id_action').val();
                    data.order_status_action = $('#order_status_action').val();
                    data.division_action = $('#division_action').val();
                    data.final_status = '1'; //completed
                },
            },
            "createdRow": function(row, data, dataIndex) {
                var order = data[1];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${order}')" title="Details Of Order">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(9).html(eyeButton);

            },

            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function showOrderDetails(order) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_outward_sub_order_details") ?>',
            type: 'POST',
            data: {
                'order_id': order
            },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response) && response.length > 0) {

                    $('#order-details-table').empty();
                    var tableContent = '';
                    var hasBrand = false;
                    response.forEach(function(item, index) {
                        if (item.brand_name && item.brand_name.trim() !== '') {
                            hasBrand = true;
                        }
                        if (item.order_status == '0') {
                            item.order_status = 'Pending';
                        } else if (item.order_status == '1') {
                            item.order_status = 'Printing Completed';
                        } else if (item.order_status == '2') {
                            item.order_status = 'Cancelled';
                        } else if (item.order_status == '3') {
                            item.order_status = 'Partially Dispatched';
                        } else if (item.order_status == '4') {
                            item.order_status = 'Fully Dispatched';
                        } else {
                            item.order_status = 'In Account Department';
                        }
                        const approved_qty = item.approved_qty ? item.approved_qty : '0';
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.order_id}</td>`;
                        tableContent += `<td>${item.group_of_article}</td>`;
                        tableContent += `<td>${item.article_name}</td>`;
                        tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                        tableContent += `<td>${item.order_quantity}</td>`;
                        tableContent += `<td class = "brand d-none">${approved_qty}</td>`;
                        tableContent += `<td>${item.pending_qty}</td>`;
                        tableContent += `<td>${item.dispatch_quantity}</td>`;
                        tableContent += `<td>${item.remark}</td>`;
                        tableContent += `<td>${item.order_status}</td>`;
                        tableContent += `</tr>`;
                    });

                    $('#order-details-table').html(tableContent);

                    $('#exampleModal').modal('show');
                } else {
                    alert('No details found for this order!');
                }
                if (hasBrand) {
                    $('.brand').removeClass('d-none');
                } else {
                    $('.brand').addClass('d-none');
                }
            },
        });
    }
</script>