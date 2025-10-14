@extends("admin._partials.layout")


@section("content")
    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Create User</h2>
                </div>
            </div>
        </div>

        @include("common.error-dump")

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body table-responsive">
                        @if($editing)
                            <form method="POST" action="{{ route("admin.users.update", ['id' => $user->id]) }}" enctype="multipart/form-data">
                                <input type="hidden" name="_method" value="PUT"/>
                                @else
                                    <form method="POST" action="{{ route("admin.users.store") }}" enctype="multipart/form-data">
                                        @endif
                                        @csrf
                                        <h2 class="card-inside-title">Name Surname</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="namesurname" placeholder="John Doe"
                                                           value="{{ $user->namesurname }}"/>
                                                </div>
                                            </div>
                                        </div>

                                        <h2 class="card-inside-title">E-Mail</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="email" data-lpignore="true"
                                                           placeholder="johndoe@example.com" value="{{ $user->email }}"/>
                                                </div>
                                            </div>
                                        </div>

                                        <h2 class="card-inside-title">Password</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="password" class="form-control" name="password"/>
                                                </div>
                                            </div>
                                        </div>


                                        <h2 class="card-inside-title">Password (Confirm)</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="password" class="form-control" name="password-confirm"/>
                                                </div>
                                            </div>
                                        </div>

                                        {{--<h2 class="card-inside-title">Hotel Limit</h2>--}}
                                        {{--<div class="row clearfix">--}}
                                        {{--<div class="col-sm-12">--}}
                                        {{--<div class="form-group">--}}
                                        {{--<input type="text" class="form-control" name="email" placeholder="johndoe@example.com"/>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}

                                        <h2 class="card-inside-title">User Type</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="radio" name="userType" value="0"> Standard
                                                    <input type="radio" name="userType" value="1"> Admin
                                                    <input type="radio" name="userType" value="2" id="check_reseller"> Reseller
                                                </div>
                                            </div>
                                        </div>

                                        <div class="reseller-logo" style="display: none;">
                                            <h2 class="card-inside-title">Reseller Logo</h2>
                                            <div class="row clearfix">
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <input type="file" class="form-control-file" name="resellerLogo" aria-describedby="fileHelp">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h2 class="card-inside-title">Rate Comparison Tool</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="checkbox" name="rateComparison" @if($user->is_rate_comparison_active) checked @endif value="true"> Active
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary pull-right">Save</button>
                                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('document').ready(function() {
            $('input:radio[name=\'userType\']').click(function() {
                if ($('#check_reseller').is(':checked'))
                    $('.reseller-logo').show()
                else
                    $('.reseller-logo').hide()
            })
        })
    </script>
@endsection
