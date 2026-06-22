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
                <h3>Master Batch List</h3>
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
                                    <th>Name</th>
                                    <th>ALIAS</th>
                                    <th>BASE</th>
                                    <th>MAKE</th>
                                    <!-- <th>STATUS</th> -->
                                    

                                    <th>Action</th>
                                </tr>
                            </thead>

                            
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
        $('.mb_list').addClass('active_cc');
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
                    filename: 'mb_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4]
                    }
                }
            ],
        //scrollX: true, 
        scrollCollapse: true,
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_mb_list",
            "type": "POST",
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    

</script>