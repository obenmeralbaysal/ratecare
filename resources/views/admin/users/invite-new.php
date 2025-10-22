@extends('layouts.admin-new')

@section('title', 'Invite User')

@section('menu-users', 'active')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Invite User</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('/admin/users/invite'); ?>">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
                        <small class="form-text text-muted">
                            An invitation email will be sent to this address
                        </small>
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
                        <label>Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Add a personal message..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="zmdi zmdi-email"></i> Send Invitation
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
