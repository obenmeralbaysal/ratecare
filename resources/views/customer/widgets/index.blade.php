@extends("customer._partials.layout")


@section("content")
    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Widgets</h2>&nbsp;
                    <a href="{{ route("customer.widget.edit") }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left"><i class="zmdi zmdi-plus"></i> New Widget</button>
                    </a>
                </div>
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <ul class="breadcrumb float-md-right padding-0">
                        <li class="breadcrumb-item"><a href="index.html"><i class="zmdi zmdi-home"></i></a></li>
                        <li class="breadcrumb-item">Widgets</li>
                        <li class="breadcrumb-item active">All Widgets</li>
                    </ul>
                </div>
            </div>
        </div>


        @if(count($widgets) > 0)
            <div class="row clearfix">

                <div class="col-lg-4 col-md-12">
                    <div class="card project_widget">
                        <div class="body">
                            <div class="row pw_content">
                                <div class="col-12 pw_header">
                                    <h6>Magazine Design</h6>
                                    <small class="text-muted">Alpino | Last Update: 12 Dec 2017</small>
                                </div>
                                <div class="col-8 pw_meta">
                                    <span>4,870 USD</span>
                                    <small class="text-danger">17 Days Remaining</small>
                                </div>
                                <div class="col-4">
                                    <div class="sparkline m-t-10" data-type="bar" data-width="97%" data-height="26px" data-bar-Width="2" data-bar-Spacing="7" data-bar-Color="#7460ee">
                                        2,5,6,3,4,5,5,6,2,1
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card project_widget">
                        <div class="body">
                            <div class="row pw_content">
                                <div class="col-12 pw_header">
                                    <h6>Magazine Design</h6>
                                    <small class="text-muted">Alpino | Last Update: 12 Dec 2017</small>
                                </div>
                                <div class="col-8 pw_meta">
                                    <span>4,870 USD</span>
                                    <small class="text-danger">17 Days Remaining</small>
                                </div>
                                <div class="col-4">
                                    <div class="sparkline m-t-10" data-type="bar" data-width="97%" data-height="26px" data-bar-Width="2" data-bar-Spacing="7" data-bar-Color="#7460ee">
                                        2,5,6,3,4,5,5,6,2,1
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card project_widget">
                        <div class="body">
                            <div class="row pw_content">
                                <div class="col-12 pw_header">
                                    <h6>Magazine Design</h6>
                                    <small class="text-muted">Alpino | Last Update: 12 Dec 2017</small>
                                </div>
                                <div class="col-8 pw_meta">
                                    <span>4,870 USD</span>
                                    <small class="text-danger">17 Days Remaining</small>
                                </div>
                                <div class="col-4">
                                    <div class="sparkline m-t-10" data-type="bar" data-width="97%" data-height="26px" data-bar-Width="2" data-bar-Spacing="7" data-bar-Color="#7460ee">
                                        2,5,6,3,4,5,5,6,2,1
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card project_widget">
                        <div class="body">
                            <div class="row pw_content">
                                <div class="col-12 pw_header">
                                    <h6>Magazine Design</h6>
                                    <small class="text-muted">Alpino | Last Update: 12 Dec 2017</small>
                                </div>
                                <div class="col-8 pw_meta">
                                    <span>4,870 USD</span>
                                    <small class="text-danger">17 Days Remaining</small>
                                </div>
                                <div class="col-4">
                                    <div class="sparkline m-t-10" data-type="bar" data-width="97%" data-height="26px" data-bar-Width="2" data-bar-Spacing="7" data-bar-Color="#7460ee">
                                        2,5,6,3,4,5,5,6,2,1
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else

            <div class="alert alert-warning">Looks like empty ! Lets create a widget...</div>

        @endif

    </div>
@endsection