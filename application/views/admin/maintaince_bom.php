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

    .table>tbody>tr>td input {
        width: 100%;
        height: 100%;
    }

    .table>tbody>tr>td[rowspan] input {
        padding: 15px;
    }

    td {
        padding: 0 !important;
    }

    td select {
        width: 100%;
        height: 100%;
        padding: 2px;
    }

    input.is-invalid,
    select.is-invalid {
        border-color: red !important;
    }

    .invalid-feedback {
        display: block !important;
        font-size: 12px;
        color: red;
    }

    #back_btn {
        margin-top: 10px;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="page-title">
        <div class="title_left">
            <h3>Maintenance BOM</h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_bom" id="add_bom" enctype="multipart/form-data">
                        <table style="width: 100%;" class="table table-striped table-bordered" id="dataTable">
                            <thead class="thead">
                                <tr>
                                    <th>Sizes of Parts Of Mould</th>
                                    <th>Parts of Mould</th>
                                    <th>UOM</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($single_article)) {
                                    $article_id = $single_article->id;
                                    $part_types = [
                                        'TYPE OF MOULD' => explode(",", $single_article->type_of_mould_id),
                                        'ALANKEY BOLT' => explode(",", $single_article->alankey_bolt_id),
                                        'AIR PIN' => explode(",", $single_article->air_pin_id),
                                        'SPRING' => explode(",", $single_article->spring_id),
                                        'PU NIPPLES' => explode(",", $single_article->pu_nipples_id),
                                        'EJECTOR PIN' => explode(",", $single_article->ejector_pin_id),
                                        'I BOLT' => explode(",", $single_article->i_bolt_id),
                                        'CORD' => explode(",", $single_article->cord_id),
                                        'O RING' => explode(",", $single_article->o_ring_id),
                                        'INSERT SLOT PLATE' => explode(",", $single_article->insert_slot_plate_id),
                                        'CORE CYLINDER SEAL' => explode(",", $single_article->core_cylinder_seal_id),
                                        'SEAL' => explode(",", $single_article->seal_id),
                                        'HOSE PIPE' => explode(",", $single_article->hose_pipe_id)
                                    ];
                                    $i = 0;
                                    foreach ($part_types as $label => $part_ids) {
                                        foreach ($part_ids as $part_id) {
                                            $part_id = trim((string) $part_id);
                                            if ($part_id === '') {
                                                continue;
                                            }
                                            $size = '';
                                            switch ($label) {
                                                case 'TYPE OF MOULD':
                                                    $mould_details = $this->Admin_model->get_type_of_mould($part_id);
                                                    $size = !empty($mould_details) ? $mould_details->type_of_mould : '';
                                                    break;
                                                default:
                                                    $mould_details = $this->db->get_where('tbl_rm_master', array('id' => $part_id, 'is_deleted' => '0'))->row();
                                                    $size = !empty($mould_details) ? $mould_details->rm_name : '';
                                                    break;
                                            }
                                            $size_trim = trim((string) $size);
                                            if ($size_trim !== '' && strcasecmp($size_trim, 'NOT APPLICABLE') !== 0) {
                                                // if($label == 'TYPE OF MOULD'){
                                                //     continue;
                                                // }
                                                $selected_mould = $this->Admin_model->get_selected_mould($article_id, $part_id, $label);

                                                $default_uom_id = null;
                                                if (!empty($selected_mould) && !empty($selected_mould->uom_id)) {
                                                    $default_uom_id = $selected_mould->uom_id;
                                                } else {
                                                    $default_uom_id = $this->Admin_model->get_default_uom_id_for_part_label($label);
                                                }
                                                ?>
                                                <tr>
                                                    <input type="hidden" value="<?= $single_article->id ?>" name="article_id">
                                                    <input type="hidden" value="<?= $single_article->group_of_article_id ?>"
                                                        name="group_of_article_id">
                                                    <td>
                                                        <input type="text" name="size_of_mould_level[<?= $i ?>]" readonly
                                                            value="<?= $size ?>" />
                                                        <input type="hidden" name="size_of_mould[<?= $i ?>]" readonly
                                                            value="<?= $mould_details->id ?>" />
                                                    </td>
                                                    <td><input type="text" name="type_of_mould[<?= $i ?>]" readonly
                                                            value="<?= $label ?>" /></td>
                                                    <td>
                                                        <select required name="uom[<?= $i ?>]">
                                                            <option value="">Please Select</option>
                                                            <?php if (!empty($uom)) {
                                                                foreach ($uom as $uom_result) { ?>
                                                                    <option value="<?= $uom_result->id ?>" <?php if (!empty($default_uom_id) && $default_uom_id == $uom_result->id)
                                                                          echo 'selected'; ?>>
                                                                        <?= $uom_result->uom_name ?>
                                                                    </option>
                                                                <?php }
                                                            } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input required type="number" min="0" name="quantity[<?= $i ?>]"
                                                            value="<?= !empty($selected_mould) ? $selected_mould->quantity : '' ?>" />
                                                    </td>
                                                </tr>
                                                <?php
                                                $i++;
                                            }
                                        }
                                    }
                                } ?>
                            </tbody>
                        </table>
                        <a href="<?= base_url('add_article/' . $this->uri->segment(2)) ?>" id="back_btn"
                            class="btn btn-primary">Back</a>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Next</button>


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
    $(document).ready(function () {
        // $('#master .child_menu').show();
        // $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        // $('.article_list').addClass('active_cc');
        $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        // Custom validation method for no space at start
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^[^\s]/.test(value);
        }, "First letter cannot be a space!");

        // Initialize validation for the form
        var validator = $('#add_bom').validate({
            ignore: [], // Ensure validation for hidden and dynamically added elements
            rules: {
                <?php for ($i = 0; $i < count($part_types); $i++) { ?>
                    'uom[<?= $i ?>]': {
                        required: true,
                    },
                    'quantity[<?= $i ?>]': {
                        required: true,
                        noSpaceAtStart: true,
                        number: true
                    },
                <?php } ?>
            },
            messages: {
                <?php for ($i = 0; $i < count($part_types); $i++) { ?>
                    'uom[<?= $i ?>]': {
                        required: "Please select UOM!",
                    },
                    'quantity[<?= $i ?>]': {
                        required: "Please enter quantity!",
                        noSpaceAtStart: "First letter cannot be a space!",
                        number: "Please enter a valid number!"
                    },
                <?php } ?>
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback'); // Add invalid-feedback class to error message

                // Place error message after the element if not already there
                if (element.next("span.invalid-feedback").length === 0) {
                    if (element.is("select")) {
                        element.closest('td').append(error); // Place error after <select>
                    } else {
                        element.after(error); // Place error after input fields
                    }
                }
            },
            highlight: function (element) {
                // Add 'is-invalid' class to the element when validation fails
                $(element).addClass('is-invalid');
                if ($(element).is('select')) {
                    $(element).closest('td').addClass('is-invalid');
                }
            },
            unhighlight: function (element) {
                // Remove 'is-invalid' class when validation passes
                $(element).removeClass('is-invalid');
                if ($(element).is('select')) {
                    $(element).closest('td').removeClass('is-invalid');
                }
            },
            submitHandler: function (form) {
                form.submit(); // Submit form if it is valid
            }
        });

        // Handling change event for the UOM select fields
        $("select[name^='uom']").on("change", function () {
            var $this = $(this);
            // Trigger the validation on the selected field to remove validation message
            if ($this.val() !== "") {
                $this.removeClass('is-invalid');  // Remove the invalid class
                $this.closest('td').removeClass('is-invalid');  // Remove invalid styling from parent td

                // Manually remove validation message if the field is valid
                validator.element($this);  // Trigger validation for this element
            } else {
                // If no value selected, apply invalid styles
                $this.addClass('is-invalid');
                $this.closest('td').addClass('is-invalid');

                // Manually add validation message
                validator.element($this);  // Trigger validation for this element
            }
        });

        // Submit the form only if it's valid
        $("#submit_btn").on("click", function () {
            // Check if the form is valid before submission
            if ($('#add_bom').valid()) {
                $('#add_bom').submit(); // If valid, submit the form
            } else {
                return false; // If invalid, do not submit
            }
        });
    });



</script>