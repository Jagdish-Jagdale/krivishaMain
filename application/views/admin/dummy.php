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
                Add Inword Form
            </h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_inward" id="add_inward" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <input autocomplete="off" type="hidden" name="id" id="id" value="">

                            <!-- Supplier Name -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Supplier Name<b class="require">*</b></label>
                                <select name="supplier_name" id="supplier_name" class="form-control select2-select"
                                    required>
                                    <option value="">Select Supplier</option>
                                    <?php if (!empty($party_name)) {
                                        foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>" <?php if (!empty($single) && $single->party_name_id == $party_result->id) { ?>selected<?php } ?>>
                                                <?= $party_result->party_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
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
                            <!-- Gate Entry No. -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Gate Entry No.</label>
                                <input name="gate_entry_no" type="text" id="gate_entry_no" class="form-control" value=""
                                    placeholder="Enter gate entry number">
                            </div>
                            <div class="row">
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
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Gate Entry Date</label>
                                    <input name="gate_entry_date" type="date" id="gate_entry_date" class="form-control"
                                        value="" placeholder="Enter gate entry date">
                                </div>
                            </div>
                            <div class="row d-none" id="material_table_container">
                                <div class="col-12">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>MATERIAL NAMEe</th>
                                                <th>Unit</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="material_table_body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Extra Charges</label>
                                    <select name="trap_hamali_extra_charges" id="trap_hamali_extra_charges"
                                        class="form-control select2-select">
                                        <option value="">Select Extra Charges</option>
                                        <?php if (!empty($extra_payment_option)) {
                                            foreach ($extra_payment_option as $extra_payment_option_result) { ?>
                                                <option value="<?= $extra_payment_option_result->id ?>">
                                                    <?= $extra_payment_option_result->extra_payment_option ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <table id="trap_table" class="table table-bordered table-striped d-none">
                                        <thead>
                                            <tr>
                                                <th>Sr. No.</th>
                                                <th>Extra Charges</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="trap_table_body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                    <button type="submit" id="submit_btn" name="submit_btn" value="submit_btn"
                                        class="btn btn-primary">Submit</button>
                                </div>
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
        $('#master').addClass('nv active-color');

        $('.datepicker').flatpickr({
            dateFormat: "Y-m-d",
            defaultDate: new Date(),
        });

        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('#material_id').on('change', function () {
            $('#material_table_container').removeClass('d-none');
            var materialId = $(this).val();
            var materialName = $('#material_id option:selected').text();
            var materialExists = false;

            $('#material_table_body tr').each(function () {
                var rowMaterialId = $(this).find('input[name="raw_material_id[]"]').val();
                if (rowMaterialId == materialId) {
                    materialExists = true;
                    return false;
                }
            });

            if (!materialExists && materialId) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_single_uom_name',
                    type: 'POST',
                    data: { materialId: materialId },
                    success: function (response) {
                        var data = JSON.parse(response);
                        var uomName = data.uom_name || "N/A";
                        var uom_id = data.uom_id;

                        var newRow = `<tr>
                    <td>${materialName}</td>
                    <td>
                        <input type="text" class="form-control" name="unit[]" value="${uomName}" readonly>
                        <input type="hidden" class="form-control" name="raw_material_id[]" value="${materialId}">
                        <input type="hidden" class="form-control" name="uom_ids[]" value="${uom_id}">
                    </td>
                    <td>
                        <input type="number" min="1" class="form-control error_qty_classs" id="validation_qty_${materialId}" data-material-id="${materialId}" name="quantity[]" placeholder="Enter quantity" required>
                    </td>
                    
                    <td>
                        <button type="button" class="btn btn-danger remove-material" data-id="${materialId}">Remove</button>
                    </td>
                </tr>`;

                        $('#material_table_body').append(newRow);

                        // Disable selected option so it cannot be chosen again
                        $('#material_id option[value="' + materialId + '"]').prop('disabled', true);

                        $('#material_id').val('').trigger('change.select2');
                        $('#material_id').select2('destroy').select2({
                            placeholder: "Select an option",
                            allowClear: true,
                            width: '100%'
                        });
                    },
                    error: function () {
                        alert("Error fetching UOM.");
                    }
                });
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
        });

        $('#trap_hamali_extra_charges').on('change', function () {
            var selectedCharge = $(this).val();
            var chargeText = $(this).find('option:selected').text();

            if (selectedCharge === "") {
                return;
            }

            $('#trap_table').removeClass('d-none');

            var chargeExists = false;
            $('#trap_table_body tr').each(function () {
                var rowChargeText = $(this).find('td').eq(1).text();
                if (rowChargeText === chargeText) {
                    chargeExists = true;
                    return false;
                }
            });

            if (!chargeExists) {
                var newRow = `<tr>
            <td></td> <!-- Serial No will be updated after append -->
            <td>${chargeText}</td>
            <td>
                <input type="number" min="1" class="form-control" name="trap_hamali_amount[]" placeholder="Enter amount" required>
                <input type="hidden" name="extra_payment_option_ids[]" value="${selectedCharge}">
            </td>
            <td>
                <button type="button" class="btn btn-danger remove-extra-charge" data-id="${selectedCharge}">Remove</button>
            </td>
        </tr>`;

                $('#trap_table_body').append(newRow);

                // Disable selected option
                $('#trap_hamali_extra_charges option[value="' + selectedCharge + '"]').prop('disabled', true);

                // Reset and refresh Select2
                $('#trap_hamali_extra_charges').val('').trigger('change.select2');
                $('#trap_hamali_extra_charges').select2('destroy').select2({
                    placeholder: "Select Extra Charges",
                    allowClear: true,
                    width: '100%'
                });

                // Update serial numbers after adding
                updateSerialNumbers();
            }
        });

        // Remove row
        $(document).on('click', '.remove-extra-charge', function () {
            var chargeId = $(this).data('id');

            // Enable back the option
            $('#trap_hamali_extra_charges option[value="' + chargeId + '"]').prop('disabled', false);

            // Remove the row
            $(this).closest('tr').remove();

            // Refresh Select2
            $('#trap_hamali_extra_charges').select2('destroy').select2({
                placeholder: "Select Extra Charges",
                allowClear: true,
                width: '100%'
            });

            // Update serial numbers after removal
            updateSerialNumbers();
        });

        // Function to update serial numbers
        function updateSerialNumbers() {
            $('#trap_table_body tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    });
</script>

<script>
    $(document).ready(function () {
        $('#add_inward').validate({
            ignore: [],
            rules: {
                supplier_name: { required: true },
                // material_id: { required: true },
                plant_id: { required: true }
            },
            messages: {
                supplier_name: { required: "Please select supplier name!" },
                material_id: { required: "Please select material!" },
                plant_id: { required: "Please select plant!" }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                // Always place error after the element for dynamic fields
                if (element.hasClass('error_qty_classs') || element.hasClass('error_article_classs')) {
                    element.after(error);
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
                let allValid = true;

                // Validate all dynamic quantity fields
                $('.error_qty_classs').each(function () {
                    if (!$(this).valid() || $(this).val() === "" || Number($(this).val()) < 1) {
                        allValid = false;
                        $(this).addClass('is-invalid');
                        $(this).next('.error-message').remove();
                        $(this).after('<span class="error-message" style="color: red;">Please enter quantity!</span>');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.error-message').remove();
                    }
                });

                if (allValid) {
                    form.submit();
                }
            }
        });

        // Remove error on input/change
        $(document).on('input change', '.error_qty_classs, .error_article_classs', function () {
            if ($(this).val() !== "" && (!$(this).hasClass('error_qty_classs') || Number($(this).val()) >= 1)) {
                $(this).removeClass('is-invalid');
                $(this).next('.error-message').remove();
                $(this).next('span.invalid-feedback').remove();
            }
        });
    });
</script>

value="<?= $article_production_data->weight_ten_eleven ?? '' ?>"<?= isset($article_production_data->weight_ten_eleven) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_eleven_twelve ?? '' ?>"<?= isset($article_production_data->weight_eleven_twelve) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_twelve_thirteen ?? '' ?>"<?= isset($article_production_data->weight_twelve_thirteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_thirteen_fourteen ?? '' ?>"<?= isset($article_production_data->weight_thirteen_fourteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_fourteen_fifteen ?? '' ?>"<?= isset($article_production_data->weight_fourteen_fifteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_fifteen_sixteen ?? '' ?>"<?= isset($article_production_data->weight_fifteen_sixteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_sixteen_seventeen ?? '' ?>"<?= isset($article_production_data->weight_sixteen_seventeen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_seventeen_eighteen ?? '' ?>"<?= isset($article_production_data->weight_seventeen_eighteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_eighteen_nineteen ?? '' ?>"<?= isset($article_production_data->weight_eighteen_nineteen) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_nineteen_twenty ?? '' ?>"<?= isset($article_production_data->weight_nineteen_twenty) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_twenty_twentyone ?? '' ?>"<?= isset($article_production_data->weight_twenty_twentyone) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_twentyone_twentytwo ?? '' ?>"<?= isset($article_production_data->weight_twentyone_twentytwo) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_twentytwo_twentythree ?? '' ?>"<?= isset($article_production_data->weight_twentytwo_twentythree) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_twentythree_zero ?? '' ?>"<?= isset($article_production_data->weight_twentythree_zero) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_zero_one ?? '' ?>"<?= isset($article_production_data->weight_zero_one) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_one_two ?? '' ?>"<?= isset($article_production_data->weight_one_two) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_two_three ?? '' ?>"<?= isset($article_production_data->weight_two_three) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_three_four ?? '' ?>"<?= isset($article_production_data->weight_three_four) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_four_five ?? '' ?>"<?= isset($article_production_data->weight_four_five) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_five_six ?? '' ?>"<?= isset($article_production_data->weight_five_six) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_six_seven ?? '' ?>"<?= isset($article_production_data->weight_six_seven) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_seven_eight ?? '' ?>"<?= isset($article_production_data->weight_seven_eight) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_eight_nine ?? '' ?>"<?= isset($article_production_data->weight_eight_nine) ? 'readonly' : '' ?>
value="<?= $article_production_data->weight_nine_ten ?? '' ?>"<?= isset($article_production_data->weight_nine_ten) ? 'readonly' : '' ?>


value="<?= $article_production_data->qty_eight_nine ?? '' ?>"<?= isset($article_production_data->qty_eight_nine) && $article_production_data->qty_eight_nine !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_nine_ten ?? '' ?>"<?= isset($article_production_data->qty_nine_ten) && $article_production_data->qty_nine_ten !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_ten_eleven ?? '' ?>"<?= isset($article_production_data->qty_ten_eleven) && $article_production_data->qty_ten_eleven !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_eleven_twelve ?? '' ?>"<?= isset($article_production_data->qty_eleven_twelve) && $article_production_data->qty_eleven_twelve !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_twelve_thirteen ?? '' ?>"<?= isset($article_production_data->qty_twelve_thirteen) && $article_production_data->qty_twelve_thirteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_thirteen_fourteen ?? '' ?>"<?= isset($article_production_data->qty_thirteen_fourteen) && $article_production_data->qty_thirteen_fourteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_fourteen_fifteen ?? '' ?>"<?= isset($article_production_data->qty_fourteen_fifteen) && $article_production_data->qty_fourteen_fifteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_fifteen_sixteen ?? '' ?>"<?= isset($article_production_data->qty_fifteen_sixteen) && $article_production_data->qty_fifteen_sixteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_sixteen_seventeen ?? '' ?>"<?= isset($article_production_data->qty_sixteen_seventeen) && $article_production_data->qty_sixteen_seventeen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_seventeen_eighteen ?? '' ?>"<?= isset($article_production_data->qty_seventeen_eighteen) && $article_production_data->qty_seventeen_eighteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_eighteen_nineteen ?? '' ?>"<?= isset($article_production_data->qty_eighteen_nineteen) && $article_production_data->qty_eighteen_nineteen !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_nineteen_twenty ?? '' ?>"<?= isset($article_production_data->qty_nineteen_twenty) && $article_production_data->qty_nineteen_twenty !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_twenty_twentyone ?? '' ?>"<?= isset($article_production_data->qty_twenty_twentyone) && $article_production_data->qty_twenty_twentyone !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_twentyone_twentytwo ?? '' ?>"<?= isset($article_production_data->qty_twentyone_twentytwo) && $article_production_data->qty_twentyone_twentytwo !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_twentytwo_twentythree ?? '' ?>"<?= isset($article_production_data->qty_twentytwo_twentythree) && $article_production_data->qty_twentytwo_twentythree !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_twentythree_zero ?? '' ?>"<?= isset($article_production_data->qty_twentythree_zero) && $article_production_data->qty_twentythree_zero !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_zero_one ?? '' ?>"<?= isset($article_production_data->qty_zero_one) && $article_production_data->qty_zero_one !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_one_two ?? '' ?>"<?= isset($article_production_data->qty_one_two) && $article_production_data->qty_one_two !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_two_three ?? '' ?>"<?= isset($article_production_data->qty_two_three) && $article_production_data->qty_two_three !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_three_four ?? '' ?>"<?= isset($article_production_data->qty_three_four) && $article_production_data->qty_three_four !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_four_five ?? '' ?>"<?= isset($article_production_data->qty_four_five) && $article_production_data->qty_four_five !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_five_six ?? '' ?>"<?= isset($article_production_data->qty_five_six) && $article_production_data->qty_five_six !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_six_seven ?? '' ?>"<?= isset($article_production_data->qty_six_seven) && $article_production_data->qty_six_seven !== '' ? ' readonly' : '' ?>
value="<?= $article_production_data->qty_seven_eight ?? '' ?>"<?= isset($article_production_data->qty_seven_eight) && $article_production_data->qty_seven_eight !== '' ? ' readonly' : '' ?>

