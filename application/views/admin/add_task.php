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

    .modelclass {
        max-width: 60%;
        width: auto;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                <?php if (!empty($single)) { ?>
                    Update Task
                <?php } else { ?>
                    Add Task
                <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_task_form" id="add_task_form" enctype="multipart/form-data">
                        <input type="hidden" name="task_type" id="task_type" value="manual">
                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Task Head <b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple"
                                    aria-placeholder="Please Choose Task Head" name="task_head" id="task_head">
                                    <option value="">Please select option</option>
                                    <option value="1">Enquiry</option>
                                    <option value="2">Cold Call</option>
                                    <option value="3">Office Requirement</option>
                                    <option value="4">Self Task</option>
                                    <option value="5">Complaint</option>

                                </select>

                            </div>


                            <div class="form-group col-md-4 col-sm-6 col-xs-12 party">
                                <label>Party Name</label>
                                <select name="party_name" id="party_name" class="form-control js-example-basic-multiple"
                                    value="" placeholder="Enter Party Name">
                                    <option value="">Please select party</option>
                                    <?php if (!empty($party_name)) {
                                        foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>"><?= $party_result->party_name ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Complete By Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control"
                                    placeholder="Please select date" name="complete_by_date" id="complete_by_date">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Complete By Time<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control"
                                    placeholder="Please select time" name="complete_by_time" id="complete_by_time">
                            </div>


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Priority<b class="require">*</b></label>
                                <div class="form-control form-radio">

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" value="1"
                                            id="high">
                                        <label class="form-check-label" for="high">
                                            High
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" value="2"
                                            id="medium">
                                        <label class="form-check-label" for="medium">
                                            Medium
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" value="3" id="low">
                                        <label class="form-check-label" for="low">
                                            Low
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Additional Comments/ Updates</label>
                                <input autocomplete="off" type="text" class="form-control"
                                    placeholder="Enter additional comments/updates" name="remark" id="remark">
                            </div>



                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Assign To Department<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_department"
                                    id="assign_departments">
                                    <option value="">Please Select Department</option>
                                    <?php if (!empty($krivisha_department)) {
                                        foreach ($krivisha_department as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>" <?php if (!empty($single) && $single->department_id == $make_result->id) { ?>selected<?php } ?>>
                                                <?= $make_result->department ?>
                                            </option>
                                        <?php }
                                    } ?>

                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Assign To<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_to" id="assign_to">
                                    <option value="">Please Select Department</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
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
?>
<script>
    $(document).ready(function () {
        $('#party_name').select2({
            placeholder: "Please select party name"
        });
        $('#task_head').select2({
            placeholder: "Please select task head"
        });
        $('#assign_departments').select2({
        });
        $('#assign_to').select2({
        });
        $('#task_depatment').select2({
        });
        $('#assign_departments').on('change', function () {
            var departmentId = $(this).val();
            $('#assign_to').html('<option value="">Loading...</option>');
            if (departmentId) {
                $.ajax({
                    url: '<?= base_url("admin/Ajax_controller/get_employees_by_department") ?>',
                    type: 'POST',
                    data: { department_id: departmentId },
                    dataType: 'json',
                    success: function (data) {
                        var options = '<option value="">Please Select</option>';
                        $.each(data, function (index, employee) {
                            options += '<option value="' + employee.id + '">' + employee.first_name + '</option>';
                        });
                        $('#assign_to').html(options);
                    }
                });
            } else {
                $('#assign_to').html('<option value="">Please Select Department</option>');
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_task').addClass('active_cc');
        // $('#task_management').addClass('nv active-color');
        flatpickr("#complete_by_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        });
        flatpickr("#complete_by_date", {
            dateFormat: "d-m-Y",
        });
    });
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    jQuery.validator.addMethod("noNumbers", function (value, element) {
        return this.optional(element) || !/\d/.test(value);
    });

    $('#add_task_form').validate({
        ignore: ":hidden:not(select)",
        rules: {
            task_head: {
                required: true
            },
            assign_department: {
                required: true
            },
            assign_to: {
                required: true
            },
            complete_by_date: {
                required: true
            },
            complete_by_time: {
                required: true
            },
            priority: {
                required: true
            }
        },
        messages: {
            task_head: {
                required: "Please select task head!"
            },
            assign_to: {
                required: "Please select assign to!"
            },
            complete_by_date: {
                required: "Please select date!"
            },
            complete_by_time: {
                required: "Please select time!"
            },
            priority: {
                required: "Please select priority!"
            }
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
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    $("#task_head").change(function () {
        $("#task_head").valid();
    });
    $("#assign_departments").change(function () {
        $("#assign_departments").valid();
    });
    $("#assign_to").change(function () {
        $("#assign_to").valid();
    });
    $("#auto_task").change(function () {
        $("#auto_task").valid();
    });
    $("#complete_by_date").change(function () {
        $("#complete_by_date").valid();
    });
    $("#complete_by_time").change(function () {
        $("#complete_by_time").valid();
    });
    $("#task_depatment").change(function () {
        $("#task_depatment").valid();
    });
    $("#auto_task").change(function () {
        $("#auto_task").valid();
    });
</script>