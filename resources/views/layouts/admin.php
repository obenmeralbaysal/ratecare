<!DOCTYPE html>
<html lang="{{ $language ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Admin Panel' }} - Hotel DigiLab</title>
    
    <!-- CSRF Token -->
    {!! csrfField() !!}
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body class="admin-layout">
    <div class="wrapper">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            @include('admin.partials.navbar')
            
            <!-- Flash Messages -->
            @include('partials.flash-messages')
            
            <!-- Page Content -->
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">{{ $title ?? 'Dashboard' }}</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    @yield('breadcrumb')
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                
                <section class="content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </section>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Admin JS -->
    <script src="{{ asset('js/admin.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
