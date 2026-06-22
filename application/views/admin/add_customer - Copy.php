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
        <?php if (!empty($single)) { ?>
                Update Party
            <?php } else { ?>
                Add Party
            <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_customer" id="add_customer" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Party Name<b class="require">*</b></label>
                                <input name="party_name" id="party_name" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->party_name; } ?>" placeholder="Enter party name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                                <span id="party_name_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 ">
                                <label>Party Type<b class="require">*</b></label>
                                <select multiple="multiple" name="party_type[]" id="party_type" class="form-control js-example-basic-multiple"  required>
                                <?php if (!empty($single)) { 
                                    $party_type = explode(',', $single->party_type);
                                    $selected_types = array_flip($party_type);
                                    ?>
                                    <option value="1" <?php echo isset($selected_types['1']) ? 'selected' : ''; ?>>Customer</option>
                                    <option value="2" <?php echo isset($selected_types['2']) ? 'selected' : ''; ?>>Supplier</option>
                                <?php } else { ?>
                                    <option value="1">Customer</option>
                                    <option value="2">Supplier</option>
                                <?php } ?>
                            </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Mobile NO<b class="require">*</b></label>
                                <input name="mobile" id="mobile" type="tel" class="form-control" value="<?php if (!empty($single)) { echo $single->mobile; } ?>" placeholder="Enter mobile no" required>
                                <span id="mobile_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>GSTIN/PAN<b class="require">*</b></label>
                                <input name="gst_pan" id="gst_pan" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->gst_pan; } ?>" placeholder="Enter GSTIN/PAN" required>
                                <span id="gst_pan_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Address<b class="require">*</b></label>
                                <input name="address" id="address" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->address; } ?>" placeholder="Enter address" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 class='select2-container'">
                                <label>City<b class="require">*</b></label>
                                <select name="city" id="city" type="text" class="form-control js-example-basic-multiple" value="">
                                    <option value="">Choose</option>
                                    <?php if (!empty($city)){
                                     foreach ($city as $city_result) { ?>
                                            <option value="<?= $city_result->id ?>"<?php if(!empty($single)&& $single->city_id==$city_result->id){?>selected<?php }?>><?= $city_result->city ?></option>
                                        <?php }} ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Contact Person Name<b class="require">*</b></label>
                                <input name="contact_name" id="contact_name" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->contact_name; } ?>" placeholder="Enter contact person name" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Designation<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('designation')">Add New</button></label>
                                <select name="designation" id="designation" class="form-control js-example-basic-multiple" value="" placeholder="Enter designation">
                                    <option value="">Select designation</option>
                                    <?php if (!empty($designation)){
                                     foreach ($designation as $designation_result) { ?>
                                            <option value="<?= $designation_result->id ?>"<?php if(!empty($single)&& $single->designation_id==$designation_result->id){?>selected<?php }?>><?= $designation_result->designation ?></option>
                                        <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Secondary Contact Person Name</label>
                                <input name="sec_contact" id="sec_contact" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->sec_contact; } ?>" placeholder="Enter secondary contact person name" >
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Designation 2</label>
                                <select name="designation_two" id="designation_two" class="form-control js-example-basic-multiple" value="" placeholder="Enter designation" >
                                <option value="">Select designation</option>
                                <?php if (!empty($designation)){
                                     foreach ($designation as $designation_result) { ?>
                                            <option value="<?= $designation_result->id ?>"<?php if(!empty($single)&& $single->designation_two_id==$designation_result->id){?>selected<?php }?>><?= $designation_result->designation ?></option>
                                        <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12 division-class ">
                                <label>Division</label>
                                <select multiple="multiple" name="division[]" id="division" class="form-control js-example-basic-multiple" value="">
                                    <?php if (!empty($single)) { 
                                        $division_ids = explode(',', $single->division_ids);
                                        $selected_ids = array_flip($division_ids);
                                        ?>
                                        <option value="1" <?php echo isset($selected_ids['1']) ? 'selected' : ''; ?>>Container</option>
                                        <option value="2" <?php echo isset($selected_ids['2']) ? 'selected' : ''; ?>>Household</option>
                                    <?php } else { ?>
                                        <option value="1">Container</option>
                                        <option value="2">Household</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Attending Salesperson<b class="require">*</b></label>
                                <select name="attending" id="attending" class="form-control js-example-basic-multiple" value="" placeholder="Select Attending Salesperson" required>
                                    <option value="">Select Attending Salesperson</option>
                                    <?php if (!empty($employee)){
                                     foreach ($employee as $employee_result) { ?>
                                            <option value="<?= $employee_result->id ?>"<?php if(!empty($single)&& $single->attending_salesperson_id==$employee_result->id){?>selected<?php }?>><?= $employee_result->first_name ?></option>
                                        <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Nature of Business<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('nature_of_business')">Add New</button></label>
                                <select id="nature_of_business" name="nature_of_business" type="text" class="form-control js-example-basic-multiple" value="" required>
                                    <option value="">Select Nature of Business </option>
                                    <?php if (!empty($nature_of_business)){
                                     foreach ($nature_of_business as $nature_of_business_result) { ?>
                                            <option value="<?= $nature_of_business_result->id ?>"<?php if(!empty($single)&& $single->nature_of_business_id==$nature_of_business_result->id){?>selected<?php }?>><?= $nature_of_business_result->nature_of_business ?></option>
                                        <?php }} ?>
                                     </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Type of Business<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('type_of_business')">Add New</button></label>
                                <select id="type_of_business" name="type_of_business" type="text" class="form-control js-example-basic-multiple" value="" required>
                                    <option value="">Select Type of Business </option>
                                    <?php if (!empty($type_of_business)){
                                     foreach ($type_of_business as $type_of_business_result) { ?>
                                            <option value="<?= $type_of_business_result->id ?>"<?php if(!empty($single)&& $single->type_of_business_id==$type_of_business_result->id){?>selected<?php }?>><?= $type_of_business_result->type_of_business ?></option>
                                        <?php }} ?>
                                                 
                                     </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container row ">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label class="add_opt">Name<b class="require">*</b></label>
                                <input name="new_option" id="new_option" type="text" class="form-control" value="" placeholder="Enter new option" required>
                                <input name="master_type" id="master_type" type="hidden" class="form-control" value="">
                                <div class="error new_option_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <button id="add_option_btn" type="button" class="btn btn-primary">Submit</button>
                        </div>
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
        $('.js-example-basic-multiple').select2({
            placeholder: "Select division"
        });
        $('#city').select2({
            placeholder: "Please select city"
        });
        $('#party_type').select2({
            placeholder: "Please select party type"
        });
        $('#designation').select2({
            placeholder: "Please select designation"
        });
        $('#designation_two').select2({
            placeholder: "Please select second designation"
        });
       
        $('#attending').select2({
            placeholder: "Please select attending salesperson"
        });
        $('#nature_of_business').select2({
            placeholder: "Please select nature of business"
        });
        $('#type_of_business').select2({
            placeholder: "Please select type of business"
        });
    });
    $("gst_pan").on("keyup", function () {
        $(this).val($(this).val().toUpperCase());
    });
</script>
<script>
    var party_type = $('#party_type').val();
    if (party_type == '2') {
        $('.division-class').hide();
        $('#division').val('');
    }
    $('#party_type').on('change', function() {
        var party_type = $(this).val();
        $('#division').val('');
        if (party_type.includes('1') && party_type.includes('2')) {
            $('.division-class').show();
        }else if (party_type.includes('1')){
            $('.division-class').show();
        }
        else {
            $('.division-class').hide();
        }
    });
    function addNewOption(master_type){
            $('#master_type').val(master_type);
            if(master_type == 'designation'){
                $('.modal-title').text('Add New Designation');  
                $('.add_opt').html('Designation<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new designation');   
            }else if(master_type == 'nature_of_business'){
                $('.modal-title').text('Add New Nature of Business');  
                $('.add_opt').html('Nature of Business<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new nature of business');   
            }else if(master_type == 'type_of_business'){
                $('.modal-title').text('Add New Type of Business');  
                $('.add_opt').html('Type of Business<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new type of business');
            } else {
                $('.modal-title').text('Add New Option');  
                $('.add_opt').html('Option<b class="require">*</b>'); 
                $('#new_option').attr('placeholder', 'Enter new option');
            }
            $('#exampleModal').modal('show');   
        }
        $('#add_option_btn').click(function(){
            var new_option = $('#new_option').val();
            var master_type = $('#master_type').val();
            if (/^\s/.test(new_option)) {
                $('.new_option_error').text('No spaces allowed at the start!');
                return false;
            }
            if(new_option == ''){
                if(master_type == 'designation'){
                    $('.new_option_error').text('Please enter designation!');
                    return false;
                }
                else if(master_type == 'attending_salesperson'){
                    $('.new_option_error').text('Please enter salsesperson!');
                    return false;
                }
                else if(master_type == 'nature_of_business'){
                    $('.new_option_error').text('Please enter nature of business!');
                    return false;
                
                }else if(master_type == 'type_of_business'){
                    $('.new_option_error').text('Please enter type of business!');
                    return false;
                }
                else{
                    $('.new_option_error').text('Please enter new option!');
                }
            }else {
                $.ajax({
                    type: 'POST',
                    url: '<?=base_url()?>admin/Ajax_controller/set_new_party_option',
                    data: {new_option: new_option, master_type: master_type},
                    success: function(data){
                        if(data == '0'){
                            if(master_type == 'designation'){
                                getAlldesignation();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'attending_salesperson'){
                                getAllsalesperson();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'nature_of_business'){
                                getAllnature_of_business();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'type_of_business'){
                                getAlltype_of_business();
                                $('#exampleModal').modal('hide');
                            }else{
                                $('#exampleModal').modal('hide');
                            }
                        }else{
                            if(master_type == 'designation'){
                                $('.new_option_error').text('This designation already exist!');
                            }else if(master_type == 'nature_of_business'){
                                $('.new_option_error').text('This nature of business already exist!');
                            }else if(master_type == 'type_of_business'){
                                $('.new_option_error').text('This type of business already exist!');
                            
                            }else{
                                $('.new_option_error').text('This option already exist!');
                            }
                        }
                    }
                }); 
            }       
        })
        function getAlldesignation(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_designation',
                success: function(data){
                    if(data != ''){
                        $('#designation').empty();
                        $('#designation').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#designation').append('<option value="' + d.id + '">' + d.designation + '</option>');
                            $('#designation_two').append('<option value="' + d.id + '">' + d.designation + '</option>');
                        });
                        $('#designation').trigger('chosen:updated');
                    }
                }
            });
        }
        function getAllsalesperson(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_salesperson',
                success: function(data){
                    if(data != ''){
                        $('#attending_salesperson').empty();
                        $('#attending_salesperson').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#attending_salesperson').append('<option value="' + d.id + '">' + d.attending_salesperson + '</option>');
                        });
                        $('#attending_salesperson').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllnature_of_business(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_nature_of_business',
                success: function(data){
                    if(data != ''){
                        $('#nature_of_business').empty();
                        $('#nature_of_business').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#nature_of_business').append('<option value="' + d.id + '">' + d.nature_of_business + '</option>');
                        });
                        $('#nature_of_business').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAlltype_of_business(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_type_of_business',
                success: function(data){
                    if(data != ''){
                        $('#type_of_business').empty();
                        $('#type_of_business').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#type_of_business').append('<option value="' + d.id + '">' + d.type_of_business + '</option>');
                        });
                        $('#type_of_business').trigger('chosen:updated')
                    }
                }
            });
        }
        $('#new_option').keyup(function(){
            $('.new_option_error').text('');
        });
        $('#exampleModal').on('hidden.bs.modal', function() {
            $('#new_option').val('');  
            $('.new_option_error').text('');  
        });  
