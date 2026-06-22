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
                <h3>View Details</h3>
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
                                    <th>Request Number</th>
                                    <th>Article Name</th>
                                    <th>Requested Qty</th>
                                    <th>Received Qty</th>
                                    <th>Request Status</th>
                                    <th>Action</th>

                                </tr>
                            </thead>

                            <tbody>

                            </tbody>
                        </table>
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
                title: 'Request Article List',
                filename: 'request_article_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4,]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_article_qty_request_data_list",
                "type": "POST",
                 "data": function(d) {
                    d.request_id = "<?= $id ?>";
                }
            },
            "complete": function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>