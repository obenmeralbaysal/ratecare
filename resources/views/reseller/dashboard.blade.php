@extends("reseller._partials.layout")

@section("content")
    <div class="container">
        <div class="row clearfix">
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("reseller.users.index") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-folder-person"></i>
                            {{--<input type="text" class="knob" data-skin="tron" value="{{ $userCount }}" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#26dad2" readonly>--}}
                            <h6 class="m-t-20">ALL USERS</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("reseller.users.create") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-plus"></i>
                            {{--<input type="text" class="knob dial2" value="{{ $widgetCount }}" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#7b69ec" readonly>--}}
                            <h6 class="m-t-20">NEW USER</h6>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center">
                <div class="card tasks_report">
                    <a href="{{ route("reseller.users.invite") }}">
                        <div class="body">
                            <i class="zmdi zmdi-hc-5x zmdi-email"></i>
                            {{--<input type="text" class="knob dial3" value="{{ $hotelCount }}" data-width="90" data-height="90" data-thickness="0.1" data-fgColor="#f9bd53" readonly>--}}
                            <h6 class="m-t-20">INVITE USER</h6>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection