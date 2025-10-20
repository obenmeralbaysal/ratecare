@extends("reseller._partials.layout")


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
                            <form method="POST" action="{{ route("reseller.users.update", ['id' => $user->id]) }}">
                                <input type="hidden" name="_method" value="PUT" />
                                @else
                                    <form method="POST" action="{{ route("reseller.users.store") }}">
                                @endif
                                        @csrf
                                        <h2 class="card-inside-title">Name Surname</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="namesurname" placeholder="John Doe" value="{{ $user->namesurname }}"/>
                                                </div>
                                            </div>
                                        </div>

                                        <h2 class="card-inside-title">E-Mail</h2>
                                        <div class="row clearfix">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="email" placeholder="johndoe@example.com" value="{{ $user->email }}"/>
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

                                        <button type="submit" class="btn btn-primary pull-right">Save</button>
                                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection