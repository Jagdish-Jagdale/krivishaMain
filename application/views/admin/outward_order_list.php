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
    <h3>Outward Order List</h3>
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <option value="9" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '9') { ?>selected="selected" <?php } ?>>Pending Dispatch
                                    </option>
                                    <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '3') { ?>selected="selected" <?php } ?>>Partially Dispatched
                                    </option>
                                    <option value="4" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '4') { ?>selected="selected" <?php } ?>>Full Dispatched
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>outward_order_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Task ID</th>
                            <th>Party Name</th>
                            <th>Order Date</th>
                            <th>Forwarded Date</th>
                            <th>Details Of Task</th>
                            <th>Delay Day's</th>
                            <th>Task Status</th>
                            <th>Remark</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
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
                                    <th>Remark</th>
                                    <th>Order Status</th>
                                </tr>
                            </thead>
                            <tbody id="order-details-table">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="downloadLogisticsPdf()">
                            <i class="fa fa-file-pdf"></i> Download PDF
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printLogisticsPdf()">
                            <i class="fa fa-print"></i> Print PDF
                        </button>
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
        $('.outward_order_list').addClass('active_cc');
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
            "scrollX": true,
            dom: "lfrtip",
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'auto_task_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            }],
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 4 },
                { data: 5 },
                { data: null, orderable: false, searchable: false, defaultContent: '' },
                { data: 6 },
                { data: 10 },
                { data: 11 },
                { data: 12, orderable: false, searchable: false }
            ],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_outward_order_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.party_action = $('#party_action').val();
                    data.order_status_action = $('#order_status_action').val();
                    data.division_action = $('#division_action').val();
                },
            },
            "createdRow": function(row, data, dataIndex) {
                var order = data[1];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${order}')" title="Details Of Order">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(5).html(eyeButton);

            },

            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#maintenance_list').on('submit', function(e) {
            e.preventDefault();
            $('#search_date').val($('#date').val());
            $('#party_action').val($('#party').val());
            $('#order_status_action').val($('#order_status').val());
            $('#division_action').val($('#division').val());
            table.ajax.reload();
        });


        $(document).on('change', '.update-task-remark', function() {
            var id = $(this).data('id');
            var remark = $(this).val();
            
            $.ajax({
                url: "<?= base_url('admin/Ajax_controller/update_task_remark') ?>",
                type: "POST",
                data: { id: id, remark: remark },
                success: function(response) {
                    var res = JSON.parse(response);
                    if(res.status == 'success') {
                        // Optional: Add a subtle toast or visual feedback here
                        console.log("Remark updated successfully");
                    }
                }
            });
        });
    });

    var currentLogisticsPdfData = [];
    var currentLogisticsOrderId = '';

    function mapTaskStatus(statusValue) {
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
            return 'Pending Dispatch';
        }

        return 'Pending';
    }

    function buildLogisticsPdfDefinition(mode) {
        var bodyRows = [[
            'SR. NO.',
            'Order ID',
            'Group Of Article',
            'Type Of Article',
            'Selected Brand',
            'Order Qty',
            'Bundle / Bag',
            'Approved Qty',
            'Pending Qty',
            'Dispatched Qty',
            'Remark',
            'Order Status'
        ]];

        currentLogisticsPdfData.forEach(function(item, index) {
            bodyRows.push([
                (index + 1).toString(),
                item.order_id ? item.order_id.toString() : '',
                item.group_of_article ? item.group_of_article.toString() : '',
                item.article_name ? item.article_name.toString() : '',
                item.brand_name ? item.brand_name.toString() : '',
                item.order_quantity ? item.order_quantity.toString() : '0',
                item.bundle_bag_qty ? item.bundle_bag_qty.toString() : '0',
                item.approved_qty ? item.approved_qty.toString() : '0',
                item.pending_qty ? item.pending_qty.toString() : '0',
                item.dispatch_quantity ? item.dispatch_quantity.toString() : '0',
                item.remark ? item.remark.toString() : '',
                mapTaskStatus(item.order_status)
            ]);
        });

        return {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            pageMargins: [20, 30, 20, 30],
            content: [
                { text: 'Logistics Task Details - ' + currentLogisticsOrderId, style: 'header', margin: [0, 0, 0, 10] },
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
    }

    function downloadLogisticsPdf() {
        if (!currentLogisticsPdfData || currentLogisticsPdfData.length === 0) {
            alert('No details available to download.');
            return;
        }

        pdfMake.createPdf(buildLogisticsPdfDefinition('download')).download(currentLogisticsOrderId + '_logistics_details.pdf');
    }

    function printLogisticsPdf() {
        if (!currentLogisticsPdfData || currentLogisticsPdfData.length === 0) {
            alert('No details available to print.');
            return;
        }

        pdfMake.createPdf(buildLogisticsPdfDefinition('print')).print();
    }

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

                    currentLogisticsOrderId = order;
                    currentLogisticsPdfData = response;

                    $('#order-details-table').empty();
                    var tableContent = '';
                    var hasBrand = false;
                    response.forEach(function(item, index) {
                        if (item.brand_name && item.brand_name.trim() !== '') {
                            hasBrand = true;
                        }
                        item.order_status = mapTaskStatus(item.order_status);
                        const approved_qty = item.approved_qty ? item.approved_qty : '0';
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.order_id}</td>`;
                        tableContent += `<td>${item.group_of_article}</td>`;
                        tableContent += `<td>${item.article_name}</td>`;
                        tableContent += `<td class = "brand d-none">${item.brand_name}</td>`;
                        tableContent += `<td>${item.order_quantity}</td>`;
                        tableContent += `<td>${item.bundle_bag_qty ? item.bundle_bag_qty : '0'}</td>`;
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