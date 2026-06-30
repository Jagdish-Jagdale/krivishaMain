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
                    Update Own Vehicle Details
                <?php } else { ?>
                    Add Own Vehicle Details
                <?php } ?>
            </h3>

        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="own_vehicle_form" id="own_vehicle_form" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="id" value="<?php if (!empty($single)) {
                            echo $single->id;
                        } ?>">
                        <div class="row flex_wrap">

                            <!-- vehical -->
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <button type="button" class="btn add_option" onclick="addNewOption('vehical')">Add
                                    New</button></label>
                                <label>Vehicle<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="vehical" id="vehical">
                                    <option value="">Please select vehicle</option>
                                    <?php if (!empty($vehical)) {
                                        foreach ($vehical as $vehical_result) { ?>
                                            <option value="<?= $vehical_result->id ?>" <?php if (!empty($single) && $single->vehical_id == $vehical_result->id) { ?>selected<?php } ?>>
                                                <?= $vehical_result->vehical ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <!-- Challan DC No -->
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="challan_dc_no">Challan DC No<b class="require">*</b></label>
                                <input type="number" name="challan_dc_no" class="form-control" id="challan_dc_no"
                                    value="<?php echo !empty($single) ? $single->challan_dc_no : '' ?>"
                                    placeholder="Please enter challan dc no">
                                <span id="challan_dc_no_error" class=""></span>
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="invoice_no">Invoice No<b class="require">*</b></label>
                                <input type="text" name="invoice_no" class="form-control" id="invoice_no"
                                    value="<?php echo !empty($single) ? $single->invoice_no : '' ?>"
                                    placeholder="Please enter invoice">
                                <span id="invoice_no_error" class=""></span>
                            </div>
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="invoice_value">Invoice Value<b class="require">*</b></label>
                                <input type="number" name="invoice_value" class="form-control" id="invoice_value"
                                    value="<?php echo !empty($single) ? $single->invoice_value : '' ?>"
                                    placeholder="Please enter invoice value">
                                <span id="invoice_value_error" class=""></span>
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="location_id">Location<b class="require">*</b></label>
                                <select name="location_id" id="location_id"
                                    class="form-control js-example-basic-multiple">
                                    <option value="">Select location</option>
                                    <?php if (!empty($location)) {
                                        foreach ($location as $result) { ?>
                                            <option value="<?= $result->id ?>" <?php if (!empty($single) && $single->location_id == $result->id) { ?>selected<?php } ?>><?= $result->city ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="pincode">Pincode<b class="require">*</b></label>
                                <input type="number" name="pincode" class="form-control" id="pincode" readonly
                                    value="<?php echo !empty($single) ? $single->pincode : '' ?>"
                                    placeholder="Please enter pincode">
                            </div>


                            <?php
                            $saved = !empty($single->purpose)
                                ? explode(',', $single->purpose)
                                : [];
                            ?>
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="purpose">Purpose <b class="require">*</b></label>
                                <select multiple name="purpose[]" id="purpose"
                                    class="form-control js-example-basic-multiple" required>
                                    <option value="1" <?= in_array('1', $saved) ? 'selected' : '' ?>>
                                        Delivery
                                    </option>
                                    <option value="2" <?= in_array('2', $saved) ? 'selected' : '' ?>>
                                        Pickup
                                    </option>
                                    <option value="3" <?= in_array('3', $saved) ? 'selected' : '' ?>>
                                        Others
                                    </option>
                                </select>
                            </div>



                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="party_id">Party Name<b class="require">*</b></label>
                                <select name="party_id" id="party_id" class="form-control js-example-basic-multiple">
                                    <option value="">Select party</option>
                                    <?php if (!empty($party_name)) {
                                        foreach ($party_name as $party_result) { ?>
                                            <option value="<?= $party_result->id ?>" <?php if (!empty($single) && $single->party_id == $party_result->id) { ?>selected<?php } ?>>
                                                <?= $party_result->party_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <input type="hidden" name="out_km" class="form-control" id="out_km"
                                value="<?php echo !empty($single) ? $single->out_km : '' ?>">

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="in_km">In KM<b class="require">*</b></label>
                                <input type="number" step="any" min="0" name="in_km" class="form-control" id="in_km"
                                    value="<?php echo !empty($single) ? $single->in_km : '' ?>"
                                    placeholder="Please enter in km">
                                <span id="in_km_error" class=""></span>
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="exact_km">Exact KM<b class="require">*</b></label>
                                <input type="number" step="0.01" name="exact_km" class="form-control" id="exact_km"
                                    value="<?php echo !empty($single) ? $single->exact_km : '' ?>"
                                    placeholder="Auto-calculated" readonly>
                            </div>
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="market_freight">Market Freight<b class="require">*</b></label>
                                <input type="number" name="market_freight" class="form-control" id="market_freight"
                                    min="1" value="<?php echo !empty($single) ? $single->market_freight : '' ?>"
                                    placeholder="Please enter market freight">
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="diesel_topup">Diesel Top-up (Ltr)<b class="require">*</b></label>
                                <input type="number" min="0" name="diesel_topup" class="form-control" id="diesel_topup"
                                    value="<?php echo !empty($single) ? $single->diesel_topup : '' ?>"
                                    placeholder="Please enter diesel top-up">
                            </div>


                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="diesel_rate">Diesel Rate (₹/Ltr)<b class="require">*</b></label>
                                <input type="number" min="0" name="diesel_rate" class="form-control" id="diesel_rate"
                                    value="<?php echo !empty($single) ? $single->diesel_rate : '' ?>"
                                    placeholder="Enter diesel rate">
                            </div>



                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="diesel_expense">Diesel Expense (₹)<b class="require">*</b></label>
                                <input type="number" step="0.01" name="diesel_expense" class="form-control"
                                    id="diesel_expense"
                                    value="<?php echo !empty($single) ? $single->diesel_expense : '' ?>"
                                    placeholder="Auto-calculated" readonly>
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="driver_expense">Driver Expense (₹)<b class="require">*</b></label>
                                <input type="number" step="0.01" name="driver_expense" class="form-control"
                                    id="driver_expense"
                                    value="<?php echo !empty($single) ? $single->driver_expense : '' ?>"
                                    placeholder="Enter driver expense">
                            </div>

                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="maintenance">Maintenance<b class="require">*</b></label>
                                <input type="text" name="maintenance" class="form-control" id="maintenance"
                                    value="<?php echo !empty($single) ? $single->maintenance : '' ?>"
                                    placeholder="Please enter maintenance">
                            </div>
                            <div class="form-group col-xl-3 col-lg-4 col-md-4 col-sm-6 col-xs-12">
                                <label for="transport_percent">Transport %</label>
                                <input type="number" step="0.01" name="transport_percent" class="form-control"
                                    id="transport_percent"
                                    value="<?php echo !empty($single) ? $single->transport_percent : '' ?>"
                                    placeholder="Auto-calculated" readonly>
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
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);

}
?>
<script>
    $(document).ready(function () {
        if ('<?= $id ?>' !== '') {
            $('#in_km').attr('readonly', true);
        }
        $('#vehical').select2({
            placeholder: "Please select vehicle",
            width: '100%'
        });
        $('#location_id').select2({
            placeholder: "Please select location",
            width: '100%'
        });
        $('#party_id').select2({
            placeholder: "Please select party",
            width: '100%'
        });
        $('#purpose').select2({
            placeholder: "Please select purpose",
            width: '100%'
        });

        // Fetch last In KM dynamically on vehicle change
        $('#vehical').on('change', function () {
            var vehicalId = $(this).val();
            if (vehicalId !== '' && '<?= $id ?>' === '') {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_last_in_km_by_vehicle',
                    method: 'POST',
                    data: {
                        vehical_id: vehicalId
                    },
                    dataType: 'json',
                    success: function (response) {
                        var last_in_km = parseFloat(response.last_in_km) || 0;
                        $('#out_km').val(last_in_km);
                        $('#in_km').trigger('keyup');
                        if ($.fn.valid) {
                            $('#in_km').valid();
                        }
                    },
                });
            } else if ('<?= $id ?>' === '') {
                $('#out_km').val('');
                $('#in_km').trigger('keyup');
                if ($.fn.valid) {
                    $('#in_km').valid();
                }
            }
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        let inKm = document.getElementById("in_km");
        let outKm = document.getElementById("out_km");
        let exactKm = document.getElementById("exact_km");
        let dieselRate = document.getElementById("diesel_rate");
        let dieselTopup = document.getElementById("diesel_topup");
        let driverExpense = document.getElementById("driver_expense");
        let dieselExpense = document.getElementById("diesel_expense");
        let invoiceValue = document.getElementById("invoice_value");
        let transportPercent = document.getElementById("transport_percent");

        function calculate() {
            let inVal = parseFloat(inKm.value) || 0;
            let outVal = parseFloat(outKm.value) || 0;
            let exact_km = parseFloat(exactKm.value) || 0;
            let km = 0;
            // if ('<?= $id ?>' !== '') {
            //     km = exact_km;
            //     exactKm.value = km.toFixed(2);
            // }else{
            //     km = (inVal && outVal) ? (inVal - outVal) : 0;
            //     exactKm.value = km.toFixed(2);
            // }
            km = (inVal && outVal) ? (inVal - outVal) : 0;
            exactKm.value = km.toFixed(2);
            
            
            let rate = parseFloat(dieselRate.value) || 0;
            // Diesel Expenses = Exact KM / 9 * Diesel Rate
            let dieselExpenses = (km / 9) * rate;

            // Set Diesel Expenses 
            dieselExpense.value = dieselExpenses.toFixed(2);

            // Invoice Value
            let invVal = parseFloat(invoiceValue.value) || 0;
            let driverExp = parseFloat(driverExpense.value) || 0;
            // Transport % = (Driver Expenses + Diesel Expenses) / Invoice Value × 100
            let tPercent = invVal > 0 ? ((driverExp + dieselExpenses) / invVal) * 100 : 0;
            transportPercent.value = tPercent.toFixed(2);
        }

        [inKm, outKm, dieselRate, dieselTopup, driverExpense, invoiceValue].forEach(el => {
            el.addEventListener("input", calculate);
        });

        if (typeof $ !== "undefined") {
            $(document).ready(function () {
                if ('<?= $id ?>' !== '') {
                    $('#diesel_rate, #driver_expense, #diesel_topup, #invoice_value').on("change", calculate);
                }else{
                    $('#in_km, #out_km, #diesel_rate, #driver_expense, #diesel_topup, #invoice_value').on("change", calculate);
                }
            });
        }

        // calculate(); 
    });
</script>


<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter cannot be a space");

    $.validator.addMethod("greaterThanOutKm", function (value, element) {
        var out_km = parseFloat($('#out_km').val()) || 0;
        var in_km = parseFloat(value) || 0;
        return this.optional(element) || in_km > out_km;
    }, function() {
        var out_km = parseFloat($('#out_km').val()) || 0;
        return "In Km must be greater than previous record In Km (" + out_km + ")";
    });

    $(document).ready(function () {
        $('#own_vehicle_form').validate({
            ignore: [],
            rules: {
                vehical: {
                    required: true,
                    noSpaceAtStart: true,
                },
                challan_dc_no: {
                    required: true,
                    noSpaceAtStart: true,
                },
                invoice_no: {
                    required: true,
                    noSpaceAtStart: true,
                },
                invoice_value: {
                    required: true,
                    noSpaceAtStart: true,
                },
                'purpose[]': {
                    required: true,
                },
                party_id: {
                    required: true,
                    noSpaceAtStart: true,
                },
                location_id: {
                    required: true,
                    noSpaceAtStart: true,
                },
                in_km: {
                    required: true,
                    number: true,
                    greaterThanOutKm: true,
                },
                market_freight: {
                    required: true,
                    number: true,
                },
                diesel_topup: {
                    required: true,
                    number: true,
                },
                driver_expense: {
                    required: true,
                    number: true,
                },
                maintenance: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {
                vehical: {
                    required: "Please enter challan vehicle!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                challan_dc_no: {
                    required: "Please enter challan dc no!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                invoice_no: {
                    required: "Please enter challan invoice no!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                invoice_value: {
                    required: "Please enter invoice value!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                'purpose[]': {
                    required: "Please select purpose!",
                },
                party_id: {
                    required: "Please enter party name!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                location_id: {
                    required: "Please enter location name!",
                    noSpaceAtStart: "First letter cannot be a space!",
                },
                in_km: {
                    required: "Please enter in km!",
                    number: "Only numeric values allowed!",
                    greaterThanOutKm: "In Km must be greater than previous record In Km!",
                },
                market_freight: {
                    required: "Please enter market freight!",
                    number: "Only numeric values allowed!",
                },
                diesel_topup: {
                    required: "Please enter diesel top-up!",
                    number: "Only numeric values allowed!",
                },
                driver_expense: {
                    required: "Please enter driver expense!",
                    number: "Only numeric values allowed!",
                },
                maintenance: {
                    required: "Please enter maintenance!",
                    noSpaceAtStart: "First letter cannot be a space!",
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
        $('#purpose').change(function () {
            $("#purpose").valid();
        });
        $('#vehical').change(function () {
            $("#vehical").valid();
        });
        $('#party_id').change(function () {
            $("#party_id").valid();
        });
        $('#location_id').change(function () {
            $("#location_id").valid();
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#location_id').on('change', function () {
            var locationId = $(this).val();
            if (locationId !== '') {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_pincode_by_location',
                    method: 'POST',
                    data: {
                        location_id: locationId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response && response.pincode) {
                            $('#pincode').val(response.pincode);
                        } else {
                            $('#pincode').val('');
                        }
                    },
                });
            } else {
                $('#pincode').val('');
            }
        });
    });
    $(document).ready(function () {
        $('#in_km').on('keyup', function () {
            var in_km = parseFloat($(this).val()) || 0;
            var out_km = parseFloat($('#out_km').val()) || 0;

            if (in_km <= out_km) {
                $('#in_km_error').text('In Km must be greater than ' + out_km);
                $('#in_km_error').addClass('error');
                $('#submit_btn').prop('disabled', true);
            } else {
                $('#in_km_error').text("");
                $('#in_km_error').removeClass('error');
                $('#submit_btn').prop('disabled', false);
            }
        });
    });
    $(document).ready(function () {
        $('#challan_dc_no').on('keyup', function () {
            var challan_dc_no = $(this).val();
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/check_unique_challan_dc_no',
                method: 'post',
                data: {
                    'challan_dc_no': challan_dc_no,
                    'id': '<?= $id ?>'
                },
                success: function (response) {
                    if (response == '1') {
                        $('#challan_dc_no_error').text("This challan no is already added!");
                        $('#challan_dc_no_error').addClass('error');
                        $('#submit_btn').prop('disabled', true);
                    } else {
                        $('#challan_dc_no_error').text("");
                        $('#submit_btn').prop('disabled', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus, errorThrown);
                }
            });
        });
    });
    $(document).ready(function () {
        $('#invoice_no').on('keyup', function () {
            var invoice_no = $(this).val();
            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/check_unique_invoice_no',
                method: 'post',
                data: {
                    'invoice_no': invoice_no,
                    'id': '<?= $id ?>'
                },
                success: function (response) {
                    if (response == '1') {
                        $('#invoice_no_error').text("This invoice no is already added!");
                        $('#invoice_no_error').addClass('error');
                        $('#submit_btn').prop('disabled', true);
                    } else {
                        $('#invoice_no_error').text("");
                        $('#submit_btn').prop('disabled', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error: ' + textStatus, errorThrown);
                }
            });
        });
    });

</script>


<script>
    function addNewOption(master_type) {
        $('#master_type').val(master_type);
        if (master_type == 'vehical') {
            $('.modal-title').text('Add New vehicle');
            $('.add_opt').html('Vehicle<b class="require">*</b>');
            $('#new_option').attr('placeholder', 'Enter new vehicle');
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
            if (master_type == 'vehical') {
                $('.new_option_error').text('Please enter vehicle.');
            } else {
                $('.new_option_error').text('Please enter option.');
            }
            return false;
        }

        if (master_type == 'vehical') {
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>admin/Ajax_controller/set_new_vehical',
                data: { new_option: new_option, master_type: master_type },
                success: function (data) {
                    if (data > 0) {
                        getAllvehical(data);
                        $('#exampleModal').modal('hide');
                    } else {
                        $('.new_option_error').text('This vehicle already exists.');
                    }
                }
            });
        }
    });
    function getAllvehical(selectedId) {
        $.ajax({
            type: 'POST',
            url: '<?= base_url() ?>admin/Ajax_controller/get_all_vehical',
            success: function (data) {
                if (data != '') {
                    var $vehical = $('#vehical');
                    $vehical.empty().append('<option value="">Please Select</option>');
                    var opts = $.parseJSON(data);
                    $.each(opts, function (i, d) {
                        $vehical.append('<option value="' + d.id + '">' + d.vehical + '</option>');
                    });

                    console.log('Dropdown options:', opts);
                    console.log('SelectedId:', selectedId);

                    if (selectedId) {
                        $vehical.val(String(selectedId)).trigger('change');
                        console.log('Set value:', $vehical.val());
                    }
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
        // $('#logistics .child_menu').show();
        $('#logistics').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.own_vehicle').addClass('active_cc');
        // $('#logistics').addClass('nv active-color');
    });
</script>