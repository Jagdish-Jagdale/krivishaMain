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

    #imageGallery {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    #imageGallery>a {
        flex: 1 1 calc(33.333% - 10px);
        box-sizing: border-box;
        height: 300px;
    }

    #imageGallery img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }

</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css" />
<div class="right_col">
    <h3>Production Report List</h3>
    <div class="main_page">
        <div class="page_title">
        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="row flex_wrap">


                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12  d-3 mb-3 form-group">
                            <label>Date Range</label>
                            <input name="date" id="date" class="form-control"
                                placeholder="Select Date Range" value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                    // Display the selected date range from the URL
                                    echo $_GET['date'];
                                } ?>">
                        </div>

                        <!-- <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
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
                        </div> -->

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group">
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

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group">
                            <label for="plant">Raw Material</label>
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


                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 type-group">

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
                        <a href="<?= base_url() ?>production_report_list" class="btn btn-sm btn-danger"
                            id="reset_btn">Reset</a>
                    </div>
                    </div>
                </form>
            </div>

        </div>


        <div class="x_panel">
            <table class="table" style="width: 100%;" id="dataTable">
                <thead>
                    <tr>
                        <th>SR. NO.</th>
                        <th>Production Date</th>
                        <th>Dispatched Date</th>
                        <th>Operator</th>
                        <!-- <th>Supervisor name</th> -->
                        <th>Machine</th>
                        <th>Group of Article</th>
                        <th>Article Names / Mould</th>
                        <th>Raw Materials</th>
                        <th>Master Batch</th>
                        <th>Rejection</th>
                        <th>Uploaded Pictures</th>
                        <th>Remark</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="viewImagesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <div id="imageGallery"></div>
                    <div id="loadingSpinner" style="display: none;">Loading...</div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add_remark" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Enter Your Text</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="modalForm">
                        <div class="mb-3">
                            <input type="hidden" name="production_id" id="production_id">
                            <textarea id="remark_text" name="remark" class="form-control" rows="4"
                                placeholder="Type here..."></textarea>
                        </div>
                        <button type="button" onclick="add_remark()" id="submitForm"
                            class="btn btn-success">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="pending_approved" id="pending_approved" value="<?php if (isset($_GET['pending_approved'])) {
    echo $_GET['pending_approved'];
} ?>">
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

<!-- Operator Selection Modal -->
<div class="modal fade" id="operator_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #0056d0 0%, #003d96 100%); color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title"><i class="fa fa-users-cog mr-2"></i> Assign Shift Operators</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="current_prod_id">
                
                <h6 style="color: #0056d0; font-weight: 700; border-bottom: 2px solid #eef2f7; padding-bottom: 8px;">Day Shift</h6>
                <div class="form-group mb-4">
                    <label class="form-label small text-muted">Select Operators</label>
                    <select id="modal_day_operators" class="operator-select2" multiple="multiple" style="width: 100%; display: none;">
                        <?php foreach($operators as $op): ?>
                            <option value="<?= $op->id ?>"><?= $op->first_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h6 style="color: #d00056; font-weight: 700; border-bottom: 2px solid #eef2f7; padding-bottom: 8px;">Night Shift</h6>
                <div class="form-group mb-2">
                    <label class="form-label small text-muted">Select Operators</label>
                    <select id="modal_night_operators" class="operator-select2" multiple="multiple" style="width: 100%; display: none;">
                        <?php foreach($operators as $op): ?>
                            <option value="<?= $op->id ?>"><?= $op->first_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8f9fa; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" onclick="save_operator_selection()" style="border-radius: 8px; background: #0056d0;">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
<!-- LightGallery JS -->
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/thumbnail/lg-thumbnail.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.min.js"></script>

