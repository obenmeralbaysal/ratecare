@extends("customer._partials.layout")

@section("content")
    <div class="container">
        <div class="block-header">
            <div class="row clearfix mb-3">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2 class="float-left">First Step > Property Setup</h2>
                </div>
                <div class="col-lg-7 col-md-7 col-sm-12">
                    <ul class="breadcrumb float-md-right padding-0">
                        <li class="breadcrumb-item"><a href="index.html"><i class="zmdi zmdi-home"></i></a></li>
                        @if($editing)
                            <li class="breadcrumb-item">Properties</li>
                            <li class="breadcrumb-item active">Setup</li>
                        @else
                            <li class="breadcrumb-item">Properties</li>
                            <li class="breadcrumb-item active">Setup</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        @include("common.error-dump")

        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card">
                    <div class="body table-responsive">
                        <form method="POST"
                              action="{{ $editing ? route("customer.hotels.update") : route("customer.hotels.store") }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Property Name</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="name"
                                                       placeholder="Example Hotel"
                                                       value="{{ $hotel->name }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <h2 class="card-inside-title">Website Url</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="web_url"
                                                       placeholder="example.com"
                                                       value="{{ $hotel->web_url }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h2 class="card-inside-title">Opening Language</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <select class="col-md-12 p-0" name="opening_language">
                                                    <option value="auto"
                                                            @if($hotel->opening_language == "auto") selected @endif>Auto
                                                    </option>
                                                    <option value="native"
                                                            @if($hotel->opening_language == "native") selected @endif>
                                                        Native
                                                    </option>
                                                    <option value="english"
                                                            @if($hotel->opening_language == "english") selected @endif>
                                                        English
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                            <input type="radio" id="sabeeapp" value="sabeeapp" name="default_ibe" {{ $hotel->default_ibe == "sabeeapp" ? "checked" : "" }}>
                                            <label for="sabeeapp">SabeeApp</label>
                                            <input type="radio" id="reseliva" value="reseliva" name="default_ibe" {{ $hotel->default_ibe == "reseliva" ? "checked" : "" }}>
                                            <label for="reseliva">Reseliva</label>
                                            <input type="radio" id="hotelrunner" value="hotelrunner" name="default_ibe" {{ $hotel->default_ibe == "hotelrunner" ? "checked" : "" }}>
                                            <label for="hotelrunner">HotelRunner</label>
                                    </div>
                                </div>
                            </div>

                            <div id="sabee-input-conatiner"
                                 class="row">
                                <div class="col-md-6">
                                    <h2 class="card-inside-title">SabeeApp Hotel ID (if any)</h2>
                                    <div class="row clearfix mb-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="sabee_hotel_id"
                                                       placeholder="ID"
                                                       value="{{ $hotel->sabee_hotel_id }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">SabeeApp Url</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="sabee_is_active" {{ $hotel->sabee_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="sabee_url"
                                               placeholder="https://ibe.sabeeapp.com/properties/Example-Hotel-booking/?p=bSpf44a337ea1a30a74"
                                               value="{{ $hotel->sabee_url }}"/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Reseliva Hotel ID</h2>
                            <div class="row clearfix mb-3">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="checkbox"
                                               name="reseliva_is_active" {{ $hotel->reseliva_is_active ? "checked" : "" }}>
                                        Active
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="reseliva_hotel_id"
                                               placeholder="7813" value="{{ $hotel->reseliva_hotel_id }}"/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">HotelRunner Url</h2>
                            <div class="row clearfix mb-3">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="checkbox"
                                               name="is_hotelrunner_active" {{ $hotel->is_hotelrunner_active ? "checked" : "" }}>
                                        Active
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="hotelrunner_url"
                                               placeholder="https://hotel.hotelrunner.com" value="{{ $hotel->hotelrunner_url }}"/>
                                    </div>
                                </div>
                            </div>


                            <h2 class="card-inside-title">Booking.Com Url</h2>
                            <div class="row clearfix mb-3">

                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="booking_is_active" {{ $hotel->booking_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="booking_url"
                                               placeholder="https://www.booking.com/hotel/tr/example.tr.html"
                                               value="{{ $hotel->booking_url }}"/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Hotels.Com Url</h2>

                            <div class="row clearfix mb-3">

                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="hotels_is_active" {{ $hotel->hotels_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="hotels_url"
                                               placeholder="hotels.com/ho211277"
                                               value="{{ $hotel->hotels_url }}"/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">TatilSepeti.Com Url</h2>
                            <div class="row clearfix mb-3">

                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="tatilsepeti_is_active" {{ $hotel->tatilsepeti_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="tatilsepeti_url"
                                               placeholder="https://www.tatilsepeti.com/example-hotel-526274"
                                               value="{{ $hotel->tatilsepeti_url }}"/>
                                    </div>
                                </div>
                            </div>

                            <h2 class="card-inside-title">Odamax Hotel ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="odamax_is_active" {{ $hotel->odamax_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="odamax_url"
                                               placeholder="https://www.odamax.com/tr/hotel/example-hotel-287883"
                                               value="{{ $hotel->odamax_url }}"/>
                                    </div>

                                </div>
                            </div>

                            <h2 class="card-inside-title">OtelZ Tesis ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="otelz_is_active" {{ $hotel->otelz_is_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="otelz_url"
                                               placeholder="4532"
                                               value="{{ $hotel->otelz_url }}"/>
                                    </div>

                                </div>
                            </div>

                            <h2 class="card-inside-title">ETSTur Hotel ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox"
                                               name="is_etstur_active" {{ $hotel->is_etstur_active ? "checked" : "" }}>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="etstur_hotel_id"
                                               placeholder="KZSAPT"
                                               value="{{ $hotel->etstur_hotel_id }}"/>
                                    </div>
                                </div>
                            </div>


{{--                            <div class="row clearfix">--}}
{{--                                <div class="col-lg-12 col-md-12 col-sm-12">--}}
{{--                                    <div class="card">--}}
{{--                                        <div class="header">--}}
{{--                                            <h2>Select Hotels for Rate Comparison</h2>--}}

{{--                                        </div>--}}
{{--                                        <div class="body">--}}
{{--                                            <select id="optgroup" class="ms" multiple="multiple">--}}
{{--                                                @foreach($hotels as $hotel)--}}
{{--                                                    <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            </select>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}

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
        $(function () {
            let $sabeeToggle = $('#sabee-toggle')
            let $sabeeInputConatiner = $('#sabee-input-conatiner')

            $sabeeToggle.change(function () {
                $sabeeInputConatiner.slideToggle($sabeeToggle.prop('checked'))
            })
        })

        $('#optgroup').multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input type='text' class='search-input col-lg-12 col-md-12 col-sm-12' autocomplete='off' placeholder='Search Hotel'>",
            selectionHeader: "<input type='text' class='search-input col-lg-12 col-md-12 col-sm-12' autocomplete='off' placeholder='Search Hotel'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },
            afterSelect: function () {
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function () {
                this.qs1.cache();
                this.qs2.cache();
            }
        });

    </script>
@endsection
