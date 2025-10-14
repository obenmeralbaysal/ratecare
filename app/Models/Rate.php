<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Rate
 *
 * @property int $id
 * @property string $hotel
 * @property float $sabee_price
 * @property float $booking_price
 * @property float $hotels_price
 * @property float $tatilsepeti_price
 * @property float $odamax_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereBookingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereHotel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereHotelsPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereOdamaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereSabeePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereTatilsepetiPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Rate extends Model
{
    //
}
