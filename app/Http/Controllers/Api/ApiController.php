<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;

class ApiController extends Controller
{
  public function getRequest(Request $r, $widgetCode)
  {
    $currency = $r->currency;
    $checkIn = date("Y-m-d", strtotime(str_replace('/', '-', $r->checkin)));
    $checkOut = date("Y-m-d", strtotime(str_replace('/', '-', $r->checkout)));

    $main_widget = Widget::all()
      ->where("code", "=", $widgetCode)
      ->where("type", "=", "main")
      ->first();
    $hotel = $main_widget->hotel()->first();

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

    $response = [
      "status" => "success",
      "data" => [
        "platforms" => [],
      ],
    ];

    switch ($defaultIbe) {
      case "sabeeapp":
        if ($hotel->sabee_is_active && $hotel->sabee_hotel_id != "") {
          $ibePrice = getSabeeRoomsPrice(
            $hotel->sabee_hotel_id,
            $currency,
            $checkIn,
            $checkOut
          );
          $ibeUrl = $hotel->sabee_url;

          $data = [
            "status" => "success",
            "name" => $defaultIbe,
            "displayName" => $defaultIbe,
            "price" => $ibePrice,
            "url" => $ibeUrl,
          ];

          array_push($response["data"]["platforms"], $data);
        }
        break;
      case "reseliva":
        if ($hotel->reseliva_is_active && $hotel->reseliva_hotel_id != "") {
          $reseliva = getReselivaPriceApi(
            $hotel->reseliva_hotel_id,
            $currency,
            $checkIn,
            $checkOut
          );
          if ($reseliva == "NA") {
            $ibeUrl = "";
            $ibePrice = "NA";
          } else {
            $ibePrice = $reseliva["price"];
            $ibeUrl = $reseliva["url"];
          }

          $data = [
            "status" => "success",
            "name" => $defaultIbe,
            "displayName" => $defaultIbe,
            "price" => $ibePrice,
            "url" => $ibeUrl,
          ];

          array_push($response["data"]["platforms"], $data);
        }
        break;
      case "hotelrunner":
        if ($hotel->is_hotelrunner_active && $hotel->hotelrunner_url != "") {
          $hr = getHotelRunnerPrice(
            $hotel->hotelrunner_url,
            $currency,
            $checkIn,
            $checkOut
          );
          if ($hr == "NA") {
            $ibeUrl = "";
            $ibePrice = "NA";
          } else {
            $ibePrice = $hr["price"];
            $ibeUrl = $hr["url"];

            $data = [
              "status" => "success",
              "name" => $defaultIbe,
              "displayName" => $defaultIbe,
              "price" => $ibePrice,
              "url" => $ibeUrl,
            ];

            array_push($response["data"]["platforms"], $data);
          }
        }
        break;
    }

    if ($hotel->booking_is_active) {
      $bookingPrice = $hotel->booking_url
        ? getBookingPrice($hotel->booking_url, $currency, $checkIn, $checkOut)
        : "NA";
      $bookingUrl = $hotel->booking_url
        ? getBookingUrl($hotel->booking_url, $currency, $checkIn, $checkOut)
        : "";

      $status = "success";

      if ($bookingPrice == "NA" or $bookingPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "booking",
        "displayName" => "Booking",
        "price" => $bookingPrice,
        "url" => $bookingUrl,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    if ($hotel->hotels_is_active) {
      $hotelsPrice = $hotel->hotels_url
        ? getHotelsPrice($hotel->hotels_url, $currency, $checkIn, $checkOut)
        : "";

      $status = "success";

      if ($hotelsPrice == "NA" or $hotelsPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "hotels",
        "displayName" => "Hotels",
        "price" => $hotelsPrice,
        "url" => $hotel->hotels_url,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    if ($hotel->tatilsepeti_is_active) {
      $tatilsepetiPrice = $hotel->tatilsepeti_url
        ? getTatilSepetiPrice(
          $hotel->tatilsepeti_url,
          $currency,
          $checkIn,
          $checkOut
        )
        : "";

      $status = "success";

      if ($tatilsepetiPrice == "NA" or $tatilsepetiPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "tatilsepeti",
        "displayName" => "Tatil Sepeti",
        "price" => $tatilsepetiPrice,
        "url" => $hotel->tatilsepeti_url,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    if ($hotel->odamax_is_active) {
      $odamaxPrice = $hotel->odamax_url
        ? getOdamaxPrice($hotel->odamax_url, $currency, $checkIn, $checkOut)
        : "";
      $odamaxUrl = getOdamaxUrl(
        $hotel->odamax_url,
        $currency,
        $checkIn,
        $checkOut
      );

      $status = "success";

      if ($odamaxPrice == "NA" or $odamaxPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "odamax",
        "displayName" => "Odamax",
        "price" => $odamaxPrice,
        "url" => $odamaxUrl,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    if ($hotel->otelz_is_active) {
      $otelzPrice = $hotel->otelz_url
        ? getOtelZPrice($hotel->otelz_url, $currency, $checkIn, $checkOut)
        : "";
      $otelzUrl = getOtelzUrl(
        $hotel->otelz_url,
        $currency,
        $checkIn,
        $checkOut
      );

      $status = "success";

      if ($otelzPrice == "NA" or $otelzPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "otelz",
        "displayName" => "OtelZ",
        "price" => $otelzPrice,
        "url" => $otelzUrl,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    if ($hotel->is_etstur_active) {
      $etstur = $hotel->etstur_hotel_id
        ? getETSTurPrice(
          $hotel->etstur_hotel_id,
          $currency,
          $checkIn,
          $checkOut
        )
        : "";
      $etsturPrice = $etstur["price"];
      $etsturURL = $etstur["url"];

      $status = "success";

      if ($etsturPrice == "NA" or $etsturPrice == "") {
        $status = "failed";
      }

      $data = [
        "status" => $status,
        "name" => "etstur",
        "displayName" => "ETSTur",
        "price" => $etsturPrice,
        "url" => $etsturURL,
      ];

      array_push($response["data"]["platforms"], $data);
    }

    $availability = false;
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
        break;
      }
    }

    return \Response::json($response);
  }

  public function getPrice(Request $r)
  {
    $main_widget = Widget::all()
      ->where("code", "=", $r->hotelCode)
      ->where("type", "=", "main")
      ->first();
    $hotel = $main_widget->hotel()->first();

    $response = [
      "currency" => $r->currency,
      "checkin" => $r->checkin,
      "checkout" => $r->checkout,
      "adults" => $r->adults,
    ];

    if (in_array("sabee", $r->type)) {
      $sabee_price = getSabeeRoomsPrice(
        $hotel->sabee_hotel_id,
        $r->currency,
        $r->startDate,
        $r->endDate
      );
      $response["prices"]["sabee"] = (int) $sabee_price;
    }

    if (in_array("booking", $r->type)) {
      $booking_price = $hotel->booking_url
        ? (getBookingPrice(
          $hotel->booking_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        ) ?:
        "NA")
        : "";
      $response["prices"]["booking"] = $booking_price;
    }

    if (in_array("hotels", $r->type)) {
      $hotels_price = $hotel->hotels_url
        ? getBookingPrice(
          $hotel->hotels_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $response["prices"]["hotels"] = $hotels_price;
    }

    if (in_array("tatilsepeti", $r->type)) {
      $tatilsepeti_price = $hotel->tatilsepeti_url
        ? getTatilSepetiPrice(
          $hotel->tatilsepeti_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $response["prices"]["tatilsepeti"] = $tatilsepeti_price;
    }

    if (in_array("odamax", $r->type)) {
      $odamax_price = $hotel->odamax_url
        ? getOdamaxPrice(
          $hotel->odamax_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $response["prices"]["odamax"] = $odamax_price;
    }

    if (in_array("otelz", $r->type)) {
      $otelz_price = $hotel->otelz_url
        ? getOtelZPrice(
          $hotel->otelz_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $response["prices"]["otelz"] = $otelz_price;
    }

    if (in_array("min", $r->type)) {
      $bookingPrice = $hotel->booking_url
        ? (getBookingPrice(
          $hotel->booking_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        ) ?:
        "NA")
        : "";
      $hotelsPrice = $hotel->hotels_url
        ? getHotelsPrice(
          $hotel->hotels_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $tatilsepetiPrice = $hotel->tatilsepeti_url
        ? getTatilSepetiPrice(
          $hotel->tatilsepeti_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $odamaxPrice = $hotel->odamax_url
        ? getOdamaxPrice(
          $hotel->odamax_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";
      $reseliva = $hotel->reseliva_hotel_id
        ? getReselivaPriceApi(
          $hotel->reseliva_hotel_id,
          $r->currency,
          $r->startDate,
          $r->endDate
        )["price"]
        : "";
      $reselivaPrice = isset($reseliva) ? $reseliva : null;
      $otelzPrice = $hotel->otelz_url
        ? getOtelZPrice(
          $hotel->otelz_url,
          $r->currency,
          $r->startDate,
          $r->endDate
        )
        : "";

      if (strpos($hotel->sabee_url, 'reseliva.com') !== false) {
        $ibePrice = getReselivaPriceApi(
          $hotel->reseliva_hotel_id,
          $r->currency,
          $r->startDate,
          $r->endDate
        );
      } else {
        $ibePrice = getSabeeRoomsPrice(
          $hotel->sabee_hotel_id,
          $r->currency,
          $r->startDate,
          $r->endDate
        );
      }

      $prices = collect([
        'booking' => $bookingPrice,
        'hotels' => $hotelsPrice,
        'tatilsepeti' => $tatilsepetiPrice,
        'odamax' => $odamaxPrice,
        'reseliva' => $reselivaPrice,
        'otelz' => $otelzPrice,
        'sabee' => $ibePrice,
      ]);

      $response["prices"] = $prices;
      $response["prices"]["min"] = $prices
        ->filter(function ($v) {
          return !in_array($v, [0, "", "NA"]);
        })
        ->min();
    }

    return \Response::json($response);
  }
}
