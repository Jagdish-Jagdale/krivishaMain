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
                <h3>Task Update List</h3>
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
                                    <th>SR NO.</th>
                                    
                                    <th>Task ID</th>
                                    <th>Your Name</th>
                                    <th>Task Status </th>
                                    <th>Task Action</th>
                                    <th>Additional Comments/ Updates</th>
                                    <th>Person To</th>
                                    <th>Assign To Department</th>
                                    

                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>CC100</td>
                                    <td>Ganesh Waghmare</td>
                                    <td>Closed</td>
                                    <td>Mark as Closed</td>
                                    <td>comments</td>
                                    <td>Ganesh Waghmare</td>
                                    <td>Accounts</td>
                                 
                                    <td>
                                        <a class="btn btn-success" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a class="btn btn-danger" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                    </td>

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
$id = 0;
if ($this->uri->segment(2) != "") {
    $id = $this->uri->segment(2);
}
?>


<script>
    $(document).ready(function() {
        $('#task_management .child_menu').show();
        $('#task_management').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.task_update_list').addClass('active_cc');
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
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'task_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4,5,6,7,8,9]
                    }
                }
            ],
        //scrollX: true, 
        scrollCollapse: true,
        // "ajax": {
        //     "url": "<?=base_url()?>admin/Ajax_controller/get_all_location_list",
        //     "type": "POST",
        // },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    

</script>