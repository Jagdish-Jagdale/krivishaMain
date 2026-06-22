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

    .modelclass {
        max-width: 50%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
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

    #example_wrapper .dt-buttons {
        float: right;
        margin-left: 10px;
    }

    #example_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }

    #example_wrapper .dt-buttons .dt-button,
    #example_wrapper .dt-buttons .buttons-csv,
    #example_wrapper .dt-buttons .buttons-csvHtml5,
    #example_wrapper .dt-buttons .buttons-pdf,
    #example_wrapper .dt-buttons .buttons-pdfHtml5 {
        background: #dc3545 !important;
        background-image: none !important;
        border: 1px solid #dc3545 !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    #example_wrapper .dt-buttons .dt-button:hover,
    #example_wrapper .dt-buttons .buttons-csv:hover,
    #example_wrapper .dt-buttons .buttons-csvHtml5:hover,
    #example_wrapper .dt-buttons .buttons-pdf:hover,
    #example_wrapper .dt-buttons .buttons-pdfHtml5:hover {
        background: #c82333 !important;
        background-image: none !important;
        border-color: #bd2130 !important;
        color: #fff !important;
    }
</style>
<div class="right_col">
    <h3>Order History</h3>
    <div class="main_page">
        <div class="page_title">

        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap gy-3">
                        <!-- Date Filter -->
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
                            <label for="date">Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>

                        <!-- Transport Filter -->
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
                            <label for="transport">Transport Name</label>
                            <select class="form-control js-example-basic-multiple" name="transport" id="transport">
                                <option value="" selected disabled>Select Transport</option>
                                <?php if (!empty($transport)) : ?>
                                    <?php foreach ($transport as $transport_result) : ?>
                                        <option value="<?= $transport_result->id ?>"
                                            <?= (isset($_GET['transport']) && $_GET['transport'] == $transport_result->id) ? 'selected' : '' ?>>
                                            <?= $transport_result->transport_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Division Filter -->
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
                            <label for="division">Division</label>
                            <select class="form-control js-example-basic-multiple" name="division" id="division">
                                <option value="" selected disabled>Select Division</option>
                                <option value="1" <?= (isset($_GET['division']) && $_GET['division'] == '1') ? 'selected' : '' ?>>
                                    Household
                                </option>
                                <option value="2" <?= (isset($_GET['division']) && $_GET['division'] == '2') ? 'selected' : '' ?>>
                                    Container
                                </option>
                                <option value="3" <?= (isset($_GET['division']) && $_GET['division'] == '3') ? 'selected' : '' ?>>
                                    Both
                                </option>
                            </select>
                        </div>

                        <!-- Payment Status Filter -->
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 ">
                            <label for="payment_status">Freight Payment Status</label>
                            <select class="form-control js-example-basic-multiple" name="payment_status" id="payment_status">
                                <option value="" selected disabled>Select Payment Status</option>
                                <option value="1" <?= (isset($_GET['payment_status']) && $_GET['payment_status'] == '1') ? 'selected' : '' ?>>
                                    Pay
                                </option>
                                <option value="2" <?= (isset($_GET['payment_status']) && $_GET['payment_status'] == '2') ? 'selected' : '' ?>>
                                    Paid
                                </option>
                            </select>
                        </div>


                        <!-- Submit and Reset Buttons -->
                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>outward_transport_history/<?= $this->uri->segment(2) ?>/<?= $this->uri->segment(3) ?>" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>


            <div class="x_panel">
                <table class="table" id="example">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Order ID</th>
                            <th>Created Date & Time</th>
                            <th>Party</th>
                            <th>Division</th>
                            <th>DC No</th>
                            <th>Invoice No</th>
                            <th>Invoice Value</th>
                            <th>Freight Amount</th>
                            <th>Location</th>
                            <th>Pincode</th>
                            <th>Transport</th>
                            <th>Dispatch Details</th>
                            <th>Bundle / Bag</th>
                            <th>Vehicle</th>
                            <th>Vehicle No</th>
                            <th>Driver Name</th>
                            <th>Driver Mobile</th>
                            <th>Freight Payment Status</th>
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
                <h5 class="modal-title" id="exampleModalLabel">Dispatch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Type Of Article</th>
                            <th class="brand d-none">Selected Brand</th>
                            <th>Order Quantity</th>
                            <th>Bundle / Bag</th>
                            <th class="brand d-none">Approved Qty</th>
                            <th>Dispatch Quantity</th>
                            <th>Remaining Quantity</th>
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
<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#maintenance').addClass('nv active-color');
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
            "scrollX": true,
            "cache": false,
            dom: "Blfrtip",
            ordering: false,
            // scrollCollapse: true,
            // scrollX: true,
            dom: "Blfrtip",
            buttons: [{
                extend: 'pdfHtml5',
                text: 'Download',
                footer: true,
                title: 'Order Details',
                filename: 'outward_order_details',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 14, 15, 16, 17, 18, 19]
                }
            }],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            ajax: {
                url: "<?= base_url('admin/Ajax_controller/get_outward_order_log_details') ?>",
                type: "POST",
                data: function(d) {
                    d.order_id = "<?= $this->uri->segment(3); ?>";
                    d.search_date = $('#date').val();
                    d.transport_action = $('#transport').val();
                    d.payment_status_action = $('#payment_status').val();
                    d.division_action = $('#division').val();
                }
            },
            createdRow: function(row, data, dataIndex) {
                var order = data[12];
                var eyeButton = `
                    <button type="button" class="btn btn-info" onclick="showOrderDetails('${order}')" title="Dispatch Article">
                        <i class="fa fa-eye"></i>
                    </button>
                `;
                $('td', row).eq(12).html(eyeButton);
            },
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            },
            cache: false
        });
        $('#maintenance_list').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });

    function showOrderDetails(dispatch_id) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_outward_dispatch_details") ?>',
            type: 'POST',
            data: {
                'dispatch_id': dispatch_id
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
                        const approved_qty = item.approved_quantity ? item.approved_quantity : '0';
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.article_name}</td>`;

                        tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                        tableContent += `<td>${item.order_quantity}</td>`;
                        tableContent += `<td>${item.bundle_bag_qty ? item.bundle_bag_qty : '0'}</td>`;
                        tableContent += `<td class = "brand d-none">${approved_qty}</td>`;
                        tableContent += `<td>${item.dispatch_quantity}</td>`;
                        tableContent += `<td>${item.remaining_quantity}</td>`;
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