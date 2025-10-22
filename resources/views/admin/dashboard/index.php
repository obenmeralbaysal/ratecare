<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrfToken() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | The Ultimate Dashboard for Hoteliers</title>
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
            background: #ffffff;
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
        
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        /* Logout button styles */
        .navbar-nav {
            display: flex;
            align-items: center;
        }
        
        .logout-btn {
            color: #333;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #dc3545;
            color: #fff;
            text-decoration: none;
        }
        
        .logout-btn i {
            font-size: 16px;
        }
        
        .ml-1 {
            margin-left: 0.25rem;
        }
        
        .navbar .nav li a i {
            font-size: 18px;
        }
        
        .h-bars {
            color: #fff;
            font-size: 18px;
            padding: 15px;
            cursor: pointer;
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
        
        .h-menu li {
            position: relative;
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
        
        .h-menu li .sub-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: #444;
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
            z-index: 1000;
        }
        
        .h-menu li:hover .sub-menu {
            display: block;
        }
        
        .h-menu li .sub-menu li a {
            padding: 10px 20px;
            border-bottom: 1px solid #555;
        }
        
        .h-menu li .sub-menu li a:hover {
            background: #007bff;
        }
        
        .content {
            padding: 30px 0;
            min-height: calc(100vh - 120px);
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .tasks_report {
            background: #fff;
            transition: all 0.3s ease;
        }
        
        .tasks_report:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .tasks_report a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .tasks_report .body {
            padding: 40px 20px;
            text-align: center;
        }
        
        .tasks_report .body i {
            color: #007bff;
            margin-bottom: 20px;
        }
        
        .tasks_report .body h6 {
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        
        /* Cache Stats Cards */
        .cache-stats-card {
            cursor: pointer;
        }
        
        .cache-stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .cache-stats-card .body h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .cache-stats-card .body h6 {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 10px 0;
        }
        
        .m-t-20 {
            margin-top: 20px;
        }
        
        .float-right {
            float: right;
        }
        
        @media (max-width: 768px) {
            .h-menu {
                flex-direction: column;
            }
            
            .h-menu li .sub-menu {
                position: static;
                display: block;
                background: #444;
            }
        }
    </style>
</head>
<body class="theme-black">

<!-- Page Loader -->
<div class="page-loader-wrapper" id="pageLoader">
    <div class="loader">
        <div class="m-t-30">
            <img src="{{ asset('common/img/rate-care-logo.fw.png') }}" width="48" alt="RateCare">
        </div>
        <p>Please wait...</p>
    </div>
</div>

<nav class="navbar">
    <div class="container">
        <!-- Logo - Sol taraf -->
        <a class="navbar-brand" href="{{ url('/dashboard') }}">
            <img src="{{ asset('common/img/rate-care-logo.fw.png') }}" alt="RateCare">
        </a>
        
        <!-- Çıkış butonu - Sağ taraf -->
        <div class="navbar-nav">
            <a href="{{ url('/logout') }}" class="logout-btn">
                <i class="zmdi zmdi-power"></i>
                <span class="ml-1">Logout</span>
            </a>
        </div>
    </div>
</nav>

<div class="menu-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="h-menu">
                    <li class="open active">
                        <a href="{{ url('/dashboard') }}">
                            <i class="zmdi zmdi-home"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/users') }}">Users</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/logs') }}">Logs</a>
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
        <div class="row clearfix">
              <!-- Cache Statistics Cards -->
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report cache-stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                    <a href="<?php echo url('/admin/cache/statistics'); ?>" style="color: inherit; text-decoration: none;">
                        <div class="body">
                            <i class="zmdi zmdi-hc-4x zmdi-flash"></i>
                            <h3 class="m-t-10 mb-0" id="cacheHitRate">--%</h3>
                            <h6 class="m-t-10">CACHE HIT RATE</h6>
                            <small style="opacity: 0.9;">Last 24 hours • Click for details</small>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report cache-stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff;">
                    <div class="body">
                        <i class="zmdi zmdi-hc-4x zmdi-chart"></i>
                        <h3 class="m-t-10 mb-0" id="totalRequests">--</h3>
                        <h6 class="m-t-10">TOTAL REQUESTS</h6>
                        <small style="opacity: 0.9;">
                            <span id="fullHits">0</span> full, 
                            <span id="partialHits">0</span> partial, 
                            <span id="misses">0</span> miss
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report cache-stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">
                    <div class="body">
                        <i class="zmdi zmdi-hc-4x zmdi-star"></i>
                        <h3 class="m-t-10 mb-0" id="topChannel">--</h3>
                        <h6 class="m-t-10">TOP CHANNEL</h6>
                        <small style="opacity: 0.9;">Most requested platform</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url('/admin/users') }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-folder-person"></i>
                            <h6 class="m-t-20">ALL USERS</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url('/admin/users/create') }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-plus"></i>
                            <h6 class="m-t-20">NEW USER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url('/admin/users/invite') }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-email"></i>
                            <h6 class="m-t-20">INVITE USER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url('/admin/logs') }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-file-text"></i>
                            <h6 class="m-t-20">LOG VIEWER</h6>
                        </div>
                    </a>
                </div>
            </div>
            
          
            
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url('/admin/settings') }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-settings"></i>
                            <h6 class="m-t-20">SETTINGS</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

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
    
    // Mobile menu toggle
    $('.h-bars').click(function() {
        $('.h-menu').slideToggle();
    });
    
    // Load cache statistics
    function loadCacheStats() {
        $.ajax({
            url: '<?php echo url("/api/v1/cache/summary"); ?>',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    
                    // Update cache hit rate
                    $('#cacheHitRate').text(data.cache_hit_rate + '%');
                    
                    // Update total requests
                    $('#totalRequests').text(data.total_requests);
                    $('#fullHits').text(data.full_hits);
                    $('#partialHits').text(data.partial_hits);
                    $('#misses').text(data.misses);
                    
                    // Update top channel
                    $('#topChannel').text(data.top_channel);
                    
                    // Add animation
                    $('.cache-stats-card').addClass('animated fadeIn');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load cache stats:', error);
                $('#cacheHitRate').text('N/A');
                $('#totalRequests').text('N/A');
                $('#topChannel').text('N/A');
            }
        });
    }
    
    // Load stats on page load
    $(document).ready(function() {
        loadCacheStats();
        
        // Auto-refresh every 30 seconds
        setInterval(loadCacheStats, 30000);
    });
</script>

</body>
</html>
