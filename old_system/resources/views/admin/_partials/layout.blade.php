<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    {{--<meta name="description" content="Responsive Bootstrap 4 and web Application ui kit.">--}}
    <title>RateCare | The Ultimate Dashboard for Hoteliers</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"> <!-- Favicon-->
    <link rel="stylesheet" href="{{ asset("assets/common/css/bootstrap.min.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/morrisjs/morris.css") }}" />
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css") }}"/>
    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset("assets/common/css/main.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/css/color_skins.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/custom.css") }}">
</head>
<body class="theme-black">
<!-- Page Loader -->
<div class="page-loader-wrapper">
    <div class="loader">
        <div class="m-t-30"><img src="{{ asset("assets/common/img/favicon.fw.png") }}" width="48" alt="Alpino"></div>
        <p>Please wait...</p>
    </div>
</div>

<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="h-bars"></a>
                    <a class="navbar-brand" href="{{ route("admin.dashboard") }}"><img height="50" src="{{ asset("assets/common/img/rate-care-logo.fw.png") }}" alt="Alpino"></a>
                </div>
            </li>


            <li class="float-right">
                <a href="javascript:void(0);" class="js-right-sidebar"><i class="zmdi zmdi-settings"></i></a>
                <a href="{{ route("customer.logOut") }}" class="mega-menu"><i class="zmdi zmdi-power"></i></a>
            </li>
        </ul>
    </div>
</nav>

<div class="menu-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="h-menu">
                    <li class="open active"><a href="{{ route("admin.dashboard") }}"><i class="zmdi zmdi-home"></i></a></li>
                    <li><a href="javascript:void(0)">Users</a>
                        <ul class="sub-menu">
                            <li><a href="{{ route("admin.users.index") }}">All Users</a></li>
                            <li><a href="{{ route("admin.users.create") }}">New User</a></li>
                            <li><a href="{{ route("admin.users.invite") }}">Invite User</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<section class="content home">
    @yield("content")
</section>

<script src="{{ asset("assets/common/js/bundles/libscripts.bundle.js") }}"></script> <!-- Lib Scripts Plugin Js ( jquery.v3.2.1, Bootstrap4 js) -->
<script src="{{ asset("assets/common/js/bundles/vendorscripts.bundle.js") }}"></script> <!-- slimscroll, waves Scripts Plugin Js -->

<script src="{{ asset("assets/common/js/bundles/knob.bundle.js") }}"></script> <!-- Jquery Knob-->
<script src="{{ asset("assets/common/js/bundles/sparkline.bundle.js") }}"></script> <!-- sparkline Plugin Js -->
<script src="{{ asset("assets/common/plugins/chartjs/Chart.bundle.js") }}"></script> <!-- Chart Plugins Js -->
<script src="{{ asset("assets/common/plugins/chartjs/polar_area_chart.js") }}"></script><!-- Polar Area Chart Js -->

<script src="{{ asset("assets/common/js/bundles/mainscripts.bundle.js") }}"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

@yield('scripts')

</body>
</html>
