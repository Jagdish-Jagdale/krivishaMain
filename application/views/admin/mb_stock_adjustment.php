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
        max-width: 100% !important;
    }

    .select2-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                Master Batch Stock Adjustment
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="adjustment_form" id="adjustment_form" enctype="multipart/form-data">
                        <div class="row flex_wrap">

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Plant<b class="require">*</b></label>
                                <select name="plant_id" id="plant_id" class="form-control select2-select" required>
                                    <option value="">Select Plant</option>
                                    <?php if (!empty($plant)) {
                                        foreach ($plant as $plant_result) { ?>
                                            <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>>
                                                <?= $plant_result->plant_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <!-- Adjustment Type -->
                            <div class="form-group col-md-4 col-sm-6">
                                <label>Adjustment Type <b class="require">*</b></label>
                                <select name="adjustment_type" id="adjustment_type" class="form-control select2-select"
                                    required>
                                    <option value="">Select Type</option>
                                    <option value="1">Increasing</option>
                                    <option value="2">Decreasing</option>
                                </select>
                            </div>

                            <!-- Material -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch (Color)<b class="require">*</b></label>
                                <select name="master_batch" id="master_batch" class="form-control select2-select"
                                    required>
                                    <option value=""></option>
                                    <?php if (!empty($color)) {
                                        foreach ($color as $color_result) { ?>
                                            <option value="<?= $color_result->id ?>">
                                                <?= $color_result->name ?>
                                            </option>
                                        <?php }
                                    } ?>

                                </select>
                            </div>

                        </div>
                        <div class="row flex_wrap">

                            <table class="table table-bordered d-none" id="material_table">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Master Batch Name</th>
                                        <th>Adjustment Type</th>
                                        <th>Quantity <b class="require">*</b></th>
                                        <th>Total Stock Qty</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="material_table_body">
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <button type="submit" id="submit_btn" name="submit_btn" value="submit_btn"
                                    class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php');
$id = 0;
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);
}
?>

<!-- <script>
    $(document).ready(function () {
        $('.datepicker').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date(),

        });
        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('#master_batch').on('change', function () {
            var adjustment_type_val = $('#adjustment_type').val();
            if (!adjustment_type_val) {
                alert("Please select an adjustment type first.");
                $(this).val('');
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy').select2();
                    $(this).select2({
                        placeholder: "Select an option",
                        allowClear: true,
                        width: '100%'
                    });
                }
                $('#material_table').addClass('d-none');
                return;
            }
            var master_batch_id = $(this).val();
            var plant_id = $('#plant_id').val();
            var materialName = $('#master_batch option:selected').text();

            $('#material_table').removeClass('d-none');


            if (master_batch_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_master_batch_stock_qty',
                    type: 'POST',
                    data: { master_batch_id: master_batch_id, plant_id: plant_id },
                    success: function (response) {
                        var data = JSON.parse(response);
                        var total_stock_qty = data.total_stock_qty || 0;

                        var adjustment_type_txt = $('#adjustment_type option:selected').text();
                        var newRow = `<tr data-id="${master_batch_id}">
                            <td>${materialName}</td>
                            <td>
                                <input type="hidden" name="master_batch_id[]" value="${master_batch_id}">
                                <input type="hidden" name="adjustment_type[]" value="${adjustment_type_val}">
                                <input type="text" class="form-control" value="${adjustment_type_txt}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control qty_validation_class" name="quantity[]" data-id="${master_batch_id}" id="${master_batch_id}" placeholder="Enter quantity">
                            </td>
                            <td>
                                <input type="text" name="total_stock_qty[]" class="form-control" value="${total_stock_qty}" readonly>
                            </td>
                            <td><input type="text" name="remark[]" class="form-control" placeholder="Remark"></td>
                            <td><button type="button" class="btn btn-danger remove-material">Remove</button></td>
                        </tr>`;
                        $('#material_table_body').append(newRow);
                        $('#master_batch option[value="' + master_batch_id + '"]').prop('disabled', true);

                        $('#master_batch').val('').trigger('change.select2');
                        $('#master_batch').select2('destroy').select2({
                            placeholder: "Select an option",
                            allowClear: true,
                            width: '100%'
                        });
                        applyQtyValidation();

                    },
                    error: function () {
                        alert("Error fetching Stock Qty.");
                    }
                });
            }
        });

        $('#material_table_body').on('input', 'input[name="quantity[]"]', function () {

            var adjustmentType = $(this).closest('tr').find('input[name="adjustment_type[]"]').val();
            var totalStockQty = $(this).closest('tr').find('input[name="total_stock_qty[]"]').val();
            var quantity = $(this).val();

            if (adjustmentType == 'Decreasing' && parseFloat(quantity) > parseFloat(totalStockQty)) {
                alert("You can't decrease more than the available stock. Please enter a valid quantity.");
                $(this).val(totalStockQty);
            }
        });

        $('#material_table_body').on('click', '.remove-material', function () {
            const color_id = $(this).closest('tr').data('id');
            $('#master_batch option[value="' + color_id + '"]').prop('disabled', false);
            $(this).closest('tr').remove();

            // Reset Select2
            $('#master_batch').val(null).trigger('change');
            applyQtyValidation();
        });
        
    });
</script> -->

<script>
        $(document).ready(function() {
        
        $('#stock_management').addClass('nv active');
       
        $('.mb_stock_adjustment').addClass('active_cc');
       
    });
