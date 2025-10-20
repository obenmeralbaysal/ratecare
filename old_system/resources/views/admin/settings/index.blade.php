@extends("admin._partials.layout")


@section("content")

    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Settings</h2>
                </div>
            </div>
        </div>

        @include("common.error-dump")

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body">
                        <h2 class="card-inside-title">Widgets</h2>
                        @if($widgetsOpen == "true")
                            <a href="{{ route("admin.settings.set-status") }}"><button class="btn btn-danger">Disable All</button></a>
                        @else
                            <a href="{{ route("admin.settings.set-status") }}"><button class="btn btn-success">Activate All</button></a>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="body">
                        <div class="col-md-6 p-0">
                            <h2 class="card-inside-title">Cache</h2>
                            <a href="{{ route("admin.settings.clear-cache") }}">
                                <button class="btn btn-warning">Clear Cache</button>
                            </a>
                        </div>

                        <div class="col-md-6 m-t-20 p-0">
                            <form action="{{ route("admin.settings.caching-time") }}" method="POST">
                                @csrf
                                <input name="_method" type="hidden" value="PUT">
                                <h2 class="card-inside-title">Cache Expiration Time (minutes)</h2>
                                <div class="form-group">
                                    <input type="text" name="cache-time" class="form-control pull-left" value="{{ $cachingTime }}" placeholder="120">
                                    <button class="btn btn-info pull-left">Set</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection