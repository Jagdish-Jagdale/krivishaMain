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

.dataTables_length {
    margin: 10px 0;
    font-size: 13px;
    color: #000;
}

.dataTables_length select {
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 0 5px;
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
</style>
<div class="right_col">
    <h3>Re-Order Stock Report</h3>
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="type">Type</label>
                            <select class="form-control" name="type" id="type">
                                <option value="">Select Type</option>
                                <option value="0"
                                    <?= (isset($_GET['type']) && $_GET['type'] == '0') ? 'selected' : '' ?>>
                                    Article
                                </option>
                                <option value="1"
                                    <?= (isset($_GET['type']) && $_GET['type'] == '1') ? 'selected' : '' ?>>
                                    Material (Raw Material)
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

               
                    <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns ">
                        <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                        <a href="<?= base_url() ?>material_reorder_report" class="btn btn-sm btn-danger"
                            id="reset_btn">Reset</a>
                    </div>
                    </div>
                </form>
            </div>
            <h2 class="material_group">Raw Material Report</h2>
            <div class="x_panel material_group d-none">
                <table style="width: 100%;" class="table table-striped table-bordered material_list" id="example">
                    <thead class="thead" style="background:#e8f0fd; color:#0056d0;">
                        <tr>
                            <th>SR. NO.</th>
                            <!-- <th>Date</th> -->
                            <th>Plant</th>
                            <th>Material (Raw Material)</th>
                            <th>UOM</th>
                            <th>Re-Order Level</th>
                            <th>Available Quantity</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <h2 class="article_group">Article Report</h2>
            <div class="x_panel article_group d-none">
                <table style="width: 100%;" class="table table-striped table-bordered article_list" id="article_list">
                    <thead class="thead" style="background:#e8f0fd; color:#0056d0;">
                        <tr>
                            <th>SR. NO.</th>
                            <!-- <th>Date</th> -->
                            <th>Plant</th>
                            <th>Article Name</th>
                            <th>Re-Order Level</th>
                            <th>Available Finish Good</th>
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

<?php include('footer.php'); ?>


<script>
    $(document).ready(function () {
        $(".js-example-basic-multiple").select2({
            width: '100%'
        });
    });
$(document).ready(function() {
    function toggleTypeFields() {
        var selectedType = $('#type').val();
        $('.type-group').hide();
        if (selectedType === '0') {
            $('#article-div').show();
            $('.material_group').addClass('d-none');
            $('.article_group').removeClass('d-none');

        } else if (selectedType === '1') {
            $('#material-div').show();
            $('.article_group').addClass('d-none');
            $('.material_group').removeClass('d-none');
           
        }else{
            $('.material_group').removeClass('d-none');
            $('.article_group').removeClass('d-none');
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
    // $('#task_management .child_menu').show();
    $('#stock_report').addClass('nv active');
    // $('.right_col').addClass('active_right');
    $('.material_reorder_report').addClass('active_cc');
});
</script>

<!-- <script>
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
        mode: "range",  // Enable range selection
        dateFormat: "d-m-Y",  // Format: Day-Month-Year
        locale: 'en',  // Adjust to your desired locale
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                var formattedDate = selectedDates[0].toLocaleDateString('en-GB') + ' to ' + selectedDates[1].toLocaleDateString('en-GB');
                document.getElementById("date").value = formattedDate;
                submitForm();
            }
        },
    });
$(".datepickers").flatpickr({
    mode: "range",
    dateFormat: "d-m-Y",
});
$(".singledatepickers").flatpickr({
    dateFormat: "d-m-Y",
});
</script> -->
<script>
$(document).ready(function() {
    var table = $('#example').DataTable({
        'searching': true,
        "processing": true,
        "serverSide": true,
        "cache": false,
        dom: "Blfrtip",
        "initComplete": function(settings, json) {
            var $wrapper = $(this).closest('.dataTables_wrapper');
            var $length = $wrapper.find('.dataTables_length');
            var $heading = $wrapper.prev('h3, .section-head, h2');
            if (!$heading.length) {
                $heading = $wrapper.parent().prev('h3, .section-head, h2');
            }
            if (!$heading.length) {
                $heading = $wrapper.closest('.main_page, .page_body').find('h3, .section-head, h2').first();
            }
            if ($length.length && $heading.length) {
                $length.insertBefore($heading);
            }
        },
        ordering: false,
        scrollCollapse: true,
        buttons: [{
            extend: 'excel',
            footer: true,
            title: 'Re-Order Report List',
            filename: 'reorder_report_list',
            exportOptions: {
                columns: [0, 1, 2, 3, 4, 5]
            }
        }],

        "ajax": {
            "url": "<?= base_url() ?>admin/Ajax_controller/get_all_raw_material_reorder_level",
            "type": "POST",
            "data": function(data) {
                data.search_date = $('#search_date').val();
                data.plant_id = $('#search_plant_id').val();
                data.raw_material_id = $('#search_material_id').val();
                // data.supplier_name = $('#search_supplier_name').val();
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
        "initComplete": function(settings, json) {
            var $wrapper = $(this).closest('.dataTables_wrapper');
            var $length = $wrapper.find('.dataTables_length');
            var $heading = $wrapper.prev('h3, .section-head, h2');
            if (!$heading.length) {
                $heading = $wrapper.parent().prev('h3, .section-head, h2');
            }
            if (!$heading.length) {
                $heading = $wrapper.closest('.main_page, .page_body').find('h3, .section-head, h2').first();
            }
            if ($length.length && $heading.length) {
                $length.insertBefore($heading);
            }
        },
        ordering: false,
        scrollCollapse: true,
        buttons: [{
            extend: 'excel',
            footer: true,
            title: 'Re-Order Report List',
            filename: 'reorder_report_list',
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            }
        }],

        "ajax": {
            "url": "<?= base_url() ?>admin/Ajax_controller/get_all_article_reorder_level",
            "type": "POST",
            "data": function(data) {
                data.search_date = $('#search_date').val();
                data.plant_id = $('#search_plant_id').val();
                data.article_id = $('#article_id').val();
                // data.supplier_name = $('#search_supplier_name').val();
            },
        },


        "drawCallback": function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
});
</script>