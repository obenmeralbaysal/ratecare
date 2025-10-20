<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo csrfToken(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | Users Management</title>
    <link rel="icon" href="<?php echo asset('assets/images/favicon.ico'); ?>" type="image/x-icon">
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
        
        .table th {
            vertical-align: middle;
        }
        
        .table tbody tr {
            vertical-align: middle;
        }
        
        .table tbody tr td {
            vertical-align: middle;
            padding: 12px 8px;
        }
        
        .table tbody tr th {
            vertical-align: middle;
            padding: 12px 8px;
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
        
        .badge {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
            vertical-align: middle;
        }
        
        .badge-danger {
            background: #dc3545;
            color: #fff;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        
        .badge-info {
            background: #17a2b8;
            color: #fff;
        }
        
        .badge-secondary {
            background: #6c757d;
            color: #fff;
        }
        
        .py-4 {
            padding: 2rem 0;
        }
        
        .mb-2 {
            margin-bottom: 0.5rem;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .pagination-info {
            margin-top: 15px;
        }
        
        .pagination .page-link {
            color: #007bff;
            background-color: #fff;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
        }
        
        .pagination .page-link:hover {
            color: #0056b3;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-nowrap {
            white-space: nowrap;
        }
        
        /* Action buttons alignment */
        .table tbody tr td:last-child {
            vertical-align: middle;
            text-align: right;
        }
        
        .table tbody tr td .btn {
            vertical-align: middle;
            margin: 0 1px;
        }
        
        /* Pagination goto styles */
        .pagination-goto {
            margin: 15px 0;
        }
        
        .pagination-input {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
        
        .pagination-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            outline: none;
        }
        
        .d-inline-flex {
            display: inline-flex;
        }
        
        .align-items-center {
            align-items: center;
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
        .ml-2 {
            margin-left: 0.5rem;
        }
        
        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
        }
        
        .btn-outline-primary:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        /* Hotel link styles */
        .hotel-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .hotel-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .hotel-name {
            color: #333;
            font-weight: 500;
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
                    <a class="navbar-brand" href="<?php echo url('/dashboard'); ?>">
                        <img src="<?php echo asset('common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
                    </a>
                </div>
            </li>
            
            <li class="float-right">
                <a href="javascript:void(0);" class="js-right-sidebar">
                    <i class="zmdi zmdi-settings"></i>
                </a>
                <a href="<?php echo url('/logout'); ?>" class="mega-menu">
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
                        <a href="<?php echo url('/dashboard'); ?>">
                            <i class="zmdi zmdi-home"></i>
                        </a>
                    </li>
                    <li class="active">
                        <a href="javascript:void(0)">Users</a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/hotels'); ?>">Hotels</a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/widgets'); ?>">Widgets</a>
                    </li>
                    <li>
                        <a href="<?php echo url('/admin/settings'); ?>">Settings</a>
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
                    <a href="<?php echo url('/admin/users/create'); ?>">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left">
                            <i class="zmdi zmdi-plus"></i> New User
                        </button>
                    </a>
                    <a href="<?php echo url('/admin/users/invite'); ?>">
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
                        <form method="GET" action="<?php echo url('/admin/users'); ?>" accept-charset="UTF-8" id="filter-form" class="input-group">
                            <input class="form-control filter-text" name="q" value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Enter text to search by name or email">
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
                                <?php if(empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="zmdi zmdi-account-o zmdi-hc-2x text-muted mb-2"></i>
                                                <p class="text-muted">No users found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($users as $user): ?>
                                        <tr>
                                            <th scope="row"><?php echo htmlspecialchars($user['id']); ?></th>
                                            <td class="td-namesurname">
                                                <a href="<?php echo url('/admin/users/edit/' . $user['id']); ?>">
                                                    <?php echo htmlspecialchars($user['namesurname']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if($user['hotel_name']): ?>
                                                    <?php if($user['hotel_web_url']): ?>
                                                        <a href="<?php echo htmlspecialchars($user['hotel_web_url']); ?>" target="_blank" class="hotel-link">
                                                            <?php echo htmlspecialchars($user['hotel_name']); ?>
                                                            <i class="zmdi zmdi-open-in-new" style="font-size: 12px; margin-left: 4px;"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="hotel-name"><?php echo htmlspecialchars($user['hotel_name']); ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No hotel</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td class="text-center">
                                                <?php if($user['is_admin']): ?>
                                                    <span class="badge badge-danger">ADMIN</span>
                                                <?php elseif($user['user_type'] == 2): ?>
                                                    <span class="badge badge-warning">RESELLER</span>
                                                <?php elseif($user['reseller_id'] > 0): ?>
                                                    <span class="badge badge-info">CUSTOMER</span>
                                                    <?php if($user['reseller_name']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($user['reseller_name']); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">STANDARD</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap"><?php echo formatDate($user['created_at'], 'M d, Y'); ?></td>
                                            <td class="text-nowrap text-right">
                                                <a href="<?php echo url('/admin/users/switch/' . $user['id'] . '?redirect=hotels'); ?>" title="EDIT PROPERTY">
                                                    <button class="btn btn-warning btn-sm"><i class="zmdi zmdi-city-alt"></i></button>
                                                </a>
                                                <a href="<?php echo url('/admin/users/delete/' . $user['id']); ?>" onclick="return confirm('Are you sure you want to delete this user?')" title="DELETE USER">
                                                    <button class="btn btn-danger btn-sm"><i class="zmdi zmdi-delete"></i></button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <!-- First Button -->
                                    <?php if($pagination['current_page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo url('/admin/users?page=1' . ($search ? '&q=' . urlencode($search) : '')); ?>" title="First Page">
                                                <i class="zmdi zmdi-skip-previous"></i> First
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="zmdi zmdi-skip-previous"></i> First</span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Previous Button -->
                                    <?php if($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['prev_page'] . ($search ? '&q=' . urlencode($search) : '')); ?>" title="Previous Page">
                                                <i class="zmdi zmdi-chevron-left"></i> Prev
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="zmdi zmdi-chevron-left"></i> Prev</span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Page Numbers -->
                                    <?php 
                                    $startPage = max(1, $pagination['current_page'] - 2);
                                    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                    ?>
                                    
                                    <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                                        <?php if($i == $pagination['current_page']): ?>
                                            <li class="page-item active">
                                                <span class="page-link"><?php echo $i; ?></span>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item">
                                                <a class="page-link" href="<?php echo url('/admin/users?page=' . $i . ($search ? '&q=' . urlencode($search) : '')); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <!-- Next Button -->
                                    <?php if($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['next_page'] . ($search ? '&q=' . urlencode($search) : '')); ?>" title="Next Page">
                                                Next <i class="zmdi zmdi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Next <i class="zmdi zmdi-chevron-right"></i></span>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <!-- Last Button -->
                                    <?php if($pagination['current_page'] < $pagination['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo url('/admin/users?page=' . $pagination['total_pages'] . ($search ? '&q=' . urlencode($search) : '')); ?>" title="Last Page">
                                                Last <i class="zmdi zmdi-skip-next"></i>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Last <i class="zmdi zmdi-skip-next"></i></span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                
                                <!-- Go to Page Input -->
                                <div class="pagination-goto mt-3 text-center">
                                    <form method="GET" action="<?php echo url('/admin/users'); ?>" class="d-inline-flex align-items-center">
                                        <?php if($search): ?>
                                            <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                                        <?php endif; ?>
                                        <span class="mr-2">Go to page:</span>
                                        <input type="number" name="page" min="1" max="<?php echo $pagination['total_pages']; ?>" 
                                               value="<?php echo $pagination['current_page']; ?>" 
                                               class="form-control form-control-sm pagination-input mr-2" 
                                               style="width: 80px;">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Go</button>
                                        <span class="ml-2 text-muted">of <?php echo $pagination['total_pages']; ?></span>
                                    </form>
                                </div>
                                
                                <!-- Pagination Info -->
                                <div class="pagination-info text-center mt-3">
                                    <small class="text-muted">
                                        Showing <?php echo (($pagination['current_page'] - 1) * $pagination['per_page']) + 1; ?> 
                                        to <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total']); ?> 
                                        of <?php echo $pagination['total']; ?> results
                                    </small>
                                </div>
                            </nav>
                        <?php endif; ?>
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
