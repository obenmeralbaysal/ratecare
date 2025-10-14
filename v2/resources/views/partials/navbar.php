<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-hotel me-2"></i>
            Hotel DigiLab
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @if(loggedIn())
                    @if(isCustomer())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-1"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/hotels') }}">
                                <i class="fas fa-building me-1"></i>
                                Hotels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/widgets') }}">
                                <i class="fas fa-puzzle-piece me-1"></i>
                                Widgets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/rates') }}">
                                <i class="fas fa-chart-line me-1"></i>
                                Rates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/statistics') }}">
                                <i class="fas fa-chart-bar me-1"></i>
                                Statistics
                            </a>
                        </li>
                    @endif
                    
                    @if(isAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/admin') }}">
                                <i class="fas fa-cog me-1"></i>
                                Admin Panel
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
            
            <ul class="navbar-nav">
                @if(loggedIn())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            {{ user()['namesurname'] ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ url('/profile') }}">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a></li>
                            <li><a class="dropdown-item" href="{{ url('/settings') }}">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ url('/logout') }}">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
