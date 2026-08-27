<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GhlPipelineStage extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_pipeline_id',
        'ghl_stage_id',
        'name',
        'position',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(GhlPipeline::class, 'ghl_pipeline_id', 'ghl_pipeline_id');
    }

    public function opportunities(): HasMany
    {
        // Foreign key: ghl_pipeline_stage_id (GhlOpportunity)
        // Local key: ghl_stage_id (GhlPipelineStage)
        return $this->hasMany(GhlOpportunity::class, 'ghl_pipeline_stage_id', 'ghl_stage_id');
    }
}