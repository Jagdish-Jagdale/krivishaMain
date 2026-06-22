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
<div class="right_col">
    <h3>Total Stock List</h3>
    <div class="main_page">
        <div class="page_title">
        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">


                        <!-- <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label>Date Range</label>
                            <input name="date" id="date" class="form-control"
                                placeholder="Select Date Range" value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                                                            // Display the selected date range from the URL
                                                                            echo $_GET['date'];
                                                                        } ?>">
                        </div> -->
                        <?php if ($this->session->userdata('assign_department_id') == '25') { ?>
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
                        <?php } ?>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="type">Stock Type</label>
                            <select class="form-control" name="type" id="type">
                                <option value="">Select Type</option>
                                <option value="0"
                                    <?= (isset($_GET['type']) && $_GET['type'] == '0') ? 'selected' : '' ?>>
                                    Article (Mould)
                                </option>
                                <option value="1"
                                    <?= (isset($_GET['type']) && $_GET['type'] == '1') ? 'selected' : '' ?>>
                                    Material (Raw Material)
                                </option>
                                <option value="2"
                                    <?= (isset($_GET['type']) && $_GET['type'] == '2') ? 'selected' : '' ?>>
                                    Master Batch (Color)
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group"
                            id="article-div" style="display:none;">
                            <label for="article">Article</label>
                            <select class="form-control js-example-basic-multiple" name="article" id="article">
                                <option value="" selected disabled>Select Article</option>
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group"
                            id="material-div" style="display:none;">
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group"
                            id="master_batch_div" style="display:none;">
                            <label for="plant">Master Batch (Color)</label>
                            <select class="form-control js-example-basic-multiple" name="color_id" id="color_id">
                                <option value="">Select Master Batch (Color)</option>
                                <?php if (!empty($color)) : ?>
                                    <?php foreach ($color as $color_result) : ?>
                                        <option value="<?= $color_result->id ?>"
                                            <?= (isset($_GET['color_id']) && $_GET['color_id'] == $color_result->id) ? 'selected' : '' ?>>
                                            <?= $color_result->name ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>


                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                            <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                            <a href="<?= base_url() ?>total_stock_list" class="btn btn-sm btn-danger"
                                id="reset_btn">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            <h2 class="material_group">Raw Material</h2>
            <div class="x_panel material_group d-none">
                <table style="width: 100%;" class="table table-striped table-bordered material_list" id="example">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Plant Name</th>
                            <th>Raw Material</th>
                            <th>Unit</th>
                            <th>Total Stock</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <h2 class="master_group">Master Batch (Color)</h2>
            <div class="x_panel master_group d-none">
                <table style="width: 100%;" class="table table-striped table-bordered master_batch_list" id="mb_list">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Plant Name</th>
                            <th>Master Batch</th>
                            <th>Total Stock</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <h2 class="article_group">Article (Mould)</h2>
            <div class="x_panel article_group d-none">
                <table style="width: 100%;" class="table table-striped table-bordered article_list" id="article_list">
                    <thead class="thead">
                        <tr>
                            <th>SR. NO.</th>
                            <th>Plant Name</th>
                            <th>Article Name</th>
                            <th>Total Stock</th>
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
<input type="hidden" name="article_id" id="article_id" value="<?php if (isset($_GET['article'])) {
                                                                    echo $_GET['article'];
                                                                } ?>">
<input type="hidden" name="search_material_id" id="search_material_id" value="<?php if (isset($_GET['material_id'])) {
                                                                                    echo $_GET['material_id'];
                                                                                } ?>">

<input type="hidden" name="master_batch_id" id="master_batch_id" value="<?php if (isset($_GET['color_id'])) {
                                                                            echo $_GET['color_id'];
                                                                        } ?>">

<?php include('footer.php'); ?>

<script>
    $(document).ready(function () {
        $('#stock_management').addClass('nv active');
        $('.total_stock_list').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function() {
        function toggleTypeFields() {
            var selectedType = $('#type').val();
            $('.type-group').hide();
            if (selectedType === '0') {
                $('#article-div').show();
                $('.material_group').addClass('d-none');
                $('.master_group').addClass('d-none');
                $('.article_group').removeClass('d-none');

            } else if (selectedType === '1') {
                $('#material-div').show();
                $('.article_group').addClass('d-none');
                $('.master_group').addClass('d-none');
                $('.material_group').removeClass('d-none');

            } else if (selectedType === '2') {
                $('#master_batch_div').show();
                $('.article_group').addClass('d-none');
                $('.material_group').addClass('d-none');
                $('.master_group').removeClass('d-none');
            } else {
                $('.material_group').removeClass('d-none');
                $('.article_group').removeClass('d-none');
                $('.master_group').removeClass('d-none');
            }
        }
        $('#type').change(function() {
            toggleTypeFields();
        });
        toggleTypeFields();
    });
</script>

<script>
    $(document).ready(function() {

        $('.auto_task_list').addClass('active_cc');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
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
    flatpickr("#date", {
        mode: "range", // Enable range selection
        dateFormat: "d-m-Y", // Format: Day-Month-Year

    });
    $(".datepickers").flatpickr({
        mode: "range",
        dateFormat: "d-m-Y",
    });
    $(".singledatepickers").flatpickr({
        dateFormat: "d-m-Y",
    });
    $(document).ready(function() {
        $(".js-example-basic-multiple").select2({
            width: '100%'
        });
    });
</script>
<script>
    $(document).ready(function() {
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
                title: 'Total RM Stock List',
                filename: 'total_rm_stock_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_total_rm_stock_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.plant_id = $('#search_plant_id').val();
                    data.raw_material_id = $('#search_material_id').val();
                },
            },


            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        var table = $('#mb_list').DataTable({
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
                title: 'Total MB Stock List',
                filename: 'total_mb_stock_list',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_total_mb_stock_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.plant_id = $('#search_plant_id').val();
                    data.master_batch_id = $('#master_batch_id').val();
                },
            },


            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
        var table = $('#article_list').DataTable({
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
                title: 'Total Article Stock List',
                filename: 'total_article_stock_list',
                exportOptions: {
                    columns: [0, 1, 2, 3]
                }
            }],

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_total_article_stock_list",
                "type": "POST",
                "data": function(data) {
                    data.search_date = $('#search_date').val();
                    data.plant_id = $('#search_plant_id').val();
                    data.article_id = $('#article_id').val();
                },
            },
            "drawCallback": function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>