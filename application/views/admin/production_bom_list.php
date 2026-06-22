<?php include('header.php'); ?>
<style type="text/css">
    .error {
        color: red;
        float: left;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Production BOM List</h3>
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
                                    <th>SR. No.</th>
                                    <th>Articles</th>
                                    <th>Batch</th>
                                    <th>Weight (Per Batch)</th>
                                    <th>Raw Material</th>
                                    <th>Raw Material 2</th>
                                    <th>Other RM</th>
                                    <th>Master batch</th>
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
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.production_bom_list').addClass('active_cc');
        // $('#master').addClass('nv active-color');
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
        "serverSide": true,
        "cache": false,
        "order": [],
        "ordering": false,     
        dom: "Blfrtip",
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'production_bom_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4,5,6,7]
                    }
                }
            ],
        //scrollX: true, 
        scrollCollapse: true,
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_production_bom_list",
            "type": "POST",
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    

</script>
