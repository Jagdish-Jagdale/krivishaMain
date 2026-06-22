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
        color: #000;
    }

    .dataTables_length {
        margin: 0 !important;
        font-size: 13px;
        color: #000;
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        float: none !important;
        clear: both !important;
        padding: 0 !important;
    }

    .dataTables_length label {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        white-space: nowrap !important;
        font-weight: normal !important;
        gap: 5px !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 30px !important;
        line-height: 30px !important;
    }

    .dataTables_length select {
        padding: 4px 8px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        margin: 0 5px !important;
        display: inline-block !important;
        width: auto !important;
        height: 30px !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .top {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-bottom: 10px !important;
        width: 100% !important;
        height: 30px !important;
    }

    .top .dataTables_length,
    .top .dataTables_filter {
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        height: 30px !important;
    }

    .dataTables_filter {
        margin: 0 !important;
        font-size: 13px;
        color: #000;
        display: inline-flex !important;
        align-items: center !important;
        width: auto !important;
        float: none !important;
        clear: both !important;
        padding: 0 !important;
    }

    .dataTables_filter label {
        display: inline-flex !important;
        flex-direction: row !important;
        align-items: center !important;
        white-space: nowrap !important;
        font-weight: normal !important;
        gap: 5px !important;
        margin: 0 !important;
        padding: 0 !important;
        height: 30px !important;
        line-height: 30px !important;
    }

    .dataTables_filter input {
        padding: 4px 8px !important;
        border: 1px solid #ccc !important;
        border-radius: 4px !important;
        margin: 0 0 0 5px !important;
        color: #000 !important;
        width: 180px !important;
        height: 30px !important;
        display: inline-block !important;
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .report-section table, .page_body table {
        margin-top: 0 !important;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    h3 {
        margin: 9px 0;
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        background: #0056d0;
        color: #fff !important;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        text-align: left;
        letter-spacing: 0.5px;
    }

    .table thead th {
        background: #eaf2ff !important;
        color: #333;
        font-weight: 700;
        font-size: 12px;
        padding: 10px 8px;
        border: 1px solid #d1d9e6 !important;
        text-align: center;
    }

    .table td {
        border: 1px solid #d1d9e6 !important;
        padding: 8px;
        color: #000;
        font-size: 12px;
    }

    /* Removed old dataTables_length styles to use flex order above */



    .card-stat {
        background-color: #f8fbff;
        border: 1px solid #d7dde5;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;    
        text-align: center;
    }

    .card-stat-label {
        font-weight: 700;
        color: #5a6472;
        font-size: 11px;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .card-stat-value {
        font-size: 22px;
        font-weight: 800;
        color: #000;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .modal-lg {
        max-width: 1000px;
    }
</style>

<div class="right_col">
    <h3>Transport Report</h3>
    <div class="main_page">
        <div class="page_body">
            <!-- CARD STATISTICS SECTION -->
            <div class="page_sec">
                <div class="card-grid">
                    <div class="card-stat">
                        <div class="card-stat-label">Total Invoice Value</div>
                        <div class="card-stat-value" id="total_invoice_value">₹0.00</div>
                    </div>
                    <div class="card-stat">
                        <div class="card-stat-label">Total Freight Value</div>
                        <div class="card-stat-value" id="total_freight_value">₹0.00</div>
                    </div>
                    <div class="card-stat">
                        <div class="card-stat-label">Freight %</div>
                        <div class="card-stat-value" id="freight_percentage">0%</div>
                    </div>
                    <div class="card-stat">
                        <div class="card-stat-label">Own Vehicle KM Running</div>
                        <div class="card-stat-value" id="own_vehicle_km">0 KM</div>
                    </div>
                    <div class="card-stat">
                        <div class="card-stat-label">Distance Covered</div>
                        <div class="card-stat-value" id="distance_covered">0 KM</div>
                    </div>
                    <div class="card-stat">
                        <div class="card-stat-label">Total Expenses</div>
                        <div class="card-stat-value" id="total_expenses">₹0.00</div>
                    </div>
                </div>
            </div>

            <!-- FILTERS SECTION -->
            <div class="page_sec">
                <form method="get" name="transport_report_form" id="transport_report_form" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                            echo $_GET['date'];
                                        } ?>">
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Party Name</label>
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
                            <label>Division</label>
                            <select class="form-control js-example-basic-multiple" name="division" id="division">
                                <option value="" selected disabled>Select Division</option>
                                <option value="1" <?php if (isset($_GET['division']) && $_GET['division'] == '1') { ?>selected="selected" <?php } ?>>Household</option>
                                <option value="2" <?php if (isset($_GET['division']) && $_GET['division'] == '2') { ?>selected="selected" <?php } ?>>Container</option>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Location</label>
                            <select class="form-control js-example-basic-multiple" name="location" id="location">
                                <option value="" selected disabled>Select Location</option>
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Transporter Name</label>
                            <select class="form-control js-example-basic-multiple" name="transporter" id="transporter">
                                <option value="" selected disabled>Select Transporter</option>
                                <?php if (!empty($transport)) : ?>
                                    <?php foreach ($transport as $transport_result) : ?>
                                        <option value="<?= $transport_result->id ?>"
                                            <?= (isset($_GET['transporter']) && $_GET['transporter'] == $transport_result->id) ? 'selected' : '' ?>>
                                            <?= $transport_result->transport_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Freight</label>
                            <select class="form-control js-example-basic-multiple" name="freight_status" id="freight_status">
                                <option value="" selected disabled>Select Freight Status</option>
                                <option value="1" <?php if (isset($_GET['freight_status']) && $_GET['freight_status'] == '1') { ?>selected="selected" <?php } ?>>Pay</option>
                                <option value="2" <?php if (isset($_GET['freight_status']) && $_GET['freight_status'] == '2') { ?>selected="selected" <?php } ?>>Paid</option>
                                <option value="3" <?php if (isset($_GET['freight_status']) && $_GET['freight_status'] == '3') { ?>selected="selected" <?php } ?>>To Pay</option>
                            </select>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns">
                            <button id="search_btn" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>transport_report" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                            <button type="button" class="btn btn-sm btn-info ml-2" id="download_pdf_btn">Download PDF</button>
                            <button type="button" class="btn btn-sm btn-success ml-2" id="print_report_btn">Print</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLE SECTION -->
            <div class="x_panel">
                <table style="width: 100%;" class="table table-striped table-bordered" id="transport_table">
                    <thead class="thead" style="background:#e8f0fd; color:#0056d0;">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Dispatch Date</th>
                            <th>Order ID</th>
                            <th>Party Name</th>
                            <th>Location</th>
                            <th>Transport Name</th>
                            <th>Total Bundle</th>
                            <th>Vehicle Type</th>
                            <th>Freight Amount</th>
                            <th>Driver No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
<input type="hidden" name="location_action" id="location_action" value="<?php if (isset($_GET['location'])) {
                                                                            echo $_GET['location'];
                                                                        } ?>">
<input type="hidden" name="transporter_action" id="transporter_action" value="<?php if (isset($_GET['transporter'])) {
                                                                                echo $_GET['transporter'];
                                                                            } ?>">
<input type="hidden" name="freight_status_action" id="freight_status_action" value="<?php if (isset($_GET['freight_status'])) {
                                                                                        echo $_GET['freight_status'];
                                                                                    } ?>">
