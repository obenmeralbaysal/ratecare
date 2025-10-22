@extends('layouts.admin-new')

@section('title', 'System Logs')

@section('menu-logs', 'active')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>System Logs</h4>
                <div class="float-right">
                    <a href="<?php echo url('/admin/logs/download'); ?>" class="btn btn-info">
                        <i class="zmdi zmdi-download"></i> Download Logs
                    </a>
                    <a href="<?php echo url('/admin/logs/clear'); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                        <i class="zmdi zmdi-delete"></i> Clear Logs
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Timestamp</th>
                                <th style="width: 80px;">Level</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-center">Loading logs...</td>
                            </tr>
                        </tbody>
                    </table>
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
