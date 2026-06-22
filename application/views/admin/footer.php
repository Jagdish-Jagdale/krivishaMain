<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="<?= base_url('update-profile-image'); ?>" method="post" enctype="multipart/form-data">
                <div class="modal-header" style="background:#f5f5f5;">
                    <h4 class="modal-title" id="profileModalLabel">My Profile</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="border: none; background: none; font-size: 24px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="profile-img mb-3 text-center">
                        <img src="<?= !empty($profile->emp_photo) ? base_url('assets/images/' . $profile->emp_photo) : base_url('assets/images/background_krivisha.jpg'); ?>"
                            class="img-profile-circle" width="120" height="120" alt="Profile Image">
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" Value="<?= $profile->first_name; ?>" name="name"
                            readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" Value="<?= $profile->email; ?>" name="email" readonly>
                    </div>
                    <div class="form-group">
                        <label for="num">Mobile No.</label>
                        <input type="text" class="form-control" Value="<?= $profile->mobile_number; ?>" name="num"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label for="profile_image">Update Image<b class="require">*</b></label>
                        <input type="file" id="profile_image" name="profile_image" class="form-control">
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="<?= base_url('change-current-password'); ?>" id="change_password" method="post">
                <div class="modal-header">
                    <h4 class="modal-title" id="changePasswordLabel">Change Password</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="border: none; background: none; font-size: 24px;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="current_password">Current Password<b class="require">*</b></label>
                        <div class="input-group">
                            <input type="password" autocomplete="off" class="form-control" name="current_password"
                                id="current_password" placeholder="Enter current password" required>
                            <span class="input-group-text toggle_password" data-toggle="current_password"
                                style="cursor:pointer;">
                                <i class="fa fa-eye"></i>
                            </span>

                        </div>
                        <span id="current_password_error" class="error"></span>
                    </div>
                    <div class="form-group new_pass d-none">
                        <label for="new_password">New Password<b class="require">*</b></label>
                        <div class="input-group">
                            <input type="password" autocomplete="off" class="form-control" name="new_password"
                                id="new_password" placeholder="Enter new password" required>
                            <span class="input-group-text toggle_password" data-toggle="new_password"
                                style="cursor:pointer;">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group new_pass d-none">
                        <label for="confirm_password">Confirm Password<b class="require">*</b></label>
                        <div class="input-group">
                            <input type="password" autocomplete="off" class="form-control" name="confirm_password"
                                id="confirm_password" placeholder="Enter confirm password" required>
                            <span class="input-group-text toggle_password" data-toggle="confirm_password"
                                style="cursor:pointer;">
                                <i class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" id="submit_btn_change" class="btn btn-primary">Change Password</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="apk_upload_Modal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <form action="<?= base_url('apk-upload'); ?>" id="apk_upload_form" method="post"
                enctype="multipart/form-data">
                 <div class="modal-body">
                    <div class="form-group">
                        <label for="version">APK Version<b class="require">*</b></label>
                        <input type="text" class="form-control" id="apk_version" name="apk_version"
                            placeholder="Enter version" required>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="profile_image">Upload Latest APK<b class="require">*</b></label>
                        <input type="file" class="form-control" id="apk_file" name="apk_file" accept=".apk" required>
                    </div>
                </div>

                <div class="modal-footer justify-content-start">
                    <button type="submit" id="submit_btn_apk" value="submit_btn_apk" name="submit_btn_apk"
                        class="btn btn-primary">Update APK FIle</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php if ($this->session->flashdata('success') != "") { ?>
    <div class="login_hide_show alert alert-success animated fadeInUp" style="color:#297401;">
        <strong style="color:#297401; "> <?= $this->session->flashdata('success') ?></strong>
    </div>
<?php } else if ($this->session->flashdata('message') != "") { ?>
        <div class="login_hide_show alert alert-danger animated fadeInUp" style="">
            <strong style=""> <?= $this->session->flashdata('message') ?></strong>
        </div>
<?php } elseif (validation_errors() != '') { ?>
        <div class="login_hide_show alert alert-danger animated fadeInUp">
            <strong> <?= validation_errors() ?></strong>
        </div>
<?php } ?>
</div>
</div>

<div id="custom_notifications" class="custom-notifications dsp_none">
    <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group"></ul>
    <div class="clearfix"></div>
    <div id="notif-group" class="tabbed_notifications"></div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>



<script>
    // jQuery is already loaded in header.php (CDN). Only load local jQuery as fallback.
    if (typeof window.jQuery === 'undefined') {
        document.write('<script src="<?= base_url() ?>assets/js/jquery.min.js"><\\/script>');
    }
</script>

<script type="text/javascript" src="<?= base_url() ?>assets/js/datatables.min.js"></script>

<script type="text/javascript" src="<?= base_url() ?>assets/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/js/pdfmake.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/js/vfs_fonts.js"></script>
<script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
<!-- <script src="<?= base_url() ?>assets/js/chartjs/chart.min.js"></script> -->
<!-- <script src="<?= base_url() ?>assets/js/progressbar/bootstrap-progressbar.min.js"></script> -->

<script type="text/javascript" src="<?= base_url() ?>assets/js/datepicker/daterangepicker.js"></script>
<!-- <script src="<?= base_url() ?>assets/js/datatables/js/jquery.dataTables.js"></script> -->
<!-- <script src="<?= base_url() ?>assets/js/datatables/tools/js/dataTables.tableTools.js"></script> -->

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="<?= base_url() ?>assets/js/summernote.min.js"></script>
<script src="<?= base_url() ?>assets/js/select2.min.js"></script>

