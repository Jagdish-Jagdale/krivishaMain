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
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Inword Form List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Inward Number</th>
                                    <th>Inward Date</th>
                                    <th>Supplier Name</th>
                                    <th>Gate Entry No.</th>
                                    <th>Gate Entry Date</th>

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>IN/2024-25/1</td>
                                    <td>2023-10-01</td>
                                    <td>D N PAINTS</td>
                                    <td>GE12345</td>
                                    <td>2023-10-01</td>

                                    <td><span class="inline_btns">
                                            <a href="#" class="btn btn-primary"
                                                onclick="window.location.href='<?= base_url() ?>view_inword_form'">View</a>

                                            <a href="#" class="btn btn-danger">Delete</a>
                                        </span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include('footer.php');
?>
<script>
    $(document).ready(function() {
        $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            // "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,

            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'brand_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],
            scrollCollapse: true,
            // "ajax": {
            //     "url": "<?= base_url() ?>admin/Ajax_controller/get_all_inward_form_list",
            //     "type": "POST",
            // },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });


    });
</script>