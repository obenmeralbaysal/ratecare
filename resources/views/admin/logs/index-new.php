@extends('layouts.admin-new')

@section('title', 'System Logs')

@section('menu-logs', 'active')

@section('content')
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
@endsection

@section('styles')
<style>
    .table tbody tr td:nth-child(2) {
        font-weight: bold;
    }
    .badge-error { background: #dc3545; }
    .badge-warning { background: #ffc107; }
    .badge-info { background: #17a2b8; }
    .badge-debug { background: #6c757d; }
</style>
@endsection
