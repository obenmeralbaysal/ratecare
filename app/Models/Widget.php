<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Widget
 *
 * @property int $id
 * @property int|null $hotel_id
 * @property int|null $language_id
 * @property string|null $code
 * @property int|null $duration
 * @property string|null $main_title
 * @property string|null $reservation_button_text
 * @property string|null $direct_reservation_text
 * @property string|null $features_text
 * @property string|null $color
 * @property float|null $discount
 * @property string|null $promotion_text
 * @property int $is_active
 * @property string|null $explanation_text
 * @property string|null $promotion_code
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $currency_id
 * @property int|null $discount_type
 * @property string|null $font
 * @property int|null $minimum_stay
 * @property int $show_mobile
 * @property string|null $activation_date
 * @property int $rotation
 * @property string|null $discount_code
 * @property float|null $discount_code_percentage
 * @property-read \App\Models\Hotel|null $hotel
 * @method static \Illuminate\Database\Eloquent\Builder|Widget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Widget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Widget query()
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereActivationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDirectReservationText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDiscountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDiscountCodePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereExplanationText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereFeaturesText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereFont($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereHotelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereMainTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereMinimumStay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget wherePromotionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget wherePromotionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereReservationButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereRotation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereShowMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Widget whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Widget extends Model
{
    protected $fillable = [
        'type'
    ];

    public function hotel(){
        return $this->belongsTo("App\Models\Hotel", "hotel_id");
    }
}