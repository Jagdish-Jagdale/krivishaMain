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
                Add Master Batch Inward
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
                                <select name="plant_id" id="plant_id" class="form-control select2-select">
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

                            <!-- Gate Entry Date -->

                            <div class="row">

                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Master Batch (Color)<b class="require">*</b></label>
                                    <select name="master_batch_id" id="master_batch_id"
                                        class="form-control select2-select">
                                        <option value=""></option>
                                        <?php if (!empty($color)) {
                                            foreach ($color as $mb_result) { ?>
                                                <option value="<?= $mb_result->id ?>">
                                                    <?= $mb_result->name ?>
                                                </option>
                                        <?php }
                                        } ?>

                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Gate Entry Date</label>
                                    <input name="gate_entry_date" type="date" id="gate_entry_date"
                                        class="form-control datepicker" value="" placeholder="Enter gate entry date">
                                </div>
                            </div>

                            <!-- Material Table -->
                            <div class="row d-none" id="material_table_container">
                                <div class="col-12">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sr. No.</th>
                                                <th>Master Batch</th>
                                                <th>Rate</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="material_table_body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Extra Charges Dropdown -->
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

                            <!-- Extra Charges Table -->
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
                                        <tbody id="trap_table_body"> <!-- ✅ different tbody id -->
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
    $(document).ready(function() {

        $('#stock_management').addClass('nv active');

        $('.mb_inward_form').addClass('active_cc');

    });
