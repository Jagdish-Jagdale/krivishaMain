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
                            Update Remark
                        <?php } else { ?>
                            Production Idle State Reasons
                        <?php } ?>
                    </h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_remark_form" id="add_remark_form" enctype="multipart/form-data">
                            <div class="row flex_wrap">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Remark<b class="require">*</b></label>
                                    <input id="remark_name" name="remark_name" type="text" class="form-control" value="<?php if (!empty($single)) {
                                        echo $single->remark_name;
                                    } ?>" placeholder="Enter remark" required>
                                    <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                                        echo $single->id;
                                    } ?>">
                                    <span id="name_error" class=""></span>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <button type="submit" id="submit_btn" name="submit_btn"
                                        class="btn btn-primary">Submit</button>
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
                    <h3>Production Idle State Reasons</h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR.NO.</th>
                                    <th>Remark</th>
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

<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#master').addClass('nv active');
        $('.remark_master').addClass('active_cc');
    });
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");

    $(document).ready(function () {
        $('#add_remark_form').validate({
            ignore: [],
            rules: {
                remark_name: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {
                remark_name: {
                    required: "Please enter remark!",
                    noSpaceAtStart: "First letter can not be space!",
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
    });
</script>
<script>
    $(document).ready(function () {
        $('#example').DataTable({
            "lengthChange": true,
            "responsive": true,
            "lengthMenu": [10, 25, 50, 100],
            "searching": true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Production Idle State Reasons',
                filename: 'remark_master_list',
                exportOptions: {
                    columns: [0, 1]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_remark_master_list",
                "type": "POST",
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>
<script>
    $('#remark_name').on('keyup', function () {
        var val = $(this).val().trim();
        if (val === "") {
            $('#submit_btn').prop('disabled', true);
            $('#name_error').text("");
        } else {
            checkRemarkUnique();
        }
    });

    function checkRemarkUnique() {
        var name = $('#remark_name').val();
        var id = $('#id').val();

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_remark_name',
            method: 'post',
            data: {
                'remark_name': name,
                'id': id
            },
            success: function (response) {
                if (response == '1') {
                    $('#name_error').text("This remark already exists!");
                    $('#name_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#name_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    }
</script>
