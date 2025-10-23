<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrfToken() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>@yield('title', 'Admin Panel') | RateCare</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Muli', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .page-loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #2c2c2c;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .loader {
            text-align: center;
            color: #fff;
        }
        
        .navbar {
            background: #f8f9fa;
            border: none;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
            min-height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .navbar .nav {
            display: flex;
            align-items: center;
            height: 100%;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .navbar .nav li {
            display: flex;
            align-items: center;
        }
        
        .navbar .nav li a {
            color: #333;
            text-decoration: none;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        
        .navbar .nav li a:hover {
            background: rgba(0,0,0,0.05);
        }
        
        .navbar .nav li a i {
            font-size: 18px;
            margin-right: 5px;
        }
        
        .navbar .nav .float-right {
            margin-left: auto;
        }
        
        .menu-container {
            background: #6c757d;
            border-bottom: 1px solid #5a6268;
        }
        
        .h-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        
        .h-menu li {
            position: relative;
        }
        
        .h-menu li a {
            color: #ffffff;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s ease;
        }
        
        .h-menu li:hover > a,
        .h-menu li.active > a {
            background: #007bff;
            color: #fff;
        }
        
        .h-menu li .sub-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #5a6268;
            border: 1px solid #495057;
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .h-menu li:hover .sub-menu {
            display: block;
        }
        
        .h-menu li .sub-menu li a {
            padding: 10px 20px;
            border-bottom: 1px solid #495057;
            color: #ffffff;
        }
        
        .h-menu li .sub-menu li a:hover {
            background: #007bff;
            color: #fff;
        }
        
        .content {
            padding: 30px 0;
            min-height: calc(100vh - 180px);
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .m-t-20 {
            margin-top: 20px;
        }
        
        .m-t-30 {
            margin-top: 30px;
        }
        
        .float-right {
            float: right;
        }
        
        @media (max-width: 768px) {
            .h-menu {
                flex-direction: column;
                display: none;
            }
            
            .h-menu.show {
                display: flex;
            }
            
            .h-menu li .sub-menu {
                position: static;
                display: block;
                background: #f0f0f0;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body class="theme-black">

<!-- Page Loader -->
<div class="page-loader-wrapper" id="pageLoader">
    <div class="loader">
        <div class="m-t-30">
            <img src="<?php echo url('/assets/common/img/rate-care-logo.fw.png'); ?>" width="48" alt="RateCare">
        </div>
        <p>Please wait...</p>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a class="navbar-brand" href="<?php echo url('/dashboard'); ?>">
                        <img src="<?php echo url('/assets/common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
                    </a>
                </div>
            </li>
            
            <li class="float-right">
                <a href="<?php echo url('/logout'); ?>" class="mega-menu">
                    <i class="zmdi zmdi-power"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Menu -->
<div class="menu-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="h-menu">
                    <li class="@yield('menu-dashboard')">
                        <a href="<?php echo url('/dashboard'); ?>">
                            <i class="zmdi zmdi-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="@yield('menu-users')">
                        <a href="javascript:void(0)">Users</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo url('/admin/users'); ?>">All Users</a></li>
                            <li><a href="<?php echo url('/admin/users/create'); ?>">New User</a></li>
                            <li><a href="<?php echo url('/admin/users/invite'); ?>">Invite User</a></li>
                        </ul>
                    </li>
                    <li class="@yield('menu-cache')">
                        <a href="javascript:void(0)">Cache</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo url('/admin/cache/statistics'); ?>">Statistics</a></li>
                        </ul>
                    </li>
                    <li class="@yield('menu-settings')">
                        <a href="<?php echo url('/admin/settings'); ?>">Settings</a>
                    </li>
                    <li class="@yield('menu-logs')">
                        <a href="<?php echo url('/admin/logs'); ?>">Logs</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<section class="content home">
    <div class="container">
        @yield('content')
    </div>
</section>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Hide page loader
$(document).ready(function() {
    setTimeout(function() {
        $('#pageLoader').fadeOut();
    }, 1000);
});

// CSRF token setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Mobile responsive: Show menu on mobile
$(window).resize(function() {
    if ($(window).width() > 768) {
        $('.h-menu').removeClass('show');
    }
});
</script>

@yield('scripts')

</body>
</html>
