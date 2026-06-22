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
            <h3>Update Transport</h3>
            <?php } else { ?>
                Add Transport
            <?php } ?>
        </h3>
       
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_transport_form" id="add_transport_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Transport Name<b class="require">*</b></label>
                                <input name="transport_name" id="transport_name" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->transport_name; } ?>" placeholder="Enter transport name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Mobile NO 1<b class="require">*</b></label>
                                <input name="mobile_one" id="mobile_one" type="tel" class="form-control" value="<?php if (!empty($single)) { echo $single->mobile_one; } ?>" placeholder="Enter mobile no 1" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Mobile NO 2</label>
                                <input name="mobile_two" id="mobile_two" type="tel" class="form-control" value="<?php if (!empty($single)) { echo $single->mobile_two; } ?>" placeholder="Enter mobile no 2" >
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Transport ID <b class="require">*</b></label>
                                <input name="transport_id" id="transport_id" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->transport_id; } ?>" placeholder="Enter transport id" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>City <b class="require">*</b></label>
                                <select name="city[]" id="city" type="text" class="form-control js-example-basic-multiple" value="" placeholder="Enter city required" multiple>
                                    <option value="">Choose</option>
                                    <?php if (!empty($city)){
                                     foreach ($city as $city_result) { ?>
                                            <option value="<?= $city_result->id ?>"<?php if(!empty($single)&& $single->city_id==$city_result->id){?>selected<?php }?>><?= $city_result->city ?></option>
                                        <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Transporter Rating<b class="require">*</b></label>
                                <select name="transport_rating" id="transport_rating" type="text" class="form-control js-example-basic-multiple" value="" placeholder="Enter Transporter Rating required">
                                    <option value="">Choose</option>
                                    <option value="1 Star" <?php echo (!empty($single) && $single->transport_rating == '1 Star') ? 'selected' : ''; ?>>1 Star</option>
                                    <option value="2 Stars"<?php echo (!empty($single) && $single->transport_rating == '2 Stars') ? 'selected' : ''; ?>>2 Stars</option>
                                    <option value="3 Stars"<?php echo (!empty($single) && $single->transport_rating == '3 Stars') ? 'selected' : ''; ?>>3 Stars</option>
                                    <option value="4 Stars"<?php echo (!empty($single) && $single->transport_rating == '4 Stars') ? 'selected' : ''; ?>>4 Stars</option>
                                    <option value="5 Stars"<?php echo (!empty($single) && $single->transport_rating == '5 Stars') ? 'selected' : ''; ?>>5 Stars</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" class="btn btn-primary">Submit</button>
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
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2({
            placeholder: "Please Select"
        });
        $('#city').select2({
            placeholder: "Please select city"
        });
        $('#transport_rating').select2({
            placeholder: "Please select transporter rating"
        });
    });
</script>
<script>
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_transport').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    jQuery.validator.addMethod("noNumbers", function (value, element) {
        return this.optional(element) || !/\d/.test(value);
    });
    $.validator.addMethod("validMobile", function(value, element) {
        return this.optional(element) || (/^[0-9]{10}$/.test(value) && !/^(.)\1{9}$/.test(value));
    }, "Please enter a valid mobile number!");
    $.validator.addMethod("validTransportId", function(value, element) {
        return this.optional(element) || !/^0+$/.test(value);
    }, "Please enter a valid Transport ID!");
    $(document).ready(function() {
        $('#add_transport_form').validate({
            ignore: [],
            rules: {
                transport_name: {
                    required: true,
                    noSpaceAtStart: true,
                },
                mobile_one: {
                    required:true,
                    number:true,
                    noSpaceAtStart: true,
                    validMobile: true
                },
                mobile_two: {
                    number:true,
                    validMobile: true
                },
                transport_id: {
                    required:true,
                    noSpaceAtStart: true,
                    validTransportId:true
                },
                'city[]': {
                    required:true
                },
                transport_rating: {
                    required:true
                }
            },
            messages: {
                transport_name: {
                    required:"Please enter transport name!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                mobile_one: {
                    required:"Please enter mobile no!",
                    noSpaceAtStart: "First digit can not be space!",
                    validMobile: "Please enter valid mobile number!",
                    number: "Please enter a valid number"
                },
                mobile_two: {
                    validMobile: "Mobile number should be 10 digits!",
                    number: "Please enter a valid number"
                },
                transport_id: {
                    required:"Please enter transport ID!",
                    noSpaceAtStart: "First letter can not be space!",
                    validTransportId: "Please enter valid transport ID!"    
                },
                'city[]': {
                    required:"Please select city!"
                },
                transport_rating: {
                    required:"Please select transporter rating!"
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
        $("#city").change(function() {
            $("#city").valid();
        });
        $("#transport_rating").change(function() {
            $("#transport_rating").valid();
        });
    })
</script>