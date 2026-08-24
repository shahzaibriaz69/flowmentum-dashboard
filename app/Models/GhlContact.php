<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlContact extends Model
{
    protected $fillable = [
        'ghl_location_id', // This will store the ghl_location_id string
        'ghl_contact_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'state',
        'state_id',
        'country',
        'dnd',
        'city',
        'company',
        'assigned_to_ghl_user',
        'source',
        'tags',
        'custom_fields',
        'date_added',
        'date_updated',
        'date_of_birth',
        'ghl_company_id',
        'postal_code',
        'user_id',
    ];

    protected $casts = [
        'tags'          => 'array',
        'custom_fields' => 'array',
        'date_added'    => 'datetime',
        'date_updated'  => 'datetime',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function appointments() : HasMany
    {
        return $this->hasMany(GhlAppointment::class, 'ghl_contact_id', 'ghl_contact_id');
    }
}
