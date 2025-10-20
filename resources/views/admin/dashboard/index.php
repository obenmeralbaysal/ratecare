@extends('layouts.admin')

@section('content')
<!-- Overview Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $overview_stats['total_users'] }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ url('/admin/users') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $overview_stats['total_hotels'] }}</h3>
                <p>Total Hotels</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ url('/admin/hotels') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $overview_stats['total_widgets'] }}</h3>
                <p>Total Widgets</p>
            </div>
            <div class="icon">
                <i class="fas fa-puzzle-piece"></i>
            </div>
            <a href="{{ url('/admin/widgets') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($overview_stats['total_views']) }}</h3>
                <p>Total Views</p>
            </div>
            <div class="icon">
                <i class="fas fa-eye"></i>
            </div>
            <a href="{{ url('/admin/statistics') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Growth</h3>
                <div class="card-tools">
                    <select id="periodSelect" class="form-select form-select-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <canvas id="growthChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="userChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- System Health and Top Hotels -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Health</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="health-indicator {{ $system_health['overall_status'] }}">
                                <i class="fas fa-{{ $system_health['overall_status'] === 'healthy' ? 'check-circle' : ($system_health['overall_status'] === 'warning' ? 'exclamation-triangle' : 'times-circle') }} fa-3x"></i>
                            </div>
                            <h5 class="mt-2">{{ ucfirst($system_health['overall_status']) }}</h5>
                        </div>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li>
                                <i class="fas fa-database me-2 text-{{ $system_health['database']['status'] === 'healthy' ? 'success' : 'danger' }}"></i>
                                Database: {{ ucfirst($system_health['database']['status']) }}
                            </li>
                            <li>
                                <i class="fas fa-server me-2 text-{{ $system_health['application']['status'] === 'healthy' ? 'success' : 'warning' }}"></i>
                                Application: {{ ucfirst($system_health['application']['status']) }}
                            </li>
                            <li>
                                <i class="fas fa-tachometer-alt me-2 text-info"></i>
                                Memory: {{ round($system_health['performance']['memory_usage'] / 1024 / 1024, 2) }}MB
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Performing Hotels</h3>
            </div>
            <div class="card-body">
                @if(empty($top_hotels))
                    <div class="text-center py-4">
                        <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No performance data available</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Hotel</th>
                                    <th>Views</th>
                                    <th>Clicks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($top_hotels, 0, 5) as $hotel)
                                    <tr>
                                        <td>
                                            <strong>{{ $hotel['hotel_name'] }}</strong><br>
                                            <small class="text-muted">{{ $hotel['city'] }}, {{ $hotel['country'] }}</small>
                                        </td>
                                        <td>{{ number_format($hotel['total_views']) }}</td>
                                        <td>{{ number_format($hotel['total_clicks']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Activities</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="refreshActivities()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(empty($recent_activities))
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No recent activities</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Details</th>
                                    <th>User</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_activities as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $activity['type'] === 'user_registered' ? 'primary' : ($activity['type'] === 'hotel_created' ? 'success' : 'info') }}">
                                                {{ str_replace('_', ' ', ucfirst($activity['type'])) }}
                                            </span>
                                        </td>
                                        <td>{{ $activity['title'] }}</td>
                                        <td>{{ $activity['subtitle'] ?? '' }}</td>
                                        <td>{{ $activity['user_name'] ?? 'System' }}</td>
                                        <td>{{ formatDate($activity['created_at'], 'M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.small-box {
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
}

.small-box > .inner {
    padding: 10px;
}

.small-box > .small-box-footer {
    background-color: rgba(0,0,0,.1);
    color: rgba(255,255,255,.8);
    display: block;
    padding: 3px 0;
    position: relative;
    text-align: center;
    text-decoration: none;
    z-index: 10;
}

.small-box .icon {
    color: rgba(0,0,0,.15);
    z-index: 0;
}

.small-box .icon > i {
    font-size: 70px;
    position: absolute;
    right: 15px;
    top: 15px;
}

.health-indicator.healthy { color: #28a745; }
.health-indicator.warning { color: #ffc107; }
.health-indicator.critical { color: #dc3545; }
</style>
@endsection

@section('scripts')
<script>
let growthChart, userChart;

$(document).ready(function() {
    loadDashboardData();
    
    $('#periodSelect').on('change', function() {
        loadDashboardData();
    });
});

function loadDashboardData() {
    const period = $('#periodSelect').val();
    
    $.get('{{ url("/admin/dashboard/data") }}', { period: period }, function(response) {
        updateCharts(response.data);
    });
}

function updateCharts(data) {
    // Growth Chart
    const ctx1 = document.getElementById('growthChart').getContext('2d');
    
    if (growthChart) {
        growthChart.destroy();
    }
    
    growthChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: data.users.map(item => item.date),
            datasets: [{
                label: 'Users',
                data: data.users.map(item => item.count),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Hotels',
                data: data.hotels.map(item => item.count),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Widgets',
                data: data.widgets.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // User Distribution Chart
    const ctx2 = document.getElementById('userChart').getContext('2d');
    
    if (userChart) {
        userChart.destroy();
    }
    
    userChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Admins', 'Resellers', 'Customers'],
            datasets: [{
                data: [
                    {{ $overview_stats['admin_count'] }},
                    {{ $overview_stats['reseller_count'] }},
                    {{ $overview_stats['customer_count'] }}
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56'
                ]
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

function refreshActivities() {
    location.reload();
}
</script>
@endsection
