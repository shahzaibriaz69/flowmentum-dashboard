<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlOpportunity extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'user_id',
        'assigned_to_ghl_user',
        'ghl_contact_id',
        'ghl_pipeline_id',
        'ghl_pipeline_stage_id',
        'ghl_opportunity_id',
        'name',
        'monetary_value',
        'status',
        'source',
        'date_added',
    ];

    protected $casts = [
        'date_added' => 'datetime',
        'monetary_value' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(GhlPipeline::class, 'ghl_pipeline_id', 'ghl_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        // Foreign key: ghl_pipeline_stage_id (GhlOpportunity)
        // Owner key: ghl_stage_id (GhlPipelineStage)
        return $this->belongsTo(GhlPipelineStage::class, 'ghl_pipeline_stage_id', 'ghl_stage_id');
    }
}