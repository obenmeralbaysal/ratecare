@extends('layouts.admin-new')

@section('title', 'Invite User')

@section('menu-users', 'active')

@section('content')
<div class="block-header">
    <div class="row clearfix">
        <div class="col-lg-5 col-md-5 col-sm-12">
            <h2 class="float-left">Invite User</h2>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-12">
            <ul class="breadcrumb float-md-right padding-0">
                <li><a href="<?php echo url('/dashboard'); ?>"><i class="zmdi zmdi-home"></i></a></li>
                <li><a href="<?php echo url('/admin/users'); ?>">Users</a></li>
                <li class="active">Invite User</li>
            </ul>
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
                <form method="POST" action="<?php echo url('/admin/users/invite'); ?>" id="inviteUserForm" onsubmit="return handleInviteUser(event)">
                    <?php echo csrfField(); ?>
                    
                    <h2 class="card-inside-title">Name Surname</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" class="form-control" name="namesurname" placeholder="John Doe" required />
                            </div>
                        </div>
                    </div>

                    <h2 class="card-inside-title">E-Mail</h2>
                    <div class="row clearfix">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="johndoe@example.com" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="zmdi zmdi-mail-send"></i> Send Invitation
                    </button>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function handleInviteUser(event) {
    event.preventDefault();
    
    const form = document.getElementById('inviteUserForm');
    const formData = new FormData(form);
    
    $.ajax({
        url: $(form).attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Invitation sent successfully!');
                form.reset();
                setTimeout(function() {
                    window.location.href = '<?php echo url("/admin/users"); ?>';
                }, 1500);
            } else {
                toastr.error(response.message || 'Failed to send invitation');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                Object.values(response.errors).forEach(function(error) {
                    toastr.error(error[0]);
                });
            } else if (response && response.message) {
                toastr.error(response.message);
            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });
    
    return false;
}
</script>
@endsection

@section('styles')
<style>
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .breadcrumb li {
        display: inline;
        font-size: 14px;
    }
    .breadcrumb li + li:before {
        content: "/";
        padding: 0 5px;
        color: #ccc;
    }
    .breadcrumb li a {
        color: #007bff;
        text-decoration: none;
    }
    .breadcrumb li.active {
        color: #6c757d;
    }
    .card-inside-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    .padding-0 {
        padding: 0;
    }
</style>
@endsection
