@extends("admin._partials.layout")

@section("content")
    <div class="container">
        <div class="row clearfix">
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("admin.users.index") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-folder-person"></i>
                            <h6 class="m-t-20">ALL USERS</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("admin.users.create") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-plus"></i>
                            <h6 class="m-t-20">NEW USER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("admin.users.invite") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-email"></i>
                            <h6 class="m-t-20">INVITE USER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ url("/log-viewer") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-alert-polygon"></i>
                            <h6 class="m-t-20">LOG VIEWER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("admin.settings.index") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-settings"></i>
                            <h6 class="m-t-20">SETTINGS</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection