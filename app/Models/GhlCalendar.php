<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlCalendar extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_calendar_id',
        'name',
        'description',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function appointments() : HasMany
    {
        return $this->hasMany(GhlAppointment::class, 'calendar_id', 'ghl_calendar_id');
    }
}