</script>
<script>
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_customer').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        }, "First letter can not be space");
        jQuery.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });
        $.validator.addMethod("validMobile", function(value, element) {
            return this.optional(element) || (/^[0-9]{10}$/.test(value) && !/^(.)\1{9}$/.test(value));
        }, "Please enter a valid mobile number!");
        // $.validator.addMethod("validGSTIN", function(value, element) {
        //     return this.optional(element) || /^[A-Z]{2}[0-9A-Z]{10}[A-Z0-9]{1}[A-Z]{1}[0-9]{1}$/.test(value);
        // }, "Please enter a valid GSTIN!");
        // $.validator.addMethod("validMobile", function(value, element) {
        //     return this.optional(element) || /^[0-9]{10}$/.test(value);
        // }, "Please enter a valid mobile number!");
        $('#add_customer').validate({
            ignore: [],
            rules: {     
                party_name: {
                    required: true,
                    noSpaceAtStart: true,
                },
                'party_type[]': {
                    required: true,
                },
                mobile: {
                    required: true,
                    number:true,
                    noSpaceAtStart :true,
                    validMobile: true
                },         
                gst_pan: {
                    required: true,
                    noSpaceAtStart :true,
                    //validGSTIN: true,
                },
                transport_id: {
                    required: true,
                    noSpaceAtStart : true,
                },
                address: {
                    required: true,
                },
                city: {
                    required: true,
                },
                contact_name: {
                    required: true,
                    noSpaceAtStart:true,
                },
                designation: {
                    required: true,
                },
               
                attending: {
                    required: true,
                },
                
                nature_of_business:{
                    required: true,
                },
                type_of_business:{
                    required: true,
                },
            },
            messages: {
                party_name : {
                    required: "Please enter party name!",
                    noSpaceAtStart: "Name should not start with space!",
                },
                'party_type[]': {
                    required: "Please select party type!"
                },
                mobile: {
                    required: "Please enter mobile number!",
                    noSpaceAtStart: "Number should not start with space!",
                    validMobile: "Please enter valid mobile number!"
                },
                gst_pan:{
                    required: "Please enter GST/PAN!",
                    noSpaceAtStart: "GST PAN should not start with space!"
                },
                transport_id:{
                    required: "Please enter Transport ID!",
                    noSpaceAtStart: "Transport ID should not start with space!"            
                },
                address:{
                    required: "Please enter address!"
                },
                city:{
                    required: "Please select city!"
                },
                contact_name:{
                    required: "Please enter contact person name!",
                    noSpaceAtStart: "Name should not start with space!"
                },
                designation:{
                    required: "Please select designation!"
                },
               
                attending:{
                    required: "Please select attending salesperson!"
                },
                nature_of_business:{
                    required: "Please select nature of business!"
                },
                type_of_business:{
                    required: "Please select type of business!"
                },
            
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
        });
        $("#city").change(function() {
            $("#city").valid();
        });
        $("#party_type").change(function() {
            $("#party_type").valid();
        });
        $("#mobile").change(function() {
            $("#mobile").valid();
        });
        $("#gst_pan").change(function() {
            $("#gst_pan").valid();
        });
        $('#designation').change(function() {
            $('#designation').valid();
        });
        $('#designation_two').change(function() {
            $('#designation_two').valid();
        });
        $('#attending').change(function() {
            $('#attending').valid();
        });
        $('#nature_of_business').change(function() {
            $('#nature_of_business').valid();
        });
        $('#type_of_business').change(function() {
            $('#type_of_business').valid();
        });
        $("#party_type").change(function() {
            $("#party_type").valid();
        });
    })
</script>
<script>
    $('#party_name').on('keyup', function(){
        var party_name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_party_name', 
            method: 'post',
            data: {
                'party_name': party_name,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#party_name_error').text("This party is already added !");
                  $('#party_name_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#party_name_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    $('#mobile').on('keyup', function(){
        var mobile = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_mobile', 
            method: 'post',
            data: {
                'mobile': mobile,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#mobile_error').text("This mobile is already added !");
                  $('#mobile_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#mobile_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    $('#gst_pan').on('keyup', function(){
        var gst_pan = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_gst_pan', 
            method: 'post',
            data: {
                'gst_pan': gst_pan,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#gst_pan_error').text("This GST/PAN number is already added !");
                  $('#gst_pan_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#gst_pan_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
</script>