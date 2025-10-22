@extends('layouts.admin-new')

@section('title', 'Users Management')

@section('menu-users', 'active')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>All Users</h4>
                <div class="float-right">
                    <a href="<?php echo url('/admin/users/create'); ?>" class="btn btn-primary">
                        <i class="zmdi zmdi-plus"></i> New User
                    </a>
                    <a href="<?php echo url('/admin/users/invite'); ?>" class="btn btn-info">
                        <i class="zmdi zmdi-email"></i> Invite User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- User list will be loaded here -->
                            <tr>
                                <td colspan="7" class="text-center">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
