<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ url('/admin') }}" class="brand-link">
        <i class="fas fa-hotel brand-icon"></i>
        <span class="brand-text font-weight-light">Hotel DigiLab</span>
    </a>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block text-white">{{ user()['namesurname'] ?? 'Admin' }}</a>
                <small class="text-muted">Administrator</small>
            </div>
        </div>
        
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('/admin') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- User Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            User Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/admin/users') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/users/create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/users/invite') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Invite User</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Hotel Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-building"></i>
                        <p>
                            Hotels
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/admin/hotels') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Hotels</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/hotels/statistics') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Hotel Statistics</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Widget Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-puzzle-piece"></i>
                        <p>
                            Widgets
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/admin/widgets') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Widgets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/widgets/performance') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Performance</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Rate Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            Rates
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/admin/rates') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Rates</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/rates/channels') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rate Channels</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Statistics -->
                <li class="nav-item">
                    <a href="{{ url('/admin/statistics') }}" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Statistics</p>
                    </a>
                </li>
                
                <!-- System -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            System
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/admin/settings') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/logs') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>System Logs</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/admin/cache') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cache Management</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Back to Site -->
                <li class="nav-item mt-3">
                    <a href="{{ url('/') }}" class="nav-link">
                        <i class="nav-icon fas fa-arrow-left"></i>
                        <p>Back to Site</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
