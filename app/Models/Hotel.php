<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Hotel
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $web_url
 * @property string|null $sabee_url
 * @property string|null $booking_url
 * @property string|null $hotels_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $sabee_hotel_id
 * @property int|null $sabee_room_id
 * @property int|null $sabee_rateplan_id
 * @property string|null $odamax_url
 * @property string|null $otelz_url
 * @property string|null $tatilsepeti_url
 * @property int $booking_is_active
 * @property int $hotels_is_active
 * @property int $odamax_is_active
 * @property int $otelz_is_active
 * @property int $tatilsepeti_is_active
 * @property string $opening_language
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Widget[] $widgets
 * @property-read int|null $widgets_count
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereBookingIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereBookingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereHotelsIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereHotelsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereOdamaxIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereOdamaxUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereOpeningLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereOtelzIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereOtelzUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereSabeeHotelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereSabeeRateplanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereSabeeRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereSabeeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereTatilsepetiIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereTatilsepetiUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hotel whereWebUrl($value)
 * @mixin \Eloquent
 */
class Hotel extends Model
{
  protected $fillable = ['id', 'user_id'];

  public function widgets()
  {
    return $this->hasMany("App\Models\Widget", 'hotel_id');
  }

  public function user()
  {
    return $this->hasOne("App\Models\User", 'id');
  }

  public function competitors()
  {
    return $this->hasMany("App\Models\RateHotels");
  }

  public function competitorChannels()
  {
    return $this->hasMany("App\Models\RateChannel");
  }

  public function comparisonCurrency()
  {
    return $this->hasOne("App\Models\RateComparisonCurrency");
  }
}
