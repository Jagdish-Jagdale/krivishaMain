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
        max-width: 100% !important;
    }

    .select2-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                Add Master Batch Request
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_material" id="add_material" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <input autocomplete="off" type="hidden" name="is_article_or_rm_material"
                                id="is_article_or_rm_material" value="1">

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <?php
                                 $assign_plant_id = $this->session->userdata('assign_plant_id'); 
                                ?>
                                <label>Plant<b class="require">*</b></label>
                                <select name="plant_id" id="plant_id" class="form-control select2-select" required>
                                    <option value="">Select Plant</option>
                                    <?php if (!empty($plant)) {
                                        foreach ($plant as $plant_result) { ?>
                                               <?php
                                                if ($this->session->userdata('is_admin') != '1') {
                                                    $assigned_plants_arr = explode(',', $assign_plant_id);
                                                    if (!in_array($plant_result->id, $assigned_plants_arr)) { 
                                                        continue;
                                                    }
                                                }
                                                ?>
                                            <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>>
                                                <?= $plant_result->plant_name ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch (Color)<b class="require">*</b></label>
                                <select name="master_batch_id" id="master_batch_id" class="form-control select2-select"
                                    required>
                                    <option value="">Select master batch</option>
                                    <?php if (!empty($master_batch)) {
                                        foreach ($master_batch as $article_result) { ?>
                                            <option value="<?= $article_result->id ?>">
                                                <?= $article_result->name ?>
                                            </option>
                                        <?php }
                                    } ?>

                                </select>
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Request Date<b class="require">*</b></label>
                                <input name="request_date" type="date" id="request_date" class="form-control datepicker"
                                    value="" placeholder="Select request date" disabled>
                            </div>

                            <div class="row d-none" id="item_table_container">
                                <table class="
                                table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Master Batch Name</th>
                                            <th>Quantity</th>
                                            <th>Remark</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="material_table_body">
                                        <!-- Dynamic rows will be added here -->
                                    </tbody>

                                </table>
                            </div>


                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                <button name="submit_btn" value="submit_btn" type="submit" id="submit_btn"
                                    class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include('footer.php');
$id = 0;
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);
}
?>
<script>
        $(document).ready(function() {
        
        $('#stock_management').addClass('nv active');
       
        $('.master_batch_request_from').addClass('active_cc');
       
    });
</script>
<script>
    $(document).ready(function () {
      
        $('.datepicker').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date(),
        });

        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        let rowCounter = 0;

        $('#master_batch_id').on('change', function () {
            $('#item_table_container').removeClass('d-none');
            var materialId = $(this).val();
            var materialName = $('#master_batch_id option:selected').text();
            var materialExists = false;

            // Check if material already exists
            $('#material_table_body tr').each(function () {
                var rowMaterialId = $(this).find('input[name="master_batch_ids[]"]').val();
                if (rowMaterialId == materialId) {
                    materialExists = true;
                    return false;
                }
            });

            if (materialId && !materialExists) {
                rowCounter++;

                var newRow = `<tr>
            <td class="sr-no"></td> <!-- Serial No -->
            <td>${materialName}
                <input type="hidden" class="form-control" name="master_batch_ids[]" value="${materialId}">
            </td>
            <td>
                <input type="text" class="form-control qty_validation_class" name="quantity[]" placeholder="Enter quantity">
            </td>
            <td>
                <input type="text" class="form-control" name="remark[]" placeholder="Enter remark">
            <td>
                <button type="button" class="btn btn-danger remove-material" data-id="${materialId}">Remove</button>
            </td>
        </tr>`;

                // Append row
                $('#material_table_body').append(newRow);

                // Disable the selected option
                $('#master_batch_id option[value="' + materialId + '"]').prop('disabled', true);

                // Reset dropdown
                $('#master_batch_id').val('').trigger('change.select2');
                $('#master_batch_id').select2('destroy').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });

                updateSerialNumbers();
            }
        });

        $(document).on('click', '.remove-material', function () {
            var materialId = $(this).data('id');

            $('#master_batch_id option[value="' + materialId + '"]').prop('disabled', false);

            $(this).closest('tr').remove();

            $('#master_batch_id').select2('destroy').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });

            updateSerialNumbers();
        });

        function updateSerialNumbers() {
            $('#material_table_body tr').each(function (index) {
                $(this).find('.sr-no').text(index + 1);
            });
        }

    });
</script>
<script>
    $(document).ready(function () {
        $('#add_material').validate({
            ignore: [],
            rules: {
                plant_id: { required: true },
                master_batch_id: {
                    required: {
                        depends: function () {
                            return $('#material_table_body tr').length === 0;
                        }
                    }
                }
            },
            messages: {
                plant_id: { required: "Please select a plant." },
                master_batch_id: { required: "Please select master batch." }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback error');
                if (element.hasClass('qty_validation_class')) {
                    error.insertAfter(element);
                } else if (element.hasClass('select2-hidden-accessible')) {
                    element.next('span').append(error);
                } else {
                    element.closest('.form-group').append(error);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function (form) {
                var allValid = true;
                $('.qty_validation_class').each(function () {
                    var val = $(this).val();
                    if (val === "" || isNaN(val)) {
                        $(this).addClass('is-invalid');
                        if ($(this).next('span.invalid-feedback error').length === 0) {
                            $(this).after('<span class="invalid-feedback error">Please enter a valid quantity</span>');
                        }
                        allValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('span.invalid-feedback error').remove();
                    }
                });
                if (allValid) {
                    form.submit();
                }
            },
        });

        $(document).on('input change', '.qty_validation_class', function () {
            var val = $(this).val();
            if (val !== "" && !isNaN(val)) {
                $(this).removeClass('is-invalid');
                $(this).next('span.invalid-feedback error').remove();
            }
        });

        $("#plant_id").change(function () {
            $("#plant_id").valid();
        });
        $('#master_batch_id').change(function () {
            $('#master_batch_id').valid();
        });
    });

</script>