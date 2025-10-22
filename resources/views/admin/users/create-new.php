@extends('layouts.admin-new')

@section('title', 'Create New User')

@section('menu-users', 'active')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Create New User</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('/admin/users/create'); ?>">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>User Type</label>
                        <select name="user_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="admin">Admin</option>
                            <option value="reseller">Reseller</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="zmdi zmdi-check"></i> Create User
                        </button>
                        <a href="<?php echo url('/admin/users'); ?>" class="btn btn-secondary">
                            <i class="zmdi zmdi-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
