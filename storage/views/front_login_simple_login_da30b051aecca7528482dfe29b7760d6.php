<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'RateCare - Login' ?? ""); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 420px;
            margin: 0 auto;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        .login-body {
            padding: 2.5rem;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-floating > .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem 0.75rem;
            height: auto;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-floating > .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.1);
        }
        .form-floating > label {
            color: #6b7280;
            font-weight: 500;
        }
        .btn-login {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }
        .remember-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .forgot-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .forgot-link:hover {
            color: #3730a3;
        }
        .divider {
            margin: 2rem 0;
            text-align: center;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .footer-text {
            text-align: center;
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 1.5rem;
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1.5rem;
        }
        .alert-danger {
            background-color: #fef2f2;
            color: #dc2626;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #16a34a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="login-header">
                    <div class="logo">
                        <i class="fas fa-chart-line me-2"></i>
                        RateCare
                    </div>
                    <div class="subtitle">The Ultimate Dashboard for Hoteliers</div>
                </div>
                
                <div class="login-body">
                    <?php echo flash('error') ? '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' . flash('error') . '</div>' : ''; ?>
                    <?php echo flash('success') ? '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' . flash('success') . '</div>' : ''; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars(url('/login') ?? ""); ?>" id="loginForm">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="name@example.com" value="<?php echo htmlspecialchars(old('email') ?? ""); ?>" required autofocus>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                        </div>
                        
                        <div class="remember-check">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="<?php echo htmlspecialchars(url('/forgot-password') ?? ""); ?>" class="forgot-link">
                                Forgot Password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Sign In
                        </button>
                    </form>
                    
                    <div class="divider">
                        <span>Need help?</span>
                    </div>
                    
                    <div class="footer-text">
                        Don't have an account? Contact your administrator for an invitation.<br>
                        <small class="text-muted mt-2 d-block">© 2025 RateCare. All rights reserved.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and UX improvements
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
        
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
