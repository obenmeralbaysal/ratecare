(function() {

// Localize jQuery variable
    var jQuery;

    /******** Load jQuery if not present *********/
    if (typeof jQuery=='undefined') {
        var script_tag = document.createElement('script');
        script_tag.setAttribute("type", "text/javascript");
        script_tag.setAttribute("src",
            "https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
        if (script_tag.readyState) {
            script_tag.onreadystatechange = function() { // For old versions of IE
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
        jQuery.noConflict();
// Restore $ and window.jQuery to their previous values and store the
// new jQuery in our local jQuery variable
        jQuery = window.jQuery.noConflict(true);
// Call our main function
        if(typeof loaded == 'undefined'){
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


    /******** Our main function ********/
    function main() {
        jQuery(document).ready(function() {

            var script = document.createElement('script');
            script.onload = function () {
            };
            script.src = "https://ratecare.net/assets/common/plugins/datetimepicker/js/bootstrap-datetimepicker.js";

            document.head.appendChild(script);


            /******* Load CSS *******/
            var css_link = jQuery("<link>", {
                rel: "stylesheet",
                type: "text/css",
                href: "assets/common/css/widget.css"
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

            if({{ exit_widget_is_active }}){

                jQuery('body').append(`<div class="hoteldigilab-exit-background-cover"></div><div class="hoteldigilab-top-exit-trigger"></div>

<div class="hoteldigilab-exit-widget" style="font-family: '{{ exit_widget_color }}', sans-serif !important;">
    <span class="hoteldigilab-close-exit-widget">X</span>

    <div class="hoteldigilab-exit-widget-top-section">
        <div class="hoteldigilab-exit-widget-main-head" style="color: {{ $exit_widget->color }}">
            {{ $exit_widget->main_title }}
        </div>

        <div class="hoteldigilab-exit-widget-sub-text">
            {{ $exit_widget->explanation_text }}
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

    <a href="{{ $hotel->sabee_url }}">
        <div class="hoteldigilab-exit-widget-bottom-button" @if($exit_widget->color)style="background-color: {{ $exit_widget->color }}"@endif>
            {{ $exit_widget->reservation_button_text }}
        </div>
    </a>
</div>`);

            }

            if({{ $main_widget->is_active }}){


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
                <img src="{{ asset("assets/common/img/loqqo.png") }}">
            </div>
            <div class="hoteldigilab-currency">
                <select id="hoteldigilab-currencies" class="hoteldigilab-currencies" style="-webkit-appearance: none; -moz-appearance: none; -appearance: none; background-image: none;">
                    @foreach($currencies as $currency)

                        <option value="{{ $currency->name }}" {{ $main_widget->currency_id == $currency->id ? 'selected="selected"' : "" }}>{{ $currency->name }}</option>

                    @endforeach
                </select>
            </div>
            <div class="hoteldigilab-clear"></div>

            <div class="hoteldigilab-main-title">
                {{ $main_widget->main_title }}
            </div>

            <a href="{{ sabeeUrlLanguageChange($lang, $hotel->sabee_url) }}" target="_blank" class="sabee-link">

                    <div class="hoteldigilab-discounted-rate-btn">
                        {{ $main_widget->direct_reservation_text }} <span id="hoteldigilab-sabee_price"></span> <span class="hoteldigilab-price_currency" id="hoteldigilab-sabee_currency"> TRY</span>
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

            <div class="channel-prices" style="display: none">
                <div class="hoteldigilab-price-from sabee">
                    <div class="hoteldigilab-from-logo"><img src="{{ asset("assets/common/img/website-icons/www.png") }}"></div>
                    <div class="hoteldigilab-from-text">{{ $websiteText }}</div>
                    <div class="hoteldigilab-from-price"><span id="hoteldigilab-website-price"></span><span class="hoteldigilab-price_currency"> TRY</span></div>
                </div>

                <div class="hoteldigilab-clear"></div>


                @if($hotel->booking_is_active)

                <div class="hoteldigilab-price-from bookingcom">
                    <div class="hoteldigilab-from-logo"><img src="{{ asset("assets/common/img/website-icons/booking.ico") }}"></div>
                    <div class="hoteldigilab-from-text">Booking.Com</div>
                    <div class="hoteldigilab-from-price"><span id="hoteldigilab-booking_price"></span><span class="hoteldigilab-price_currency"> TRY</span></div>
                </div>

                @endif

                <div class="hoteldigilab-clear"></div>

                @if($hotel->hotels_is_active)


                <div class="hoteldigilab-price-from hotelscom">
                    <div class="hoteldigilab-from-logo"><img src="{{ asset("assets/common/img/website-icons/hotels.ico") }}"></div>
                    <div class="hoteldigilab-from-text">Hotels.Com</div>
                    <div class="hoteldigilab-from-price"><span id="hoteldigilab-hotels_price"></span><span class="hoteldigilab-price_currency"> TRY</span></div>
                </div>

                <div class="hoteldigilab-clear"></div>


                @endif

                @if($hotel->tatilsepeti_is_active)


                <div class="hoteldigilab-price-from tatilsepeti">
                    <div class="hoteldigilab-from-logo"><img src="{{ asset("assets/common/img/website-icons/tatilsepeti-icon.png") }}"></div>
                    <div class="hoteldigilab-from-text">TatilSepeti.Com</div>
                    <div class="hoteldigilab-from-price"><span id="hoteldigilab-tatilsepeti_price"></span><span class="hoteldigilab-price_currency"> TRY</span></div>
                </div>

                <div class="hoteldigilab-clear"></div>

                @endif

                @if($hotel->odamax_is_active)

                <div class="hoteldigilab-price-from odamax">
                    <div class="hoteldigilab-from-logo"><img src="{{ asset("assets/common/img/website-icons/odamax-icon.png") }}"></div>
                    <div class="hoteldigilab-from-text">Odamax.Com</div>
                    <div class="hoteldigilab-from-price"><span id="hoteldigilab-odamax_price"></span><span class="hoteldigilab-price_currency"> TRY</span></div>
                </div>

                @endif

            </div>

        </div>
    </div>
</div>
`);

            }



            var date = new Date();
            var tomorrow = new Date();
            tomorrow.setDate(date.getDate()+1)

            jQuery('#hoteldigilab-startDate').val(date.toISOString().substr(0, 10));
            jQuery('#hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));

            jQuery(".hoteldigilab-price_currency").text(" " + jQuery('#hoteldigilab-currencies').val());

            jQuery.ajax({
                type:'POST',
                url:'{{ url("/") }}/widgetAjaxRequest/{{ $main_widget->code }}',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                dataType: 'json',
                beforeSend: function(xhr){
                    withCredentials = true;
                    jQuery('#hoteldigilab-loading').addClass("hoteldigilab-loading");
                },
                data: { _token: '{{ csrf_token() }}', startDate: jQuery('#hoteldigilab-startDate').val(), endDate: jQuery('#hoteldigilab-endDate').val(), currency: jQuery('#hoteldigilab-currencies').val(), type: 'pageLoad' },
                success:function(data) {

                    if(!data.availability){
                        jQuery(".no-availability").show();
                        jQuery(".channel-prices").hide();
                    }

                    else{
                        jQuery(".no-availability").hide();
                        jQuery(".channel-prices").show();

                        if(data.tatilsepeti_price == ""){
                            jQuery(".tatilsepeti").hide();
                        }

                        else{
                            jQuery(".tatilsepeti").show();
                            jQuery("#hoteldigilab-tatilsepeti_price").text(data.tatilsepeti_price);
                        }

                        if(data.odamax_price == ""){
                            jQuery(".odamax").hide();
                        }

                        else{
                            jQuery(".odamax").show();
                            jQuery("#hoteldigilab-odamax_price").text(data.odamax_price);
                        }

                        if(data.hotels_price == ""){
                            jQuery(".hotelscom").hide();
                        }

                        else{
                            jQuery(".hotelscom").show();
                            jQuery("#hoteldigilab-hotels_price").text(data.hotels_price);
                        }

                        if(data.booking_price == ""){
                            jQuery(".bookingcom").hide();
                        }

                        else{
                            jQuery(".bookingcom").show();
                            jQuery("#hoteldigilab-booking_price").text(data.booking_price);
                        }

                    }

                    jQuery("#hoteldigilab-sabee_price").text(data.sabee_price);
                    jQuery("#hoteldigilab-website-price").text(data.sabee_price);
                    jQuery("#hoteldigilab-booking_price").text(data.booking_price);

                },
                complete: function(){
                    jQuery('#hoteldigilab-loading').removeClass("hoteldigilab-loading");
                }
            });

            jQuery('.hoteldigilab-widget').delay(2000).animate({'right': '0px'}, 1000).delay({{ $main_widget->duration }}000).animate({'right': '-275px'}, 1000);

        });

    }

})(); // We call our anonymous function immediately

jQuery('document').ready(function(){

    jQuery(document).on('change', '#hoteldigilab-startDate', function(){
        var tomorrow = new Date(this.value);
        tomorrow.setDate(tomorrow.getDate()+1)
        jQuery('#hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));
    });


    jQuery(document).on('change', '#hoteldigilab-currencies', function(){
        jQuery.ajax({
            type:'POST',
            url:'{{ url("/") }}/widgetAjaxRequest/{{ $main_widget->code }}',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            dataType: 'json',
            beforeSend: function(){
                jQuery('#hoteldigilab-loading').addClass("hoteldigilab-loading");
            },
            data: { _token: '{{ csrf_token() }}', startDate: jQuery('#hoteldigilab-startDate').val(), endDate: jQuery('#hoteldigilab-endDate').val(), currency: jQuery('#hoteldigilab-currencies').val(), type: 'currencyChange' },
            success:function(data) {

                if(!data.availability){
                    jQuery(".no-availability").show();
                    jQuery(".channel-prices").hide();
                }

                else{
                    jQuery(".no-availability").hide();
                    jQuery(".channel-prices").show();

                    if(data.tatilsepeti_price == ""){
                        jQuery(".tatilsepeti").hide();
                    }

                    else{
                        jQuery(".tatilsepeti").show();
                        jQuery("#hoteldigilab-tatilsepeti_price").text(data.tatilsepeti_price);
                    }

                    if(data.odamax_price == ""){
                        jQuery(".odamax").hide();
                    }

                    else{
                        jQuery(".odamax").show();
                        jQuery("#hoteldigilab-odamax_price").text(data.odamax_price);
                    }


                    if(data.hotels_price == ""){
                        jQuery(".hotelscom").hide();
                    }

                    else{
                        jQuery(".hotelscom").show();
                        jQuery("#hoteldigilab-hotels_price").text(data.hotels_price);
                    }

                    if(data.booking_price == ""){
                        jQuery(".bookingcom").hide();
                    }

                    else{
                        jQuery(".bookingcom").show();
                        jQuery("#hoteldigilab-booking_price").text(data.booking_price);
                    }

                }

                jQuery("#hoteldigilab-sabee_price").text(data.sabee_price);
                jQuery("#hoteldigilab-booking_price").text(data.booking_price);
                jQuery("#hoteldigilab-website-price").text(data.sabee_price);
                jQuery(".hoteldigilab-price_currency").text(" " + jQuery('#hoteldigilab-currencies').val());
            },
            complete: function(){
                jQuery('#hoteldigilab-loading').removeClass("hoteldigilab-loading");
            }
        });
    });

    jQuery(document).on('click', '#hoteldigilab-dateSubmit', function(){

        jQuery.ajax({
            type:'POST',
            url:'{{ url("/") }}/widgetAjaxRequest/{{ $main_widget->code }}',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            dataType: 'json',
            beforeSend: function(){
                jQuery('#hoteldigilab-loading').addClass("hoteldigilab-loading");
            },
            data: { _token: '{{ csrf_token() }}', startDate: jQuery('#hoteldigilab-startDate').val(), endDate: jQuery('#hoteldigilab-endDate').val(), currency: jQuery('#hoteldigilab-currencies').val(), type: 'dateSearch' },
            success:function(data) {
                if(!data.availability){
                    jQuery(".no-availability").show();
                    jQuery(".channel-prices").hide();
                }

                else{

                    jQuery(".no-availability").hide();
                    jQuery(".channel-prices").show();

                    if(data.tatilsepeti_price == ""){
                        jQuery(".tatilsepeti").hide();
                    }

                    else{
                        jQuery(".tatilsepeti").show();
                        jQuery("#hoteldigilab-tatilsepeti_price").text(data.tatilsepeti_price);
                    }

                    if(data.odamax_price == ""){
                        jQuery(".odamax").hide();
                    }

                    else{
                        jQuery(".odamax").show();
                        jQuery("#hoteldigilab-odamax_price").text(data.odamax_price);
                    }

                    if(data.hotels_price == ""){
                        jQuery(".hotelscom").hide();
                    }

                    else{
                        jQuery(".hotelscom").show();
                        jQuery("#hoteldigilab-hotels_price").text(data.hotels_price);
                    }

                    if(data.booking_price == ""){
                        jQuery(".bookingcom").hide();
                    }

                    else{
                        jQuery(".bookingcom").show();
                        jQuery("#hoteldigilab-booking_price").text(data.booking_price);
                    }
                }

                jQuery("#hoteldigilab-sabee_price").text(data.sabee_price);
                jQuery("#hoteldigilab-website-price").text(data.sabee_price);

            },
            complete: function(){
                jQuery('#hoteldigilab-loading').removeClass("hoteldigilab-loading");
            }
        });
    });

    mainWidgetOpened = 0;

    jQuery(document).on('click', '#hoteldigilab-reservation-button', function(){
        if(mainWidgetOpened == 0){
            jQuery('.hoteldigilab-widget').animate({'right': '0px'}, 1000);
            mainWidgetOpened = 1;
        }

        else{
            jQuery('.hoteldigilab-widget').animate({'right': '-275px'}, 1000);
            mainWidgetOpened = 0;
        }
    });

    jQuery(document).on('click', '.hoteldigilab-close-exit-widget', function(){

        jQuery('.hoteldigilab-exit-widget').hide();
        jQuery('.hoteldigilab-exit-background-cover').hide();


    });

    exitWidgetOpened = 0;

    jQuery(document).on('mouseover', '.hoteldigilab-top-exit-trigger', function(){
        if(exitWidgetOpened == 0){
            jQuery('.hoteldigilab-exit-widget').show();
            jQuery('.hoteldigilab-exit-background-cover').show();
            exitWidgetOpened = 1;
        }
    });

});



