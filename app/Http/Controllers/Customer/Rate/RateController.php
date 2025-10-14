<?php

namespace App\Http\Controllers\Customer\Rate;

use App\Models\Hotel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\RateHotels;
use App\Models\RateChannel;
use App\Models\RateComparisonCurrency;

class RateController extends Controller
{
  public function __construct()
  {
    $this->middleware("auth");
  }

  public function index()
  {
    $hotel = user()
      ->hotels()
      ->first();
    $period = CarbonPeriod::create(
      Carbon::now()->startOfMonth(),
      Carbon::now()->endOfMonth()
    );
    $competitors = $hotel
      ->competitors()
      ->pluck("competitor_hotel_id")
      ->toArray();

    $competitorHotels = $hotel
      ->competitors()
      ->with("hotel")
      ->with("user")
      ->get();

    $channels = $hotel
      ->competitorChannels()
      ->pluck("channel")
      ->toArray();
    if ($hotel->comparisonCurrency()->first()) {
      $currency = $hotel->comparisonCurrency()->first()["currency"];
    } else {
      $currency = "";
    }

    $hotels = Hotel::all();
    return view("customer.rates.index")
      ->with("hotels", $hotels)
      ->with('prices', [])
      ->with("period", $period)
      ->with("channels", $channels)
      ->with("currency", $currency)
      ->with("competitorHotels", $competitorHotels)
      ->with("competitors", $competitors);
  }

  public function store(Request $request)
  {
    $hotel = user()
      ->hotels()
      ->first();
    $competitorHotels = $request->get("competitorHotels");
    $channels = $request->get("channels");

    $hotel->competitors()->delete();

    if ($competitorHotels) {
      foreach ($competitorHotels as $competitorHotel) {
        $competitor = new RateHotels();
        $competitor->hotel_id = $hotel->id;
        $competitor->competitor_hotel_id = $competitorHotel;
        $competitor->save();
      }
    }

    $hotel->competitorChannels()->delete();

    if ($channels) {
      foreach ($channels as $channel) {
        $c = new RateChannel();
        $c->hotel_id = $hotel->id;
        $c->channel = $channel;
        $c->save();
      }
    }

    $hotel->comparisonCurrency()->delete();

    $currency = new RateComparisonCurrency();
    $currency->hotel_id = $hotel->id;
    $currency->currency = $request->get("currency");
    $currency->save();

    return back()->with("success", "Successfully saved.");
  }

  public function rateRequest(Request $request)
  {
    $hotel = user()
      ->hotels()
      ->first();

    $startDate = date(
      "Y-m-d",
      strtotime(str_replace('/', '-', $request->get("startDate")))
    );
    $endDate = date(
      "Y-m-d",
      strtotime(str_replace('/', '-', $request->get("endDate")))
    );
    $currency = $hotel->comparisonCurrency()->first()["currency"];

    $mainHotelId = $hotel->id;
    $competitors = $hotel->competitors()->pluck("competitor_hotel_id");
    $competitorChannels = $hotel
      ->competitorChannels()
      ->pluck("channel")
      ->toArray();
    $finalPrices = [];

    $competitors[] = $hotel->id;

    $ibePrice = "";
    $reselivaPrice = "";
    $bookingPrice = "";
    $hotelsPrice = "";
    $tatilsepetiPrice = "";
    $odamaxPrice = "";
    $otelzPrice = "";
    $etsturPrice = "";
    $defaultIbe = $hotel->default_ibe;

    foreach ($competitors as $hotelID) {
      $competitorHotel = Hotel::find($hotelID);

      if (in_array("SabeeApp", $competitorChannels)) {
        $ibePrice = $competitorHotel->sabee_hotel_id
          ? (getSabeeRoomsPrice(
            $competitorHotel->sabee_hotel_id,
            $currency,
            $startDate,
            $endDate
          ) ?:
          "")
          : "";
      }

      if (in_array("Booking", $competitorChannels)) {
        $bookingPrice = $competitorHotel->booking_url
          ? (getBookingPrice(
            $competitorHotel->booking_url,
            $currency,
            $startDate,
            $endDate
          ) ?:
          "NA")
          : "";
      }

      if (in_array("Hotels", $competitorChannels)) {
        $hotelsPrice = $competitorHotel->hotels_url
          ? getHotelsPrice(
            $competitorHotel->hotels_url,
            $currency,
            $startDate,
            $endDate
          )
          : "";
      }

      if (in_array("TatilSepeti", $competitorChannels)) {
        $tatilsepetiPrice = $competitorHotel->tatilsepeti_url
          ? getTatilSepetiPrice(
            $competitorHotel->tatilsepeti_url,
            $currency,
            $startDate,
            $endDate
          )
          : "";
      }

      if (in_array("Odamax", $competitorChannels)) {
        $odamaxPrice = $competitorHotel->odamax_url
          ? getOdamaxPrice(
            $competitorHotel->odamax_url,
            $currency,
            $startDate,
            $endDate
          )
          : "";
      }

      if (in_array("OtelZ", $competitorChannels)) {
        $otelzPrice = $competitorHotel->otelz_url
          ? getOtelZPrice(
            $competitorHotel->otelz_url,
            $currency,
            $startDate,
            $endDate
          )
          : "";
      }

      if (in_array("Reseliva", $competitorChannels)) {
        $reselivaPrice = $competitorHotel->reseliva_hotel_id
          ? getReselivaPriceApi(
            $competitorHotel->reseliva_hotel_id,
            $currency,
            $startDate,
            $endDate
          )["price"]
          : "";
      }

      if (in_array("ETSTur", $competitorChannels)) {
        $etsturPrice = $competitorHotel->etstur_hotel_id
          ? getETSTurPrice(
            $hotel->etstur_hotel_id,
            $currency,
            $startDate,
            $endDate
          )["price"]
          : "";
      }

      $prices = [
        'ibePrice' => $ibePrice,
        'bookingPrice' => $bookingPrice,
        'hotelsPrice' => $hotelsPrice,
        'tatilsepetiPrice' => $tatilsepetiPrice,
        'odamaxPrice' => $odamaxPrice,
        'otelzPrice' => $otelzPrice,
        'reselivaPrice' => $reselivaPrice,
        'etsturPrice' => $etsturPrice,
      ];

      $filteredPrices = [];

      foreach ($prices as $key => $value) {
        if ($value != 0 and $value != "" and $value != "NA") {
          $filteredPrices[$key] = (int) $value;
        }
      }

      if ($filteredPrices != null) {
        $minimumKey = array_keys($filteredPrices, min($filteredPrices));

        $data = [
          "hotelName" => $competitorHotel->name,
          "minPriceChannel" => $minimumKey[0],
          "minPrice" => $filteredPrices[$minimumKey[0]],
          "isMain" => $hotel->name == $competitorHotel->name,
        ];

        array_push($finalPrices, $data);
      }
    }

    return response()->json($finalPrices);
  }
}
