<?php include('header.php');?>
<style>
	@keyframes loading {
		0%, 20% {
			opacity: 1; 
		}
		40%, 60% {
			opacity: 0; 
		}
		80%, 100% {
			opacity: 1;
		}
	}

	#loader span {
		display: inline-block;
		width: 8px; 
		height: 8px;
		background-color: #000; 
		border-radius: 50%;
		margin: 0 2px;
		animation: loading 1.4s infinite; 
	}

	#loader .dot1 {
		animation-delay: 0s;
	}

	#loader .dot2 {
		animation-delay: 0.2s;
	}

	#loader .dot3 {
		animation-delay: 0.4s;
	}

	#loader .dot4 {
		animation-delay: 0.6s;
	}
	.close_crop_model{
		padding: 6px 44px !important;
	}
</style>
    <div class="container-fluid page-body-wrapper">
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-md-6 grid-margin stretch-card priviledge-form">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Exam Setup</h4>
                  <p class="card-description">
                    Please enter Exam Setup details
                  </p>
                  <form class="forms-sample" name="id_form" id="id_form" method="post" enctype="multipart/form-data">
					    <div class="row">
							<div class="form-group col-md-4">
                                <label class=" form-control-label">Student Type * </label>
                                <select name="student_type" id="student_type" class="form-control js-example-basic-single" onchange="uniqueExamSetup()">
                                    <option value="">Select Student Type</option>
                                    <option value="0" <?php if(!empty($single) && $single->student_type == '0'){ echo 'selected';}?>>Regular</option>
                                    <option value="1" <?php if(!empty($single) && $single->student_type == '1'){ echo 'selected';}?>>Blended</option>
                                </select>	
							</div>
							<div class="form-group col-md-4">
                                <label class=" form-control-label">Exam Month * </label>
                                <select name="month" id="month" class="form-control js-example-basic-single" onchange="uniqueExamSetup()">
                                    <option value="">Select Exam Month</option>
                                    <option value="January" <?php if(!empty($single) && $single->month == 'January'){ echo 'selected';}?>>January</option>
                                    <option value="February" <?php if(!empty($single) && $single->month == 'February'){ echo 'selected';}?>>February</option>
                                    <option value="March" <?php if(!empty($single) && $single->month == 'March'){ echo 'selected';}?>>March</option>
                                    <option value="April" <?php if(!empty($single) && $single->month == 'April'){ echo 'selected';}?>>April</option>
                                    <option value="May" <?php if(!empty($single) && $single->month == 'May'){ echo 'selected';}?>>May</option>
                                    <option value="June" <?php if(!empty($single) && $single->month == 'June'){ echo 'selected';}?>>June</option>
                                    <option value="July" <?php if(!empty($single) && $single->month == 'July'){ echo 'selected';}?>>July</option>
                                    <option value="August" <?php if(!empty($single) && $single->month == 'August'){ echo 'selected';}?>>August</option>
                                    <option value="September" <?php if(!empty($single) && $single->month == 'September'){ echo 'selected';}?>>September</option>
                                    <option value="October" <?php if(!empty($single) && $single->month == 'October'){ echo 'selected';}?>>October</option>
                                    <option value="November" <?php if(!empty($single) && $single->month == 'November'){ echo 'selected';}?>>November</option>
                                    <option value="December" <?php if(!empty($single) && $single->month == 'December'){ echo 'selected';}?>>December</option>
                                </select>	
							</div>
							<div class="form-group col-md-4">
                                <label class=" form-control-label">Exam Year * </label>
                                <select name="year" id="year" class="form-control js-example-basic-single" onchange="uniqueExamSetup()">
                                    <option value="">Select Exam Year</option>
                                    <?php for($year=2021;$year <= date("Y");$year++){?>
                                    <option value="<?=$year?>" <?php if(!empty($single) && $single->year == $year){ echo 'selected';}?>><?=$year?></option>
                                    <?php }?>
                                </select>	
							</div>
                            <div class="form-group col-md-12 error" id="student_error"></div>
						</div>
					    <div class="row">
                            <div class="form-group col-md-6">
                                <label>Result Declare Date</label>
                                <input type="text" class="form-control datepicker" name="result_declare_date" id="result_declare_date" value="<?php if(!empty($single)){ echo date('d-m-Y',strtotime($single->result_declare_date)); }?>"> 
                            </div>
                            <div class="form-group col-md-6">
                                <label>Marksheet Issue Date</label>
                                <input type="text" class="form-control datepicker" name="marksheet_issue_date" id="marksheet_issue_date" value="<?php if(!empty($single)){ echo date('d-m-Y',strtotime($single->marksheet_issue_date)); }?>"> 
                            </div>
						</div>
					    <div class="row">
                            <div class="form-group col-md-12">
                                <label>Signature</label>
                                <select class="form-control js-example-basic-single" name="signature" id="signature">
                                    <option value="">Select Signature</option>
                                    <?php if(!empty($single)){
                                        $signature = $this->Setting_model->get_selected_signatures($single->student_type);
                                        if(!empty($signature)){ foreach($signature as $signature_result){?>
                                    <option value="<?=$signature_result->id?>" <?php if(!empty($single) && $single->signature == $signature_result->id){ echo 'selected';}?>><?=$signature_result->name?> <?=$signature_result->dispalay_signature?> </option>
                                    <?php }}}?>
                                </select>
                            </div>
						</div>
					    <div class="row">
                            <div class="form-group col-md-6">
                                <label>Available For</label>
                            </div>
                        </div>
                        <?php 
                            $i = 0;
                            if(!empty($single) && !empty($single_details)){ 
                                foreach($single_details as $single_details_result){
                        ?>
                        <div class="row" id="removable_details_<?php echo $single_details_result->id;?>">
                            <input type="hidden" value="<?=$i;?>" name="indices[]">
                            <div class="form-group col-lg-5">
                                <label>Session*</label>
                                <select required class="form-control session" name="session_<?=$i;?>" id="session_<?=$i;?>">
                                    <option value="">Select Session</option>
                                    <?php if(!empty($session)){foreach($session as $result){?><option value="<?=$result->id;?>" <?php if($result->id == $single_details_result->session){ echo 'selected';}?>><?=$result->session_name;?></option><?php }} ?>
                                </select>                         
                            </div>
                            <div class="row form-group col-lg-6">
                                <div class="col-lg-4">
                                    <label>For Year</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_year_<?=$i;?>" class="form-control applicable_for" value="1" <?php if($single_details_result->applicable_for == '1'){ echo 'checked';}?>>
                                </div>
                                <div class="col-lg-4">
                                    <label>For Sem</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_sem_<?=$i;?>" class="form-control applicable_for" value="2" <?php if($single_details_result->applicable_for == '2'){ echo 'checked';}?>>
                                </div>
                                <div class="col-lg-4">
                                    <label>For Both</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_both_<?=$i;?>" class="form-control applicable_for" value="3" <?php if($single_details_result->applicable_for == '3'){ echo 'checked';}?>>
                                </div>
                                <span for="applicable_for_<?=$i;?>" generated="true" class="error invalid-feedback" style="display: none;">Please select option</span>
                            </div>
                            <div class="form-group col-lg-1 ">
                                <span onclick="deleteRow(<?=$single_details_result->id;?>)" class="delete_area"><i class="fa fa-close" style="color:red;margin-top:35px;" aria-hidden="true"></i></span>
                            </div>
                        </div>
                        <?php $i++; }}else{ ?>
                        <div class="row">
                            <input type="hidden" value="<?=$i;?>" name="indices[]">
                            <div class="form-group col-lg-5">
                                <label>Session*</label>
                                <select required class="form-control" name="session_<?=$i;?>" id="session_<?=$i;?>">
                                    <option value="">Select Session</option>
                                    <?php if(!empty($session)){foreach($session as $result){?><option value="<?=$result->id;?>"><?=$result->session_name;?></option><?php }} ?>
                                </select>                         
                            </div>
                            <div class="row form-group col-lg-6">
                                <div class="col-lg-4">
                                    <label>For Year</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_year_<?=$i;?>" class="form-control" value="1">
                                </div>
                                <div class="col-lg-4">
                                    <label>For Sem</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_sem_<?=$i;?>" class="form-control" value="2">
                                </div>
                                <div class="col-lg-4">
                                    <label>For Both</label>
                                    <input type="radio" name="applicable_for_<?=$i;?>" id="for_both_<?=$i;?>" class="form-control" value="3">
                                </div>
                                <span for="applicable_for_<?=$i;?>" generated="true" class="error invalid-feedback" style="display: none;">Please select option</span>
                            </div>
                        </div>
                        <?php $i++; } ?>
                        <div id="add_more_div"></div>
                        <div class="row">
                            <div class="form-group col-lg-12">
                                <a id="add_more_button" class="btn btn-info" style="margin-top:30px;color:white;" onclick="createAddMoreFields()">Add More</a>
                            </div>       
                        </div>
						<button type="submit" id="save_btn" class="btn btn-primary mr-2">Submit</button> 
                  </form>
                </div>
              </div>
            </div>
            <div class="col-md-6 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Exam Setup List</h4>
                  <p class="card-description">
                    All list of Exam Setup
                  </p>
                  <table id="example" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
				<thead>
					 <tr> 
						<th>Sr.No.</th>
						<th>Status</th>
						<th>Student Type</th>
						<th>Exam Month</th> 
						<th>Exam Year</th>
						<th>Result Declare Date</th>
						<th>Marksheet Issue Date</th>
						<th>Signature</th> 
						<th>Details</th> 
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					
				</tbody>
			</table>
                </div>
              </div>
            </div>
          </div>
        </div>
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Exam Setup Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="response">
            
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('footer.php');
$id = 0;
if($this->uri->segment(2) !=""){
	$id = $this->uri->segment(2);
}
?>
<script>
    $("#student_type").change(function(){ 
        $.ajax({
            type: "POST",
            url: "<?=base_url();?>admin/Ajax_controller/get_studentwise_signature_ajax",
            data:{'student_type':$("#student_type").val()},
            success: function(data){
                $("#signature").empty();
                $('#signature').append('<option value="">Select Signature</option>');
                var opts = $.parseJSON(data);
                $.each(opts, function(i, d) {
                $('#signature').append('<option value="' + d.id + '">' + d.name + ' ' + d.dispalay_signature +'</option>');
                });
                $('#signature').trigger('change');
                $('#signature').trigger('chosen:updated');
            },
            error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
            }
        });
    });
    function uniqueExamSetup(){
        $.ajax({
            type: "POST",
            url: "<?=base_url();?>admin/Ajax_controller/check_unique_exam_setup",
            data:{
                'month':$("#month").val(),
                'year':$("#year").val(),
                'student_type':$("#student_type").val(),
                'id':<?php echo $id; ?>,
            },
            success: function(data){
                if(data == "0"){
                    $("#register").show();
                    $("#student_error").html('');
                }else{
                    $("#register").hide(); 
                    $("#student_error").html('Exam setup for this student type, month and year is already set');
                }
            },
                error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
function click_popup(str){
    $.ajax({
        type: "POST",
        url: "<?=base_url();?>admin/Ajax_controller/get_setup_details_popup",
        data: {'id': str},
        success: function(data) { 
            $("#response").html(data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });
}
function deleteRow(id) {
    if(confirm("Are you sure you want to delete this?")){ 
        $.ajax({
            type: "POST",
            url: "<?=base_url();?>admin/Ajax_controller/remove_unused_setup_details",
            data: {
                'id': id,
            },
            success: function(data) {
                    $("#removable_details_"+id).remove();
                    initializeValidationForFields();                    
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
}
var i = <?php echo $i; ?>;
function createAddMoreFields() {
    let appedData = `<div class="row">
                        <input type="hidden" value="${i}" name="indices[]">
                        <div class="form-group col-lg-5">
                            <label>Session*</label>
                            <select required class="form-control session" name="session_${i}" id="session_${i}">
                                <option value="">Select Session</option>
                                <?php if(!empty($session)){foreach($session as $result){?><option value="<?=$result->id;?>"><?=$result->session_name;?></option><?php }} ?>
                            </select>                         
                        </div>
                        <div class="row form-group col-lg-6">
                            <div class="col-lg-4">
                                <label>For Year</label>
                                <input type="radio" name="applicable_for_${i}" id="for_year_${i}" class="form-control applicable_for" value="1">
                            </div>
                            <div class="col-lg-4">
                                <label>For Sem</label>
                                <input type="radio" name="applicable_for_${i}" id="for_sem_${i}" class="form-control applicable_for" value="2">
                            </div>
                            <div class="col-lg-4">
                                <label>For Both</label>
                                <input type="radio" name="applicable_for_${i}" id="for_both_${i}" class="form-control applicable_for" value="3">
                            </div>
                            <span for="applicable_for_${i}" generated="true" class="error" style="display:none;">Please select option</span>
                        </div>
                        <div class="form-group col-lg-1 ">
                            <span onclick="removeRow(this)" class="delete_area"><i class="fa fa-close" style="color:red;margin-top:35px;" aria-hidden="true"></i></span>
                        </div>
                    </div>`;
    $('#add_more_div').append(appedData);
    i++;
    initializeValidationForFields();
}
function removeRow(arg) {
    $(arg).parent().parent().remove();
    initializeValidationForFields();
}   
function initializeValidationForFields() {
    $(".session").each(function () {
        $(this).rules("add", {
            required: true,
            messages: {
                required: "Please select session",
            },
        });
    });

    $(".applicable_for").each(function () {
        $(this).rules("add", {
            required: true,
            messages: {
                required: "Please select option",
            },
        });
    });
}
function addErrorMessage(element) {
    element.on('focusout', function () {
        element.valid();
    });

    element.on('keyup', function () {
        element.valid();
    });
}
$( function() { 
    $( ".datepicker" ).datepicker({ 
		dateFormat:"dd-mm-yy", 
		changeMonth:true, 
		changeYear:true, 
		// maxDate:0, 
	}); 
});

$("#id_form").validate({  
  rules: {
    student_type:{
      required:true,
    },
    month:{
      required:true,
    },
    year:{
      required: true, 
    },
    result_declare_date:{
      required: true, 
    },
    marksheet_issue_date:{
      required: true, 
    },
	signature:{
      required: true, 
    },
    session_0:{
        required: true, 
    },
	applicable_for_0:{
        required: true, 
    },
  },
  messages: {
    student_type:{
        required:"Please select student type", 
    },
    month:{
      required:"Please select exam month", 
    },
    year:{
      required:"Please select exam year", 
    },
    result_declare_date:{
      required:"Please enter result declare date", 
    },
    marksheet_issue_date:{
      required:"Please enter result declare date", 
    },
	signature:{
        required:"Please select signature", 
    },
    session_0:{
      required:"Please select session", 
    },
	applicable_for_0:{
        required:"Please select option", 
    },
  }, 
  submitHandler: function (form) {
        var isConfirmed = confirm("Are you sure you want to submit the form?");
        
        if (isConfirmed) {
            form.submit();
        }
    },

    errorElement: 'span', 
    errorPlacement: function (error, element) {
        if (
            element.hasClass('session') ||
            element.hasClass('applicable_for')
        ) {
            error.insertAfter(element);
        } else {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        }
    },
    invalidHandler: function(event, validator) {
        $(".error-message").show();
    },
});

$(document).ready(function() { 
	var oldExportAction = function (self, e, dt, button, config) {
        if (button[0].className.indexOf('buttons-excel') >= 0) {
            if ($.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)) {
                $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config);
            }
            else {
                $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
            }
        } else if (button[0].className.indexOf('buttons-print') >= 0) {
            $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
        }
    };
    
    var newExportAction = function (e, dt, button, config) {
        var self = this;
        var oldStart = dt.settings()[0]._iDisplayStart;
    
        dt.one('preXhr', function (e, s, data) {
            // Just this once, load all data from the server...
            data.start = 0;
            data.length = 2147483647;
    
            dt.one('preDraw', function (e, settings) {
                // Call the original action function 
                oldExportAction(self, e, dt, button, config);
    
                dt.one('preXhr', function (e, s, data) {
                    // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                    // Set the property to what it was before exporting.
                    settings._iDisplayStart = oldStart;
                    data.start = oldStart;
                });
    
                // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                setTimeout(dt.ajax.reload, 0);
    
                // Prevent rendering of the full data to the DOM
                return false;
            });
        });
    
        // Requery the server with the new one-time export settings
        dt.ajax.reload();
    };
	
	var table = $('#example').DataTable({
	    "lengthChange": false,
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"cache": false,
		"order": [[0, "desc" ]],
		buttons:[
			
			{
				extend: "excelHtml5",
				title:"Signatures",
				messageBottom: 'The information in this table is copyright to Bir Tikendrajeet University.',
				exportOptions: {
                    columns: [0,1,2,3,4,5,6,7],
                    modifier: {
						search: 'applied',
						order: 'applied'
					},
                },
                action: newExportAction,
			}
		],
		dom:"Bfrtip",
		
		"ajax":{
			"url" : "<?=base_url();?>admin/Ajax_controller/get_all_exam_setup_ajax",
			"type": "POST",
		},	
		"complete": function() {
			$('[data-toggle="tooltip"]').tooltip();			
		},
    });
    //table.buttons().container().appendTo( '#example_wrapper:eq(0)' );
	
});	
</script>
 