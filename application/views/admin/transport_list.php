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
                <h3>Transport List</h3>
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
                                    <th>Transport Name</th>
                                    <th>Mobile No 1</th>
                                    <th>Mobile No 2</th>
                                    <th>Transport ID</th>
                                    <th>City</th>
                                    <th>Transporter Rating</th>
                                    <th>Action</th>
                                </tr>
                            </thead>


                           
                    </div>
                </div>
            </div>
        </div>
    </div>
    
 
</div>

<?php include('footer.php'); ?>



<script>
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.transport_list').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
        
        'searching': true,
        "processing": true,
        "serverSide": true,
        "cache": false,
       
        
        dom: "Blfrtip",
        ordering: false,
        
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'transport_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4,5,6]
                    }
                }
            ],
        
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_transport_list",
            "type": "POST",
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    

</script>