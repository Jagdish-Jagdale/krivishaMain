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


.multi-select {
    display: none;
}
</style>
<!-- page content -->
<div class="right_col" role="main">


    <div class="row">
        <div class="col-md-4">
            <div class="page-title">
                <div class="title_left">
                    <h3>Add Printing</h3>
                </div>

            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_printing_form" id="add_printing_form"
                            enctype="multipart/form-data">

                            <div class="container row ">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <label class="add_opt">Printing Name<b class="require">*</b></label>
                                        <input name="new_option" id="new_option" type="text" class="form-control"
                                            value="" placeholder="Enter printing name" required>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <button id="add_option_btn" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="page-title">
                <div class="title_left">
                    <h3>Printing List</h3>
                </div>

            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="dataTable">
                            <thead class="thead">
                                <tr>
                                    <th>SR NO.</th>
                                    <th>Printing Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>


                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Printing 1</td>
                                    <td>
                                        <a class="btn btn-danger"
                                            onclick="return confirm('Are you sure to delete this record?');"
                                            href="<?= base_url() ?>"><i class="fa-solid fa-trash"></i></a>
                                        <a class="btn btn-success" href="<?= base_url() ?>"><i
                                                class="fa-solid fa-pen-to-square"></i></a>
                                    </td>
                                <tr>

                                <tr>
                                    <td>2</td>
                                    <td>Printing 1</td>
                                    <td>
                                        <a class="btn btn-danger"
                                            onclick="return confirm('Are you sure to delete this record?');"
                                            href="<?= base_url() ?>"><i class="fa-solid fa-trash"></i></a>
                                        <a class="btn btn-success" href="<?= base_url() ?>"><i
                                                class="fa-solid fa-pen-to-square"></i></a>
                                    </td>
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

    $(".js-example-basic-multiple").select2({});


});
</script>

<script>
$(document).ready(function() {
    $('#master .child_menu').show();
    $('#master').addClass('nv active');
    $('.right_col').addClass('active_right');
    $('.add_printing').addClass('active_cc');
});
</script>