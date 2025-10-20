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
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/morrisjs/morris.css") }}"/>
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css") }}"/>
    <!-- Custom Css -->
    <link rel="stylesheet" href="{{ asset("assets/common/css/main.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/css/color_skins.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/custom.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/fullcalendar/fullcalendar.min.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/multi-select/css/multi-select.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/bootstrap-select/css/bootstrap-select.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/common/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css") }}">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Abel|Cairo|Dosis|Open+Sans+Condensed:300|PT+Sans+Narrow|Rajdhani|Roboto|Roboto+Condensed|Saira+Semi+Condensed|Teko&display=swap">
</head>
<body class="theme-black">
<!-- Page Loader -->
<div class="page-loader-wrapper">
    <div class="loader">
        <div class="m-t-30"><img src="{{ asset("assets/common/img/favicon.fw.png") }}" width="48" alt="HOTELDIGILAB"></div>
        <p>Please wait...</p>
    </div>
</div>

<nav class="navbar">
    <div class="container">
        <ul class="nav navbar-nav">
            <li>
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="h-bars"></a>
                    <a class="navbar-brand" href="{{ route("customer.widget.edit") }}">
                        <img height="60" alt="{{ user()->namesurname }}" height="50" src="{{ asset("assets/common/img/rate-care-logo.fw.png") }}">
                    </a>
                </div>
            </li>


            <li class="float-right">

                @if(getImpersonatingSuperUser())
                    @if(isReseller(getImpersonatingSuperUser()))
                        <a href="{{ route("reseller.dashboard") }}" class="mega-menu"><i class="zmdi zmdi-accounts-list-alt"></i></a>
                    @elseif(getImpersonatingSuperUser()->is_admin)
                        <a href="{{ route("admin.dashboard") }}" class="mega-menu"><i class="zmdi zmdi-accounts-list-alt"></i></a>
                    @endif
                @endif

                <a href="#" target="_blank" class="js-right-sidebar"><i class="zmdi zmdi-account"></i> {{ user()->namesurname }}</a>
                <a href="{{ route("customer.hotels.edit") }}" class="js-right-sidebar"><i class="zmdi zmdi-settings"></i></a>


                <a href="{{ route("customer.logOut") }}" class="mega-menu"><i class="zmdi zmdi-power"></i></a>

                @if(user()->is_admin)
                    <a href="{{ route("admin.dashboard") }}" class="mega-menu"><i class="zmdi zmdi-mail-send"></i></a>
                @endif

                @if(isReseller())
                    <a href="{{ route("reseller.dashboard") }}" class="mega-menu"><i class="zmdi zmdi-mail-send"></i></a>
                @endif
            </li>
        </ul>
    </div>
</nav>

<div class="menu-container">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="h-menu">
                    <li class="open active"><a href="{{ route("customer.widget.edit") }}"><i class="zmdi zmdi-home"></i></a></li>
                    <li><a href="{{ route("customer.widget.edit") }}">Widgets</a>
                        {{--<ul class="sub-menu">--}}
                        {{--<li><a href="{{ route("customer.widget.index") }}">All Widgets</a></li>--}}
                        {{--<li><a href="{{ route("customer.widget.create") }}">New Widget</a></li>--}}
                        {{--</ul>--}}
                    </li>

                    @if(user()->is_rate_comparison_active)<li><a href="{{ route("customer.rate.index") }}">Rates</a></li>@endif
                    <li><a href="{{ route("customer.statistics.index") }}">Statistics</a></li>
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
<script src="{{ asset("assets/common/plugins/chartjs/Chart.js") }}"></script> <!-- Chart Plugins Js -->
<script src="{{ asset("assets/common/plugins/chartjs/Chart.bundle.js") }}"></script> <!-- Chart Plugins Js -->
<script src="{{ asset("assets/common/plugins/chartjs/polar_area_chart.js") }}"></script><!-- Polar Area Chart Js -->
<script src="{{ asset("assets/common/plugins/momentjs/moment.js") }}"></script><!-- Bootstrap Material Datetimepicker Js -->
<script src="{{ asset("assets/common/plugins/multi-select/js/jquery.multi-select.js") }}"></script><!-- jQuery Multiselect -->
<script src="{{ asset("assets/common/plugins/fullcalendar/fullcalendarscripts.bundle.js") }}"></script><!-- Calendar Js -->
<script src="{{ asset("assets/common/plugins/quicksearch/jquery.quicksearch.js") }}"></script><!-- Quicksearch Js -->
<script src="{{ asset("assets/common/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js") }}"></script>
<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>

<!-- Bootstrap Material Datetimepicker Js -->


<script src="{{ asset("assets/common/js/bundles/mainscripts.bundle.js") }}"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
</script>
@yield("scripts")
</body>
</html>
