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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
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
        
        /* Topbar (Logo + Logout) */
        .topbar {
            background: #f8f9fa;
            border: none;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
            min-height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .topbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        
        .topbar-brand img {
            height: 40px;
        }
        
        .topbar .nav {
            display: flex;
            align-items: center;
            height: 100%;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .topbar .nav li {
            display: flex;
            align-items: center;
        }
        
        .topbar .nav li a {
            color: #333;
            text-decoration: none;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        
        .topbar .nav li a:hover {
            background: rgba(0,0,0,0.05);
        }
        
        .topbar .nav li a i {
            font-size: 18px;
            margin-right: 5px;
        }
        
        .topbar .nav .float-right {
            margin-left: auto;
        }
        
        /* Navbar (Main Menu) */
        .navbar {
            background: #6c757d;
            border-bottom: 1px solid #5a6268;
        }
        
        .navbar-toggler {
            display: none;
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 18px;
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
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .navbar-toggler {
                display: block;
            }
            
            .h-menu {
                flex-direction: column;
                display: none;
                width: 100%;
            }
            
            .h-menu.show {
                display: flex;
            }
            
            .h-menu li {
                width: 100%;
            }
            
            .h-menu li a {
                width: 100%;
                border-bottom: 1px solid #5a6268;
            }
            
            .h-menu li .sub-menu {
                position: static;
                display: none;
                background: #5a6268;
                box-shadow: none;
            }
            
            .h-menu li:hover .sub-menu {
                display: none;
            }
            
            .h-menu li.open .sub-menu {
                display: block;
            }
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

<!-- Topbar (Logo + Logout) -->
<div class="topbar">
    <div class="container">
        <a class="topbar-brand" href="<?php echo url('/dashboard'); ?>">
            <img src="<?php echo url('/assets/common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
        </a>
        
        <ul class="nav float-right">
            <li>
                <a href="<?php echo url('/logout'); ?>">
                    <i class="zmdi zmdi-power"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Navbar (Main Menu) -->
<nav class="navbar">
    <div class="container">
        <button class="navbar-toggler" onclick="toggleMobileMenu()">
            <i class="zmdi zmdi-menu"></i>
        </button>
        
        <ul class="h-menu" id="mainMenu">
            <li class="@yield('menu-dashboard')">
                <a href="<?php echo url('/dashboard'); ?>">
                    <i class="zmdi zmdi-home"></i> Dashboard
                </a>
            </li>
            <li class="@yield('menu-users')">
                <a href="javascript:void(0)" onclick="toggleSubMenu(this)">
                    Users <i class="zmdi zmdi-chevron-down"></i>
                </a>
                <ul class="sub-menu">
                    <li><a href="<?php echo url('/admin/users'); ?>">All Users</a></li>
                    <li><a href="<?php echo url('/admin/users/create'); ?>">New User</a></li>
                    <li><a href="<?php echo url('/admin/users/invite'); ?>">Invite User</a></li>
                </ul>
            </li>
            <li class="@yield('menu-cache')">
                <a href="javascript:void(0)" onclick="toggleSubMenu(this)">
                    Cache <i class="zmdi zmdi-chevron-down"></i>
                </a>
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
</nav>

<!-- Page Content -->
<section class="content home">
    <div class="container">
        @yield('content')
    </div>
</section>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

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

// Mobile menu toggle
function toggleMobileMenu() {
    $('#mainMenu').toggleClass('show');
}

// Mobile submenu toggle
function toggleSubMenu(element) {
    if ($(window).width() <= 768) {
        $(element).parent().toggleClass('open');
        return false;
    }
}

// Close mobile menu on resize
$(window).resize(function() {
    if ($(window).width() > 768) {
        $('#mainMenu').removeClass('show');
        $('.h-menu li').removeClass('open');
    }
});

// Global AJAX form handler
function submitFormAjax(form, successCallback) {
    const formData = new FormData(form);
    const url = $(form).attr('action');
    const method = $(form).attr('method') || 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Operation completed successfully!');
                if (successCallback) successCallback(response);
            } else {
                toastr.error(response.message || 'Operation failed!');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                toastr.error(response.message);
            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });
}
</script>

@yield('scripts')

</body>
</html>
