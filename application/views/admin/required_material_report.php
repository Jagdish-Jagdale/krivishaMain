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
<div class="right_col">
    <h3>Required Raw Material Stock Report</h3>
    <div class="main_page">
        <div class="page_title">
        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="plant">Plant</label>
                            <select class="form-control js-example-basic-multiple" name="plant" id="plant">
                                <option value="" selected disabled>Select Plant</option>
                                <?php if (!empty($plant)) : ?>
                                <?php foreach ($plant as $plant_result) : ?>
                                <option value="<?= $plant_result->id ?>"
                                    <?= (isset($_GET['plant']) && $_GET['plant'] == $plant_result->id) ? 'selected' : '' ?>>
                                    <?= $plant_result->plant_name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                    

                    
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group">
                            <label for="plant">Material (Raw Material)</label>
                            <select class="form-control js-example-basic-multiple" name="material_id" id="material_id">
                                <option value="">Select Material (Raw Material)</option>
                                <?php if (!empty($raw_material)) : ?>
                                <?php foreach ($raw_material as $raw_material_result) : ?>
                                <option value="<?= $raw_material_result->id ?>"
                                    <?= (isset($_GET['material_id']) && $_GET['material_id'] == $raw_material_result->id) ? 'selected' : '' ?>>
                                    <?= $raw_material_result->rm_name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                 
                    <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                        <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="<?= base_url() ?>required_material_report" class="btn btn-sm btn-danger"
                            id="reset_btn">Reset</a>
                    </div>
                    </div>
                </form>
            </div>
            <div class="x_panel">
                <table style="width: 100%;" class="table table-striped table-bordered material_list" id="example">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Plant</th>
                            <th>Material (Raw Material)</th>
                            <th>Required Quantity</th>
                            <th>Available Quantity</th>
                            <th>Need To Inward</th>
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

<input type="hidden" name="search_plant_id" id="search_plant_id" value="<?php if (isset($_GET['plant'])) {
    echo $_GET['plant'];
} ?>">

<input type="hidden" name="search_material_id" id="search_material_id" value="<?php if (isset($_GET['material_id'])) {
    echo $_GET['material_id'];
} ?>">

<?php include('footer.php'); ?>


<script>
$(document).ready(function() {
    $('#stock_report').addClass('nv active');
    $('.required_material_report').addClass('active_cc');
});
</script>

<script>
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({});
    });
    $(document).ready(function () {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            dom: "Blfrtip",
            ordering: false,
            scrollCollapse: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Required Stock Report List',
                filename: 'required_stock_report_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_required_stock_list_data",
                "type": "POST",
                "data": function (data) {
                    data.search_date = $('#search_date').val();
                    data.plant_id = $('#search_plant_id').val();
                    data.raw_material_id = $('#search_material_id').val();
                },
            },
            "drawCallback": function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

    });
</script>