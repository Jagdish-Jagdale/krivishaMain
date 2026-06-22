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
                    <form method="post" name="adjustment_form" id="adjustment_form" enctype="multipart/form-data">
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
    $(document).ready(function () {
        //$('#master .child_menu').show();
        $('#master').addClass('nv active-color');
        //$('.right_col').addClass('active_right');
        // $('.add_location').addClass('active_cc');
    });
</script>

<script>
    $(document).ready(function () {
        $('.datepicker').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date(),

        });
        // Initialize Select2 for dropdowns
        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('#material_id').on('change', function () {
            $('#material_table_container').removeClass('d-none');
            var materialId = $(this).val();
            var plant_id = $('#plant_id').val();
            var materialName = $('#material_id option:selected').text();

            var materialExists = false;
            $('#material_table_body tr').each(function () {
                var rowMaterialId = $(this).find('input[name="raw_material_id[]"]').val();
                if (rowMaterialId == materialId) {
                    materialExists = true;
                    return false;
                }
            });
            $('#material_table').removeClass('d-none');

            if (!materialExists && materialId) {
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

                        var material_id = $('#material_id').val();
                        var adjustment_type = $('#adjustment_type option:selected').text();

                        var newRow = `<tr>
                            <td>${materialName}</td>
                            <td>
                                <input type="text" class="form-control" name="unit[]" value="${uomName}" readonly>
                                <input type="hidden" class="form-control" name="raw_material_id[]" value="${material_id}">
                                <input type="hidden" class="form-control" name="uom_ids[]" value="${uom_id}">
                            </td>
                            <td><input type="text" name="adjustment_type[]" class="form-control" value="${adjustment_type}" readonly placeholder="select adjustment type"></td>
                            <td>
                                <input type="number" class="form-control" name="quantity[]" placeholder="Enter quantity" required>
                            </td>
                            <td> <input type="text" name="total_stock_qty[]" class="form-control" value="${total_stock_qty}" readonly> </td>
                            <td><input type="text" name="remark[]" class="form-control" placeholder="Remark"></td>
                            <td>
                                <button type="button" class="btn btn-danger remove-material">Remove</button>
                            </td>
                        </tr>`;
                        $('#material_table_body').append(newRow);
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

        $('#material_table_body').on('click', '.remove-material', function () {
            $(this).closest('tr').remove();
        });

        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || !/^\s/.test(value);
        }, "First letter cannot be a space.");

        $('#adjustment_form').validate({
            rules: {
                plant_id: {
                    required: true
                },
                adjustment_type: {
                    required: true
                },
                material_id: {
                    required: true
                },
            },
            messages: {
                plant_id: {
                    required: "Please select a plant."
                },
                adjustment_type: {
                    required: "Please select adjustment type."
                },
                material_id: {
                    required: "Please select a material."
                },

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
            }
        });
    });
</script>