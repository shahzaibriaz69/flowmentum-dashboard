<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlCustomValue extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_value_id',
        'name',
        'value',
        'field_key',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }
}
