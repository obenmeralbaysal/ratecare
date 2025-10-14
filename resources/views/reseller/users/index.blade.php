@extends("reseller._partials.layout")


@section("content")


    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">Users</h2>&nbsp;
                    <a href="{{ route("reseller.users.create") }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left"><i class="zmdi zmdi-plus"></i> New User</button>
                    </a>
                    <a href="{{ route("reseller.users.invite") }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left"><i class="zmdi zmdi-mail-send"></i> Invite</button>
                    </a>
                    <a href="{{ route("reseller.users.exportExcel") }}">
                        <button class="new-widget-btn btn btn-primary btn-sm float-left"><i class="zmdi zmdi-download"></i> Export Excel</button>
                    </a>
                </div>
            </div>
        </div>


        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">

                <div class="card">
                    <div class="body">

                        <form method="GET" action="" accept-charset="UTF-8"
                              id="filter-form" class="input-group">

                            <input class="form-control filter-text" name="filters[q]" value="{{ array_get(request()->get('filters'), 'q') }}"
                                   placeholder="Enter text to search by name">
                            <div class="input-group-append">
                                <button type="submit" name="filter" value="1"
                                        class="btn btn-primary btn-round waves-effect filter-submit-btn">
                                    SEARCH
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="card">
                    <div class="body table-responsive">
                        <table class="table m-b-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>NAME</th>
                                <th>HOTEL</th>
                                <th>E-MAIL</th>
                                <th>JOINED AT</th>
                                <th>ACTIONS</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <th scope="row">{{ $user->id }}</th>
                                    <td class="td-namesurname"><a
                                                href="{{ route("reseller.users.switchUser", $user->id) }}">{{ $user->namesurname }}</a>
                                    </td>
                                    <td>
                                        @if($user->hotels()->first())
                                            <a href="{{ $user->hotels()->first()->web_url }}" target="_blank">
                                                {{ $user->hotels()->first()->name }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-nowrap">{{ formatDate($user->created_at) }}</td>
                                    <td class="text-nowrap text-right">
                                        <a href="{{ route("reseller.users.switchUser",
                                            ['id' => $user->id, 'redirect-after' => route('customer.hotels.edit')]) }}"
                                           title="EDIT PROPERTY">
                                            <button class="btn btn-warning btn-sm btn-primary"><i class="zmdi zmdi-city-alt"></i></button>
                                        </a>

                                        <a href="{{ route("reseller.users.switchUser",
                                            ['id' => $user->id, 'redirect-after' => route('customer.widget.edit')]) }}"
                                           title="EDIT WIDGET">
                                            <button class="btn btn-warning btn-sm btn-dark"><i class="zmdi zmdi-layers"></i></button>
                                        </a>

                                        <a href="{{ route("reseller.users.edit", $user->id) }}">
                                            <button class="btn btn-warning btn-sm btn-blue"><i class="zmdi zmdi-edit"></i></button>
                                        </a>
                                        <a href="{{ route("reseller.users.delete", $user->id) }}" onclick="return confirm('Are you sure?')">
                                            <span class="btn btn-danger btn-sm btn-red" id="btnDelete"><i class="zmdi zmdi-delete"></i></span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {!! $users->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
