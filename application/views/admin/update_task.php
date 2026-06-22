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
                    <form method="post" name="update_task" id="update_task" >

                        <div class="row flex_wrap">


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Task ID<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="task_id" id="task_id">
                                    <option value="">Select Option</option>
                                   <option value="CC100">CC100</option>
                                   <option value="CC100">CC101</option>
                                   <option value="CC100">CC102</option>
                                   <option value="CC100">CC103</option>
                                   <option value="CC100">CC104</option>

                                   <option value="CC100">CC105</option>
                                   <option value="CC100">CC106</option>
                                   <option value="CC100">CC107</option>
                                   <option value="CC100">CC108</option>
                                </select>
                            </div>

    


                            

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Person To<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="give_to" id="give_to ">
                                    <option value="">Select Option</option>
                                    <option value="Ganesh Waghmare">Ganesh Waghmare</option>
                                    <option value="Nanda Asude">Nanda Asude</option>
                                    <option value="Shakambari Hawaldar">Shakambari Hawaldar</option>
                                    <option value="Jyoti Shinde">Jyoti Shinde</option>
                                    <option value="Narendra Tiwari">Narendra Tiwari</option>
                                    <option value="Rajendra Koshti">Rajendra Koshti</option>
                                    <option value="Krishna Shedji">Krishna Shedji</option>
                                    <option value="Alka Pujugullu">Alka Pujugullu</option>
                                    <option value="Ganesh Vartile">Ganesh Vartile</option>
                                    <option value="Mohan Shete">Mohan Shete</option>
                                    <option value="Sanjay Kamble">Sanjay Kamble</option>
                                    <option value="Santosh Kale">Santosh Kale</option>
                                    <option value="Nitin Jadhav">Nitin Jadhav</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Assign To Department<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="assign_department" id="assign_departments">
                                    <option value="">Select Option</option>
                                    <option value="Accounts">Accounts</option>
                                    <option value="Production">Production</option>
                                    <option value="Printing">Printing</option>
                                    <option value="Dispatch">Dispatch</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Administrative">Administrative</option>
                                    <option value="Management (Krishna)">Management (Krishna)</option>
                                </select>
                            </div>


                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="form-group ">
                                    <button id="submit" type="submit" class="btn btn-primary">Submit</button>
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
    $(document).ready(function() {
        $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.update_task').addClass('active_cc');
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

    });
</script>



<<script>
    $(document).ready(function() {
        
        $('.js-example-basic-multiple').select2();


        $('#update_task').validate({
            ignore: ":hidden:not(select)",
            rules: {
                task_id: {
                    required: true
                },
                name: 'required',
                task_status: 'required',
                task_action: 'required',
                give_to: 'required',
                comments: 'required',

            },
            messages: {
                // custom messages
            },
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
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