<script>
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            placeholder: "Select..."
        });

        // Initialize Select2 for modal with dropdown parent for correct rendering inside modal
        $('.operator-select2').select2({
            dropdownParent: $('#operator_modal'),
            placeholder: "Select operators...",
            closeOnSelect: false
        });
        
        $('#product_master').addClass('nv active');
        $('.production_report_list').addClass('active_cc');
    });

    $(document).on('click', '#imageGallery img', function () {
        $('#viewImagesModal').modal('hide');
    });

    function show_operator_modal(prod_id) {
        $('#current_prod_id').val(prod_id);
        
        var day_csv = $('#day_operators_' + prod_id).val() || '';
        var night_csv = $('#night_operators_' + prod_id).val() || '';
        
        var day_ids = day_csv ? day_csv.split(',') : [];
        var night_ids = night_csv ? night_csv.split(',') : [];
        
        // Store the already-saved IDs so we can lock them
        $('#modal_day_operators').data('saved_ids', day_ids);
        $('#modal_night_operators').data('saved_ids', night_ids);

        $('#modal_day_operators').val(day_ids).trigger('change');
        $('#modal_night_operators').val(night_ids).trigger('change');

        // Lock the × button on already-saved tags
        lockSavedTags('#modal_day_operators', day_ids);
        lockSavedTags('#modal_night_operators', night_ids);

        $('#operator_modal').modal('show');
    }

    // Hide the × (remove) button on tags that were already saved
    function lockSavedTags(selectId, savedIds) {
        if (!savedIds || savedIds.length === 0) return;
        var $container = $(selectId).next('.select2-container');
        $container.find('.select2-selection__choice').each(function () {
            var $tag = $(this);
            var tagVal = $tag.data('value') ? String($tag.data('value')) : '';
            // Also try reading from the rendered title attribute
            if (!tagVal) {
                tagVal = $tag.attr('title') || '';
            }
            // Match by value stored in data-value or by iterating option text
            var isSaved = savedIds.some(function (id) { return String(id) === tagVal; });
            if (!isSaved) {
                // Try matching by option text (Select2 sometimes stores text in title)
                var optionText = $tag.find('.select2-selection__choice__display').text().trim();
                isSaved = savedIds.some(function (id) {
                    var optText = $(selectId + ' option[value="' + id + '"]').text().trim();
                    return optText === optionText;
                });
            }
            if (isSaved) {
                $tag.find('.select2-selection__choice__remove').hide();
                $tag.css('opacity', '1');
            }
        });
    }

    // Prevent removal of already-saved operators
    $(document).on('select2:unselecting', '#modal_day_operators, #modal_night_operators', function (e) {
        var savedIds = $(this).data('saved_ids') || [];
        var removingId = String(e.params.args.data.id);
        if (savedIds.some(function (id) { return String(id) === removingId; })) {
            e.preventDefault();
        }
    });

    // Re-lock tags after modal is fully shown (Select2 may render after trigger)
    $('#operator_modal').on('shown.bs.modal', function () {
        var day_ids = $('#modal_day_operators').data('saved_ids') || [];
        var night_ids = $('#modal_night_operators').data('saved_ids') || [];
        lockSavedTags('#modal_day_operators', day_ids);
        lockSavedTags('#modal_night_operators', night_ids);
    });

    // Re-lock after any selection change (adding new operators)
    $(document).on('select2:select', '#modal_day_operators', function () {
        lockSavedTags('#modal_day_operators', $(this).data('saved_ids') || []);
    });
    $(document).on('select2:select', '#modal_night_operators', function () {
        lockSavedTags('#modal_night_operators', $(this).data('saved_ids') || []);
    });

    function save_operator_selection() {
        var prod_id = $('#current_prod_id').val();
        var day_operators = $('#modal_day_operators').val(); // Array
        var night_operators = $('#modal_night_operators').val(); // Array
        
        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_production_operators",
            type: "POST",
            data: {
                production_id: prod_id,
                day_operators: day_operators,
                night_operators: night_operators
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    var day_csv = day_operators ? day_operators.join(',') : '';
                    var night_csv = night_operators ? night_operators.join(',') : '';
                    
                    $('#day_operators_' + prod_id).val(day_csv);
                    $('#night_operators_' + prod_id).val(night_csv);
                    
                    var display = [];
                    if(day_operators && day_operators.length > 0) display.push('(D' + day_operators.length + ')');
                    if(night_operators && night_operators.length > 0) display.push('(N' + night_operators.length + ')');
                    if(display.length === 0) display.push('-');
                    
                    $('#op_display_' + prod_id).text(display.join(' '));
                    $('#operator_modal').modal('hide');
                }
            }
        });
    }
