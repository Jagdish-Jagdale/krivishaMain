<?php include('header.php'); ?>
<style>
	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:first-child {
		padding-left: 30px;
	}

	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:nth-child(3) {
		padding-left: 20px;
	}

	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:nth-child(4) {
		padding-left: 20px;
	}
</style>
<div class="right_col" role="main">
	<div class="">
		<div class="page-title">
			<div class="title_left">
				<h3>Manage Privilege Head</h3>
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="row">
			<div class="col-md-4 col-sm-4 col-xs-12" style="margin:0px auto;">
				<div class="x_panel">
					<div class="x_content">
						<form id="make_form" name="make_form" method="post" enctype="multipart/form-data"
							data-parsley-validate>
							<div class="col-lg-12 form-group">
								<label for="fullname">Privilege Head Name <b class="require">*</b></label>
								<input placeholder="Enter privilege head name" autocomplete="off" type="text"
									id="previleges" class="form-control" name="previleges"
									value="<?php if (!empty($single)) {
												echo $single->previlege;
											} ?>" required />
								<div class="error" id="status_error"></div>
								<input type="hidden" id="id" class="form-control" name="id"
									value="<?php if (!empty($single)) {
												echo $single->id;
											} ?>" />
							</div>
							<div class="clearfix"></div>
							<div class="clearfix"></div>
							<div class="col-md-3  col-sm-3 col-xs-12">
								<button type="submit" id="submit_button" class="btn btn-primary">Submit</button>
							</div>
							<div class="clearfix"></div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-md-8 col-sm-8 col-xs-12" style="margin:0px auto;">
				<div class="x_panel">
					<table id="example" class=" table table-striped table-bordered example_class" style="width:100%">
						<thead>
							<tr>
								<th>Sr. No.</th>
								<!-- <th class="">Status</th> -->
								<th>Privilege Head</th>
								<th class="">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($previleges)) {
								$i = 1;
								foreach ($previleges as $previleges_result) {
							?>
									<tr>
										<td></td>
										<!-- <td>
										<?php if ($previleges_result->status == "0") { ?>
											<span class="label label-danger">Inactive</span>
										<?php } else { ?>
											<span class="label label-success">Active</span>
										<?php } ?>
									</td> -->
										<td><?= $previleges_result->previlege ?></td>
										<td>
											<?php
											$action = '';
											// if ($previleges_result->status == "1") {
											// 	$action .= '<a class="btn btn-info inactive_btn" href="' . base_url() . 'inactive/' . $previleges_result->id . '/tbl_previleges"  onclick="return confirm(\'Are you sure you want to inactive this privilege head?\');"><i class="fa-solid fa-xmark"></i></a>';
											// } else {
											// 	$action .= '<a class="btn btn-warning active_btn" href="' . base_url() . 'active/' . $previleges_result->id . '/tbl_previleges"  onclick="return confirm(\'Are you sure you want to active this privilege head?\');"><i class="fa-solid fa-check"></i></a>';
											// }
											$action .= '<a class="btn btn-danger" href="' . base_url() . 'delete/' . $previleges_result->id . '/tbl_previleges"  onclick="return confirm(\'Are you sure you want to delete this privilege head?\');"><i class="fa-solid fa-trash"></i></a>';
											$action .= '<a class="btn btn-success" href="' . base_url() . 'add-previleges/' . $previleges_result->id . '" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
											echo $action;
											?>
										</td>
									</tr>
							<?php }
							} ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<?php include('footer.php');
if ($this->uri->segment(2) == "") {
	$id = 0;
} else {
	$id = $this->uri->segment(2);
}
?>
<script>
	$(document).ready(function() {
		// $('#privileges_management .child_menu').show();
		$('#privileges_management').addClass('nv active');
		// $('.right_col').addClass('active_right');
		$('.add-previleges').addClass('active_cc');
	});
	$("#previleges").keyup(function() {
		$.ajax({
			type: "POST",
			url: "<?= base_url(); ?>admin/Ajax_controller/get_unique_name_ajax",
			data: {
				'name': $("#previleges").val(),
				'label': "previlege",
				'table_name': "tbl_previleges",
				'id': '<?= $id ?>'
			},
			success: function(data) {
				var res = $.trim(data);
				if (res == '0') {
					$("#status_error").html("");
					$("#submit_button").attr('disabled', false);
				} else {
					$("#status_error").html("Previleges is already added");
					$("#submit_button").attr('disabled', true);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	});

	$.validator.addMethod("noSpaceatfirst", function(value, element) {
		return this.optional(element) || /^\s/.test(value) === false;
	}, "First Letter Can't Be Space!");

	$("#make_form").validate({
		rules: {
			previleges: {
				required: true,
				noSpaceatfirst: true,
			},
		},
		messages: {
			previleges: {
				required: 'Please enter privilege head name!',
				noSpaceatfirst: "First letter can't be space!",
			},
		},
		submitHandler: function(form) {
			if (confirm("Are you sure to submit the form?")) {
				form.submit();
			}
		}
	});
	$(document).ready(function() {
		var t = $('#example').DataTable({
			"order": [],
			"columnDefs": [{
				"orderable": false,
				"targets": [0, 1, 2] // make all columns non-sortable if needed
			}],
			dom: 'Blfrtip',
			buttons: [{
				extend: 'excelHtml5',
				title: 'Privilege Head List',
				filename: 'Privilege_Head_List',
				exportOptions: {
					columns: [0, 1]
				}
			}],
			lengthMenu: [
				[10, 25, 50, -1],
				['10', '25', '50', 'All']
			],
			pageLength: 10,

			// 👇 Auto-generate Sr. No. on search, sort, paginate
			drawCallback: function(settings) {
				var api = this.api();
				api.column(0, {
					page: 'current'
				}).nodes().each(function(cell, i) {
					cell.innerHTML = i + 1;
				});
			}
		});
	});
</script>