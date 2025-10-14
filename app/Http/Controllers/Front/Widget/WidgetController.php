<?php

namespace App\Http\Controllers\Front\Widget;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Statistic;
use App\Models\Widget;
use Carbon\Carbon;
use Faker\Calculator\Iban;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WidgetController extends Controller
{
  public function fixedWidget($code)
  {
    //return Response::make('// widget disabled', 200)
    //    ->header('Content-Type', 'application/javascript');

    $script = File::get(resource_path('js/widgets/fixed.js'));

    $widget = Widget::all()
      ->where("code", "=", $code)
      ->where("type", "=", "main")
      ->where("language_id", 1)
      ->first();

    $hotel = $widget->hotel()->first();

    switch ($hotel->opening_language) {
      case "auto":
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        break;
      case "native":
        $lang = "tr";
        break;
      case "english":
        $lang = "en";
    }

    if ($lang == "tr") {
      $mainWidget = $widget;
      $exitWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "exit")
        ->where("language_id", 1)
        ->first();
      $mainTitle = "Fiyat Karşılaştırın!";
      $websiteText = "Otelin Fiyatı";
      $selectYourDatesText = "Tarih Seç & Rezervasyon Yap";
      $findOut = "Neden Fiyat Karşılaştırmalısınız?";
      $noAvaliability =
        "Ne yazık ki seçili tarihler için müsaitlik bulunmamaktadır. Lütfen arama kriterlerini değiştiriniz ve tekrar sorgulayınız.";
      $search = "Arama";
    } else {
      $mainWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "main")
        ->where("language_id", 2)
        ->first();
      $exitWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "exit")
        ->where("language_id", 2)
        ->first();
      $mainTitle = "Compare Prices!";
      $websiteText = "Official Website";
      $selectYourDatesText = ""; //"Choose a Date & Make a Reservation";
      $findOut = ""; //"Why to use Price Comparison?";
      $noAvaliability =
        "Unfortunately, there is no availability for the selected dates. Please change your criterias and search again.";
      $search = "Search";
    }

    $currencies = Currency::all();

    $exitWidgetHtml = view('front.widget.exit-widget')
      ->with("exit_widget", $exitWidget)
      ->with("hotel", $hotel);

    $fixedWidgetHtml = view('front.widget.fixed-widget')
      ->with("main_widget", $mainWidget)
      ->with("exit_widget", $exitWidget)
      ->with("hotel", $hotel)
      ->with("mainTitle", $mainTitle)
      ->with("websiteText", $websiteText)
      ->with("currencies", $currencies)
      ->with("findOut", $findOut)
      ->with("selectYourDatesText", $selectYourDatesText)
      ->with("search", $search)
      ->with("noAvailability", $noAvaliability)
      ->with("lang", $lang);

    // replace tokens in js file with appropriate values
    // prepare the tokens
    $tokens = [
      '$SHOW_ON_MOBILE$' => $mainWidget->show_mobile,
      '$CSS_URL$' => asset("assets/common/css/fixed-widget.css"),
      '$DATETIMEPICKER_CSS_URL$' => asset(
        "assets/common/plugins/datetimepicker/css/bootstrap-datetimepicker.min.css"
      ),
      '$DATEPICKER_CSS_URL$' => asset(
        "assets/common/plugins/datepicker/datepicker.min.css"
      ),
      '$EXIT_WIDGET_ACTIVE$' => $exitWidget->is_active,
      '$MAIN_WIDGET_ACTIVE$' => $mainWidget->is_active,
      '$MINIMUM_STAY$' => $mainWidget->minimum_stay,
      '$SABEE_URL$' => $hotel->sabee_url,
      '$ENDPOINT_URL$' => url("/") . '/widgetAjaxRequest/' . $mainWidget->code,
      '$ACTIVATION_DATE$' => $mainWidget->activation_date,
      '$FIXED_WIDGET_HTML$' => $fixedWidgetHtml,
      '$EXIT_WIDGET_HTML$' => $exitWidgetHtml,
    ];

    // replace the text content
    $script = str_replace(array_keys($tokens), array_values($tokens), $script);

    return Response::make($script, 200)->header(
      'Content-Type',
      'application/javascript'
    );
  }

  public function widget($code)
  {
    $widget = Widget::all()
      ->where("code", "=", $code)
      ->where("type", "=", "main")
      ->where("language_id", 1)
      ->first();

    $hotel = $widget->hotel()->first();

    switch ($hotel->opening_language) {
      case "auto":
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        break;
      case "native":
        $lang = "tr";
        break;
      case "english":
        $lang = "en";
    }

    if ($lang == "tr") {
      $mainWidget = $widget;
      $exitWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "exit")
        ->where("language_id", 1)
        ->first();
      $websiteText = "Resmi Web Sitesi";
      $selectYourDatesText = "Tarihlerinizi Seçin & Arayın";
      $findOut = "Neden Doğrudan Rezervasyon?";
      $noAvaliability =
        "Ne yazık ki seçili tarihler için müsaitlik bulunmamaktadır ya da minimum geceleme kısıtlaması söz konusudur. Lütfen arama kriterlerini değiştiriniz ve tekrar sorgulayınız.";
      $search = "Arama";
    } else {
      $mainWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "main")
        ->where("language_id", 2)
        ->first();
      $exitWidget = Widget::all()
        ->where("code", "=", $code)
        ->where("type", "=", "exit")
        ->where("language_id", 2)
        ->first();
      $websiteText = "Official Website";
      $selectYourDatesText = "Select Your Dates & Search";
      $findOut = "Find Out Why Booking Direct!";
      $noAvaliability =
        "Unfortunately, there is no availability or there might be a minimum stay restriction for the selected dates. Please change your criterias and search again.";
      $search = "Search";
    }

    $currencies = Currency::all();

    $js_content = view('front.widget.widget')
      ->with("main_widget", $mainWidget)
      ->with("exit_widget", $exitWidget)
      ->with("hotel", $hotel)
      ->with("websiteText", $websiteText)
      ->with("currencies", $currencies)
      ->with("findOut", $findOut)
      ->with("selectYourDatesText", $selectYourDatesText)
      ->with("search", $search)
      ->with("noAvailability", $noAvaliability)
      ->with("lang", $lang);

    return Response::make($js_content, 200)->header(
      'Content-Type',
      'application/javascript'
    );
  }

  public function ajaxRequest(Request $request, $code)
  {
    $mainWidget = Widget::all()
      ->where("code", "=", $code)
      ->where("type", "=", "main")
      ->first();

    $statistic = new Statistic();

    $startDate = date(
      "Y-m-d",
      strtotime(str_replace('/', '-', $request->startDate))
    );
    $endDate = date(
      "Y-m-d",
      strtotime(str_replace('/', '-', $request->endDate))
    );

    $statistic->widget_code = $code;
    $statistic->ip = '127.0.0.1';
    $statistic->type = $request->type;
    $statistic->arrival = $startDate
      ? $startDate
      : Carbon::now()->format('Y-m-d H:i:s');
    $statistic->departure = $endDate
      ? $endDate
      : Carbon::tomorrow()->format('Y-m-d H:i:s');
    $statistic->ip = $_SERVER['REMOTE_ADDR'];
    $statistic->result = $_SERVER['REMOTE_ADDR'];

    $widgetType = $request->widgetType ?: 'private';

    $ip = $_SERVER['REMOTE_ADDR'];
    $c = Statistic::where('ip', $ip)
      ->select("country")
      ->first();

    if ($c && $c['country']) {
      $statistic->country = $c['country'];
    } else {
      $geoLoc = json_decode(
        file_get_contents("http://www.geoplugin.net/json.gp?ip=$ip")
      );
      $statistic->country = $geoLoc->geoplugin_countryName;
    }

    $hotel = $mainWidget->hotel()->first();

    $ibePrice = "";
    $bookingPrice = "";
    $bookingUrl = "";
    $hotelsPrice = "";
    $tatilsepetiPrice = "";
    $odamaxPrice = "";
    $odamaxUrl = "";
    $otelzPrice = "";
    $otelzUrl = "";
    $etsturPrice = "";
    $etsturURL = "";
    $ibeUrl = "";
    $defaultIbe = $hotel->default_ibe;

    switch ($defaultIbe) {
      case "sabeeapp":
        if ($hotel->sabee_is_active && $hotel->sabee_hotel_id != "") {
          $ibePrice = getSabeeRoomsPrice(
            $hotel->sabee_hotel_id,
            $request->currency,
            $startDate,
            $endDate
          );
          $ibeUrl = $hotel->sabee_url;
        }
        break;
      case "reseliva":
        if ($hotel->reseliva_is_active && $hotel->reseliva_hotel_id != "") {
          $reseliva = getReselivaPriceApi(
            $hotel->reseliva_hotel_id,
            $request->currency,
            $startDate,
            $endDate
          );
          if (!isset($reseliva["price"])) {
            $ibePrice = "";
            $ibeUrl = "";
          } else {
            $ibePrice = $reseliva["price"];
            $ibeUrl = $reseliva["url"];
          }
        }
        break;
      case "hotelrunner":
        if ($hotel->is_hotelrunner_active && $hotel->hotelrunner_url != "") {
          $hr = getHotelRunnerPrice(
            $hotel->hotelrunner_url,
            $request->currency,
            $startDate,
            $endDate
          );
          if ($hr == "NA") {
            $ibeUrl = "";
            $ibePrice = "NA";
          } else {
            $ibePrice = $hr["price"];
            $ibeUrl = $hr["url"];
          }
        }
        break;
    }

    if ($hotel->booking_is_active) {
      $bookingPrice = $hotel->booking_url
        ? getBookingPrice(
          $hotel->booking_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "NA";
      $bookingUrl = $hotel->booking_url
        ? getBookingUrl(
          $hotel->booking_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
    }

    if ($hotel->hotels_is_active) {
      $hotelsPrice = $hotel->hotels_url
        ? getHotelsPrice(
          $hotel->hotels_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
    }

    if ($hotel->tatilsepeti_is_active) {
      $tatilsepetiPrice = $hotel->tatilsepeti_url
        ? getTatilSepetiPrice(
          $hotel->tatilsepeti_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
    }

    if ($hotel->odamax_is_active) {
      $odamaxPrice = $hotel->odamax_url
        ? getOdamaxPrice(
          $hotel->odamax_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
      $odamaxUrl = getOdamaxUrl(
        $hotel->odamax_url,
        $request->currency,
        $startDate,
        $endDate
      );
    }

    if ($hotel->otelz_is_active) {
      $otelzPrice = $hotel->otelz_url
        ? getOtelZPrice(
          $hotel->otelz_url,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
      $otelzUrl = getOtelzUrl(
        $hotel->otelz_url,
        $request->currency,
        $startDate,
        $endDate
      );
    }

    if ($hotel->is_etstur_active) {
      $etstur = $hotel->etstur_hotel_id
        ? getETSTurPrice(
          $hotel->etstur_hotel_id,
          $request->currency,
          $startDate,
          $endDate
        )
        : "";
      $etsturPrice = $etstur["price"];
      $etsturURL = $etstur["url"];
    }

    $availability = false;
    $statistic->result = 0;
    $channels = [
      $ibePrice,
      $bookingPrice,
      $hotelsPrice,
      $otelzPrice,
      $etsturPrice,
      $tatilsepetiPrice,
      $odamaxPrice,
    ];
    foreach ($channels as $c) {
      if ($c != "NA" and $c != "") {
        $availability = true;
        $statistic->result = 1;
        break;
      }
    }

    $statistic->save();

    $channelPrices = [
      "availability" => $availability,
      "ibe" => [
        "defaultIbe" => $defaultIbe,
        "price" => $ibePrice,
        "url" => $ibeUrl,
      ],
      "booking" => ["price" => $bookingPrice, "url" => $bookingUrl],
      "hotels" => ["price" => $hotelsPrice, "url" => $hotel->hotels_url],
      "otelz" => ["price" => $otelzPrice, "url" => $otelzUrl],
      "etstur" => ["price" => $etsturPrice, "url" => $etsturURL],
      "tatilsepeti" => [
        "price" => $tatilsepetiPrice,
        "url" => $hotel->tatilsepeti_url,
      ],
      "odamax" => ["price" => $odamaxPrice, "url" => $odamaxUrl],
    ];

    return $channelPrices;
  }
}
