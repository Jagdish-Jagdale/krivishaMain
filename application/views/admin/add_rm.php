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
                    Update Raw Material
                <?php } else { ?>
                    Add Raw Material
                <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_rm_form" id="add_rm_form" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>NAME<b class="require">*</b></label>
                                <input name="rm_name" type="text" class="form-control" id="rm_name"
                                    value="<?php if (!empty($single)) {
                                        echo $single->rm_name;
                                    } ?>"
                                    placeholder="Enter Name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id"
                                    value="<?php if (!empty($single)) {
                                        echo $single->id;
                                    } ?>">
                                <span id="rm_name_error" class=""></span>
                            </div>
                             <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Re-Order Level<b class="require">*</b></label>
                                <input id="reorder_level" name="reorder_level" type="number" min="0" class="form-control" value="<?php if (!empty($single)) { echo $single->reorder_level; } ?>" placeholder="Enter RM reorder level" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12" <?php if (!empty($single)): ?> style="display: none;" <?php endif; ?>>
                                <label for="type_of_rm">Select RM Type<b class="require">*</b></label>
                                <select name="type_of_rm" id="type_of_rm"
                                    class="form-control js-example-basic-multiple">
                                    <option value="">Please Select</option>
                                    <option value="1" <?php if (!empty($single) && $single->type_of_rm == '1') { ?>
                                            selected <?php } ?>>New</option>
                                    <option value="2" <?php if (!empty($single) && $single->type_of_rm == '2') { ?>
                                            selected <?php } ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="MFI">MFI<b class="require">*</b></label>
                                <input type="text" name="mfi" class="form-control" id="mfi"
                                    value="<?php if (!empty($single)) {
                                        echo $single->mfi;
                                    } ?>"
                                    placeholder="Enter MFI">
                                <span id="mfi_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="alias">ALIAS<b class="require">*</b></label>
                                <input type="text" name="alias" class="form-control" id="alias"
                                    value="<?php if (!empty($single)) {
                                        echo $single->alias;
                                    } ?>"
                                    placeholder="Enter ALIAS">
                                <span id="alias_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="code">CODE</label>
                                <input type="text" name="code" class="form-control" id="code"
                                    value="<?php if (!empty($single)) {
                                        echo $single->code;
                                    } ?>"
                                    placeholder="Enter CODE">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="make">MAKE<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('make')">Add
                                    New</button></label>
                                <select name="make" id="make" class="form-control js-example-basic-multiple">
                                    <option value="">Please Select Make</option>
                                    <?php if (!empty($make)) {
                                        foreach ($make as $make_result) { ?>
                                            <option value="<?= $make_result->id ?>" <?php if (!empty($single) && $single->make_id == $make_result->id) { ?>selected<?php } ?>>
                                                <?= $make_result->make ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="type">Type<b class="require">*</b></label>
                                <button type="button" class="btn add_option" onclick="addNewOption('type')">Add
                                    New</button></label>
                                <select name="type" id="type" class="form-control js-example-basic-multiple">
                                    <option value="">Please Select Type</option>
                                    <?php if (!empty($type)) {
                                        foreach ($type as $type_result) { ?>
                                            <option value="<?= $type_result->id ?>" <?php if (!empty($single) && $single->type_id == $type_result->id) { ?>selected<?php } ?>>
                                                <?= $type_result->type ?></option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>UOM<b class="require">*</b></label>
                                <select name="uom_id" id="uom_id" class="form-control select2-select"
                                    required>
                                    <option value="">Select UOM</option>
                                    <?php if (!empty($uom)) {
                                        foreach ($uom as $uom_result) { ?>
                                            <option value="<?= $uom_result->id ?>" <?php if (!empty($single) && $single->uom_id == $uom_result->id) { ?>selected<?php } ?>>
                                                <?= $uom_result->uom_name ?>
                                            </option>
                                        <?php }
                                    } ?>

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
                            <input name="new_option" id="new_option" type="text" class="form-control" value=""
                                placeholder="Enter new option" required>
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
$type_of_rm = '';
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);
    $type_of_rm = $this->uri->segment(3);
}
?>

<script>
    function addNewOption(master_type) {
        $('#master_type').val(master_type);
        if (master_type == 'type') {
            $('.modal-title').text('Add New Type');
            $('.add_opt').html('Type<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new type');
        } else if (master_type == 'make') {
            $('.modal-title').text('Add New Make');
            $('.add_opt').html('Make<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new make');
        } else if (master_type == 'ink') {
            $('.modal-title').text('Add New Ink');
            $('.add_opt').html('Ink<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new ink');
        } else {
            $('.modal-title').text('Add New Option');
            $('.add_opt').html('Option<b class="require">*</b>');
        }
        $('#exampleModal').modal('show');
    }
    $('#add_option_btn').click(function () {
        var new_option = $('#new_option').val();
        var master_type = $('#master_type').val();
        if (/^\s/.test(new_option)) {
            $('.new_option_error').text('No spaces allowed at the start!');
            return false;
        }
        if (new_option == '') {
            if (master_type == 'type') {
                $('.new_option_error').text('Please enter type!');
                return false;
            } else if (master_type == 'make') {
                $('.new_option_error').text('Please enter make!');
            } else if (master_type == 'ink') {
                $('.new_option_error').text('Please enter ink!');
            }
            else {
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
                    if (master_type == 'type') {
                        getAlltype();
                        $('#exampleModal').modal('hide');
                    } else if (master_type == 'make') {
                        getAllMake();
                        $('#exampleModal').modal('hide');
                    } else if (master_type == 'ink') {
                        getAllInk();
                        $('#exampleModal').modal('hide');
                    }
                } else {
                    if (master_type == 'type') {
                        $('.new_option_error').text('This type already exist!');
                    } else if (master_type == 'make') {
                        $('.new_option_error').text('This make already exist!');
                    } else if (master_type == 'ink') {
                        $('.new_option_error').text('This ink already exist!');
                    }
                }
            }
        });
    })
    function getAlltype() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_rm_type',
            success: function (data) {
                if (data != '') {
                    $('#type').empty();
                    $('#type').append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function (i, d) {
                        $('#type').append('<option value="' + d.id + '">' + d.type + '</option>');
                    });
                    $('#type').trigger('chosen:updated');
                }
            }
        });
    }
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
    function getAllInk() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_rm_ink',
            success: function (data) {
                if (data != '') {
                    $('#ink').empty();
                    $('#ink').append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function (i, d) {
                        $('#ink').append('<option value="' + d.id + '">' + d.ink + '</option>');
                    });
                    $('#ink').trigger('chosen:updated');
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

    $(document).ready(function () {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_rm').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please select type"
        });
    });
