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

    .right_col .page_title,
    .right_col .page_body {
        padding: -1px 8px;
    }

    .page_sec {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
        height: auto;
        margin-left: -10px;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;
    }

    h3 {
        margin: 9px 0;
        font-size: 18px;
        font-weight: 800;
        color: #0056d0;
    }
</style>
<div class="right_col">
    <h3>Vehical List Details</h3>
    <div class="main_page">
        <div class="page_title">

        </div>
        <div class="page_body">

            <div class="x_panel">
                <table class="table" style="width: 100%;" id="example">
                    <thead>
                        <tr>
                            <th>SR NO.</th>
                            <th>Vehical</th>
                            <th>Challan DC No</th>
                            <th>Invoice No</th>
                            <th>Location</th>
                            <th>Pincode</th>
                            <th>Purpose</th>
                            <th>Party Name</th>
                            <th>In KM</th>
                            <th>Market Freight</th>
                            <th>Diesel Top-up</th>
                            <th>Driver Expense</th>
                            <th>Maintenance</th>
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


<?php include('footer.php'); ?>
<!-- <script type="text/javascript" src="http://localhost:81/krivisha/assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://localhost:81/krivisha/assets/js/datepicker/daterangepicker.js"></script> -->


<script>
    $(document).ready(function() {
        $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.own_vehicle_list').addClass('active_cc');
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            'searching': true,
            "processing": true,
            "serverSide": true,
            'ordering': false,
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                filename: 'Vehical Detail List',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
                }
            }],
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_all_vehical_list_details",
                type: "POST",
                // data: function(d) {
                //     d.search_date = $("#date").val();
                //     d.search_mwo_code = $("#mwo_code").val();
                //     d.search_status_of_work = $("#status_of_work").val();
                //     d.search_material_used_for_maintenance = $("#material_used_for_maintenance").val();
                //     d.search_maintain_action = $("#maintain_action").val();
                // },
                complete: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                cache: false
            }
        });
        $('#maintenance_list').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
</script>