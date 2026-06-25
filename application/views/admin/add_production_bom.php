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

    .table>tbody>tr>td input {
        width: 100%;
        height: 100%;
    }

    .add_option {
        width: 100%;
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

    .js-example-basic-multiple {
        display: none;
    }

    label {
        width: 100%;
        height: 28px;
    }

    .select2-container {
        width: 100% !important;
    }

    .input-wrapper {
        position: relative;
    }
   .custom-modal {
        overflow-y: auto;
    }

    .modal-dialog {
        max-width: 1120px;
        width: 90%;
        margin: 1.75rem auto;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Add Production BOM</h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_production_vom" id="add_production_vom">

                        <div class="row flex_wrap">


                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Article</label>
                                <input type="text" class="form-control" name="article" id="article" value="<?php $segment_id = $this->uri->segment(2);
                                if (!empty($article)) {
                                    foreach ($article as $article_result) {
                                        if (!empty($segment_id) && $segment_id == $article_result->id ||(!empty($single) && $single->article_id == $article_result->id)) {
                                            echo $article_result->article_name;
                                        }
                                    }
                                }
                                ?>" readonly>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Batch <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" value="<?php if (!empty($single))echo $single->batch; ?>" placeholder="Please enter batch" name="batch" id="batch">
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Weight (Per Batch)<b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" value ="<?php if (!empty($single))echo $single->weight; ?>" placeholder="Please enter weight" 
                                    name="weight" id="weight">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Raw Material <span style="font-weight: normal;">(%)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" 
                                    value="<?php if (!empty($single)) echo $single->raw_material_one; ?>" 
                                    placeholder="Please enter raw material"
                                    name="raw_material_one" id="raw_material_one" 
                                    onkeyup="validateTotalPercentage()">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Raw Material 2 <span style="font-weight: normal;">(%)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" 
                                    value="<?php if (!empty($single)) echo $single->raw_material_two; ?>" 
                                    placeholder="Please enter second raw material"
                                    name="raw_material_two" id="raw_material_two" 
                                    onkeyup="validateTotalPercentage()">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Other RM <span style="font-weight: normal;">(%)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" 
                                    value="<?php if (!empty($single)) echo $single->other_rm; ?>" 
                                    placeholder="Please enter other RM"
                                    name="other_rm" id="other_rm" 
                                    onkeyup="validateTotalPercentage()">
                            </div>

                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Master Batch <span style="font-weight: normal;">(%)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control" 
                                    value="<?php if (!empty($single)) echo $single->master_batch; ?>" 
                                    placeholder="Please enter master batch"
                                    name="master_batch" id="master_batch" 
                                    onkeyup="validateTotalPercentage()">
                            </div>

                            <!-- P1 New Fields: Std Cycle Time & Std Weight -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Standard Cycle Time <span style="font-weight: normal;">(seconds)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control no-scroll-number" type="number" step="0.01" min="0" required
                                    value="<?php if (!empty($single)) echo $single->std_cycle_time; ?>" 
                                    placeholder="Enter standard cycle time in seconds"
                                    name="std_cycle_time" id="std_cycle_time">
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Standard Article Weight <span style="font-weight: normal;">(grams)</span> <b class="require">*</b></label>
                                <input autocomplete="off" class="form-control no-scroll-number" type="number" step="0.001" min="0" required
                                    value="<?php if (!empty($single)) echo $single->std_weight; ?>" 
                                    placeholder="Enter finished article weight in grams"
                                    name="std_weight" id="std_weight">
                            </div>
                            <!-- End P1 New Fields -->


                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <table style="width:100%;margin-top:10px;" class="table table-striped table-bordered"
                                    id="example">
                                    <thead class="thead">
                                        <tr>
                                            <th>PARTICULARS</th>
                                            <th style="width: 50px;"></th>
                                            <th>Subtype</th>
                                            <th style="width: 30px;"></th>
                                            <th>UOM</th>
                                            <th>Qty/Batch</th>
                                            <th style="width: 20px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($particulars)) { 
                                            $i=0;
                                            foreach ($particulars as $particular) { 
                                               $bom_result = "";
                                                    if (!empty($bom)) {
                                                        foreach ($bom as $bom_item) {
                                                            if ($bom_item->particulars_id == $particular->id) {
                                                                $bom_result = $bom_item;
                                                                break;  
                                                            }
                                                        }
                                                    }
                                                    $sub_category = isset($sub_categories[$particular->id]) ? $sub_categories[$particular->id] : [];
                                                ?>
                                                <tr class="particular_row">
                                                    <td>
                                                        <input type="text" class="particular_input" name="add_particular[]"
                                                            value="<?php echo $particular->particulars_type; ?>" placeholder="Enter new type" disabled>
                                                            <input type="hidden" name="particular_id[]" id="particular_id_<?php echo $i; ?>" value="<?php echo $particular->id; ?>" class="particular_id" >
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary save_option_btn d-none">Save</button>
                                                    </td>
                                                    <td>
                                                        <select name="sub_category[]" id="sub_category_<?= $i; ?>" class="sub_category">
                                                            <option value="">Please Select</option>
                                                            <?php if (!empty($sub_category)) {
                                                                foreach ($sub_category as $sub_category_result) { ?>
                                                                    <option value="<?= $sub_category_result->id ?>" 
                                                                            data-uom="<?= $sub_category_result->uom_name ?>" data-uom_id="<?= $sub_category_result->uom_id ?>"
                                                                        <?php if (!empty($bom_result) && $bom_result->sub_category_id == $sub_category_result->id) { ?>
                                                                            selected
                                                                        <?php } ?>>
                                                                        <?= $sub_category_result->rm_name ?>
                                                                    </option>
                                                            <?php }
                                                            } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                    <button type="button" class="btn add_option" onclick="addNewOption(<?= $i; ?>)">+</button>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="uom[]" value="<?php if (!empty($bom_result))echo $bom_result->uom_name; ?>" id = "uom_<?= $i; ?>"readonly>
                                                        <input type="hidden" name="uom_id[]" value="<?php if (!empty($bom_result))echo $bom_result->uom_id; ?>" id = "uom_id_<?= $i; ?>"readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="quantity[]" min="0" value="<?php if (!empty($bom_result))echo $bom_result->quantity; ?>">
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            <?php 
                                            $i++;
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ">
                                    <button id="add_new_row" type="button" class="btn btn-primary">Add New</button>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="form-group ">
                                    <button id="submit_btn" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade custom-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Sub Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container row ">
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">Name<b class="require">*</b></label>
                            <input name="new_option" id="new_option" type="text" class="form-control" value="" placeholder="Enter new option" required>
                            <div class="error new_option_error"></div>
                            <input name="type" id="row_id" type="hidden" class="form-control" value="">
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">Re-Order Level<b class="require">*</b></label>
                            <input name="reorder_level" id="reorder_level" type="text" class="form-control" value="" placeholder="Enter re-order level" required>
                            <div class="error reorder_level_error"></div>
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">MFI<b class="require">*</b></label>
                            <input name="mfi" id="mfi" type="text" class="form-control" value="" placeholder="Enter MFI" required>
                            <div class="error mfi_error"></div>
                        </div>
                       
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">ALIAS<b class="require">*</b></label>
                            <input name="alias" id="alias" type="text" class="form-control" value="" placeholder="Enter ALIAS" required>
                            <div class="error alias_error"></div>
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">CODE</label>
                            <input name="code" id="code" type="text" class="form-control" value="" placeholder="Enter CODE" required>
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">MAKE<b class="require">*</b></label>
                            <select name="make" id="make" class="form-control">
                                <option value="">Select MAKE</option>
                                <?php if (!empty($make)) {
                                    foreach ($make as $make_result) { ?>
                                        
                                        <option value="<?= $make_result->id ?>"><?= $make_result->make ?></option>
                                <?php }
                                } ?>
                            </select>
                            <div class="error make_error"></div>
                            <input name="type" id="row_id" type="hidden" class="form-control" value="">
                            <input name="type" id="type" type="hidden" class="form-control" value="">
                            <input type="hidden" name="particular_selected" id="particular_selected" value="">
                            
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label class="add_opt">UOM<b class="require">*</b></label>
                            <select name="uom" id="uom" class="form-control">
                                <option value="">Select UOM</option>
                                <?php if (!empty($uom)) {
                                    foreach ($uom as $uom_result) { ?>
                                        
                                        <option value="<?= $uom_result->id ?>"><?= $uom_result->uom_name ?></option>
                                <?php }
                                } ?>
                            </select>
                            <div class="error uom_error"></div>
                            
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <button id="add_option_btn" type="button" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.article_list').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.no-scroll-number').forEach(function (input) {
            input.addEventListener('wheel', function (event) {
                event.preventDefault();
            }, { passive: false });
        });

        // Using delegated event handler to support dynamically added rows
        $(document).on('change', '.sub_category', function () {
            let dropdown = this;
            let idParts = dropdown.id.split("_");
            let rowIndex = idParts[idParts.length - 1];

            // Clear previous values
            document.getElementById("uom_" + rowIndex).value = "";
            document.getElementById("uom_id_" + rowIndex).value = "";

            let selectedOption = dropdown.options[dropdown.selectedIndex];
            if (selectedOption) {
                let uomName = selectedOption.getAttribute("data-uom") || "";
                let uomId   = selectedOption.getAttribute("data-uom_id") || "";

                document.getElementById("uom_" + rowIndex).value = uomName;
                document.getElementById("uom_id_" + rowIndex).value = uomId;
            }
        });

        // Trigger change for existing rows to populate their UOM fields on load
        document.querySelectorAll(".sub_category").forEach(function (dropdown) {
            dropdown.dispatchEvent(new Event("change"));
        });
    });
    function validateTotalPercentage() {
        let raw1 = parseFloat(document.getElementById('raw_material_one').value) || 0;
        let raw2 = parseFloat(document.getElementById('raw_material_two').value) || 0;
        let other = parseFloat(document.getElementById('other_rm').value) || 0;
        let master = parseFloat(document.getElementById('master_batch').value) || 0;

        let total = raw1 + raw2 + other + master;

        if (total > 100) {
            alert("Total percentage cannot exceed 100%");
            document.activeElement.value = '';
        }
    }
    $(document).ready(function () {
        var new_row = 0;
        $('#add_new_row').click(function (e) {
            e.preventDefault();
          
            new_row = $('.particular_row').length;   
            var newRow = `
                <tr class="particular_row">
                    <td><input type="text" class="particular_input" name="add_particular[]" value="" placeholder="Enter new type">
                    <input type="hidden" name="particular_id[]" id="particular_id_${new_row}" value="" class="particular_id" >  
                    </td>
                    <td><button type="button" class="btn btn-primary save_option_btn">Save</button></td>
                    <td><select name="sub_category[]" id = "sub_category_${new_row}" class="sub_category"><option value="">Please Select</option></select></td>
                    <td><button type="button" class="btn add_option" onclick="addNewOption(${new_row})">+</button></td>
                    <td>
                        <input type="text" name="uom[]" id="uom_${new_row}" readonly>
                        <input type="hidden" name="uom_id[]" id="uom_id_${new_row}" readonly>
                    </td>
                    <td><input type="number" min="0" name="quantity[]"></td>
                    <td><button type="button" class="btn btn-danger remove_row_btn"><i class="fa fa-trash"></i></button></td>
                </tr>
            `;
            $('#example tbody').append(newRow);
        });
        $(document).on('click', '.remove_row_btn', function () {
            $(this).closest('tr').remove();
        });
        $('#example').on('click', '.save_option_btn', function (e) {
            e.preventDefault();
            var add_particular = $(this).closest("tr").find(".particular_input").val();  
            var $row = $(this).closest("tr");
            var row_id = $(this).closest("tr").find(".particular_id").attr('id').split('_');
            var new_row_id = row_id[2];
            if (add_particular !== '') {
                $.ajax({
                    type: "POST",
                    url: "<?= base_url() ?>admin/Ajax_controller/set_new_particular",
                    data: { add_particular: add_particular },
                    success: function (data) {
                        if (data != '') {
                            $('#particular_id_' + new_row_id).val(data);
                            $row.find(".save_option_btn").hide(); 
                            $row.find(".particular_input").attr('readonly', true);
                        } else {
                            alert('Already exists in the list!');
                        }
                    },
                });
            } else {
                alert('Please enter a value before saving!');
            }
        });   
    });
