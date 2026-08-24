<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlAppointment extends Model
{
    protected $fillable = [
        'user_id',
        'ghl_location_id',
        'ghl_company_id',
        'ghl_contact_id',
        'calendar_id',
        'ghl_appointment_id',
        'assigned_to_ghl_user',
        'title',
        'address',
        'status',
        'ghl_group_id',
        'users',
        'notes',
        'source',
        'start_time',
        'end_time',
        'date_added',
        'date_updated',
    ];

    protected $casts = [
        'users'        => 'array',
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'date_added'   => 'datetime',
        'date_updated' => 'datetime',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contact() : BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function calendar() : BelongsTo
    {
        return $this->belongsTo(GhlCalendar::class, 'calendar_id', 'ghl_calendar_id');
    }
}
