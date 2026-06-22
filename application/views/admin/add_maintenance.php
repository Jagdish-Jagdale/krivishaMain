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
        max-width: 100% !important;
    }
</style>
<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <?php if (!empty($single)) { ?>
                <h3>Update Maintenance Report</h3>
            <?php } else { ?>
                <h3>Add Maintenance Report</h3>
            <?php } ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_production" id="add_production" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label>Plant Name<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    name="plant" id="plant">
                                    <option value="">Please select plant</option>
                                    <?php if (!empty($plant)) {
                                        foreach ($plant as $plant_result) { ?>
                                            <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>>
                                                <?= $plant_result->plant_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="emp_name">Employee Name<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    name="employee" id="employee">
                                    <option value="">Please select employee</option>
                                    <?php
                                    $loggedInEmployeeId = $this->session->userdata("id");
                                    if (!empty($employee)) {
                                        foreach ($employee as $employee_result) {
                                            if ($employee_result->id == $loggedInEmployeeId) {
                                                ?>
                                                <option value="<?= $employee_result->id ?>" selected>
                                                    <?= $employee_result->first_name ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Select Date"
                                    name="date" id="date"
                                    value="<?php echo (!empty($single)) ? date('d-m-Y', strtotime($single->date)) : ''; ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Type of Action<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    name="type_action" id="type_action">
                                    <option value="">Type of Action</option>
                                    <option value="1" <?php echo (!empty($single) && $single->maintaince == '1') ? 'selected' : ''; ?>>Emergency</option>
                                    <option value="2" <?php echo (!empty($single) && $single->maintaince == '2') ? 'selected' : ''; ?>>Online Breakdown</option>
                                    <option value="3" <?php echo (!empty($single) && $single->maintaince == '3') ? 'selected' : ''; ?>>Preventive</option>
                                    <option value="4" <?php echo (!empty($single) && $single->maintaince == '4') ? 'selected' : ''; ?>>Outside Work</option>
                                    <option value="5" <?php echo (!empty($single) && $single->maintaince == '5') ? 'selected' : ''; ?>>General</option>
                                    <option value="6" <?php echo (!empty($single) && $single->maintaince == '6') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Maintenance required for<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    name="maintain_action" id="maintain_action">
                                    <option value="">Please select maintenance</option>
                                    <option value="1" <?php echo (!empty($single) && $single->maintaince == '1') ? 'selected' : ''; ?>>Machine</option>
                                    <option value="2" <?php echo (!empty($single) && $single->maintaince == '2') ? 'selected' : ''; ?>>Mould/Article Name</option>
                                    <option value="3" <?php echo (!empty($single) && $single->maintaince == '3') ? 'selected' : ''; ?>>Printing Unit</option>
                                    <option value="4" <?php echo (!empty($single) && $single->maintaince == '4') ? 'selected' : ''; ?>>Plant</option>
                                    <option value="5" <?php echo (!empty($single) && $single->maintaince == '5') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Subcategory Maintenance<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    name="sub_type" id="sub_type">
                                    <option value="">Please select subcategory</option>
                                    <?php if (!empty($single)) {
                                        $sub_type_id = $single->sub_type_id;
                                        $maintaince = $single->maintaince;
                                        $machine_name_id = $this->Admin_model->get_all_sub_types_display($maintaince, $sub_type_id);
                                        if ($maintaince != '5') {
                                            foreach ($machine_name_id as $machine_result) { ?>
                                                <option value="<?= $machine_result->type_id ?>" <?php if (!empty($single) && $sub_type_id == $machine_result->type_id) { ?> selected <?php } ?>>
                                                    <?= $machine_result->name ?>
                                                </option>
                                            <?php }
                                        } else { ?>
                                            <option value="5" selected>N/A</option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Details of Maintenance<b class="require">*</b></label>
                                <select style="display: none;" class="form-control js-example-basic-multiple"
                                    aria-placeholder="Please Choose Article" multiple="multiple"
                                    name="details_maintainance[]" id="details_maintainance">
                                    <option value="">Choose Multiple</option>
                                    <?php if (!empty($single)) {
                                        $sub_type_id = $single->sub_type_id;
                                        $maintaince = $single->maintaince;
                                        $machine_name_id = $this->Admin_model->get_all_problems_display($maintaince, $sub_type_id);
                                        $problem_ids = !empty($single->problem_id) ? explode(',', $single->problem_id) : [];
                                        if ($machine_name_id != '') {
                                            foreach ($machine_name_id as $machine_result) { ?>

                                                <option value="<?= $machine_result->id ?>" <?php if (in_array($machine_result->id, $problem_ids)) { ?> selected <?php } ?>>

                                                    <?= $machine_result->problem ?>

                                                </option>

                                            <?php }
                                        }
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group mt-3">
                                    <button type="submit" name="submit_btn" value="submit_btn"
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
<?php include('footer.php'); ?>
<script>
    flatpickr("#date", {
        dateFormat: "d-m-Y",
        minDate: new Date(),
        // maxDate: new Date() ,
    });
</script>
<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please Select"
        });
        $('#maintain_action').select2({
            placeholder: "Please select maintenance"
        });
        $('#plant').select2({
            placeholder: "Please select plant"
        });
        $('#employee').select2({
            placeholder: "Please select employee"
        });
        $('#type_action').select2({

            placeholder: "Please select type action"
        });
        $('#sub_type').select2({
            placeholder: "Please select sub category"
        });
        $('#details_maintainance').select2({
            placeholder: "Please select details of maintenance"
        });
        $('#maintain_action').on('change', function () {
            var selected_master = $('#maintain_action').val();
            $('#sub_type').html('<option value="">Select Type</option>');
            $.ajax({
                type: "POST",
                url: "<?= base_url("admin/Ajax_controller/get_machine_types") ?>",
                data: {
                    'selected_master': selected_master
                },
                success: function (response) {
                    $("#sub_type").empty();
                    $("#details_maintainance").empty();
                    $('#sub_type').append('<option value="">Select Subcategory Maintenance</option>');
                    var opts = $.parseJSON(response);
                    $.each(opts, function (i, d) {
                        if (selected_master == '1') {
                            $('#sub_type').append('<option value="' + d.type_id + '">' + d.machine_name + '</option>');
                        } else if (selected_master == '2') {
                            $('#sub_type').append('<option value="' + d.type_id + '">' + d.article_name + '</option>');
                        } else if (selected_master == '3') {
                            $('#sub_type').append('<option value="' + d.type_id + '">' + d.machine_name + '</option>');
                        } else if (selected_master == '4') {
                            $('#sub_type').append('<option value="' + d.type_id + '">' + d.plant_name + '</option>');
                        } else if (selected_master == '5') {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        }
                    });
                    $('#sub_type').trigger('chosen:updated');
                }
            });
        });
        $('#sub_type').on('change', function () {
            var selected_type = $('#sub_type').val();
            var selected_master = $('#maintain_action').val();
            $('#details_maintainance').html('<option value="">Select Type</option>');
            $.ajax({
                type: "POST",
                url: "<?= base_url("admin/Ajax_controller/get_all_sub_types") ?>",
                data: {
                    'selected_type': selected_type,
                    'selected_master': selected_master
                },
                success: function (response) {
                    console.log(response);
                    $("#details_maintainance").empty();
                    $('#details_maintainance').append('<option value="">Select details of maintenance</option>');
                    var opts = $.parseJSON(response);
                    console.log(opts);
                    $.each(opts, function (i, d) {
                        if (selected_master == '1') {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        } else if (selected_master == '2') {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        } else if (selected_master == '3') {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        } else if (selected_master == '4') {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        } else {
                            $('#details_maintainance').append('<option value="' + d.id + '">' + d.problem + '</option>');
                        }
                    });
                    $('#details_maintainance').trigger('chosen:updated');
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function () {
        // $('#maintenance .child_menu').show();
        $('#maintenance').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_maintenance').addClass('active_cc');
        // $('#maintenance').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        $('#add_production').validate({
            ignore: [],
            rules: {
                plant: {
                    required: true,
                },
                date: {
                    required: true
                },
                employee: 'required',
                type_action: 'required',
                maintain_action: 'required',
                sub_type: {
                    required: function () {
                        return $('#maintain_action').val() != '5';
                    },
                },
                'details_maintainance[]': 'required',
            },
            messages: {
                plant: {
                    required: "Please select plant!",
                },
                date: {
                    required: "Please select date!",
                },
                employee: {
                    required: "Please select employee!",
                },
                type_action: {
                    required: "Please select type!",
                },
                maintain_action: {
                    required: "Please select maintenance!",
                },
                sub_type: {
                    required: "Please select sub category!",
                },
                'details_maintainance[]': {
                    required: "Please select details of maintenance!",
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
        $("#plant").change(function () {
            $("#plant").valid();
        });
        $("#date").change(function () {
            $("#date").valid();
        });
        $("#employee").change(function () {
            $("#employee").valid();
        });
        $("#type_action").change(function () {
            $("#type_action").valid();
        });
        $("#sub_type").change(function () {
            $("#sub_type").valid();
        });
        $("#details_maintainance").change(function () {
            $("#details_maintainance").valid();
        });
        $("#maintain_action").change(function () {
            $("#maintain_action").valid();
        });
    });
</script>