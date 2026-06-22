<?php include('header.php'); ?>

<div class="right_col">
    <div class="page_title">
        <div class="title_left">
            <h3>Transport Order Details</h3>
        </div>
        <div class="title_right">
            <a href="<?= base_url('transport_report') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Report
            </a>
            <button onclick="window.print()" class="btn btn-info btn-sm">
                <i class="fa fa-file-pdf"></i> Download PDF
            </button>
            <button onclick="window.print()" class="btn btn-success btn-sm">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="page_body">
        <?php if(!empty($order)): ?>
            <!-- Order Summary Section -->
            <div class="page_sec">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Order Information</h4>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>Order ID:</strong></td>
                                <td><?= $order->order_id ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Dispatch Date:</strong></td>
                                <td><?= !empty($order->created_on) ? date('d-m-Y H:i', strtotime($order->created_on)) : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Invoice Value:</strong></td>
                                <td>₹<?= number_format($order->invoice_value ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Freight Amount:</strong></td>
                                <td>₹<?= number_format($order->freight_amount ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Order Quantity:</strong></td>
                                <td><?= $order->order_quantity ?? '0' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Dispatch Quantity:</strong></td>
                                <td><?= $order->dispatch_quantity ?? '0' ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h4>Transport & Location Details</h4>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>Party Name:</strong></td>
                                <td><?= $order->party_name ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Party Contact:</strong></td>
                                <td><?= $order->mobile ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Contact Person:</strong></td>
                                <td><?= $order->contact_name ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?= $order->location_name ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>District/State:</strong></td>
                                <td><?= ($order->district_name ?? '-') . ' / ' . ($order->state_name ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Transporter:</strong></td>
                                <td><?= $order->transport_name ?? '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Vehicle Details:</strong></td>
                                <td><?= $order->vehicle ?? '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Driver Details Section -->
            <div class="page_sec">
                <h4>Driver Information</h4>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td><strong>Driver Name:</strong></td>
                                <td><?= $order->driver_name ?? '-' ?></td>
                                <td><strong>Driver Mobile:</strong></td>
                                <td><?= $order->driver_mobile ?? '-' ?></td>
                                <td><strong>Vehicle Number:</strong></td>
                                <td><?= $order->vehicle_no ?? '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="page_sec">
                <h4>Order Line Items</h4>
                <?php if(!empty($sub_details)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Article Name</th>
                                    <th>Article Code</th>
                                    <th>Order Qty</th>
                                    <th>Bill Qty</th>
                                    <th>Bundle Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sr = 1; foreach($sub_details as $item): ?>
                                    <tr>
                                        <td><?= $sr++ ?></td>
                                        <td><?= $item->article_name ?? '-' ?></td>
                                        <td><?= $item->article_code ?? '-' ?></td>
                                        <td class="text-right"><?= $item->order_qty ?? '0' ?></td>
                                        <td class="text-right"><?= $item->bill_qty ?? '0' ?></td>
                                        <td class="text-right"><?= $item->bundle_bag_qty ?? '0' ?></td>
                                        <td class="text-right">₹<?= number_format($item->unit_price ?? 0, 2) ?></td>
                                        <td class="text-right">₹<?= number_format($item->total_amount ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No line items found for this order.
                    </div>
                <?php endif; ?>
            </div>


        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> Order details not found.
            </div>
        <?php endif; ?>


    </div>
</div>

<style>
    .page_sec {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        background: #ffffff;
    }

    .page_sec h4 {
        color: #0056d0;
        margin-bottom: 15px;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
        padding-bottom: 10px;
    }

    .table {
        margin-bottom: 0;
    }

    .table td {
        padding: 10px;
        vertical-align: middle;
    }

    .table td strong {
        color: #333;
        min-width: 150px;
        display: inline-block;
    }

    .badge {
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 500;
    }

    .mt-3 {
        margin-top: 20px;
    }

    @media print {
        .title_right, .float-right {
            display: none !important;
        }
        .page_sec {
            page-break-inside: avoid;
        }
    }
</style>

<?php include('footer.php'); ?>

