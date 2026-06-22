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

    .modelclass {
        max-width: 40%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
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
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Raw Material Inward List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
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
                            <label for="brand">Supplier Name</label>
                            <select class="form-control js-example-basic-multiple" name="party" id="party">
                                <option value="" selected disabled>Select Supplier</option>
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
                            <div class="form-group">
                                <label>Plant Name</label>
                                <select class="form-control js-example-basic-multiple" name="plant" id="plant">
                                    <?php if (!empty($plant)) : ?>
                                        <option value="" selected disabled>Select Plant</option>
                                        <?php foreach ($plant as $plant_result) : ?>
                                            <option value="<?= $plant_result->id ?>"
                                                <?= (isset($_GET['plant']) && $_GET['plant'] == $plant_result->id) ? 'selected' : '' ?>>
                                                <?= $plant_result->plant_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>rm_inward_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Inward Number</th>
                                    <th>Inward Date & Time</th>
                                    <th>Supplier Name</th>
                                    <th>Plant Name</th>
                                    <th>Gate Entry No.</th>
                                    <th>Gate Entry Date</th>
                                    <th>Extra Charges Details</th>
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
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Inward Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Inward Number</th>
                            <th>Extra Charges For</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-table">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
<input type="hidden" name="plant_id" id="plant_id" value="<?php if (isset($_GET['plant'])) {
                                                                echo $_GET['plant'];
                                                            } ?>">
<?php include('footer.php');
?>

<script>
        $(document).ready(function() {
        
        $('#stock_management').addClass('nv active');
       
        $('.rm_inward_list').addClass('active_cc');
       
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
            "lengthChange": true,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            // "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Inward Form List',
                filename: 'inward_form_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_inward_form_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.party_action = $('#party_action').val();
                    data.plant_id = $('#plant_id').val();
                    data.inward_for = '0'; //for Raw Material inward
                },
            },
            "createdRow": function(row, data, dataIndex) {
                var memberId = data[7];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${memberId}')" title="Extra Charges Details">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(7).html(eyeButton);

            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });


    });

    function showOrderDetails(memberId) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_extra_charges_details") ?>',
            type: 'POST',
            data: {
                'database_inward_id': memberId
            },
            dataType: 'json',
            success: function(response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#order-details-table').empty();
                    var tableContent = '';
                    response.forEach(function(item, index) {
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.inward_no}</td>`;
                        tableContent += `<td>${item.extra_payment_option}</td>`;
                        tableContent += `<td>${item.trap_hamali_amount}</td>`;
                        tableContent += `</tr>`;
                    });
                    $('#order-details-table').html(tableContent);

                    $('#exampleModal').modal('show');
                } else {
                    alert('No details found for this inward!');
                }

            },
        });
    }
</script>