(function() {

    let showOnMobile = '$SHOW_ON_MOBILE$'

    // Localize jQuery variable
    var jQuery
    var dateFormatted = 0

    /******** Load jQuery if not present *********/
    if (typeof jQuery == 'undefined') {
        var script_tag = document.createElement('script')
        script_tag.setAttribute('type', 'text/javascript')
        script_tag.setAttribute('src',
            'https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js')
        if (script_tag.readyState) {
            script_tag.onreadystatechange = function() { // For old versions of IE
                if (this.readyState === 'complete' || this.readyState === 'loaded') {
                    scriptLoadHandler()
                }
            }
        } else {
            script_tag.onload = scriptLoadHandler
        }
        // Try to find the head, otherwise default to the documentElement
        (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(script_tag)
    } else {
        // The jQuery version on the window is the one we want to use
        jQuery = window.jQuery
    }


    /******** Called once jQuery has loaded ******/
    function scriptLoadHandler() {
        // Restore $ and window.jQuery to their previous values and store the
        // new jQuery in our local jQuery variable
        jQuery = window.jQuery.noConflict(true)
        // Call our main function

        jQuery.ajax({
            url     : 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js',
            dataType: 'script',
            async   : false,
        })

        jQuery.ajax({
            url     : 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.9.2/i18n/jquery.ui.datepicker-tr.min.js',
            dataType: 'script',
            async   : false,
        })

        if (typeof loaded == 'undefined') {
            main()
            loaded = true
        }

    }

    /* String Parameter */
    function getFormattedDate(dateParam) {
        date      = new Date(dateParam)
        let year  = date.getFullYear()
        let month = (1 + date.getMonth()).toString().padStart(2, '0')
        let day   = date.getDate().toString().padStart(2, '0')

        return day + '/' + month + '/' + year
    }

    function sleep(milliseconds) {
        const date      = Date.now()
        let currentDate = null
        do {
            currentDate = Date.now()
        } while (currentDate - date < milliseconds)
    }


    function ajaxCall(startDateVar, endDateVar, currencyVar, typeVar) {
        jQuery.ajax({
            type      : 'POST',
            url       : '$ENDPOINT_URL$',
            headers   : {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            dataType  : 'json',
            beforeSend: function() {
                withCredentials = true
                jQuery('.hoteldigilab-loading-out').addClass('hoteldigilab-loading')
                jQuery('.hoteldigilab-loading-text-out').addClass('hoteldigilab-loading-text')
            },
            data      : {
                _token    : '{{ csrf_token() }}',
                startDate : startDateVar,
                endDate   : endDateVar,
                currency  : currencyVar,
                type      : typeVar,
                widgetType: 'fixed',
            },
            success   : function(data) {

                let min_price = data.ibe.price

                if (!data.availability) {
                    jQuery('.no-availability').show()
                    jQuery('.hoteldigilab-channel-prices').hide()
                }

                else {
                    jQuery('.no-availability').hide()
                    jQuery('.hoteldigilab-channel-prices').show()

                    let link = '$SABEE_URL$'

                    if (data.ibe.price == "") {
                        jQuery('.sabee').hide()
                    }

                    else{
                        jQuery('.sabee').show()
                        jQuery('.hoteldigilab-website-price').text(data.ibe.price)
                        jQuery('.hoteldigilab-sabee-price-url').attr('href', data.ibe.url)
                        jQuery('.sabee-link').attr('href', data.ibe.url)
                    }

                    if (data.booking.price == "NA" || data.booking.price == "") {
                        jQuery('.bookingcom').hide()
                    } else {
                        if (!min_price || min_price > data.booking.price) {
                            min_price = data.booking.price
                            jQuery('.sabee-link').attr('href', data.booking.url + '&aid=1939100')
                        }

                        jQuery('.bookingcom').show()
                        jQuery('.hoteldigilab-booking_price').text(data.booking.price)
                        jQuery('.hoteldigilab-booking-price-url').attr('href', data.booking.url + '&aid=1939100')
                    }

                    if (data.etstur.price == "" || data.etstur.price == "NA") {
                        jQuery('.etstur').hide()
                    }

                    else {
                        if (!min_price || min_price > data.etstur.price) {
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
                        min_price = data.tatilsepeti.price

                        jQuery('.tatilsepeti').show()
                        jQuery('.hoteldigilab-tatilsepeti_price').text(data.tatilsepeti.price)
                    }

                    if (data.otelz.price == "" || data.otelz.price == "NA") {
                        jQuery('.otelz').hide()
                    } else {
                        if (!min_price || min_price > data.otelz.price) {
                            min_price = data.otelz.price
                            jQuery('.sabee-link').attr('href', data.otelz.url)
                        }

                        jQuery('.otelz').show()
                        jQuery('.hoteldigilab-otelz_price').text(data.otelz.price)
                        jQuery('.hoteldigilab-otelz-price-url').attr('href', data.otelz.url)

                    }

                    if (data.odamax.price == "" || data.odamax.price == "NA") {
                        jQuery('.odamax').hide()
                    } else {
                        if (!min_price || min_price > data.odamax.price) {
                            min_price = data.odamax.price
                            jQuery('.sabee-link').attr('href', data.odamax.price)
                        }


                        jQuery('.odamax').show()
                        jQuery('.hoteldigilab-odamax_price').text(data.odamax.price)
                        jQuery('.hoteldigilab-odamax-price-url').attr('href', data.odamax.url)
                    }

                    if (data.hotels.price == "" || data.hotels.price == "NA") {
                        jQuery('.hotelscom').hide()
                    } else {
                        if (!min_price || min_price > data.hotels.price)
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
            complete  : function() {
                jQuery('.hoteldigilab-loading-out').removeClass('hoteldigilab-loading')
                jQuery('.hoteldigilab-loading-text-out').removeClass('hoteldigilab-loading-text')

                if (dateFormatted == 0) {
                    var language = navigator.language || navigator.userLanguage


                    if (language == 'tr-TR') {
                        window.jQuery('#hoteldigilab-startdate').datepicker(window.jQuery.datepicker.regional['tr'])
                        window.jQuery('#hoteldigilab-enddate').datepicker(window.jQuery.datepicker.regional['tr'])
                    }

                    window.jQuery('#hoteldigilab-startdate').datepicker()
                    window.jQuery('#hoteldigilab-enddate').datepicker()

                    let startDate = new Date(startDateVar)
                    let endDate   = new Date(endDateVar)
                    window.jQuery('#hoteldigilab-startdate').datepicker('setDate', startDate)
                    window.jQuery('#hoteldigilab-startdate').datepicker('option', 'dateFormat', 'dd/mm/yy')
                    window.jQuery('#hoteldigilab-enddate').datepicker('setDate', endDate)
                    window.jQuery('#hoteldigilab-enddate').datepicker('option', 'dateFormat', 'dd/mm/yy')

                    dateFormatted = 1
                }
            },
        })
    }


    /******** Our main function ********/
    function main() {
        jQuery(document).ready(function() {


            let css_link


            /******* Load CSS *******/
            css_link = jQuery('<link>', {
                rel : 'stylesheet',
                type: 'text/css',
                href: '$CSS_URL$',
            })
            css_link.appendTo('head')

            /******* Load CSS *******/
            css_link = jQuery('<link>', {
                rel : 'stylesheet',
                type: 'text/css',
                href: 'https://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css',
            })
            css_link.appendTo('head')

            /******* Load CSS *******/
            css_link = jQuery('<link>', {
                rel : 'stylesheet',
                type: 'text/css',
                href: 'https://fonts.googleapis.com/css?family=Abel|Cairo|Dosis|Open+Sans+Condensed:300|PT+Sans+Narrow|Rajdhani|Roboto|Roboto+Condensed|Saira+Semi+Condensed|Teko&display=swap',
            })
            css_link.appendTo('head')

            /******* Load CSS *******/
            css_link = jQuery('<link>', {
                rel : 'stylesheet',
                type: 'text/css',
                href: 'https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.css',
            })
            css_link.appendTo('head')

            /******* Load HTML *******/
            if (exitWidget) {
                if ($EXIT_WIDGET_ACTIVE$) {
                    jQuery('body').append(`$EXIT_WIDGET_HTML$`)
                }
            }

            /******* Load HTML *******/
            if (showOnMobile) {
                if (mainWidget) {
                    if ($MAIN_WIDGET_ACTIVE$) {
                        jQuery('.hoteldigilab-kucukoteller-widget').append(`$FIXED_WIDGET_HTML$`)
                    }
                }
            }

            let activation_date = '$ACTIVATION_DATE$'
            let date            = new Date()
            if (activation_date) {
                let dateParts          = activation_date.split('-')
                let activation_date_js = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substring(0, 2))
                date                   = activation_date_js > date ? activation_date_js : date
            }

            var tomorrow = new Date(date)
            date.setHours(date.getHours() + 3)
            tomorrow.setHours(tomorrow.getHours() + 3)
            tomorrow.setDate(tomorrow.getDate() + $MINIMUM_STAY$)

            let $startDate = jQuery('.hoteldigilab-startDate')
            let $endDate   = jQuery('.hoteldigilab-endDate')

            // window.jQuery("#hoteldigilab-startdate").datepicker('setDate', date);
            // window.jQuery("#hoteldigilab-enddate").datepicker('setDate', tomorrow.toISOString().substr(0, 10));

            $startDate.val(date.toISOString().substring(0, 10))
            $endDate.val(tomorrow.toISOString().substring(0, 10))

            // console.log(tomorrow);

            jQuery('.hoteldigilab-price_currency').text(' ' + jQuery('.hoteldigilab-currencies').val())

            ajaxCall($startDate.val(), $endDate.val(), jQuery('.hoteldigilab-currencies').val(), 'pageLoad')

            window.jQuery('#hoteldigilab-startdate').datepicker(
                {
                    onSelect: function(dateText) {
                        let newStartDate = dateText
                        var dateParts    = newStartDate.split('/')
                        let tomorrow     = new Date(+dateParts[2], dateParts[1] - 1, +dateParts[0])
                        $startDate.val(newStartDate)

                        tomorrow.setDate(tomorrow.getDate() + $MINIMUM_STAY$)
                        window.jQuery('#hoteldigilab-enddate').datepicker('setDate', tomorrow)
                    },
                },
            )

            //old one

            // $startDate.change(function (e) {
            //     let newStartDate = $(e.currentTarget).val()
            //     let tomorrow = new Date(newStartDate)
            //     $startDate.val(newStartDate)
            //
            //     tomorrow.setDate(tomorrow.getDate() + $MINIMUM_STAY$)
            //     $endDate.datepicker('setDate', tomorrow);
            //     // $endDate.val(tomorrow.toISOString().substr(0, 10))
            // })

            jQuery('.hoteldigilab-currencies').change(function() {
                ajaxCall($startDate.val(), $endDate.val(), jQuery('.hoteldigilab-currencies').val(), 'currencyChange')
            })

            jQuery('.hoteldigilab-dateSubmit').click(function() {
                ajaxCall($startDate.val(), $endDate.val(), jQuery('.hoteldigilab-currencies').val(), 'dateSearch')
            })

            mainWidgetOpened = 0

            jQuery('.hoteldigilab-close-exit-widget').click(function() {
                jQuery('.hoteldigilab-exit-widget').hide()
                jQuery('.hoteldigilab-exit-background-cover').hide()
            })

            exitWidgetOpened = 0

            jQuery('.hoteldigilab-top-exit-trigger').mouseover(function() {
                if (exitWidgetOpened === 0) {
                    jQuery('.hoteldigilab-exit-widget').show()
                    jQuery('.hoteldigilab-exit-background-cover').show()
                    exitWidgetOpened = 1
                }
            })

        })

    }

})()
