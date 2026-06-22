<?php include('header.php'); ?>
<style>
  .chosen-container-single .chosen-single {
    /* height: 50px !important; */
    background-color: #fff !important;
    /* padding-top: 13px !important; */
  }

  #accordion .card-background .card-header .card-link {
    padding: 13px;
    display: block;
    cursor: pointer;
    user-select: none;
  }

  #accordion .card-background .card-header {
    background: #eee;
    font-size: 15px;
    font-weight: 600;
    border-bottom: 1px solid #ddd;
  }

  .card {
    border-radius: 0;
  }

  #accordion .card-background .card-body {
    padding: 13px;
    font-size: 13px;
    color: #565656;
  }

  /*
   * Using max-height instead of Bootstrap collapse (display:none).
   * Bootstrap collapse hides checkboxes from form submission.
   */
  .priv-panel-body {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.25s ease-out;
  }
  .priv-panel-body.priv-open {
    max-height: 2000px;
    transition: max-height 0.4s ease-in;
  }
</style>
<div class="right_col" role="main">
  <div class="">
    <div class="page-title">
      <div class="title_left">
        <h3>Assign Privileges</h3>
      </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
      <div class="col-lg-12 col-mg-12">
        <div class="x_panel">
          <div class="x_content">
            <form name="add_payment" id="add_payment" autocomplete="off" method="POST"
                  action="<?= base_url('manage-privilege/' . $this->uri->segment(2)) ?>">
              <!-- Hidden field to reliably trigger the save branch in the controller -->
              <input type="hidden" name="form_submitted" value="1">
              <!-- Hidden field ensures employee ID is always submitted even if chosen-select has issues -->
              <input type="hidden" name="employee" id="employee_hidden_val" value="<?= $this->uri->segment(2) ?>">
              <div class="form-group col-md-12">
                <label for="">Select Employee <b class="require">*</b> </label>
                <select name="employee_display" id="employee" class="form-control chosen-select">
                  <option value="">Select employee</option>
                  <?php if ($employee) {
                    foreach ($employee as $employee_result) { ?>
                      <option value="<?= $employee_result->id ?>" <?php if (!empty($single_employee) && $single_employee->id == $employee_result->id) { ?>selected="selected" <?php } ?>><?= $employee_result->first_name . ' ' . $employee_result->middle_name . ' ' . $employee_result->last_name ?></option>
                  <?php }
                  } ?>
                </select>
              </div>
              <?php if ($this->uri->segment(2) != "") { ?>
                <div class="form-group col-lg-6">
                  <label for="fullname">Check All</label>
                  <input type="checkbox" id="check-all">
                  <div id="print_error_message" style="color: red;margin-bottom:10px;"></div>
                  <label id="validation-message" style="color:#c32b2b;"></label>
                </div>
              <?php } ?>
              <div class="form-group col-md-12">
                <div id="accordion">
                  <?php if (!empty($single_employee)) {
                    if (!empty($privilege)) {
                      // Build the saved privilege ID array ONCE — cast all to string for reliable in_array
                      $exp = array();
                      if (!empty($user_privilege) && !empty($user_privilege->previleges)) {
                        $exp = array_map('trim', explode(",", $user_privilege->previleges));
                      }
                      foreach ($privilege as $privilege_result) {
                  ?>
                        <div class="card card-background">
                          <div class="card-header">
                            <a class="card-link priv-toggle" data-target="#priv-panel-<?= $privilege_result->id ?>">
                              <?= $privilege_result->previlege ?> <i class="fa fa-angle-down" aria-hidden="true"></i>
                            </a>
                          </div>
                          <!-- Using max-height toggle so checkboxes are always submitted -->
                          <div id="priv-panel-<?= $privilege_result->id ?>" class="priv-panel-body">
                            <div class="card-body">
                              <?php
                              $link = $this->Admin_model->get_selected_link($privilege_result->id);
                              if (!empty($link)) {
                                foreach ($link as $link_result) {
                              ?>
                                  <input type="checkbox" class="checkbox-ids" name="link[]" value="<?= $link_result->id ?>" <?php if (in_array((string)$link_result->id, $exp)) { ?>checked="checked" <?php } ?>> <?= $link_result->submenu ?>
                                  <br>
                              <?php   }
                              }
                              ?>
                            </div>
                          </div>
                        </div>
                  <?php }
                    }
                  } ?>
                  <div class="row form-group">
                  </div>
                </div>
              </div>
              <?php if ($this->uri->segment(2) != "") { ?>
                <div class="col-lg-4">
                  <button type="submit" name="save" value="Save" class="form_submit btn btn-primary">Submit</button>
                </div>
              <?php } ?>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include("footer.php"); ?>
<script type="text/javascript">
  $(document).ready(function() {
    // $('#privileges_management .child_menu').show();
    $('#privileges_management').addClass('nv active');
    // $('.right_col').addClass('active_right');
    $('.manage-privilege').addClass('active_cc');

    // Check All toggle
    $("#check-all").change(function() {
      $(".checkbox-ids").prop('checked', $(this).prop("checked"));
    });

    // Sync check-all state on load
    var totalCheckboxes = $(".checkbox-ids").length;
    var checkedCheckboxes = $(".checkbox-ids:checked").length;
    if (totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes) {
      $("#check-all").prop("checked", true);
    }

    // Auto-open panels that have checked checkboxes on page load
    $('.priv-panel-body').each(function() {
      if ($(this).find('.checkbox-ids:checked').length > 0) {
        $(this).addClass('priv-open');
        $(this).closest('.card').find('.priv-toggle .fa')
               .removeClass('fa-angle-down').addClass('fa-angle-up');
      }
    });

    // Prevent accidental form submission (e.g. hitting Enter in the search box) before redirecting
    $('#add_payment').on('submit', function(e) {
      if (!$("#employee_hidden_val").val()) {
        e.preventDefault();
        return false;
      }
    });
  });

  // Employee selection: redirect to privilege page for that employee
  $(".chosen-select").chosen();
  $("#employee").change(function() {
    var val = $(this).val();
    $("#employee_hidden_val").val(val);
    window.location.href = "<?= base_url(); ?>manage-privilege/" + val;
  });

  // Custom accordion toggle using max-height (NOT display:none)
  $(document).on('click', '.priv-toggle', function() {
    var $this = $(this);
    var target = $this.data('target');
    var $panel = $(target);
    var isOpen = $panel.hasClass('priv-open');

    // Close all panels
    $('.priv-panel-body').removeClass('priv-open');
    $('.priv-toggle .fa').removeClass('fa-angle-up').addClass('fa-angle-down');

    // Toggle the clicked panel
    if (!isOpen) {
      $panel.addClass('priv-open');
      $this.find('.fa').removeClass('fa-angle-down').addClass('fa-angle-up');
    }
  });
</script>