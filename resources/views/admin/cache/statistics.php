<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrfToken() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Cache Statistics | RateCare</title>
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
        }
        
        .h-bars {
            color: #333;
            font-size: 18px;
            padding: 15px;
            cursor: pointer;
        }
        
        .menu-container {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
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
            color: #333;
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
            background: #fff;
            border: 1px solid #dee2e6;
            min-width: 200px;
            list-style: none;
            margin: 0;
            padding: 0;
            display: none;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .h-menu li:hover .sub-menu {
            display: block;
        }
        
        .h-menu li .sub-menu li a {
            padding: 10px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        
        .h-menu li .sub-menu li a:hover {
            background: #007bff;
            color: #fff;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .content {
            padding: 0 0 30px 0;
            min-height: calc(100vh - 180px);
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }
        
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; }
        .badge-danger { background: #dc3545; }
        
        .table-responsive {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
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

<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="h-bars">☰</a>
                    <a class="navbar-brand" href="<?php echo url('/dashboard'); ?>">
                        <img src="<?php echo url('/assets/common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
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
                            <i class="zmdi zmdi-home"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">Users</a>
                        <ul class="sub-menu">
                            <li><a href="<?php echo url('/admin/users'); ?>">All Users</a></li>
                            <li><a href="<?php echo url('/admin/users/create'); ?>">New User</a></li>
                            <li><a href="<?php echo url('/admin/users/invite'); ?>">Invite User</a></li>
                        </ul>
                    </li>
                    <li class="active">
                        <a href="javascript:void(0)">Cache</a>
                        <ul class="sub-menu">
                            <li class="active"><a href="<?php echo url('/admin/cache/statistics'); ?>">Statistics</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/settings'); ?>">Settings</a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/logs'); ?>">Logs</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="zmdi zmdi-chart"></i> Cache Statistics & Analytics</h1>
        <p class="mb-0">Real-time cache performance metrics and platform analytics</p>
    </div>
</div>

<section class="content home">
<div class="container">
    
    <!-- Overview Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="zmdi zmdi-flash zmdi-hc-3x" style="color: #667eea;"></i>
                <div class="stat-number" id="overallHitRate">--%</div>
                <div class="stat-label">Overall Hit Rate</div>
                <small class="text-muted">Last 7 days</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="zmdi zmdi-check-circle zmdi-hc-3x" style="color: #28a745;"></i>
                <div class="stat-number" id="totalRequests">--</div>
                <div class="stat-label">Total Requests</div>
                <small class="text-muted">Last 7 days</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="zmdi zmdi-time zmdi-hc-3x" style="color: #ffc107;"></i>
                <div class="stat-number" id="avgResponseTime">--ms</div>
                <div class="stat-label">Avg Response Time</div>
                <small class="text-muted">Cache hits only</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <i class="zmdi zmdi-storage zmdi-hc-3x" style="color: #17a2b8;"></i>
                <div class="stat-number" id="cacheEntries">--</div>
                <div class="stat-label">Active Cache Entries</div>
                <small class="text-muted">Current</small>
            </div>
        </div>
    </div>
    
    <!-- Cache Hit Breakdown Chart -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5><i class="zmdi zmdi-chart-donut"></i> Cache Hit Type Distribution</h5>
                <div class="chart-container">
                    <canvas id="hitTypeChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5><i class="zmdi zmdi-info"></i> Cache Hit Types</h5>
                <div class="mt-4">
                    <div class="mb-3">
                        <span class="badge badge-success">Full Hit</span>
                        <p class="small mb-0">All platforms served from cache</p>
                        <strong id="fullHitCount">0</strong> requests
                    </div>
                    <div class="mb-3">
                        <span class="badge badge-warning">Partial Hit</span>
                        <p class="small mb-0">Some platforms refreshed</p>
                        <strong id="partialHitCount">0</strong> requests
                    </div>
                    <div class="mb-3">
                        <span class="badge badge-danger">Miss</span>
                        <p class="small mb-0">No cache, all platforms requested</p>
                        <strong id="missCount">0</strong> requests
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Daily Trend Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="stat-card">
                <h5><i class="zmdi zmdi-trending-up"></i> Cache Performance Trend (Last 7 Days)</h5>
                <div class="chart-container" style="height: 250px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Channel Usage -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5><i class="zmdi zmdi-star"></i> Top Channels by Usage</h5>
                <div class="chart-container">
                    <canvas id="channelChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h5><i class="zmdi zmdi-format-list-bulleted"></i> Channel Statistics</h5>
                <div class="table-responsive">
                    <table class="table table-sm" id="channelTable">
                        <thead>
                            <tr>
                                <th>Channel</th>
                                <th>Requests</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-5 mb-5"></div>
</div>
</section>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

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

let hitTypeChart, trendChart, channelChart;

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '<?php echo url("/api/v1/cache/statistics"); ?>',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                updateOverviewStats(response.data.overview);
                updateHitTypeChart(response.data.hit_breakdown);
                updateTrendChart(response.data.trend);
                updateChannelChart(response.data.channels);
            }
        },
        error: function() {
            console.error('Failed to load statistics');
        }
    });
}

// Update overview stats
function updateOverviewStats(data) {
    $('#overallHitRate').text(data.hit_rate + '%');
    $('#totalRequests').text(data.total_requests);
    $('#avgResponseTime').text(data.avg_response_time + 'ms');
    $('#cacheEntries').text(data.cache_entries);
    
    $('#fullHitCount').text(data.full_hits);
    $('#partialHitCount').text(data.partial_hits);
    $('#missCount').text(data.misses);
}

// Hit Type Pie Chart
function updateHitTypeChart(data) {
    const ctx = document.getElementById('hitTypeChart').getContext('2d');
    
    if (hitTypeChart) {
        hitTypeChart.destroy();
    }
    
    hitTypeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Full Hit', 'Partial Hit', 'Miss'],
            datasets: [{
                data: [data.full_hits, data.partial_hits, data.misses],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Trend Line Chart
function updateTrendChart(data) {
    const ctx = document.getElementById('trendChart').getContext('2d');
    
    if (trendChart) {
        trendChart.destroy();
    }
    
    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Full Hits',
                    data: data.full_hits,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Partial Hits',
                    data: data.partial_hits,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Misses',
                    data: data.misses,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Channel Bar Chart
function updateChannelChart(data) {
    const ctx = document.getElementById('channelChart').getContext('2d');
    
    if (channelChart) {
        channelChart.destroy();
    }
    
    channelChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Requests',
                data: data.values,
                backgroundColor: '#667eea'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Update table
    let tableHtml = '';
    const total = data.values.reduce((a, b) => a + b, 0);
    data.labels.forEach((label, index) => {
        const count = data.values[index];
        const percentage = ((count / total) * 100).toFixed(1);
        tableHtml += `
            <tr>
                <td><strong>${label}</strong></td>
                <td>${count}</td>
                <td>${percentage}%</td>
            </tr>
        `;
    });
    $('#channelTable tbody').html(tableHtml);
}

// Load on page load
$(document).ready(function() {
    loadStatistics();
    
    // Auto-refresh every 30 seconds
    setInterval(loadStatistics, 30000);
});
</script>

</body>
</html>
