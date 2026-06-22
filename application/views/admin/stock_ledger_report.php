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
    max-width: 100% !important;
}

.right_col .page_title,
.right_col .page_body {
    padding: -1px 8px;
    color: #000;
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
    align-items: center;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 8px;
}

.inline-btns-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.modelclass {
    max-width: 60%;
    width: auto;
}

.content_body {
    padding: 20px;
    text-align: center;
}

.table thead th {
    background: #eaf2ff !important;
    color: #333;
    font-weight: 700;
    font-size: 12px;
    padding: 10px 8px;
    border: 1px solid #d1d9e6;
    text-align: center;
}

.table td {
    border: 1px solid #d1d9e6 !important;
    padding: 8px;
    color: #000;
    font-size: 12px;
}

h3 {
    margin: 9px 0;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    background: #0056d0;
    color: #fff !important;
    padding: 10px 15px;
    border-radius: 4px 4px 0 0;
    text-align: left;
    letter-spacing: 0.5px;
}

.dataTables_length {
    margin: 0 !important;
    font-size: 13px;
    color: #000;
    display: inline-flex !important;
    align-items: center !important;
    width: auto !important;
    float: none !important;
    clear: both !important;
    padding: 0 !important;
}

.dataTables_length label {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    white-space: nowrap !important;
    font-weight: normal !important;
    gap: 5px !important;
    margin: 0 !important;
    padding: 0 !important;
    height: 30px !important;
    line-height: 30px !important;
}

.dataTables_length select {
    padding: 4px 8px !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    margin: 0 5px !important;
    display: inline-block !important;
    width: auto !important;
    height: 30px !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
}

.top {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 10px !important;
    width: 100% !important;
    height: 30px !important;
}

.top .dataTables_length,
.top .dataTables_filter {
    margin: 0 !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    height: 30px !important;
}

.dataTables_filter {
    margin: 0 !important;
    font-size: 13px;
    color: #000;
    display: inline-flex !important;
    align-items: center !important;
    width: auto !important;
    float: none !important;
    clear: both !important;
    padding: 0 !important;
}

.dataTables_filter label {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    white-space: nowrap !important;
    font-weight: normal !important;
    gap: 5px !important;
    margin: 0 !important;
    padding: 0 !important;
    height: 30px !important;
    line-height: 30px !important;
}

.dataTables_filter input {
    padding: 4px 8px !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    margin: 0 0 0 5px !important;
    color: #000 !important;
    width: 180px !important;
    height: 30px !important;
    display: inline-block !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
}

.dt-buttons button {
    padding: 6px 12px;
    font-size: 12px;
    margin-right: 5px;
}

