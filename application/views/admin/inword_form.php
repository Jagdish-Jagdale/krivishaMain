<?php include('header.php') ?>

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

    .select2-select {
        display: none;
    }
</style>
<!-- page content -->
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>
                Add Inword Form
            </h3>
        </div>

    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <form method="post" name="add_inward" id="add_inward" enctype="multipart/form-data">
                        <div class="row flex_wrap">
                            <input autocomplete="off" type="hidden" name="id" id="id" value="">

                            <!-- Inward Number -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Inward Number<b class="require">*</b></label>
                                <input name="inward_number" type="text" id="inward_number" class="form-control"
                                    value="INW-<?= date('Y') ?>-<?= rand(1000, 9999) ?>"
                                    placeholder="Enter inward number" readonly required>
                            </div>

                            <!-- Inward Date -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Inward Date<b class="require">*</b></label>
                                <input name="inward_date" type="date" id="inward_date" class="form-control datepicker" placeholder="Select inward date"
                                    value="" required>
                            </div>

                            <!-- Supplier Name -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Supplier Name<b class="require">*</b></label>
                                <select name="supplier_name" id="supplier_name" class="form-control select2-select" required>
                                    <option value="">Select Supplier</option>
                                    <!-- Populate dynamically if needed -->
                                </select>
                            </div>

                            <!-- Gate Entry No. -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Gate Entry No.</label>
                                <input name="gate_entry_no" type="text" id="gate_entry_no" class="form-control"
                                    value=""
                                    placeholder="Enter gate entry number" required>
                            </div>

                            <!-- Gate Entry Date -->
                            <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                <label>Gate Entry Date</label>
                                <input name="gate_entry_date" type="text" id="gate_entry_date" class="form-control"
                                    value=""
                                    placeholder="Enter gate entry date" required>
                            </div>


                            <div class="row">

                                <div class="form-group col-md-4 col-sm-6 col-xs-12">
                                    <label>Material (Raw Material)<b class="require">*</b></label>
                                    <select name="material_id" id="material_id" class="form-control select2-select" required>
                                        <option value="">Select Material</option>
                                        <option value="1">Material 1</option>
                                        <option value="2">Material 2</option>
                                        <option value="3">Material 3</option>


                                    </select>
                                </div>
                            </div>

                            <div class="row d-none" id="material_table_container">
                                <div class="col-12">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>MATERIAL NAMEe</th>
                                                <th>Description</th>
                                                <th>Unit</th>
                                                <th>Rate</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="material_table_body">
                                            <!-- Dynamic rows will be added here -->
                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-3 col-sm-3 col-xs-3">
                                    <label>Trap Hamali Extra Charges</label>
                                    <select name="trap_hamali_extra_charges" id="trap_hamali_extra_charges" class="form-control select2-select">
                                        <option value="">Select Trap Hamali Extra Charges</option>
                                        <option value="1">Charge 1</option>
                                        <option value="2">Charge 2</option>
                                        <option value="3">Charge 3</option>
                                        <!-- Populate dynamically if needed -->
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <table id="trap_table" class="table table-bordered table-striped d-none">
                                        <thead>
                                            <tr>
                                                <th>SR No.</th>
                                                <th>Trap Hamali Extra Charges</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="material_table_body">

                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 col-sm-6 col-xs-12">
                                    <button onclick="window.location.href='<?= base_url() ?>inword_form_list'" type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
        $('#master').addClass('nv active-color');

        $('.datepicker').flatpickr({
            dateFormat: "Y-m-d",
            defaultDate: new Date(),
        });

        $('.select2-select').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('#material_id').on('change', function() {


            $('#material_table_container').removeClass('d-none');
            var materialId = $(this).val();
            var materialName = $(this).find('option:selected').text();
            var newRow = `<tr>
                <td>${materialName}</td>
                <td>Description for ${materialName}</td>
                <td>
                    <input type="text" class="form-control" name="unit[]" value="nos" readonly>
                </td>
                <td>
                    <input type="number" class="form-control" name="rate[]" placeholder="Enter rate" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="quantity[]" placeholder="Enter quantity" required>
                </td>
             
                <td>
                    <button type="button" class="btn btn-danger remove-material">Remove</button>
                </td>
            </tr>`;
            $('#material_table_body').append(newRow);
        });


        $('#trap_hamali_extra_charges').on('change', function() {
            var selectedCharge = $(this).val();
            var chargeText = $(this).find('option:selected').text();
            $('#trap_table').removeClass('d-none');


            var newRow = `<tr>
                    <td>${$('#material_table_body tr').length + 1}</td>
                    <td>${chargeText}</td>
                    <td>
                    <input type="number" class="form-control" name="trap_hamali_amount[]" placeholder="Enter amount" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-material">Remove</button>
                    </td>
                </tr>`;
            $('#trap_table').append(newRow);

        });

        $(document).on('click', '.remove-material', function() {
            $(this).closest('tr').remove();
        });


    });
</script>