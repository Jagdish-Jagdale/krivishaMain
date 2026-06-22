<?php include('header.php'); ?>
<style>
	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:first-child {
		/* padding-left: 30px; */
	}

	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:nth-child(3) {
		padding-left: 20px;
	}

	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:nth-child(4) {
		padding-left: 20px;
	}

	table.table-bordered.dataTable tbody th,
	table.table-bordered.dataTable tbody td:nth-child(5) {
		padding-left: 20px;
	}
</style>
<div class="right_col" role="main">
	<div class="">
		<div class="page-title">
			<div class="title_left">
				<h3>Manage Privilege Sub Head</h3>
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
								<label for="fullname">Select Privilege Main Head <b class="require">*</b></label>
								<select id="previleges" class="chosen-select form-control" name="previleges">
									<option value="">Select Privilege Main Head</option>
									<?php if (!empty($previleges)) {
										foreach ($previleges as $previleges_result) { ?>
											<option value="<?= $previleges_result->id ?>" <?php if (!empty($single) && $single->previlege == $previleges_result->id) { ?>selected="selected" <?php } ?>>
												<?= $previleges_result->previlege ?></option>
									<?php }
									} ?>
								</select>
								<label for="previleges" generated="true" class="error" style="display:none;">Please
									select previlege main head!</label>
							</div>
							<div class="col-lg-12 form-group">
								<label for="fullname">Privileges Sub Head Name <b class="require">*</b></label>
								<input placeholder="Enter Privilege Sub Head Name" autocomplete="off" type="text"
									id="submenu" class="form-control" name="submenu"
									value="<?php if (!empty($single)) {
												echo $single->submenu;
											} ?>" required />
								<input type="hidden" id="id" class="form-control" name="id"
									value="<?php if (!empty($single)) {
												echo $single->id;
											} ?>" />
							</div>
							<div class="col-lg-12 form-group">
								<label for="fullname">Link <b class="require">*</b></label>
								<input placeholder="Enter Link" autocomplete="off" type="text" id="link"
									class="form-control" name="link"
									value="<?php if (!empty($single)) {
												echo $single->link;
											} ?>" required />
								<div class="error" id="status_error"></div>
								<input type="hidden" id="id" class="form-control" name="id"
									value="<?php if (!empty($single)) {
												echo $single->id;
											} ?>" />
							</div>
							<div class="error" id="status_error"></div>
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
								<th>Head</th>
								<th>Sub Head</th>
								<th>Link</th>
								<th class="">Action</th>

							</tr>
						</thead>
						<tbody>
							<?php if (!empty($submenu)) {
								$i = 1;
								foreach ($submenu as $previleges_result) {
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
										<td><?= $previleges_result->submenu ?></td>
										<td><?= $previleges_result->link ?></td>
										<td>
											<?php
											$action = '';
											// if ($previleges_result->status == "1") {
											// 	$action .= '<a class="btn btn-info inactive_btn" href="' . base_url() . 'inactive/' . $previleges_result->id . '/tbl_subprevilege"  onclick="return confirm(\'Are you sure you want to inactive this privilege sub head?\');"><i class="fa-solid fa-xmark"></i></a>';
											// } else {
											// 	$action .= '<a class="btn btn-warning active_btn" href="' . base_url() . 'active/' . $previleges_result->id . '/tbl_subprevilege"  onclick="return confirm(\'Are you sure you want to active this privilege sub head?\');"><i class="fa-solid fa-check"></i></a>';
											// }
											$action .= '<a class="btn btn-danger" href="' . base_url() . 'delete/' . $previleges_result->id . '/tbl_subprevilege"  onclick="return confirm(\'Are you sure you want to delete this privilege sub head?\');"><i class="fa-solid fa-trash"></i></a>';
											$action .= '<a class="btn btn-success" href="' . base_url() . 'add-submenu/' . $previleges_result->id . '"><i class="fa-solid fa-pencil"></i></a>';
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
		$('.add-submenu').addClass('active_cc');
	});

	$.validator.addMethod("noSpaceatfirst", function(value, element) {
		return this.optional(element) || /^\s/.test(value) === false;
	}, "First Letter Can't Be Space!");

	$("#make_form").validate({
		ignore: [],
		rules: {
			previleges: {
				required: true,
			},
			submenu: {
				required: true,
				noSpaceatfirst: true,
			},
			link: {
				required: true,
				noSpaceatfirst: true,
			},
		},
		messages: {
			previleges: {
				required: "Please select previlege main head!",
			},
			submenu: {
				required: 'Please enter previlege sub head name!',
				noSpaceatfirst: "First letter can't be space!",
			},
			link: {
				required: 'Please enter link!',
				noSpaceatfirst: "First letter can't be space!",
			},
		},
		submitHandler: function(form) {
			if (confirm("Are you sure to submit the form?")) {
				form.submit();
			}
		}
	});

	$('#previleges').on('change', function() {
		$('#previleges').valid();
	});

	$("#submenu").keyup(function() {
		$.ajax({
			type: "POST",
			url: "<?= base_url(); ?>admin/Ajax_controller/get_unique_name_ajax",
			data: {
				'name': $("#submenu").val(),
				'label': "submenu",
				'table_name': "tbl_subprevilege",
				'id': '<?= $id ?>'
			},
			success: function(data) {
				var res = $.trim(data);
				console.log(res);
				if (res == '0') {
					$("#status_error").html("");
					$("#submit_button").attr('disabled', false);
					$("#link").trigger('keyup');
				} else {
					$("#status_error").html("Privileges is already added");
					$("#submit_button").attr('disabled', true);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	});
	$("#link").keyup(function() {
		$.ajax({
			type: "POST",
			url: "<?= base_url(); ?>admin/Ajax_controller/get_unique_name_ajax",
			data: {
				'name': $("#link").val(),
				'label': "link",
				'table_name': "tbl_subprevilege",
				'id': '<?= $id ?>'
			},
			success: function(data) {
				var res = $.trim(data);
				console.log(res);
				if (res == '0') {
					$("#status_error").html("");
					$("#submit_button").attr('disabled', false);
					$("#submenu").trigger('keyup');
				} else {
					$("#status_error").html("Link is already added");
					$("#submit_button").attr('disabled', true);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});
	});

	$(document).ready(function() {
		var t = $('#example').DataTable({
			"order": [],
			"columnDefs": [{
				"orderable": false,
				"targets": [0, 1, 2, 3, 4], // Make all columns unsortable if desired
				// "targets": '_all',
				// "className": 'tbl-min-width-100'
			}],

			"scrollX": true,
			dom: 'Blfrtip',
			buttons: [{
				extend: 'excelHtml5',
				title: 'Privileges List',
				filename: 'privileges_list',
				exportOptions: {
					columns: [0, 1, 2, 3]
				}
			}],
			lengthMenu: [
				[10, 25, 50, -1],
				['10', '25', '50', 'All']
			],
			pageLength: 10,
			// 🔁 Update Sr. No. after draw (search/pagination)
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