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
        width: 100% !important;
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
    <h3>Order List</h3>
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
                            <div class="form-group">
                                <label>Order Type</label>
                                <select class="form-control js-example-basic-multiple" name="division" id="division">
                                    <option value="" selected disabled>Select Order Type</option>
                                    <option value="1" <?php if (isset($_GET['division']) && $_GET['division'] == '1') { ?>selected="selected" <?php } ?>>Household
                                    </option>
                                    <option value="2" <?php if (isset($_GET['division']) && $_GET['division'] == '2') { ?>selected="selected" <?php } ?>>Container
                                    </option>
                                    <option value="3" <?php if (isset($_GET['division']) && $_GET['division'] == '3') { ?>selected="selected" <?php } ?>>Both
                                    </option>
                                   
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <
                                     <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '1') { ?>selected="selected" <?php } ?>>Pending
                                    </option>
                                    <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '2') { ?>selected="selected" <?php } ?>>Processed to Account
                                    </option>
                                    <option value="7" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '7') { ?>selected="selected" <?php } ?>>Printing Inprocess
                                    </option>
                                    <option value="8" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '8') { ?>selected="selected" <?php } ?>>Printing Completed
                                    </option>
                                    <option value="9" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '9') { ?>selected="selected" <?php } ?>>Dispatch Inprocess
                                    </option>
                                    <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '3') { ?>selected="selected" <?php } ?>>Partially Dispatched
                                    </option>
                                    <option value="4" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '4') { ?>selected="selected" <?php } ?>>Full Dispatched
                                    </option>
                                    <option value="5" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '5') { ?>selected="selected" <?php } ?>>Order Closed
                                    </option>
                                    <option value="6" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '6') { ?>selected="selected" <?php } ?>>Order Cancelled
                                    </option> 
                                    
                                   
                                </select>
                            </div>
                        </div>
             
                    <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                        <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="<?= base_url() ?>order_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                    </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <div class="x_content class_style">
                    <div class="container">
                        <div class="modal fade" id="nextPrintingModal" tabindex="-1"
                            aria-labelledby="nextPrintingModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="nextPrintingModalLabel">Proceed Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="printingModalMessage">
                                        <!-- Message will be inserted here dynamically -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="#" id="proceedBtn" class="btn btn-primary">Proceed</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="nextPrintingModal" tabindex="-1"
                            aria-labelledby="nextAccountModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="nextAccountModalLabel">Proceed to Printing</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Do you want to proceed the Printing?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <a href="<?= base_url() ?>order_list" id="proceedBtn" name="proceedBtn"
                                            class="btn btn-primary">Proceed</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Order ID</th>
                                    <th>Order Date</th>
                                    <th>Party Name</th>
                                    <th>Selected Order Type</th>
                                    <th>Order Details</th>
                                    <th>Container Type</th>
                                    <th>Dispatch Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
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
                            <th>Bundle / Bag</th>
                            <th class="brand d-none">Approved Qty</th>
                            <th>Pending Qty</th>
                            <th>Dispatched Qty</th>
                            <th>Dispatch Date</th>
                            <th>Remark</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-table">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="generateOrderPdf($('#exampleModal').data('order-id'), $('#exampleModal').data('order-type'), 'download')">
                            <i class="fa fa-file-pdf"></i> Download PDF
                        </button>
                        <button type="button" class="btn btn-primary" onclick="generateOrderPdf($('#exampleModal').data('order-id'), $('#exampleModal').data('order-type'), 'print')">
                            <i class="fa fa-print"></i> Print PDF
                        </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
