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

    .js-example-basic-multiple {
        display: none;
    }

    .select2-container {
        width: 100% !important;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Update Task</h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_task_form" id="add_task_form" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Status</label>
                                <select class="form-control js-example-basic-multiple" name="task_status"
                                    id="task_status">
                                    <option value="1" <?php if (!empty($single) && $single->task_status == 1) { ?>selected<?php } ?>>Pending</option>
                                    <option value="2" <?php if (!empty($single) && $single->task_status == 2) { ?>selected<?php } ?>>Complete</option>
                                </select>
                            </div>
                            <input type="hidden" id="party_id" name="party_id" value="<?= $this->uri->segment(4) ?>">
                            <input type="hidden" id="order_id" name="order_id" value="<?php if (!empty($single)) {
                                echo $single->task_id;
                            } ?>">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Task Action<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="task_action"
                                    id="task_action">
                                    <option value="">Select Option</option>
                                    <option value="1" <?php if (!empty($single) && $single->task_action == 1) { ?>selected<?php } ?>>Forward to other Department/Person</option>
                                    <option value="2" <?php if (!empty($single) && $single->task_action == 2) { ?>selected<?php } ?>>Mark as Closed</option>
                                    <option value="3" <?php if (!empty($single) && $single->task_action == 3) { ?>selected<?php } ?>>Create Order</option>
                                </select>
                            </div>
                           
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 department d-none">
                                <label>Assign To Department<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_department"
                                    id="assign_departments">
                                    <option value="">Please Select Department</option>
                                    <?php
                                    $order_id = $this->uri->segment(2);
                                    $check_order_department = $this->db->select('order_department_status')
                                        ->from('tbl_auto_task_list')
                                        ->where('id', $order_id)
                                        ->where('type_of_order', '2')
                                        ->where('is_deleted', '0')
                                        ->get()
                                        ->row();

                                    if (!empty($krivisha_department)) {
                                        foreach ($krivisha_department as $make_result) {
                                            $status = $check_order_department->order_department_status ?? null;

                                            // Skip department id 21 if status is not 2 or 3
                                            if (($status == '1') && $make_result->id == 11) {
                                                continue;
                                            }

                                            $selected = (!empty($single) && $single->department_id == $make_result->id) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $make_result->id ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($make_result->department) ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="assign_department_name" id="assign_department_name" value="">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 employee d-none">
                                <label>Assign To<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_to" id="assign_to">
                                    <option value="">Please Select</option>
                                    <?php if (!empty($single)) {
                                        $department = $this->Admin_model->get_employee_by_department($single->department_id);
                                        foreach ($department as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>" <?php if (!empty($single) && $single->assign_to_id == $make_result->id) { ?>selected<?php } ?>>
                                                <?= $make_result->first_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Remarks</label>
                                <input autocomplete="off" type="text" class="form-control"
                                    placeholder="Enter Details of Enquiry" name="enquiry_details" id="enquiry_details"
                                    value="<?php if (!empty($single)) {
                                        echo $single->details_of_task;
                                    } ?>">
                            </div>


                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ">
                                    <button id="submit_btn" type="submit" value="submit_btn" name="submit_btn"
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
    $(document).ready(function () {
        // $('#task_management .child_menu').show();
        // $('#task_management').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_task').addClass('active_cc');
        $('#task_management').addClass('nv active');

    });
</script>
<script>
    $(document).ready(function () {
        flatpickr("#complete_by_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        });
        flatpickr("#complete_by_date", {
            dateFormat: "d-m-Y",
        });
        var selectedValue = $('#task_action').val();
        if (selectedValue === "1") {
            $(".department").removeClass("d-none");
            $(".employee").removeClass("d-none");
        }

        $("#task_action").on('change', function () {
            $("#task_action").valid();
            var selectedValue = this.value;
            if (selectedValue === "1") {
                $(".department").removeClass("d-none");
                $(".employee").removeClass("d-none");
            } else {
                $(".department").addClass("d-none");
                $(".employee").addClass("d-none");
            }
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
        $('#assign_departments').on('change', function () {
            var selectedText = $(this).find("option:selected").text();
            $('#assign_department_name').val(selectedText);
        });
        $('#assign_departments').trigger('change');
        $('.js-example-basic-multiple').select2();
        var markAsClosedOption = $('#task_action option[value="2"]').clone();
        var uriSegment3 = "<?php echo $this->uri->segment(3); ?>";
        if (uriSegment3 == "143" || uriSegment3 != "Enquiry") {
            $('#task_action option[value="3"]').remove();
        }
        function toggleTaskAction() {
            var taskStatus = $('#task_status').val();

            if (taskStatus == "1") {
                $('#task_action option[value="2"]').remove();
                if ($('#task_action').val() == "2") {
                    $('#task_action').val('');
                }
            } else {
                if ($('#task_action option[value="2"]').length === 0) {
                    $('#task_action').append(markAsClosedOption.clone());
                }
            }

            if ($('#task_action').hasClass('js-example-basic-multiple')) {
                $('#task_action').trigger('change');
            }
        }

        toggleTaskAction();
        $('#task_status').on('change', toggleTaskAction);
    });
</script>



<script>
    $(document).ready(function () {
        var selectedValue = $('#task_action').val();
        $('.js-example-basic-multiple').select2();


        $('#add_task_form').validate({
            ignore: [],
            rules: {
                task_action: {
                    required: true
                },
                assign_departments: {
                    required: function () {
                        return $selectedValue == 1;
                    }
                },
                assign_to: {
                    required: function () {
                        return $selectedValue == 1;
                    }
                },
            },
            messages: {
                task_action: {
                    required: "Please select an action!"
                },
                assign_departments: {
                    required: "Please select a department!"
                },
                assign_to: {
                    required: "Please select an employee!"
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                if (element.hasClass('select2-hidden-accessible')) {
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
                form.submit();
            },
        });
        $("#assign_departments").change(function () {
            $("#assign_departments").valid();
        });
        $('#assign_to').change(function () {
            $('#assign_to').valid();
        });
    });
</script>