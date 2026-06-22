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
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                <?php if (!empty($single)) { ?>
                    Update Brand
                <?php } else { ?>
                    Add Brand

                <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_brand" id="add_brand" enctype="multipart/form-data">

                        <div class="row flex_wrap">

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Brand Name<b class="require">*</b></label>
                                <input name="brand_name" id="brand_name" type="text" class="form-control" value="<?php if (!empty(($single))) {
                                                                                                                        echo $single->brand_name;
                                                                                                                    } ?>" placeholder="Enter brand name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                                                                                                        echo $single->id;
                                                                                                    } ?>">
                                <span id="brand_name_error" class=""></span>

                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Type<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('brand_type')">Add New</button></label>
                                <select name="brand_type" id="brand_type" class="form-control js-example-basic-multiple" value="" placeholder="Enter Type" required>
                                    <option value="">Select Type</option>
                                    <?php if (!empty($brand_type)) {
                                        foreach ($brand_type as $brand_type_result) { ?>
                                            <option value="<?= $brand_type_result->id ?>" <?php if (!empty($single) && $single->brand_type_id == $brand_type_result->id) { ?>selected<?php } ?>><?= $brand_type_result->brand_type ?></option>
                                    <?php }
                                    } ?>

                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Party Name<b class="require">*</b></label>
                                <select name="party_name" id="party_name" class="form-control js-example-basic-multiple" value="" placeholder="Enter Party Name" required>

                                    <option value="">Choose</option>
                                    <?php if (!empty($party_name)) {
                                        foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>" <?php if (!empty($single) && $single->party_name_id == $party_result->id) { ?>selected<?php } ?>><?= $party_result->party_name ?></option>
                                    <?php }
                                    } ?>

                                </select>

                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Ink<b class="require">*</b></label>
                                <select multiple="multiple" name="ink[]" id="ink" class="form-control js-example-basic-multiple">
                                    <option value="">Please select ink</option>
                                    <?php if (!empty($ink)) {
                                        if (!empty($single)) {
                                            $ink_selected = explode(",", $single->ink_ids);
                                        } else {
                                            $ink_selected = [];
                                        }
                                        foreach ($ink as $ink_result) { ?>
                                            <option value="<?= $ink_result->id ?>"
                                                <?php if (in_array($ink_result->id, $ink_selected)) { ?>selected="selected" <?php } ?>>
                                                <?= htmlspecialchars($ink_result->rm_name) ?>
                                            </option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Department<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('department')">Add New</button></label>
                                <select id="department" name="department" class="form-control js-example-basic-multiple" value="" placeholder="Enter Department" required>
                                    <option value="">Select Department </option>
                                    <?php if (!empty($department)) {
                                        foreach ($department as $department_result) { ?>
                                            <option value="<?= $department_result->id ?>" <?php if (!empty($single) && $single->department_id == $department_result->id) { ?>selected<?php } ?>><?= $department_result->department ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>


                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
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
    $(document).ready(function() {
        // $('.js-example-basic-multiple').select2({
        //     placeholder: "Please Select Option"
        // });
        $('#party_name').select2({
            placeholder: "Please select party name"
        });
        $('#brand_type').select2({
            placeholder: "Please select brand type"
        });
        $('#department').select2({
            placeholder: "Please select department"
        });
        $('#ink').select2({
            placeholder: "Please select ink"
        });


    });
</script>

<script>
    function addNewOption(master_type) {
        $('#master_type').val(master_type);
        if (master_type == 'brand_type') {
            $('.modal-title').text('Add New Brand Type');
            $('.add_opt').html('Type<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new brand type');
        } else if (master_type == 'department') {
            $('.modal-title').text('Add New Department');
            $('.add_opt').html('Department<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new department');

        } else {
            $('.modal-title').text('Add New Option');
            $('.add_opt').html('Option<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new option');
        }
        $('#exampleModal').modal('show');

    }
    $('#add_option_btn').click(function() {
        var new_option = $('#new_option').val();
        var master_type = $('#master_type').val();
        if (/^\s/.test(new_option)) {
            $('.new_option_error').text('No spaces allowed at the start!');
            return false;
        }
        if (new_option == '') {
            if (master_type == 'brand_type') {
                $('.new_option_error').text('Please enter brand type.');
                return false;
            } else if (master_type == 'department') {
                $('.new_option_error').text('Please enter department.');
                return false;
            } else {
                $('.new_option_error').text('Please enter option.');
            }
        } else if (master_type == 'brand_type') {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>admin/Ajax_controller/set_new_brand_option',
                data: {
                    new_option: new_option,
                    master_type: master_type
                },
                success: function(data) {
                    if (data == '0') {
                        if (master_type == 'brand_type') {
                            getAllbrandtype();
                            $('#exampleModal').modal('hide');
                        }
                    } else {
                        if (master_type == 'brand_type') {
                            $('.new_option_error').text('This brand type already exist.');
                        }
                    }
                }
            });

        } else if (master_type == 'department') {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>admin/Ajax_controller/set_new_brand_option',
                data: {
                    new_option: new_option,
                    master_type: master_type
                },
                success: function(data) {
                    if (data == '0') {
                        if (master_type == 'department') {
                            getAlldepartment();
                            $('#exampleModal').modal('hide');
                        }
                    } else {
                        if (master_type == 'department') {
                            $('.new_option_error').text('This department already exist.');
                        }
                    }
                }
            });
        }
    })

    function getAllbrandtype() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_brand_type',
            success: function(data) {
                if (data != '') {
                    $('#brand_type').empty();
                    $('#brand_type').append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function(i, d) {
                        $('#brand_type').append('<option value="' + d.id + '">' + d.brand_type + '</option>');
                    });
                    $('#brand_type').trigger('chosen:updated');
                }
            }
        });
    }

    function getAlldepartment() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_department',
            success: function(data) {
                if (data != '') {
                    $('#department').empty();
                    $('#department').append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function(i, d) {
                        $('#department').append('<option value="' + d.id + '">' + d.department + '</option>');
                    });
                    $('#department').trigger('chosen:updated')
                }
            }
        });
    }
    $('#new_option').keyup(function() {
        $('.new_option_error').text('');
    });
    $('#exampleModal').on('hidden.bs.modal', function() {
        $('#new_option').val('');
        $('.new_option_error').text('');
    });
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_brand').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>


<script>
    $.validator.addMethod("noSpaceAtStart", function(value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    jQuery.validator.addMethod("noNumbers", function(value, element) {
        return this.optional(element) || !/\d/.test(value);
    });
    $(document).ready(function() {
        $('#add_brand').validate({
            ignore: [],
            rules: {

                brand_name: {
                    required: true,
                    noSpaceAtStart: true,

                },
                brand_type: {
                    required: true,
                },
                party_name: {
                    required: true,
                },

                department: {
                    required: true,
                },
                'ink[]': {
                    required: true,
                }
            },
            messages: {
                brand_name: {
                    required: "Please enter brand name!",
                    noSpaceAtStart: "First letter can not be space!",
                },
                brand_type: {
                    required: "Please select type!",
                },
                party_name: {
                    required: "Please select party name!",
                },
                department: {
                    required: "Please select department!",
                },
                'ink[]': {
                    required: "Please select ink!",
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

        $("#brand_type").change(function() {
            $("#brand_type").valid();
        });
        $("#department").change(function() {
            $("#department").valid();
        });
        $("#party_name").change(function() {
            $("#party_name").valid();
        });
        $("#ink").change(function() {
            $("#ink").valid();
        });

    })
</script>


<!-- P1: Brand uniqueness AJAX check removed — duplicate brand names now allowed per party/plant -->