@media print {
    .btn, form, .page_sec {
        display: none !important;
    }
    body {
        background: white;
    }
    table {
        border: 1px solid #000;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
    }
}
</style>
<div class="right_col">
    <h3>Stock Ledger Report</h3>
    <div class="main_page">
        <div class="page_title">
        </div>
        <div class="page_body">
            <div class="page_sec">
                <form method="get" name="stock_ledger_form" id="stock_ledger_form" class="no-global-disable" enctype="multipart/form-data">
                    <div class="row flex_wrap">
                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="report_type">Report Type</label>
                            <select class="form-control" name="report_type" id="report_type">
                                <option value="raw_material" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'raw_material') ? 'selected' : '' ?>>Raw Material</option>
                                <option value="article" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'article') ? 'selected' : '' ?>>Article</option>
                                <option value="color" <?= (isset($_GET['report_type']) && $_GET['report_type'] == 'color') ? 'selected' : '' ?>>Master batch</option>
                            </select>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 d-3 mb-3 form-group">
                            <label>Date Range</label>
                            <input name="date" id="date" class="form-control"
                                placeholder="Select Date Range" value="<?php if (isset($_GET['date']) && $_GET['date'] != '') {
                                    echo $_GET['date'];
                                } ?>">
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12">
                            <label for="plant">Plant</label>
                            <select class="form-control js-example-basic-multiple" name="plant" id="plant">
                                <option value="" selected disabled>Select Plant</option>
                                <?php if (!empty($plant)) : ?>
                                <?php foreach ($plant as $plant_result) : ?>
                                <option value="<?= $plant_result->id ?>"
                                    <?= (isset($_GET['plant']) && $_GET['plant'] == $plant_result->id) ? 'selected' : '' ?>>
                                    <?= $plant_result->plant_name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 filter-rm">
                            <label for="material">Raw Material</label>
                            <select class="form-control js-example-basic-multiple" name="material_id" id="material_id">
                                <option value="">Select Material</option>
                                <?php if (!empty($raw_material)) : ?>
                                <?php foreach ($raw_material as $raw_material_result) : ?>
                                <option value="<?= $raw_material_result->id ?>"
                                    <?= (isset($_GET['material_id']) && $_GET['material_id'] == $raw_material_result->id) ? 'selected' : '' ?>>
                                    <?= $raw_material_result->rm_name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 filter-article">
                            <label for="article">Article</label>
                            <select class="form-control js-example-basic-multiple" name="article_id" id="article_id">
                                <option value="">Select Article</option>
                                <?php if (!empty($article)) : ?>
                                <?php foreach ($article as $article_result) : ?>
                                <option value="<?= $article_result->id ?>"
                                    <?= (isset($_GET['article_id']) && $_GET['article_id'] == $article_result->id) ? 'selected' : '' ?>>
                                    <?= $article_result->article_name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-xl-3 col-lg-4 col-md-6 col-sm-12 col-xs-12 filter-color">
                            <label for="color">Master batch</label>
                            <select class="form-control js-example-basic-multiple" name="color_id" id="color_id">
                                <option value="">Select Master Batch</option>
                                <?php if (!empty($color)) : ?>
                                <?php foreach ($color as $color_result) : ?>
                                <option value="<?= $color_result->id ?>"
                                    <?= (isset($_GET['color_id']) && $_GET['color_id'] == $color_result->id) ? 'selected' : '' ?>>
                                    <?= $color_result->name ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group col-md-12 col-sm-6 col-xs-12 mt-3 inline-btns">
                            <div class="inline-btns-left">
                                <button id="submit" type="submit" class="btn btn-sm btn-primary">Search</button>
                                <a href="<?= base_url() ?>stock_ledger_report" class="btn btn-sm btn-danger" id="reset_btn">Reset</a>
                                <button type="button" id="btn_download_excel" class="btn btn-sm btn-success" title="Download as Excel">
                                    <i class="fa fa-file-excel-o"></i> Download Excel
                                </button>
                                <button type="button" id="btn_download_pdf" class="btn btn-sm btn-danger" title="Download as PDF">
                                    <i class="fa fa-file-pdf-o"></i> Download PDF
                                </button>
                                <button type="button" id="btn_print" class="btn btn-sm btn-info" title="Print Report">
                                    <i class="fa fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>



            <div class="page_sec">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stock_ledger_table">
                        <thead class="thead-dark" style="background:#e8f0fd; color:#0056d0;">
                        <tr>
                            <th class="table-plus">SR. NO.</th>
                            <th>Date</th>
                            <th>Plant</th>
                            <th>Voucher Type</th>
                            <th style="width: 10%;">Reference No</th>
                            <th>Order ID</th>
                            <th id="ledger_item_header">Item Name</th>
                            <th>Inward</th>
                            <th>Outward</th>
                            <th>Balance Qty</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="search_report_type" id="search_report_type" value="<?php if (isset($_GET['report_type'])) {
    echo $_GET['report_type'];
} else { echo 'raw_material'; } ?>">

<input type="hidden" name="search_date" id="search_date" value="<?php if (isset($_GET['date'])) {
    echo $_GET['date'];
} ?>">

<input type="hidden" name="search_plant_id" id="search_plant_id" value="<?php if (isset($_GET['plant'])) {
    echo $_GET['plant'];
} ?>">

<input type="hidden" name="search_material_id" id="search_material_id" value="<?php if (isset($_GET['material_id'])) {
    echo $_GET['material_id'];
} ?>">

<input type="hidden" name="search_article_id" id="search_article_id" value="<?php if (isset($_GET['article_id'])) {
    echo $_GET['article_id'];
} ?>">

