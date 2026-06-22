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
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                <?php if (!empty($single)) { ?>
                    Update PWO Status
                <?php } else { ?>
                    Add PWO Status
                <?php } ?>
            </h3>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <!-- First Panel: Form -->
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="pwo_form" id="pwo_form" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <!-- Order No (Auto-generated) -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="order_no">Order No <b class="require">*</b></label>
                                <input type="text" name="order_no" class="form-control" id="order_no"
                                    value="Auto-generated" disabled>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Second Panel: Table -->
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <table style="width: 100%;" class="table table-striped table-bordered" id="dataTable">
                        <thead class="thead">
                            <tr>
                                <th>Status</th>
                                <th>Size</th>
                                <th>Brand Name</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="store_rm_data">
                            <tr>
                                <td>Completed</td>
                                <td>10kg</td>
                                <td>
                                    ANAND OBD
                                </td>
                                <td>
                                 55
                                </td>
                                
                            </tr>
                           
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#printing_unit .child_menu').show();
        $('#printing_unit').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.pwo_status').addClass('active_cc');
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
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter cannot be a space");

    $(document).ready(function () {
        $('#pwo_form').validate({
            ignore: [],
            rules: {
                status: {
                    required: true,
                },
                size: {
                    required: true,
                },
                brand_name: {
                    required: true,
                },
                quantity: {
                    required: true,
                    number: true,
                }
            },
            messages: {
                status: {
                    required: "Please select status!",
                },
                size: {
                    required: "Please select size!",
                },
                brand_name: {
                    required: "Please select brand name!",
                },
                quantity: {
                    required: "Please enter quantity!",
                    number: "Only numeric values allowed!",
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