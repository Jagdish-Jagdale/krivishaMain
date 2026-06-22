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
    td{
        padding: 0 !important;
    }
    td select{
        width: 100%;
        height: 100%;padding: 2px;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Production BOM Details</h3>
            </div>

        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="dataTable">
                            <thead class="thead">
                                <tr>
                                   <th>Sizes of Parts Of Material</th>
                                   <th>Parts of articles</th>
                                   <th>UOM</th>
                                   <th>Qty</th>

                                    <!-- <th>Action</th> -->
                                </tr>
                            </thead>


                            <tbody>
                                <tr>
                                    <td><input type="text" value="A" readonly></td>
                                    <td><input type="text" readonly value="ABC"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td>
                                 
                                    <!-- <td> <a onclick="return confirm('Are you sure to inactivate this record?');" class="btn btn-info" href="<?= base_url() ?>"><i class="fa-solid fa-times-circle"></i></a>
                                        <a class="btn btn-danger" onclick="return confirm('Are you sure to delete this record?');" href="<?= base_url() ?>"><i class="fa-solid fa-trash"></i></a>
                                        <a class="btn btn-success" href="<?= base_url() ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                                    </td> -->


                                    <!-- <td class="text-center"><a class="icon_link" href="<?= base_url() ?>"><i class="fa fa-eye" aria-hidden="true"></i></a></td> -->
                                </tr>
                                <tr>
                                    <td><input type="text" value="B" readonly></td>
                                    <td><input type="text" readonly value="ABC"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td>
                                 
                                    <!-- <td> <a onclick="return confirm('Are you sure to inactivate this record?');" class="btn btn-info" href="<?= base_url() ?>"><i class="fa-solid fa-times-circle"></i></a>
                                        <a class="btn btn-danger" onclick="return confirm('Are you sure to delete this record?');" href="<?= base_url() ?>"><i class="fa-solid fa-trash"></i></a>
                                        <a class="btn btn-success" href="<?= base_url() ?>"><i class="fa-solid fa-pen-to-square"></i></a>
                                    </td> -->


                                    <!-- <td class="text-center"><a class="icon_link" href="<?= base_url() ?>"><i class="fa fa-eye" aria-hidden="true"></i></a></td> -->
                                </tr>
                                <tr>
                                    <td><input type="text" value="C" readonly></td>
                                    <td><input type="text" readonly value="ABC"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                 <tr>
                                    <td><input type="text" value="1" readonly></td>
                                    <td><input type="text" readonly value="EFG"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                 <tr>
                                    <td><input type="text" value="2" readonly></td>
                                    <td><input type="text" readonly value="EFG"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                 <tr>
                                    <td><input type="text" value="3" readonly></td>
                                    <td><input type="text" readonly value="EFG"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                        <tr>
                                    <td><input type="text" value="X" readonly></td>
                                    <td><input type="text" readonly value="CDE"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                 <tr>
                                    <td><input type="text" value="Y" readonly></td>
                                    <td><input type="text" readonly value="CDE"></td>
                                    <td><select name="uom_dropdown" id="uom_dropdown">
                                        <option value="">Please Select</option>
                                        <option value="1">Option 1</option>
                                        <option value="2">Option 2</option>
                                        <option value="3">Option 3</option>
                                    </select></td>
                                    <td><input type="text"></td> 
                                       </tr>
                                
                                
                                
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<?php include('footer.php'); ?>



<script>
    $(document).ready(function() {
        $('#master .child_menu').show();
        $('#master').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.production_bom_list').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function() {
       var table = $('#dataTable').DataTable();

    });
</script>