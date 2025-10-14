
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
jQuery('.hoteldigilab-loading').addClass("hoteldigilab-loading");
jQuery('.hoteldigilab-loading-text').addClass("hoteldigilab-loading-text");
},
data: {
_token: '{{ csrf_token() }}',
startDate: startDateVar,
endDate: endDateVar,
currency: currencyVar,
type: typeVar,
widgetType: "fixed"
},
success: function (data) {

if (!data.availability) {
jQuery(".no-availability").show();
jQuery(".hoteldigilab-channel-prices").hide();
} else {


link = "{{ $hotel->sabee_url }}";

if (link.includes("reseliva.com")) {
jQuery(".sabee-link").attr('href', "{{ $hotel->sabee_url }}?pCheckInDate=" + getFormattedDate(startDateVar) + "&pCheckOutDate=" + getFormattedDate(endDateVar));
} else {
jQuery(".sabee-link").attr('href', "{{ $hotel->sabee_url }}&source=HotelDigilabWidget&checkin=" + startDateVar + "&checkout=" + endDateVar);
}


jQuery(".no-availability").hide();
jQuery(".hoteldigilab-channel-prices").show();

if (data.tatilsepeti_price == "") {
jQuery(".tatilsepeti").hide();
} else {
jQuery(".tatilsepeti").show();
jQuery(".hoteldigilab-tatilsepeti_price").text(data.tatilsepeti_price);
}

if (data.odamax_price == "") {
jQuery(".odamax").hide();
} else if (data.odamax_price == null) {
jQuery(".odamax").hide();
} else {
jQuery(".odamax").show();
jQuery(".hoteldigilab-odamax_price").text(data.odamax_price);
}

if (data.reseliva_price == "") {
jQuery(".reseliva").hide();
} else {
jQuery(".reseliva").show();
jQuery(".hoteldigilab-reseliva_price").text(data.reseliva_price);
}

if (data.hotels_price == "") {
jQuery(".hotelscom").hide();
} else {
jQuery(".hotelscom").show();
jQuery(".hoteldigilab-hotels_price").text(data.hotels_price);
}

if (data.booking_price == "") {
jQuery(".bookingcom").hide();
} else {
jQuery(".bookingcom").show();
jQuery(".hoteldigilab-booking_price").text(data.booking_price);
}

}

jQuery(".hoteldigilab-sabee_price").text(data.sabee_price);
jQuery(".hoteldigilab-website-price").text(data.sabee_price);
jQuery(".hoteldigilab-booking_price").text(data.booking_price);
if (typeVar == "currencyChange") {
jQuery(".hoteldigilab-price_currency").text(" " + jQuery('.hoteldigilab-currencies').val());
}

},
complete: function () {
jQuery('.hoteldigilab-loading').removeClass("hoteldigilab-loading");
jQuery('.hoteldigilab-loading-text').removeClass("hoteldigilab-loading-text");
}
});
}


