<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Invite
 *
 * @property int $id
 * @property string $code
 * @property string $namesurname
 * @property string $email
 * @property string $accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $reseller_id
 * @method static \Illuminate\Database\Eloquent\Builder|Invite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereNamesurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereResellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invite whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Invite extends Model
{
    //
}
