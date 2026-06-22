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
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>View Details/Master Batch Transfer</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form action="" method="post">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                                <thead class="thead">
                                    <tr>
                                        <th>SR. NO.</th>
                                        <th>Master Batch (Color)</th>
                                        <th>Request Qty</th>
                                        <th>Qty</th>
                                        <th>Previous Send Qty</th>
                                        <th>Available Stock Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($master_batch_list)): ?>
                                        <?php $srNo = 1; ?>
                                        <?php foreach ($master_batch_list as $item):
                                            $master_batch_id = $item->master_batch_id;
                                            $total_qty = $this->db->select_sum('total_quantity')
                                                ->where('master_batch_id', $master_batch_id)
                                                ->where('plant_id', $this->session->userdata('assign_plant_id'))
                                                ->where('is_deleted', '0')
                                                ->get('tbl_master_batch_stock_report')
                                                ->row();
                                            ?>
                                            <tr>
                                                <td><?= $srNo++; ?></td>
                                                <td><?= $item->mb_name; ?></td>
                                                <td><?= $item->request_quantity; ?></td>
                                                <td>
                                                    <input type="hidden" name="master_batch_id[]"
                                                        id="master_batch_id_<?= $item->id ?>"
                                                        value="<?= $item->master_batch_id ?>">
                                                    <input type="hidden" name="request_no[]" id="request_no_<?= $item->id ?>"
                                                        value="<?= $item->request_no ?>">
                                                    <input type="hidden" name="request_quantity[]"
                                                        id="request_quantity_<?= $item->id ?>"
                                                        value="<?= $item->request_quantity ?>">
                                                    <input type="hidden" name="previous_send_qty[]"
                                                        id="previous_send_qty_<?= $item->id ?>"
                                                        value="<?= $item->received_qty ?>">
                                                    <input type="hidden" name="my_plant_id[]" id="my_plant_id_<?= $item->id ?>"
                                                        value="<?= $item->my_plant_id ?>">
                                                    <input type="number" placeholder="Enter Quantity" min="1" name="received_qty[]"
                                                        id="received_qty_<?= $item->id ?>" value="" class="form-control"
                                                        max="<?= $total_qty->total_quantity ?>"
                                                        oninput="validateQty(<?= $item->id ?>, <?= $total_qty->total_quantity > 0 ? $total_qty->total_quantity : 0; ?>)"
                                                        <?= ($item->received_qty == $item->request_quantity || $total_qty->total_quantity == 0) ? 'disabled' : '' ?> />
                                                </td>
                                                <td><?= $item->received_qty; ?></td>
                                                <td>
                                                    <?php
                                                    echo $total_qty->total_quantity > 0 ? $total_qty->total_quantity : 0;
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">No requisition data available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </table>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" name="submit_btn" value="submit_btn"
                                        class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include('footer.php');
?>
<script>
    $(document).ready(function () {
        $('#master').addClass('nv active-color');
    });
</script>

<script>
    function validateQty(itemId, maxQty) {
        var inputField = document.getElementById('received_qty_' + itemId);
        var enteredQty = parseInt(inputField.value, 10);

        var previous_send_qty = document.getElementById('previous_send_qty_' + itemId).value;
        var request_quantity = document.getElementById('request_quantity_' + itemId).value;

        if (previous_send_qty != '') {
            var total_request_qty = request_quantity - previous_send_qty;
            if (enteredQty > total_request_qty) {
                inputField.value = total_request_qty;
                alert("Exceeds requested qty. Adjusted to " + total_request_qty);
                return;
            }
        } else {
            if (enteredQty > request_quantity) {
                inputField.value = request_quantity;
                alert("Exceeds requested qty. Adjusted to " + request_quantity);
                return;
            }
        }
        if (enteredQty > maxQty) {
            inputField.value = maxQty;
            alert("Quantity exceeds stock. Adjusted to " + maxQty);
            return;
        }
    }
</script>