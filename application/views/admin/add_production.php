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

    .chosen-container {
        width: 100% !important;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Add Production Report</h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_production" id="add_production" enctype="multipart/form-data">

                        <?php if (!empty($single) && !empty($single->id)): ?>
                            <input type="hidden" name="id" value="<?= $single->id ?>">
                        <?php endif; ?>
                        <div class="row flex_wrap">

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Date<b class="require">*</b></label>

                                <input autocomplete="off" type="text" class="form-control" placeholder="Select Date"
                                    name="production_date" id="production_date"
                                    value="<?php echo (!empty($single)) ? $single->production_date : ''; ?>">

                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Machine<b class="require">*</b></label>
                                <select class="form-control" name="machine_id" id="machine_id">
                                    <option value="">Select Machine Type</option>
                                    <?php if (!empty($machine_data)) {
                                        foreach ($machine_data as $machine) {
                                            $selected = (!empty($single) && $single->machine_id == $machine->id) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $machine->id; ?>" <?= $selected; ?>>
                                                <?= $machine->machine_name . ' (' . $machine->plant_name . ') '; ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>

                            </div>


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Group Of Article<b class="require">*</b></label>
                                <select class="form-control group_of_article" multiple name="article_group_id[]"
                                    id="article_group_id_<?= $i ?>">
                                    <option value="" disabled>Please select group</option>
                                    <?php
                                    $selected_group_ids = !empty($single->article_group_id) ? explode(',', $single->article_group_id) : [];
                                    $group_map = [];
                                    foreach ($group_of_article as $group) {
                                        $group_map[$group->id] = $group;
                                    }

                                    // Render selected groups in the correct order
                                    foreach ($selected_group_ids as $id) {
                                        if (isset($group_map[$id])) {
                                            $group = $group_map[$id];
                                            ?>
                                            <option value="<?= $group->id ?>" selected><?= $group->group_of_article ?></option>
                                            <?php
                                        }
                                    }

                                    // Render remaining unselected groups
                                    foreach ($group_of_article as $group) {
                                        if (!in_array($group->id, $selected_group_ids)) {
                                            ?>
                                            <option value="<?= $group->id ?>"><?= $group->group_of_article ?></option>
                                            <?php
                                        }
                                    }
                                    ?>

                                </select>
                                <input type="hidden" name="article_group_ordered" id="article_group_id_order_<?= $i ?>"
                                    value="<?= !empty($single) ? $single->article_group_id : '' ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Article Names / Mould <?= $i ?><b class="require">*</b></label>
                                <select class="form-control article_id" multiple="multiple" name="article_id_temp[]"
                                    id="article_id_<?= $i ?>">
                                    <option value="" disabled>Choose article</option>
                                    <?php
                                    $selected_article_ids = !empty($single) && !empty($single->article_id)
                                        ? explode(',', $single->article_id)
                                        : [];
                                    $article_map = [];
                                    foreach ($article_data as $article) {
                                        $article_map[$article->id] = $article;
                                    }
                                    foreach ($selected_article_ids as $id) {
                                        if (isset($article_map[$id])) {
                                            $article = $article_map[$id];
                                            ?>
                                            <option value="<?= $article->id ?>" selected>
                                                <?= $article->article_name ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    foreach ($article_data as $article) {
                                        if (!in_array($article->id, $selected_article_ids)) {
                                            ?>
                                            <option value="<?= $article->id ?>">
                                                <?= $article->article_name ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>

                                </select>
                                <input type="hidden" name="article_id_temp" id="article_id_ordered_<?= $i ?>"
                                    value="<?= !empty($single) ? $single->article_id : '' ?>">
                            </div>



                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Raw Materials <?= $i ?><b class="require">*</b></label>
                                <select class="form-control rm" multiple="multiple" name="raw_material_id_temp[]"
                                    id="raw_material_id<?= $i ?>">
                                    <option value="" disabled>Choose Raw Materials</option>
                                    <?php
                                    if (!empty($raw_material_data)) {
                                        foreach ($raw_material_data as $raw_material) {
                                            $selected = '';
                                            if (!empty($single) && !empty($single->raw_material_id)) {
                                                $selected_ids = explode(',', $single->raw_material_id);
                                                if (in_array($raw_material->id, $selected_ids)) {
                                                    $selected = 'selected';
                                                }
                                            }
                                            ?>
                                            <option value="<?= $raw_material->id ?>" <?= $selected ?>>
                                                <?= $raw_material->rm_name ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="raw_material_id" id="raw_material_id_ordered<?= $i ?>"
                                    value="<?= !empty($single) ? $single->raw_material_id : '' ?>">
                            </div>
                            <!-- Master Batch Dropdown -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch<b class="require">*</b></label>
                                <select class="form-control mb" multiple="multiple" name="master_batch_id_temp[]"
                                    id="master_batch_id">
                                    <option value="" disabled>Select Master Batch</option>
                                    <?php if (!empty($master_batch_data)) {
                                        foreach ($master_batch_data as $master_batch) {
                                            $selected = '';
                                            if (!empty($single) && !empty($single->master_batch_id)) {
                                                $selected_ids = explode(',', $single->master_batch_id);
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
                                <input type="hidden" name="master_batch_id" id="master_batch_id_ordered"
                                    value="<?= !empty($single) ? $single->master_batch_id : '' ?>">
                            </div>
                            <!-- </div> -->

                            <!-- Rejection Dropdown -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Rejection Raw Material<b class="require">*</b></label>
                                <select class="form-control reject_rm" multiple="multiple" name="rejection_id_temp[]"
                                    id="rejection_id">
                                    <option value="" disabled>Select Rejection Raw Material</option>
                                    <?php if (!empty($rejection_data)) {
                                        foreach ($rejection_data as $rejection) {
                                            $selected = '';
                                            if (!empty($single) && !empty($single->rejection_id)) {
                                                $selected_ids = explode(',', $single->rejection_id);
                                                if (in_array($rejection->id, $selected_ids)) {
                                                    $selected = 'selected';
                                                }
                                            }
                                            ?>
                                            <option value="<?= $rejection->id ?>" <?= $selected ?>>
                                                <?= $rejection->rm_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                                <input type="hidden" name="rejection_id" id="rejection_id_ordered"
                                    value="<?= !empty($single) ? $single->rejection_id : '' ?>">
                            </div>


                            <div class="row flex_wrap">

                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="form-group ">

                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
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
        $('#add_production input, #add_production select').on('change keyup', function () {
            var input = $(this);

            if (input.val() !== '') {
                input.next('.error').fadeOut();
            }
        });

    });
</script>
<script>
    $(document).ready(function () {
        let articlGroup = <?= !empty($single) ? json_encode(explode(',', $single->article_group_id)) : '[]' ?>;
        let articleOrder = <?= !empty($single) ? json_encode(explode(',', $single->article_id)) : '[]' ?>;
        let rawMaterialOrder = <?= !empty($single) ? json_encode(explode(',', $single->raw_material_id)) : '[]' ?>;
        let masterBatchOrder = <?= !empty($single) ? json_encode(explode(',', $single->master_batch_id)) : '[]' ?>;
        let rejectionOrder = <?= !empty($single) ? json_encode(explode(',', $single->rejection_id)) : '[]' ?>;

        function updateAllHiddenInputs() {
            $('#article_id_ordered_<?= $i ?>').val(articleOrder.join(','));
            $('#raw_material_id_ordered<?= $i ?>').val(rawMaterialOrder.join(','));
            $('#master_batch_id_ordered').val(masterBatchOrder.join(','));
            $('#rejection_id_ordered').val(rejectionOrder.join(','));
            $('#article_group_id_order_<?= $i ?>').val(articlGroup.join(','));
        }

        $('#article_id_<?= $i ?>').on('change', function (evt, params) {
            if (params.selected) {
                articleOrder.push(params.selected);
            } else if (params.deselected) {
                articleOrder = articleOrder.filter(val => val !== params.deselected);
            }
            updateAllHiddenInputs();
        });
        $('#article_group_id_<?= $i ?>').on('change', function (evt, params) {
            if (params.selected) {
                articlGroup.push(params.selected);
            } else if (params.deselected) {
                articlGroup = articlGroup.filter(val => val !== params.deselected);
            }
            updateAllHiddenInputs();
        });
        $('#raw_material_id<?= $i ?>').on('change', function (evt, params) {
            if (params.selected) {
                rawMaterialOrder.push(params.selected);
            } else if (params.deselected) {
                rawMaterialOrder = rawMaterialOrder.filter(val => val !== params.deselected);
            }
            updateAllHiddenInputs();
        });

        $('#master_batch_id').on('change', function (evt, params) {
            if (params.selected) {
                masterBatchOrder.push(params.selected);
            } else if (params.deselected) {
                masterBatchOrder = masterBatchOrder.filter(val => val !== params.deselected);
            }
            updateAllHiddenInputs();
        });
        $('#rejection_id').on('change', function (evt, params) {
            if (params.selected) {
                rejectionOrder.push(params.selected);
            } else if (params.deselected) {
                rejectionOrder = rejectionOrder.filter(val => val !== params.deselected);
            }
            updateAllHiddenInputs();
        });
        updateAllHiddenInputs();

        $("select[id^='article_group_id_']").change(function () {
            var group_id = $(this).val();
            var index = $(this).attr("id").split("_").pop();
            var $articleSelect = $("#article_id_" + index);
            var $hiddenInput = $("#article_id_ordered_" + index);
            articleOrder = [];

            $articleSelect.empty();
            $articleSelect.trigger("chosen:updated");
            $hiddenInput.val('');

            updateAllHiddenInputs();

            if (group_id && group_id.length > 0) {
                $.ajax({
                    url: '<?= base_url('admin/Ajax_controller/get_articles_by_group') ?>',
                    type: 'POST',
                    data: { group_id: group_id },
                    dataType: 'json',
                    success: function (data) {
                        $.each(data, function (index, article) {
                            $articleSelect.append(
                                $('<option>', {
                                    value: article.id,
                                    text: article.article_name
                                })
                            );
                        });
                        $articleSelect.trigger("chosen:updated");
                    },
                    error: function () {
                        alert('Error fetching articles.');
                    }
                });
            }
        });

    });
</script>

<script>
    flatpickr("#production_date", {
        enableTime: true,
        dateFormat: "d-m-Y h:i K",
        maxDate: "today",
        minDate: new Date().fp_incr(-4)
    });
</script>




<script>
    $(document).ready(function () {
        // $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.add_production').addClass('active_cc');
        // $('#product_master').addClass('nv active-color');
    });
</script>


<script>
    $(document).ready(function () {
        var validator = $('#add_production').validate({
            ignore: [],
            rules: {
                production_date: {
                    required: true
                },
                machine_id: {
                    required: true
                },
                'article_group_id[]': {
                    required: true
                },
                'article_id_temp[]': {
                    required: true
                },
                'raw_material_id_temp[]': {
                    required: true
                },
                'master_batch_id_temp[]': {
                    required: true
                },
                'rejection_id_temp[]': {
                    required: true
                },
            },
            messages: {
                production_date: {
                    required: 'Please select date!'
                },
                machine_id: {
                    required: 'Please select machine!'
                },
                'article_group_id[]': {
                    required: 'Please select article group!'
                },
                'article_id_temp[]': {
                    required: 'Please select articles!'
                },
                'raw_material_id_temp[]': {
                    required: 'Please select raw material!'
                },
                'master_batch_id_temp[]': {
                    required: 'Please select master batch!'
                },
                'rejection_id_temp[]': {
                    required: 'Please select rejections!'
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


        $('.article_id').chosen({
            placeholder: "Please select article"
        });


        $('.group_of_article').chosen({
            placeholder: "Please select group of article"
        });
        $('.article_name').chosen({
            placeholder: "Please select article names / mould"
        });
        $('.rm').chosen({
            placeholder: "Please select raw materials"
        });
        $('.mb').chosen({
            placeholder: "Please select master batch"
        });
        $('.reject_rm').chosen({
            placeholder: "Please select rejection raw material"
        });
        $('#machine_id').on('change', function () {
            $(this).valid();
        });
        $('.group_of_article').on('change', function () {
            $(this).valid();
        });
        $('#production_date').on('change', function () {
            $(this).valid();
        });

        $('.article_id').on('change', function () {
            $(this).valid();
        });

        $('.article_name').on('change', function () {
            $(this).valid();
        });

        $('.rm').on('change', function () {
            $(this).valid();
        });

        $('.mb').on('change', function () {
            $(this).valid();
        });

        $('.reject_rm').on('change', function () {
            $(this).valid();
        });

    });
</script>