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

    table{
        table-layout: fixed;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Article List</h3>
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
                                    <th>Article/ Mould Name</th>
                                    <th>Group of Article</th>
                                    <th>Sizes of Parts Of Mould</th>
                                    <th>Parts of Mould</th>
                                    <th>UOM</th>
                                    <th>Quantity</th>

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

<?php include('footer.php'); ?>
<script>
    $(document).ready(function() {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.article_list').addClass('active_cc');
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
        layout:{
                topStart: 'pageLength',
                topStart: 'buttons',
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging'
            },
        dom: "Blfrtip",
        ordering :false,
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'article_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4,5,6]
                    }
                }
            ],
        scrollX: true, 
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_maintance_bom_list",
            "type": "POST",
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    
</script>