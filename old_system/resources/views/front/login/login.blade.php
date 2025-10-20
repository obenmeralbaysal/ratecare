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
                        <img src="assets/common/img/rate-care-logo.fw.png" alt="">
                        <h3>The Ultimate  Dashboard for Hoteliers </h3>
                        <p>In a rapidly changing and highly demanding business environment, the need to build and maintain strong synergies becomes a necessity.</p>
                        <div class="footer">
                            {{--<ul  class="social_link list-unstyled">--}}
                                {{--<li><a href="https://thememakker.com" title="ThemeMakker"><i class="zmdi zmdi-globe"></i></a></li>--}}
                                {{--<li><a href="https://themeforest.net/user/thememakker" title="Themeforest"><i class="zmdi zmdi-shield-check"></i></a></li>--}}
                                {{--<li><a href="https://www.linkedin.com/company/thememakker/" title="LinkedIn"><i class="zmdi zmdi-linkedin"></i></a></li>--}}
                                {{--<li><a href="https://www.facebook.com/thememakkerteam" title="Facebook"><i class="zmdi zmdi-facebook"></i></a></li>--}}
                                {{--<li><a href="http://twitter.com/thememakker" title="Twitter"><i class="zmdi zmdi-twitter"></i></a></li>--}}
                                {{--<li><a href="http://plus.google.com/+thememakker" title="Google plus"><i class="zmdi zmdi-google-plus"></i></a></li>--}}
                                {{--<li><a href="https://www.behance.net/thememakker" title="Behance"><i class="zmdi zmdi-behance"></i></a></li>--}}
                            {{--</ul>--}}
                            <hr>
                            <ul>
                                <li><a href="https://ratecare.co" target="_blank">Visit our website</a></li>
                                {{--<li><a href="javascript:void(0);">FAQ</a></li>--}}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12 offset-lg-1">
                    <div class="card-plain">
                        <div class="header">
                            <h5>Log in</h5>
                        </div>
                        <form class="form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="email" placeholder="E-mail">
                                <span class="input-group-addon"><i class="zmdi zmdi-account-circle"></i></span>
                            </div>
                            <div class="input-group">
                                <input type="password" placeholder="Password" name="password" class="form-control" />
                                <span class="input-group-addon"><i class="zmdi zmdi-lock"></i></span>
                            </div>
                            <select class="form-control show-tick">
                                <option value="">-- Language --</option>
                                <option value="10">Turkish</option>
                                <option value="20">English</option>
                            </select>
                            <div class="footer">
                                <button type="submit" class="btn btn-primary btn-round btn-block">SIGN IN</button>
                            </div>
                        </form>
                        <a href="{{ route('front.forgot-password') }}" class="link">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Jquery Core Js -->

<script>var mainWidget = true; var exitWidget = true;</script>
<script src="assets/common/js/bundles/libscripts.bundle.js"></script>
<script src="assets/common/js/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js -->

<script src="assets/common/js/bundles/mainscripts.bundle.js"></script><!-- Custom Js -->


</body>
</html>
