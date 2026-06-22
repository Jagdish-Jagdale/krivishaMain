<!DOCTYPE html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Login</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?=base_url();?>assets/images/logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="<?= base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet">

    <link href="<?= base_url() ?>assets/fonts/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/animate.min.css" rel="stylesheet">

    <link href="<?= base_url() ?>assets/css/custom-front.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/icheck/flat/green.css" rel="stylesheet">


    <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>

    <style>
        .error {
            color: red;
        }
		.background_GB {
			background-color: #ffffff;
			overflow: hidden;
		}
        .toggle_password {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 15px;
            cursor: pointer;
        }
        .toggle_password_2 {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 15px;
            cursor: pointer;
        }
      
        input{
            margin-bottom:0px !important;
        }
        .login_bg{
            height: 111vh;
        }
        .login-input{
            margin-bottom:20px !important;
        }
    </style>
</head>

<body class="background_GB">

    <div class="login_bg" style="background-image:url(<?=base_url();?>assets/images/5.jpg);">
        <a class="hiddenanchor" id="toregister"></a>
        <a class="hiddenanchor" id="tologin"></a>

        <div id="wrapper">
            <div id="login" class="animate form">
                <section class="login_content">
                    <form method="post" name="forgot_pass_form" id="forgot_pass_form">
                        <h1>RESET PASSWORD</h1>
                        <div class="text-left login-input">
                        <input onkeyup="convert_letter_small(this)" autocomplete="off" type="text" class="form-control" name="email" id="email" placeholder="Username" />
						 <p style="color:red;text:bold;" id="unique_email"></p>
                        </div>
                            <div class="text-left login-input" style="position: relative;text-align: left;">
                                <input autocomplete="off" type="password" class="form-control" name="password" id="password" placeholder="New Password" />
                                    <span class="toggle_password" onclick="togglePassword('password', 'new_pass', 'new_hideIcon')">
                                        <i  data-toggle="tooltip" title="Show New Password" class="fa fa-eye" aria-hidden="true" id="new_pass"></i>
                                        <i data-toggle="tooltip" title="Hide New Password" class="fa fa-eye-slash" aria-hidden="true" id="new_hideIcon" style="display:none;"></i>
                                    </span>
                            </div>
                            <div class="text-left login-input" style="position: relative;text-align: left;">
                                <input type="password" class="form-control" name="password1" id="password1" placeholder="Confirm Password"/>
                                <span class="toggle_password_2" onclick="togglePassword('password1', 'confirm_showIcon', 'confirm_hideIcon')">
                                    <i data-toggle="tooltip" title="Show Confirm Password" class="fa fa-eye" aria-hidden="true" id="confirm_showIcon"></i>
                                    <i data-toggle="tooltip" title="Hide Confirm Password" class="fa fa-eye-slash" aria-hidden="true" id="confirm_hideIcon" style="display:none;"></i>
                                </span>
                            </div>
                            <div style="text-align:right;margin-bottom:8px;">
                              <a  href="<?=base_url()?>">Login ?</a>
                            </div>
                        <div>
                            <button class="submit_button" id="set_btn" style="box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2), 0 3px 10px 0 rgba(0, 0, 0, 0.10);" type="submit" class="btn btn-light submit">Reset Password</button>
                            
                        </div>
                        <div class="clearfix"></div>
                        <div class="separator">
                           
                            <div class="clearfix"></div>
                            <br />
                            <div>
                                <p>©<?php echo date('Y');?>Krivisha | All Rights Reserved</p>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>



    <?php if ($this->session->flashdata('success') != "") { ?>
        <div class="alert alert-success animated fadeInUp">
            <strong>Success!</strong> <?= $this->session->flashdata('success') ?>
        </div>
    <?php } else if ($this->session->flashdata('message') != "") { ?>
        <div class="alert alert-danger animated fadeInUp">
            <strong>Error!</strong> <?= $this->session->flashdata('message') ?>
        </div>
    <?php } elseif (validation_errors() != '') { ?>
        <div class="alert alert-danger animated fadeInUp">
            <strong>Error!</strong> <?= validation_errors() ?>
        </div>
    <?php } ?>
</body>
</html>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script>
    $(".alert").fadeTo(5000, 500).slideUp(500, function() {
        $(".alert").slideUp(500);
    });

    $.validator.addMethod("lowercase", function(value, element) {
            var lowercaseValue = value.toLowerCase();
            $(element).val(lowercaseValue);
            return true;
        }, "Please enter in lowercase");

 $(document).ready(function () {
            $('#forgot_pass_form').validate({
                rules: {
                    email: {
                        lowercase:true,
                        required: true,
                        email: true ,
                    },
                    password: {
                        required: true,
                    },
                    password1: {
                        required: true,
                        equalTo: "#password",
                    },
                },
                messages: {
                    email: {
                        required: "Please enter email",
                        email: "Please enter a valid email", 
                    },
                    password: {
                        required: "Please enter password",
                    },
                    password1: {
                        required: "Please enter confirm password",
                        equalTo: "Password does not match", 
                    },
                },
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
