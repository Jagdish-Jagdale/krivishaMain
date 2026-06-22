<?php include('header.php') ?>

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


    .multi-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">
    <?php ?>
    <div class="page-title">
        <div class="title_left">
            <h3>
                <?php if (!empty($single)) { ?>
                    Update Printing Report
                <?php } else { ?>
                    Add Printing Report
                <?php } ?>
            </h3>

        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" action="<?= base_url('printing_unit_report/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/' . $this->uri->segment(4) . '/' . $this->uri->segment(5)) ?>" name="printing_unit_form" id="printing_unit_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">

                                <label for="order_status">Order Status<b class="require">*</b></label>
                                <select name="order_status" id="order_status" class="form-control">
                                    <option value="0" <?php if (!empty($single) && $single->order_status == 0)
                                        echo 'selected'; ?>>
                                        Pending</option>
                                    <option value="1" <?php if (!empty($single) && $single->order_status == 1)
                                        echo 'selected'; ?>>
                                        Completed</option>
                                    <option value="2" <?php if (!empty($single) && $single->order_status == 2)
                                        echo 'selected'; ?>>
                                        Cancelled</option>
                                </select>
                            </div>
                            <input type="hidden" id="article_id" name="article_id" value="<?php echo !empty($single) ? $single->article_id : '' ?>">
                            <input type="hidden" name="order_id" class="form-control" id="order_id"
                                value="<?= $this->uri->segment(4) ?>" readonly>
                            <input type="hidden" name="sub_order_id" class="form-control" id="sub_order_id"
                                value="<?= $this->uri->segment(2) ?>" readonly>

                            <input type="hidden" id="id" name="id" value="<?php if (!empty($single)) {
                                echo $single->id;
                            } ?>">
                            <input type="hidden" id="party_id" name="party_id" value="<?= $this->uri->segment(3) ?>">
                            
                            <?php
                            $order_qty = $this->Admin_model->get_container_order_qty($this->uri->segment(2));
                            ?>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 ">
                                <label for="order_qty">Order Quantity<b class="require">*</b></label>
                                <input type="number" min="0" name="order_qty" class="form-control" id="order_qty"
                                    value="<?php echo !empty($order_qty) ? $order_qty : '' ?>" readonly>

                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="approvd_qty">Approved Quantity<b class="require">*</b></label>
                                <input type="number" min=0 name="approvd_qty" class="form-control" id="approvd_qty"
                                    value="<?php echo !empty($single) ? $single->approvd_qty : '' ?>"
                                    placeholder="Please enter approved quantity">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="brand_name">Brand Name <b class="require">*</b></label>
                                <input type="text" class="form-control" name="brand_name" id="brand_name"
                                    value="<?= !empty($brand) ? ($brand->brand_name) : '' ?>" readonly>
                                <input type="hidden" name="brand_id" value="<?= !empty($brand) ? $brand->id : '' ?>">
                            </div>
                            <?php
                            $ink_names = !empty($brand->ink_names) ? $brand->ink_names : [];
                            $ink_ids = !empty($brand->ink_ids) ? explode(',', $brand->ink_ids) : [];
                            $ink_count = count($ink_ids);
                            ?>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="ink_names">How Many Color Job<b class="require">*</b></label>
                                <input type="number" min="0" name="ink_names" class="form-control" id="ink_names"
                                    value="<?php echo !empty($ink_count) ? $ink_count : '' ?>" readonly>
                            </div>

                            <?php for ($i = 1; $i <= $ink_count; $i++): ?>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report ">
                                    <label for="ink_consumed_<?= $i ?>">Ink Consumed <?= $i ?> (Shade Code)
                                        <?php if ($i == 1)
                                            echo '<b class="require">*</b>'; ?>
                                    </label>
                                    <select id="ink_consumed_<?= $i ?>" class="form-control" disabled>
                                        <option value="">Select Ink Consumed <?= $i ?></option>
                                        <?php foreach ($ink as $result_ink): ?>
                                            <?php $selected = (!empty($ink_ids[$i - 1]) && $ink_ids[$i - 1] == $result_ink->id) ? 'selected' : ''; ?>
                                            <option value="<?= $result_ink->id ?>" <?= $selected ?>>
                                                <?= $result_ink->rm_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <input type="hidden" name="ink_consumed[]"
                                        value="<?= htmlspecialchars($ink_ids[$i - 1] ?? '') ?>">

                                </div>

                                <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                    <label for="ink_qty_<?= $i ?>">Qty <?= $i ?>
                                        <?php if ($i == 1)
                                            echo '<b class="require">*</b>'; ?>
                                    </label>
                                    <input type="text" name="ink_qty[]" id="ink_qty_<?= $i ?>" class="form-control"
                                        value="<?= !empty($raw_material[$i - 1]) ? $raw_material[$i - 1]->quantity : '' ?>"
                                        placeholder="Please enter quantity <?= $i ?>">
                                </div>
                            <?php endfor; ?>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="other_material">Other Material 1 <b class="require">*</b></label>
                                <select name="other_material" id="other_material" class="form-control js-example-basic-multiple">
                                    <option value="">Select Other Material 1</option>
                                    <?php if (!empty($raw_matertal)): ?>
                                        <?php foreach ($raw_matertal as $result_raw_matertal): ?>
                                            <option value="<?= $result_raw_matertal->id ?>" <?= (!empty($single) && $single->other_material == $result_raw_matertal->id) ? 'selected' : '' ?>>
                                                <?= $result_raw_matertal->rm_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="other_material_qty_1">Other Material Qty 1 <b class="require">*</b></label>
                                <input type="text" name="other_material_qty_1" id="other_material_qty_1"
                                    class="form-control"
                                    value="<?php echo !empty($single) ? $single->other_material_qty_1 : '' ?>"
                                    placeholder="Please enter material qty 1">

                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="other_material_two">Other Material 2</label>
                                <select name="other_material_two" id="other_material_two" class="form-control js-example-basic-multiple">
                                    <option value="">Select Other Material 2</option>
                                    <?php if (!empty($raw_matertal)): ?>
                                        <?php foreach ($raw_matertal as $result_raw_matertal): ?>
                                            <option value="<?= $result_raw_matertal->id ?>" <?= (!empty($single) && $single->other_material_two == $result_raw_matertal->id) ? 'selected' : '' ?>>
                                                <?= $result_raw_matertal->rm_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 maretial_report">
                                <label for="other_material_qty_2">Other Material Qty 2</label>
                                <input type="text" name="other_material_qty_2" id="other_material_qty_2"
                                    class="form-control"
                                    value="<?php echo !empty($single) ? $single->other_material_qty_2 : '' ?>"
                                    placeholder="Please enter material qty 2">

                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 job_work">
                                <label for="remark">Remark</label>
                                <input type="text" name="remark" id="remark" class="form-control"
                                    value=""
                                    placeholder="Please enter remark">

                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" name="submit_btn" id="submit_btn" value="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    $(document).ready(function () {
        // $('#printing_unit .child_menu').show();
        // $('#printing_unit').addClass('nv active');
        // $('.right_col').addClass('active_right');
        // $('.printing_unit_report').addClass('active_cc');
        $('#printing_unit').addClass('nv active-color');
    });

    $(document).ready(function () {
        toggleFields();
        function toggleFields() {
            var val = $('#order_status').val();
            if (val == '1') {
                $('.job_work').show();
                $('.maretial_report').show();
            } else if (val == '2') {
                $('.job_work').show();
                $('.maretial_report').hide();
            } else {
                $('.job_work, .maretial_report').hide();
            }
        }
        $('#order_status').change(function () {
            toggleFields();
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please select type"
        });
    });
