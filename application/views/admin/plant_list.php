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
    max-width: 100% !important;
}


.multi-select {
    display: none;
}
</style>
<!-- page content -->
<div class="right_col" role="main">


    <div class="row">
        <div class="col-md-4">
            <div class="page-title">
                <div class="title_left">
                <h3>
                <?php if (!empty($single)) { ?>
                        Update Plant
                    <?php } else { ?>
                        Add Plant
                    <?php } ?>
                </h3>
                </div>

            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">
                        <form method="post" name="add_machine_form" id="add_machine_form" enctype="multipart/form-data">
                            <div class="row flex_wrap">

                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                    <label>Plant Name<b class="require">*</b></label>
                                    <input id="plant_name" name="plant_name" type="text" class="form-control" value="<?php if (!empty($single)) { echo $single->plant_name; } ?>" placeholder="Enter plant name" required>
                                    <input autocomplete="off" type="hidden" name="id" id="id" value="<?php if (!empty($single)) { echo $single->id; } ?>">
                                    <span id = "name_error" class = ""></span>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <button type="submit" id="submit_btn" class="btn btn-primary">Submit</button>
                            </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="page-title">
                <div class="title_left">
                    <h3>Plant List</h3>
                </div>

            </div>
            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="container">

                        <table style="width: 100%;" class="table table-striped table-bordered" id="example">
                            <thead class="thead">
                                <tr>
                                    <th>SR. NO.</th>
                                    <th>Plant Name</th>
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
    $('.add_plant').addClass('active_cc');
    // $('#master').addClass('nv active-color');
});
</script>
<script>
$(document).ready(function() {

    $(".js-example-basic-multiple").select2({});


});
</script>
<script>
        $.validator.addMethod("noSpaceAtStart", function (value, element) {
            return this.optional(element) || /^\s/.test(value) === false;
        }, "First letter can not be space");
        jQuery.validator.addMethod("noNumbers", function (value, element) {
            return this.optional(element) || !/\d/.test(value);
        });
        $(document).ready(function() {
            $('#add_machine_form').validate({
                ignore: [],
                rules: {
                    plant_name: {
                        required: true,
                        noSpaceAtStart: true,
                    },
                },
                messages: {
                    plant_name: {
                        required: "Please enter plant name!",
                        noSpaceAtStart: "First letter can not be space!",
                    },
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });


</script>

<script>
    $('#plant_name').on('keyup', function () {
        var plant_name = $(this).val();
        $.ajax({
            url: '<?= base_url() ?>admin/Ajax_controller/check_unique_plant_name',
            method: 'post',
            data: { 
                'plant_name': plant_name ,
                'id': '<?= $id ?>'
            },
            success: function (response) {
                if (response == '1') {
                    $('#name_error').text("This plant name is already exists !");
                    $('#name_error').addClass('error');
                    $('#submit_btn').prop('disabled', true);
                } else {
                    $('#name_error').text("");
                    $('#submit_btn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error: ' + textStatus, errorThrown);
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
        "lengthChange": true,
        "responsive": true,
        "lengthMenu": [10, 25, 50, 100],
        'searching': true,
        "processing": true,
        "serverSide": true,
        "cache": false,
        "order": [],
        "ordering": false,
        
        dom: "Blfrtip",
        buttons: [
                {
                    extend: 'excel',
                    footer: true,
                    filename: 'plant_list',
                    exportOptions: {
                        columns: [0,1]
                    }
                }
            ],
        //scrollX: true, 
        scrollCollapse: true,
        "ajax": {
            "url": "<?=base_url()?>admin/Ajax_controller/get_all_plant_list",
            "type": "POST",
        },
        "complete": function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
       });
    });
    

</script>