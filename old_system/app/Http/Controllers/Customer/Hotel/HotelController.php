<?php

namespace App\Http\Controllers\Customer\Hotel;

use App\Http\Requests\HotelStoreRequest;
use App\Models\Hotel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HotelController extends Controller
{
  public function store(HotelStoreRequest $request)
  {
    $hotel = new Hotel();

    $hotel->user_id = user()->id;
    $hotel->name = $request->name;
    $hotel->web_url = $request->web_url;
    $hotel->is_sabee_user = $request->is_sabee_user ? 1 : 0;
    $hotel->sabee_hotel_id = $request->sabee_hotel_id;
    $hotel->sabee_room_id = $request->room;
    $hotel->sabee_rateplan_id = $request->rateplan;
    $hotel->sabee_url = $request->sabee_url;
    $hotel->booking_url = $request->booking_url;
    $hotel->hotels_url = $request->hotels_url;
    $hotel->otelz_url = $request->otelz_url;
    $hotel->tatilsepeti_url = $request->tatilsepeti_url;
    $hotel->booking_is_active = $request->booking_is_active ? 1 : 0;
    $hotel->hotels_is_active = $request->hotels_is_active ? 1 : 0;
    $hotel->tatilsepeti_is_active = $request->tatilsepeti_is_active ? 1 : 0;
    $hotel->odamax_url = $request->odamax_url;
    $hotel->odamax_is_active = $request->odamax_is_active ? 1 : 0;
    $hotel->is_etstur_active = $request->is_etstur_active ? 1 : 0;
    $hotel->etstur_hotel_id = $request->etstur_hotel_id;
    $hotel->otelz_is_active = $request->otelz_is_active ? 1 : 0;
    $hotel->sabee_is_active = $request->sabee_is_active ? 1 : 0;
    $hotel->opening_language = $request->opening_language;
    $hotel->reseliva_hotel_id = $request->reseliva_hotel_id;
    $hotel->reseliva_is_active = $request->reseliva_is_active ? 1 : 0;
    $hotel->hotelrunner_url = $request->hotelrunner_url;
    $hotel->is_hotelrunner_active = $request->is_hotelrunner_active ? 1 : 0;
    $hotel->default_ibe = $request->default_ibe ?: "sabeeapp";

    $hotel->save();

    return redirect()->route("customer.widget.edit");
  }

  public function edit()
  {
    $hotel = user()
      ->hotels()
      ->first();
    $hotels = Hotel::all();

    if (!$hotel) {
      return redirect(route("customer.widget.edit"));
    }

    return view("customer.hotels.create")
      ->with("editing", true)
      ->with("hotels", $hotels)
      ->with("hotel", $hotel);
  }

  public function update(HotelStoreRequest $request)
  {
    $hotel = user()
      ->hotels()
      ->first();

    // process otelz url
    $otelz_url = $request->otelz_url;
    //        if ($otelz_url) {
    //            // throw away the query section of the url
    //            $otelz_url = substr($otelz_url, 0, strrpos($otelz_url, '?'));
    //        }

    $hotel->user_id = user()->id;
    $hotel->name = $request->name;
    $hotel->web_url = $request->web_url;
    $hotel->is_sabee_user = $request->is_sabee_user ? 1 : 0;
    $hotel->sabee_hotel_id = $request->sabee_hotel_id;
    $hotel->sabee_room_id = $request->room;
    $hotel->sabee_rateplan_id = $request->rateplan;
    $hotel->sabee_url = $request->sabee_url;
    $hotel->booking_url = $request->booking_url;
    $hotel->hotels_url = $request->hotels_url;
    $hotel->otelz_url = $otelz_url;
    $hotel->tatilsepeti_url = $request->tatilsepeti_url;
    $hotel->odamax_url = $request->odamax_url;
    $hotel->booking_is_active = $request->booking_is_active ? 1 : 0;
    $hotel->hotels_is_active = $request->hotels_is_active ? 1 : 0;
    $hotel->tatilsepeti_is_active = $request->tatilsepeti_is_active ? 1 : 0;
    $hotel->odamax_is_active = $request->odamax_is_active ? 1 : 0;
    $hotel->otelz_is_active = $request->otelz_is_active ? 1 : 0;
    $hotel->is_etstur_active = $request->is_etstur_active ? 1 : 0;
    $hotel->etstur_hotel_id = $request->etstur_hotel_id;
    $hotel->sabee_is_active = $request->sabee_is_active ? 1 : 0;
    $hotel->opening_language = $request->opening_language;
    $hotel->reseliva_hotel_id = $request->reseliva_hotel_id;
    $hotel->reseliva_is_active = $request->reseliva_is_active ? 1 : 0;
    $hotel->hotelrunner_url = $request->hotelrunner_url;
    $hotel->is_hotelrunner_active = $request->is_hotelrunner_active ? 1 : 0;
    $hotel->default_ibe = $request->default_ibe ?: "sabeeapp";

    $hotel->save();

    return redirect()->route("customer.hotels.edit");
  }
}