</script>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderQtyInput = document.getElementById('order_qty');
        const approvedQtyInput = document.getElementById('approvd_qty');

        approvedQtyInput.addEventListener('input', function () {
            const orderQty = parseFloat(orderQtyInput.value) || 0;
            const approvedQty = parseFloat(this.value) || 0;

            if (approvedQty > orderQty) {
                alert('Approved Quantity cannot be greater than Order Quantity.');
                this.value = orderQty;
            }
        });
    });
</script> -->

<script>
    $(document).ready(function () {
        // jQuery Validate - ignore Select2 hidden selects to avoid false blocks
        $.validator.setDefaults({
            ignore: ':hidden:not(.select2-hidden-accessible)'
        });

        $('#printing_unit_form').validate({
            ignore: ':hidden:not(.select2-hidden-accessible)',
            rules: {
                order_status: {
                    required: true
                },
                approvd_qty: {
                    required: function () {
                        return $('#order_status').val() === '1';
                    }
                },
                other_material: {
                    required: function () {
                        return $('#order_status').val() === '1';
                    }
                },
                other_material_qty_1: {
                    required: function () {
                        return $('#order_status').val() === '1';
                    }
                }
            },
            messages: {
                order_status: {
                    required: "Please select status!"
                },
                approvd_qty: {
                    required: "Please enter approved quantity!",
                    number: "Only numeric values allowed!"
                },
                other_material: {
                    required: "Please select Other Material!"
                },
                other_material_qty_1: {
                    required: "Please enter Material Qty 1!",
                    number: "Only numeric values allowed!"
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                form.submit();
            }
        });

        // Trigger re-validation on Select2 change events
        $('#other_material').on('change', function () {
            $(this).valid();
        });
        $('#other_material_two').on('change', function () {
            $(this).valid();
        });
        $('#other_material_qty_1').on('input change', function () {
            $(this).valid();
        });
        $('#ink_qty_1').on('input change', function () {
            $(this).valid();
        });
        $('#ink_consumed_1').on('change', function () {
            $(this).valid();
        });
    });
</script>