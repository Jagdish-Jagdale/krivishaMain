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
<div class="right_col" role="main">

    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Party List</h3>
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
                                    <th>Party Name</th>
                                    <th>Party Type</th>
                                    <th>Mobile NO</th>
                                    <th>GSTIN/PAN</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Contact Person Name</th>
                                    <th>Designation</th>
                                    <th>Secondary Contact Person Name</th>
                                    <th>Secondary Designation</th>
                                    <th>Division</th>
                                    <th>Attending Salesperson</th>
                                    <th>Nature of Business</th>
                                    <th>Type Of Business</th>
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
<input type="hidden" name="party_filter" id="party_filter" value="<?php if (isset($_GET['party_filter'])) {
    echo $_GET['party_filter'];
} ?>">
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
        $('.customer_list').addClass('active_cc');
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
        scrollX:true,
        
        ordering: false,
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'customer_list',
                    exportOptions: {
                        columns: [0, 1, 2,3,4,5,6,7,8,9,10,11,12,13,14]
                    }
                }
            ],
        
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_parties_list",
            "type": "POST",
            "data": function ( d ) {
                d.party_filter = $('#party_filter').val();
            }
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
</script>