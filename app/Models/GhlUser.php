<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlUser extends Model
{
    protected $fillable = [
        'user_id',
        'ghl_location_ids',
        'ghl_user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'type',
        'role',
        'permissions',
    ];

    protected $casts = [
        'permissions'      => 'array',
        'ghl_location_ids' => 'array',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
