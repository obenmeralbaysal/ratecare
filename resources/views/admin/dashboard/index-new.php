@extends('layouts.admin-new')

@section('title', 'Dashboard')

@section('menu-dashboard', 'active')

@section('styles')
<style>
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
</style>
@endsection

@section('content')
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
                <h6 class="m-t-10">MOST STABLE CHANNEL</h6>
                <small style="opacity: 0.9;">Least errors & most reliable</small>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 text-center">
        <div class="card tasks_report">
            <a href="<?php echo url('/admin/users'); ?>">
                <div class="body">
                    <i class="zmdi zmdi-hc-5x zmdi-folder-person"></i>
                    <h6 class="m-t-20">ALL USERS</h6>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 text-center">
        <div class="card tasks_report">
            <a href="<?php echo url('/admin/users/create'); ?>">
                <div class="body">
                    <i class="zmdi zmdi-hc-5x zmdi-plus"></i>
                    <h6 class="m-t-20">NEW USER</h6>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 text-center">
        <div class="card tasks_report">
            <a href="<?php echo url('/admin/users/invite'); ?>">
                <div class="body">
                    <i class="zmdi zmdi-hc-5x zmdi-email"></i>
                    <h6 class="m-t-20">INVITE USER</h6>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 text-center">
        <div class="card tasks_report">
            <a href="<?php echo url('/admin/logs'); ?>">
                <div class="body">
                    <i class="zmdi zmdi-hc-5x zmdi-file-text"></i>
                    <h6 class="m-t-20">LOGS</h6>
                </div>
            </a>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-12 text-center">
        <div class="card tasks_report">
            <a href="<?php echo url('/admin/settings'); ?>">
                <div class="body">
                    <i class="zmdi zmdi-hc-5x zmdi-settings"></i>
                    <h6 class="m-t-20">SETTINGS</h6>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Load cache statistics
function loadCacheStats() {
    $.ajax({
        url: '<?php echo url("/api/v1/cache/summary"); ?>',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                $('#cacheHitRate').text(response.data.cache_hit_rate + '%');
                $('#totalRequests').text(response.data.total_requests);
                $('#fullHits').text(response.data.full_hits);
                $('#partialHits').text(response.data.partial_hits);
                $('#misses').text(response.data.misses);
                $('#topChannel').text(response.data.top_channel);
            }
        }
    });
}

$(document).ready(function() {
    loadCacheStats();
    setInterval(loadCacheStats, 30000);
});
</script>
@endsection
