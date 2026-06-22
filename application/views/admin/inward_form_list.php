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

    .modelclass {
        max-width: 40%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">
    <div class="table">
        <div class="page-title">
            <div class="title_left">
                <h3>Inword Form List</h3>
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
                                    <th>Inward Number</th>
                                    <th>Inward Date</th>
                                    <th>Supplier Name</th>
                                    <th>Plant Name</th>
                                    <th>Gate Entry No.</th>
                                    <th>Gate Entry Date</th>
                                    <th>Extra Charges Details</th>
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
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>SR. NO.</th>
                            <th>Inward Number</th>
                            <th>Extra Charges For</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="order-details-table">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php');
?>
<script>
    $(document).ready(function () {
        $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
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
                title: 'Inward Form List',
                filename: 'inward_form_list',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_inward_form_list",
                "type": "POST",
            },
            "createdRow": function (row, data, dataIndex) {
                var memberId = data[7];
                var eyeButton = `
                <button type="button" class="btn btn-info" onclick="showOrderDetails('${memberId}')" title="Extra Charges Details">
                    <i class="fa fa-eye"></i>
                </button>
            `;
                $('td', row).eq(7).html(eyeButton);

            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });


    });

    function showOrderDetails(memberId) {
        $.ajax({
            url: '<?= base_url("admin/Ajax_controller/get_extra_charges_details") ?>',
            type: 'POST',
            data: { 'database_inward_id': memberId },
            dataType: 'json',
            success: function (response) {
                if (Array.isArray(response) && response.length > 0) {
                    $('#order-details-table').empty();
                    var tableContent = '';
                    response.forEach(function (item, index) {
                        tableContent += `<tr>`;
                        tableContent += `<td>${index + 1}</td>`;
                        tableContent += `<td>${item.inward_no}</td>`;
                        tableContent += `<td>${item.extra_payment_option}</td>`;
                        tableContent += `<td>${item.trap_hamali_amount}</td>`;
                        tableContent += `</tr>`;
                    });
                    $('#order-details-table').html(tableContent);

                    $('#exampleModal').modal('show');
                } else {
                    alert('No details found for this inward!');
                }

            },
        });
    }
</script>