{{ asd }}

(function () {

// Localize jQuery variable
    var jQuery;

    /******** Load jQuery if not present *********/
    if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.4.2') {
        var script_tag = document.createElement('script');
        script_tag.setAttribute("type", "text/javascript");
        script_tag.setAttribute("src",
            "https://code.jquery.com/jquery-3.3.1.min.js");
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
        main();
    }

    /******** Called once jQuery has loaded ******/
    function scriptLoadHandler() {
// Restore $ and window.jQuery to their previous values and store the
// new jQuery in our local jQuery variable
        jQuery = window.jQuery.noConflict(true);
// Call our main function
        main();
    }


    /******** Our main function ********/
    function main() {
        jQuery(document).ready(function ($) {


            /******* Load CSS *******/
            var css_link = $("<link>", {
                rel: "stylesheet",
                type: "text/css",
                href: "{{ asd }}"
        })
            ;
            css_link.appendTo('head');

            /******* Load HTML *******/

            $('body').prepend(`<div class="widget">

        <div class="reservation-button" id="reservation-button">
            <div class="reservation-button-text">{{  }}</div>
            <div class="reservation-button-img">
                <img src="{{ asset("assets/common/img/reservation-img.png") }}" />
            </div>
        </div>
    <div class="main">
        <div id="loading"></div>
        <div class="red-area">
            <div class="logo">
                <img src="http://hoteldigilab-old/dashboard/include/docs/images/loqqo.png">
            </div>
            <div class="currency">
                <select id="currencies">
                    <option value="TRY">TRY</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                </select>
            </div>
            <div class="clear"></div>

            <div class="main-title">
                {{ $main_widget->main_title }}
            </div>

            <hr/>

            <a href="#">
                <div class="discounted-rate-btn">
                    {{ $main_widget->direct_reservation_text }} <span id="sabee_price"></span> TRY
                </div>
            </a>

            <div class="reservation-text">
                Find Out Why Booking Direct!
            </div>

            <div class="promoted-text">
                {{ $main_widget->features_text }}
            </div>
        </div>

        <div class="bottom-area">

            <div class="date">
                <input type="date" format="Y-m-d" placeholder="Giriş Tarihi" value="{{ date('Y-m-d') }}" id="startDate" style="cursor: pointer;">
                <input type="date" format="Y-m-d" placeholder="Çıkış Tarihi" value="{{ date("Y-m-d", strtotime("+1 day")) }}" id="endDate" style="cursor: pointer;">
                <button type="submit" id="dateSubmit"><i class="zmdi zmdi-check"></i></button>
            </div>

            <div class="price-from">
                <div class="from-logo"><img src="{{ asset("assets/common/img/website-icons/booking.ico") }}"></div>
                <div class="from-text">Booking.com</div>
                <div class="from-price"><span id="booking_price"></span><span class="price_currency">TRY</span></div>
            </div>

            <div class="clear"></div>

            <div class="price-from">
                <div class="from-logo"><img src="{{ asset("assets/common/img/website-icons/hotels.ico") }}"></div>
                <div class="from-text">Hotels.com</div>
                <div class="from-price"><span id="hotels_price"></span><span class="price_currency">TRY</span></div>
            </div>

        </div>
    </div>
</div>`);
        });


    }

})(); // We call our anonymous function immediately


$('document').ready(function () {

    $('.widget').animate({'right': '0px'}, 1000).delay(5000).animate({'right': '-300px'}, 1000);

    $(document).on('change', '#currencies', function () {
        $.ajax({
            type: 'POST',
            url: '/widgetAjaxRequest/{{ $main_widget->code }}',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#loading').addClass("loading");
            },
            data: {startDate: $('#startDate').val(), endDate: $('#endDate').val(), currency: $('#currencies').val()},
            success: function (data) {
                $("#sabee_price").text(data.sabee_price);
                $("#booking_price").text(data.booking_price);
                $("#hotels_price").text(data.hotels_price);
                $(".price_currency").text($('#currencies').val());
            },
            complete: function () {
                $('#loading').removeClass("loading");
            }
        });
    });

    $(document).on('click', '#dateSubmit', function () {

        $.ajax({
            type: 'POST',
            url: '/widgetAjaxRequest/{{ $main_widget->code }}',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $('#loading').addClass("loading");
            },
            data: {startDate: $('#startDate').val(), endDate: $('#endDate').val(), currency: $('#currencies').val()},
            success: function (data) {
                $("#sabee_price").text(data.sabee_price);
                $("#booking_price").text(data.booking_price);
                $("#hotels_price").text(data.hotels_price);
            },
            complete: function () {
                $('#loading').removeClass("loading");
            }
        });
    });

    open = 0;

    $(document).on('click', '#reservation-button', function () {
        if (open == 0) {
            $('.widget').animate({'right': '0px'}, 1000);
            open = 1;
        }

        else {
            $('.widget').animate({'right': '-300px'}, 1000);
            open = 0;
        }
    });

});