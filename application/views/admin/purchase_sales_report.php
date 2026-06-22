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

    table.dataTable th {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;

    }

    .table.dataTable td.dataTables_empty,
    table.dataTable th.dataTables_empty {

        text-align: start !important;
        padding-left: 20%;
    }

    #example_wrapper {
        /* overflow-x: auto !important; */
    }
</style>
<div class="right_col">
    <h3>Purchase Report</h3>
    <div class="main_page">
        <div class="page_body">
            <div class="page_sec">
                <form action="" method="POST" enctype="multipart/form-data">
                    <label>Upload CSV File:</label>
                    <input type="file" name="csv_file" required>
                    <button type="submit" id="submit_btn" name="submit_btn" value="submit_btn"
                        class="btn btn-primary">Upload</button>
                </form>
            </div>
            <div class="x_panel">
                <table class="table" id="example">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Supplier Address</th>
                            <th>Consignee</th>
                            <th>Plant Name</th>
                            <th>Supplier Invoice No</th>
                            <th>Supplier Invoice Date</th>
                            <th>GSTIN/UIN</th>
                            <th>PAN No</th>
                            <th>Order No & Date</th>
                            <th>Terms of Payment</th>
                            <th>Receipt Note No & Date</th>
                            <th>Receipt Doc./LR. No</th>
                            <th>Dispatch Through</th>
                            <th>Destination</th>
                            <th>Article Name</th>
                            <th>Rate</th>
                            <th>Value</th>
                            <th>Addl. Cost</th>
                            <th>Taxes GST</th>
                            <th>Gross Total</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>

<script>
    $(document).ready(function() {
        // $('#purchase_and_sales .child_menu').show();
        $('#purchase_and_sales').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.purchase_sales_report').addClass('active_cc');
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
            // scrollCollapse: true,
            scrollX: true,
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'Purchase List',
                filename: 'purchase_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21]
                }
            }],
            ajax: {
                url: "<?= base_url() ?>admin/Ajax_controller/get_all_purchase_sales_report_details",
                type: "POST",
                complete: function() {
                    $('[data-toggle="tooltip"]').tooltip();
                },
                cache: false
            }
        });
    });
</script>