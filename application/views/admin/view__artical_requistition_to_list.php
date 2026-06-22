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
                <h3>View Details/Article Transfer</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_material" id="add_material" enctype="multipart/form-data">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                                <thead class="thead">
                                    <tr>
                                        <th>SR. NO.</th>
                                        <th>Article Name</th>
                                        <th>Request Qty</th>
                                        <th>Qty</th>
                                        <th>Previous Send Qty</th>
                                        <th>Available Stock Qty</th>
                                        <th>Requested Plant Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($article_requistion_list)): ?>
                                        <?php $srNo = 1; ?>
                                        <?php foreach ($article_requistion_list as $item):
                                            $article_id = $item->article_id;
                                            $total_qty = $this->db->select_sum('total_quantity')
                                                ->where('article_id', $article_id)
                                                ->where('plant_id', $this->session->userdata('assign_plant_id'))
                                                ->where('is_deleted', '0')
                                                ->get('tbl_article_stock_report')
                                                ->row();
                                                $user_plant_id = $this->session->userdata('assign_plant_id');

                                            ?>
                                            <tr>
                                                <td><?= $srNo++; ?></td>
                                                <td><?= $item->article_name; ?></td>
                                                <td><?= $item->request_quantity; ?></td>
                                                <td>
                                                    <input type="hidden" name="plant_id" value="<?= $item->plant_id ?>">

                                                    <input type="hidden" name="article_id[<?= $srNo ?>]" value="<?= $item->article_id ?>">
                                                    <input type="hidden" name="request_no[<?= $srNo ?>]" value="<?= $item->request_no ?>">
                                                    <input type="hidden" name="request_quantity[<?= $srNo ?>]" id="request_quantity_<?= $item->id ?>" value="<?= $item->request_quantity ?>">
                                                    <input type="hidden" name="previous_send_qty[<?= $srNo ?>]" id="previous_send_qty_<?= $item->id ?>" value="<?= $item->received_qty ?>">
                                                    <input type="hidden" name="my_plant_id[<?= $srNo ?>]" value="<?= $item->my_plant_id ?>">

                                                    <!-- empty value holder -->
                                                    <input type="hidden" name="received_qty[<?= $srNo ?>]" value="">

                                                    <!-- actual input -->
                                                
                                                    <input type="number"
                                                        placeholder="Enter Quantity"
                                                        name="received_qty[<?= $srNo ?>]"
                                                        id="received_qty_<?= $item->id ?>"
                                                        class="form-control qty_validation_class"
                                                        max="<?= $total_qty->total_quantity ?>"
                                                        oninput="validateQty(<?= $item->id ?>, <?= $total_qty->total_quantity > 0 ? $total_qty->total_quantity : 0; ?>)"
                                                        <?= ($item->received_qty == $item->request_quantity || $total_qty->total_quantity == 0) ? 'disabled' : '' ?>
                                                    />
                                                </td>
                                                
                                                <td><?= $item->received_qty; ?></td>
                                                <td>
                                                    <?php
                                                    echo $total_qty->total_quantity > 0 ? $total_qty->total_quantity : 0;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo $item->remark;
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
    $(document).ready(function () {
        $('#add_material').validate({
            ignore: [],
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback error');
                if (element.hasClass('qty_validation_class')) {
                    error.insertAfter(element);
                } else if (element.hasClass('select2-hidden-accessible')) {
                    element.next('span').append(error);
                } else {
                    element.closest('.form-group').append(error);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var anyQtyFilled = false;

                $('.qty_validation_class').each(function () {
                    var val = $(this).val();
                    if (val !== "" && !isNaN(val)) {
                        anyQtyFilled = true;
                        return false; // stop loop once one valid qty found
                    }
                });

                // Remove old errors
                $('.qty_validation_class').removeClass('is-invalid');
                $('.qty_validation_class').next('span.invalid-feedback.error').remove();

                if (!anyQtyFilled) {
                    // show error on first enabled qty field
                    var firstInput = $('.qty_validation_class:not(:disabled)').first();
                    firstInput.addClass('is-invalid');
                    firstInput.after('<span class="invalid-feedback error">Please enter at least one quantity</span>');
                    return false;
                }

                form.submit();
            }
        });

        $(document).on('input change', '.qty_validation_class', function () {
            var val = $(this).val();
            if (val !== "" && !isNaN(val)) {
                $(this).removeClass('is-invalid');
                $(this).next('span.invalid-feedback error').remove();
            }
        });
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