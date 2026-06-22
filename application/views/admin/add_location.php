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
                    Update Location
                <?php } else { ?>
                    Add Location
                <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_location" id="add_location" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                        <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>City Name<b class="require">*</b></label>
                                <input name="city" type="text" id="city" class="form-control"
                                    value="<?php if (!empty($single)) {
                                        echo $single->city;
                                    } ?>"
                                    placeholder="Enter city name" required>
                                <span id="city_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>District Name<b class="require">*</b></label>
                                <input name="district_name" type="text" id="district_name" class="form-control"
                                    value="<?php if (!empty($single)) {
                                        echo $single->district_name;
                                    } ?>"
                                    placeholder="Enter district name" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>State Name<b class="require">*</b></label>
                                <input name="state_name" type="text" id="state_name" class="form-control"
                                    value="<?php if (!empty($single)) {
                                        echo $single->state_name;
                                    } ?>"
                                    placeholder="Enter state name" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Pincode<b class="require">*</b></label>
                                <input name="pincode" type="text" id="pincode" class="form-control"
                                    value="<?php if (!empty($single)) {
                                        echo $single->pincode;
                                    } ?>"
                                    placeholder="Enter pincode" required>
                                <span id="pincode_error" class=""></span>
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



<?php include('footer.php');
$id = 0;
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);
}
?>





<script>
    $(document).ready(function () {
        //$('#master .child_menu').show();
        $('#master').addClass('nv active');
        //$('.right_col').addClass('active_right');
       $('.add_location').addClass('active_cc');
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
        $.validator.addMethod("validPincode", function (value, element) {
            return this.optional(element) || (/^[0-9]{6}$/.test(value) && value !== '000000');
        }, "Please enter a valid Pincode!");
        jQuery.validator.addMethod("noAlphabets", function (value, element) {
            return this.optional(element) || /^[^A-Za-z]+$/.test(value);
        }, "Alphabetic characters are not allowed.");


        $('#add_location').validate({
            rules: {
                city: {
                    required: true,
                    noSpaceAtStart: true,
                   // noNumbers: true
                },
                district_name: {
                    required: true,
                    noSpaceAtStart: true,
                    noNumbers: true

                },
                state_name: {
                    required: true,
                    noSpaceAtStart: true,
                    noNumbers: true
                },
                pincode: {
                    required: true,
                    noSpaceAtStart: true,
                    noAlphabets: true,
                    validPincode: true,
                }

            },
            messages: {
                city: {
                    required: "Please enter city!",
                    noSpaceAtStart: "First letter can not be space!",
                    //noNumbers: "Numbers are not allowed!"
                },
                district_name: {
                    required: "Please enter district name!",
                    noSpaceAtStart: "First letter can not be space!",
                    noNumbers: "Numbers are not allowed!"
                },
                state_name: {
                    required: "Please enter state name!",
                    noSpaceAtStart: "First letter can not be space!",
                    noNumbers: "Numbers are not allowed!"
                },
                pincode: {
                    required: "Please enter pincode!",
                    noSpaceAtStart: "First letter can not be space!",
                    validPincode: "Please enter a valid Pincode!",
                    noAlphabets: "Alphabets are not allowed!"

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
    $('#city').on('keyup', function () {
        var city = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_city_name',
            method: 'post',
            data: {
                'city': city,
                'id': '<?= $id ?>'
            },
            success: function (response) {
                if (response == '1') {
                    $('#city_error').text("This city is already added!");
                    $('#city_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#city_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });

    $('#pincode').on('keyup', function () {
        var pincode = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_pincode_name',
            method: 'post',
            data: {
                'pincode': pincode,
                'id': '<?= $id ?>'

            },
            success: function (response) {
                if (response == '1') {
                    $('#pincode_error').text("This pincode is already added!");
                    $('#pincode_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#pincode_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });


    });

</script>