</script>
<script>
    $(document).ready(function () {
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        }, "First letter can not be space");
        jQuery.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });
        jQuery.validator.addMethod("noAlphabets", function (value, element) {
            return this.optional(element) || /^[^A-Za-z]+$/.test(value);
        }, "Alphabets are not allowed!");

        $('#add_rm_form').validate({
            ignore: [],
            rules: {
                rm_name: {
                    required: true,
                    noSpaceAtStart: true,
                },
                type_of_rm: {
                    required: true
                },
                mfi: {
                    required: true,
                    noSpaceAtStart: true,

                },
                alias: {
                    required: true,
                    noSpaceAtStart: true,
                },
                make: {
                    required: true,
                },
                type: {
                    required: true
                },
                ink: {
                    required: true
                },
                uom_id: {
                    required: true
                },
                reorder_level: {
                    required: true
                },

            },
            messages: {
                rm_name: {
                    required: "Please enter name!",
                    noSpaceAtStart: "First letter can not be space!",
                },
                type_of_rm: {
                    required: "Please select raw  material type!"
                },
                mfi: {
                    required: "Please enter MFI!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                alias: {
                    required: "Please enter ALIAS!",
                    noSpaceAtStart: "First letter can not be space!",
                },
                make: {
                    required: "Please select MAKE!",
                },
                type: {
                    required: "Please select type!"
                },
                ink: {
                    required: "Please select ink!"
                },
                uom_id: {
                    required: "Please select UOM!"
                },
                reorder_level: {
                    required: "Please enter reorder level!"
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
            }
        });
        $("#type").change(function () {
            $("#type").valid();
        });
        $("#type_of_rm").change(function () {
            $("#type_of_rm").valid();
        });
        $("#make").change(function () {
            $("#make").valid();
        });
        $("#ink").change(function () {
            $("#ink").valid();
        });
    });
</script>
<script>
    $('#rm_name').on('keyup', function () {
        var rm_name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_rm_name',
            method: 'post',
            data: { 'rm_name': rm_name },
            success: function (response) {
                if (response == '1') {
                    $('#rm_name_error').text("This name is already added !");
                    $('#rm_name_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#rm_name_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    $('#mfi').on('keyup', function () {
        var mfi = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_rm_mfi',
            method: 'post',
            data: { 'mfi': mfi },
            success: function (response) {
                if (response == '1') {
                    $('#mfi_error').text("This mfi is already added !");
                    $('#mfi_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#mfi_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });

    $('#alias').on('keyup', function () {
        var alias = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_rm_alias',
            method: 'post',
            data: { 'alias': alias },
            success: function (response) {
                if (response == '1') {
                    $('#alias_error').text("This alias is already added !");
                    $('#alias_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#alias_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });

</script>