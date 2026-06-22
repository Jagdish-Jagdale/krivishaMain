<?php
$access = $this->Admin_model->get_previlege();
$profile = $this->Admin_model->get_user_profile();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= !empty($title) ? $title : ($this->uri->segment(1) ? ucwords(str_replace('_', ' ', $this->uri->segment(1))) : 'Dashboard') ?></title>

    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url(); ?>assets/images/krivisha_logo.png">
    <link href="<?= base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/select/select2.min.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.3.7/dist/apexcharts.min.css"> -->
    <link href="<?= base_url() ?>assets/fonts/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/standard.css" rel="stylesheet">
    <!-- <link href="<?= base_url() ?>assets/css/jquery-ui.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="<?= base_url() ?>assets/css/custom-front.css?v=<?= time() ?>" rel="stylesheet">
    <!-- <link href="<?= base_url() ?>assets/css/chosen.css" rel="stylesheet"> 
    <link href="<?= base_url() ?>assets/css/chosen.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <link href="<?= base_url() ?>assets/css/datatables/tools/css/dataTables.tableTools.css" rel="stylesheet">
    <link href="<?= base_url() ?>assets/css/datatables/tools/css/dataTables.tableTools.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/datatables.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@dmuy/timepicker@2.0.2/dist/mdtimepicker.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css' rel='stylesheet' />
    <script src="<?= base_url() ?>assets/js/jquery.min.js"></script>
    <script src="<?= base_url() ?>assets/js/jquery.validate.min.js"></script>
    <style>
        .error {
            color: red;
        }

        .top-btn-btn .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 160px;
            padding: 10px 0;
        }

        .top-btn-btn .dropdown-menu li a {
            padding: 8px 20px;
            display: block;
            color: #333;
        }

        .top-btn-btn .dropdown-menu li a:hover {
            background-color: #f5f5f5;
        }

        .img-profile-circle {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #f5f5f5;
            /* Optional: adds a border */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            /* Optional: subtle shadow */
        }

        .buttons,
        button,
        .btn {
            margin-bottom: 1px;
            margin-right: 5px;
            margin-top: 10px !important;
        }

        .add_option {
            margin-bottom: 0;
            margin-right: 0px;
            margin-top: -3px !important;
        }
    </style>
    <style id="dark-theme-overrides">
        /* Normal (Expanded) Mode */
        body .container.body .left_col,
        body .container.body .left_col.scroll-view,
        .main_menu_side,
        .menu_section,
        .nav.side-menu {
            width: 250px !important;
            background: #eff6ff !important;
            border-right: 1px solid #bfdbfe;
            color: #334155;
        }

        body .right_col {
            margin-left: 250px !important;
        }

        body .main_container .top_nav {
            margin-left: 250px !important;
        }

        /* Collapsed Mode */
        body.nav-sm .container.body .left_col,
        body.nav-sm .container.body .left_col.scroll-view,
        body.nav-sm .main_menu_side,
        body.nav-sm .menu_section,
        body.nav-sm .nav.side-menu {
            width: 70px !important;
            background: #eff6ff !important;
            border-right: 1px solid #bfdbfe;
        }
        body.nav-sm .right_col {
            margin-left: 70px !important;
        }
        body.nav-sm .main_container .top_nav {
            margin-left: 70px !important;
        }
        body.nav-sm .navbar.nav_title {
            background: #eff6ff !important;
            border-bottom: 1px solid #bfdbfe !important;
            width: 70px !important;
        }
        body.nav-sm .navbar.nav_title span {
            display: none !important;
        }

        /* Logo bar */
        .navbar.nav_title {
            background: #eff6ff !important;
            border-bottom: 1px solid #bfdbfe !important;
        }
        .navbar.nav_title span {
            color: #1e293b !important;
        }

        /* Fix text and layout in collapsed mode */
        body.nav-sm .nav.side-menu > li > a {
            text-align: center !important;
            padding: 15px 5px !important;
            display: block !important;
        }
        body.nav-sm .nav.side-menu > li > a > b,
        body.nav-sm .nav.side-menu > li > a > span.fa {
            display: none !important;
        }
        body.nav-sm .nav.side-menu > li > a > i.nav-icon,
        body.nav-sm .nav.side-menu > li > a > i.fa-dashboard {
            width: 100% !important;
            font-size: 22px !important;
            margin: 0 !important;
            text-align: center !important;
        }

        /* General Sidebar Styles */
        .nav.side-menu > li > a {
            color: #475569 !important;
            font-weight: 500;
            padding: 13px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #bfdbfe;
            background-color: transparent !important;
        }

        .nav.side-menu > li > a:hover {
            background: #eff6ff !important;
            color: #2563eb !important;
        }

        .nav.side-menu > li.active > a,
        .nav.side-menu > li.active_parent > a,
        .nav.side-menu > li.active-sm > a {
            background: #2563eb !important;
            color: #ffffff !important;
        }

        /* Blue left border indicator for active main menu */
        .nav.side-menu > li.active > a,
        .nav.side-menu > li.active_parent > a,
        .nav.side-menu > li.active-sm > a {
            border-left: 4px solid #1d4ed8 !important;
            padding-left: 16px !important;
        }

        /* Override legacy Gentelella styles for active-sm */
        body.nav-sm .nav.side-menu li.active-sm {
            border-right: none !important;
        }
        body.nav-sm .nav.side-menu > li.active-sm > a {
            padding-left: 5px !important;
        }

        /* Ensure header_sticky doesn't overlap sidebar */
        body.nav-md .header_sticky {
            margin-left: 230px;
        }
        body.nav-sm .header_sticky {
            margin-left: 70px;
        }

        /* Suppress legacy bootstrap caret pseudo-elements on top dropdown */
        .dropdown-toggle::after { display: none !important; }
        .dropdown-toggle .caret { display: none !important; }

        .nav.side-menu > li > a > i {
            width: 30px;
            font-size: 16px;
            margin-right: 5px;
            text-align: left;
            color: #1e293b !important;
            transition: color 0.2s ease;
        }

        .nav.side-menu > li.active > a > i,
        .nav.side-menu > li.active_parent > a > i,
        .nav.side-menu > li.active-sm > a > i {
            color: #ffffff !important;
        }

        .nav.side-menu > li > a:hover > i {
            color: #2563eb !important;
        }

        .nav.side-menu > li.active > a:hover > i,
        .nav.side-menu > li.active_parent > a:hover > i,
        .nav.side-menu > li.active-sm > a:hover > i {
            color: #ffffff !important;
        }

        /* Sidebar chevron */
        .nav.side-menu > li > a > span.fa {
            margin-left: auto;
            float: none !important;
            margin-top: 0 !important;
            color: #1e293b !important;
            transition: color 0.2s ease;
        }
        body.nav-sm .nav.side-menu > li > a > span.fa {
            display: none !important;
        }

        .nav.side-menu > li.active > a > span.fa,
        .nav.side-menu > li.active_parent > a > span.fa,
        .nav.side-menu > li.active-sm > a > span.fa {
            color: #ffffff !important;
        }

        .nav.side-menu > li > a:hover > span.fa {
            color: #2563eb !important;
        }

        .nav.side-menu > li.active > a:hover > span.fa,
        .nav.side-menu > li.active_parent > a:hover > span.fa,
        .nav.side-menu > li.active-sm > a:hover > span.fa {
            color: #ffffff !important;
        }

        /* Submenu container */
        .child_menu {
            background: #dbeafe !important;
            border: none !important;
            border-top: 1px solid #bfdbfe !important;
        }
        /* Force accordion behavior in Expanded Mode (nav-md) */
        body.nav-md .child_menu {
            position: relative !important;
            left: auto !important;
            top: auto !important;
            bottom: auto !important;
            width: 100% !important;
            height: auto !important;
            box-shadow: none !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Flyout behavior in Collapsed Mode (nav-sm) */
        body.nav-sm .child_menu {
            position: absolute !important;
            left: 100% !important;
            top: 0 !important;
            border: 1px solid #bfdbfe !important;
            box-shadow: 4px 4px 16px rgba(37,99,235,0.08) !important;
            width: 220px !important;
            z-index: 9999 !important;
        }
        
        body.nav-sm .nav.side-menu > li {
            position: relative;
        }

        /* Submenu section headers */
        .child_menu h4 {
            color: #2563eb !important;
            text-transform: uppercase;
            font-size: 11px !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 12px 20px 8px 20px !important;
            border-top: 1px solid #bfdbfe !important;
            border-bottom: 1px solid #bfdbfe !important;
            margin: 0 !important;
            cursor: default;
            background: #eff6ff;
        }

        /* Submenu links */
        .nav.child_menu > li > a {
            color: #475569 !important;
            padding: 9px 20px 9px 40px !important;
            font-size: 13px;
            display: flex;
            align-items: center;
            position: relative;
            border: none !important;
        }

        .nav.child_menu > li > a:hover {
            color: #2563eb !important;
            background: #eff6ff !important;
        }

        .nav.child_menu > li > a.active_cc {
            color: #2563eb !important;
            font-weight: 600;
            background: #dbeafe !important;
        }

        /* Base Icon for ALL Submenu Links */
        .nav.child_menu > li > a::before {
            font-family: "Font Awesome 6 Free", "FontAwesome" !important;
            font-weight: 900 !important;
            content: "\f03a" !important;
            margin-right: 10px !important;
            font-size: 12px !important;
            color: #1e293b !important;
            opacity: 0.85 !important;
            display: inline-block !important;
            transition: color 0.2s ease, opacity 0.2s ease;
        }

        .nav.child_menu > li > a:hover::before,
        .nav.child_menu > li > a.active_cc::before {
            color: #2563eb !important;
            opacity: 1 !important;
        }

        /* Override for Add/Create Links */
        .nav.child_menu > li > a[href*="add"]::before,
        .nav.child_menu > li > a[href*="Create"]::before,
        .nav.child_menu > li > a[class*="add"]::before,
        .nav.child_menu > li > a[href*="department"]::before,
        .nav.child_menu > li > a[href*="employee"]:not([href*="list"])::before {
            content: "\f055" !important;
        }

        /* Scrollbar styling for light sidebar */
        .left_col.scroll-view::-webkit-scrollbar {
            width: 4px;
        }
        .left_col.scroll-view::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .left_col.scroll-view::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
</head>

<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <div class="left_col">
                <div class="left_col scroll-view">
                    <div class="navbar nav_title" style="border: 0; padding: 0 15px; height: 60px; text-align: left; display: flex; align-items: center; justify-content: flex-start; gap: 12px; border-bottom: 1px solid #bfdbfe; background: #eff6ff;">
                        <img src="<?= base_url() ?>assets/images/krivisha_logo.png" alt="Logo" style="width: 40px; height: 40px; border-radius: 8px; object-fit: contain; background: transparent;">
                        <span style="color: #1e293b; font-size: 20px; font-weight: bold; letter-spacing: 1px;">KRIVISHA</span>
                    </div>
                    <div class="clearfix"></div>
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <ul class="nav side-menu">
                                <?php if ($profile->is_admin == '1' || in_array('dashboard', $access)) { ?>
                                    <li><a href="<?= base_url() ?>dashboard"><i
                                                class="fa fa-dashboard"></i><b>Dashboard</b></a></li>
                                <?php } ?>
                                <?php if ($profile->is_admin == '1' || (in_array('add_brand', $access) || in_array('brand_list', $access) || in_array('add_location', $access) || in_array('location_list', $access)  || in_array('add_transport', $access) || in_array('transport_list', $access) || in_array('add_customer', $access) || in_array('customer_list', $access) || in_array('add_article', $access) || in_array('article_list', $access) || in_array('production_bom_list', $access) || in_array('add_rm', $access) || in_array('rm_rejection_list', $access)
                                    || in_array('rm_list', $access)  || in_array('add_mb', $access) || in_array('mb_list', $access) || in_array('plant_list', $access) || in_array('machine_list', $access) || in_array('problems_list', $access) || in_array('uom_list', $access) || in_array('krivisha_department', $access) || in_array('krivisha_employee', $access) || in_array('krivisha_employee_list', $access) || in_array('remark_master', $access))) { ?>
                                    <li class="master" id="master"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-database nav-icon"></i><b
                                                class="nav-title">Master</b><span
                                                class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">
                                            <?php if ($profile->is_admin == '1' || in_array('add_brand', $access) || in_array('brand_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Brand</h4>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('add_brand', $access)) { ?>
                                                <li><a class="add_brand" href="<?= base_url(); ?>add_brand">Add Brand </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('brand_list', $access)) { ?>
                                                <li><a class="brand_list" href="<?= base_url(); ?>brand_list">Brand List</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('add_location', $access) || in_array('location_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Location</h4>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('add_location', $access)) { ?>
                                                <li><a class="add_location" href="<?= base_url(); ?>add_location">Add
                                                        Location</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('location_list', $access)) { ?>
                                                <li><a class="location_list" href="<?= base_url(); ?>location_list">Location
                                                        List</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_transport', $access) || in_array('transport_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Transport</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_transport', $access)) { ?>
                                                <li><a class="add_transport" href="<?= base_url(); ?>add_transport">Add
                                                        Transport</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('transport_list', $access)) { ?>
                                                <li><a class="transport_list" href="<?= base_url(); ?>transport_list">Transport
                                                        List</a> </li>
                                            <?php } ?>


                                            <?php if ($profile->is_admin == '1' || in_array('add_customer', $access) || in_array('customer_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Party</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_customer', $access)) { ?>
                                                <li><a class="add_customer" href="<?= base_url(); ?>add_customer">Add Party</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('customer_list', $access)) { ?>
                                                <li><a class="customer_list" href="<?= base_url(); ?>customer_list">Party
                                                        List</a> </li>
                                            <?php } ?>


                                            <?php if ($profile->is_admin == '1' || in_array('add_article', $access) || in_array('article_list', $access) || in_array('production_bom_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Article/ Mould Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_article', $access)) { ?>
                                                <li><a class="add_article" href="<?= base_url(); ?>add_article">Add Article/
                                                        Mould</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('article_list', $access)) { ?>
                                                <li><a class="article_list" href="<?= base_url(); ?>article_list">Article/ Mould
                                                        List</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('production_bom_list', $access)) { ?>
                                                <li><a class="production_bom_list"
                                                        href="<?= base_url(); ?>production_bom_list">Production BOM
                                                        List</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_rm', $access) || in_array('rm_rejection_list', $access) || in_array('rm_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Raw Material Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_rm', $access)) { ?>
                                                <li><a class="add_rm" href="<?= base_url(); ?>add_rm">Add Raw Material</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('rm_rejection_list', $access)) { ?>
                                                <li><a class="rm_rejection_list" href="<?= base_url(); ?>rm_rejection_list">Raw
                                                        Material Rejected List</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('rm_list', $access)) { ?>
                                                <li><a class="rm_list" href="<?= base_url(); ?>rm_list">Raw Material List</a>
                                                </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_mb', $access) || in_array('mb_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Master Batch</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('add_mb', $access)) { ?>
                                                <li><a class="add_mb" href="<?= base_url(); ?>add_mb">Add Master Batch</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('mb_list', $access)) { ?>
                                                <li><a class="mb_list" href="<?= base_url(); ?>mb_list">Master Batch List</a>
                                                </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('plant_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Plant Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('plant_list', $access)) { ?>
                                                <li><a class="add_plant" href="<?= base_url(); ?>plant_list"> Add
                                                        Plant</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('machine_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Machine Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('machine_list', $access)) { ?>
                                                <li><a class="add_machine" href="<?= base_url(); ?>machine_list"> Add
                                                        Machine</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('impression_rate', $access)) { ?>
                                            <h4 style="margin-top:0px;">Impression Rate Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('impression_rate', $access)) { ?>
                                                <li><a class="impression_rate" href="<?= base_url(); ?>impression_rate"> Impression Rate</a> </li>
                                            <?php } ?>



                                            <!-- <h4 style="margin-top:0px;">Printing
                                            Unit
                                            Master</h4>

                                        <li><a class="add_printing_unit" href="<?= base_url(); ?>printing_unit_list">
                                                Add
                                                Printing Unit</a> </li> -->


                                            <?php if ($profile->is_admin == '1' || in_array('problems_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Problems Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('problems_list', $access)) { ?>
                                                <li><a class="add_problems" href="<?= base_url(); ?>problems_list"> Add
                                                        Problems</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('uom_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Unit of Measurement</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('uom_list', $access)) { ?>
                                                <li><a class="uom_list" href="<?= base_url(); ?>uom_list">Add UOM
                                                    </a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('krivisha_department', $access) || in_array('krivisha_employee', $access) || in_array('krivisha_employee_list', $access)) { ?>
                                            <h4 style="margin-top:0px;">Employee Management</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('krivisha_department', $access)) { ?>
                                                <li><a class="krivisha_department"
                                                        href="<?= base_url(); ?>krivisha_department">Add Department
                                                    </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('krivisha_employee', $access)) { ?>
                                                <li><a class="krivisha_employee" href="<?= base_url(); ?>krivisha_employee">Add
                                                        Employee
                                                    </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('krivisha_employee_list', $access)) { ?>
                                                <li><a class="krivisha_employee_list"
                                                        href="<?= base_url(); ?>krivisha_employee_list">Employee List
                                                    </a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('extra_payment_master', $access)) { ?>
                                            <h4 style="margin-top:0px;">Extra Payment Option</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('extra_payment_master', $access)) { ?>
                                                <li><a class="extra_payment_master"
                                                        href="<?= base_url(); ?>extra_payment_master">Extra Payment Option
                                                    </a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('remark_master', $access)) { ?>
                                            <h4 style="margin-top:0px;">Remark Master</h4>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('remark_master', $access)) { ?>
                                                <li><a class="remark_master"
                                                        href="<?= base_url(); ?>remark_master">Production Idle State Reasons
                                                    </a> </li>
                                            <?php } ?>

                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- <li id="product_master"><a href="javascript:void(0);"><i class="fa-solid fa-gears nav-icon"></i>
                                <b
                                            class="nav-title">Production</b><span class="fa fa-chevron-down down-icon"></span></a>
                                    <ul class="nav child_menu" style="display: none">
                                        <h4 style="margin-top:0px;">Production Schedule</h4>
                                        <li><a class="production_schedule"
                                                href="<?= base_url(); ?>production_schedule">Production Schedule</a>
                                        </li>
                                        <li><a class="store_rm" href="<?= base_url(); ?>store_rm">Store RM</a> </li>

                                        <h4 style="margin-top:0px;">Production Report</h4>
                                        <li><a class="add_production" href="<?= base_url(); ?>add_production">Add
                                                Production </a> </li>
                                        <li><a class="production_report_list"
                                                href="<?= base_url(); ?>production_report_list">Production list</a>
                                        </li>
                                    </ul>
                                </li> -->

                                <!-- Production Module -->

                                <?php if ($profile->is_admin == '1' || (in_array('production_schedule', $access) || in_array('store_rm', $access) || in_array('add_production', $access) || in_array('production_report_list', $access))) { ?>
                                    <li id="product_master"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-gears nav-icon"></i>
                                            <b class="nav-title">Production</b><span
                                                class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">
                                            <h4 style="margin-top:0px;">Production Schedule</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('production_schedule', $access)) { ?>
                                                <li><a class="production_schedule"
                                                        href="<?= base_url(); ?>production_schedule">Production Schedule</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('store_rm', $access)) { ?>
                                                <li><a class="store_rm" href="<?= base_url(); ?>store_rm">Store RM</a> </li>
                                            <?php } ?>

                                            <h4 style="margin-top:0px;">Production Report</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('add_production', $access)) { ?>
                                                <li><a class="add_production" href="<?= base_url(); ?>add_production">Add
                                                        Production </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('production_report_list', $access)) { ?>
                                                <li><a class="production_report_list"
                                                        href="<?= base_url(); ?>production_report_list">Production list</a>
                                                </li>
                                                <li><a class="production_report"
                                                        href="<?= base_url(); ?>production_report">Production Report</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('production_report_list', $access)) { ?>
                                                <li><a class="process_parameter_list"
                                                        href="<?= base_url(); ?>process_parameter_list">Process Parameter Sheet</a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- Maintenance Module -->

                                <?php if ($profile->is_admin == '1' || (in_array('add_maintenance', $access) || in_array('production_maintenance_list', $access) || in_array('maintenance_list_details', $access) || in_array('maintenance_report', $access))) { ?>
                                    <li id="maintenance"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-screwdriver-wrench nav-icon"></i><b
                                                class="nav-title">Maintenance</b><span
                                                class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">

                                            <h4 style="margin-top:0px;">Maintenance</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('add_maintenance', $access)) { ?>
                                                <li><a class="add_maintenance" href="<?= base_url(); ?>add_maintenance">Add
                                                        Maintenance</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('production_maintenance_list', $access)) { ?>
                                                <li><a class="production_maintenance_list"
                                                        href="<?= base_url(); ?>production_maintenance_list">Production
                                                        Maintenance
                                                        List</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('maintenance_list_details', $access)) { ?>
                                                <li><a class="maintenance_list_detail"
                                                        href="<?= base_url(); ?>maintenance_list_details">Maintenance
                                                        List Details </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('maintenance_report', $access)) { ?>
                                                <li><a class="maintenance_report"
                                                        href="<?= base_url(); ?>maintenance_report">Maintenance Report</a> </li>
                                            <?php } ?>

                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- Task Management Module -->

                                <?php if ($profile->is_admin == '1' || (in_array('add_order', $access) || in_array('order_list', $access) || in_array('add_task', $access) || in_array('task_list', $access) || in_array('manual_task_access', $access) || in_array('salesman_on_fields_details', $access)  || in_array('auto_task_list', $access) || in_array('auto_task_access', $access))) { ?>
                                    <li id="task_management"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-list-check nav-icon"></i><b class="nav-title">Task
                                                Mangement</b><span class="fa fa-chevron-down down-icon"></span></a>

                                        <ul class="nav child_menu" style="display: none">
                                            <h4 style="margin-top:0px;">Create Order</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('add_order', $access)) { ?>
                                                <li><a class="add_order" href="<?= base_url(); ?>add_order">Create
                                                        Order </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('order_list', $access)) { ?>
                                                <li><a class="order_list" href="<?= base_url(); ?>order_list">Order List</a>
                                                </li>
                                            <?php } ?>
                                            <h4 style="margin-top:0px;">Task Management</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('add_task', $access)) { ?>
                                                <li><a class="add_task" href="<?= base_url(); ?>add_task">Add
                                                        Task </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('task_list', $access) || in_array('manual_task_access', $access)) { ?>
                                                <li><a class="task_list" href="<?= base_url(); ?>task_list">Manual Task list</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('auto_task_list', $access) || in_array('auto_task_access', $access)) { ?>
                                                <li><a class="auto_task_list" href="<?= base_url(); ?>auto_task_list">Auto Task
                                                        list</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('salesman_on_fields_details', $access)) { ?>
                                                <li><a class="salesman_on_fields_details" href="<?= base_url(); ?>salesman_on_fields_details">Salesman On Fields</a></li>
                                                        
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- Printing Unit Module -->

                                <?php if ($profile->is_admin == '1' || (in_array('printing_unit_report', $access) || in_array('printing_report', $access) || in_array('pwo_status', $access))) { ?>
                                    <li id="printing_unit"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-print nav-icon"></i><b class="nav-title">Printing
                                                Unit</b><span class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">

                                            <h4 style="margin-top:0px;">Printing Unit</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('printing_unit_report', $access)) { ?>
                                                <!-- <li>
                                                    <a class="printing_unit_report" href="<?= base_url(); ?>printing_unit_report">Printing Unit Report
                                                    </a>
                                                </li> -->
                                                <li>
                                                    <a class="printing_order_list"
                                                        href="<?= base_url(); ?>printing_order_list">Printing Order List
                                                    </a>
                                                </li>
                                                <!-- <li>
                                                    <a class="job_work_printing_unit_report_list"
                                                        href="<?= base_url(); ?>job_work_printing_unit_report_list">Job Work List</a>
                                                </li> -->
                                                <li>
                                                    <a class="material_report_printing_unit_report_list"
                                                        href="<?= base_url(); ?>material_report_printing_unit_report_list">Material
                                                        Report List</a>
                                                </li>
                                                <li>
                                                    <a class="printing_report" href="<?= base_url(); ?>printing_report">Printing Report</a>
                                                </li>
                                            <?php } ?>

                                            <!-- <?php if ($profile->is_admin == '1' || in_array('pwo_status', $access)) { ?>
                                                <li><a class="pwo_status" href="<?= base_url(); ?>pwo_status">POW Status</a> </li>
                                            <?php } ?> -->

                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- Logistics Module -->

                                <?php if ($profile->is_admin == '1' || (in_array('outward_order_list', $access) || in_array('own_vehicle', $access) || in_array('dispach_order_list', $access) || in_array('own_vehicle_list', $access) || in_array('transport_report', $access))) { ?>
                                    <li id="logistics"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-truck-fast nav-icon"></i><b
                                                class="nav-title">Logistics</b><span
                                                class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">

                                            <h4 style="margin-top:0px;">Outward Transport</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('outward_order_list', $access)) { ?>
                                                <li><a class="outward_order_list"
                                                        href="<?= base_url(); ?>outward_order_list">Outward Transport</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('dispach_order_list', $access)) { ?>
                                                <li><a class="dispach_order_list"
                                                        href="<?= base_url(); ?>dispach_order_list">Dispatched Orders</a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('transport_report', $access)) { ?>
                                                <li><a class="transport_report"
                                                        href="<?= base_url(); ?>transport_report">Transport Report</a> </li>
                                            <?php } ?>

                                            <!-- <li><a class="production_maintenance_list" href="<?= base_url(); ?>production_maintenance_list">Outward Transport
                                                List</a> </li> -->
                                            <?php if ($profile->is_admin == '1' || in_array('own_vehicle', $access)) { ?>
                                                <li><a class="own_vehicle" href="<?= base_url(); ?>own_vehicle">Own Vehicle
                                                    </a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('own_vehicle_list', $access)) { ?>
                                                <li><a class="own_vehicle_list"
                                                        href="<?= base_url(); ?>own_vehicle_list">Own Vehicle List</a>
                                                </li>
                                            <?php } ?>

                                        </ul>
                                    </li>
                                <?php } ?>

                                <!-- Purchase And Sales Module -->

                                <!-- <?php if ($profile->is_admin == '1' || (in_array('purchase_sales_report', $access) || in_array('sales_report_list', $access))) { ?>
                                    <li id="purchase_and_sales"><a href="javascript:void(0);"> <i
                                                class="fa-solid fa-handshake nav-icon"></i><b class="nav-title">Purchase And
                                                Sales</b><span class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">

                                            <h4 style="margin-top:0px;">Purchase And Sales</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('purchase_sales_report', $access)) { ?>
                                                <li><a class="purchase_sales_report"
                                                        href="<?= base_url(); ?>purchase_sales_report">Purchase Register List
                                                    </a> </li>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('sales_report_list', $access)) { ?>
                                                <li><a class="sales_report_list"
                                                        href="<?= base_url(); ?>sales_report_list">Sales Report List</a> </li>
                                            <?php } ?>

                                        </ul>
                                    </li>
                                <?php } ?> -->







                                <!-- <li id="sub_master_management"><a href="javascript:void(0);"><i
                                            class="fa-solid fa-database nav-icon"></i><b
                                            class="nav-title">Sub Master Mangement</b><span
                                            class="fa fa-chevron-down down-icon"></span></a> -->

                                <ul class="nav child_menu" style="display: none">
                                    <h4 style="margin-top:0px;">Sub Master Management</h4>
                                    <li><a class="brand_type_list" href="<?= base_url(); ?>brand_type_list">Brand Type
                                        </a> </li>
                                    <li><a class="brand_department_list"
                                            href="<?= base_url(); ?>brand_department_list">Brand Department </a> </li>
                                    <li><a class="party_designation_list"
                                            href="<?= base_url(); ?>party_designation_list">Party Designation </a> </li>
                                    <li><a class="party_nature_list" href="<?= base_url(); ?>party_nature_list">Party
                                            Nature of Business </a> </li>
                                    <li><a class="party_type_businesss_list"
                                            href="<?= base_url(); ?>party_type_businesss_list">Party Type of Business
                                        </a> </li>
                                    <li><a class="group_of_list" href="<?= base_url(); ?>group_of_list">Group Of Article
                                        </a> </li>
                                    <li><a class="type_of_mould_list" href="<?= base_url(); ?>type_of_mould_list">Type
                                            Of Mould </a> </li>
                                    <li><a class="alanky_bolt_list" href="<?= base_url(); ?>alanky_bolt_list">ALANKEY
                                            BOLT </a> </li>
                                    <li><a class="air_pin_list" href="<?= base_url(); ?>air_pin_list">Air Pin </a> </li>
                                    <li><a class="spring_list" href="<?= base_url(); ?>spring_list">Spring </a> </li>
                                    <li><a class="pu_nipple_list" href="<?= base_url(); ?>pu_nipple_list">PU Nipples
                                        </a> </li>
                                    <li><a class="ejector_pin_list" href="<?= base_url(); ?>ejector_pin_list">Ejector
                                            Pin </a> </li>
                                    <li><a class="i_bolt_list" href="<?= base_url(); ?>i_bolt_list">I Bolt </a> </li>
                                    <li><a class="cord_list" href="<?= base_url(); ?>cord_list">Cord </a> </li>
                                    <li><a class="o_ring_list" href="<?= base_url(); ?>o_ring_list">O Ring </a> </li>
                                    <li><a class="insert_slot_list" href="<?= base_url(); ?>insert_slot_list">Inset Slot
                                            Plate </a> </li>
                                    <li><a class="core_cylender_list" href="<?= base_url(); ?>core_cylender_list">Core
                                            Cylender Seal </a> </li>
                                    <li><a class="seal_list" href="<?= base_url(); ?>seal_list">Seal </a> </li>
                                    <li><a class="hope_pipe_list" href="<?= base_url(); ?>hope_pipe_list">Hose Pipe </a>
                                    </li>
                                    <li><a class="rm_make_list" href="<?= base_url(); ?>rm_make_list">RM MAKE </a> </li>
                                    <li><a class="rm_type_list" href="<?= base_url(); ?>rm_type_list">RM Type </a> </li>
                                    <li><a class="mb_make_list" href="<?= base_url(); ?>mb_make_list">MB MAKE </a> </li>
                                    <li><a class="machine_department_list"
                                            href="<?= base_url(); ?>machine_department_list">Machine Department </a>
                                    </li>
                                </ul>
                                <!-- </li>  -->
                                <?php if ($profile->is_admin == '1' || (in_array('add-previleges', $access) || in_array('add-submenu', $access) || in_array('manage-privilege', $access))) { ?>
                                    <li id="privileges_management"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-key nav-icon"></i><b class="nav-title">Privileges
                                                Management</b><span class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">
                                            <h4 style="margin-top:0px;">Privileges Management</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('add-previleges', $access)) { ?>
                                                <li><a class="add-previleges" href="<?= base_url() ?>add-previleges">Manage
                                                        Privilege Head</a></li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('add-submenu', $access)) { ?>
                                                <li><a class="add-submenu" href="<?= base_url() ?>add-submenu">Manage Privilege
                                                        Sub Head</a></li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('manage-privilege', $access)) { ?>
                                                <li><a class="manage-privilege" href="<?= base_url() ?>manage-privilege">Assign
                                                        Privileges</a></li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <!-- Stock Management Module -->
                                <?php if ($profile->is_admin == '1' || (in_array('rm_inward_form', $access) || in_array('rm_inward_list', $access) || in_array('mb_inward_form', $access) || in_array('mb_inward_list', $access) || in_array('material_artical_requistition_from', $access) || in_array('master_batch_request_from', $access) || in_array('article_request_from', $access) || in_array('material_artical_requistition_from_list', $access) || in_array('material_artical_requistition_to_list', $access) || in_array('rm_stock_adjustment', $access) || in_array('mb_stock_adjustment', $access) || in_array('article_stock_adjustment', $access) || in_array('stock_adjustment_list', $access) || in_array('raw_material_reorder_level', $access) || in_array('article_reorder_level', $access) || in_array('return_stock', $access))) { ?>
                                    <li id="stock_management"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-boxes-stacked nav-icon"></i><b class="nav-title">Stock
                                                Management</b><span class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">
                                            
                                            <?php if ($profile->is_admin == '1' || in_array('total_stock_list', $access)) { ?>
                                                <h4 style="margin-top:0px;">Total Stock</h4>

                                                <li><a class="total_stock_list" href="<?= base_url(); ?>total_stock_list">Total Stock List</a> </li>
                                            <?php } ?>
                                            <h4 style="margin-top:0px;">Inward Form</h4>

                                            <li>
                                                <?php if ($profile->is_admin == '1' || in_array('rm_inward_form', $access)) { ?>
                                                    <a class="rm_inward_form" href="<?= base_url(); ?>rm_inward_form">RM Inward Form</a>
                                                <?php } ?>
                                                <?php if ($profile->is_admin == '1' || in_array('rm_inward_list', $access)) { ?>
                                                    <a class="rm_inward_list" href="<?= base_url(); ?>rm_inward_list">RM Inward
                                                        List</a>
                                                <?php } ?>
                                                <?php if ($profile->is_admin == '1' || in_array('mb_inward_form', $access)) { ?>
                                                    <a class="mb_inward_form" href="<?= base_url(); ?>mb_inward_form">MB Inward Form</a>
                                                <?php } ?>
                                            </li>
                                            <?php if ($profile->is_admin == '1' || in_array('mb_inward_list', $access)) { ?>
                                                <li>
                                                    <a class="mb_inward_list" href="<?= base_url(); ?>mb_inward_list">MB Inward
                                                        List</a>
                                                </li>
                                            <?php } ?>


                                            <h4 style="margin-top:0px;">Material Requisition</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('material_artical_requistition_from', $access)) { ?>
                                                <li><a class="material_artical_requistition_from"
                                                        href="<?= base_url(); ?>material_artical_requistition_from">
                                                        Add RM Material Request
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('master_batch_request_from', $access)) { ?>
                                                <li><a class="master_batch_request_from"
                                                        href="<?= base_url(); ?>master_batch_request_from">
                                                        Add MB (Color) Request
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('article_request_from', $access)) { ?>
                                                <li><a class="article_request_from"
                                                        href="<?= base_url(); ?>article_request_from">
                                                        Add Article/Mould Request
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('material_artical_requistition_from_list', $access)) { ?>
                                                <li><a class="material_artical_requistition_from_list"
                                                        href="<?= base_url(); ?>material_artical_requistition_from_list">
                                                        Material Requisition List
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <h4 style="margin-top:0px;">Material Request</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('material_artical_requistition_to_list', $access)) { ?>
                                                <li><a class="material_artical_requistition_to_list"
                                                        href="<?= base_url(); ?>material_artical_requistition_to_list">
                                                        All Material Request List
                                                    </a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('rm_stock_adjustment', $access) || in_array('mb_stock_adjustment', $access) || in_array('article_stock_adjustment', $access) || in_array('stock_adjustment_list', $access)) { ?>
                                                <h4 style="margin-top:0px;">Stock Adjustment</h4>
                                                <?php if ($profile->is_admin == '1' || in_array('rm_stock_adjustment', $access)) { ?>
                                                    <li><a class="rm_stock_adjustment" href="<?= base_url(); ?>rm_stock_adjustment">RM Stock
                                                            Adjustment</a> </li>
                                                <?php } ?>
                                                <?php if ($profile->is_admin == '1' || in_array('mb_stock_adjustment', $access)) { ?>
                                                    <li><a class="mb_stock_adjustment" href="<?= base_url(); ?>mb_stock_adjustment">MB (Color) Stock
                                                            Adjustment</a> </li>
                                                <?php } ?>
                                                <?php if ($profile->is_admin == '1' || in_array('article_stock_adjustment', $access)) { ?>
                                                    <li><a class="article_stock_adjustment" href="<?= base_url(); ?>article_stock_adjustment">Article Stock
                                                            Adjustment</a> </li>
                                                <?php } ?>
                                                <?php if ($profile->is_admin == '1' || in_array('stock_adjustment_list', $access)) { ?>
                                                    <li><a class="stock_adjustment_list"
                                                            href="<?= base_url(); ?>stock_adjustment_list">Stock Adjustment List</a>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if ($profile->is_admin == '1' || in_array('return_stock', $access)) { ?>
                                                <h4 style="margin-top:0px;">Return Stock To Store</h4>

                                                <li><a class="return_stock" href="<?= base_url(); ?>return_stock">Return Stock</a> </li>
                                            <?php } ?>

                                            


                                        </ul>
                                    </li>
                                <?php } ?>
                                <!-- Stock Report Module -->
                                <?php if ($profile->is_admin == '1' || (in_array('stock_report_inward', $access) || in_array('stock_report_raw_material', $access) || in_array('stock_ledger_report', $access) || in_array('article_production_stock_report', $access) || in_array('material_reorder_report', $access) || in_array('required_material_report', $access) || in_array('stock_return_history_report', $access))) { ?>
                                    <li id="stock_report"><a href="javascript:void(0);"><i
                                                class="fa-solid fa-chart-bar nav-icon"></i><b class="nav-title">Stock
                                                Report</b><span class="fa fa-chevron-down down-icon"></span></a>
                                        <ul class="nav child_menu" style="display: none">

                                            <h4 style="margin-top:0px;">Stock Report</h4>
                                            <?php if ($profile->is_admin == '1' || in_array('stock_report_inward', $access)) { ?>
                                                <li><a class="stock_report_inward"
                                                        href="<?= base_url(); ?>stock_report_inward">Inward Stock
                                                        Report</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('stock_ledger_report', $access)) { ?>
                                                <li><a class="stock_ledger_report"
                                                        href="<?= base_url(); ?>stock_ledger_report">Stock Ledger
                                                        Report</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('stock_report_raw_material', $access)) { ?>
                                                <li><a class="stock_report_raw_material"
                                                        href="<?= base_url(); ?>stock_report_raw_material">Stock Transfer
                                                        Report(One Plant To Other Plant)</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('article_production_stock_report', $access)) { ?>
                                                <li><a class="article_production_stock_report"
                                                        href="<?= base_url(); ?>article_production_stock_report">Article Production Stock Report</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('material_reorder_report', $access)) { ?>
                                                <li><a class="material_reorder_report"
                                                        href="<?= base_url(); ?>material_reorder_report">Material Re-Order Report</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('required_material_report', $access)) { ?>

                                                <li><a class="required_material_report"
                                                        href="<?= base_url(); ?>required_material_report">Required Material Stock Report</a> </li>
                                            <?php } ?>
                                            <?php if ($profile->is_admin == '1' || in_array('stock_return_history_report', $access)) { ?>
                                                <li><a class="stock_return_history_report"
                                                        href="<?= base_url(); ?>stock_return_history_report">Stock Return Report</a> </li>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php } ?>

                                <li id="hrms"><a target="_blank" href="https://sohcm.com/smartapp"><i
                                            class="fa-solid fa-users nav-icon"></i><b> HRMS</b></a></li>


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header_sticky" style="height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; background: white; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <!-- Sidebar Toggle Button -->
                    <button id="sidebarToggleBtn" onclick="toggleSidebar()" title="Toggle Sidebar"
                        style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; color: #475569; flex-shrink: 0;"
                        onmouseover="this.style.background='#2563eb'; this.style.color='white'; this.style.borderColor='#2563eb';"
                        onmouseout="this.style.background='#f1f5f9'; this.style.color='#475569'; this.style.borderColor='#e2e8f0';">
                        <i id="sidebarToggleIcon" class="fa fa-bars" style="font-size: 16px;"></i>
                    </button>
                    <div class="company_name" style="font-size: 18px; font-weight: 700; color: #1e293b; letter-spacing: 0.5px;">
                        Krivisha Industries Pvt. Ltd.
                    </div>
                </div>
                <div style="margin: 0 !important; padding: 0 !important; display: flex; align-items: center;">
                    <div class="dropdown" style="margin: 0 !important; padding: 0 !important; display: flex; align-items: center;">
                        <button class="dropdown-toggle" type="button" id="dropdownMenu1"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            style="background: transparent; border: none; outline: none; box-shadow: none; display: flex; align-items: center; gap: 8px; margin: 0 !important; padding: 6px 12px !important; cursor: pointer; border-radius: 30px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='transparent';">
                            <img id="header-profile"
                                src="<?= !empty($profile->emp_photo) ? base_url('assets/images/' . $profile->emp_photo) : base_url('assets/images/background_krivisha.jpg'); ?>"
                                class="img-profile-circle" alt="Profile Image" style="width: 36px; height: 36px; border: 2px solid #cbd5e1; border-radius: 50%; object-fit: cover; margin: 0;">
                            <span class="user-text" style="font-weight: 600; font-size: 15px; color: #334155; text-shadow: none; letter-spacing: 0.3px; font-family: 'Inter', 'Segoe UI', sans-serif; -webkit-text-stroke: 0;"><?= !empty($profile->first_name) ? htmlspecialchars(ucfirst($profile->first_name)) : 'Admin' ?></span>
                            <i class="fa fa-angle-down" style="color: #64748b; font-size: 16px; margin-left: 2px;"></i>
                        </button>

                        <ul class="dropdown-menu top-menu" aria-labelledby="dropdownMenu1" style="border: none; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05); padding: 8px; min-width: 220px; font-family: 'Inter', sans-serif; margin-top: 12px;">
                            <?php if ($profile->is_admin == '1' || in_array('view-profile', $access)) { ?>
                                <li style="margin-bottom: 2px;"><a href="javascript:void(0);" data-toggle="modal"
                                        data-target="#profileModal" style="padding: 10px 15px; color: #475569; font-weight: 500; border-radius: 8px; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#2563eb';" onmouseout="this.style.background='transparent'; this.style.color='#475569';"><i class="fa fa-user" style="font-size: 16px; width: 20px; text-align: center;"></i> Profile</a></li>
                            <?php } ?>

                            <li style="margin-bottom: 2px;"><a href="javascript:void(0);" data-toggle="modal"
                                    data-target="#changePasswordModal" style="padding: 10px 15px; color: #475569; font-weight: 500; border-radius: 8px; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#2563eb';" onmouseout="this.style.background='transparent'; this.style.color='#475569';"><i class="fa fa-lock" style="font-size: 16px; width: 20px; text-align: center;"></i> Change Password</a></li>

                            <li style="margin-bottom: 2px;"><a href="javascript:void(0);" data-toggle="modal"
                                    data-target="#apk_upload_Modal" style="padding: 10px 15px; color: #475569; font-weight: 500; border-radius: 8px; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#2563eb';" onmouseout="this.style.background='transparent'; this.style.color='#475569';"><i class="fa fa-android" style="font-size: 16px; width: 20px; text-align: center;"></i> Latest APK</a></li>

                            <hr style="margin: 8px 0; border-top: 1px solid #e2e8f0;">

                            <li><a href="<?= base_url(); ?>admin_logout"
                                    onclick="return confirm('Are you sure you want to log out?');" style="padding: 10px 15px; color: #ef4444; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 10px; transition: background 0.2s;" onmouseover="this.style.background='#fef2f2';" onmouseout="this.style.background='transparent';"><i class="fa fa-sign-out" style="font-size: 16px; width: 20px; text-align: center;"></i> Log Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
<script>
$(document).ready(function() {
    var $body = $('body');
    var $sidebarMenu = $('#sidebar-menu');

    function minimizeSidebar() {
        $sidebarMenu.find('li.active ul').hide();
        $sidebarMenu.find('li.active').addClass('active-sm').removeClass('active');
        $body.removeClass('nav-md').addClass('nav-sm');
        $('#sidebarToggleIcon').removeClass('fa-chevron-left').addClass('fa-bars');
        localStorage.setItem('sidebarState', 'minimized');
    }

    function maximizeSidebar() {
        $sidebarMenu.find('li.active-sm ul').show();
        $sidebarMenu.find('li.active-sm').addClass('active').removeClass('active-sm');
        $body.removeClass('nav-sm').addClass('nav-md');
        $('#sidebarToggleIcon').removeClass('fa-bars').addClass('fa-chevron-left');
        localStorage.setItem('sidebarState', 'maximized');
    }

    // Restore saved state or default to maximized
    var savedState = localStorage.getItem('sidebarState');
    if (savedState === 'minimized') {
        setTimeout(minimizeSidebar, 100);
    } else {
        setTimeout(maximizeSidebar, 100);
    }

    // Automatically expand the active submenu and highlight the active parent on load
    setTimeout(function() {
        var $activeLink = $('.nav.child_menu a.active_cc');
        if ($activeLink.length) {
            var $parentUl = $activeLink.closest('ul.child_menu');
            var $parentLi = $parentUl.closest('li');
            $parentLi.addClass('active'); // Highlight parent menu
            
            // Expand the submenu inline only if the sidebar is expanded (nav-md)
            if ($('body').hasClass('nav-md')) {
                $parentUl.show();
            }
        }
    }, 150);

    // Global toggle function for the button
    window.toggleSidebar = function() {
        if ($body.hasClass('nav-md')) {
            minimizeSidebar();
        } else {
            maximizeSidebar();
        }
    };

    // Expand sidebar when clicking on a main menu option in minimized mode
    $sidebarMenu.find('.nav.side-menu > li > a').on('click', function(e) {
        if ($body.hasClass('nav-sm')) {
            maximizeSidebar();
        }
    });
});
</script>