/******** Our main function ********/
function main() {
jQuery(document).ready(function () {

/******* Load CSS *******/
var css_link = jQuery("<link>", {
rel: "stylesheet",
type: "text/css",
href: "{{ asset("assets/common/css/fixed-widget.css") }}"
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
        <div class="hoteldigilab-exit-widget-bottom-button" style="background-color: #3696C9">
            {{ $exit_widget->reservation_button_text }}
        </div>
    </a>
</div>`);

}
}

if (showMobile) {
if (mainWidget == true) {

if ({{ $main_widget->is_active }}) {


jQuery('.hoteldigilab-kucukoteller-widget').append(`

<div class="hoteldigilab-widget" style="font-family: '{{ $main_widget->font }}', sans-serif !important;">
    <div class="hoteldigilab-main">
        <div class="hoteldigilab-loading-text" style="display: none;">
            Sizin için en iyi fiyatları arıyoruz...
        </div>
        <div class="hoteldigilab-loading">

        </div>
        <div class="hoteldigilab-red-area" style="background-color: #3696C9">
            {{--            <div class="hoteldigilab-logo">--}}
            {{--                <img src="{{ asset("assets/common/img/loqqo.png") }}">--}}
            {{--            </div>--}}
            <div class="hoteldigilab-flex hoteldigilab-align-items-center hoteldigilab-justify-content-between">
                <div class="hoteldigilab-fixed-main-title">
                    {{ $mainTitle }}
                </div>
                <div class="hoteldigilab-currency">
                    <select class="hoteldigilab-currencies"
                            style="-webkit-appearance: none; -moz-appearance: none; -appearance: none; background-image: none;">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->name }}" {{ $main_widget->currency_id == $currency->id ? 'selected="selected"' : "" }}>{{ $currency->symbol }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <a href="{{ sabeeUrlLanguageChange($lang, $hotel->sabee_url) }}" target="_blank" class="sabee-link">
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
                    <input type="date" format="DD-MM-YYYY" placeholder="Giriş Tarihi" class="hoteldigilab-startDate"
                           style="cursor: pointer;">
                    <input type="date" format="DD-MM-YYYY" placeholder="Çıkış Tarihi" class="hoteldigilab-endDate"
                           style="cursor: pointer;">
                    <button type="submit" class="hoteldigilab-dateSubmit hoteldigilab-dateSubmitBtn"
                            title="{{ $search }}"><i class="zmdi zmdi-search"></i></button>
                </div>
            </div>


            <div class="no-availability" style="display:none;">
                {{ $noAvailability }}
            </div>
            <div class="hoteldigilab-channel-prices" style="display: none">
                {{--------------------------------------------------------------------------------------- sabee --}}
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

                {{-------------------------------------------------------------------------------------- odamax --}}
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


                {{-------------------------------------------------------------------------- otelz will be here --}}


                {{--------------------------------------------------------------------------------- tatilsepeti --}}
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


                {{------------------------------------------------------------------------------------- booking --}}
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

                {{-------------------------------------------------------------------------------------- hotels --}}
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

                {{------------------------------------------------------------------------------------- resliva --}}
                @if($hotel->reseliva_is_active)
                    <div class="hoteldigilab-price-from reseliva">
                        <div class="hoteldigilab-from-logo">
                            <img src="{{ asset("assets/common/img/website-icons/reseliva.ico") }}">
                        </div>
                        <div class="hoteldigilab-from-text">Reseliva.Com</div>
                        <div class="hoteldigilab-from-price">
                            <span class="hoteldigilab-reseliva_price"></span>
                            <span class="hoteldigilab-price_currency"> TRY</span>
                        </div>
                    </div>
                    <div class="hoteldigilab-clear"></div>
                @endif

                {{------------------------------------------------------------------------------------------------}}
            </div>

        </div>
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

jQuery('.hoteldigilab-startDate').val(date.toISOString().substr(0, 10));
jQuery('.hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));

jQuery(".hoteldigilab-price_currency").text(" " + jQuery('.hoteldigilab-currencies').val());

ajaxCall(jQuery('.hoteldigilab-startDate').val(), jQuery('.hoteldigilab-endDate').val(), jQuery('.hoteldigilab-currencies').val(), "pageLoad");

jQuery('.hoteldigilab-startDate').change(function () {
var tomorrow = new Date(this.value);
tomorrow.setDate(tomorrow.getDate() + {{ $main_widget->minimum_stay }})
jQuery('.hoteldigilab-endDate').val(tomorrow.toISOString().substr(0, 10));
});


jQuery('.hoteldigilab-currencies').change(function () {
ajaxCall(jQuery('.hoteldigilab-startDate').val(), jQuery('.hoteldigilab-endDate').val(), jQuery('.hoteldigilab-currencies').val(), "currencyChange");
});

jQuery('.hoteldigilab-dateSubmit').click(function () {
ajaxCall(jQuery('.hoteldigilab-startDate').val(), jQuery('.hoteldigilab-endDate').val(), jQuery('.hoteldigilab-currencies').val(), "dateSearch");
});

mainWidgetOpened = 0;

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