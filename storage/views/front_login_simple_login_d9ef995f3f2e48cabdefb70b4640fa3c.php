<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="description" content="Hoteldigilab">

    <title>RateCare | The Ultimate Dashboard for Hoteliers</title>
    <!-- Favicon-->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars(asset('assets/images/logo-goz.png') ?? ""); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Muli', sans-serif;
            background: #2c2c2c;
            margin: 0;
            padding: 0;
        }
        
        .authentication {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: #2c2c2c;
        }
        
        .content-center {
            display: flex;
            align-items: center;
            min-height: 100vh;
        }
        
        .company_detail {
            padding: 40px;
            color: #fff;
        }
        
        .company_detail img {
            max-width: 200px;
            margin-bottom: 30px;
        }
        
        .company_detail h3 {
            color: #fff;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .company_detail p {
            color: rgba(255,255,255,0.8);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .company_detail .footer {
            margin-top: 40px;
        }
        
        .company_detail .footer hr {
            border-color: rgba(255,255,255,0.2);
            margin: 20px 0;
        }
        
        .company_detail .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .company_detail .footer ul li {
            display: inline-block;
            margin-right: 20px;
        }
        
        .company_detail .footer ul li a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
        }
        
        .company_detail .footer ul li a:hover {
            color: #fff;
        }
        
        .card-plain {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 20px;
        }
        
        .card-plain .header h5 {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-group .form-control {
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 15px 50px 15px 20px;
            font-size: 14px;
            height: auto;
            background: #f8f9fa;
            border-right: none;
        }
        
        .input-group .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
            background: #fff;
        }
        
        .input-group-addon {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-left: none;
            border-radius: 0 25px 25px 0;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group .form-control:focus + .input-group-addon {
            background: #fff;
            border-color: #007bff;
        }
        
        .input-group-addon i {
            color: #666;
            font-size: 16px;
        }
        
        .form-control.show-tick {
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 15px 20px;
            font-size: 14px;
            height: auto;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .link {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        
        .link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .footer {
            margin-top: 30px;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .company_detail {
                text-align: center;
                padding: 20px;
            }
            
            .card-plain {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body class="theme-black">
<div class="authentication">
    <div class="container">
        <div class="col-md-12 content-center">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="company_detail">
                        <img src="<?php echo htmlspecialchars(asset('assets/common/img/rate-care-logo.fw.png') ?? ""); ?>" alt="RateCare Logo">
                        <h3>The Ultimate Dashboard for Hoteliers</h3>
                        <p>In a rapidly changing and highly demanding business environment, the need to build and maintain strong synergies becomes a necessity.</p>
                        <div class="footer">
                            <hr>
                            <ul>
                                <li><a href="https://ratecare.co" target="_blank">Visit our website</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 offset-lg-1">
                    <div class="card-plain">
                        <div class="header">
                            <h5>Log in</h5>
                        </div>
                        
                        <?php echo flash('error') ? '<div class="alert alert-danger">' . flash('error') . '</div>' : ''; ?>
                        <?php echo flash('success') ? '<div class="alert alert-success">' . flash('success') . '</div>' : ''; ?>
                        
                        <form class="form" method="POST" action="<?php echo htmlspecialchars(url('/login') ?? ""); ?>" id="loginForm">
                            <?php echo csrfField(); ?>
                            
                            <div class="input-group">
                                <input type="email" class="form-control" name="email" placeholder="E-mail" 
                                       value="<?php echo htmlspecialchars(old('email') ?? ""); ?>" required autofocus>
                                <span class="input-group-addon"><i class="zmdi zmdi-account-circle"></i></span>
                            </div>
                            
                            <div class="input-group">
                                <input type="password" placeholder="Password" name="password" class="form-control" required>
                                <span class="input-group-addon"><i class="zmdi zmdi-lock"></i></span>
                            </div>
                            
                            <select class="form-control show-tick" name="language">
                                <option value="">-- Language --</option>
                                <option value="tr">Turkish</option>
                                <option value="en" selected>English</option>
                            </select>
                            
                            <div class="footer">
                                <button type="submit" class="btn btn-primary btn-round btn-block">SIGN IN</button>
                            </div>
                        </form>
                        
                        <a href="<?php echo htmlspecialchars(url('/forgot-password') ?? ""); ?>" class="link">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const email = document.querySelector('input[name="email"]').value;
        const password = document.querySelector('input[name="password"]').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="zmdi zmdi-refresh zmdi-hc-spin"></i> SIGNING IN...';
        submitBtn.disabled = true;
    });
    
    // Auto-focus on email field
    document.querySelector('input[name="email"]').focus();
</script>

</body>
</html>
