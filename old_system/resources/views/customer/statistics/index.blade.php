@extends('customer._partials.layout')

@section('content')
    <div class="container">
        <div class="row clearfix">


            <div class="card">
                <div class="row profile_state">
                    <div class="col-lg-3 col-md-3 col-6">
                        <div class="body">
                            <i class="zmdi zmdi-eye zmdi-hc-2x col-blue"></i>
                            <h3 class="m-b-0">{{ $statisticsToday }} Today</h3>
                            <h6>{{ $statisticsYesterday }} <span class="text-tn">Yesterday</span></h6>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-6">
                        <div class="body">
                            <i class="zmdi zmdi-eye zmdi-hc-2x col-green"></i>
                            <h3 class="m-b-0">{{ $statisticsThisWeek }} This Week</h3>
                            <h6>{{ $statisticsLastWeek }} <span class="text-tn">Last Week</span></h6>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-6">
                        <div class="body">
                            <i class="zmdi zmdi-eye zmdi-hc-2x col-red"></i>
                            <h3 class="m-b-0">{{ $statisticsThisMonth }} This Month</h3>
                            <h6>{{ $statisticsLastMonth }} <span class="text-tn">Last Month</span></h6>

                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3 col-6">
                        <div class="body">
                            <i class="zmdi zmdi-eye zmdi-hc-2x col-black"></i>
                            <h3 class="m-b-0">{{ $statisticsThisYear }} This Year</h3>
                            <h6>{{ $statisticsLastYear }} <span class="text-tn">Last Year</span></h6>

                        </div>
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="header">
                    <h2> <strong>Filter</strong>  </h2>

                </div>
                <form method="get" action="{{ route("customer.statistics.index") }}">
                    <input type="hidden" name="filter" value="true">

                    <div class="body">
                    <div class="row clearfix">
                        <div class="col-md-4">
                            <h2 class="card-inside-title">Date</h2>
                            <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="zmdi zmdi-calendar"></i>
                                    </span>
                                <input type="text" class="form-control datetimepicker" name="beginDate"  id="beginDate" placeholder="From" @if($beginDate != "") value="{{ $beginDate }}" @endif>
                                <input type="text" class="form-control datetimepicker" name="endDate" id="endDate" placeholder="To" @if($endDate != "") value="{{ $endDate }}" @endif>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <h2 class="card-inside-title">Country</h2>
                            <select class="form-control show-tick" name="country">
                                <option value=""> -- Country --</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->name }}" @if($country == $c->name) selected @endif>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <h2 class="card-inside-title">Type</h2>
                            <select class="form-control show-tick" name="type">
                                <option value=""> -- Type --</option>
                                <option value="pageLoad" @if($type == "pageLoad") selected @endif>Page Load</option>
                                <option value="dateSearch" @if($type == "dateSearch") selected @endif>Date Search</option>
                                <option value="currencyChange" @if($type == "currencyChange") selected @endif>Currency Change</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <h2 class="card-inside-title">Arrival</h2>
                            <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="zmdi zmdi-calendar"></i>
                                    </span>
                                <input type="text" name="arrival" class="form-control datetimepicker" placeholder="Choose a Date" @if($arrival != "") value="{{ $arrival }}" @endif>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <h2 class="card-inside-title">Departure</h2>
                            <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="zmdi zmdi-calendar"></i>
                                    </span>
                                <input type="text" name="departure" class="form-control datetimepicker" placeholder="Choose a Date" @if($departure != "") value="{{ $departure }}" @endif>
                            </div>
                        </div>
                        <div class="col-md-2 statistic-filter-btn">
                            <button class="btn btn-primary" type="submit">Generate</button>
                        </div>
                    </div>
                </div>
                </form>
            </div>

            <div class="card">



                <div class="body table-responsive">
                    <div><h6>{{ count($statistics) }} record(s) listed</h6></div>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>DATE & TIME</th>
                            <th>IP</th>
                            <th>COUNTRY</th>
                            <th>TYPE</th>
                            <th>ARRIVAL</th>
                            <th>DEPARTURE</th>
                            <th>DAY(S)</th>

                            <th>SUCCESS</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($statistics as $statistic)
                            <tr>
                                <th scope="row">{{ date("d.m.Y h:i:s", strtotime($statistic->created_at)) }}</th>
                                <td>{{ $statistic->ip }}</td>
                                <td>@if($statistic->country)<img src="{{ asset("assets/common/img/flags") . "/" . str_replace(" ", "-", $statistic->country) . ".png" }}"/> {{ $statistic->country }}@else - @endif</td>
                                <td>@if($statistic->type == "dateSearch")<span class="badge badge-success">DATE SEARCH</span>@elseif($statistic->type == "currencyChange")<span class="badge badge-danger">Currency Change</span>@elseif($statistic->type == "pageLoad")<span class="badge badge-default">Page Load</span>@endif</td>
                                <td>{{ date("d.m.Y", strtotime($statistic->arrival)) }}</td>
                                <td>{{ date("d.m.Y", strtotime($statistic->departure)) }}</td>
                                <td>{{ abs(strtotime($statistic->departure) - strtotime($statistic->arrival)) /60/60/24 }}</td>
                                <td>@if($statistic->result == true) <i class="zmdi zmdi-check zmdi-hc-2x mdc-text-green col-green"></i> @else <i class="zmdi zmdi-close zmdi-hc-2x col-red"></i> @endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section("scripts")

<script>
    $('.datetimepicker').bootstrapMaterialDatePicker({
        format: 'DD-MM-YYYY',
        clearButton: true,
        weekStart: 1,
        time: false,
        switchOnClick: true,
    });

    $('document').ready(function(){
        $('#beginDate').on('dateSelected', function(e, date)
        {
            $('#endDate').bootstrapMaterialDatePicker('setMinDate', date);

        });
    });


</script>

@endsection