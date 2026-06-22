<?php include 'header.php';?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox-content">
            <p>Today PO List</p>
            <hr>
            <table id="example" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Sr.No</th>
                        <th>country</th>
                        <th>state</th>
                        <th>city</th>
                        <th>location name</th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td class="center">1</td>
                        <td>sakshi</td>
                        <td>Maharashtra</td>
                        <td>pune</td>
                        <td>pune</td>
                        <td><button class="btn btn-1 btn-warning btn-sm">Edit</button>
                            <button class="btn btn-2 btn-warning btn-sm">Active</button>
                            <button class="btn btn-3 btn-warning btn-sm">In-Active</button>
                            <button class="btn btn-4 btn-warning btn-sm">Delete</button>
                        </td>

                        </td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php include 'footer.php';?>
<script>
$(document).ready(function() {
    $('#example').DataTable({
        colReorder: true,
        responsive: true
    });
});
</script>