<input type="hidden" name="search_color_id" id="search_color_id" value="<?php if (isset($_GET['color_id'])) {
    echo $_GET['color_id'];
} ?>">

<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= base_url() ?>assets/js/pdfmake.min.js"></script>
<script src="<?= base_url() ?>assets/js/vfs_fonts.js"></script>
<script>
$(document).ready(function() {
    flatpickr("#date", {
        mode: "range",
        dateFormat: "d-m-Y",
    });

    $(".js-example-basic-multiple").select2({
        width: '100%'
    });

    function toggleFilters() {
        var type = $('#report_type').val();
        $('.filter-rm, .filter-article, .filter-color').hide();
        if (type == 'raw_material') {
            $('.filter-rm').show();
            $('#ledger_item_header').text('Raw Material');
        } else if (type == 'article') {
            $('.filter-article').show();
            $('#ledger_item_header').text('Article');
        } else if (type == 'color') {
            $('.filter-color').show();
            $('#ledger_item_header').text('Master batch');
        }
    }

    $('#report_type').change(function() {
        $('#search_report_type').val($(this).val());
        toggleFilters();
        // Reload table immediately when report type changes
        if (typeof window.stockTable !== 'undefined') {
            window.stockTable.ajax.reload();
        }
    });

    toggleFilters();
});
</script>

