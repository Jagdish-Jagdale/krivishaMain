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
                Update Master Batch
            <?php } else { ?>
                Add Master Batch
            <?php } ?>
        </h3>
            
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_mb_form" id="add_mb_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>NAME<b class="require">*</b></label>
                                <input name="name" type="text" class="form-control" id="name" value="<?php if (!empty($single)) { echo $single->name; } ?>" placeholder="Enter Name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                                <span id="name_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="alias">ALIAS<b class="require">*</b></label>
                                <input type="text" name="alias" class="form-control" id="alias" value="<?php if (!empty($single)) { echo $single->alias; } ?>" placeholder="Please Enter ALIAS" required>
                                <span id="alias_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="base">BASE<b class="require">*</b></label>
                                <input type="text" name="base" class="form-control" id="base" value="<?php if (!empty($single)) { echo $single->base; } ?>" placeholder="Please Enter BASE" required>
                                <span id="base_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="make">MAKE<b class="require">*</b></label>
                                    <button type="button" class="btn add_option" onclick="addNewOption('make')">Add New</button></label>
                                    <select name="make" id="make" class="form-control js-example-basic-multiple">
                                    <option value="">Please Select Make</option>
                                    <?php if (!empty($make)){
                                        foreach ($make as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>"<?php if(!empty($single)&& $single->make_id==$make_result->id){?>selected<?php }?>><?= $make_result->make ?></option>
                                        <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
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
    function addNewOption(master_type){
        $('#master_type').val(master_type);
        if(master_type == 'make'){
            $('.modal-title').text('Add New Make');  
            $('.add_opt').html('Make<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new make');   
        }else {
            $('.modal-title').text('Add New Option');  
            $('.add_opt').html('Option<b class="require">*</b>'); 
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
            if(master_type == 'make'){
                $('.new_option_error').text('Please enter make!');
            }
            else{
                $('.new_option_error').text('Please enter option!');
            }
            return false;
        }
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/set_new_type_option',
            data: { new_option: new_option, master_type: master_type },
            success: function (data) {
                if (data == '0') {
                    if (master_type == 'make') {
                        getAllMake();
                        $('#exampleModal').modal('hide');
                    }
                } else {
                    if (master_type == 'make') {
                        $('.new_option_error').text('This make already exist!');
                    }
                }
            }
        });
    })
    function getAllMake() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_rm_make',
            success: function (data) {
                if (data != '') {
                    $('#make').empty();
                    $('#make').append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function (i, d) {
                        $('#make').append('<option value="' + d.id + '">' + d.make + '</option>');
                    });
                    $('#make').trigger('chosen:updated');
                }
            }
        });
    }
    $('#new_option').keyup(function () {
        $('.new_option_error').text('');
    });
    $('#exampleModal').on('hidden.bs.modal', function () {
        $('#new_option').val('');
        $('.new_option_error').text('');
    });

    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_mb').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please select type"
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
    jQuery.validator.addMethod("noAlphabets", function (value, element) {
        return this.optional(element) || /^[^A-Za-z]+$/.test(value);
    }, "Alphabets are not allowed!");
    $(document).ready(function() {
        $('#add_mb_form').validate({
            ignore: [],
            rules: {
                name: {
                    required: true,
                    noSpaceAtStart: true,
                   // noNumbers: true,
                },
                alias:{
                    required: true,
                    noSpaceAtStart:true,
                    //noAlphabets:true,
                },
                base: {
                    required: true,
                    noSpaceAtStart: true,
                },
                make: {
                    required: true
                },
            },
            messages: {
                name: {
                    required: "Please enter name!",
                    noSpaceAtStart: "First letter can not be space!",
                    noNumbers: "Numbers are not allowed!"
                },
                alias:{
                    required: "Please enter ALIAS!",
                    noSpaceAtStart: "First letter can not be space!",
                    //noAlphabets:"Alphabets are not allowed!",
                },
                base: {
                    required: "Please enter BASE!",
                    noSpaceAtStart: "First letter can not be space!",
                },
                make: {
                    required: "Please select MAKE!"
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
            }
        });
        $("#make").change(function() {
            $("#make").valid();
        });
    });
</script>
<script>
    $('#name').on('keyup', function(){
        var name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_mb_name', 
            method: 'post',
            data: {
                'name': name,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#name_error').text("This name is already added!");
                  $('#name_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#name_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    
    $('#alias').on('keyup', function(){
        var alias = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_mb_alias', 
            method: 'post',
            data: {
                'alias': alias,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#alias_error').text("This alias is already added!");
                  $('#alias_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#alias_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    $('#base').on('keyup', function(){
        var base = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_mb_base', 
            method: 'post',
            data: {
                'base': base,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#base_error').text("This base is already added!");
                  $('#base_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#base_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);}
        });
    });

</script>

