<?php include('header.php') ?>

<style type="text/css">
.error { color: red; float: left; }
.flex_wrap { display: flex; flex-wrap: wrap; }
.select2-container { max-width: 100% !important; }
</style>

<div class="right_col" role="main">
    <div class="row">
        <div class="col-md-4">
            <div class="page-title">
                <div class="title_left">
                <h3>
                <?php if (!empty($single)) { ?> Update Impression Rate <?php } else { ?> Add Impression Rate <?php } ?>
                </h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_rate_form" id="add_rate_form" action="<?= base_url('impression_rate') ?>">
                            <div class="row flex_wrap">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Article Name<b class="require">*</b></label>
                                    <select class="form-control js-example-basic-single" name="article_id" id="article_id" style="display: none; width: 100%;" required>
                                        <option value="">Select Article</option>
                                        <?php if (!empty($articles)) {
                                            foreach ($articles as $article) { ?>
                                                <option value="<?= $article->id ?>" <?php if (!empty($single) && $single['article_id'] == $article->id) echo 'selected'; ?>><?= $article->article_name ?></option>
                                        <?php } } ?>
                                    </select>
                                    <input autocomplete="off" type="hidden" name="id" value="<?php if (!empty($single)) { echo $single['id']; } ?>">
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Impression Rate<b class="require">*</b></label>
                                    <input autocomplete="off" name="impression_rate" type="number" step="0.01" class="form-control" value="<?php if (!empty($single)) { echo $single['impression_rate']; } ?>" placeholder="Enter Rate" required>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" class="btn btn-primary">Submit</button>
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
                    <h3>Impression Rates</h3>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Article Name</th>
                                    <th>Impression Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rates)) { 
                                    $i = 1;
                                    foreach ($rates as $row) { ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= $row->article_name ?></td>
                                        <td><?= number_format($row->impression_rate, 2) ?></td>
                                        <td>
                                            <a href="<?= base_url('impression_rate/'.$row->id) ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="<?= base_url('delete/'.$row->id.'/tbl_impression_rate') ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php } } ?>
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
    $('#master').addClass('nv active');
    $('.impression_rate').addClass('active_cc');
    $(".js-example-basic-single").select2({
        placeholder: "Select Article",
        allowClear: true
    });

    $('#add_rate_form').validate({
        rules: {
            article_id: { required: true },
            impression_rate: { required: true, number: true },
        },
        messages: {
            article_id: { required: "Please select an article" },
            impression_rate: { required: "Please enter a valid rate" },
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback error');
            element.closest('.form-group').append(error);
        }
    });

    $('#example').DataTable({
        "dom": "Blfrtip",
        "buttons": ['excel'],
		"lengthChange": true,
        "lengthMenu": [10, 25, 50, 100],
    });
});
</script>
