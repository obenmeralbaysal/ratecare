<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Statistic
 *
 * @property int $id
 * @property string $widget_code
 * @property string $ip
 * @property string $type
 * @property string|null $departure
 * @property string|null $arrival
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $country
 * @property int $result
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Statistic whereWidgetCode($value)
 * @mixin \Eloquent
 */
class Statistic extends Model
{
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today()->toDateString());
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('created_at', Carbon::yesterday()->toDateString());
    }
}
