<?php $this->layout = "layouts.app"; ?>

<?php $this->startSection("content"); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login to Hotel DigiLab
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars(url('/login') ?? ""); ?>">
                        <?php echo csrfField(); ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars(old('email') ?? ""); ?>" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </button>
                        </div>
                    </form>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="<?php echo htmlspecialchars(url('/forgot-password') ?? ""); ?>" class="text-decoration-none">
                            <i class="fas fa-question-circle me-1"></i>
                            Forgot your password?
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Don't have an account? Contact your administrator for an invitation.
                </small>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection("scripts"); ?>
<script>
$(document).ready(function() {
    // Focus on email field
    $('#email').focus();
    
    // Form validation
    $('form').on('submit', function(e) {
        var email = $('#email').val();
        var password = $('#password').val();
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
        }
    });
});
</script>
<?php $this->endSection(); ?>
