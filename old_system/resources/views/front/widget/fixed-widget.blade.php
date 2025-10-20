<div class="hoteldigilab-widget" style="font-family: '{{ $main_widget->font }}', sans-serif !important;">
    <div class="hoteldigilab-main">

        <div class="hoteldigilab-loading-text-out" style="display: none;">
            Sizin için en iyi fiyatları arıyoruz...
        </div>

        <div class="hoteldigilab-loading-out"></div>

        <div class="hoteldigilab-red-area" style="background-color: #3696C9">
            <div class="hoteldigilab-flex hoteldigilab-align-items-center hoteldigilab-justify-content-between">
                <div class="hoteldigilab-fixed-main-title">
                    {{ $mainTitle }}
                </div>
                <div class="hoteldigilab-currency">
                    <select class="hoteldigilab-currencies"
                            style="-webkit-appearance: none; -moz-appearance: none; -appearance: none; background-image: none;">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->name }}" {{ $main_widget->currency_id == $currency->id ? 'selected="selected"' : "" }}>
                                {{ $currency->symbol }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <a href="{{ sabeeUrlLanguageChange($lang, $hotel->sabee_url) }}" target="_blank" class="sabee-link" rel=”nofollow”>
                <div class="hoteldigilab-discounted-rate-btn">
                    {{ $main_widget->direct_reservation_text }}
                    <span class="hoteldigilab-sabee_price"></span>
                    <span class="hoteldigilab-price_currency"> TRY</span>
                </div>
            </a>

            <div class="hoteldigilab-reservation-text">
                {{ $findOut }}
            </div>

            <div class="hoteldigilab-promoted-text">
                {!! $main_widget->features_text !!}
            </div>
        </div>

        <div class="hoteldigilab-bottom-area">

            <div class="hoteldigilab-date">
                <div class="date-header">
                    {{ $selectYourDatesText }}
                </div>

                <div class="hoteldigilab-date-select">
                    <input type="text" id="hoteldigilab-startdate" class="hoteldigilab-startDate" style="cursor: pointer;">
                    <input type="text" id="hoteldigilab-enddate" class="hoteldigilab-endDate" data-date-format='yy-mm-dd' style="cursor: pointer;">
                    <button type="submit" class="hoteldigilab-dateSubmit hoteldigilab-dateSubmitBtn" title="{{ $search }}">
                        Ara
                    </button>
                </div>
            </div>

            <div class="no-availability" style="display:none;">
                {{ $noAvailability }}
            </div>
            <div class="hoteldigilab-channel-prices" style="display: none">
                {{------------------------------------------------------------------------------------------------------------------------- sabee --}}
                <div class="hoteldigilab-price-from sabee">
                    <a href="" class="hoteldigilab-sabee-price-url" target="_blank" rel=”nofollow”>
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/www.png") }}"></div>
                        <div class="hoteldigilab-from-text">{{ $websiteText }}</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-website-price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                    </a>
                </div>

                <div class="hoteldigilab-clear"></div>

                {{------------------------------------------------------------------------------------------------------------------------ odamax --}}
                @if($hotel->odamax_is_active)
                    <div class="hoteldigilab-price-from odamax">
                        <a href="" class="hoteldigilab-odamax-price-url" target="_blank" rel=”nofollow”>
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/odamax-icon.png") }}">
                            </div>
                            <div class="hoteldigilab-from-text">Odamax.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-odamax_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                        </a>
                    </div>

                    <div class="hoteldigilab-clear"></div>
                @endif

                {{------------------------------------------------------------------------------------------------------------------- tatilsepeti --}}
                @if($hotel->tatilsepeti_is_active)
                    <div class="hoteldigilab-price-from tatilsepeti">
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/tatilsepeti-icon.png") }}">
                        </div>
                        <div class="hoteldigilab-from-text">TatilSepeti.Com</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-tatilsepeti_price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                    </div>

                    <div class="hoteldigilab-clear"></div>
                @endif

                {{----------------------------------------------------------------------------------------------------------------------- booking --}}
                @if($hotel->booking_is_active)
                    <div class="hoteldigilab-price-from bookingcom">
                        <a href="" class="hoteldigilab-booking-price-url" target="_blank">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/booking.ico") }}"></div>
                            <div class="hoteldigilab-from-text">Booking.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-booking_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                        </a>
                    </div>

                    <div class="hoteldigilab-clear"></div>
                @endif

                {{------------------------------------------------------------------------------------------------------------------------ hotels --}}
                @if($hotel->hotels_is_active)
                    <div class="hoteldigilab-price-from hotelscom">
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/hotels.ico") }}">
                        </div>
                        <div class="hoteldigilab-from-text">Hotels.Com</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-hotels_price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                    </div>

                    <div class="hoteldigilab-clear"></div>
                @endif

                
                {{----------------------------------------------------------------------------------------------------------------------- otelz --}}
                @if($hotel->otelz_is_active)
                    <div class="hoteldigilab-price-from otelz">
                        <a href="" class="hoteldigilab-otelz-price-url" target="_blank">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/otelz.png") }}">
                            </div>
                            <div class="hoteldigilab-from-text">OtelZ.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-otelz_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                        </a>
                    </div>
                    <div class="hoteldigilab-clear"></div>
                @endif

                {{----------------------------------------------------------------------------------------------------------------------- etstur --}}
                @if($hotel->is_etstur_active)
                    <div class="hoteldigilab-price-from etstur">
                        <a href="" class="hoteldigilab-etstur-price-url" target="_blank">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/etstur.ico") }}">
                            </div>
                            <div class="hoteldigilab-from-text">etstur.com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-etstur_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                        </a>
                    </div>
                    <div class="hoteldigilab-clear"></div>
                @endif

                {{----------------------------------------------------------------------------------------------------------------------------------}}
            </div>

        </div>

        <div style="font-size: 12px;text-align: right;margin-right: 20px;margin-top: 10px;">Powered By <span style="font-weight: bold">RateCare</span></div>

    </div>


</div>
