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
                <h3>Brand List</h3>
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
                                    <th>Brand Name</th>
                                    <th>Type</th>
                                    <th>Party Name</th>
                                    <th>Department Name</th>
                                    <th>Ink</th>
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
<input type="hidden" id="custom" value="<?= $this->input->get('brand_filter') ?? $this->input->post('brand_filter') ?? '' ?>">
<?php include('footer.php');
?>
<script>
    $(document).ready(function () {
        // $('#master .child_menu').show();
        $('#master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.brand_list').addClass('active_cc');
        // $('#master').addClass('nv active-color');
    });
</script>
<script>
    $(document).ready(function () {
        var table = $('#example').DataTable({
            "lengthChange": true,
            "responsive": false,
            "scrollX": true,
            "lengthMenu": [10, 25, 50, 100],
            'searching': true,
            "processing": true,
            "serverSide": true,
            "cache": false,
            "order": [],
            "ordering": false,
            columnDefs: [{
                targets: '_all',
                className: 'tbl-min-width'

            }],
            dom: "Blfrtip",
            buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'brand_list',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4,5]
                    }
                }
            ],
            scrollCollapse: true,
            "ajax": {
                "url": "<?= base_url() ?>admin/Ajax_controller/get_all_brands_list",
                "type": "POST",
                "data": function (d) {
                    d.brand_filter = $('#custom').val();
                }
                
            },
            "complete": function () {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    });
</script>