<script src="<?= base_url() ?>assets/js/custom-lib.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>



<script>
    $(".alert").fadeTo(5000, 500).slideUp(500, function () {
        $(".alert").slideUp(500);
    });

</script>
<!-- <script>
    $(window).on('pageshow', function () {

        const $sidebar = $('.child_menu');
        const $activeItem = $('.nav.child_menu > li > a.active_cc');

        if ($sidebar.length && $activeItem.length) {
            $sidebar.animate({
                scrollTop: $activeItem.offset().top - $sidebar.offset().top
            }, 1500);
        }

    })
</script> -->

<!-- <script>
 const target = document.querySelector('.nav.child_menu');

const observer = new MutationObserver(() => {
    if ($(target).is(':visible')) {
        scrollToActiveItem();
    }
});

observer.observe(target, { attributes: true, attributeFilter: ['style', 'class'] });

function scrollToActiveItem() {
    const $sidebar = $('.child_menu');
    const $activeItem = $('.nav.child_menu > li > a.active_cc');

    if ($sidebar.length && $activeItem.length) {
        $sidebar.animate({
            scrollTop: $activeItem.offset().top - $sidebar.offset().top
        }, 1500);
    }
}
</script> -->
<script>
    $(document).ready(function () {
        $('#change_password').validate({
            rules: {
                current_password: {
                    required: true
                },
                new_password: {
                    required: true
                },
                confirm_password: {
                    required: true,
                    equalTo: '[name="new_password"]'
                },
            },
            messages: {
                current_password: {
                    required: "Please enter current password!"
                },
                new_password: {
                    required: "Please enter new password!"
                },
                confirm_password: {
                    required: "Please enter confirm password!",
                    equalTo: "Confirm password must match new password!"
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
                if (element.attr('name') === 'current_password') {
                    $('#current_password_error').text('');
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
        $('#current_password').on('keyup', function () {
            var current_password = $(this).val();
            if (current_password === '') {
                $('#current_password_error').text('');
                $('.new_pass').addClass('d-none');
                $('#submit_btn_change').prop('disabled', true);
                return;
            }
            if (!$(this).hasClass('is-invalid')) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/check_current_password_match_or_not',
                    method: 'post',
                    data: {
                        'current_password': current_password,
                    },
                    success: function (response) {
                        if (response == '0') {
                            $('#current_password_error').text("Old password does not match!");
                            $('#current_password_error').addClass('error');
                            $('.new_pass').addClass('d-none');
                            $('#submit_btn_change').prop('disabled', true);
                        } else {
                            $('#current_password_error').text("");
                            $('.new_pass').removeClass('d-none');
                            $('#submit_btn_change').prop('disabled', false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('AJAX Error: ' + textStatus, errorThrown);
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('.toggle_password').on('click', function () {
            var inputId = $(this).data('toggle');
            var input = $('#' + inputId);
            var icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
    $(document).ready(function () {
        $('form[action*="update-profile-image"]').validate({
            rules: {
                profile_image: {
                    required: true,
                    extension: "jpg|jpeg|png|gif",
                    filesize: 2097152
                }
            },
            messages: {
                profile_image: {
                    required: "Please select a profile image!",
                    extension: "Please select a valid image file (jpg, jpeg, png, gif)!",
                    filesize: "File size must be less than 2 MB!"
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        // Custom method for file size
        $.validator.addMethod('apkfilesize', function (value, element, param) {
            if (element.files.length === 0) {
                return true;
            }
            return element.files[0].size <= param;
        }, 'File size must be less than 2 MB.');
        $('#profile_image').on('change', function () {
            var $input = $(this);
            var $formGroup = $input.closest('.form-group');
            $formGroup.find('span.invalid-feedback, span.error').remove();
            $input.removeClass('is-invalid').addClass('is-valid');
        });

        $('#apk_upload_form').validate({
            ignore: [],
            rules: {
                apk_file: {
                    required: true,
                    apkfilesize: 524288000 // 500MB in bytes
                },
                apk_version: {
                    required: true,
                }
            },
            messages: {
                apk_file: {
                    required: "Please select an APK file!",
                    apkfilesize: "File size must be less than 500 MB!"
                },
                apk_version: {
                    required: "Please enter the APK version!",
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });

</script>

<!-- Global Double Submit Prevention -->
<script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            var $form = $(this);
            
            // Bypass forms that handle their own Ajax submit state
            if ($form.hasClass('no-global-disable')) {
                return true;
            }
            
            // Check if form is valid using jQuery Validate (if present)
            if (typeof $form.valid === 'function' && !$form.valid()) {
                // Validation failed, allow user to correct it
                return true; 
            }
            
            // If already submitted, stop it
            if ($form.data('submitted') === true) {
                e.preventDefault();
                return false;
            }
            
            // Mark as submitted
            $form.data('submitted', true);
            
            var $submitBtn = $form.find('button[type="submit"], input[type="submit"], button[name="submit_btn"]');
            
            // Preserve button name/value as hidden input before disabling it
            $submitBtn.each(function() {
                var btn = $(this);
                if (btn.attr('name')) {
                    var btnValue = btn.val() || btn.attr('value') || '';
                    $form.append('<input type="hidden" name="' + btn.attr('name') + '" value="' + btnValue + '">');
                }
            });

            // Disable button and change text
            setTimeout(function() {
                $submitBtn.each(function() {
                    var btn = $(this);
                    if (btn.is('button')) {
                        btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
                    } else {
                        btn.val('Processing...').prop('disabled', true);
                    }
                });
            }, 10);
            
            return true;
        });
    });
</script>

</body>

</html>
