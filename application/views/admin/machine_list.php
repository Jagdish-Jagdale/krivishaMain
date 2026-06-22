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
    <div class="row">
        <div class="col-md-4">
            <div class="page-title">
                <div class="title_left">
                    <h3>
                        <?php if (!empty($single)) { ?>
                            Update Machine
                        <?php } else { ?>
                            Add Machine
                        <?php } ?>
                    </h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_machine_form" id="add_machine_form" enctype="multipart/form-data">
                            <div class="row flex_wrap">

                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Machine Name<b class="require">*</b></label>
                                    <input id="machine_name" name="machine_name" type="text" class="form-control" value="<?php if (!empty($single)) {
                                                                                                                                echo $single->machine_name;
                                                                                                                            } ?>" placeholder="Enter machine name" required>
                                    <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                                                                                                            echo $single->id;
                                                                                                        } ?>">
                                    <span id="name_error" class=""></span>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <button type="button" class="btn add_option" onclick="addNewOption('department')">Add New</button></label>
                                    <label>Department<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="department" id="department">
                                        <option value="">Please select department</option>
                                        <?php if (!empty($department)) {
                                            foreach ($department as $department_result) { ?>
                                                <option value="<?= $department_result->id ?>" <?php if (!empty($single) && $single->department_id == $department_result->id) { ?>selected<?php } ?>><?= $department_result->department ?></option>
                                        <?php }
                                        } ?>

                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Plant<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="plant" id="plant">
                                        <option value="">Please select plant</option>
                                        <?php if (!empty($plant)) {
                                            foreach ($plant as $plant_result) { ?>
                                                <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>><?= $plant_result->plant_name ?></option>
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

        <div class="col-md-8">
            <div class="page-title">
                <div class="title_left">
                    <h3>Machine List</h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Machine Name</th>
                                    <th>Department</th>
                                    <th>Plant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
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
    function addNewOption(master_type) {
        $('#master_type').val(master_type);
        if (master_type == 'department') {
            $('.modal-title').text('Add New Department');
            $('.add_opt').html('Department<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new department');
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
            if (master_type == 'department') {
                $('.new_option_error').text('Please enter department.');
                return false;
            } else {
                $('.new_option_error').text('Please enter option.');
            }
        } else if (master_type == 'department') {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>admin/Ajax_controller/set_new_department',
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

    function getAlldepartment() {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_machine_department',
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
        $('.add_machine').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
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
        $('#add_machine_form').validate({
            ignore: [],
            rules: {
                machine_name: {
                    required: true,
                    noSpaceAtStart: true,
                },
                department: {
                    required: true,
                },
                plant: {
                    required: true,
                },
            },
            messages: {
                machine_name: {
                    required: "Please enter machine name!",
                    noSpaceAtStart: "First letter can not be space!",
                },
                department: {
                    required: "Please select department!",
                },
                plant: {
                    required: "Please select plant!",
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
        $("#department").change(function() {
            $("#department").valid();
        });
        $("#plant").change(function() {
            $("#plant").valid();
        });

    });
</script>
<script>
    $('#machine_name').on('keyup', function() {
        var machine_name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_machine_name',
            method: 'post',
            data: {
                'machine_name': machine_name,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if (response == '1') {
                    $('#name_error').text("This machine name is already exists !");
                    $('#name_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#name_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            // "responsive": true,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'machine_list',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_machine_list",
                "type": "POST",
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>