<?php include('header.php') ?>

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


    .multi-select {
        display: none;
    }

    #row_material {
        display: none;
    }
    .chosen-container-multi .chosen-choices li.search-choice .search-choice-close {
        position: absolute;
        top: 4px;
        right: 3px;
        display: block;
        width: 12px;
        height: 12px !important;
        background: url(chosen-sprite.png) -42px 1px no-repeat;
        font-size: 1px;
    }
</style>

<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Production Schedule </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">

            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_rm_form" id="add_rm_form" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <input type="hidden" id="schedule_id" name="schedule_id"
                                value="<?= $this->session->userdata('schedule_id'); ?>">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="plant">Plant Name<b class="require"></b></label>
                                <input type="text" class="form-control" name="plant" id="plant"
                                    value="<?= $this->session->userdata('plant_name'); ?>" readonly>

                                <input type="hidden" class="form-control" name="plant_id" id="plant_id"
                                    value="<?= $this->session->userdata('plant_id'); ?>">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="machine">Machine Name<b class="require"></b></label>
                                <input type="text" name="machine" class="form-control" id="machine"
                                    value="<?= $this->session->userdata('machine_name'); ?>" readonly>

                                <input type="hidden" name="machine_id" class="form-control" id="machine_id"
                                    value="<?= $this->session->userdata('machine_id'); ?>">

                                <input type="hidden" name="pro_scheduled_date" class="form-control"
                                    id="pro_scheduled_date"
                                    value="<?= $this->session->userdata('pro_scheduled_date'); ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="plant">Article Group<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="article_group_id" id="article_group_id">
                                    <option value="">Please article group</option>
                                    <?php if (!empty($article_group)) {
                                        foreach ($article_group as $article_group_result) { ?>
                                            <option value="<?= $article_group_result->id ?>" <?php if (!empty($single) && $single->article_group_id == $article_group_result->id) { ?>selected<?php } ?>>
                                                <?= $article_group_result->group_of_article ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label for="plant">Article Name<b class="require">*</b></label>
                                <select class="form-control js-example-basic-multiple" name="article_id" id="article_id">
                                    <option value="">Please select article name</option>
                                    <?php if (!empty($article)) {
                                        foreach ($article as $article_result) { ?>
                                            <option value="<?= $article_result->id ?>" <?php if (!empty($single) && $single->article_id == $article_result->id) { ?>selected<?php } ?>>
                                                <?= $article_result->article_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="qty">Qty<b class="require">*</b></label>
                                <input type="number" min="1" name="qty" class="form-control" id="qty" value="<?php if (!empty($single)) {
                                    echo $single->qty;
                                } ?>" placeholder="Enter Quantity">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch<b class="require">*</b></label>
                                <select multiple id="color_id" name="color_id_temp[]"
                                    data-placeholder="Please select batch">
                                    <option value="" disabled>Select Master Batch</option>
                                    <?php if (!empty($master_batch_data)) {
                                        foreach ($master_batch_data as $master_batch) {
                                            $selected = '';
                                            if (!empty($single) && !empty($single->color_id)) {
                                                $selected_ids = explode(',', $single->color_id);
                                                if (in_array($master_batch->id, $selected_ids)) {
                                                    $selected = 'selected';
                                                }
                                            }
                                            ?>
                                            <option value="<?= $master_batch->id ?>" <?= $selected ?>>
                                                <?= $master_batch->name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <input type="hidden" name="color_id" id="color_id_ordered"
                                    value="<?= !empty($single) ? $single->color_id : '' ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="row_material">Raw Material<b class="require">*</b></label>
                                <select multiple id="row_material" name="row_material_temp[]"
                                    data-placeholder="Please select raw material" class="form-control chosen-select">
                                    <option value="" disabled>Please Select Raw Material</option>
                                    <?php
                                    $selected_raw_materials = !empty($single) ? explode(',', $single->raw_materials) : [];
                                    if (!empty($raw_material_data)) {
                                        foreach ($raw_material_data as $raw_material_data_result) {
                                            $selected = in_array($raw_material_data_result->id, $selected_raw_materials) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $raw_material_data_result->id ?>" <?= $selected ?>>
                                                <?= $raw_material_data_result->rm_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <input type="hidden" name="row_material" id="row_material_ordered"
                                    value="<?= !empty($single) ? $single->raw_materials : '' ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="make">Start time<b class="require"></b></label>
                                <input type="text" name="production_schedule_start_time" class="form-control"
                                    id="production_schedule_start_time"
                                    value="<?= $this->session->userdata('production_schedule_start_time'); ?>" readonly>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs 12">
                                <label for="make">End time<b class="require"></b></label>
                                <input type="text" name="production_schedule_end_time" class="form-control"
                                    id="production_schedule_end_time"
                                    value="<?= $this->session->userdata('production_schedule_end_time'); ?>" readonly>
                            </div>
                            <input type="hidden" name="production_schedule_start_date" id="production_schedule_start_date"
                                value="<?= $this->session->userdata('production_schedule_start_date'); ?>">
                            <input type="hidden" name="production_schedule_end_date" id="production_schedule_end_date"
                                value="<?= $this->session->userdata('production_schedule_end_date'); ?>">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>

                            <div id="calendar"></div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>



<?php include('footer.php'); ?>


<script>
    $(document).ready(function () {
        // $('#product_master .child_menu').show();
        // $('#product_master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        // $('.add_rm').addClass('active_cc');
        $('#product_master').addClass('nv active-color');
    });
    $(document).ready(function () {
        $('.js-example-basic-multiple').select2({
            // placeholder: "Please select type"
        });
    });
</script>
<script>
    $('#plant_id').on('change', function () {
        if ($(this).val()) {
            $("#plant_id-error").hide();
        }
    });
</script>
<script>
    $(document).ready(function () {
        $('#add_rm_form').validate({
            ignore: [],
            rules: {

                article_id: {
                    required: true
                },
                article_group_id: {
                    required: true
                },
                qty: {
                    required: true,
                },
                'color_id_temp[]': {
                    required: true
                },
                'row_material_temp[]': {
                    required: true
                },


            },
            messages: {

                article_id: {
                    required: 'Please select article'
                },
                article_group_id: {
                    required: 'Please select article group'
                },
                qty: {
                    required: 'Please enter quantity'
                },
                'color_id_temp[]': {
                    required: 'Please select color'
                },
                'row_material_temp[]': {
                    required: 'Please select raw material'
                },

            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        $("#row_material").change(function () {
            $("#row_material").valid();
        });
        $("#article_id").change(function () {
            $("#article_id").valid();
        });
        $("#article_group_id").change(function () {
            $("#article_group_id").valid();
        });
        $("#color_id").change(function () {
            $("#color_id").valid();
        });



    });
</script>

<script>
    $(document).ready(function () {
        $('#color_id').chosen({
            placeholder_text_single: "Please select batch",
            allow_single_deselect: true
        });
        $('#row_material').chosen({
            placeholder_text_single: "Please select raw material",
            allow_single_deselect: true
        });
        let selectedRawOrder = <?= !empty($single) ? json_encode(explode(',', $single->raw_materials)) : '[]' ?>;
        let selectedColorOrder = <?= !empty($single) ? json_encode(explode(',', $single->color_id)) : '[]' ?>;

        function updateHiddenInputs() {
            $('#row_material_ordered').val(selectedRawOrder.join(','));
            $('#color_id_ordered').val(selectedColorOrder.join(','));
        }

        $('#row_material').on('change', function (evt, params) {
            if (params.selected) {
                selectedRawOrder.push(params.selected);
            } else if (params.deselected) {
                selectedRawOrder = selectedRawOrder.filter(val => val !== params.deselected);
            }
            updateHiddenInputs();
        });

        $('#color_id').on('change', function (evt, params) {
            if (params.selected) {
                selectedColorOrder.push(params.selected);
            } else if (params.deselected) {
                selectedColorOrder = selectedColorOrder.filter(val => val !== params.deselected);
            }
            updateHiddenInputs();
        });

        updateHiddenInputs();
    });
</script>

</script>
<script>
    $(document).ready(function () {

        function loadArticlesByGroup(article_group_id, selected_article_id = '') {
            console.log(article_group_id);
            if (article_group_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_all_article_by_group_production',
                    type: 'POST',
                    data: {
                        selected_article: article_group_id
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#article_id').empty();
                        $('#article_id').append('<option value="">Select articles</option>');
                        $.each(data, function (index, article) {
                            let selected = (article.id == selected_article_id) ? 'selected' : '';
                            $('#article_id').append('<option value="' + article.id + '" ' + selected + '>' + article.article_name + '</option>');
                        });
                    },
                    error: function () {
                        alert('Error retrieving articles. Please try again.');
                    }
                });
            } else {
                $('#article_id').empty();
                $('#article_id').append('<option value="">Select article</option>');
            }
        }

        $('#article_group_id').change(function () {
            var article_group_id = $(this).val();
            loadArticlesByGroup(article_group_id);
        });

        var initial_article_group_id = $('#article_group_id').val();
        var initial_article_id = "<?= !empty($single) ? $single->article_id : '' ?>";
        if (initial_article_group_id) {
            loadArticlesByGroup(initial_article_group_id, initial_article_id);
        }
    });

</script>