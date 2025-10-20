<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $namesurname
 * @property string $email
 * @property int|null $is_admin
 * @property int $hotel_limit
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_type
 * @property int|null $reseller_id
 * @property string|null $logo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Hotel[] $hotels
 * @property-read int|null $hotels_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereHotelLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNamesurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereResellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserType($value)
 * @mixin \Eloquent
 */
class User extends Model
{
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel', 'user_id');
    }
}
