<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrfToken() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | Users Management</title>
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
        
        .navbar {
            background: #2c2c2c;
            border: none;
            padding: 0;
            min-height: 60px;
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
        
        .navbar .nav li a {
            color: #fff;
            text-decoration: none;
            padding: 15px;
            display: flex;
            align-items: center;
        }
        
        .navbar .nav li a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .menu-container {
            background: #333;
            border-bottom: 1px solid #444;
        }
        
        .h-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        
        .h-menu li a {
            color: #fff;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: all 0.3s ease;
        }
        
        .h-menu li:hover > a,
        .h-menu li.active > a {
            background: #007bff;
        }
        
        .content {
            padding: 30px 0;
            min-height: calc(100vh - 120px);
        }
        
        .block-header {
            margin-bottom: 30px;
        }
        
        .block-header h2 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            margin-right: 15px;
        }
        
        .new-widget-btn {
            margin-right: 10px;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .card .body {
            padding: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .table td {
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        
        .btn {
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 11px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
        }
        
        .btn-warning {
            background: #ffc107;
            border: none;
            color: #333;
        }
        
        .btn-danger {
            background: #dc3545;
            border: none;
        }
        
        .text-nowrap {
            white-space: nowrap;
        }
        
        .filter-text {
            border-radius: 25px 0 0 25px;
        }
        
        .filter-submit-btn {
            border-radius: 0 25px 25px 0;
        }
        
        .td-namesurname a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .td-namesurname a:hover {
            text-decoration: underline;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination .page-link {
            border-radius: 4px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            background: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body class="theme-black">

<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="h-bars">☰</a>
                    <a class="navbar-brand" href="{{ url('/dashboard') }}">
                        <img src="{{ asset('assets/common/img/rate-care-logo.fw.png') }}" alt="RateCare">
                    </a>
                </div>
            </li>
            
            <li class="float-right">
                <a href="javascript:void(0);" class="js-right-sidebar">
                    <i class="zmdi zmdi-settings"></i>
                </a>
                <a href="{{ url('/logout') }}" class="mega-menu">
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
                        <a href="{{ url('/dashboard') }}">
                            <i class="zmdi zmdi-home"></i>
                        </a>
                    </li>
                    <li class="active">
                        <a href="javascript:void(0)">Users</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/hotels') }}">Hotels</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/widgets') }}">Widgets</a>
                    </li>
                    <li>
                        <a href="{{ url('/admin/settings') }}">Settings</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="content home">
    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Users</h2>
                    <a href="{{ url('/admin/users/create') }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left">
                            <i class="zmdi zmdi-plus"></i> New User
                        </button>
                    </a>
                    <a href="{{ url('/admin/users/invite') }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left">
                            <i class="zmdi zmdi-mail-send"></i> Invite
                        </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">

                <div class="card">
                    <div class="body">
                        <form method="GET" action="" accept-charset="UTF-8" id="filter-form" class="input-group">
                            <input class="form-control filter-text" name="q" value="{{ request('q') }}"
                                   placeholder="Enter text to search by name">
                            <div class="input-group-append">
                                <button type="submit" name="filter" value="1"
                                        class="btn btn-primary btn-round waves-effect filter-submit-btn">
                                    SEARCH
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="body table-responsive">
                        <table class="table m-b-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>NAME</th>
                                <th>HOTEL</th>
                                <th>MAIL</th>
                                <th>RESELLER</th>
                                <th>JOINED AT</th>
                                <th>ACTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                                <!-- Sample data for demo -->
                                <tr>
                                    <th scope="row">1</th>
                                    <td class="td-namesurname">
                                        <a href="#">Test Admin</a>
                                    </td>
                                    <td>
                                        <a href="#" target="_blank">Sample Hotel</a>
                                    </td>
                                    <td>admin@ratecare.net</td>
                                    <td class="text-center">ADMIN ACCOUNT</td>
                                    <td class="text-nowrap">Oct 20, 2025</td>
                                    <td class="text-nowrap text-right">
                                        <a href="#" title="EDIT PROPERTY">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-city-alt"></i></button>
                                        </a>
                                        <a href="#" title="EDIT WIDGET">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-layers"></i></button>
                                        </a>
                                        <a href="#" title="EDIT USER">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-edit"></i></button>
                                        </a>
                                        <a href="#" onclick="return confirm('Are you sure?')" title="DELETE USER">
                                            <span class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i></span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td class="td-namesurname">
                                        <a href="#">Sample Reseller</a>
                                    </td>
                                    <td>
                                        <a href="#" target="_blank">Reseller Hotel</a>
                                    </td>
                                    <td>reseller@example.com</td>
                                    <td class="text-center">RESELLER ACCOUNT</td>
                                    <td class="text-nowrap">Oct 19, 2025</td>
                                    <td class="text-nowrap text-right">
                                        <a href="#" title="EDIT PROPERTY">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-city-alt"></i></button>
                                        </a>
                                        <a href="#" title="EDIT WIDGET">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-layers"></i></button>
                                        </a>
                                        <a href="#" title="EDIT USER">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-edit"></i></button>
                                        </a>
                                        <a href="#" onclick="return confirm('Are you sure?')" title="DELETE USER">
                                            <span class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i></span>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td class="td-namesurname">
                                        <a href="#">Customer User</a>
                                    </td>
                                    <td>
                                        <a href="#" target="_blank">Customer Hotel</a>
                                    </td>
                                    <td>customer@example.com</td>
                                    <td class="text-center">Sample Reseller</td>
                                    <td class="text-nowrap">Oct 18, 2025</td>
                                    <td class="text-nowrap text-right">
                                        <a href="#" title="EDIT PROPERTY">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-city-alt"></i></button>
                                        </a>
                                        <a href="#" title="EDIT WIDGET">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-layers"></i></button>
                                        </a>
                                        <a href="#" title="EDIT USER">
                                            <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-edit"></i></button>
                                        </a>
                                        <a href="#" onclick="return confirm('Are you sure?')" title="DELETE USER">
                                            <span class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i></span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
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
</script>

</body>
</html>
