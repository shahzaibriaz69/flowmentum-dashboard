<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlContactCustomField extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_contact_id', // ghl_contact_id
        'ghl_field_id',   // ghl_field_id
        'value',
    ];

    public function contact() : BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function field() : BelongsTo
    {
        return $this->belongsTo(GhlCustomField::class, 'ghl_field_id', 'ghl_field_id');
    }
}
