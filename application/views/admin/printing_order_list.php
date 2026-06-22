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
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Printing Order List</h3>
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
                            <label for="brand">Brand Name</label>
                            <select class="form-control js-example-basic-multiple" name="brand" id="brand">
                                <option value="" selected disabled>Select Brand</option>
                                <?php if (!empty($brand)) : ?>
                                    <?php foreach ($brand as $brand_result) : ?>
                                        <option value="<?= $brand_result->id ?>"
                                            <?= (isset($_GET['brand']) && $_GET['brand'] == $brand_result->id) ? 'selected' : '' ?>>
                                            <?= $brand_result->brand_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="article">Bucket Size</label>
                            <select class="form-control js-example-basic-multiple" name="article" id="article">
                                <option value="" selected disabled>Select Bucket Size</option>
                                <?php if (!empty($article)) : ?>
                                    <?php foreach ($article as $article_result) : ?>
                                        <option value="<?= $article_result->id ?>"
                                            <?= (isset($_GET['article']) && $_GET['article'] == $article_result->id) ? 'selected' : '' ?>>
                                            <?= $article_result->article_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Order Status</label>
                                <select class="form-control js-example-basic-multiple" name="order_status" id="order_status">
                                    <option value="" selected disabled>Select Order Status</option>
                                    <option value="0" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '0') { ?>selected="selected" <?php } ?>>Pending
                                    </option>
                                    <option value="1" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '1') { ?>selected="selected" <?php } ?>>Printing Completed
                                    </option>
                                    <option value="2" <?php if (isset($_GET['order_status']) && $_GET['order_status'] == '2') { ?>selected="selected" <?php } ?>>Cancelled
                                    </option>
                                </select>
                            </div>
                        </div> -->


                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>printing_order_list" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
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
                                    <th>Order ID</th>
                                    <th>Order Date</th>
                                    <th>Forwarded Dated</th>
                                    <th>Party Name</th>
                                    <th>Type of article</th>
                                    <th>Brand name</th>
                                    <th>Ink</th>
                                    <th>Order Qty</th>
                                    <th>remark</th>
                                    <th>Last Remark</th>
                                    <th>Status</th>
                                    <th>Delay Day's</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
<input type="hidden" name="article_action" id="article_action" value="<?php if (isset($_GET['article'])) {
                                                                            echo $_GET['article'];
                                                                        } ?>">
<input type="hidden" name="order_status_action" id="order_status_action" value="<?php if (isset($_GET['order_status'])) {
                                                                                    echo $_GET['order_status'];
                                                                                } ?>">

<?php include('footer.php'); ?>
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
        // $('#printing_unit .child_menu').show();
        $('#printing_unit').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.printing_order_list').addClass('active_cc');
        // $('#printing_unit').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "scrollX": true,
            "cache": false,
            dom: "Blfrtip",
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            buttons: [{
                extend: 'excel',
                title: 'Printing Order List',
                footer: true,
                filename: 'printing_order_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                }
            }], 
            ordering: false,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_printing_order_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.brand_action = $('#brand_action').val();
                    data.article_action = $('#article_action').val();
                    data.order_status_action = $('#order_status_action').val();
                },
            },
        });
    });
</script>