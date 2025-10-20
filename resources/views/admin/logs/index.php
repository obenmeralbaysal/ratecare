<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo csrfToken(); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>RateCare | Log Viewer</title>
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
            background: #ffffff;
            border: none;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
            min-height: 60px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        /* Logout button styles */
        .navbar-nav {
            display: flex;
            align-items: center;
        }
        
        .logout-btn {
            color: #333;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #dc3545;
            color: #fff;
            text-decoration: none;
        }
        
        .logout-btn i {
            font-size: 16px;
        }
        
        .ml-1 {
            margin-left: 0.25rem;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px 15px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
            outline: none;
        }
        
        .btn {
            border-radius: 4px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 8px 16px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: #fff;
        }
        
        .btn-success {
            background: #28a745;
            border: none;
            color: #fff;
        }
        
        .btn-warning {
            background: #ffc107;
            border: none;
            color: #333;
        }
        
        .btn-danger {
            background: #dc3545;
            border: none;
            color: #fff;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .log-viewer {
            background: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            border-radius: 4px;
            max-height: 600px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .log-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .log-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .log-controls .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 200px;
        }
        
        .log-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .log-stat {
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
            flex: 1;
            min-width: 150px;
        }
        
        .log-stat h6 {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .log-stat .value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .empty-logs {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-logs i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ccc;
        }
    </style>
</head>
<body class="theme-black">

<nav class="navbar">
    <div class="container">
        <!-- Logo - Sol taraf -->
        <a class="navbar-brand" href="<?php echo url('/dashboard'); ?>">
            <img src="<?php echo asset('common/img/rate-care-logo.fw.png'); ?>" alt="RateCare">
        </a>
        
        <!-- Çıkış butonu - Sağ taraf -->
        <div class="navbar-nav">
            <a href="<?php echo url('/logout'); ?>" class="logout-btn">
                <i class="zmdi zmdi-power"></i>
                <span class="ml-1">Logout</span>
            </a>
        </div>
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
                    <li>
                        <a href="<?php echo url('/admin/users'); ?>">Users</a>
                    </li>
                    <li class="active">
                        <a href="<?php echo url('/admin/logs'); ?>">Logs</a>
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
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2 class="float-left">Log Viewer</h2>
                </div>
            </div>
        </div>

        <?php if(flash('error')): ?>
            <div class="alert alert-danger"><?php echo flash('error'); ?></div>
        <?php endif; ?>
        
        <?php if(flash('success')): ?>
            <div class="alert alert-success"><?php echo flash('success'); ?></div>
        <?php endif; ?>

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body">
                        <div class="log-controls">
                            <div class="form-group">
                                <label>Select Log File:</label>
                                <select class="form-control" id="logFileSelect" onchange="changeLogFile()">
                                    <option value="">-- Select Log File --</option>
                                    <?php foreach($logFiles as $file): ?>
                                        <option value="<?php echo htmlspecialchars($file); ?>" 
                                                <?php echo $file === $selectedFile ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($file); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if($selectedFile): ?>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <a href="<?php echo url('/admin/logs/download?file=' . urlencode($selectedFile)); ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="zmdi zmdi-download"></i> Download
                                        </a>
                                        <a href="<?php echo url('/admin/logs/clear?file=' . urlencode($selectedFile)); ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to clear this log file?')">
                                            <i class="zmdi zmdi-delete"></i> Clear
                                        </a>
                                        <button class="btn btn-primary btn-sm" onclick="refreshLog()">
                                            <i class="zmdi zmdi-refresh"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($selectedFile): ?>
                            <div class="log-stats">
                                <div class="log-stat">
                                    <h6>File Name</h6>
                                    <div class="value"><?php echo htmlspecialchars($selectedFile); ?></div>
                                </div>
                                <div class="log-stat">
                                    <h6>Total Lines</h6>
                                    <div class="value"><?php echo number_format($totalLines); ?></div>
                                </div>
                                <div class="log-stat">
                                    <h6>File Size</h6>
                                    <div class="value">
                                        <?php 
                                        $filePath = __DIR__ . '/../../../storage/logs/' . $selectedFile;
                                        if (file_exists($filePath)) {
                                            $size = filesize($filePath);
                                            if ($size < 1024) {
                                                echo $size . ' B';
                                            } elseif ($size < 1048576) {
                                                echo round($size / 1024, 2) . ' KB';
                                            } else {
                                                echo round($size / 1048576, 2) . ' MB';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="log-info">
                                <strong>Note:</strong> For performance reasons, only the last 50KB of the log file is displayed. 
                                Use the download button to get the complete log file.
                            </div>

                            <div class="log-viewer" id="logContent">
                                <?php echo htmlspecialchars($logContent); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-logs">
                                <i class="zmdi zmdi-file-text"></i>
                                <h4>No Log Files Found</h4>
                                <p>There are no log files available to display.</p>
                            </div>
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
    function changeLogFile() {
        const select = document.getElementById('logFileSelect');
        const selectedFile = select.value;
        
        if (selectedFile) {
            window.location.href = '<?php echo url('/admin/logs'); ?>?file=' + encodeURIComponent(selectedFile);
        }
    }
    
    function refreshLog() {
        window.location.reload();
    }
    
    // Auto-scroll to bottom of log content
    document.addEventListener('DOMContentLoaded', function() {
        const logContent = document.getElementById('logContent');
        if (logContent) {
            logContent.scrollTop = logContent.scrollHeight;
        }
    });
    
    // Auto-refresh every 30 seconds if enabled
    let autoRefresh = false;
    
    function toggleAutoRefresh() {
        autoRefresh = !autoRefresh;
        if (autoRefresh) {
            setInterval(function() {
                if (autoRefresh) {
                    refreshLog();
                }
            }, 30000);
        }
    }
</script>

</body>
</html>
