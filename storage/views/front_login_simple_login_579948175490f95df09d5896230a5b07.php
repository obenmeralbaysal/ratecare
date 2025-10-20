<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RateCare | The Ultimate Dashboard for Hoteliers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 32px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 16px;
            color: #6b7280;
            font-weight: 400;
            line-height: 1.5;
        }
        
        .description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        .website-link {
            display: inline-block;
            margin-bottom: 32px;
        }
        
        .website-link a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .website-link a:hover {
            text-decoration: underline;
        }
        
        .login-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 24px;
        }
        
        .login-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .login-button {
            width: 100%;
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease-in-out;
            margin-bottom: 16px;
        }
        
        .login-button:hover {
            background-color: #2563eb;
        }
        
        .login-button:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }
        
        .forgot-password {
            text-align: center;
        }
        
        .forgot-password a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .remember-me label {
            font-size: 14px;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">RateCare</h1>
            <p class="subtitle">The Ultimate Dashboard for Hoteliers</p>
        </div>
        
        <p class="description">
            In a rapidly changing and highly demanding business environment, the need to build and maintain strong synergies becomes a necessity.
        </p>
        
        <div class="website-link">
            <a href="https://ratecare.co" target="_blank">Visit our website</a>
        </div>
        
        <div class="login-section">
            <h2 class="login-title">Log in</h2>
            
            <?php echo flash('error') ? '<div class="alert alert-error">' . flash('error') . '</div>' : ''; ?>
            <?php echo flash('success') ? '<div class="alert alert-success">' . flash('success') . '</div>' : ''; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars(url('/login') ?? ""); ?>" id="loginForm">
                <?php echo csrfField(); ?>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars(old('email') ?? ""); ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="login-button">Log in</button>
            </form>
            
            <div class="forgot-password">
                <a href="<?php echo htmlspecialchars(url('/forgot-password') ?? ""); ?>">Forgot Password?</a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Logging in...';
            submitBtn.disabled = true;
        });
        
        document.getElementById('email').focus();
    </script>
</body>
</html>
