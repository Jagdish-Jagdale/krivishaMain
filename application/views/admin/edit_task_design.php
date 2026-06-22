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
            <h3>Add Task</h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_task_form" id="add_task_form" >

                        <div class="row flex_wrap">




                            
                            
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Status</label>
                                <select class="form-control js-example-basic-multiple" name="task_status" id="task_status">
                                    <option value="">Pending</option>
                                    <option value="">Complete </option>
                

                                </select>
                            </div>



                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Task Action</label>
                                <select class="form-control js-example-basic-multiple" name="task_action" id="task_action">
                                    <option value="">Select Option</option>
                                    <option  value="Accounts">Forward to other Department/Person</option>
                                    <option  value="Production">Mark as Closed</option>
                        
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 department d-none">
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

                            <div class="form-group col-md-4 col-sm-6 col-xs-12 employee d-none" >
                                <label>Assign To<b class="require">*</b></label>
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
                                <label>Remarks<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Enter Details of Enquiry" name="enquiry_details" id="enquiry_details">
                            </div>


                            <div class="col-md-12 col-sm-12 col-xs-12">
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

    $("#task_action").on('change', function () {
        var selectedValue = this.value;
        console.log(selectedValue);
        if (selectedValue === "Accounts") {
            $(".department").removeClass("d-none");
            $(".employee").removeClass("d-none");
        } else {
            $(".department").addClass("d-none");
            $(".employee").addClass("d-none");
        }
    });
});
</script>



<<script>
    $(document).ready(function() {
        
        $('.js-example-basic-multiple').select2();


        $('#add_task_form').validate({
            ignore: ":hidden:not(select)",
            rules: {
                date: {
                    required: true
                },
               
                
                task_head: 'required',
                assign_department: 'required',
                give_to: 'required',
                complete_by_date: 'required',
                complete_by_time: 'required',
                enquiry_details: 'required',
                
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
