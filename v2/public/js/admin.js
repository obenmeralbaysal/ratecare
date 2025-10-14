/**
 * Admin Panel JavaScript
 */

// Admin namespace
window.Admin = {
    config: {
        baseUrl: window.location.origin,
        apiUrl: window.location.origin + '/api/v1',
        refreshInterval: 300000 // 5 minutes
    },
    
    // Dashboard functionality
    dashboard: {
        charts: {},
        
        init: function() {
            this.loadSystemStats();
            this.setupCharts();
            this.setupRealTimeUpdates();
            this.setupHealthMonitoring();
        },
        
        loadSystemStats: function() {
            const url = `${Admin.config.apiUrl}/admin/statistics`;
            
            HotelDigiLab.utils.ajax(url)
                .then(data => {
                    this.updateStatsBoxes(data);
                    this.updateCharts(data);
                })
                .catch(error => {
                    console.error('Failed to load system stats:', error);
                });
        },
        
        updateStatsBoxes: function(data) {
            // Update small boxes with latest stats
            const boxes = {
                'total_users': data.overview_stats?.total_users || 0,
                'total_hotels': data.overview_stats?.total_hotels || 0,
                'total_widgets': data.overview_stats?.total_widgets || 0,
                'total_views': data.overview_stats?.total_views || 0
            };
            
            Object.keys(boxes).forEach(key => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    element.textContent = this.formatNumber(boxes[key]);
                }
            });
        },
        
        setupCharts: function() {
            this.setupGrowthChart();
            this.setupUserDistributionChart();
        },
        
        setupGrowthChart: function() {
            const ctx = document.getElementById('growthChart');
            if (!ctx) return;
            
            this.charts.growth = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Users',
                        data: [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Hotels',
                        data: [],
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Widgets',
                        data: [],
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
        },
        
        setupUserDistributionChart: function() {
            const ctx = document.getElementById('userChart');
            if (!ctx) return;
            
            this.charts.userDistribution = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Admins', 'Resellers', 'Customers'],
                    datasets: [{
                        data: [0, 0, 0],
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
        },
        
        updateCharts: function(data) {
            // Update growth chart
            if (this.charts.growth && data.growth_data) {
                this.charts.growth.data.labels = data.growth_data.labels || [];
                this.charts.growth.data.datasets[0].data = data.growth_data.users || [];
                this.charts.growth.data.datasets[1].data = data.growth_data.hotels || [];
                this.charts.growth.data.datasets[2].data = data.growth_data.widgets || [];
                this.charts.growth.update();
            }
            
            // Update user distribution chart
            if (this.charts.userDistribution && data.overview_stats) {
                this.charts.userDistribution.data.datasets[0].data = [
                    data.overview_stats.admin_count || 0,
                    data.overview_stats.reseller_count || 0,
                    data.overview_stats.customer_count || 0
                ];
                this.charts.userDistribution.update();
            }
        },
        
        setupRealTimeUpdates: function() {
            // Update dashboard every 5 minutes
            setInterval(() => {
                this.loadSystemStats();
            }, Admin.config.refreshInterval);
        },
        
        setupHealthMonitoring: function() {
            this.checkSystemHealth();
            
            // Check health every minute
            setInterval(() => {
                this.checkSystemHealth();
            }, 60000);
        },
        
        checkSystemHealth: function() {
            const url = `${Admin.config.apiUrl}/admin/health`;
            
            HotelDigiLab.utils.ajax(url)
                .then(data => {
                    this.updateHealthIndicators(data);
                })
                .catch(error => {
                    console.error('Health check failed:', error);
                    this.updateHealthIndicators({ overall_status: 'critical' });
                });
        },
        
        updateHealthIndicators: function(health) {
            const indicator = document.querySelector('.health-indicator');
            if (indicator) {
                indicator.className = `health-indicator ${health.overall_status || 'critical'}`;
                
                const icon = indicator.querySelector('i');
                if (icon) {
                    icon.className = this.getHealthIcon(health.overall_status);
                }
            }
            
            // Update individual service indicators
            if (health.database) {
                this.updateServiceIndicator('database', health.database.status);
            }
            if (health.application) {
                this.updateServiceIndicator('application', health.application.status);
            }
        },
        
        updateServiceIndicator: function(service, status) {
            const element = document.querySelector(`[data-service="${service}"] i`);
            if (element) {
                element.className = `fas fa-${this.getServiceIcon(service)} me-2 text-${this.getStatusColor(status)}`;
            }
        },
        
        getHealthIcon: function(status) {
            switch (status) {
                case 'healthy': return 'fas fa-check-circle';
                case 'warning': return 'fas fa-exclamation-triangle';
                case 'critical': return 'fas fa-times-circle';
                default: return 'fas fa-question-circle';
            }
        },
        
        getServiceIcon: function(service) {
            switch (service) {
                case 'database': return 'database';
                case 'application': return 'server';
                default: return 'cog';
            }
        },
        
        getStatusColor: function(status) {
            switch (status) {
                case 'healthy': return 'success';
                case 'warning': return 'warning';
                case 'critical': return 'danger';
                default: return 'secondary';
            }
        },
        
        formatNumber: function(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        }
    },
    
    // User management
    users: {
        init: function() {
            this.setupUserActions();
            this.setupInviteForm();
        },
        
        setupUserActions: function() {
            // Activate/Deactivate users
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('toggle-user-status')) {
                    e.preventDefault();
                    Admin.users.toggleUserStatus(e.target);
                }
            });
        },
        
        toggleUserStatus: function(button) {
            const userId = button.dataset.userId;
            const currentStatus = button.dataset.status;
            const newStatus = currentStatus === 'active' ? 'deactivate' : 'activate';
            
            const url = `${Admin.config.apiUrl}/users/${userId}/${newStatus}`;
            
            HotelDigiLab.utils.ajax(url, { method: 'POST' })
                .then(response => {
                    HotelDigiLab.utils.showNotification(response.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                })
                .catch(error => {
                    HotelDigiLab.utils.showNotification('Failed to update user status', 'danger');
                });
        },
        
        setupInviteForm: function() {
            const form = document.getElementById('inviteUserForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Admin.users.sendInvite(form);
                });
            }
        },
        
        sendInvite: function(form) {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const url = `${Admin.config.apiUrl}/users/invite`;
            
            HotelDigiLab.utils.ajax(url, {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(response => {
                HotelDigiLab.utils.showNotification('Invitation sent successfully', 'success');
                form.reset();
            })
            .catch(error => {
                HotelDigiLab.utils.showNotification('Failed to send invitation', 'danger');
            });
        }
    },
    
    // Settings management
    settings: {
        init: function() {
            this.setupSettingsForm();
            this.setupImportExport();
        },
        
        setupSettingsForm: function() {
            const form = document.getElementById('settingsForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Admin.settings.saveSettings(form);
                });
            }
        },
        
        saveSettings: function(form) {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const url = `${Admin.config.apiUrl}/settings`;
            
            HotelDigiLab.utils.ajax(url, {
                method: 'POST',
                body: JSON.stringify(data)
            })
            .then(response => {
                HotelDigiLab.utils.showNotification('Settings saved successfully', 'success');
            })
            .catch(error => {
                HotelDigiLab.utils.showNotification('Failed to save settings', 'danger');
            });
        },
        
        setupImportExport: function() {
            // Export settings
            const exportBtn = document.getElementById('exportSettings');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    Admin.settings.exportSettings();
                });
            }
            
            // Import settings
            const importBtn = document.getElementById('importSettings');
            if (importBtn) {
                importBtn.addEventListener('click', function() {
                    document.getElementById('importFile').click();
                });
            }
            
            const importFile = document.getElementById('importFile');
            if (importFile) {
                importFile.addEventListener('change', function() {
                    Admin.settings.importSettings(this.files[0]);
                });
            }
        },
        
        exportSettings: function() {
            const url = `${Admin.config.apiUrl}/settings/export`;
            
            HotelDigiLab.utils.ajax(url)
                .then(data => {
                    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `settings-${new Date().toISOString().split('T')[0]}.json`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                })
                .catch(error => {
                    HotelDigiLab.utils.showNotification('Failed to export settings', 'danger');
                });
        },
        
        importSettings: function(file) {
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    
                    const url = `${Admin.config.apiUrl}/settings/import`;
                    
                    HotelDigiLab.utils.ajax(url, {
                        method: 'POST',
                        body: JSON.stringify(settings)
                    })
                    .then(response => {
                        HotelDigiLab.utils.showNotification('Settings imported successfully', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    })
                    .catch(error => {
                        HotelDigiLab.utils.showNotification('Failed to import settings', 'danger');
                    });
                } catch (error) {
                    HotelDigiLab.utils.showNotification('Invalid settings file', 'danger');
                }
            };
            reader.readAsText(file);
        }
    },
    
    // Cache management
    cache: {
        init: function() {
            this.setupCacheActions();
            this.loadCacheStats();
        },
        
        setupCacheActions: function() {
            const clearBtn = document.getElementById('clearCache');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    Admin.cache.clearCache();
                });
            }
        },
        
        clearCache: function() {
            if (!confirm('Are you sure you want to clear all cache?')) {
                return;
            }
            
            const url = `${Admin.config.apiUrl}/admin/cache/clear`;
            
            HotelDigiLab.utils.ajax(url, { method: 'POST' })
                .then(response => {
                    HotelDigiLab.utils.showNotification('Cache cleared successfully', 'success');
                    this.loadCacheStats();
                })
                .catch(error => {
                    HotelDigiLab.utils.showNotification('Failed to clear cache', 'danger');
                });
        },
        
        loadCacheStats: function() {
            const url = `${Admin.config.apiUrl}/admin/cache/stats`;
            
            HotelDigiLab.utils.ajax(url)
                .then(data => {
                    this.updateCacheStats(data);
                })
                .catch(error => {
                    console.error('Failed to load cache stats:', error);
                });
        },
        
        updateCacheStats: function(stats) {
            const elements = {
                'cache-size': stats.total_size_formatted || '0 B',
                'cache-files': stats.total_files || 0,
                'cache-hits': stats.hit_rate || '0%'
            };
            
            Object.keys(elements).forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = elements[id];
                }
            });
        }
    },
    
    // Initialize admin panel
    init: function() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initModules();
            });
        } else {
            this.initModules();
        }
    },
    
    initModules: function() {
        // Initialize based on current page
        if (document.querySelector('.admin-dashboard')) {
            this.dashboard.init();
        }
        
        if (document.querySelector('.users-management')) {
            this.users.init();
        }
        
        if (document.querySelector('.settings-management')) {
            this.settings.init();
        }
        
        if (document.querySelector('.cache-management')) {
            this.cache.init();
        }
        
        // Setup sidebar toggle for mobile
        this.setupSidebarToggle();
    },
    
    setupSidebarToggle: function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.main-sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
        }
    }
};

// Initialize admin panel
Admin.init();
