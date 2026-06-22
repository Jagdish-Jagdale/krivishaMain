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

    .right_col .page_title,
    .right_col .page_body {
        padding: -1px 8px;
    }

    .page_sec {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        height: auto;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    .modelclass {
        max-width: 60%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">
<div class="right_col">
    <h3>Vehicle List Details</h3>
    <div class="main_page">
        <div class="page_title">

        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Party Name</label>
                            <select class="form-control js-example-basic-multiple" name="party" id="party">
                                <option value="" selected disabled>Select Party</option>
                                <?php if (!empty($party)) : ?>
                                    <?php foreach ($party as $party_result) : ?>
                                        <option value="<?= $party_result->id ?>"
                                            <?= (isset($_GET['party']) && $_GET['party'] == $party_result->id) ? 'selected' : '' ?>>
                                            <?= $party_result->party_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>


                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Transport</label>
                            <select class="form-control js-example-basic-multiple" name="vehical" id="vehical">
                                <option value="" selected disabled>Select vehical</option>
                                <?php if (!empty($vehical)) : ?>
                                    <?php foreach ($vehical as $vehical_result) : ?>
                                        <option value="<?= $vehical_result->id ?>"
                                            <?= (isset($_GET['vehical']) && $_GET['vehical'] == $vehical_result->id) ? 'selected' : '' ?>>
                                            <?= $vehical_result->vehical ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Location</label>
                            <select class="form-control js-example-basic-multiple" name="location" id="location">
                                <option value="" selected disabled>Select location</option>
                                <?php if (!empty($location)) : ?>
                                    <?php foreach ($location as $location_result) : ?>
                                        <option value="<?= $location_result->id ?>"
                                            <?= (isset($_GET['location']) && $_GET['location'] == $location_result->id) ? 'selected' : '' ?>>
                                            <?= $location_result->city ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="metrics-row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div class="metric-card" style="background-color: #e3f2fd; color: #0d47a1; padding: 15px; border-radius: 8px; flex: 1;">
                                <h3>Total Distance (KM)</h3>
                                <div class="metric-value"><?= number_format($metrics['exact_km']) ?></div>
                            </div>

                            <div class="metric-card" style="background-color: #f1f8e9; color: #33691e; padding: 15px; border-radius: 8px; flex: 1;">
                                <h3>Total Invoice Value</h3>
                                <div class="metric-value"><?= number_format($metrics['invoice_count']) ?></div>
                            </div>

                            <div class="metric-card" style="background-color: #fff3e0; color: #e65100; padding: 15px; border-radius: 8px; flex: 1;">
                                <h3>Total Freight Value</h3>
                                <div class="metric-value"><?= number_format($metrics['market_freight']) ?></div>
                            </div>

                            <div class="metric-card" style="background-color: #fce4ec; color: #880e4f; padding: 15px; border-radius: 8px; flex: 1;">
                                <h3>Trap %</h3>
                                <div class="metric-value"><?= number_format($metrics['transport_percent'], 2) ?>%</div>
                            </div>

                            <div class="metric-card" style="background-color: #ede7f6; color: #4527a0; padding: 15px; border-radius: 8px; flex: 1;">
                                <h3>Total Expenses</h3>
                                <div class="metric-value"><?= number_format($metrics['diesel_expense']) ?></div>
                            </div>
                        </div>



                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>own_vehicle_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table class="table" style="width: 100%;" id="example">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Vehicle No</th>
                            <th>Challan DC No</th>
                            <th>Invoice No</th>
                            <th>Invoice Value</th>
                            <th>Location</th>
                            <th>Pincode</th>
                            <th>Purpose</th>
                            <th>Party Name</th>
                            <th>In KM</th>
                            <th>Out KM</th>
                            <th>Exact KM</th>

                            <th>Market Freight</th>
                            <th>Diesel Top-up</th>
                            <th>Diesel Rate</th>

                            <th>Diesel Expense</th>
                            <th>Driver Expense</th>
                            <th>Maintenance</th>
                            <th>Transport %</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
                                                                    echo $_GET['date'];
                                                                } ?>">

<input type="hidden" name="party_action" id="party_action" value="<?php if (isset($_GET['party'])) {
                                                                        echo $_GET['party'];
                                                                    } ?>">
<input type="hidden" name="vehical_id" id="vehical_id" value="<?php if (isset($_GET['vehical'])) {
                                                                    echo $_GET['vehical'];
                                                                } ?>">
<input type="hidden" name="location_id" id="location_id" value="<?php if (isset($_GET['location'])) {
                                                                    echo $_GET['location'];
                                                                } ?>">
<?php include('footer.php'); ?>
<script>
    $(document).ready(function() {
        $('#logistics').addClass('nv active');
        $('.own_vehicle_list').addClass('active_cc');
    });
</script>
<script>
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });

    $(document).ready(function() {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            }
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                'DD-MM-YYYY'));
        });
        $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
    $(".datepickers").flatpickr({
        mode: "range",
        dateFormat: "d-m-Y",
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
    });
</script>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "scrollX": true,
            "cache": false,
            dom: "Blfrtip",
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'Vehical Detail List',
                title: 'Vehical Detail List',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18]
                }
            }],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width-100'

            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_vehical_list_details",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.party_action = $('#party_action').val();
                    data.vehical_id = $('#vehical_id').val();
                    data.location_id = $('#location_id').val();
                },
            },
            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>