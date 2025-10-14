@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <p class="text-muted">Welcome back, {{ $user['namesurname'] }}!</p>
                </div>
                <div>
                    <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_hotels'] }}</h4>
                            <p class="mb-0">Hotels</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_widgets'] }}</h4>
                            <p class="mb-0">Widgets</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-puzzle-piece fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_views']) }}</h4>
                            <p class="mb-0">Total Views</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['conversion_rate'] }}%</h4>
                            <p class="mb-0">Conversion Rate</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performance Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Widgets</h5>
                </div>
                <div class="card-body">
                    <canvas id="widgetChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hotels and Recent Activities -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Hotels</h5>
                    <a href="{{ url('/customer/hotels/create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Add Hotel
                    </a>
                </div>
                <div class="card-body">
                    @if(empty($hotels))
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hotels found</p>
                            <a href="{{ url('/customer/hotels/create') }}" class="btn btn-primary">
                                Create Your First Hotel
                            </a>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($hotels as $hotel)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $hotel['name'] }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $hotel['city'] }}, {{ $hotel['country'] }}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $hotel['is_active'] ? 'success' : 'secondary' }}">
                                            {{ $hotel['is_active'] ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(count($hotels) > 5)
                            <div class="text-center mt-3">
                                <a href="{{ url('/customer/hotels') }}" class="btn btn-outline-primary btn-sm">
                                    View All Hotels
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    @if(empty($recent_activities))
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No recent activities</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($recent_activities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $activity['metric_name'] }}</h6>
                                        <p class="mb-1 text-muted">
                                            {{ $activity['hotel_name'] }}
                                            @if($activity['widget_name'])
                                                - {{ $activity['widget_name'] }}
                                            @endif
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ formatDate($activity['created_at'], 'M d, Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    width: 2px;
    height: calc(100% + 5px);
    background-color: #dee2e6;
}
</style>
@endsection

@section('scripts')
<script>
let performanceChart, widgetChart;

$(document).ready(function() {
    loadDashboardData();
});

function loadDashboardData() {
    $.get('{{ url("/customer/dashboard/data") }}', function(response) {
        updateCharts(response);
    });
}

function updateCharts(data) {
    // Performance Chart
    const ctx1 = document.getElementById('performanceChart').getContext('2d');
    
    if (performanceChart) {
        performanceChart.destroy();
    }
    
    performanceChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: data.chart_data.labels,
            datasets: [{
                label: 'Views',
                data: data.chart_data.datasets.views,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Clicks',
                data: data.chart_data.datasets.clicks,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
            }, {
                label: 'Bookings',
                data: data.chart_data.datasets.bookings,
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
    
    // Widget Chart
    const ctx2 = document.getElementById('widgetChart').getContext('2d');
    
    if (widgetChart) {
        widgetChart.destroy();
    }
    
    if (data.top_widgets && data.top_widgets.length > 0) {
        widgetChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: data.top_widgets.map(w => w.widget_name),
                datasets: [{
                    data: data.top_widgets.map(w => w.total_value),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
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
}

function refreshDashboard() {
    loadDashboardData();
    location.reload();
}
</script>
@endsection
