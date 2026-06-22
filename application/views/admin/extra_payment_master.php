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
                            Update Extra Payment Option
                        <?php } else { ?>
                            Add Extra Payment Option
                        <?php } ?>
                    </h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_uom_form" id="add_uom_form" enctype="multipart/form-data">
                            <div class="row flex_wrap">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Extra Payment Option<b class="require">*</b></label>
                                    <input id="extra_payment_id" name="extra_payment_id" type="text"
                                        class="form-control" value="<?php if (!empty($single)) {
                                            echo $single->extra_payment_option;
                                        } ?>" placeholder="Enter extra payment option" required>
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
                    <h3>Extra Payment Option List</h3>
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
                                    <th>Extra Payment Option</th>
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
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.extra_payment_master').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });
</script>

<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    jQuery.validator.addMethod("noNumbers", function (value, element) {
        return this.optional(element) || !/\d/.test(value);
    });

    $(document).ready(function () {
        $('#add_uom_form').validate({
            ignore: [],
            rules: {
                extra_payment_id: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {
                extra_payment_id: {
                    required: "Please enter extra payment option!",
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
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    title: 'Extra Payment Option List',
                    filename: 'extra_payment_option_list',
                    exportOptions: {
                        columns: [0, 1]
                    }
                }
            ],
            //scrollX: true, 
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_extra_payment_option_list",
                "type": "POST",
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });


</script>
<script>
    $('#extra_payment_id').on('keyup', function () {
        var val = $(this).val().trim();
        if (val === "") {
            $('#submit_btn').prop('disabled', true);
            $('#name_error').text("");
        } else {
            checkDepartmentUnique();
        }
    });
    function checkDepartmentUnique() {
        var name = $('#extra_payment_id').val();
        var id = $('#id').val();

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_extra_payment_name',
            method: 'post',
            data: {
                'extra_payment_id': name,
                'id': id
            },
            success: function (response) {
                if (response == '1') {
                    $('#name_error').text("This payment option already exists!");
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
