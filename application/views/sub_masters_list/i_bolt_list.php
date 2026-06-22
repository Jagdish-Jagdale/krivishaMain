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
<?php
$uri_segment_2 = $this->uri->segment(2);
?>

<div class="right_col" role="main">
    <div class="row">
        <?php if (!empty($uri_segment_2)): ?>
        <div class="col-md-4">
            <div class="page-title">
                <div class="title_left">
                    <h3>Update I Bolt</h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_form" id="add_form" enctype="multipart/form-data">
                            <div class="row flex_wrap">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>I Bolt<b class="require">*</b></label>
                                    <input id="i_bolt" name="i_bolt" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->i_bolt;} ?>"
                                           placeholder="Enter i bolt" required>
                                    <input autocomplete="off" type="hidden" name="id" id="id"
                                           value="<?php if (!empty($single)) { echo $single->id; } ?>">
                                    <span id="name_error" class=""></span>
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
        <?php endif; ?>
        <?php
        if (!empty($uri_segment_2)):
            ?>
            <div class="col-md-8">
            <?php
        else:
            ?>
            <div class="col-md-12">
            <?php
        endif;
        ?>
                <div class="page-title">
                    <div class="title_left">
                        <h3>I Bolt List</h3>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="x_panel">
                    <div class="x_content">
                        <div class="container">
                            <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                                <thead class="thead">
                                    <tr>
                                        <th>SR NO.</th>
                                        <th>I Bolt </th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php include('footer.php');
?>
<script>
    $(document).ready(function () {
        $('#sub_master_management .child_menu').show();
        $('#sub_master_management').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.i_bolt_list').addClass('active_cc');
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
        $('#add_form').validate({
            ignore: [],
            rules: {
                i_bolt: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {
                i_bolt: {
                    required: "Please enter i bolt!",
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
                    filename: 'list',
                    exportOptions: {
                        columns: [0, 1]
                    }
                }
            ],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_sub_i_bolt_list",
                "type": "POST",
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>