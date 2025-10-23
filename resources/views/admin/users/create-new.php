@extends('layouts.admin-new')

@section('title', 'Create New User')

@section('menu-users', 'active')

@section('content')
<div class="block-header">
    <div class="row clearfix">
        <div class="col-lg-5 col-md-5 col-sm-12">
            <h2 class="float-left">Create User</h2>
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
        <div class="card p-4">
            <div class="body">
                <form method="POST" action="<?php echo url('/admin/users/create'); ?>" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    
                    <h2 class="card-inside-title">Name Surname</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" class="form-control" name="namesurname" placeholder="John Doe" required/>
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">E-Mail</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" data-lpignore="true"
                                       placeholder="johndoe@example.com" required/>
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">Password</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="password" class="form-control" name="password" required/>
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">Password (Confirm)</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="password" class="form-control" name="password_confirmation" required/>
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">User Type</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="radio" name="userType" value="0" checked> Standard
                                <input type="radio" name="userType" value="1"> Admin
                                <input type="radio" name="userType" value="2" id="check_reseller"> Reseller
                            </div>
                        </div>
                    </div>

                    <div class="reseller-logo" style="display: none;">
                        <h2 class="card-inside-title">Reseller Logo</h2>
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input type="file" class="form-control-file" name="resellerLogo" aria-describedby="fileHelp">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">Rate Comparison Tool</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="checkbox" name="rateComparison" value="1"> Active
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary pull-right">Save</button>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Reseller logo toggle
    $(document).ready(function() {
        $('input:radio[name="userType"]').click(function() {
            if ($('#check_reseller').is(':checked'))
                $('.reseller-logo').show();
            else
                $('.reseller-logo').hide();
        });
    });
</script>
@endsection

@section('styles')
<style>
    .card-inside-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
</style>
@endsection
