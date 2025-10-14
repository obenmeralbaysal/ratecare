<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="description" content="Hoteldigilab">

    <title>RateCare | The Ultimate Dashboard for Hoteliers</title>
    <!-- Favicon-->
    <link rel="icon" type="image/png" href="assets/images/logo-goz.png">
    <link rel="stylesheet" href="assets/common/css/bootstrap.min.css">

    <!-- Custom Css -->
    <link rel="stylesheet" href="assets/common/plugins/bootstrap-select/css/bootstrap-select.css"  />
    <link rel="stylesheet" href="assets/common/css/main.css">
    <link rel="stylesheet" href="assets/common/css/color_skins.css">

</head>
<body class="theme-black">
<div class="authentication">
    @include("common.error-dump")
    <div class="container">
        <div class="col-md-12 content-center">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="company_detail">
                        <img src="assets/common/img/logo.png" alt="">
                        <h3>The ultimate <strong>Bootstrap 4</strong> Admin Dashboard</h3>
                        <p>Alpino is fully based on HTML5 + CSS3 Standards. Is fully responsive and clean on every device and every browser</p>
                        <div class="footer">
                            <ul  class="social_link list-unstyled">
                                <li><a href="https://thememakker.com" title="ThemeMakker"><i class="zmdi zmdi-globe"></i></a></li>
                                <li><a href="https://themeforest.net/user/thememakker" title="Themeforest"><i class="zmdi zmdi-shield-check"></i></a></li>
                                <li><a href="https://www.linkedin.com/company/thememakker/" title="LinkedIn"><i class="zmdi zmdi-linkedin"></i></a></li>
                                <li><a href="https://www.facebook.com/thememakkerteam" title="Facebook"><i class="zmdi zmdi-facebook"></i></a></li>
                                <li><a href="http://twitter.com/thememakker" title="Twitter"><i class="zmdi zmdi-twitter"></i></a></li>
                                <li><a href="http://plus.google.com/+thememakker" title="Google plus"><i class="zmdi zmdi-google-plus"></i></a></li>
                                <li><a href="https://www.behance.net/thememakker" title="Behance"><i class="zmdi zmdi-behance"></i></a></li>
                            </ul>
                            <hr>
                            <ul>
                                <li><a href="http://thememakker.com/contact/" target="_blank">Contact Us</a></li>
                                <li><a href="http://thememakker.com/about/" target="_blank">About Us</a></li>
                                <li><a href="http://thememakker.com/services/" target="_blank">Services</a></li>
                                <li><a href="javascript:void(0);">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 offset-lg-1">
                    <div class="card-plain">
                        <div class="header">
                            <h5>Forgot Password ?</h5>
                        </div>
                        <form class="form" method="POST" action="{{ route('front.post-forgot-password') }}">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="email" placeholder="E-mail">
                                <span class="input-group-addon"><i class="zmdi zmdi-account-circle"></i></span>
                            </div>
                            <div class="footer">
                                <button type="submit" class="btn btn-primary btn-round btn-block">Rescue me !</button>
                            </div>
                        </form>
                        <a href="{{ route('front.login') }}" class="link">Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Jquery Core Js -->
<script src="assets/common/js/bundles/libscripts.bundle.js"></script>
<script src="assets/common/js/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js -->

<script src="assets/common/js/bundles/mainscripts.bundle.js"></script><!-- Custom Js -->
</body>
</html>
