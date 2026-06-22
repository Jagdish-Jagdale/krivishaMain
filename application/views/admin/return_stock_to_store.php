<?php include('header.php'); ?>
<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    .nothing-ready {
        background-color: #FF6B6B;
        color: white;
        padding: 11px;
        border-radius: 16px;
        text-align: center;
        margin: 7px;
        display: inline-block;
    }

    .partially-ready {
        background-color: #FFC107;
        color: white;
        padding: 11px;
        border-radius: 16px;
        text-align: center;
        margin: 7px;
        display: inline-block;
    }

    .color_status {
        padding-top: 10px !important;
    }

    .fully-ready {
        background-color: #4CAF50;
        color: white;
        padding: 11px;
        border-radius: 16px;
        text-align: center;
        margin: 7px;
        display: inline-block;
    }
</style>
<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Return Stock To Store</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 col-md-4">
                            <label for="plant">Plant<b class="require">*</b></label>
                            <select class="form-control" name="plant_id" id="plant_id"
                                onchange="getDataIfallSelected()">
                                <option value="">Please select plant</option>
                                <?php if (!empty($plant)) {
                                    foreach ($plant as $plant_result) { ?>
                                        <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>>
                                            <?= $plant_result->plant_name ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                            <label id="plant_id-error" class="error" for="plant_id" style="display:none"></label>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label for="machine">Machine<b class="require">*</b></label>
                            <select class="form-control" name="machine_id" id="machine_id"
                                onchange="getDataIfallSelected()">
                                <option value="">Select Machine</option>
                                <?php if (!empty($machine)) { ?>
                                    <option value="all">All Machines</option>
                                    <?php foreach ($machine as $machine_result) { ?>
                                        <option value="<?= $machine_result->id ?>" <?php if (!empty($single) && $single->machine_id == $machine_result->id) { ?>selected<?php } ?>>
                                            <?= $machine_result->machine_name ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                <label for="date_picker">Select Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Select Date"
                                    onchange="getDataIfallSelected()" name="filter_date" id="filter_date">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Stock Details</h3>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container" id="rm_container">
                    <label class="error">Please select Plant, Machine, Date</label>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#stock_management').addClass('nv active');
        $('.return_stock').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function () {
        flatpickr("#filter_date", {
            dateFormat: "d-m-Y",
            autoclose: true
        });
        $('#plant_id').change(function () {
            var plant_id = $(this).val();
            $('#machine_id').empty();
            if (plant_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_all_machines',
                    type: 'POST',
                    data: {
                        plant_id: plant_id
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#machine_id').append('<option value="">Select Machine</option>');
                        $('#machine_id').append('<option value="all">All Machines</option>');
                        $.each(data, function (index, machine) {
                            $('#machine_id').append('<option value="' + machine.id + '">' + machine.machine_name + '</option>');
                        });
                    },
                    error: function () {
                        alert('Error retrieving machines. Please try again.');
                    }
                });
            } else {
                $('#machine_id').append('<option value="">Select Machine</option>');
            }
        });
    });
</script>
<script>
    function getDataIfallSelected() {
        var plantId = $('#plant_id').val();
        var machineId = $('#machine_id').val();
        var filterDate = $('#filter_date').val().split('-').reverse().join('-');
        //alert(machineId);

        if (plantId !== '' && machineId !== '' && filterDate !== '') {
            $.ajax({
                type: "POST",
                url: '<?= base_url("admin/Ajax_controller/get_return_stock_production_schedule_data") ?>',
                data: {
                    'plant_id': plantId,
                    'machine_id': machineId,
                    'filter_date': filterDate
                },
                success: function (res) {
                    // console.log(res);
                    $('#rm_container').empty().html(res);
                },
                error: function () {
                    alert("Something went wrong while fetching schedule data.");
                }
            });
        }
    }
    // function validateMax(input) {
    //     input.value = input.value.replace(/[^0-9]/g, '');
    //     const max = parseFloat(input.getAttribute("max")) || 0;
    //     const value = parseFloat(input.value) || 0;

    //     if (value > max) {
    //         input.value = max;
    //     }
    // }
    function validateMax(input) {
        // allow numbers + dot
        // input.value = input.value.replace(/[^0-9.]/g, '');

        // allow only one dot
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.slice(0, -1);
        }

        // prevent value above max
        const max = parseFloat(input.max);
        const value = parseFloat(input.value);
        if (!isNaN(max) && !isNaN(value) && value > max) {
            input.value = max;
        }
    }
    function formatDecimals(input) {
        if (input.value !== '') {
            input.value = parseFloat(input.value).toFixed(2);
        }
    }
    function setRMStatuFOrReturn(type, schedule_id, item_id, total_qty_div_id, qty_div_id, action_div_id, return_qty_div_id) {
        var return_qty_str = $(return_qty_div_id).val().trim();
        if (return_qty_str === '' || isNaN(return_qty_str)) {
            alert('Please enter a valid return quantity.');
            return;
        }
        if (!confirm("Are you sure you want to return this qty to store?")) {
            return;
        }
        var total_qty = $(total_qty_div_id).val();
        var qty = $(qty_div_id).val();
        var plant_id = $('#plant_id').val();

        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/set_production_bom_status_ajx',
            type: 'POST',
            data: {
                'item_type': type,
                'schedule_id': schedule_id,
                'item_id': item_id,
                'total_qty': total_qty,
                'qty': qty,
                'return_qty': return_qty_str,
                'plant_id': plant_id
            },
            success: function (data) {
                if (data == '1') {
                    $(return_qty_div_id).attr('readonly', true);
                    $(action_div_id).empty();
                    alert('Stock returned to store successfully.');

                } else {
                    alert('Something went wrong, please try again.');
                }
            },
            error: function () {
                alert('Something went wrong, please try again.');
            }
        });
    }


</script>