<input type="hidden" name="order_status_action" id="order_status_action" value="<?php if (isset($_GET['order_status'])) {
    echo $_GET['order_status'];
} ?>">
<input type="hidden" name="division_action" id="division_action" value="<?php if (isset($_GET['division'])) {
    echo $_GET['division'];
} ?>">
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
       
        // $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.order_list').addClass('active_cc');
        // $('#task_management').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });
    $(document).ready(function () {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });
        $('#daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });
        $('#daterange').on('cancel.daterangepicker', function (ev, picker) {
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
</script>
<script>
    $(document).ready(function () {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "scrollX":true,
            "cache": false,
            dom: "lfrtip",
            ordering: false,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_order_list",
                "type": "POST",
                "data": function (data) {
                    data.search_date = $('#search_date').val();
                    data.party_action = $('#party_action').val();
                    data.order_status_action = $('#order_status_action').val();
                    data.division_action = $('#division_action').val();
                },
            },
            columnDefs: [
                {
                    targets: '_all',
                    className: 'tbl-min-width'
                },
                {
                    targets: 7,
                    render: function (data, type, row) {
                        if (type === 'display' && data) {
                            // Split comma-separated dates and show one per line
                            return data.split(',').map(function (d) {
                                return d.trim();
                            }).join('<br>');
                        }
                        return data ? data : '';
                    }
                }
            ],
            "createdRow": function (row, data, dataIndex) {
                var memberId = data[5];
                var order = data[1];
                var order_type = data[4];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${order}', '${order_type}')" title="Group Of Article">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(5).html(eyeButton);

            },

            "drawCallback": function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
    function showOrderDetails(order, order_type) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_sub_order_details") ?>',
            type: 'POST',
            data: { 'order_id': order, 'order_type': order_type },
            dataType: 'json',
            success: function (response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#exampleModal').data('order-id', order);
                    $('#exampleModal').data('order-type', order_type);
                    $('#order-details-table').empty();
                    var tableContent = '';
                    response.forEach(function (item, index) {
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
                        }else if (item.order_status == '7') {
                            item.order_status = 'Printing Inprocess';
                        }else if (item.order_status == '8') {
                            item.order_status = 'Printing Completed';
                        }else if (item.order_status == '9') {
                            item.order_status = 'Dispatch Inprocess';
                        }else if (item.order_status == '10') {
                            item.order_status = 'Manually Closed';
                        }else{
                            item.order_status = 'Pending';
                        }
                        const approved_qty = item.approved_qty ? item.approved_qty : '0';
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.order_id}</td>`;
                        tableContent += `<td>${item.group_of_article}</td>`;
                        tableContent += `<td>${item.article_name}</td>`;
                        tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                        tableContent += `<td>${item.order_quantity}</td>`;
                        tableContent += `<td>${item.bundle_bag_qty ? item.bundle_bag_qty : ''}</td>`;
                        tableContent += `<td class = "brand d-none">${approved_qty}</td>`;
                        tableContent += `<td>${item.pending_qty}</td>`;
                        tableContent += `<td>${item.dispatch_quantity}</td>`;
                        tableContent += `<td>${item.dispatch_dates ? item.dispatch_dates : ''}</td>`;
                        tableContent += `<td>${item.remark}</td>`;
                        tableContent += `<td>${item.order_status}</td>`;
                        tableContent += `</tr>`;
                    });
                    $('#order-details-table').html(tableContent);

                    $('#exampleModal').modal('show');
                } else {
                    alert('No details found for this order!');
                }
                if (order_type == 'Container') {
                    $('.brand').removeClass('d-none');
                } else {
                    $('.brand').addClass('d-none');
                }

            },
        });
    }

    function mapOrderStatus(statusValue) {
        if (statusValue == '0') {
            return 'Pending';
        } else if (statusValue == '1') {
            return 'Printing Completed';
        } else if (statusValue == '2') {
            return 'Cancelled';
        } else if (statusValue == '3') {
            return 'Partially Dispatched';
        } else if (statusValue == '4') {
            return 'Fully Dispatched';
        } else if (statusValue == '7') {
            return 'Printing Inprocess';
        } else if (statusValue == '8') {
            return 'Printing Completed';
        } else if (statusValue == '9') {
            return 'Dispatch Inprocess';
        } else if (statusValue == '10') {
            return 'Manually Closed';
        }

        return 'Pending';
    }

    function generateOrderPdf(order, order_type, mode) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_sub_order_details") ?>',
            type: 'POST',
            data: { 'order_id': order, 'order_type': order_type },
            dataType: 'json',
            success: function (response) {
                if (!Array.isArray(response) || response.length === 0) {
                    alert('No details found for this order!');
                    return;
                }

                var isContainerOrder = (order_type === 'Container' || order_type === '2');
                var headerRow = [
                    'SR. NO.',
                    'Order ID',
                    'Group Of Article',
                    'Type Of Article'
                ];

                if (isContainerOrder) {
                    headerRow.push('Selected Brand');
                }

                headerRow.push('Order Qty');
                headerRow.push('Bundle / Bag');
                if (isContainerOrder) {
                    headerRow.push('Approved Qty');
                }
                headerRow.push('Pending Qty');
                headerRow.push('Dispatched Qty');
                headerRow.push('Dispatch Date');
                headerRow.push('Remark');
                headerRow.push('Order Status');

                var bodyRows = [headerRow];

                response.forEach(function (item, index) {
                    var row = [
                        (index + 1).toString(),
                        item.order_id ? item.order_id.toString() : '',
                        item.group_of_article ? item.group_of_article.toString() : '',
                        item.article_name ? item.article_name.toString() : ''
                    ];

                    if (isContainerOrder) {
                        row.push(item.brand_name ? item.brand_name.toString() : '');
                    }

                    row.push(item.order_quantity ? item.order_quantity.toString() : '0');
                    row.push(item.bundle_bag_qty ? item.bundle_bag_qty.toString() : '');
                    if (isContainerOrder) {
                        row.push(item.approved_qty ? item.approved_qty.toString() : '0');
                    }
                    row.push(item.pending_qty ? item.pending_qty.toString() : '0');
                    row.push(item.dispatch_quantity ? item.dispatch_quantity.toString() : '0');
                    row.push(item.dispatch_dates ? item.dispatch_dates.toString() : '');
                    row.push(item.remark ? item.remark.toString() : '');
                    row.push(mapOrderStatus(item.order_status));

                    bodyRows.push(row);
                });

                var docDefinition = {
                    pageOrientation: 'landscape',
                    pageSize: 'A4',
                    pageMargins: [20, 30, 20, 30],
                    content: [
                        { text: 'Order Details - ' + order, style: 'header', margin: [0, 0, 0, 10] },
                        { text: 'Generated On: ' + new Date().toLocaleString(), style: 'meta', margin: [0, 0, 0, 10] },
                        {
                            table: {
                                headerRows: 1,
                                body: bodyRows
                            },
                            layout: 'lightHorizontalLines'
                        }
                    ],
                    styles: {
                        header: {
                            fontSize: 14,
                            bold: true
                        },
                        meta: {
                            fontSize: 9,
                            color: '#555555'
                        }
                    },
                    defaultStyle: {
                        fontSize: 8
                    }
                };

                if (mode === 'print') {
                    pdfMake.createPdf(docDefinition).print();
                } else {
                    pdfMake.createPdf(docDefinition).download(order + '_details.pdf');
                }
            },
            error: function () {
                alert('Unable to generate PDF right now. Please try again.');
            }
        });
    }
    function proceed_Account(Id, inkType, order_type, party_id) {
        
        $('#proceedBtn').data('order-id', Id);
        $('#proceedBtn').data('status', inkType);
        $('#proceedBtn').data('order_type', order_type);
        $('#proceedBtn').data('party_id', party_id);
        $('#proceedBtn').data('action', 'account');
        // $('#order-type').val(order_type);
        // $('#party-id').val(party_id);
       const msg = 'Are you sure you want to process this order to Account?';
        $('#printingModalMessage').text(msg);
    }
    
    function proceed_Logistics(Id, inkType, order_type, party_id) {
        $('#proceedBtn').data('order-id', Id);
        $('#proceedBtn').data('status', inkType);
        $('#proceedBtn').data('order_type', order_type);
        $('#proceedBtn').data('party_id', party_id);
        $('#proceedBtn').data('action', 'logistics');
        const msg = 'Are you sure you want to proceed this order to Logistics?';
        $('#printingModalMessage').text(msg);
    }

    function proceed_Printing(Id, inkType, order_type, party_id) {
        $('#proceedBtn').data('order-id', Id);
        $('#proceedBtn').data('status', inkType);
        $('#proceedBtn').data('order_type', order_type);
        $('#proceedBtn').data('party_id', party_id);
        $('#proceedBtn').data('action', 'printing');
        const msg = 'Are you sure you want to proceed this order to Printing?';
        $('#printingModalMessage').text(msg);
    }

    $(document).ready(function () {
        $('#proceedBtn').on('click', function (e) {
            e.preventDefault();
            const Id = $(this).data('order-id');
            const val = $(this).data('status');
            const order_type = $(this).data('order_type');
            const party_id = $(this).data('party_id');
            const action = $(this).data('action');
           
            if (action === 'logistics') {
                proceedToLogistics(Id, val, order_type, party_id);
            } else if (action === 'printing') {
                proceedToPrinting(Id, val, order_type, party_id);
            } else {
                proceedToAccounts(Id, val, order_type, party_id);
            }
        });
    });

    function proceedToLogistics(Id, val, order_type, party_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/set_order_status_logistics") ?>',
            type: 'POST',
            data: { 'id': Id, 'status': val, 'order_type': order_type, 'party_id': party_id },
            dataType: 'json',
            success: function (response) {
                if (response.status === '1') {
                    location.reload();
                } else {
                    location.reload();
                }
            },
            error: function () {
                alert("AJAX error occurred.");
            }
        });
    }

    function proceedToPrinting(Id, val, order_type, party_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/set_order_status_printing") ?>',
            type: 'POST',
            data: { 'id': Id, 'status': val, 'order_type': order_type, 'party_id': party_id },
            dataType: 'json',
            success: function (response) {
                if (response.status === '1') {
                    location.reload();
                } else {
                    location.reload();
                }
            },
            error: function () {
                alert("AJAX error occurred.");
            }
        });
    }

    function proceedToAccounts(Id, val, order_type, party_id) {
		// alert('Yes Calling');
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/set_order_status") ?>',
            type: 'POST',
            data: { 'id': Id, 'status': val, 'order_type': order_type, 'party_id': party_id },
            dataType: 'json',
            success: function (response) {
                if (response.status === '1') {
                    location.reload();
                } else {
                    location.reload();
                }
            },
            error: function () {
                alert("AJAX error occurred.");
            }
        });
    }






</script>