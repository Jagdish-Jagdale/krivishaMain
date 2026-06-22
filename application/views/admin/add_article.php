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
        /* max-width: 100% !important; */
        width: 100% !important;
    }

    select.form-control {
        display: none !important;
    }
    label{
        margin-bottom: 0;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
            <?php if (!empty($single)) { ?>
                Update Article/Mould
            <?php } else { ?>
                Add Article/Mould
            <?php } ?>
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_article" id="add_article" enctype="multipart/form-data">

                        <div class="row flex_wrap">
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Article/ Mould Name<b class="require">*</b></label>
                                <input id="article_name" name="article_name" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->article_name; } ?>" placeholder="Enter article name" required>
                                <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                                <span id="name_error" class=""></span>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Article Re-Order Level<b class="require">*</b></label>
                                <input id="reorder_level" name="reorder_level" type="number" min="0" class="form-control" value="<?php if (!empty($single)) { echo $single->reorder_level; } ?>" placeholder="Enter article reorder level" required>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Group Of Article<b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('group_of_article')">Add New</button></label>
                                <select name="group_of_article" id="group_of_article" class="form-control js-example-basic-multiple">
                                    <option value="">Please select group</option>
                                    <?php if (!empty($group_of_article)){
                                    foreach ($group_of_article as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->group_of_article_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->group_of_article ?>
                                        </option>
                                    <?php }} ?>
                                    </select>
                                 
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Type Of Mould<b class="require">*</b>
                                    
                                <button type="button" class="btn add_option" onclick="addNewOption('type_of_mould')">Add New</button></label>
                                <select name="type_of_mould" id="type_of_mould" class="form-control js-example-basic-multiple">
                                    <option value="">Please select mould</option>
                                    <?php if (!empty($type_of_mould)){
                                     foreach ($type_of_mould as $type_of_mould_result) { ?>
                                            <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single)&& $single->type_of_mould_id==$type_of_mould_result->id){?>selected<?php }?>><?= $type_of_mould_result->type_of_mould ?></option>
                                        <?php }} ?>
                                                 
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">ALANKEY BOLT<b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('alankey_bolt')">Add New</button></label>
                                <select multiple="multiple" name="alankey_bolt[]" id="alankey_bolt" class="form-control js-example-basic-multiple">
                                    <option value="">Please select alankey bolt</option>
                                    <?php if (!empty($alankey_bolt)) { 
                                        if (!empty($single)) {
                                            $alankey_bolt_selected = explode(",", $single->alankey_bolt_id); 
                                        } else {
                                            $alankey_bolt_selected = []; 
                                        }

                                        foreach ($alankey_bolt as $alankey_bolt_result) { ?>
                                            <option value="<?= $alankey_bolt_result->id ?>" 
                                                <?php if (in_array($alankey_bolt_result->id, $alankey_bolt_selected)) { ?>selected="selected"<?php } ?>>
                                                <?= htmlspecialchars($alankey_bolt_result->alankey_bolt) ?>
                                            </option>
                                        <?php }
                                    } ?>
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Air Pin<b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('air_pin')">Add New</button></label>
                                <select multiple="multiple" name="air_pin[]" id="air_pin" class="form-control js-example-basic-multiple">
                                    <!-- <option value="">Please select air pin</option> -->
                                    <?php if (!empty($air_pin)) { 
                                        if (!empty($single)) {
                                            $air_pin_selected = explode(",", $single->air_pin_id); 
                                        } else {
                                            $air_pin_selected = []; 
                                        }
                                        foreach ($air_pin as $air_pin_result) { ?>
                                            <option value="<?= $air_pin_result->id ?>" 
                                                <?php if (in_array($air_pin_result->id, $air_pin_selected)) { ?>selected="selected"<?php } ?>>
                                                <?= htmlspecialchars($air_pin_result->air_pin) ?>
                                            </option>
                                        <?php }
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Spring
                                <button type="button" class="btn add_option" onclick="addNewOption('spring')">Add New</button></label>
                                <select name="spring" id="spring" class="form-control js-example-basic-multiple">
                                    <option value="">Please select spring</option>
                                    <?php if (!empty($spring)){
                                    foreach ($spring as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->spring_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->spring ?>
                                        </option>
                                    <?php }} ?>
                                    </select>
                                 
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">PU Nipples <b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('pu_nipples')">Add New</button></label>
                                <select multiple="multiple" name="pu_nipples[]" id="pu_nipples" class="form-control js-example-basic-multiple">
                                    <option value="">Please select pu nipples</option>
                                    <?php if (!empty($pu_nipples)) { 
                                        if (!empty($single)) {
                                            $pu_nipples_selected = explode(",", $single->pu_nipples_id); 
                                        } else {
                                            $pu_nipples_selected = []; 
                                        }

                                        foreach ($pu_nipples as $pu_nipples_result) { ?>
                                            <option value="<?= $pu_nipples_result->id ?>" 
                                                <?php if (in_array($pu_nipples_result->id, $pu_nipples_selected)) { ?>selected="selected"<?php } ?>>
                                                <?= htmlspecialchars($pu_nipples_result->pu_nipples) ?>
                                            </option>
                                        <?php }
                                    } ?>
                                    
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Ejector Pin <b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('ejector_pin')">Add New</button></label>
                                <select name="ejector_pin" id="ejector_pin" class="form-control js-example-basic-multiple">
                                    <option value="">Please select ejector pin</option>
                                    <?php if (!empty($ejector_pin)){
                                    foreach ($ejector_pin as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->ejector_pin_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->ejector_pin ?>
                                        </option>
                                    <?php }} ?>
                                    </select>
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">I Bolt<b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('i_bolt')">Add New</button></label>
                                <select name="i_bolt" id="i_bolt" class="form-control js-example-basic-multiple">
                                    <option value="">Please select i bolt</option>
                                    <?php if (!empty($i_bolt)){
                                    foreach ($i_bolt as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->i_bolt_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->i_bolt ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Cord<b class="require">*</b>
                                <button type="button" class="btn add_option" onclick="addNewOption('cord')">Add New</button></label>
                                <select name="cord" id="cord" class="form-control js-example-basic-multiple">
                                    <option value="">Please select cord</option>
                                    <?php if (!empty($cord)){
                                    foreach ($cord as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->cord_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->cord ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">O Ring
                                <button type="button" class="btn add_option" onclick="addNewOption('o_ring')">Add New</button></label>
                                <select name="o_ring" id="o_ring" class="form-control js-example-basic-multiple">
                                    <option value="">Please select o ring</option>
                                    <?php if (!empty($o_ring)){
                                    foreach ($o_ring as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->o_ring_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->o_ring ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Inset Slot Plate
                                <button type="button" class="btn add_option" onclick="addNewOption('insert_slot_plate')">Add New</button></label>
                                <select name="insert_slot_plate" id="insert_slot_plate" class="form-control js-example-basic-multiple">
                                    <option value="">Please select insert slot plate</option>
                                    <?php if (!empty($insert_slot_plate)){
                                    foreach ($insert_slot_plate as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->insert_slot_plate_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->insert_slot_plate ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Core cylinder Seal
                                <button type="button" class="btn add_option" onclick="addNewOption('core_cylinder_seal')">Add New</button></label>
                                <select name="core_cylinder_seal" id="core_cylinder_seal" class="form-control js-example-basic-multiple">
                                    <option value="">Please select core cylinder seal</option>
                                    <?php if (!empty($core_cylinder_seal)){
                                    foreach ($core_cylinder_seal as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->core_cylinder_seal_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->core_cylinder_seal ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Seal
                                <button type="button" class="btn add_option" onclick="addNewOption('seal')">Add New</button></label>
                                <select name="seal" id="seal" class="form-control js-example-basic-multiple">
                                    <option value="">Please select seal</option>
                                    <?php if (!empty($seal)){
                                    foreach ($seal as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->seal_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->seal ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label class="add_opt">Hose Pipe
                                <button type="button" class="btn add_option" onclick="addNewOption('hose_pipe')">Add New</button></label>
                                <select name="hose_pipe" id="hose_pipe" class="form-control js-example-basic-multiple">
                                    <option value="">Please select hose pipe</option>
                                    <?php if (!empty($hose_pipe)){
                                    foreach ($hose_pipe as $type_of_mould_result) { ?>
                                        <option value="<?= $type_of_mould_result->id ?>"<?php if(!empty($single) && $single->hose_pipe_id == $type_of_mould_result->id){ ?> selected <?php } ?>>
                                            <?= $type_of_mould_result->hose_pipe ?>
                                        </option>
                                    <?php }} ?>
                                </select>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                             
                                <button type="submit" id="submit_btn" class="btn btn-primary">Next</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container row ">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label class="add_opp">Name<b class="require">*</b></label>
                                <input name="new_option" id="new_option" type="text" class="form-control" value="" placeholder="Enter new option" required>
                                <input name="master_type" id="master_type" type="hidden" class="form-control" value="">
                                <div class="error new_option_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <button id="add_option_btn" type="button" class="btn btn-primary">Submit</button>
                        </div>
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
       function addNewOption(master_type){
            $('#master_type').val(master_type);
            if(master_type == 'type_of_mould'){
                $('.modal-title').text('Add New Type Of Mould');  
                $('.add_opp').html('Type Of Mould<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new type of mould');   
            }else if(master_type == 'air_pin'){
                $('.modal-title').text('Add New Air Pin');
                $('.add_opp').html('Air Pin<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new air pin');
            }else if(master_type == 'spring'){
                $('.modal-title').text('Add New Spring');
                $('.add_opp').html('Spring<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new spring');
            }else if(master_type == 'pu_nipples'){
                $('.modal-title').text('Add New PU Nipples');
                $('.add_opp').html('PU Nipples<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new PU nipples');
            }else if(master_type == 'ejector_pin'){
                $('.modal-title').text('Add New Ejector Pin');
                $('.add_opp').html('Ejector Pin<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new ejector pin');
            }else if(master_type == 'i_bolt'){
                $('.modal-title').text('Add New I Bolt');
                $('.add_opp').html('I Bolt<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new I bolt');
            }else if(master_type == 'cord'){
                $('.modal-title').text('Add New Cord');
                $('.add_opp').html('Cord<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new cord');
            }else if(master_type == 'o_ring'){
                $('.modal-title').text('Add New O Ring');
                $('.add_opp').html('O Ring<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new O ring');
            }else if(master_type == 'insert_slot_plate'){
                $('.modal-title').text('Add New Insert Slot Plate');
                $('.add_opp').html('Insert Slot Plate<b class="require">*</b>');                
                $('#new_option').attr('placeholder', 'Enter new insert slot plate');    
            }else if(master_type == 'core_cylinder_seal'){
                $('.modal-title').text('Add New Core Cylinder Seal');
                $('.add_opp').html('Core Cylinder Seal<b class="require">*</b>');                
                $('#new_option').attr('placeholder', 'Enter new core cylinder seal');    
            }else if(master_type == 'hose_pipe'){
                $('.modal-title').text('Add New Hose Pipe');
                $('.add_opp').html('Hose Pipe<b class="require">*</b>');                
                $('#new_option').attr('placeholder', 'Enter new hose pipe');    
            }else if(master_type == 'seal'){
                $('.modal-title').text('Add New Seal');
                $('.add_opp').html('Seal<b class="require">*</b>');                
                $('#new_option').attr('placeholder', 'Enter new seal');    
            }else if(master_type == 'alankey_bolt'){
                $('.modal-title').text('Add New Alankey Bolt');
                $('.add_opp').html('Alankey Bolt<b class="require">*</b>');                
                $('#new_option').attr('placeholder', 'Enter new alankey bolt');
            }else if(master_type == 'group_of_article'){
                $('.modal-title').text('Add New Group Of Article');
                $('.add_opp').html('Group Of Article<b class="require">*</b>');
                $('#new_option').attr('placeholder', 'Enter new group of article'); 
                
            } else {
                $('.modal-title').text('Add New Option');  
                $('.add_opp').html('Option<b class="require">*</b>'); 
                $('#new_option').attr('placeholder', 'Enter new option');
            }
            $('#exampleModal').modal('show');
            
        }
        $('#add_option_btn').click(function(){
            var new_option = $('#new_option').val();
            var master_type = $('#master_type').val();
            if (/^\s/.test(new_option)) {
                $('.new_option_error').text('No spaces allowed at the start!');
                return false;
            }
            if(new_option == ''){
                if(master_type == 'type_of_mould'){
                    $('.new_option_error').text('Please enter type of mould!');
                    return false;
                }else if(master_type =='air_pin'){
                    $('.new_option_error').text('Please enter air pin!');
                    return false;
                }else if(master_type == 'spring'){
                    $('.new_option_error').text('Please enter spring!');
                    return false;
                }else if(master_type == 'pu_nipples'){
                    $('.new_option_error').text('Please enter PU nipples!');
                    return false;
                }else if(master_type == 'ejector_pin'){
                    $('.new_option_error').text('Please enter ejector pin!');
                    return false;
                }else if (master_type == 'i_bolt'){
                    $('.new_option_error').text('Please enter I bolt!');
                    return false;
                }else if(master_type == 'cord'){
                    $('.new_option_error').text('Please enter cord!');
                    return false;
                }else if(master_type == 'o_ring'){
                    $('.new_option_error').text('Please enter O ring!');
                    return false;
                }else if(master_type == 'insert_slot_plate'){
                    $('.new_option_error').text('Please enter insert slot plate!');
                    return false;
                }else if(master_type == 'core_cylinder_seal'){
                    $('.new_option_error').text('Please enter core cylinder seal!');
                    return false;
                }else if(master_type == 'seal'){
                    $('.new_option_error').text('Please enter seal!');
                    return false;
     
                }else if(master_type == 'hose_pipe'){
                    $('.new_option_error').text('Please enter hose pipe!');
                    return false;
                }else if(master_type == 'alankey_bolt'){
                    $('.new_option_error').text('Please enter alankey bolt!');
                    return false;
                }else if(master_type == 'group_of_article'){
                    $('.new_option_error').text('Please enter group of article!');
                    return false;
                }                
                else{
                    $('.new_option_error').text('Please enter new option!');
                }
            }else if(master_type != ''){
                    $.ajax({
                    type: 'POST',
                    url: '<?=base_url()?>admin/Ajax_controller/set_new_option',
                    data: {new_option: new_option, master_type: master_type},
                    success: function(data){
                        if(data == '0'){
                            if(master_type == 'type_of_mould'){
                                getAllTypeOfMould();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'air_pin'){
                                getAllAirPin();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'spring'){
                                getAllSpring();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'pu_nipples'){
                                getAllPuNipples();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'ejector_pin'){
                                getAllEjectorPin();
                                $('#exampleModal').modal('hide');
                            }else if (master_type == 'i_bolt'){
                                getAllIbolt();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'cord'){
                                getAllcord();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'o_ring'){
                                getAlloring();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'insert_slot_plate'){
                                getAllInsertSlotPlate();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'core_cylinder_seal'){
                                getAllCoreCylinderSeal();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'seal'){
                                getAllSeal();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'hose_pipe'){
                                getAllHosePipe();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'alankey_bolt'){
                                getAllAlankeyBolt();
                                $('#exampleModal').modal('hide');
                            }else if(master_type == 'group_of_article'){
                                getAllGroupOfArticle();
                                $('#exampleModal').modal('hide');
                            }else{
                                $('#exampleModal').modal('hide');
                            }
                        }else{
                            if(master_type == 'type_of_mould'){
                                $('.new_option_error').text('This type of mould already exist!');
                            }else if(master_type == 'air_pin'){
                                $('.new_option_error').text('This air pin already exist!');
                            }else if(master_type == 'spring'){
                                $('.new_option_error').text('This spring already exist!');
                            }else if(master_type == 'pu_nipples'){
                                $('.new_option_error').text('This PU nipples already exist!');
                            }else if(master_type == 'ejector_pin'){
                                $('.new_option_error').text('This ejector pin already exist!');
                            }else if (master_type == 'i_bolt'){
                                $('.new_option_error').text('This I bolt already exist!');
                            }else if(master_type == 'cord'){
                                $('.new_option_error').text('This cord already exist!');
                            }else if(master_type == 'o_ring'){
                                $('.new_option_error').text('This O ring already exist!');
                            }else if(master_type == 'insert_slot_plate'){
                                $('.new_option_error').text('This insert slot plate already exist!');
                            }else if(master_type == 'core_cylinder_seal'){
                                $('.new_option_error').text('This core cylinder seal already exist!');
                            }else if(master_type == 'seal'){
                                $('.new_option_error').text('This seal already exist!');
                            }else if(master_type == 'hose_pipe'){
                                $('.new_option_error').text('This hose pipe already exist!');
                            }else if(master_type == 'alankey_bolt'){
                                $('.new_option_error').text('This alankey bolt already exist!');
                            }else if(master_type == 'group_of_article'){
                                $('.new_option_error').text('This group of article already exist!');
                            }else{
                                $('.new_option_error').text('This option already exist!');
                            }
                        }
                    }
                }); 
            }

            
        })
        
        function getAllTypeOfMould(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_type_of_mould',
                success: function(data){
                    if(data != ''){
                        $('#type_of_mould').empty();
                        $('#type_of_mould').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#type_of_mould').append('<option value="' + d.id + '">' + d.type_of_mould + '</option>');
                        });
                        $('#type_of_mould').trigger('chosen:updated');
                    }
                }
            });
        }
        function getAllAirPin(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_air_pin',
                success: function(data){
                    if(data != ''){
                        $('#air_pin').empty();
                        $('#air_pin').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#air_pin').append('<option value="' + d.id + '">' + d.air_pin + '</option>');
                        });
                        $('#air_pin').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllSpring(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_air_spring',
                success: function(data){
                    if(data != ''){
                        $('#spring').empty();
                        $('#spring').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#spring').append('<option value="' + d.id + '">' + d.spring + '</option>');
                        });
                        $('#spring').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllPuNipples(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_pu_nipples',
                success: function(data){
                    if(data != ''){
                        $('#pu_nipples').empty();
                        $('#pu_nipples').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#pu_nipples').append('<option value="' + d.id + '">' + d.pu_nipples + '</option>');
                        });
                        $('#pu_nipples').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllEjectorPin(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_ejector_pin',
                success: function(data){
                    if(data != ''){
                        $('#ejector_pin').empty();
                        $('#ejector_pin').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#ejector_pin').append('<option value="' + d.id + '">' + d.ejector_pin + '</option>');
                        });
                        $('#ejector_pin').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllIbolt(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_i_bolt',
                success: function(data){
                    if(data != ''){
                        $('#i_bolt').empty();
                        $('#i_bolt').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#i_bolt').append('<option value="' + d.id + '">' + d.i_bolt + '</option>');
                        });
                        $('#i_bolt').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllcord(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_cord',
                success: function(data){
                    if(data != ''){
                        $('#cord').empty();
                        $('#cord').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#cord').append('<option value="' + d.id + '">' + d.cord + '</option>');
                        });
                        $('#cord').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAlloring(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_o_ring',
                success: function(data){
                    if(data != ''){
                        $('#o_ring').empty();
                        $('#o_ring').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#o_ring').append('<option value="' + d.id + '">' + d.o_ring + '</option>');
                        });
                        $('#o_ring').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllInsertSlotPlate(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_insert_slot_plate',
                success: function(data){
                    if(data != ''){
                        $('#insert_slot_plate').empty();
                        $('#insert_slot_plate').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#insert_slot_plate').append('<option value="' + d.id + '">' + d.insert_slot_plate + '</option>');
                        });
                        $('#insert_slot_plate').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllCoreCylinderSeal(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_core_cylinder_seal',
                success: function(data){
                    if(data != ''){
                        $('#core_cylinder_seal').empty();
                        $('#core_cylinder_seal').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#core_cylinder_seal').append('<option value="' + d.id + '">' + d.core_cylinder_seal + '</option>');
                        });
                        $('#core_cylinder_seal').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllSeal(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_seal',
                success: function(data){
                    if(data != ''){
                        $('#seal').empty();
                        $('#seal').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#seal').append('<option value="' + d.id + '">' + d.seal + '</option>');
                        });
                        $('#seal').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllHosePipe(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_hose_pipe',
                success: function(data){
                    if(data != ''){
                        $('#hose_pipe').empty();
                        $('#hose_pipe').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#hose_pipe').append('<option value="' + d.id + '">' + d.hose_pipe + '</option>');
                        });
                        $('#hose_pipe').trigger('chosen:updated')
                    }
                }
            });
        }
        function getAllAlankeyBolt(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_alankey_bolt',
                success: function(data){
                    if(data != ''){
                        $('#alankey_bolt').empty();
                        $('#alankey_bolt').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#alankey_bolt').append('<option value="' + d.id + '">' + d.alankey_bolt + '</option>');
                        });
                        $('#alankey_bolt').trigger('chosen:updated');
                    }
                }
            });
        }
        function getAllGroupOfArticle(){
            $.ajax({
                type: 'POST',
                url: '<?=base_url()?>admin/Ajax_controller/get_all_group_of_article',
                success: function(data){
                    if(data != ''){
                        $('#group_of_article').empty();
                        $('#group_of_article').append('<option value="">Please Select</option>');
                        var opts = $.parseJSON(data);
                        $.each(opts, function (i, d) {
                            $('#group_of_article').append('<option value="' + d.id + '">' + d.group_of_article + '</option>');
                        });
                        $('#group_of_article').trigger('chosen:updated');
                    }
                }
            });
        }
        
        $('#new_option').keyup(function(){
            $('.new_option_error').text('');
        });
        $('#exampleModal').on('hidden.bs.modal', function() {
            $('#new_option').val('');  
            $('.new_option_error').text('');  
        });

        $(document).ready(function() {
            // $('#master .child_menu').show();
            $('#master').addClass('nv active');
            // $('.right_col').addClass('active_right');
            $('.add_article').addClass('active_cc');
            // $('#master').addClass('nv active-color');
        });
    </script>
    <script>
        $(document).ready(function() {

        $(".js-example-basic-multiple").select2({});
        $('#air_pin').select2({
            placeholder: "Please select air pin"
        });
        $('#pu_nipples').select2({
            placeholder: "Please select pu nipples"
        });
        $('#alankey_bolt').select2({
            placeholder: "Please select alankey bolt"
        });
        });
    </script>

    <script>
        
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        }, "First letter can not be space");
        jQuery.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });
        $(document).ready(function() {
            $('#add_article').validate({
                ignore: [],
                rules: {
                    article_name:{
                        required: true,
                        noSpaceAtStart: true,
                    },
                    group_of_article: {
                        required: true
                    },
                    type_of_mould: {
                        required: true
                    },
                    'air_pin[]': {
                        required: true
                    },
                    reorder_level: {
                        required: true
                    },
                    'pu_nipples[]':{
                        required: true
                    },
                    ejector_pin: {
                        required: true
                    },
                    i_bolt: {
                        required: true
                    },
                    cord: {
                        required: true
                    },
                    // o_ring: {
                    //     required: true
                    // },
                    // insert_slot_plate: {
                    //     required: true
                    // },
                    // core_cylinder_seal: {
                    //     required: true
                    // },
                    // seal: {
                    //     required: true
                    // },
                    // hose_pipe: {
                    //     required: true
                    // },
                    'alankey_bolt[]': {
                        required: true
                    }
                },
                messages: {
                    article_name: {
                        required: 'Please enter article name!',
                        noSpaceAtStart: 'Article name should not start with space!'
                    },
                    group_of_article: {
                        required: 'Please select group of article!'
                    },
                    type_of_mould: {
                        required: 'Please select type of mould!'
                    },
                    reorder_level: {
                        required: 'Please enter article reorder level!'
                    },
                    'air_pin[]': {
                        required: 'Please select air pin!'
                    },
                    spring: {
                        required: 'Please select spring!'
                    },
                    'pu_nipples[]': {
                        required: 'Please select pu nipples!'
                    },
                    ejector_pin: {
                        required: 'Please select ejector pin!'
                    },
                    i_bolt: {
                        required: 'Please select i bolt!'
                    },
                    cord: {
                        required: 'Please select cord!'
                    },
                    o_ring: {
                        required: 'Please select o ring!'
                    },
                    insert_slot_plate: {
                        required: 'Please select insert slot plate!'
                    },
                    core_cylinder_seal: {
                        required: 'Please select core cylinder seal!'
                    },
                    seal: {
                        required: 'Please select seal!'
                    },
                    hose_pipe: {
                        required: 'Please select hose pipe!'
                    },
                    'alankey_bolt[]': {
                        required: 'Please select alankey bolt!'
                    }


                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
            $("#type_of_mould").change( function() {
                $("#type_of_mould").valid();
            }); 
            
            $('#air_pin').change( function() {
                $("#air_pin").valid();
            });
            $('#ejector_pin').change( function() {
                $("#ejector_pin").valid();
            });
           
            $('#spring').change( function() {
                $("#spring").valid();
            });
            $('#pu_nipples').change( function() {
                $("#pu_nipples").valid();
            });
            $('#alankey_bolt').change( function() {
                $("#alankey_bolt").valid();
            });
            $('#i_bolt').change( function() {
               $("#i_bolt").valid();
            });
            $('#cord').change( function() {
                $("#cord").valid();
            });
            $('#o_ring').change( function() {
                $("#o_ring").valid();
            });
            $('#insert_slot_plate').change( function() {
                $("#insert_slot_plate").valid();
            });
            $('#core_cylinder_seal').change( function() {
                $("#core_cylinder_seal").valid();
            });
            $('#seal').change( function() {
                $("#seal").valid();
            });
            $('#hose_pipe').change( function() {
                $("#hose_pipe").valid();
            });
            $('#group_of_article').change( function() {
                $("#group_of_article").valid();
            });
           
        })
</script>

    <script>
        $('#article_name').on('keyup', function(){
        var article_name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_article_name', 
            method: 'post',
            data: {
                'article_name': article_name,
                'id': '<?= $id ?>'
            },
            success: function(response) {
                if(response == '1' ){
                  $('#name_error').text("This Article name is already added!");
                  $('#name_error').addClass('error');
                  $('#submit_btn').prop('disabled',true);
                }else{
                $('#name_error').text("");
                $('#submit_btn').prop('disabled',false);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
    </script>

