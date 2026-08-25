<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlPipeline extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_pipeline_id',
        'name',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(GhlPipelineStage::class, 'ghl_pipeline_id', 'ghl_pipeline_id')
                    ->orderBy('position', 'asc');
    }
}