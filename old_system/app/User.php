<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User
 *
 * @property int                                                                                                            $id
 * @property string                                                                                                         $namesurname
 * @property string                                                                                                         $email
 * @property int|null                                                                                                       $is_admin
 * @property int                                                                                                            $hotel_limit
 * @property string|null                                                                                                    $email_verified_at
 * @property string                                                                                                         $password
 * @property string|null                                                                                                    $remember_token
 * @property \Illuminate\Support\Carbon|null                                                                                $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                $updated_at
 * @property int                                                                                                            $user_type
 * @property int|null                                                                                                       $reseller_id
 * @property string|null                                                                                                    $logo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Hotel[]                                              $hotels
 * @property-read int|null                                                                                                  $hotels_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null                                                                                                  $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[]                                                           $subUsers
 * @property-read int|null                                                                                                  $sub_users_count
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
class User extends Authenticatable
{
    use Notifiable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel');
    }
    
    public function subUsers()
    {
        return $this->hasMany("app\User", "reseller_id", "id");
    }
    
    public function reseller()
    {
        return $this->hasOne(User::class, "id", "reseller_id");
    }
    
    public function scopeFilter($q, $filters = null)
    {
        if ($filters === null)
            $filters = request()->get('filters');
        
        $query = array_get($filters, 'q');
        
        if ($query) {
            $q->where('namesurname', 'LIKE', "%$query%")
                ->orWhere('email', 'LIKE', "%$query%")
            ->orWhereHas('hotels', function($q) use ($query) {
                $q->where('name', 'LIKE', "%$query%");
            });
        }
        
        return $q;
    }
    
    public function getLogo()
    {
        $logo = user()->logo;
        
        if (!$logo)
            $logo = 'default-hotel-logo.png';
        
        return asset("logo/$logo");
    }
}
