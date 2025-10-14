@extends("customer._partials.layout")

@section("content")
<form method="POST" action="{{ route("customer.rate.store") }}">
    @csrf
    <div class="col-md-12">
        <div class="row">
        <div class="col-md-3">
                <h6 class="card-inside-title">Compare With</h6>
                <div class="row clearfix mb-3">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="form-control input-group input-group-btn col-md-12 p-0 selectpicker" data-container="form-group" name="competitorHotels[]" multiple data-live-search="true" data-max-options="5" data-actions-box="true">
                                @if($competitorHotels)
                                    @foreach($competitorHotels as $ch)                                                                
                                        <option @if(in_array($ch->hotel->id, $competitors)) selected @endif value="{{ $ch->hotel->id }}" data-content="
                                            <span>{{ $ch->hotel->name }}</span>
                                            @if($ch->hotel->is_sabee_user)<img src='{{ asset("assets/common/img/website-icons/www.png") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->reseliva_is_active)<img src='{{ asset("assets/common/img/website-icons/reseliva.ico") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->otelz_is_active)<img src='{{ asset("assets/common/img/website-icons/otelz.png") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->is_etstur_active)<img src='{{ asset("assets/common/img/website-icons/etstur.ico") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->is_hotelrunner_active)<img src='{{ asset("assets/common/img/website-icons/web.svg") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->booking_is_active)<img src='{{ asset("assets/common/img/website-icons/booking.ico") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->hotels_is_active)<img src='{{ asset("assets/common/img/website-icons/hotels.ico") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->tatilsepeti_is_active)<img src='{{ asset("assets/common/img/website-icons/tatilsepeti.ico") }}' class='dropdown-channel-logo'>@endif
                                            @if($ch->hotel->odamax_is_active)<img src='{{ asset("assets/common/img/website-icons/odamax-icon.png") }}' class='dropdown-channel-logo'>@endif
                                            ">
                                            {{ $ch->hotel->name }}                                        
                                        </option>

                                        @if($loop->last) <option data-divider="true"></option> @endif
                                    @endforeach
                                @endif
                                
                                @foreach($hotels as $hotel)   
                                    @if(in_array($hotel->id, $competitors))
                                        @continue
                                    @endif                                                 
                                    <option value="{{ $hotel->id }}" data-content="
                                        <span>{{ $hotel->name }}</span>
                                        @if($hotel->is_sabee_user)<img src='{{ asset("assets/common/img/website-icons/www.png") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->reseliva_is_active)<img src='{{ asset("assets/common/img/website-icons/reseliva.ico") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->otelz_is_active)<img src='{{ asset("assets/common/img/website-icons/otelz.png") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->is_etstur_active)<img src='{{ asset("assets/common/img/website-icons/etstur.ico") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->is_hotelrunner_active)<img src='{{ asset("assets/common/img/website-icons/web.svg") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->booking_is_active)<img src='{{ asset("assets/common/img/website-icons/booking.ico") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->hotels_is_active)<img src='{{ asset("assets/common/img/website-icons/hotels.ico") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->tatilsepeti_is_active)<img src='{{ asset("assets/common/img/website-icons/tatilsepeti.ico") }}' class='dropdown-channel-logo'>@endif
                                        @if($hotel->odamax_is_active)<img src='{{ asset("assets/common/img/website-icons/odamax-icon.png") }}' class='dropdown-channel-logo'>@endif
                                        ">
                                        {{ $hotel->name }}                                        
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="col-md-3">
            <h6 class="card-inside-title">Select Channels</h6>
                <div class="row clearfix mb-3">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="col-md-12 p-0" data-container="form-group" name="channels[]" multiple data-live-search="true" data-actions-box="true">
                                <option @if(in_array("SabeeApp", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/www.png") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>SabeeApp</span>" value="SabeeApp">SabeeApp</option>
                                <option @if(in_array("Reseliva", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/reseliva.ico") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>Reseliva</span>" value="Reseliva">Reseliva</option>
                                <option @if(in_array("OtelZ", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/otelz.png") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>OtelZ</span>" value="OtelZ">OtelZ</option>
                                <option @if(in_array("ETSTur", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/etstur.ico") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>ETSTur</span>" value="ETSTur">ETSTur</option>
                                <option @if(in_array("HotelRunner", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/web.svg") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>HotelRunner</span>" value="HotelRunner">HotelRunner</option>
                                <option @if(in_array("Booking", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/booking.ico") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>Booking</span>" value="Booking">Booking</option>
                                <option @if(in_array("Hotels", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/hotels.ico") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>Hotels</span>" value="Hotels">Hotels</option>
                                <option @if(in_array("Tatilsepeti", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/tatilsepeti.ico") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>TatilSepeti</span>" value="Tatilsepeti">Tatilsepeti</option>
                                <option @if(in_array("Odamax", $channels)) selected @endif data-content="<img src='{{ asset("assets/common/img/website-icons/odamax-icon.png") }}' class='dropdown-channel-logo'> <span class='dropdown-channel-text'>Odamax</span>" value="Odamax">Odamax</option>
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class="col-md-1">
            <h6 class="card-inside-title">Currency</h6>
                <div class="row clearfix mb-3">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <select class="col-md-12 p-0" data-container="form-group" name="currency">
                                <option value="TRY" @if($currency == "TRY") selected @endif>TRY</option>
                                <option value="EUR" @if($currency == "EUR") selected @endif>EUR</option>
                                <option value="USD" @if($currency == "USD") selected @endif>USD</option>
                            </select>
                        </div>
                    </div>
                </div>
        </div>
        <div class=col-md-3>
            <button type="submit" class="btn btn-primary pull-right rate-btn-save" >Save</button>
        </div>
            </div>
    </div>
</form>
    <div class="col-md-12">
        <div class="card">
            <div class="body table-responsive">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <div class="card">
                            <div class="body">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modals"></div>

@endsection

@section("scripts")
    <script>
        'use strict'

        $('.selectpicker').selectpicker();

        let currency = $('[name="currency"]').val();
        let currencySymbol;
        if(currency == "TRY"){
            currencySymbol = "₺"
        }
        else if(currency == "EUR"){
            currencySymbol = "€"
        }
        else if(currency == "USD"){
            currencySymbol = "$"
        }

        function getDatesBetween(startDate, endDate) {
            const currentDate = new Date(startDate.getTime())
            const dates       = []
            while (currentDate <= endDate) {
                dates.push(new Date(currentDate))
                currentDate.setDate(currentDate.getDate() + 1)
            }
            return dates
        }        

        $('#calendar').fullCalendar({
            header     : {
                left  : 'prev,next today',
                center: 'title',
                right : '<span>asdasdasd</span>',
            },
            defaultDate: '{{ Carbon\Carbon::now()->format("Y-m-d") }}',
            editable   : false,
            droppable  : false, // this allows things to be dropped onto the calendar
            drop       : function() {
                // is the "remove after drop" checkbox checked?
                if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove()
                }
            },
            eventLimit : true, // allow "more" link when too many events
            eventClick : function(info) {
                $('#date-' + info.date).modal({'backdrop': 'static'}).focus()
            },
            viewRender : function(view, element) {
                let startDate = view.intervalStart._d
                let endDate   = view.intervalEnd._d
                endDate.setDate(endDate.getDate() - 1)
                const period = getDatesBetween(startDate, endDate)
                let today = new Date();
                today.setHours(0,0,0,0);
                for (const date of period) {
                    let calendarDate = new Date(date);
                    calendarDate.setHours(0,0,0,0);
                    if(calendarDate < today){
                        continue;
                    }                

                    const sDate    = ('0' + date.getDate()).slice(-2) + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + date.getFullYear()                    
                    const sDateReversed    = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2)
                    const ModalDate = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear()     

                    const tomorrow = date                
                    tomorrow.setDate(tomorrow.getDate() + 1)
                    const eDate = ('0' + tomorrow.getDate()).slice(-2) + '-' + ('0' + (tomorrow.getMonth() + 1)).slice(-2) + '-' + tomorrow.getFullYear()                  
                    
                    const tomorrowReversed = new Date()
                    const eDateReversed    = tomorrow.getFullYear() + '-' + ('0' + (tomorrow.getMonth() + 1)).slice(-2) + '-' + ('0' + tomorrow.getDate()).slice(-2)

                    $('.modals').append('<div class="dateModal modal fade" id="date-' + sDateReversed + '" tabindex="2" role="dialog" aria-modal=-"true"> <div class="modal-dialog" role="document"><div class="modal-content"> <div class="modal-header"><h4 class="title" id="defaultModalLabel"> ' + ModalDate + ' Rate Scan Results</h4> </div> <div class="modal-body"> <div class="table-responsive"> <table class="table table-hover m-b-0" id="pricesTable"> <thead> <tr> <td><b>Hotel Name</b></td> <td><b>Price</b></td> <td><b>OTA</b></td> </tr> </thead> <tbody> </tbody> </table> </div>{{--                        <div class="text-area">--}}{{--                            <br><b>Your Price:</b> <span class="price-field"></span>' + currencySymbol + '<br>--}}{{--                            <b>Average:</b> <span class="avarage-field"></span>' + currencySymbol + '<br>--}}{{--                            %<span class="percentage-field"></span> vs competitors--}}{{--                        </div>--}}</div> <div class="modal-footer"> <button type="button" class="btn btn-danger btn-simple btn-round waves-effect" data-dismiss="modal">CLOSE</button> </div> </div> </div> </div>')
                    

                    const newEvent = {
                        title       : 'Long Event',
                        start       : '' + sDateReversed + '',
                        className   : '' + sDateReversed + ' hidden calendar-price-button',
                        date        : '' + sDateReversed + '',
                        eventContent: {html: '<i>some html</i>'},
                    }
                    $('#calendar').fullCalendar('renderEvent', newEvent)

                    $.ajax({
                        url        : {!! '"' . route("customer.rate.ajax-request") . '"' !!},
                        type       : 'POST',
                        indexValue : {date1: sDateReversed, date2: eDateReversed},
                        data       : jQuery.param({startDate: sDate, endDate: eDate, currency: 'TRY'}),
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        success    : function(d) {

                            var dateModal  = '#date-' + this.indexValue.date1 + ''
                            var dateButton = '.' + this.indexValue.date1 + ''


                            if (d.length === 0) {
                                $(dateModal).remove()
                                $(dateButton).removeClass('hidden').addClass('l-coral').addClass('show')
                                $(dateButton + '> .fc-content > .fc-title').html('<center>N/A</center>')
                                $(dateButton + '> .fc-content').css('padding', '19px')
                            } else {
                                var lowPrices     = []
                                var avaragePrices = []
                                var buttonMinPrice, buttonAvarage, buttonPercentage
                                $(dateModal).find('#pricesTable tbody tr').remove()
                                d.forEach(function(i) {
                                    lowPrices.push(i.minPrice)
                                    var logo
                                    switch (i.minPriceChannel) {
                                        case 'ibePrice':
                                            logo = '{{ asset("assets/common/img/website-icons/www.png") }}'
                                            break

                                        case 'odamaxPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/odamax-icon.png") }}'
                                            break

                                        case 'tatilsepetiPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/tatilsepeti-icon.png") }}'
                                            break

                                        case 'bookingPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/booking.ico") }}'
                                            break

                                        case 'hotelsPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/hotels.ico") }}'
                                            break

                                        case 'reselivaPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/reseliva.ico") }}'
                                            break

                                        case 'etsturPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/etstur.ico") }}'
                                            break

                                        case 'otelzPrice':
                                            logo = '{{ asset("assets/common/img/website-icons/otelz.png") }}'
                                            break

                                        default:
                                            logo = ''
                                            break
                                    }
                                    
                                    $(dateModal).find('#pricesTable tbody').append('<tr><td>' + i.hotelName + '</td><td>' + i.minPrice + currencySymbol + '</td><td><img src="' + logo + '" style="height: 15px; width: 15px;"/></td></tr>')

                                    if (!i.isMain) {
                                        avaragePrices.push(i.minPrice)
                                    } else {
                                        buttonMinPrice = i.minPrice
                                    }


                                    const sum        = avaragePrices.reduce((a, b) => a + b, 0)
                                    const avg        = (sum / avaragePrices.length) || 0
                                    buttonAvarage    = Math.round(avg)
                                    buttonPercentage = Math.round(100 - (buttonAvarage / buttonMinPrice * 100))
                                    
                                    if(!isNaN(buttonPercentage)){
                                        if(buttonPercentage < 0){
                                            $(dateButton).css("background", "#F6BE25");
                                        }

                                        else{
                                            $(dateButton).css("background", "#B2DC9B");
                                        }
                                    }

                                    else{
                                            $(dateButton).css("background", "#F6BE25");
                                        }

                                    // $(dateButton + '> .fc-content > .fc-title').text("Best Price : " + Math.min.apply(null,lowPrices));
                                    $(dateButton + '> .fc-content > .fc-title').html('<center><b>Your Price :</b> ' + (buttonMinPrice == undefined ? "No Availability" : buttonMinPrice + currencySymbol)  + '<br><b>Comp. Average :</b> ' + buttonAvarage + currencySymbol +'<br><div style=\'font-size:14px; margin-top: 5px\'><b>' + (isNaN(buttonPercentage) ? "" : ('%' + buttonPercentage + ' vs</b> competitors</div></center>')))

                                })
                                $(dateButton).removeClass('hidden').addClass('show')
                            }
                        },
                        error      : function() {
                            console.log('error')
                        },
                    })
                }
            },
        })
    </script>
@endsection