<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo csrfToken(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | Edit Property</title>
    <link rel="icon" href="<?php echo asset('assets/images/favicon.ico'); ?>" type="image/x-icon">
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
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 14px;
        }
        
        .breadcrumb li {
            display: inline-block;
        }
        
        .breadcrumb li + li:before {
            content: "/";
            padding: 0 8px;
            color: #666;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .breadcrumb .active {
            color: #666;
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
        
        .padding-0 {
            padding: 0;
        }
        
        .float-md-right {
            float: right;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        select.form-control {
            height: auto;
            padding: 12px 15px;
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
        
        label {
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        
        hr {
            border: 0;
            height: 1px;
            background: #dee2e6;
            margin: 30px 0;
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
                    <a class="navbar-brand" href="<?php echo url('/dashboard'); ?>">
                        <img src="<?php echo asset('common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
                    </a>
                </div>
            </li>
            
            <li class="float-right">
                <a href="javascript:void(0);" class="js-right-sidebar">
                    <i class="zmdi zmdi-settings"></i>
                </a>
                <a href="<?php echo url('/logout'); ?>" class="mega-menu">
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
                        <a href="<?php echo url('/dashboard'); ?>">
                            <i class="zmdi zmdi-home"></i>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/users'); ?>">Users</a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/settings'); ?>">Settings</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="content home">
    <div class="container">
        <div class="block-header">
            <div class="row clearfix mb-3">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Property Setup</h2>
                </div>
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <ul class="breadcrumb float-md-right padding-0">
                        <li><a href="<?php echo url('/dashboard'); ?>"><i class="zmdi zmdi-home"></i></a></li>
                        <li><a href="<?php echo url('/admin/users'); ?>">Users</a></li>
                        <li class="active">Property Setup</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if(flash('error')): ?>
            <div class="alert alert-danger"><?php echo flash('error'); ?></div>
        <?php endif; ?>
        
        <?php if(flash('success')): ?>
            <div class="alert alert-success"><?php echo flash('success'); ?></div>
        <?php endif; ?>

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body">
                        <form method="POST" action="<?php echo url('/admin/hotels/update/' . ($hotel['id'] ?? '')); ?>">
                            <?php echo csrfField(); ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Property Name</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="name"
                                                       placeholder="Example Hotel"
                                                       value="<?php echo htmlspecialchars($hotel['name'] ?? ''); ?>" required/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Website Url</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="web_url"
                                                       placeholder="https://example.com"
                                                       value="<?php echo htmlspecialchars($hotel['web_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Opening Language</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <select class="form-control" name="opening_language">
                                                    <option value="auto" <?php echo ($hotel['opening_language'] ?? '') == 'auto' ? 'selected' : ''; ?>>Auto</option>
                                                    <option value="native" <?php echo ($hotel['opening_language'] ?? '') == 'native' ? 'selected' : ''; ?>>Native</option>
                                                    <option value="english" <?php echo ($hotel['opening_language'] ?? '') == 'english' ? 'selected' : ''; ?>>English</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h2 class="card-inside-title">Default IBE (Internet Booking Engine)</h2>
                                    <div class="form-group">
                                        <input type="radio" id="sabeeapp" value="sabeeapp" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'sabeeapp' ? 'checked' : ''; ?>>
                                        <label for="sabeeapp">SabeeApp</label>
                                        <input type="radio" id="reseliva" value="reseliva" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'reseliva' ? 'checked' : ''; ?>>
                                        <label for="reseliva">Reseliva</label>
                                        <input type="radio" id="hotelrunner" value="hotelrunner" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'hotelrunner' ? 'checked' : ''; ?>>
                                        <label for="hotelrunner">HotelRunner</label>
                                    </div>
                                </div>
                            </div>

                            <!-- SabeeApp Section -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">SabeeApp Hotel ID</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="sabee_hotel_id"
                                                       placeholder="Hotel ID"
                                                       value="<?php echo htmlspecialchars($hotel['sabee_hotel_id'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">SabeeApp URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="sabee_is_active" value="1" <?php echo ($hotel['sabee_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="sabee_url"
                                                       placeholder="https://ibe.sabeeapp.com/properties/Example-Hotel-booking/?p=bSpf44a337ea1a30a74"
                                                       value="<?php echo htmlspecialchars($hotel['sabee_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reseliva Section -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Reseliva Hotel ID</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="checkbox" name="reseliva_is_active" value="1" <?php echo ($hotel['reseliva_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="reseliva_hotel_id"
                                                       placeholder="7813" 
                                                       value="<?php echo htmlspecialchars($hotel['reseliva_hotel_id'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">HotelRunner URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="checkbox" name="is_hotelrunner_active" value="1" <?php echo ($hotel['is_hotelrunner_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </div>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="hotelrunner_url"
                                                       placeholder="https://hotel.hotelrunner.com" 
                                                       value="<?php echo htmlspecialchars($hotel['hotelrunner_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Booking Platforms -->
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Booking.com URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="booking_is_active" value="1" <?php echo ($hotel['booking_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="booking_url"
                                                       placeholder="https://www.booking.com/hotel/tr/example.tr.html"
                                                       value="<?php echo htmlspecialchars($hotel['booking_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Hotels.com URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="hotels_is_active" value="1" <?php echo ($hotel['hotels_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="hotels_url"
                                                       placeholder="https://hotels.com/ho211277"
                                                       value="<?php echo htmlspecialchars($hotel['hotels_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">TatilSepeti.com URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="tatilsepeti_is_active" value="1" <?php echo ($hotel['tatilsepeti_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="tatilsepeti_url"
                                                       placeholder="https://www.tatilsepeti.com/example-hotel-526274"
                                                       value="<?php echo htmlspecialchars($hotel['tatilsepeti_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Odamax URL</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="odamax_is_active" value="1" <?php echo ($hotel['odamax_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="url" class="form-control" name="odamax_url"
                                                       placeholder="https://www.odamax.com/tr/hotel/example-hotel-287883"
                                                       value="<?php echo htmlspecialchars($hotel['odamax_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">OtelZ Tesis ID</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="otelz_is_active" value="1" <?php echo ($hotel['otelz_is_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="otelz_url"
                                                       placeholder="4532"
                                                       value="<?php echo htmlspecialchars($hotel['otelz_url'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">ETSTur Hotel ID</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <label class="form-group">
                                                <input type="checkbox" name="is_etstur_active" value="1" <?php echo ($hotel['is_etstur_active'] ?? false) ? 'checked' : ''; ?>>
                                                Active
                                            </label>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="etstur_hotel_id"
                                                       placeholder="KZSAPT"
                                                       value="<?php echo htmlspecialchars($hotel['etstur_hotel_id'] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary pull-right">
                                        <i class="zmdi zmdi-save"></i> Save Property
                                    </button>
                                    <a href="<?php echo url('/admin/users'); ?>" class="btn btn-secondary mr-2">
                                        <i class="zmdi zmdi-arrow-left"></i> Back to Users
                                    </a>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
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
</script>

</body>
</html>