</script>       
<script>
document.getElementById('submit_btn').addEventListener('click', function (e) {
    let raw1 = parseFloat(document.getElementById('raw_material_one').value) || 0;
    let raw2 = parseFloat(document.getElementById('raw_material_two').value) || 0;
    let other = parseFloat(document.getElementById('other_rm').value) || 0;
    let master = parseFloat(document.getElementById('master_batch').value) || 0;

    let total = raw1 + raw2 + other + master;

    if (total !== 100) {
        e.preventDefault(); 
        alert("Total percentage must be exactly 100%");
    }
});
</script>

<script>
    function addNewOption(row_id) {  
             
            $('#type').val($('#particular_id_' + row_id).val());
            $('#row_id').val(row_id);
            var type = $('#type').val();
            if (type != '') {
                $('#exampleModal').modal('show');
            }else {
                alert('Please enter type before adding a new option!');
            }
        }
        $('#add_option_btn').click(function(){
            var new_option = $('#new_option').val();
            var reorder_level = $('#reorder_level').val();
            var mfi = $('#mfi').val();
            var alias = $('#alias').val();
            var make = $('#make').val();
            var code = $('#code').val();
            var uom = $('#uom').val();
            var rm_type = $('#type').val(); 
            var row_id = $('#row_id').val(); 
            if(new_option == '' && reorder_level == '' && mfi == '' && alias == '' && make == '' && uom == ''){
                $('.new_option_error').text('Please enter sub type!');
                $('.reorder_level_error').text('Please enter reorder level!');
                $('.mfi_error').text('Please enter MFI!');
                $('.alias_error').text('Please enter ALIAS!');
                $('.make_error').text('Please select MAKE!');
                $('.uom_error').text('Please select UOM!');
            }else if(new_option == ''){
                $('.new_option_error').text('Please enter sub type!');
            }else if(reorder_level == ''){
                $('.reorder_level_error').text('Please enter reorder level!');
            }else if(mfi == ''){
                $('.mfi_error').text('Please enter MFI!');
            }else if(alias == ''){
                $('.alias_error').text('Please enter ALIAS!');
            }else if(make == ''){
                $('.make_error').text('Please select MAKE!');
            }else if(uom == ''){
                $('.uom_error').text('Please select UOM!');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '<?=base_url()?>admin/Ajax_controller/set_new_sub_type',
                    data: {new_option: new_option, rm_type: rm_type , reorder_level: reorder_level, mfi: mfi, code: code, alias: alias, make: make, uom: uom},
                    success: function(data){
                        if(data == '0'){
                            $('#exampleModal').modal('hide');
                            getAllSubCategory(row_id);
                        }else{
                            $('.new_option_error').text('This sub type already exist!');
                        }
                    }
                }); 
            }       
        })    
        function getAllSubCategory(row_id){
            var particular_id = $('#type').val(); 
            $.ajax({
                type: 'POST',
                url: '<?= base_url() ?>admin/Ajax_controller/get_all_sub_category',
                    data: { particular_id: particular_id },
                    success: function (data) {
                        if(data != ''){
                            var opts = $.parseJSON(data);
                            var sub_category = $('#sub_category_' + row_id);
                            sub_category.empty(); 
                            sub_category.append('<option value="">Please Select</option>');
                            $.each(opts, function (i, d) {
                                sub_category.append(
                                    '<option value="' + d.id + '" data-uom="' + d.uom_name + '" data-uom_id="' + d.uom_id + '">' +
                                        d.rm_name +
                                    '</option>'
                                );
                            });
                            sub_category.trigger('chosen:updated');
                        }
                    }
                });
            }
        $('#new_option').keyup(function(){
        $('.new_option_error').text('');
        });
        $('#reorder_level').keyup(function(){
        $('.reorder_level_error').text('');
        });
        $('#mfi').keyup(function(){
        $('.mfi_error').text('');
        });
        $('#alias').keyup(function(){
        $('.alias_error').text('');
        });
        $('#make').change(function(){
        $('.make_error').text('');
        });
        $('#uom').change(function(){
        $('.uom_error').text('');
        }); 
        $('#exampleModal').on('hidden.bs.modal', function() {
            $('#new_option').val(''); 
            $('#reorder_level').val('');
            $('#mfi').val('');
            $('#alias').val('');    
            $('#make').val('');
            $('#uom').val('');
            $('#code').val('');
            $('.new_option_error').text(''); 
            $('.reorder_level_error').text('');
            $('.mfi_error').text('');
            $('.alias_error').text('');
            $('.make_error').text('');
            $('.uom_error').text('');
        });  
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    jQuery.validator.addMethod("noNumbers", function (value, element) {
        return this.optional(element) || !/\d/.test(value);
    });
    jQuery.validator.addMethod("noAlphabets", function (value, element) {
        return this.optional(element) || /^[^A-Za-z]+$/.test(value);
    }, "Alphabets are not allowed!");

    $(document).ready(function () {
        $('#add_production_vom').validate({
            ignore: [],
            rules: {
                batch: {
                    required: true,
                    noSpaceAtStart: true,
                },
                weight: {
                    required: true,
                    noSpaceAtStart: true,
                },
                raw_material_one: {
                    required: true,
                    noSpaceAtStart: true,
                },
                raw_material_two: {
                    required: true,
                    noSpaceAtStart: true,
                },
                master_batch: {
                    required: true,
                },
                std_cycle_time: {
                    required: true,
                    noSpaceAtStart: true,
                },
                std_weight: {
                    required: true,
                    noSpaceAtStart: true,
                }
            },
            messages: {

                batch: {
                    required: "Please enter batch number!",
                    noSpaceAtStart: "First letter can not be space",
                },
                weight: {
                    required: "Please enter weight!",
                    noSpaceAtStart: "First letter can not be space",
                },
                raw_material_one: {
                    required: "Please enter first raw material!",
                    noSpaceAtStart: "First letter can not be space",
                },
                raw_material_two: {
                    required: "Please enter second raw material!",
                    noSpaceAtStart: "First letter can not be space",
                },
                master_batch: {
                    required: "Please enter master batch!",
                },
                std_cycle_time: {
                    required: "Please enter standard cycle time!",
                    noSpaceAtStart: "First letter can not be space",
                },
                std_weight: {
                    required: "Please enter standard article weight!",
                    noSpaceAtStart: "First letter can not be space",
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
    });
</script>
