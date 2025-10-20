@extends("admin._partials.layout")


@section("content")
    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Invite User</h2>
                </div>
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <ul class="breadcrumb float-md-right padding-0">
                        <li class="breadcrumb-item"><a href="index.html"><i class="zmdi zmdi-home"></i></a></li>
                        <li class="breadcrumb-item">Users</li>
                        <li class="breadcrumb-item active">Create User</li>
                    </ul>
                </div>
            </div>
        </div>

        @include("common.error-dump")

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body table-responsive">
                        <form method="POST" action="{{ route("admin.users.postInvite") }}">
                            @csrf
                            <h2 class="card-inside-title">Name Surname</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="namesurname" placeholder="John Doe" />
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">E-Mail</h2>
                            <div class="row clearfix">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="email" placeholder="johndoe@example.com" />
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary pull-right">Invite</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection