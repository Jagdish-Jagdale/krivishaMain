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


    .multi-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                <?php if (!empty($single)) { ?>
                    Update Employee Details
                <?php } else { ?>
                    Add Employee
                <?php } ?>
            </h3>

        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="employee_form" id="employee_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <!-- Employee Name -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Employee NAME<b class="require">*</b></label>
                                <input name="employee_name" type="text" class="form-control" id="employee_name" value="<?php if (!empty($single)) {
                                                                                                                            echo $single->first_name;
                                                                                                                        } ?>" placeholder="Enter employee name" required>

                                <span id="employee_name_error" class=""></span>
                            </div>
                            <!-- Employee Email -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="employee_email">Employee Email<b class="require">*</b></label>
                                <input type="email" name="employee_email" class="form-control" id="employee_email"
                                    value="<?php if (!empty($single)) {
                                                echo $single->email;
                                            } ?>" placeholder="Please enter email" required>
                                <span id="employee_email_error" class=""></span>
                            </div>
                            <input type="hidden" id="id" name="id" value="<?php if (!empty($single)) {
                                                                                echo $single->id;
                                                                            } ?>">
                            <!-- Employee Contact Number -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="employee_contact">Employee Contact Number<b class="require">*</b></label>
                                <input type="text" name="employee_contact" class="form-control" id="employee_contact"
                                    value="<?php if (!empty($single)) {
                                                echo $single->mobile_number;
                                            } ?>" placeholder="Please enter contact number" required>
                                <span id="employee_contact_error" class=""></span>
                            </div>


                            <!-- Department (Multi-Select) -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="department_id">Department<b class="require">*</b></label>
                                <select multiple="multiple" name="department_id[]" id="department_id"
                                    class="form-control js-example-basic-multiple">
                                    <?php
                                    $selected_dept_ids = [];
                                    if (!empty($single) && !empty($single->department_id)) {
                                        $selected_dept_ids = array_map('trim', explode(',', $single->department_id));
                                    }
                                    if (!empty($krivisha_department)) {
                                        foreach ($krivisha_department as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>"
                                                <?php if (in_array((string)$make_result->id, $selected_dept_ids)) echo 'selected'; ?>>
                                                <?= $make_result->department ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>

                            <!-- Plant (Multi-Select) -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="plant_id">Plant<b class="require">*</b></label>
                                <select multiple="multiple" name="plant_id[]" id="plant_id" class="form-control js-example-basic-multiple">
                                    <?php
                                    $selected_plant_ids = [];
                                    if (!empty($single) && !empty($single->plant_id)) {
                                        $selected_plant_ids = array_map('trim', explode(',', $single->plant_id));
                                    }
                                    if (!empty($plant)) {
                                        foreach ($plant as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>"
                                                <?php if (in_array((string)$make_result->id, $selected_plant_ids)) echo 'selected'; ?>>
                                                <?= $make_result->plant_name ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>

                            <!-- Employee Designation -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="designation">Employee Designation<b class="require">*</b></label>
                                <input type="text" name="designation" class="form-control" id="designation" value="<?php if (!empty($single)) {
                                                                                                                        echo $single->designation;
                                                                                                                    } ?>" placeholder="Please enter designation" required>
                                <span id="designation_error" class=""></span>
                            </div>

                            <!-- Date Of Joining -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="joining_date">Date Of Joining<b class="require">*</b></label>
                                <input type="date" name="joining_date" class="form-control" id="joining_date" value="<?php if (!empty($single)) {
                                                                                                                            echo $single->date_of_joininig;
                                                                                                                        } ?>" required>
                                <span id="joining_date_error" class=""></span>
                            </div>

                            <!-- Employee Password -->
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="employee_password">Employee Password<b class="require">*</b></label>
                                <input type="text" name="employee_password" class="form-control" id="employee_password"
                                    value="<?php if (!empty($single)) {
                                                echo $single->org_password;
                                            } ?>" placeholder="Please enter password" required>
                                <span id="employee_password_error" class=""></span>
                            </div>


                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.krivisha_employee').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        $('#department_id').select2({
            placeholder: "Please select department(s)"
        });
        $('#plant_id').select2({
            placeholder: "Please select plant(s)"
        });
    });
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function(value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter cannot be space!");
    $.validator.addMethod("validMobile", function(value, element) {
        return this.optional(element) || (/^[0-9]{10}$/.test(value) && !/^(.)\1{9}$/.test(value));
    }, "Please enter a valid mobile number!");
    $.validator.addMethod("validEmail", function(value, element) {
        // Regular expression for validating email format
        return this.optional(element) || /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
    }, "Please enter a valid email address!");

    $(document).ready(function() {
        $('#employee_form').validate({
            ignore: [],
            rules: {
                employee_name: {
                    required: true,
                    noSpaceAtStart: true
                },

                employee_email: {
                    required: true,
                    email: true,
                    validEmail: true
                },
                employee_contact: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 10,
                    validMobile: true
                },
                'department_id[]': {
                    required: true
                },
                'plant_id[]': {
                    required: true
                },
                designation: {
                    required: true,
                    noSpaceAtStart: true
                },
                joining_date: {
                    required: true,
                },
                employee_password: {
                    required: true,
                    noSpaceAtStart: true
                }
            },
            messages: {
                employee_name: {
                    required: "Please enter employee name!",
                    noSpaceAtStart: "First letter cannot be space!"
                },
                employee_email: {
                    required: "Please enter employee email!",
                    email: "Please enter a valid email address!",
                    validEmail: "Please enter a valid email address!"
                },
                employee_contact: {
                    required: "Please enter contact number!",
                    digits: "Contact number can only contain digits!",
                    minlength: "Contact number must be at least 10 digits!",
                    maxlength: "Contact number must not exceed 10 digits!",
                    validMobile: "Please enter a valid mobile number!"
                },
                'plant_id[]': {
                    required: "Please select at least one plant!"
                },
                'department_id[]': {
                    required: "Please select at least one department!"
                },
                designation: {
                    required: "Please enter employee designation!",
                    noSpaceAtStart: "First letter cannot be space!"
                },
                joining_date: {
                    required: "Please select date of joining!",
                },
                employee_password: {
                    required: "Please enter a password!",
                    noSpaceAtStart: "First letter cannot be space!",
                    // minlength: "Password must be at least 6 characters long!"
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $("#department_id").change(function() {
            $("#department_id").valid();
        });
        $("#plant_id").change(function() {
            $("#plant_id").valid();
        });
        $("#joining_date").change(function() {
            $("#joining_date").valid();
        });
        // Trigger validation on select2 multi-select change
        $('#department_id, #plant_id').on('change', function() {
            $(this).valid();
        });
    });
</script>

<script>
    $('#employee_contact').on('keyup', function() {
        var employee_contact = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_employee_id',
            method: 'post',
            data: {
                'employee_contact': employee_contact,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if (response == '1') {
                    $('#employee_contact_error').text("This contact is already added!");
                    $('#employee_contact_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#employee_contact_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    // $('#department_id').change(function () {
    //     var department = $(this).find('option:selected').text().trim(); // Added .trim()
    //     if (department) {
    //         $.ajax({
    //             url: '<?= base_url('admin/Ajax_controller/get_plants_by_department') ?>',
    //             type: 'POST',
    //             data: { department: department },
    //             dataType: 'json',
    //             success: function (data) {
    //                 $('#plant_id').empty().append('<option value="">Select Plant</option>');
    //                 $.each(data, function (index, plant) {
    //                     $('#plant_id').append('<option value="' + plant.id + '">' + plant.plant_name + '</option>');
    //                 });
    //             },
    //             error: function () {
    //                 alert('Error retrieving plants. Please try again.');
    //             }
    //         });
    //     } else {
    //         $('#plant_id').empty().append('<option value="">Select Plant</option>');
    //     }
    // });

    $('#employee_email').on('keyup', function() {
        var employee_email = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_employee_email',
            method: 'post',
            data: {
                'employee_email': employee_email,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if (response == '1') {
                    $('#employee_email_error').text("This email is already registered!");
                    $('#employee_email_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#employee_email_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
</script>