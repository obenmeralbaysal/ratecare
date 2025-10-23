@extends('layouts.admin-new')

@section('title', 'Cache Statistics')

@section('menu-cache', 'active')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css">
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 0;
        margin: -30px 0 30px 0;
        margin-left: -15px;
        margin-right: -15px;
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
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="zmdi zmdi-chart"></i> Cache Statistics & Analytics</h1>
        <p class="mb-0">Real-time cache performance metrics and platform analytics</p>
    </div>
</div>

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

<!-- Channel Error Rates -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="stat-card">
            <h5><i class="zmdi zmdi-alert-circle"></i> Channel Error Rates & Reliability</h5>
            <div class="table-responsive">
                <table class="table table-sm" id="channelErrorTable">
                    <thead>
                        <tr>
                            <th>Channel</th>
                            <th>Total Requests</th>
                            <th>Errors</th>
                            <th>Error Rate</th>
                            <th>Success Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
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
                updateChannelErrorTable(response.data.channel_errors);
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

// Update Channel Error Table
function updateChannelErrorTable(data) {
    if (!data || data.length === 0) {
        $('#channelErrorTable tbody').html('<tr><td colspan="6" class="text-center text-muted">No error data available</td></tr>');
        return;
    }
    
    let tableHtml = '';
    data.forEach(function(channel) {
        const statusBadge = getStatusBadge(channel.status, channel.error_rate);
        tableHtml += `
            <tr>
                <td><strong>${channel.channel}</strong></td>
                <td>${channel.total_requests.toLocaleString()}</td>
                <td><span class="badge badge-danger">${channel.error_requests}</span></td>
                <td><strong>${channel.error_rate}%</strong></td>
                <td><span class="badge badge-success">${channel.success_rate}%</span></td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });
    $('#channelErrorTable tbody').html(tableHtml);
}

function getStatusBadge(status, errorRate) {
    if (status === 'critical') {
        return `<span class="badge badge-danger"><i class="zmdi zmdi-alert-circle"></i> Critical (${errorRate}%)</span>`;
    } else if (status === 'warning') {
        return `<span class="badge badge-warning"><i class="zmdi zmdi-alert-triangle"></i> Warning (${errorRate}%)</span>`;
    } else {
        return `<span class="badge badge-success"><i class="zmdi zmdi-check"></i> Healthy (${errorRate}%)</span>`;
    }
}

// Load on page load
$(document).ready(function() {
    loadStatistics();
    
    // Auto-refresh every 30 seconds
    setInterval(loadStatistics, 30000);
});
</script>
@endsection
