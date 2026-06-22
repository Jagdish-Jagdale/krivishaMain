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
                Article(FG) Stock Adjustment
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
                                <label>Article(Finish Good)<b class="require">*</b></label>
                                <select name="article_id" id="article_id" class="form-control select2-select"
                                    required>
                                    <option value=""></option>
                                    <?php if (!empty($article)) {
                                        foreach ($article as $article_result) { ?>
                                            <option value="<?= $article_result->id ?>">
                                                <?= $article_result->article_name ?>
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
                                        <th>Article Name</th>
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
    $(document).ready(function () {
        //$('#master .child_menu').show();
        $('#stock_management').addClass('nv active');
        //$('.right_col').addClass('active_right');
        $('.article_stock_adjustment').addClass('active_cc');
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

        $('#article_id').on('change', function () {
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
            var materialName = $('#article_id option:selected').text();
            var materialExists = false;

            $('#material_table_body tr').each(function () {
                var rowMaterialId = $(this).find('input[name="article_ids[]"]').val();
                if (rowMaterialId == materialId) {
                    materialExists = true;
                    return false;
                }
            });
            var article_id = $(this).val();
            var plant_id = $('#plant_id').val();
            var materialName = $('#article_id option:selected').text();

            $('#material_table').removeClass('d-none');

            if (article_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_article_stock_qty',
                    type: 'POST',
                    data: { article_id: article_id, plant_id: plant_id },
                    success: function (response) {
                        var data = JSON.parse(response);
                        var total_stock_qty = data.total_stock_qty || 0;
                        var adjustment_type_txt = $('#adjustment_type option:selected').text();

                        var material_id = $('#article_id').val();
                        rowCounter++; // Increment counter for unique IDs

                        // Generate unique IDs for each row
                        var rowId = 'row_' + rowCounter;
                        var qtyId = 'qty_' + rowCounter;
                        var removeBtnId = 'remove_' + rowCounter;

                        var newRow = `<tr id="${rowId}">
                    <td class="sr-no"></td> 
                    <td>${materialName}</td>
                    <td>
                        <input type="hidden" name="article_ids[]" value="${article_id}">
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
                        $('#article_id option[value="' + materialId + '"]').prop('disabled', true);

                        $('#article_id').val('').trigger('change.select2');
                        $('#article_id').select2('destroy').select2({
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
            $('#article_id option[value="' + materialId + '"]').prop('disabled', false);
            $(this).closest('tr').remove();

            $('#article_id').select2('destroy').select2({
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
                article_id: {
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
                article_id: { required: "Please select article!" }
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
        $(document).on('input change', '#plant_id, #article_id, #adjustment_type', function () {
            $(this).valid();
        });
    });

</script>