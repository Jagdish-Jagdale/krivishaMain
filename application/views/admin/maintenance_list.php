<?php include('header.php'); ?>

<style type="text/css">
    .error {

        color: red;

        float: left;

    }

    .form-control.error {
        color: #717171 !important;
    }

    .flex_wrap {

        display: flex;

        flex-wrap: wrap;

    }

    .select2-container {

        width: 100% !important;

    }

    .red {
        border: 2px solid red;
        background-color: #ffcccc;
    }

    .btn {
        display: flex;
        justify-content: center;
        display: inline-block;
        margin-left: 10px;
        margin-right: 10px;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
            border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        cursor: pointer;
    }


    .btn-success {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }


    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }

    .inline-btns {
        display: flex;
        align-items: baseline;

    }

    .modelclass {
        max-width: 69%;
        width: auto;
    }

    .content_body {
        padding: 20px;
        text-align: center;
    }

    /* .select-approve {
        background-color: #f8d7da;
        color: green;
        font-weight: bold;
    }

    .select-disapprove {
        background-color: #f8d7da;
        color:red;
        font-weight: bold;
    } */
    .input_table {
        text-align: center;
    }

    .table_btn {
        text-align: center;
        margin: 4px;
        padding-left: 14px;
        padding-right: 14px;
        padding-top: 3px;
        padding-bottom: 5px;
    }
</style>



<div class="right_col" role="main">



    <div class="table">

        <div class="page-title">

            <div class="title_left">
                <h3>Maintenance List</h3>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div class="x_panel">
                <form method="post" name="maintenance_list" id="maintenance_list" enctype="multipart/form-data">
                    <div class="x_content">
                        <div class="container table-responsive">
                            <table class="table table-striped table-bordered" id="dataTable">
                                <thead class="thead">
                                    <tr>
                                        <th style="width: 80px;">MWO Code</th>
                                        <th style="width: 127px;">Plant Name </th>
                                        <th style="width: 105px;">Status of Work</th>
                                        <th>Last Update Date</th>
                                        <th>Last Updated By</th>
                                        <th>Material used for maintenance</th>
                                        <th>Material Cost</th>
                                        <th>Total Labour Hour Involved</th>
                                        <th>Labour Cost Per Hour</th>
                                        <th>Total Cost</th>
                                        <th>Plant Manager Approval Status</th>
                                        <th>Remark of Plant Manager</th>
                                        <th>Maintenance History</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="input_table">
                                            <input
                                                value="<?php echo !empty($maintenance) ? $maintenance->mwo_code : ''; ?>"
                                                type="text" name="mwo_code" id="mwo_code" readonly class="form-control">
                                        </td>

                                        <input autocomplete="off" type="hidden" name="id" id="id"
                                            value="<?php if (!empty($last)) {
                                                echo $last->id;
                                            } ?>">
                                        <input autocomplete="off" type="hidden" name="type_of_action"
                                            id="type_of_action"
                                            value="<?php if (!empty($maintenance)) {
                                                echo $maintenance->type_of_action;
                                            } ?>">

                                        <td class="input_table"><input
                                                value="<?php echo !empty($maintenance) ? $maintenance->plant_name : '' ?>"
                                                type="text" name="plant_id" id="plant_id" readonly class="form-control">
                                        </td>

                                        <td class="input_table">
                                            <select class="form-control" name="status_of_work" id="status_of_work">
                                                <option value="2" <?= (isset($last->status_of_work) && $last->status_of_work == '2') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="1" <?= (isset($last->status_of_work) && $last->status_of_work == '1') ? 'selected' : ''; ?>>Completed</option>
                                                <option value="3" <?= (isset($last->status_of_work) && $last->status_of_work == '3') ? 'selected' : ''; ?>>Reopen</option>
                                                <option value="4" <?= (isset($last->status_of_work) && $last->status_of_work == '4') ? 'selected' : ''; ?>>Out of Scope
                                                </option>
                                            </select>
                                        </td>
                                        <td class="input_table">
                                            <input
                                                value="<?php echo !empty($last) && !empty($last->date) ? date('Y-m-d', strtotime($last->date)) : date('Y-m-d'); ?>"
                                                type="text" name="date" id="date" readonly class="form-control">
                                        </td>

                                        <td class="input_table">
                                            <input
                                                value="<?php echo !empty($single) ? $single->first_name : (!empty($maintenance) ? $maintenance->first_name : ''); ?>"
                                                type="text" name="employee_id" id="employee_id" readonly
                                                class="form-control">
                                        </td>

                                        <td class="input_table"><input
                                                value="<?php echo !empty($last) ? $last->material_used_for_maintenance : ''; ?>"
                                                type="text" name="material_used_for_maintenance"
                                                id="material_used_for_maintenance" class="form-control"></td>
                                        <td class="input_table"><input
                                                value="<?php echo !empty($last) ? $last->material_cost : ''; ?>"
                                                type="number" min="0" name="material_cost" id="material_cost"
                                                oninput="calculateTotalCost()" class="form-control"></td>
                                        <td class="input_table">
                                            <input
                                                value="<?php echo !empty($last) ? $last->total_labour_hour_involved : ''; ?>"
                                                type="number" min="0" name="total_labour_hour_involved"
                                                id="total_labour_hour_involved" class="form-control"
                                                oninput="calculateTotalCost()">
                                        </td>
                                        <td class="input_table">
                                            <input
                                                value="<?php echo !empty($last) ? $last->labour_cost_per_hour : ''; ?>"
                                                type="number" min="0" name="labour_cost_per_hour"
                                                id="labour_cost_per_hour" class="form-control"
                                                oninput="calculateTotalCost();validateTotalCost();">
                                        </td>
                                        <td class="input_table">
                                            <input onkeyup="check_input();"
                                                value="<?php echo !empty($last) ? $last->total_cost : ''; ?>"
                                                type="number" min="0" name="total_cost" id="total_cost"
                                                class="form-control">
                                        </td>
                                        <td class="input_table">
                                            <select class="form-control" name="plant_manager_approval_status"
                                                id="plant_manager_approval_status">
                                                <option value="" <?= (isset($last->plant_manager_approval_status) && $last->plant_manager_approval_status == '') ? 'selected' : ''; ?>>
                                                    Please select</option>
                                                 <option value="0" <?= (isset($last->plant_manager_approval_status) && $last->plant_manager_approval_status == '0') ? 'selected' : ''; ?>>
                                                    Approve</option>
                                                <option value="1" <?= (isset($last->plant_manager_approval_status) && $last->plant_manager_approval_status == '1') ? 'selected' : ''; ?>>
                                                    Disapprove</option>
                                               
                                            </select>
                                        </td>
                                        <td class="input_table"><input
                                                value="<?php echo !empty($last) ? $last->remark_of_plant_manager : ''; ?>"
                                                type="text" name="remark_of_plant_manager" id="remark_of_plant_manager"
                                                class="form-control"></td>
                                        </td>
                                        <td class="input_table inline-btns">
                                            <button type="submit" name="submit_btn" value="submit_btn"
                                                class="btn btn-primary">Submit</button>
                                            <button class="btn btn-secondary table_btn" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" name="check_histroy"
                                                id="check_history">Log</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modelclass">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Complete Histroy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body content_body">
                <div class="container ">
                    <!-- Data Table -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped">
                            <thead class="">
                                <tr>
                                    <th>SR NO.</th>
                                    <th>MWO Code</th>
                                    <th>Plant Name</th>
                                    <th>Status of Work</th>
                                    <th>Last Update Date</th>
                                    <th>Last Updated By</th>
                                    <th>Used material</th>
                                    <th>Material Cost</th>
                                    <th>Total Labour Hour Involved</th>
                                    <th>Labour Cost Per Hour</th>
                                    <th>Total Cost</th>
                                    <th>Approval Status</th>
                                    <th>Remark</th>
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
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        // $('#product_master .child_menu').show();
        // $('#product_master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        // $('.production_maintenance_list').addClass('active_cc');
    });
