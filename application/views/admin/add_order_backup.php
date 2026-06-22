
<!-- <<script>
    $(document).ready(function() {
        
        $('.js-example-basic-multiple').select2();

        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        }, "First letter can not be space");
        jQuery.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });
        $('#add_task_form').validate({
            ignore: ":hidden:not(select)",
            
               
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.hasClass('select2-hidden-accessible')) {
                    element.next('span').append(error);
                } else {
                    element.closest('.form-group').append(error);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script> -->


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
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
            <?php if(!empty($single)) { ?>
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

                        <div class="row flex_wrap">
                            
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Task Head <b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" aria-placeholder="Please Choose Task Head" name="task_head" id="task_head">
                                    <option value="">Please select option</option>
                                    <option value="1">Enquiry</option>
                                    <option value="2">Cold Call</option>
                                    <option value="3">Office Requirement</option>
                                    <option value="4">Self Task</option>
                                    <option value="5">Complaint</option>
                                    
                                </select>

                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 task_depatment d-none">
                                <label>Select Task Flow<b class="require">*</b></label>
                                <select name="task_depatment" id="task_depatment" class="form-control js-example-basic-multiple" value=""  >
                                    <option value="">Please select department</option>
                                    <option value="1">Manual Order</option>
                                    <option value="2">Production Schedule</option>
                                    <option value="3">Production</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 auto_task d-none">
                                <label>Select Task<b class="require">*</b></label>
                                <select name="auto_task" id="auto_task" class="form-control js-example-basic-multiple" value=""  >
                                    <option value="">Please select task</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 party" >
                                <label>Party Name<b class="require">*</b></label>
                                <select name="party_name" id="party_name" class="form-control js-example-basic-multiple" value="" placeholder="Enter Party Name" >
                                    <option value="">Please select party</option>
                                    <?php if (!empty($party_name)){
                                     foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>"><?= $party_result->party_name ?></option>
                                        <?php }} ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Complete By Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Please select date" name="complete_by_date" id="complete_by_date">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Complete By Time<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Please select time" name="complete_by_time" id="complete_by_time">
                            </div>


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Priority<b class="require">*</b></label>
                                <div class="form-control form-radio">   

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" value="1" id="high">
                                        <label class="form-check-label" for="high">
                                            High
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="priority" value="2" id="medium">
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
                                <input autocomplete="off" type="text" class="form-control" placeholder="Enter additional comments/updates" name="remark" id="remark">
                            </div>



                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Assign To Department<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_department" id="assign_departments">
                                    <option value="">Select Option</option>
                                    <option value="Accounts">Accounts</option>
                                   
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Assign To<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_to" id="assign_to">
                                    <option value="">Select Option</option>
                                    <option value="1">Ganesh Waghmare</option>
                                    <option value="2">Shakambari Hawaldar</option>
                                   
                                </select>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                    <button type="submit" id ="submit_btn" name = "submit_btn" value="submit_btn" class="btn btn-primary">Submit</button>
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
    $(document).ready(function() {
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
        // $('#auto_task').select2({
        //     placeholder: "Please select task"
        // });
           

    });
</script>

<script>
    $(document).ready(function() {
        $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.add_task').addClass('active_cc');      
    });
</script>

<script>
    
    $(document).ready(function() {
        flatpickr("#complete_by_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        });
        flatpickr("#complete_by_date", {
            dateFormat: "d-m-Y",
        });
        $("#task_head").on('change', function () {
            var task_head = $('#task_head').val();
            if (task_head === '3') {
                $('.task_depatment').removeClass('d-none');
                $('.party').addClass('d-none');
            }else{
                $('.task_depatment').addClass('d-none');
                $('.auto_task').addClass('d-none');
                $('.party').removeClass('d-none');
            }

        })
        $('#auto_task').select2({
            templateResult: formatOption,  
            templateSelection: formatSelection 
        });
        $(document).on('click', '.btn-info', function() {
            console.log('Eye button clicked');
            var id = $(this).data('id');
            console.log('ID: ' + id);
            showOrderDetails(id);
        });
        
       
        $('#task_depatment').on('change', function() {
            var task_depatment = $('#task_depatment').val();
            if (task_depatment === '1') {
                $('.auto_task').removeClass('d-none');  
            }
            $.ajax({
                type: "POST",
                url: "<?= base_url("admin/Ajax_controller/get_all_task_list") ?>",
                data: {
                    'task_depatment': task_depatment
                },
                success: function(response) {
                    console.log(response);
                    $("#auto_task").empty();
                    $('#auto_task').append('<option value="">Select type</option>');
                    var opts = $.parseJSON(response);
                    console.log(opts);
                    $.each(opts, function(i, d) {
                        if (task_depatment == '1') {
                            $('#auto_task').append('<option value="' + d.id + '">' + d.party_name + ' ' + d.id + '</option>');
                        } else if (task_depatment == '2') {
                            $('#auto_task').append('<option value="' + d.type_id + '">' + d.article_name + '</option>');
                        } else if (task_depatment == '3') {
                            $('#auto_task').append('<option value="' + d.type_id + '">' + d.printing_name + '</option>');
                        }
                    });
                    $('#auto_task').trigger('chosen:updated');
                }
            });

        });

    });
    function formatOption(option) {
        if (!option.id) {
            return option.text;
        }

        var eyeButton = '<button type="button" class="btn btn-info btn-sm" data-id="' + option.id + '" title="Group Of Article"> <i class="fa fa-eye"></i></button>';

        var spanContent = $('<span style="display: flex; justify-content: space-between; width: 100%;">' + option.text + ' ' + option.id + '</span>');

        var buttonContainer = $('<span style="margin-left: auto;"></span>').append(eyeButton);

        spanContent.append(buttonContainer);
        
        console.log('Formatted option:', spanContent);

        return spanContent;
    }

    function formatSelection(option) {
        console.log('Selected option:', option);
        return option.text;
    }

    function showOrderDetails(id) {
        console.log('Showing details for ID:', id);
        alert('Show details for ID: ' + id);
    }
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
                // party_name: {
                //     required: true
                // },
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
                // priority: {
                //     required: true
                // }        
            },
            messages: {
                task_head: {
                    required: "Please select task head!"
                },
                party_name: {
                    required: "Please select party name!"
                },
                assign_department: {
                    required: "Please select department!"
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
                // priority: {
                //     required: "Please select priority!"
                // }
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
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        $("#task_head").change(function() {
            $("#task_head").valid();
        });
        $("#assign_departments").change(function() {
            $("#assign_departments").valid();
        });
        $("#assign_to").change(function() {
            $("#assign_to").valid();
        });
        $("#party_name").change(function() {
            $("#party_name").valid();
        });
        $("#auto_task").change(function() {
            $("#auto_task").valid();
        });
        $("#complete_by_date").change(function() {
            $("#complete_by_date").valid();
        });
        $("#complete_by_time").change(function() {
            $("#complete_by_time").valid();
        });
        $("#task_depatment").change(function() {
            $("#task_depatment").valid();
        });

 
</script>