<input type="hidden" name="division_action" id="division_action" value="<?php if (isset($_GET['division'])) {
                                                                            echo $_GET['division'];
                                                                        } ?>">

<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#logistics').addClass('nv active');
        $('.transport_report').addClass('active_cc');
    });
</script>

<script>
    flatpickr("#date", {
        dateFormat: "d-m-Y",
        mode: "range",
        allowInput: true,
        placeholder: "Select Date Range"
    });

    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({});
    });
</script>

<script>
    $(document).ready(function() {
        var table = $('#transport_table').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "scrollX": true,
            "lengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
            dom: '<"top"lf>rt<"bottom"ip><"clear">',

            "initComplete": function(settings, json) {
                var $wrapper = $(this).closest('.dataTables_wrapper');
                var $top = $wrapper.find('.top');
                var $parent = $wrapper.parent();
                var $heading = $parent.find('h3, .section-head, h2, .page-h3').first();
                if ($top.length && $heading.length) {
                    $top.insertBefore($heading);
                }
            },
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'transport_report',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                }
            }],
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6 },
                { data: 7 },
                { data: 8 },
                { data: 9 },
                { data: 10, orderable: false, searchable: false }
            ],
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'
            }],
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_transport_report_data",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#date').val();
                    data.party_action = $('#party').val();
                    data.location_action = $('#location').val();
                    data.transporter_action = $('#transporter').val();
                    data.freight_status_action = $('#freight_status').val();
                    data.division_action = $('#division').val();
                },
                "error": function(xhr, status, error) {
                    console.error('AJAX Error:', {status: status, error: error, response: xhr.responseText});
                    alert('Error loading data: ' + error + '\nResponse: ' + xhr.responseText.substring(0, 200));
                },
                "dataSrc": function(json) {
                    console.log('AJAX Response:', {
                        draw: json.draw,
                        recordsTotal: json.recordsTotal,
                        recordsFiltered: json.recordsFiltered,
                        dataCount: (json.data || []).length,
                        data: json.data
                    });
                    if (json.error) {
                        alert('Data Error: ' + json.error);
                        return [];
                    }
                    return json.data || [];
                }
            },
            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
                updateCardStatistics();
            }
        });

        // Search form submit - reload DataTable with current filter values
        $('#transport_report_form').on('submit', function(e) {
            e.preventDefault();
            $('#search_date').val($('#date').val());
            $('#party_action').val($('#party').val());
            $('#location_action').val($('#location').val());
            $('#transporter_action').val($('#transporter').val());
            $('#freight_status_action').val($('#freight_status').val());
            $('#division_action').val($('#division').val());

            table.ajax.reload();
            updateCardStatistics();
        });
    });

    function updateCardStatistics() {
        // This function will be called to update the card statistics
        // You can make an AJAX call to get the aggregated data
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_transport_report_summary") ?>',
            type: 'POST',
            data: {
                'search_date': $('#date').val(),
                'party_action': $('#party').val(),
                'location_action': $('#location').val(),
                'transporter_action': $('#transporter').val(),
                'freight_status_action': $('#freight_status').val(),
                'division_action': $('#division').val(),
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#total_invoice_value').text('₹' + (parseFloat(response.total_invoice_value) || 0).toFixed(2));
                    $('#total_freight_value').text('₹' + (parseFloat(response.total_freight_value) || 0).toFixed(2));
                    $('#freight_percentage').text((parseFloat(response.freight_percentage) || 0).toFixed(2) + '%');
                    $('#own_vehicle_km').text((parseFloat(response.own_vehicle_km) || 0).toFixed(0) + ' KM');
                    $('#distance_covered').text((parseFloat(response.distance_covered) || 0).toFixed(0) + ' KM');
                    $('#total_expenses').text('₹' + (parseFloat(response.total_expenses) || 0).toFixed(2));
                }
            }
        });
    }

    // Call updateCardStatistics on page load
    $(document).ready(function() {
        updateCardStatistics();
    });

    // Search form submit is handled inside document.ready above

    // Re-bind safely (in case of partial reload)
    $('#print_report_btn').off('click').on('click', function() {
        var printContents = document.getElementById('transport_table').outerHTML;
        var summaryContents = $('.page_sec').first().html(); // Get summary cards
        var originalContents = document.body.innerHTML;
        
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Transport Report</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">');
        printWindow.document.write('<style>body { font-family: Arial, sans-serif; padding: 20px; color: #000; } table { width: 100%; border-collapse: collapse; margin-top: 0 !important; } th, td { border: 1px solid #d1d9e6; padding: 8px; text-align: left; font-size: 10px; color: #000; } th { background-color: #eef2f7 !important; color: #333 !important; font-weight: bold; -webkit-print-color-adjust: exact; } h3 { font-size: 15px; margin: 20px 0 0; text-transform: uppercase; background: #0056d0; color: #fff !important; padding: 10px 15px; font-weight: 800; text-align: left; border-radius: 4px 4px 0 0; -webkit-print-color-adjust: exact; } .card-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; } .card-stat { border: 1px solid #d7dde5; background: #f8fbff !important; padding: 10px; text-align: center; -webkit-print-color-adjust: exact; color: #000; } .card-stat-label { font-size: 9px; color: #5a6472; font-weight: 700; text-transform: uppercase; } .card-stat-value { font-size: 14px; font-weight: bold; color: #000; } .no-print { display: none; } </style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h3 class="text-center mb-4">Transport Report</h3>');
        printWindow.document.write('<div class="container-fluid">' + summaryContents + '</div>');
        printWindow.document.write(printContents);
        // Remove the Action column from the printed table
        $(printWindow.document).find('th:last-child, td:last-child').remove();
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    });

    // PDF functionality (triggers print dialog which allows "Save as PDF")
    $('#download_pdf_btn').on('click', function() {
        $('#print_report_btn').trigger('click');
    });
</script>
