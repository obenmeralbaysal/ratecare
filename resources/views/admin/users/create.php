<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrfToken() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | Create User</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
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
        
        .navbar {
            background: #2c2c2c;
            border: none;
            padding: 0;
            min-height: 60px;
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
        
        .navbar .nav li a {
            color: #fff;
            text-decoration: none;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        
        .navbar .nav li a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .menu-container {
            background: #333;
            border-bottom: 1px solid #444;
        }
        
        .h-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        
        .h-menu li a {
            color: #fff;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s ease;
        }
        
        .h-menu li:hover > a,
        .h-menu li.active > a {
            background: #007bff;
        }
        
        .content {
            padding: 30px 0;
            min-height: calc(100vh - 120px);
        }
        
        .block-header {
            margin-bottom: 30px;
        }
        
        .block-header h2 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card .body {
            padding: 30px;
        }
        
        .card-inside-title {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        
        .card-inside-title:first-child {
            margin-top: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px 15px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            outline: none;
        }
        
        .form-control-file {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            width: 100%;
        }
        
        .btn {
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 12px 30px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        
        .pull-right {
            float: right;
        }
        
        .reseller-logo {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        input[type="radio"] {
            margin-right: 8px;
            margin-left: 15px;
        }
        
        input[type="radio"]:first-child {
            margin-left: 0;
        }
        
        input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
    </style>
</head>
<body class="theme-black">

<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="h-bars">☰</a>
                    <a class="navbar-brand" href="{{ url('/dashboard') }}">
                        <img src="{{ asset('common/img/rate-care-logo.fw.png') }}" alt="RateCare">
                    </a>
                </div>
            </li>
            
            <li class="float-right">
                <a href="javascript:void(0);" class="js-right-sidebar">
                    <i class="zmdi zmdi-settings"></i>
                </a>
                <a href="{{ url('/logout') }}" class="mega-menu">
                    <i class="zmdi zmdi-power"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="menu-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="h-menu">
                    <li>
                        <a href="{{ url('/dashboard') }}">
                            <i class="zmdi zmdi-home"></i>
                        </a>
                    </li>
                    <li class="active">
                        <a href="{{ url('/admin/users') }}">Users</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/hotels') }}">Hotels</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/widgets') }}">Widgets</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/settings') }}">Settings</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="content home">
    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Create User</h2>
                </div>
            </div>
        </div>

        {!! flash('error') ? '<div class="alert alert-danger">' . flash('error') . '</div>' : '' !!}
        {!! flash('success') ? '<div class="alert alert-success">' . flash('success') . '</div>' : '' !!}

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body">
                        <form method="POST" action="{{ url('/admin/users/create') }}" enctype="multipart/form-data">
                            {!! csrfField() !!}
                            
                            <h2 class="card-inside-title">Name Surname</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="namesurname" placeholder="John Doe"
                                               value="{{ old('namesurname') }}" required/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">E-Mail</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="email" class="form-control" name="email" data-lpignore="true"
                                               placeholder="johndoe@example.com" value="{{ old('email') }}" required/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Password</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="password" class="form-control" name="password" required/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Password (Confirm)</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="password" class="form-control" name="password_confirmation" required/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">User Type</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="radio" name="userType" value="0" checked> Standard
                                        <input type="radio" name="userType" value="1"> Admin
                                        <input type="radio" name="userType" value="2" id="check_reseller"> Reseller
                                    </div>
                                </div>
                            </div>

                            <div class="reseller-logo" style="display: none;">
                                <h2 class="card-inside-title">Reseller Logo</h2>
                                <div class="row clearfix">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input type="file" class="form-control-file" name="resellerLogo" aria-describedby="fileHelp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Rate Comparison Tool</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="checkbox" name="rateComparison" value="1"> Active
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary pull-right">Save</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Mobile menu toggle
    $('.h-bars').click(function() {
        $('.h-menu').slideToggle();
    });
    
    // Reseller logo toggle
    $(document).ready(function() {
        $('input:radio[name="userType"]').click(function() {
            if ($('#check_reseller').is(':checked'))
                $('.reseller-logo').show();
            else
                $('.reseller-logo').hide();
        });
    });
</script>

</body>
</html>
