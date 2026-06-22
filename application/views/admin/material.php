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
    .select2-select{
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                Inword Form
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
                            <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                                                                                                    echo $single->id;
                                                                                                } ?>">

                            <!-- Inward Number -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Inward Number<b class="require">*</b></label>
                                <input name="inward_number" type="text" id="inward_number" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->inward_number;
                                            } ?>"
                                    placeholder="Enter inward number" required>
                            </div>

                            <!-- Inward Date -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Inward Date<b class="require">*</b></label>
                                <input name="inward_date" type="date" id="inward_date" class="form-control datepicker" placeholder="Select inward date"
                                    value="<?php if (!empty($single)) {
                                                echo $single->inward_date;
                                            } ?>" required>
                            </div>

                            <!-- Supplier Name -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Supplier Name<b class="require">*</b></label>
                                <select name="supplier_name" id="supplier_name" class="form-control select2-select" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier) { ?>
                                        <option value="<?php echo $supplier->id; ?>"
                                            <?php if (!empty($single) && $single->supplier_name == $supplier->id) echo 'selected'; ?>>
                                            <?php echo $supplier->name; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Gate Entry No. -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Gate Entry No. </label>
                                <input name="gate_entry_no" type="text" id="gate_entry_no" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->gate_entry_no;
                                            } ?>"
                                    placeholder="Enter gate entry number" required>
                            </div>

                            <!-- Gate Entry Date -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Gate Entry Date </label>
                                <input name="gate_entry_date" type="text" id="gate_entry_date" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->gate_entry_date;
                                            } ?>"
                                    placeholder="Enter gate entry date" required>
                            </div>

                            <!-- Material (Raw Material) -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Material (Raw Material)<b class="require">*</b></label>
                                <select name="material_id" id="material_id" class="form-control select2-select" required multiple>
                                    <option value="">Select Material</option>
                                    <?php foreach ($materials as $material) { ?>
                                        <option value="<?php echo $material->id; ?>"
                                            <?php if (!empty($single) && $single->material_id == $material->id) echo 'selected'; ?>>
                                            <?php echo $material->name; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Unit -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Unit<b class="require">*</b></label>
                                <input name="unit" type="text" id="unit" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->unit;
                                            } ?>"
                                    placeholder="Enter unit" required>
                            </div>

                            <!-- Rate -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Rate<b class="require">*</b></label>
                                <input name="rate" type="number" step="0.01" id="rate" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->rate;
                                            } ?>"
                                    placeholder="Enter rate" required>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Quantity<b class="require">*</b></label>
                                <input name="quantity" type="number" step="0.01" id="quantity" class="form-control"
                                    value="<?php if (!empty($single)) {
                                                echo $single->quantity;
                                            } ?>"
                                    placeholder="Enter quantity" required>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                    <button type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
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
        //$('#master .child_menu').show();
        $('#master').addClass('nv active-color');
        //$('.right_col').addClass('active_right');
        // $('.add_location').addClass('active_cc');
    });
</script>

<script>
$(document).ready(function () {

    $('.datepicker').flatpickr({
        dateFormat: "Y-m-d",
        currentDate: new Date(),
    });

    // Initialize Select2 for dropdowns
    $('.select2-select').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });

    // Custom Validation Methods
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter cannot be a space.");

    $.validator.addMethod("noSpecialChars", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9\s\-]+$/.test(value);
    }, "Special characters are not allowed.");

    $.validator.addMethod("positiveNumber", function (value, element) {
        return this.optional(element) || parseFloat(value) > 0;
    }, "Please enter a positive number.");

    $('#add_inward').validate({
        rules: {
            inward_number: {
                required: true,
                noSpaceAtStart: true
            },
            inward_date: {
                required: true,
                date: true
            },
            supplier_name: {
                required: true
            },
            gate_entry_no: {
                required: false,
                noSpaceAtStart: true,
                noSpecialChars: true
            },
            gate_entry_date: {
                required: false
            },
            material_id: {
                required: true
            },
            unit: {
                required: true,
                noSpaceAtStart: true
            },
            rate: {
                required: true,
                number: true,
                positiveNumber: true
            },
            quantity: {
                required: true,
                number: true,
                positiveNumber: true
            }
        },
        messages: {
            inward_number: {
                required: "Please enter inward number.",
                noSpaceAtStart: "First letter cannot be a space."
            },
            inward_date: {
                required: "Please enter inward date.",
                date: "Please enter a valid date."
            },
            supplier_name: {
                required: "Please select supplier."
            },
            gate_entry_no: {
                required: "Please enter gate entry number.",
                noSpaceAtStart: "First letter cannot be a space.",
                noSpecialChars: "Special characters are not allowed."
            },
            gate_entry_date: {
                required: "Please enter gate entry date."
            },
            material_id: {
                required: "Please select material."
            },
            unit: {
                required: "Please enter unit.",
                noSpaceAtStart: "First letter cannot be a space."
            },
            rate: {
                required: "Please enter rate.",
                number: "Only numbers allowed.",
                positiveNumber: "Rate must be greater than zero."
            },
            quantity: {
                required: "Please enter quantity.",
                number: "Only numbers allowed.",
                positiveNumber: "Quantity must be greater than zero."
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
        }
    });

});
</script>
 