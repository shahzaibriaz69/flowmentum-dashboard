<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlResponse extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_contact_id',
        'assigned_user_id',
        'date_created',
        'first_response',
    ];

    protected $casts = [
        'date_created'   => 'datetime',
        'first_response' => 'datetime',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contact() : BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function assignedUser() : BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