</script>
<script>
    $(document).ready(function() {
        // Initialize active class


        // Initialize Flatpickr for datepicker
        $('.datepicker').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date(),
            maxDate: new Date()
        });

        // Initialize Select2
        $('.select2-select, #trap_hamali_extra_charges').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        let rowCounter = 0; // Use let for block scope

        // Material selection handler
        $('#master_batch_id').on('change', function() {
            const materialId = $(this).val();
            if (!materialId) return;

            const materialName = $('#master_batch_id option:selected').text();
            let materialExists = false;

            // Check if material already exists in table
            $('#material_table_body tr').each(function() {
                if ($(this).find('input[name="master_batch_id[]"]').val() === materialId) {
                    materialExists = true;
                    return false; // Exit loop
                }
            });

            if (materialExists) {
                alert('Material already added!');
                $('#master_batch_id').val('').trigger('change.select2');
                return;
            }

            $('#material_table_container').removeClass('d-none');

            // Fetch UOM via AJAX
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/get_single_uom_name',
                type: 'POST',
                data: {
                    materialId: materialId
                },
                dataType: 'json', // Ensure JSON response
                success: function(response) {

                    rowCounter++;
                    const rowId = `row_${rowCounter}`;
                    const qtyId = `qty_${rowCounter}`;
                    const rateId = `rate_${rowCounter}`;
                    const removeBtnId = `remove_${rowCounter}`;

                    // Add new row to material table
                    const newRow = `
                    <tr id="${rowId}">
                    <td></td>
                        <td>${materialName}
                            <input type="hidden" name="color_id[]" value="${materialId}">
                        </td>
                        <td>
                            <input type="number" class="form-control rate_validation_class" name="rate[]" id="${rateId}" placeholder="Enter rate" step="0.01">
                        </td>
                        <td>
                            <input type="number" class="form-control qty_validation_class" name="quantity[]" id="${qtyId}" placeholder="Enter quantity"  step="0.01">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-material" data-id="${materialId}" id="${removeBtnId}">Remove</button>
                        </td>
                    </tr>`;

                    $('#material_table_body').append(newRow);

                    $('#master_batch_id option[value="' + materialId + '"]').prop('disabled', true);

                    $('#master_batch_id').val('').trigger('change.select2');
                    $('#master_batch_id').select2('destroy').select2({
                        placeholder: "Select an option",
                        allowClear: true,
                        width: '100%'
                    });
                    updateMaterialSerialNumbers();
                },
                error: function() {
                    alert('Error fetching UOM.');
                }
            });
        });

        // Remove material row
        $(document).on('click', '.remove-material', function() {
            var materialId = $(this).data('id');
            $('#master_batch_id option[value="' + materialId + '"]').prop('disabled', false);
            $(this).closest('tr').remove();

            $('#master_batch_id').select2('destroy').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
            if ($('#material_table_body tr').length === 0) {
                $('#material_table_container').addClass('d-none');
            }
            updateMaterialSerialNumbers();
        });


        // Extra charges selection handler
        $('#trap_hamali_extra_charges').on('change', function() {
            const selectedCharge = $(this).val();
            if (!selectedCharge) return;

            const chargeText = $(this).find('option:selected').text();
            let chargeExists = false;

            // Check if charge already exists
            $('#trap_table tbody tr').each(function() {
                if ($(this).find('td').eq(1).text() === chargeText) {
                    chargeExists = true;
                    return false;
                }
            });

            if (chargeExists) {
                alert('Charge already added!');
                $('#trap_hamali_extra_charges').val('').trigger('change.select2');
                return;
            }

            $('#trap_table').removeClass('d-none');
            rowCounter++;
            const qtyId = `qty_${rowCounter}`;
            const removeBtnId = `remove_${rowCounter}`;

            // Add new row to extra charges table
            const newRow = `
            <tr>
                <td></td>
                <td>${chargeText}</td>
                <td>
                    <input type="number" class="form-control trap_qty_validation_class" id="${qtyId}" name="trap_hamali_amount[]" placeholder="Enter amount" step="0.01" required>
                    <input type="hidden" name="extra_payment_option_ids[]" value="${selectedCharge}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-extra-charge" id="${removeBtnId}" data-id="${selectedCharge}">Remove</button>
                </td>
            </tr>`;

            $('#trap_table tbody').append(newRow);
            $('#trap_table tbody tr:last').find('td:first').text(rowCounter);
            $('#trap_hamali_extra_charges option[value="' + selectedCharge + '"]').prop('disabled', true);
            $('#trap_hamali_extra_charges').val('').trigger('change.select2');
            $('#trap_hamali_extra_charges').select2('destroy').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });

            updateSerialNumbers();
        });

        // Remove extra charge row

        $(document).on('click', '.remove-extra-charge', function() {

            const chargeId = $(this).data('id');
            $('#trap_hamali_extra_charges option[value="' + chargeId + '"]').prop('disabled', false);
            $(this).closest('tr').remove();
            if ($('#trap_table tbody tr').length === 0) {
                $('#trap_table').addClass('d-none');
            }
            updateSerialNumbers();
        });


        // Update serial numbers for extra charges table
        function updateSerialNumbers() {
            $('#trap_table tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        function updateMaterialSerialNumbers() {
            $('#material_table_body tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Form validation
        $('#add_inward').validate({
            ignore: [], // Validate hidden fields
            rules: {
                plant_id: {
                    required: true
                },
                master_batch_id: {
                    required: function() {
                        return $('#material_table_body tr').length === 0;
                    }
                },
                supplier_name: {
                    required: true
                },
                'rate[]': {
                    required: true,
                    number: true
                },
                'quantity[]': {
                    required: true,
                    number: true
                },
                'trap_hamali_amount[]': {
                    required: true,
                    number: true
                }
            },
            messages: {
                plant_id: 'Please select a plant!',
                master_batch_id: 'Please select at least one master batch!',
                supplier_name: 'Please select a supplier!',
                'rate[]': {
                    required: 'Please enter a valid rate!',
                    number: 'Please enter a valid rate!'
                },
                'quantity[]': {
                    required: 'Please enter a valid quantity!',
                    number: 'Please enter a valid quantity!'
                },
                'trap_hamali_amount[]': {
                    required: 'Please enter a valid amount!',
                    number: 'Please enter a valid amount!'
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback error');
                if (element.hasClass('qty_validation_class') || element.hasClass('trap_qty_validation_class') || element.hasClass('rate_validation_class')) {
                    error.insertAfter(element);
                } else if (element.hasClass('select2-hidden-accessible')) {
                    element.next('.select2').append(error);
                } else {
                    element.closest('.form-group').append(error);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                let isValid = true;

                // Validate quantity fields
                $('.qty_validation_class, .trap_qty_validation_class').each(function() {
                    const val = $(this).val();
                    if (!val || isNaN(val)) {
                        $(this).addClass('is-invalid');
                        if (!$(this).next('.invalid-feedback error').length) {
                            $(this).after('<span class="invalid-feedback error">Please enter a valid quantity !</span>');
                        }
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid').next('.invalid-feedback error').remove();
                    }
                });
                $('.rate_validation_class').each(function () {
                    const val = $(this).val();
                    if (!val || isNaN(val) ) {
                        $(this).addClass('is-invalid');
                        if (!$(this).next('.invalid-feedback error').length) {
                            $(this).after('<span class="invalid-feedback error">Please enter a valid rate !</span>');
                        }
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid').next('.invalid-feedback error').remove();
                    }
                });

                if (isValid) {
                    form.submit();
                }
            }
        });

        // Real-time validation for quantity fields
        $(document).on('input change', '.qty_validation_class, .trap_qty_validation_class,.rate_validation_class', function() {
            const val = $(this).val();
            if (val && !isNaN(val)) {
                $(this).removeClass('is-invalid').next('.invalid-feedback error').remove();
            }
        });

        // Trigger validation on select field changes
        $('#plant_id, #master_batch_id, #supplier_name').on('change', function() {
            $(this).valid();
        });
    });
</script>