<script>
$(document).ready(function() {
    window.stockTable = $('#stock_ledger_table').DataTable({
        'searching': true,
        "processing": true,
        "serverSide": true,
        "cache": false,
        "pageLength": 10,
        "searchDelay": 1500,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        ordering: false,
        scrollCollapse: true,

        "ajax": {
            "url": "<?= base_url() ?>admin/Ajax_controller/get_stock_ledger_list",
            "type": "POST",
            "data": function(data) {
                data.search_report_type = $('#search_report_type').val();
                data.search_date = $('#search_date').val();
                data.plant_id = $('#search_plant_id').val();
                data.raw_material_id = $('#search_material_id').val();
                data.article_id = $('#search_article_id').val();
                data.color_id = $('#search_color_id').val();

            },
        },

        "drawCallback": function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Download Excel
    $('#btn_download_excel').click(function() {
        var data = [];
        var headers = ['SR. NO.', 'Date', 'Plant', 'Vch Type', 'Reference No', 'Order ID', 'Item Name', 'Inward Qty', 'Outward Qty', 'Balance Qty'];
        
        // Get all table data
        var rows = $('#stock_ledger_table tbody tr');
        var rowData = [];
        
        rows.each(function(index) {
            var cols = $(this).find('td');
            var row = [];
            cols.each(function() {
                row.push($(this).text().trim());
            });
            rowData.push(row);
        });

        // Create CSV content
        var csv = headers.join(',') + '\n';
        rowData.forEach(function(row) {
            csv += row.map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',') + '\n';
        });

        // Download CSV as Excel
        var link = document.createElement('a');
        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        link.download = 'Stock_Ledger_Report_' + new Date().getTime() + '.csv';
        link.click();
    });

    // Download PDF
    $('#btn_download_pdf').click(function() {
        var headers = ['SR. NO.', 'Date', 'Plant', 'Vch Type', 'Reference No', 'Order ID', 'Item Name', 'Inward Qty', 'Outward Qty', 'Balance Qty'];
        var rows = [];
        
        $('#stock_ledger_table tbody tr').each(function() {
            var cols = $(this).find('td');
            var row = [];
            cols.each(function() {
                row.push($(this).text().trim());
            });
            rows.push(row);
        });

        var docDefinition = {
            pageSize: 'A4',
            pageOrientation: 'landscape',
            content: [
                {
                    text: 'Stock Ledger Report',
                    style: 'header',
                    margin: [0, 0, 0, 20]
                },
                {
                    text: 'Generated on: ' + new Date().toLocaleString(),
                    fontSize: 10,
                    margin: [0, 0, 0, 10]
                },
                {
                    table: {
                        headerRows: 1,
                        widths: ['5%', '10%', '10%', '10%', '12%', '10%', '15%', '9%', '9%', '10%'],
                        body: [headers, ...rows]
                    },
                    style: 'table'
                }
            ],
            styles: {
                header: {
                    fontSize: 14,
                    bold: true,
                    color: '#0056d0'
                },
                table: {
                    fontSize: 9,
                    border: [1, 'solid', '#ccc']
                }
            }
        };

        pdfMake.createPdf(docDefinition).download('Stock_Ledger_Report_' + new Date().getTime() + '.pdf');
    });

    // Print Report
    $('#btn_print').click(function() {
        var printWindow = window.open('', '', 'height=600,width=1200');
        var printContent = '<html><head><title>Stock Ledger Report</title>';
        printContent += '<link rel="stylesheet" href="<?= base_url() ?>assets/css/bootstrap.min.css">';
        printContent += '<style>';
        printContent += 'body { font-family: Arial, sans-serif; margin: 20px; color: #000; }';
        printContent += 'h3 { font-size: 15px; margin: 20px 0 0; text-transform: uppercase; background: #0056d0; color: #fff !important; padding: 10px 15px; font-weight: 800; text-align: left; border-radius: 4px 4px 0 0; -webkit-print-color-adjust: exact; }';
        printContent += 'table { width: 100%; border-collapse: collapse; margin-top: 0 !important; }';
        printContent += 'th, td { border: 1px solid #d1d9e6; padding: 8px; text-align: left; color: #000; }';
        printContent += 'th { background-color: #eef2f7 !important; font-weight: bold; color: #333 !important; -webkit-print-color-adjust: exact; }';
        printContent += 'tr:nth-child(even) { background-color: #f9f9f9; }';
        printContent += '.report-date { text-align: center; margin-bottom: 20px; font-size: 12px; }';
        printContent += '@media print { .no-print { display: none; } }';
        printContent += '</style></head><body>';
        
        printContent += '<h3>Stock Ledger Report</h3>';
        printContent += '<div class="report-date">Generated on: ' + new Date().toLocaleString() + '</div>';
        printContent += '<div class="report-date">';
        
        // Add filter info
        var dateRange = $('#search_date').val();
        var plant = $('#search_plant_id').find("option:selected").text();
        var material = $('#search_material_id').find("option:selected").text();
        var article = $('#search_article_id').find("option:selected").text();
        if(dateRange) printContent += '<strong>Date Range:</strong> ' + dateRange + ' | ';
        if(plant && plant != 'Select Plant') printContent += '<strong>Plant:</strong> ' + plant + ' | ';
        if(material && material != 'Select Material') printContent += '<strong>Material:</strong> ' + material + ' | ';
        if(article && article != 'Select Article') printContent += '<strong>Article:</strong> ' + article + ' | ';
        
        printContent += '</div>';
        printContent += '<table>' + $('#stock_ledger_table').html() + '</table>';
        printContent += '</body></html>';
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        setTimeout(function() {
            printWindow.print();
        }, 250);
    });

    // Form Submit Handler - Prevent page reload and reload table instead
    $('#stock_ledger_form').on('submit', function(e) {
        e.preventDefault();
        
        // Validation: Must select an item before searching
        var type = $('#report_type').val();
        if (type == 'raw_material' && !$('#material_id').val()) {
            alert('Please select a specific Raw Material first to generate a continuous ledger.');
            return;
        } else if (type == 'article' && !$('#article_id').val()) {
            alert('Please select a specific Article first to generate a continuous ledger.');
            return;
        } else if (type == 'color' && !$('#color_id').val()) {
            alert('Please select a specific Master Batch first to generate a continuous ledger.');
            return;
        }
        
        // Copy visible field values to hidden fields
        $('#search_report_type').val($('#report_type').val());
        $('#search_date').val($('#date').val());
        $('#search_plant_id').val($('#plant').val());
        $('#search_material_id').val($('#material_id').val());
        $('#search_article_id').val($('#article_id').val());
        $('#search_color_id').val($('#color_id').val());

        
        // Reload the table with new filters
        window.stockTable.ajax.reload();
    });
});
</script>

<script>
$(document).ready(function() {
    $('#stock_report').addClass('nv active');
    $('.stock_ledger_report').addClass('active_cc');
});
</script>
