(function () {

// Localize jQuery variable
        var jQuery;
        showMobile = true;

        if ({{ $main_widget->show_mobile }} == 0 && window.innerWidth < 768
    )
        showMobile = false

        /******** Load jQuery if not present *********/
        if (typeof jQuery == 'undefined') {
            var script_tag = document.createElement('script');
            script_tag.setAttribute("type", "text/javascript");
            script_tag.setAttribute("src",
                "https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
            if (script_tag.readyState) {
                script_tag.onreadystatechange = function () { // For old versions of IE
                    if (this.readyState == 'complete' || this.readyState == 'loaded') {
                        scriptLoadHandler();
                    }
                };
            } else {
                script_tag.onload = scriptLoadHandler;
            }
// Try to find the head, otherwise default to the documentElement
            (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
        } else {
// The jQuery version on the window is the one we want to use
            jQuery = window.jQuery;
        }

        /******** Called once jQuery has loaded ******/
        function scriptLoadHandler() {
// Restore $ and window.jQuery to their previous values and store the
// new jQuery in our local jQuery variable
            jQuery = window.jQuery.noConflict(true);
// Call our main function
            if (typeof loaded == 'undefined') {
                main();
                loaded = true;
            }
        }

        /* String Parameter */

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        function getFormattedDate(dateParam) {
            date = new Date(dateParam);
            let year = date.getFullYear();
            let month = (1 + date.getMonth()).toString().padStart(2, '0');
            let day = date.getDate().toString().padStart(2, '0');

            return day + '/' + month + '/' + year;
        }


        function ajaxCall(startDateVar, endDateVar, currencyVar, typeVar) {
            jQuery.ajax({
                type: 'POST',
                url: '{{ url("/") }}/widgetAjaxRequest/{{ $main_widget->code }}',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    withCredentials = true;
                    jQuery('#hoteldigilab-loading').addClass("hoteldigilab-loading");
                },
                data: {
                    _token: '{{ csrf_token() }}',
                    startDate: startDateVar,
                    endDate: endDateVar,
                    currency: currencyVar,
                    type: typeVar
                },
                success: function (data) {

                    let min_price = data.ibe.price
                    let link = data.ibe.url

                    if (!data.availability) {
                        jQuery('.no-availability').show()
                        jQuery('.hoteldigilab-channel-prices').hide()
                    }

                    else {
                        jQuery('.no-availability').hide()
                        jQuery('.hoteldigilab-channel-prices').show()

                        let link = data.ibe.url
                        let min_price = data.ibe.price

                        if (data.ibe.price != "NA") {
                            jQuery('.sabee').show()
                            jQuery('.hoteldigilab-website-price').text(data.ibe.price)
                            jQuery('.hoteldigilab-sabee-price-url').attr('href', data.ibe.url)
                            jQuery('.sabee-link').attr('href', data.ibe.url)
                        }

                        else{
                            jQuery('.sabee').hide()
                        }

                        if (data.booking.price == "NA") {
                            jQuery('.bookingcom').hide()
                        } else {
                            if (!min_price && min_price > data.booking.price) {
                                min_price = data.booking.price
                                jQuery('.sabee-link').attr('href', data.booking.url + '&aid=1939100')
                            }

                            jQuery('.bookingcom').show()
                            jQuery('.hoteldigilab-booking_price').text(data.booking.price)
                            jQuery('.hoteldigilab-booking-price-url').attr('href', data.booking.url)
                        }

                        if (data.etstur.price == "") {
                            jQuery('.etstur').hide()
                        }

                        else {
                            if (!min_price && min_price > data.etstur.price) {
                                min_price = data.etstur.price
                                jQuery('.sabee-link').attr('href', data.etstur.url)
                            }

                            jQuery('.etstur').show()
                            jQuery('.hoteldigilab-etstur_price').text(data.etstur.price)
                            jQuery('.hoteldigilab-etstur-price-url').attr('href', data.etstur.url)
                        }

                        if (data.tatilsepeti.price == "" || data.tatilsepeti.price == "NA") {
                            jQuery('.tatilsepeti').hide()
                        } else {
                            console.log(min_price);
                            if (!min_price || min_price > data.tatilsepeti.price) {
                                min_price = data.tatilsepeti.price
                                jQuery('.sabee-link').attr('href', data.tatilsepeti.url)
                            }

                            jQuery('.tatilsepeti').show()
                            jQuery('.hoteldigilab-tatilsepeti_price').text(data.tatilsepeti.price)
                        }

                        if (data.otelz.price == "NA") {
                            jQuery('.otelz').hide()
                        } else {
                            console.log("Min price" + min_price > data.otelz.price)
                            if (!min_price && min_price > data.otelz.price) {
                                min_price = data.otelz.price
                                jQuery('.sabee-link').attr('href', data.otelz.url)
                            }

                            jQuery('.otelz').show()
                            jQuery('.hoteldigilab-otelz_price').text(data.otelz.price)
                            jQuery('.hoteldigilab-otelz-price-url').attr('href', data.otelz.url)

                        }

                        if (data.odamax.price == "NA") {
                            jQuery('.odamax').hide()
                        } else {
                            if (!min_price && min_price > data.odamax.price) {
                                min_price = data.odamax.price
                                console.log("join");
                                jQuery('.sabee-link').attr('href', data.odamax.url)
                            }


                            jQuery('.odamax').show()
                            jQuery('.hoteldigilab-odamax_price').text(data.odamax.price)
                            jQuery('.hoteldigilab-odamax-price-url').attr('href', data.odamax.url)
                        }

                        if (data.hotels.price == "NA") {
                            jQuery('.hotelscom').hide()
                        } else {
                            if (!min_price && min_price > data.hotels.price)
                                min_price = data.hotels.price

                            jQuery('.hotelscom').show()
                            jQuery('.hoteldigilab-hotels_price').text(data.hotels.price)
                        }


                    }

                    jQuery('.hoteldigilab-sabee_price').text(min_price)
                    jQuery('.hoteldigilab-booking_price').text(data.booking_price)
                    if (typeVar === 'currencyChange') {
                        jQuery('.hoteldigilab-price_currency').text(' ' + jQuery('.hoteldigilab-currencies').val())
                    }

                },
                complete: function () {
                    jQuery('#hoteldigilab-loading').removeClass("hoteldigilab-loading");
                }
            });
        }


        /******** Our main function ********/
        function main() {
            jQuery(document).ready(function () {

                var script = document.createElement('script');
                script.onload = function () {
                    //do stuff with the script
                };
                script.src = "https://ratecare.net/assets/common/plugins/momentjs/moment.js";

                document.head.appendChild(script);


                /******* Load CSS *******/
                var css_link = jQuery("<link>", {
                    rel: "stylesheet",
                    type: "text/css",
                    href: "{{ asset("assets/common/css/widget.css") }}"
                });
                css_link.appendTo('head');

                /******* Load CSS *******/
                var css_link = jQuery("<link>", {
                    rel: "stylesheet",
                    type: "text/css",
                    href: "{{ asset("assets/common/plugins/datetimepicker/css/bootstrap-datetimepicker.min.css") }}"
                });
                css_link.appendTo('head');

                /******* Load CSS *******/
                var css_link = jQuery("<link>", {
                    rel: "stylesheet",
                    type: "text/css",
                    href: "https://fonts.googleapis.com/css?family=Abel|Cairo|Dosis|Open+Sans+Condensed:300|PT+Sans+Narrow|Rajdhani|Roboto|Roboto+Condensed|Saira+Semi+Condensed|Teko&display=swap"
                });
                css_link.appendTo('head');

                /******* Load CSS *******/
                var css_link = jQuery("<link>", {
                    rel: "stylesheet",
                    type: "text/css",
                    href: "https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.css"
                });
                css_link.appendTo('head');

                /******* Load CSS *******/
                var css_link = jQuery("<link>", {
                    rel: "stylesheet",
                    type: "text/css",
                    href: "{{ asset("assets/common/plugins/datepicker/datepicker.min.css") }}"
                });
                css_link.appendTo('head');

                /******* Load HTML *******/

                if (exitWidget == true) {
                    if ({{ $exit_widget->is_active }}) {

                        jQuery('body').append(`<div class="hoteldigilab-exit-background-cover"></div><div class="hoteldigilab-top-exit-trigger"></div>

<div class="hoteldigilab-exit-widget" style="font-family: '{{ $exit_widget->font }}', sans-serif !important;">
    <span class="hoteldigilab-close-exit-widget">X</span>
    <div class="logo-band" style="background-color: {{ $exit_widget->color }}">
        <img src="{{ asset("assets/common/img/logo_animated.gif") }}">
    </div>

    <div class="hoteldigilab-exit-widget-top-section">


        <div class="hoteldigilab-exit-widget-main-head" style="color: {{ $exit_widget->color }}">
            {{ $exit_widget->main_title }}
                        </div>

                        <div class="hoteldigilab-exit-widget-sub-text">
{!! $exit_widget->explanation_text !!}
                        </div>
                    </div>

                    <div class="hoteldigilab-exit-widget-middle-section">
                        </hr>

                        <div class="hoteldigilab-exit-widget-discount-code-title">{{ $exit_widget->promotion_text }}</div>

        <div class="hoteldigilab-exit-widget-discount-code">{{ $exit_widget->promotion_code }}</div>

        <div class="hoteldigilab-best-offers">
            {!! $exit_widget->features_text !!}
                        </div>
                    </div>

                    <a href="{{ $hotel->sabee_url }}" target="_blank">
        <div class="hoteldigilab-exit-widget-bottom-button" @if($exit_widget->color)style="background-color: {{ $exit_widget->color }}"@endif>
            {{ $exit_widget->reservation_button_text }}
                        </div>
                    </a>
                </div>`);

                    }
                }

                if (showMobile) {
                    if (mainWidget == true) {

                        if ({{ $main_widget->is_active }}) {


                            jQuery('body').append(`

<div class="hoteldigilab-widget" style="font-family: '{{ $main_widget->font }}', sans-serif !important;">

        <div class="hoteldigilab-reservation-button" id="hoteldigilab-reservation-button" style="background-color: {{ $main_widget->color }}">

                <div class="hoteldigilab-reservation-button-text">{{ $main_widget->reservation_button_text }}</div>
            <div class="hoteldigilab-reservation-button-img">
                <img src="{{ asset("assets/common/img/reservation-img.png") }}" />
            </div>
        </div>
    <div class="hoteldigilab-main">
        <div id="hoteldigilab-loading"></div>
        <div class="hoteldigilab-red-area" style="background-color: {{ $main_widget->color }}">
            <div class="hoteldigilab-logo">
                <img src="{{ asset("assets/common/img/logo_animated.gif") }}">
            </div>
            <div class="hoteldigilab-currency">
                <select id="hoteldigilab-currencies" class="hoteldigilab-currencies" style="-webkit-appearance: none; -moz-appearance: none; -appearance: none; background-image: none;">
                    @foreach($currencies as $currency)

                            <option value="{{ $currency->name }}" {{ $main_widget->currency_id == $currency->id ? 'selected="selected"' : "" }}>{{ $currency->symbol }}</option>

                    @endforeach
                            </select>
                        </div>
                        <div class="hoteldigilab-clear"></div>

                        <div class="hoteldigilab-main-title">
{{ $main_widget->main_title }}
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
                                <input type="date" format="DD-MM-YYYY" placeholder="Giriş Tarihi" id="hoteldigilab-startDate" style="cursor: pointer;">
                                <input type="date" format="DD-MM-YYYY" placeholder="Çıkış Tarihi" id="hoteldigilab-endDate" style="cursor: pointer;">
                                <button type="submit" id="hoteldigilab-dateSubmit" class="hoteldigilab-dateSubmitBtn" title="{{ $search }}"><i class="zmdi zmdi-search"></i></button>
                </div>
            </div>


            <div class="no-availability" style="display:none;">
                {{ $noAvailability }}
                            </div>

                            <div class="hoteldigilab-channel-prices" style="display: none">
                {{------------------------------------------------------------------------------------------------------------------------- sabee --}}
                <div class="hoteldigilab-price-from sabee">
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/www.png") }}"></div>
                        <div class="hoteldigilab-from-text">{{ $websiteText }}</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-website-price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                </div>

                <div class="hoteldigilab-clear"></div>

                {{------------------------------------------------------------------------------------------------------------------------ odamax --}}
                @if($hotel->odamax_is_active)
                    <div class="hoteldigilab-price-from odamax">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/odamax-icon.png") }}">
                            </div>
                            <div class="hoteldigilab-from-text">Odamax.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-odamax_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
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
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/booking.ico") }}"></div>
                            <div class="hoteldigilab-from-text">Booking.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-booking_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
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

                {{----------------------------------------------------------------------------------------------------------------------- resliva --}}
                @if($hotel->reseliva_is_active)
                    <!-- <div class="hoteldigilab-price-from reseliva">
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/reseliva.ico") }}">
                        </div>
                        <div class="hoteldigilab-from-text">Reseliva.Com</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-reseliva_price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                    </div>
                    <div class="hoteldigilab-clear"></div> -->
                @endif

                {{----------------------------------------------------------------------------------------------------------------------- otelz --}}
                @if($hotel->otelz_is_active)
                    <div class="hoteldigilab-price-from otelz">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/otelz.png") }}">
                            </div>
                            <div class="hoteldigilab-from-text">OtelZ.Com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-otelz_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                    </div>
                    <div class="hoteldigilab-clear"></div>
                @endif

                {{----------------------------------------------------------------------------------------------------------------------- etstur --}}
                @if($hotel->is_etstur_active)
                    <div class="hoteldigilab-price-from etstur">
                            <div class="hoteldigilab-from-logo">
                                <img src="{{ asset("assets/common/img/website-icons/etstur.ico") }}">
                            </div>
                            <div class="hoteldigilab-from-text">etstur.com</div>
                            <div class="hoteldigilab-from-price">
                                <span class="hoteldigilab-etstur_price"></span>
                                <span class="hoteldigilab-price_currency"> TRY</span>
                            </div>
                    </div>
                    <div class="hoteldigilab-clear"></div>
                @endif

                {{----------------------------------------------------------------------------------------------------------------------------------}}
            </div>

                        </div>

                        <div style="font-size: 12px;text-align: right;margin-right: 20px;margin-top: 10px;">Powered By <span style="font-weight: bold">RateCare</span></div>

    </div>
                </div>
`);

                        }
                    }
                }

                activation_date = "{{ $main_widget->activation_date }}";
                var date = new Date();
                if (activation_date) {
                    var dateParts = activation_date.split("-");
                    var activation_date_js = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0, 2));
                    date = activation_date_js > date ? activation_date_js : date;
                }

                var tomorrow = new Date(date);
                date.setHours(date.getHours() + 3);
                tomorrow.setHours(tomorrow.getHours() + 3);
                tomorrow.setDate(tomorrow.getDate() + {{ $main_widget->minimum_stay }});

                jQuery('#hoteldigilab-startDate').val(date.toISOString().substr(0, 10));
                jQuery('#hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));

                jQuery(".hoteldigilab-price_currency").text(" " + jQuery('#hoteldigilab-currencies').val());

                ajaxCall(jQuery('#hoteldigilab-startDate').val(), jQuery('#hoteldigilab-endDate').val(), jQuery('#hoteldigilab-currencies').val(), "pageLoad");

                if(window.screen.width > 768){
                    jQuery('.hoteldigilab-widget').delay(2000).animate({'right': '0px'}, 1000).delay({{ $main_widget->duration }}000).animate({'right': '-275px'}, 1000);
                }

                jQuery('#hoteldigilab-startDate').change(function () {
                    var tomorrow = new Date(this.value);
                    tomorrow.setDate(tomorrow.getDate() + {{ $main_widget->minimum_stay }})
                    jQuery('#hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));
                });


                jQuery('#hoteldigilab-currencies').change(function () {
                    ajaxCall(jQuery('#hoteldigilab-startDate').val(), jQuery('#hoteldigilab-endDate').val(), jQuery('#hoteldigilab-currencies').val(), "currencyChange");
                });

                jQuery('#hoteldigilab-dateSubmit').click(function () {
                    ajaxCall(jQuery('#hoteldigilab-startDate').val(), jQuery('#hoteldigilab-endDate').val(), jQuery('#hoteldigilab-currencies').val(), "dateSearch");
                });

                mainWidgetOpened = 0;

                jQuery('#hoteldigilab-reservation-button').click(function () {
                    if (mainWidgetOpened == 0) {
                        jQuery('.hoteldigilab-widget').animate({'right': '0px'}, 1000);
                        mainWidgetOpened = 1;
                    } else {
                        jQuery('.hoteldigilab-widget').animate({'right': '-275px'}, 1000);
                        mainWidgetOpened = 0;
                    }
                });

                jQuery('.hoteldigilab-close-exit-widget').click(function () {

                    jQuery('.hoteldigilab-exit-widget').hide();
                    jQuery('.hoteldigilab-exit-background-cover').hide();


                });

                exitWidgetOpened = 0;

                jQuery('.hoteldigilab-top-exit-trigger').mouseover(function () {
                    if (exitWidgetOpened == 0) {
                        jQuery('.hoteldigilab-exit-widget').show();
                        jQuery('.hoteldigilab-exit-background-cover').show();
                        exitWidgetOpened = 1;
                    }
                });

            });

        }

    })(); // We call our anonymous function immediately
