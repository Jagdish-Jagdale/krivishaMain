<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?= base_url('assets\images\krivisha_logo.png'); ?>" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Admin Login</title>
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/font-awesome/css/font-awesome.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/animate.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/standard.css'); ?>" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/custom-front.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/salon-style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<style>
    .error {
        color: red;

    }

    .background_GB {
        background-color: #ffffff;
        overflow: hidden;
        position: relative;
    }

    .alert {
        position: absolute;
        bottom: 0;
        right: 0;
        margin: 0px 30px 65px 0px;
    }

    .toggle_password {
        position: absolute;
        right: 10px;
        top: 10px;
        font-size: 15px;
        cursor: pointer;
    }

    #password-error {}

    input {
        margin-bottom: 0px !important;
    }

    .login_bg {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;

        position: relative;

    }

    #wrapper {
        width: 100%;
    }

    .login-input {
        margin-bottom: 20px !important;
    }

    .float_btn {
        position: absolute;
        top: 25px;
        right: 20px;
    }

    .version_text {
        margin-bottom: 6px;
    }
</style>

<body>
    <?php
    $version = '';
    $files = glob(FCPATH . 'assets/uploads/apkversion-*.apk'); // find matching files
    
    if (!empty($files)) {
        // Take latest file (sorted by modified time)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latest_file = basename($files[0]); // e.g. apkversion-12.8.apk
    
        // Extract version number from filename
        if (preg_match('/apkversion-(.*)\.apk$/', $latest_file, $matches)) {
            $version = $matches[1];
        }
    }
    ?>
    <div class="login_bg" style="background-image:url(<?= base_url(); ?>assets/images/5153829.jpg);">
        <a class="hiddenanchor" id="toregister"></a>
        <a class="hiddenanchor" id="tologin"></a>
        <span class="float_btn">
            <p class="version_text">Version-<?= $version ?></p>
            <a href="<?= base_url('assets/uploads/apklatest.apk') ?>" class="btn btn-success" download>Download Latest
                APK</a>
        </span>
        <div id="wrapper">
            <div id="login" class="animate form">
                <section class="login_content">



                    <form method="post" name="login_form" id="login_form">
                        <h1>Welcome To Krivisha</h1>
                        <div class="text-left login-input">
                            <input onkeyup="convert_letter_small(this)" autocomplete="off" type="text"
                                class="form-control" name="email" id="email" placeholder="Username" />
                        </div>
                        <div class="text-left login-input" style="position: relative;">
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="Password" />
                            <span class="toggle_password" onclick="togglePassword('password', 'showIcon', 'hideIcon')">
                                <i title="Show Password" class="fa fa-eye" aria-hidden="true" id="showIcon"></i>
                                <i title="Hide Password" class="fa fa-eye-slash" aria-hidden="true" id="hideIcon"
                                    style="display:none;"></i>
                            </span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <a href="<?= base_url() ?>privacy_policy">Privacy Policy</a>
                            <a href="<?= base_url() ?>forgot_password">Forgot Password ?</a>
                        </div>
                        <div>
                            <button class="submit_button"
                                style="box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2), 0 3px 10px 0 rgba(0, 0, 0, 0.10);"
                                type="submit" class="btn btn-light submit">Log in</button>

                        </div>
                        <div class="clearfix"></div>
                        <div class="separator">

                            <div class="clearfix"></div>
                            <br />
                            <div>
                                <p>©<?php echo date('Y'); ?>Krivisha | All Rights Reserved</p>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
    </div>
    <?php if (isset($_GET['logout']) && $_GET['logout'] == 'true') { ?>
        <div class="alert alert-success animated fadeInUp">
            <strong>Success!</strong> You have successfully logged out.
        </div>
    <?php } ?>
    </div>



    <!-- Mainly scripts -->
    <script src="<?= base_url('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.min.js'); ?>"></script>
    <script src="<?= base_url('assets/js/jquery.validate.min.js'); ?>"></script>

    <script>

        $(".alert").fadeTo(5000, 500).slideUp(500, function () {
            $(".alert").slideUp(500);
        });


        $(document).ready(function () {
            jQuery.validator.addMethod("validate_email", function (value, element) {
                if (/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/.test(value)) {
                    return true;
                } else {
                    return false;
                }
            }, "Please enter a valid Email.");
            jQuery.validator.addMethod("noHTMLtags", function (value, element) {
                if (this.optional(element) || /<\/?[^>]+(>|$)/g.test(value)) {
                    if (value == "") {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }, "HTML tags are Not allowed.");


            $.validator.addMethod("validEmail", function (value, element) {
                var lowercaseValue = value.toLowerCase();
                return this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(lowercaseValue);
            }, "Please enter a valid email address");

            $.validator.addMethod("lowercase", function (value, element) {
                var lowercaseValue = value.toLowerCase();
                $(element).val(lowercaseValue);
                return true;
            }, "Please enter in lowercase");

            $('#login_form').validate({
                rules: {
                    email: {
                        lowercase: true,
                        validEmail: true,
                        required: true,
                        validate_email: true,
                        noHTMLtags: true,

                    },
                    password: {
                        required: true,
                        noHTMLtags: true,
                    },
                },
                messages: {
                    email: {
                        required: "Please enter email",
                        validate_email: "Please enter valid email",
                        noHTMLtags: "HTML tags not allowed!",
                    },
                    password: {
                        required: "Please enter password",
                        noHTMLtags: "HTML tags not allowed!",
                    },
                },
                submitHandler: function (form) {
                    form.submit();
                }

            });
        });
    </script>
    <script>
        function togglePassword(inputId, showIconId, hideIconId) {
            var passwordInput = document.getElementById(inputId);
            var showIcon = document.getElementById(showIconId);
            var hideIcon = document.getElementById(hideIconId);

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                showIcon.style.display = "none";
                hideIcon.style.display = "inline-block";
            } else {
                passwordInput.type = "password";
                showIcon.style.display = "inline-block";
                hideIcon.style.display = "none";
            }
        }
        function convert_letter_small(input) {
            input.value = input.value.toLowerCase();
        }
    </script>

    <?php include('footer.php'); ?>