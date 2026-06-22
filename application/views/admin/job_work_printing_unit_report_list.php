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
        <h3>Job Work Report List</h3>
        <div class="main_page">
            <div class="page_title">

            </div>
            <div class="page_body">

                <div class="x_panel">
                    <table class="table" style="width: 100%;" id="example">
                        <thead>
                            <tr>
                                <th>SR. NO.</th>
                                <th>Order ID</th>
                                <th>Size</th>
                                <th>Brand Name</th>
                                <th>Quantity</th>
                                <th>Color Job</th>
                            
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

    <script>
        $(document).ready(function () {
            // $('#printing_unit .child_menu').show();
            // $('#printing_unit').addClass('nv active');
            // $('.right_col').addClass('active_right');
            // $('.job_work_printing_unit_report_list').addClass('active_cc');
            $('#printing_unit').addClass('nv active-color');
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
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }],
                ajax: {
                    url: "<?= base_url() ?>admin/Ajax_controller/get_all_job_work_printing_unit_details",
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