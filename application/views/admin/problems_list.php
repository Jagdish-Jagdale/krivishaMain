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
                            Update Problem
                        <?php } else { ?>
                            Add Problem
                        <?php } ?>
                    </h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_problems_form" id="add_problems_form"
                            enctype="multipart/form-data">
                            <div class="row flex_wrap">
                                <div class="form-group col-md-12 col-sm-12 col-xs 12">
                                    <label>Maintenance required for<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="maintain_actions" id="maintain_actions">
                                        <option value="">Please select maintenance</option>
                                        <option value="1" <?php echo (!empty($single) && $single->maintaince == '1') ? 'selected' : ''; ?>>Machine</option>
                                        <option value="2" <?php echo (!empty($single) && $single->maintaince == '2') ? 'selected' : ''; ?>>Mould/Article Name</option>
                                        <option value="3" <?php echo (!empty($single) && $single->maintaince == '3') ? 'selected' : ''; ?>>Printing Unit</option>
                                        <option value="4" <?php echo (!empty($single) && $single->maintaince == '4') ? 'selected' : ''; ?>>Plant</option>
                                        <option value="5" <?php echo (!empty($single) && $single->maintaince == '5') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                                                                                                        echo $single->id;
                                                                                                    } ?>">
                                <div class="form-group col-md-12 col-sm-12 col-xs 12" id="type_div">
                                    <label>Type<b class="require">*</b></label>
                                    <select style="display: none;" class="form-control js-example-basic-multiple"
                                        name="type" id="type">
                                        <option value="">Type</option>
                                        <?php
                                        if (!empty($single)) {
                                            if ($single->maintaince == '1') {
                                                $machine_name_id = $this->Admin_model->get_machine_type_problem($single->maintaince);
                                                if (!empty($machine_name_id)) {
                                                    foreach ($machine_name_id as $machine_result) { ?>
                                                        <option value="<?= $machine_result->id ?>" <?php if (!empty($single) && $single->type_id == $machine_result->id) { ?> selected <?php } ?>>
                                                            <?= $machine_result->machine_name ?>
                                                        </option>
                                                    <?php }
                                                }
                                            } elseif ($single->maintaince == '2') {
                                                $mould_article_name_id = $this->Admin_model->get_article_type_problem();
                                                if (!empty($mould_article_name_id)) {
                                                    foreach ($mould_article_name_id as $mould_article_name_result) { ?>
                                                        <option value="<?= $mould_article_name_result->id ?>" <?php if (!empty($single) && $single->type_id == $mould_article_name_result->id) { ?> selected <?php } ?>>
                                                            <?= $mould_article_name_result->article_name ?>
                                                        </option>
                                                    <?php }
                                                }
                                            } elseif ($single->maintaince == '3') {
                                                $printing_unit_name_id = $this->Admin_model->get_machine_type_problem($single->maintaince);
                                                if (!empty($printing_unit_name_id)) {
                                                    foreach ($printing_unit_name_id as $printing_unit_name_result) { ?>
                                                        <option value="<?= $printing_unit_name_result->id ?>" <?php if (!empty($single) && $single->type_id == $printing_unit_name_result->id) { ?> selected <?php } ?>>
                                                            <?= $printing_unit_name_result->machine_name ?>
                                                        </option>
                                                    <?php }
                                                }
                                            } elseif ($single->maintaince == '4') {
                                                $plant_name_id = $this->Admin_model->get_plant_type_problem();
                                                if (!empty($plant_name_id)) {
                                                    foreach ($plant_name_id as $plant_name_result) { ?>
                                                        <option value="<?= $plant_name_result->id ?>" <?php if (!empty($single) && $single->type_id == $plant_name_result->id) { ?> selected <?php } ?>>
                                                            <?= $plant_name_result->plant_name ?>
                                                        </option>
                                                <?php }
                                                }
                                            } elseif ($single->maintaince == '5') { ?>
                                                <option value="5" selected>N/A</option>
                                        <?php }
                                        } ?>

                                    </select>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Problem<b class="require">*</b></label>
                                    <input name="problem" id="problem" type="text" class="form-control" value="<?php echo (!empty($single) ? $single->problem : ''); ?>"
                                        placeholder="Enter problem description" required>
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
        <div class="col-md-8">
            <div class="page-title">
                <div class="title_left">
                    <h3>Problems List</h3>
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
                                    <th>Maintenance</th>
                                    <th>Type</th>
                                    <th>Problems</th>
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
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_problems').addClass('active_cc');
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
        $('#add_problems_form').validate({
            ignore: [],
            rules: {
                maintain_actions: {
                    required: true,

                },
                type: {
                    required: function() {
                        return $('#maintain_actions').val() != '5';
                    },
                },
                problem: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {
                maintain_actions: {
                    required: "Please select maintenance!",

                },
                type: {
                    required: "Please select type!",
                },
                problem: {
                    required: "Please enter problem!",
                    noSpaceAtStart: "First letter can not be space",
                }
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
        $('#maintain_actions').change(function() {
            $("#maintain_actions").valid();
        });
        $('#type').change(function() {
            $("#type").valid();
        });
        $('#problem').change(function() {
            $("#problem").valid();
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#maintain_actions').select2({
            placeholder: "Please select Maintenance"
        });

        $('#type').select2({
            placeholder: "Please select type"
        });

        $('#maintain_actions').on('change', function() {
            var selected_master = $('#maintain_actions').val();
            $('#type').val(selected_master);
            $('#type').html('<option value="">Select Type</option>');
            $.ajax({
                type: "POST",
                url: "<?= base_url("admin/Ajax_controller/get_all_type_of_maintenance") ?>",
                data: {
                    'selected_master': selected_master
                },
                success: function(response) {
                    $("#type").empty();
                    $('#type').append('<option value="">Select type</option>');
                    var opts = $.parseJSON(response);
                    $.each(opts, function(i, d) {
                        if (selected_master == '1') {
                            $('#type').append('<option value="' + d.id + '">' + d.machine_name + '</option>');
                        } else if (selected_master == '2') {
                            $('#type').append('<option value="' + d.id + '">' + d.article_name + '</option>');
                        } else if (selected_master == '3') {
                            $('#type').append('<option value="' + d.id + '">' + d.machine_name + '</option>');
                        } else if (selected_master == '4') {
                            $('#type').append('<option value="' + d.id + '">' + d.plant_name + '</option>');
                        }
                    });
                    $('#type').trigger('chosen:updated');

                }
            });



        });
    });
</script>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,
            layout: {
                topStart: {
                    buttons: ['pageLength']
                }
            },
            dom: "Blfrtip",
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'problems_list',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_problems_list",
                "type": "POST",
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>