/**
 * Hotel DigiLab - Main JavaScript
 */

// Global App Object
window.HotelDigiLab = {
    config: {
        baseUrl: window.location.origin,
        apiUrl: window.location.origin + '/api/v1',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    },
    
    // Utility functions
    utils: {
        // Format currency
        formatCurrency: function(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },
        
        // Format date
        formatDate: function(date, format = 'short') {
            const options = format === 'short' 
                ? { year: 'numeric', month: 'short', day: 'numeric' }
                : { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            
            return new Intl.DateTimeFormat('en-US', options).format(new Date(date));
        },
        
        // Show notification
        showNotification: function(message, type = 'info') {
            const alertClass = `alert-${type}`;
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container') || document.body;
            container.insertBefore(alert, container.firstChild);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        },
        
        // AJAX helper
        ajax: function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (HotelDigiLab.config.csrfToken) {
                defaults.headers['X-CSRF-TOKEN'] = HotelDigiLab.config.csrfToken;
            }
            
            const config = Object.assign({}, defaults, options);
            
            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    HotelDigiLab.utils.showNotification('An error occurred. Please try again.', 'danger');
                    throw error;
                });
        }
    },
    
    // Dashboard functionality
    dashboard: {
        init: function() {
            this.loadCharts();
            this.setupRefreshButton();
            this.setupRealTimeUpdates();
        },
        
        loadCharts: function() {
            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart');
            if (performanceCtx) {
                this.createPerformanceChart(performanceCtx);
            }
            
            // Widget Chart
            const widgetCtx = document.getElementById('widgetChart');
            if (widgetCtx) {
                this.createWidgetChart(widgetCtx);
            }
        },
        
        createPerformanceChart: function(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Views',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Clicks',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
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
        },
        
        createWidgetChart: function(ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
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
        },
        
        setupRefreshButton: function() {
            const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    HotelDigiLab.dashboard.refresh();
                });
            }
        },
        
        setupRealTimeUpdates: function() {
            // Update dashboard every 5 minutes
            setInterval(() => {
                this.refresh();
            }, 300000);
        },
        
        refresh: function() {
            const url = `${HotelDigiLab.config.baseUrl}/customer/dashboard/data`;
            
            HotelDigiLab.utils.ajax(url)
                .then(data => {
                    this.updateStats(data);
                    this.updateCharts(data);
                    HotelDigiLab.utils.showNotification('Dashboard updated successfully', 'success');
                })
                .catch(error => {
                    console.error('Dashboard refresh failed:', error);
                });
        },
        
        updateStats: function(data) {
            // Update stat cards
            const statCards = document.querySelectorAll('.stat-number');
            statCards.forEach(card => {
                const metric = card.dataset.metric;
                if (data.stats && data.stats[metric]) {
                    card.textContent = data.stats[metric];
                }
            });
        },
        
        updateCharts: function(data) {
            // Update charts with new data
            if (window.performanceChart && data.chart_data) {
                window.performanceChart.data.labels = data.chart_data.labels;
                window.performanceChart.data.datasets[0].data = data.chart_data.datasets.views;
                window.performanceChart.data.datasets[1].data = data.chart_data.datasets.clicks;
                window.performanceChart.update();
            }
        }
    },
    
    // Widget management
    widgets: {
        init: function() {
            this.setupCreateForm();
            this.setupEditForms();
            this.setupDeleteButtons();
        },
        
        setupCreateForm: function() {
            const form = document.getElementById('createWidgetForm');
            if (form) {
                form.addEventListener('submit', this.handleCreate.bind(this));
            }
        },
        
        setupEditForms: function() {
            const forms = document.querySelectorAll('.edit-widget-form');
            forms.forEach(form => {
                form.addEventListener('submit', this.handleEdit.bind(this));
            });
        },
        
        setupDeleteButtons: function() {
            const buttons = document.querySelectorAll('.delete-widget-btn');
            buttons.forEach(button => {
                button.addEventListener('click', this.handleDelete.bind(this));
            });
        },
        
        handleCreate: function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const url = `${HotelDigiLab.config.apiUrl}/widgets`;
            
            HotelDigiLab.utils.ajax(url, {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(response => {
                HotelDigiLab.utils.showNotification('Widget created successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                HotelDigiLab.utils.showNotification('Failed to create widget', 'danger');
            });
        },
        
        handleEdit: function(e) {
            e.preventDefault();
            const form = e.target;
            const widgetId = form.dataset.widgetId;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const url = `${HotelDigiLab.config.apiUrl}/widgets/${widgetId}`;
            
            HotelDigiLab.utils.ajax(url, {
                method: 'PUT',
                body: JSON.stringify(data)
            })
            .then(response => {
                HotelDigiLab.utils.showNotification('Widget updated successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                HotelDigiLab.utils.showNotification('Failed to update widget', 'danger');
            });
        },
        
        handleDelete: function(e) {
            e.preventDefault();
            const button = e.target;
            const widgetId = button.dataset.widgetId;
            
            if (!confirm('Are you sure you want to delete this widget?')) {
                return;
            }
            
            const url = `${HotelDigiLab.config.apiUrl}/widgets/${widgetId}`;
            
            HotelDigiLab.utils.ajax(url, {
                method: 'DELETE'
            })
            .then(response => {
                HotelDigiLab.utils.showNotification('Widget deleted successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            })
            .catch(error => {
                HotelDigiLab.utils.showNotification('Failed to delete widget', 'danger');
            });
        }
    },
    
    // Form validation
    validation: {
        init: function() {
            this.setupFormValidation();
        },
        
        setupFormValidation: function() {
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
        }
    },
    
    // Initialize all modules
    init: function() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initModules();
            });
        } else {
            this.initModules();
        }
    },
    
    initModules: function() {
        // Initialize modules based on page
        if (document.querySelector('.dashboard-page')) {
            this.dashboard.init();
        }
        
        if (document.querySelector('.widgets-page')) {
            this.widgets.init();
        }
        
        // Always initialize validation
        this.validation.init();
        
        // Setup global event listeners
        this.setupGlobalEvents();
    },
    
    setupGlobalEvents: function() {
        // Handle all AJAX form submissions
        document.addEventListener('submit', function(e) {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                HotelDigiLab.handleAjaxForm(e.target);
            }
        });
        
        // Handle confirmation dialogs
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('confirm-action')) {
                const message = e.target.dataset.confirm || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    },
    
    // Generic AJAX form handler
    handleAjaxForm: function(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const url = form.action || window.location.href;
        const method = form.method || 'POST';
        
        HotelDigiLab.utils.ajax(url, {
            method: method.toUpperCase(),
            body: JSON.stringify(data)
        })
        .then(response => {
            if (response.success) {
                HotelDigiLab.utils.showNotification(response.message || 'Operation completed successfully', 'success');
                
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                }
            } else {
                HotelDigiLab.utils.showNotification(response.message || 'Operation failed', 'danger');
            }
        })
        .catch(error => {
            HotelDigiLab.utils.showNotification('An error occurred. Please try again.', 'danger');
        });
    }
};

// Global functions for backward compatibility
function refreshDashboard() {
    HotelDigiLab.dashboard.refresh();
}

function bookRate(rateId) {
    // Track booking click and redirect
    const trackingUrl = `${HotelDigiLab.config.apiUrl}/widgets/track`;
    
    fetch(trackingUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event: 'booking_click',
            rate_id: rateId,
            timestamp: Date.now()
        })
    });
    
    window.open(`/booking?rate_id=${rateId}`, '_blank');
}

function refreshRates(widgetId) {
    const refreshButton = document.querySelector('.refresh-rates');
    if (refreshButton) {
        const originalText = refreshButton.innerHTML;
        refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshButton.disabled = true;
        
        const url = `${HotelDigiLab.config.apiUrl}/widgets/${widgetId}/render`;
        
        HotelDigiLab.utils.ajax(url)
            .then(data => {
                if (data.html) {
                    const widget = document.querySelector(`[data-widget-id="${widgetId}"]`);
                    if (widget) {
                        widget.outerHTML = data.html;
                    }
                }
            })
            .finally(() => {
                refreshButton.innerHTML = originalText;
                refreshButton.disabled = false;
            });
    }
}

// Initialize the application
HotelDigiLab.init();
