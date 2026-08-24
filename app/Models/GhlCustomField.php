<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlCustomField extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_field_id',
        'name',
        'field_key',
        'data_type',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }
}