</script>
<script>
    const approvalSelect = document.getElementById('plant_manager_approval_status');
    function setRemark() {
        approvalSelect.classList.remove('select-approve', 'select-disapprove');
        if (approvalSelect.value === "1") {
            approvalSelect.classList.add('select-approve');
        } else if (approvalSelect.value === "2") {
            approvalSelect.classList.add('select-disapprove');
        }
    }
    setRemark();
    approvalSelect.addEventListener('change', setRemark);
</script>

<script>
    function validateTotalCost() {
        var totalCostValue = $('#total_cost').val(); // Get the value of the total_cost input
        if (totalCostValue !== '') {
            // If the value is not empty, trigger the validation
            $('#total_cost').valid();
        }
    }
</script>
<script>
    $.validator.addMethod("noSpaceAtStart", function (value, element) {
        return this.optional(element) || /^\s/.test(value) === false;
    }, "First letter can not be space");
    $(document).ready(function () {
        $("#maintenance_list").validate({
            rules: {
                status_of_work: {
                    required: true
                },
                plant_manager_approval_status: {
                    required: true
                },
                material_used_for_maintenance: {
                    required: true,
                    noSpaceAtStart: true
                },
                material_cost: {
                    required: true,
                    noSpaceAtStart: true
                },
                total_labour_hour_involved: {
                    required: true,
                    noSpaceAtStart: true
                },
                labour_cost_per_hour: {
                    required: true,
                    noSpaceAtStart: true
                },
                total_cost: {
                    required: true,
                    noSpaceAtStart: true
                },

            },
            messages: {
                status_of_work: {
                    required: "Please select status of work!"
                },
                plant_manager_approval_status: {
                    required: "Please select plant manager approval status!"
                },
                material_used_for_maintenance: {
                    required: "Please enter material used for maintenance!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                material_cost: {
                    required: "Please enter material cost!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                total_labour_hour_involved: {
                    required: "Please enter total labour hour involved!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                labour_cost_per_hour: {
                    required: "Please enter labour cost per hour!",
                    noSpaceAtStart: "First letter can not be space!"
                },
                total_cost: {
                    required: "Please enter total cost!",
                    noSpaceAtStart: "First letter can not be space!"
                },
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            }
        });

        $('#maintenanceForm').on('submit', function (e) {
            if (!$(this).valid()) {
                e.preventDefault(); // prevent submission
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        // Get the status_of_work from PHP
        var statusOfWork = '<?= isset($last->status_of_work) ? $last->status_of_work : ''; ?>';

        // Function to update options visibility based on the selected status
        function toggleStatusOptions(status) {
            if (status === '1' || status === '3' || status === '4') {
                $('#status_of_work option[value="2"]').hide();
            } else {
                // Show the "Pending" option if status is blank or any value other than 1 or 3
                $('#status_of_work option[value="2"]').show();
            }
        }

        toggleStatusOptions(statusOfWork);
    }); 
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#datepicker", {
        mode: "range",
        dateFormat: "Y-m-d"
    });
    $('#status_of_work').on('change', function () {
        toggleFields();
    });

    toggleFields();
</script>
<script>
    function toggleFields() {
        var status = $('#status_of_work').val();

        var materialUsedField = $('#material_used_for_maintenance');
        var materialCostField = $('#material_cost');
        var totalLabourHourField = $('#total_labour_hour_involved');
        var labourCostPerHourField = $('#labour_cost_per_hour');
        var totalCostField = $('#total_cost');
        var approvalStatusField = $('#plant_manager_approval_status');
        var remarkField = $('#remark_of_plant_manager');

        if (status == '2' || status == '4') {
            materialUsedField.prop('readonly', true).prop('disabled', true);
            materialCostField.prop('readonly', true).prop('disabled', true);
            totalLabourHourField.prop('readonly', true).prop('disabled', true);
            labourCostPerHourField.prop('readonly', true).prop('disabled', true);
            totalCostField.prop('readonly', true).prop('disabled', true);
            approvalStatusField.prop('disabled', true);
            remarkField.prop('readonly', true).prop('disabled', true);
        } else {
            materialUsedField.prop('readonly', false).prop('disabled', false);
            materialCostField.prop('readonly', false).prop('disabled', false);
            totalLabourHourField.prop('readonly', false).prop('disabled', false);
            labourCostPerHourField.prop('readonly', false).prop('disabled', false);
            totalCostField.prop('readonly', false).prop('disabled', false);
            approvalStatusField.prop('disabled', false);
            remarkField.prop('readonly', false).prop('disabled', false);
        }

    }

    $('#status_of_work').on('change', function () {
        toggleFields();
    });

    $(document).ready(function () {
        toggleFields();
    });
</script>


<script>
    function check_input() {
        let totalCostElement = document.getElementById("total_cost");
        let enteredValue = parseFloat(totalCostElement.value);
        let hours = parseFloat(document.getElementById("total_labour_hour_involved").value);
        let rate = parseFloat(document.getElementById("labour_cost_per_hour").value);
        let material_cost = parseFloat(document.getElementById("material_cost").value);
        let calculatedTotal = parseFloat((hours * rate).toFixed(2));
        let total = calculatedTotal + material_cost;
        totalCostElement.classList.remove("red");

        if (enteredValue !== total) {
            totalCostElement.classList.add("red");
        }
    }

    function calculateTotalCost() {
        let hours = parseFloat(document.getElementById("total_labour_hour_involved").value);
        let rate = parseFloat(document.getElementById("labour_cost_per_hour").value);
        let material_cost = parseFloat(document.getElementById("material_cost").value);
        let total = hours * rate;
        let totalnew = total + material_cost;
        // $('#total_cost').valid();
        let totalCostElement = document.getElementById("total_cost");
        totalCostElement.value = totalnew.toFixed(2);
        check_input();
    }

    document.getElementById("total_cost").addEventListener("input", check_input);
</script>
<script>
    $(document).on('click', '#check_history', function (e) {
        e.preventDefault();
        var mwo_code = $('#mwo_code').val();

        $.ajax({
            url: "<?= base_url('admin/Ajax_controller/get_all_complete_maintenance_list_details_ajax') ?>",
            method: "POST",
            data: {
                mwo_code: mwo_code
            },
            dataType: "json",
            success: function (response) {
                var tbody = "";
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function (index, record) {
                        tbody += `<tr>
                        <td>${record.sr_no}</td>
                        <td>${record.mwo_code}</td>
                        <td>${record.plant_name}</td>
                        <td>${record.status_of_work}</td>
                        <td>${record.date}</td>
                        <td>${record.first_name}</td>
                        <td>${record.material_used}</td>
                        <td>${record.material_cost}</td>
                        <td>${record.total_labour_hours}</td>
                        <td>${record.labour_cost_per_hour}</td>
                        <td>${record.total_cost}</td>
                        <td>${record.approval_status}</td>
                        <td>${record.remark}</td>
                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='13'>No history found.</td></tr>";
                }
                $("#exampleModal table tbody").html(tbody);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
</script>