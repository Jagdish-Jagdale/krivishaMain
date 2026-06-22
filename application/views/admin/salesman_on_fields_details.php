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
        width: 100% !important;
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


    .select2-container {
        width: 100% !important;
    }

    .modelclass {
        max-width: 60%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Salesman On Fields Details</h3>
            </div>

        </div>
        <div class="clearfix"></div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label>Date Range</label>
                            <input name="date" id="date" class="form-control datepickers" placeholder="Select Date"
                                value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                    echo $_GET['date'];
                                } ?>">
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="brand">Sales Officer</label>
                            <select class="form-control js-example-basic-multiple" name="brand" id="brand">
                                <option value="" selected disabled>Select Sales Officer</option>
                                <?php if (!empty($sales_officer)) : ?>
                                    <?php foreach ($sales_officer as $brand_result) : ?>
                                        <option value="<?= $brand_result->id ?>" 
                                                <?= (isset($_GET['brand']) && $_GET['brand'] == $brand_result->id) ? 'selected' : '' ?>>
                                            <?= $brand_result->first_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
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
                            <div class="form-group">
                                <label>Purpose Of Visit</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Purpose Of Visit</option>
                                    <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '1') { ?>selected="selected" <?php } ?>>Cold all- Introduction to our offerings
                                    </option>
                                    <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '2') { ?>selected="selected" <?php } ?>>Planned Relationship Meet
                                    </option>
                                    <option value="3" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '3') { ?>selected="selected" <?php } ?>>Order/ Payment Follow Up
                                    </option>
                                    <option value="4" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '4') { ?>selected="selected" <?php } ?>>Complaint Visit
                                    </option>
                                    <option value="5" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '5') { ?>selected="selected" <?php } ?>>Other: Marketing or Greetings Visit
                                    </option>
                                </select>
                            </div>
                        </div>

               
                    <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                        <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="<?= base_url() ?>salesman_on_fields_details" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                    </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <div class="x_content class_style">
                    <div class="container">
                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Visit Request ID</th>
                                    <th>Visit Date</th>
                                    <th>Sales Officer Name</th>
                                    <th>Party Name</th>
                                    <th>Customer Mo. No.</th>
                                    <th>City Name</th>
                                    <th>Pincode</th>
                                    <th>State</th>
                                    <th>Purpose Of Visit</th>
                                    <th>Visit Photo</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Customer Address</th>
                                    <th>Status Of Visit</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Meeting Proof</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 text-center">
        <img id="proofImage" src="" class="img-fluid w-100" alt="Meeting Proof">
      </div>
    </div>
  </div>
</div>
<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
    echo $_GET['date'];
} ?>">

<input type="hidden" name="brand_action" id="brand_action" value="<?php if (isset($_GET['brand'])) {
    echo $_GET['brand'];
} ?>">
<input type="hidden" name="party_action" id="party_action" value="<?php if (isset($_GET['party'])) {
    echo $_GET['party'];
} ?>">
<input type="hidden" name="order_status_action" id="order_status_action" value="<?php if (isset($_GET['order_status'])) {
    echo $_GET['order_status'];
} ?>">

<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });
    $(document).on('click', '.meeting-proof-thumb', function () {
        let imgSrc = $(this).data('img');
        $('#proofImage').attr('src', imgSrc);
        $('#proofModal').modal('show');
    });
    flatpickr("#date", {
        dateFormat: "d-m-Y",
    });
    
    $(".datepickers").flatpickr({
        mode: "range",
        dateFormat: "d-m-Y",
        defaultDate: new Date(),
    });
</script>

<script>
    $(document).ready(function () {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            dom: "Blfrtip",
            buttons: [
                {
                    extend: 'excel',
                    title: 'Salesman on Fields Details',
                    footer: true,
                    filename: 'salesman_on_fields_details',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6,7, 8,11,12,13,14]
                    }
                }
            ],
            ordering: false,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_salesman_on_fields_details",
                "type": "POST",
                "data": function (data) {
                    data.search_date = $('#search_date').val();
                    data.brand_action = $('#brand_action').val();
                    data.party_action = $('#party_action').val();
                    data.order_status_action = $('#order_status_action').val();
                },
            },
        });
    });

</script>