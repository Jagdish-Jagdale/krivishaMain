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
                Stock Adjustment
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_material" id="add_material" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <!-- Date -->
                            <!-- <div class="form-group col-md-4 col-sm-6">
                                <label>Date <b class="require">*</b></label>
                                <input type="date" name="adjustment_date" id="adjustment_date"
                                    class="form-control datepicker" disabled>
                            </div> -->
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
                                <label>Material (Raw Material)<b class="require">*</b></label>
                                <select name="material_id" id="material_id" class="form-control select2-select"
                                    required>
                                    <option value=""></option>
                                    <?php if (!empty($raw_material)) {
                                        foreach ($raw_material as $raw_material_result) { ?>
                                            <option value="<?= $raw_material_result->id ?>">
                                                <?= $raw_material_result->rm_name ?>
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
                                        <th>MATERIAL NAMEe</th>
                                        <th>Unit</th>
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
<script>
        $(document).ready(function() {
        
        $('#stock_management').addClass('nv active');
       
        $('.rm_stock_adjustment').addClass('active_cc');
       
    });
</script>

<script>
    $(document).ready(function () {
        $('#master').addClass('nv active-color');
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
        $('#plant_id').on('change', function () {
            $('#material_id').val('').trigger('change.select2');
           
        });
        $('#material_id').on('change', function () {
            var materialId = $(this).val();
            if (!materialId) {
                return;
            }
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
            
            var plant_id = $('#plant_id').val();
            var materialName = $('#material_id option:selected').text();

            $('#material_table').removeClass('d-none');

            if (materialId) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_single_uom_name',
                    type: 'POST',
                    data: { materialId: materialId, plant_id: plant_id },
                    success: function (response) {
                        var data = JSON.parse(response);
                        var uomName = data.uom_name;
                        if (!uomName) {
                            uomName = "N/A";
                        }
                        var uom_id = data.uom_id;
                        var total_stock_qty = data.total_stock_qty || 0;
                        var adjustment_type_txt = $('#adjustment_type option:selected').text();

                        var material_id = $('#material_id').val();
                        rowCounter++; // Increment counter for unique IDs

                        // Generate unique IDs for each row
                        var rowId = 'row_' + rowCounter;
                        var qtyId = 'qty_' + rowCounter;
                        var removeBtnId = 'remove_' + rowCounter;

                        var newRow = `<tr id="${rowId}">
                    <td class="sr-no"></td> 
                    <td>${materialName}</td>
                    <td>
                        <input type="text" class="form-control" name="unit[]" value="${uomName}" readonly>
                    </td>
                    <td>
                        <input type="hidden" name="raw_material_id[]" value="${material_id}">
                        <input type="hidden" class="form-control" name="uom_ids[]" value="${uom_id}">
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
                        $('#material_id option[value="' + materialId + '"]').prop('disabled', true);

                        $('#material_id').val('').trigger('change.select2');
                        $('#material_id').select2('destroy').select2({
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
            $('#material_id option[value="' + materialId + '"]').prop('disabled', false);
            $(this).closest('tr').remove();

            $('#material_id').select2('destroy').select2({
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
        $('#add_material').validate({
            ignore: [],
            rules: {
                plant_id: { required: true },
                adjustment_type: { required: true },
                material_id: {
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
                material_id: { required: "Please select raw material!" }
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
                            $(this).after('<span class="invalid-feedback error">Please enter a valid quantity!</span>');
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
        $(document).on('input change', '#plant_id, #material_id, #adjustment_type', function () {
            $(this).valid();
        });
    });
</script>