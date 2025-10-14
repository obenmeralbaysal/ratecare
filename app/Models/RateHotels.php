<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateHotels extends Model
{
  protected $table = 'rate_hotels';
  public $timestamps = false;

  public function hotel()
  {
    return $this->belongsTo("App\Models\Hotel", "competitor_hotel_id");
  }

  public function user()
  {
    return $this->belongsTo("App\Models\User", "user_id");
  }

  public function getHotelName()
  {
    return $this->hotel()->firstOrFail()->name;
  }
}