</script>
<script>
    $(document).ready(function () {
        
        $('.datepicker').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date(),
        });

        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        let rowCounter = 0;

        $('#master_batch').on('change', function () {
            var adjustment_type_val = $('#adjustment_type').val();
            if (!adjustment_type_val) {
                alert("Please select an adjustment type first.");
                $(this).val('');
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy').select2({
                        placeholder: "Select an option",
                        allowClear: true,
                        width: '100%'
                    });
                }
                $('#material_table').addClass('d-none');
                return;
            }
            $('#item_table_container').removeClass('d-none');
            var materialId = $(this).val();
            var materialName = $('#master_batch option:selected').text();
            var materialExists = false;

            $('#material_table_body tr').each(function () {
                var rowMaterialId = $(this).find('input[name="master_batch_id[]"]').val();
                if (rowMaterialId == materialId) {
                    materialExists = true;
                    return false;
                }
            });
            var master_batch_id = $(this).val();
            var plant_id = $('#plant_id').val();
            var materialName = $('#master_batch option:selected').text();

            $('#material_table').removeClass('d-none');

            if (master_batch_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_master_batch_stock_qty',
                    type: 'POST',
                    data: { master_batch_id: master_batch_id, plant_id: plant_id },
                    success: function (response) {
                        var data = JSON.parse(response);
                        var total_stock_qty = data.total_stock_qty || 0;
                        var adjustment_type_txt = $('#adjustment_type option:selected').text();

                        var material_id = $('#master_batch').val();
                        rowCounter++; // Increment counter for unique IDs

                        // Generate unique IDs for each row
                        var rowId = 'row_' + rowCounter;
                        var qtyId = 'qty_' + rowCounter;
                        var removeBtnId = 'remove_' + rowCounter;

                        var newRow = `<tr id="${rowId}">
                    <td class="sr-no"></td> 
                    <td>${materialName}</td>
                    <td>
                        <input type="hidden" name="master_batch_id[]" value="${master_batch_id}">
                        <input type="text" name="adjustment_type[]" class="form-control" value="${adjustment_type_txt}" readonly placeholder="select adjustment type">
                    </td>
                    <td>
                        <input type="number" class="form-control qty_validation_class" name="quantity[]" id="${qtyId}" placeholder="Enter quantity">
                    </td>
                    <td>
                        <input type="text" name="total_stock_qty[]" class="form-control" value="${total_stock_qty}" readonly>
                    </td>
                    <td>
                    <input type="text" name="remark[]" class="form-control" placeholder="Remark">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-material" data-id="${material_id}" id="${removeBtnId}">Remove</button>
                    </td>
                </tr>`;

                        $('#material_table_body').append(newRow);
                        $('#master_batch option[value="' + materialId + '"]').prop('disabled', true);

                        $('#master_batch').val('').trigger('change.select2');
                        $('#master_batch').select2('destroy').select2({
                            placeholder: "Select an option",
                            allowClear: true,
                            width: '100%'
                        });

                        updateSerialNumbers();
                    },
                    error: function () {
                        alert("Error fetching UOM.");
                    }
                });
            }
        });
        $('#material_table_body').on('input', 'input[name="quantity[]"]', function () {
            var adjustmentType = $(this).closest('tr').find('input[name="adjustment_type[]"]').val();
            var totalStockQty = $(this).closest('tr').find('input[name="total_stock_qty[]"]').val();
            var quantity = $(this).val();

            if (adjustmentType == 'Decreasing' && parseFloat(quantity) > parseFloat(totalStockQty)) {
                alert("You can't decrease more than the available stock. Please enter a valid quantity.");
                $(this).val(totalStockQty);
            }
        });

        $(document).on('click', '.remove-material', function () {
            var materialId = $(this).data('id');
            $('#master_batch option[value="' + materialId + '"]').prop('disabled', false);
            $(this).closest('tr').remove();

            $('#master_batch').select2('destroy').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });

            updateSerialNumbers();
        });

        function updateSerialNumbers() {
            $('#material_table_body tr').each(function (index) {
                $(this).find('.sr-no').text(index + 1);
            });
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('#adjustment_form').validate({
            ignore: [],
            rules: {
                plant_id: { required: true },
                adjustment_type: { required: true },
                master_batch: {
                    required: {
                        depends: function () {
                            return $('#material_table_body tr').length === 0;
                        }
                    }
                }
            },
            messages: {
                plant_id: { required: "Please select a plant!" },
                adjustment_type: { required: "Please select adjustment type!" },
                master_batch: { required: "Please select master batch!" }
            },
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
                var allValid = true;
                $('.qty_validation_class').each(function () {
                    var val = $(this).val();
                    if (val === "" || isNaN(val)) {
                        $(this).addClass('is-invalid');
                        if ($(this).next('span.invalid-feedback error').length === 0) {
                            $(this).after('<span class="invalid-feedback error">Please enter a valid quantity min 1!</span>');
                        }
                        allValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('span.invalid-feedback error').remove();
                    }
                });
                if (allValid) {
                    form.submit();
                }
            },
        });

        $(document).on('input change', '.qty_validation_class', function () {
            var val = $(this).val();
            if (val !== "" && !isNaN(val)) {
                $(this).removeClass('is-invalid');
                $(this).next('span.invalid-feedback error').remove();
            }
        });
        $(document).on('input change', '#plant_id, #master_batch, #adjustment_type', function () {
            $(this).valid();
        });
    });

</script>