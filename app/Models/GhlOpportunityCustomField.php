<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhlOpportunityCustomField extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_opportunity_id', // ghl_opportunity_id
        'ghl_field_id',   // ghl_field_id
        'value',
    ];

    public function opportunity() : BelongsTo
    {
        return $this->belongsTo(GhlOpportunity::class, 'ghl_opportunity_id', 'ghl_opportunity_id');
    }

    public function field() : BelongsTo
    {
        return $this->belongsTo(GhlCustomField::class, 'ghl_field_id', 'ghl_field_id');
    }
}