</script>

<script>
    var productionReportTable = null;

    $(document).ready(function () {
        productionReportTable = $('#dataTable').DataTable({

            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],

            layout: {
                topStart: 'pageLength',
                topStart: 'buttons',
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            },

            dom: "Blfrtip",
            ordering: false,

                buttons: [{
                extend: 'excel',
                title: 'Production Report List',
                footer: true,
                filename: 'Production Report List',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                }
            }],
            scrollX: true,

            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_production_report_list",
                "type": "POST",
                "data": function (d) {
                    d.search_date = $('#search_date').val();
                    d.pending_approved = $('#pending_approved').val();
                    d.raw_material_id = $('#search_material_id').val();
                    d.master_batch_id = $('#master_batch_id').val();
                    d.article_id = $('#article_id').val();
                }
            },

            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });

    function get_all_production_report_list() {
        if (productionReportTable) {
            productionReportTable.ajax.reload();
        }
    }

    function get_all_production_images(production_id) {
        if (!production_id) {
            alert("No production ID provided");
            return;
        }
        $('#loadingSpinner').show();
        $('#imageGallery').empty();

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/get_production_images",
            type: "POST",
            data: { production_id },
            success: function (response) {
                $('#loadingSpinner').hide();
                let json;
                try {
                    json = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    $('#imageGallery').html('<p class="text-danger">Invalid server response.</p>');
                    return $('#viewImagesModal').modal('show');
                }

                if (json.status === "success" && json.data.length) {
                    const galleryHtml = json.data.map(img =>
                        `<a href="<?= base_url('assets/images/production/') ?>${img.image_names}">
                        <img src="<?= base_url('assets/images/production/') ?>${img.image_names}" />
                    </a>`
                    ).join('');

                    $('#imageGallery').html(galleryHtml);

                    // Destroy previous gallery instance if already loaded
                    if (window.lgInstance) {
                        window.lgInstance.destroy();
                    }

                    // Reinitialize LightGallery
                    window.lgInstance = lightGallery(document.getElementById('imageGallery'), {
                        thumbnail: true,
                        zoom: true,
                        fullScreen: true
                    });

                } else {
                    $('#imageGallery').html('<p>No images found for this production.</p>');
                }

                $('#viewImagesModal').modal('show');
            },
            error: function (xhr, status, error) {
                $('#loadingSpinner').hide();
                console.error("AJAX Error:", status, error);
                $('#imageGallery').html('<p class="text-danger">Failed to load images.</p>');
                $('#viewImagesModal').modal('show');
            }
        });

    }
    function add_remark_modal(id) {
        $('#production_id').val(id);
        $('#add_remark').modal("show");
    }

    function add_remark() {
        remark = $('#remark_text').val();
        production_id = $('#production_id').val();

        $.ajax({
            url: "<?= base_url() ?>admin/Ajax_controller/set_remark",
            type: "POST",
            data: {
                'id': production_id,
                'remark': remark
            },
            beforeSend: function () {
            },
            success: function (response) {
                response = JSON.parse(response);
                if (response.status === "success") {
                    $('#add_remark').modal("hide");
                    $('#dataTable').DataTable().ajax.reload(null, false);
                } else {
                    $('#add_remark').modal("hide");
                }
            },
            error: function (xhr, status, error) {
            }
        });
    }
</script>
<script>
$(document).ready(function() {
    

    flatpickr("#date", {
        mode: "range",
        dateFormat: "d-m-Y",
        
    });
});
</script>