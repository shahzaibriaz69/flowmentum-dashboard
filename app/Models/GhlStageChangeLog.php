<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlStageChangeLog extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_opportunity_stage_id', // ghl_opportunity_id
        'from_stage_id',  // ghl_stage_id
        'to_stage_id',    // ghl_stage_id
        'ghl_pipeline_id',   // ghl_pipeline_id
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function opportunity() : BelongsTo
    {
        return $this->belongsTo(GhlOpportunity::class, 'ghl_opportunity_stage_id', 'ghl_opportunity_id');
    }

    public function pipeline() : BelongsTo
    {
        return $this->belongsTo(GhlPipeline::class, 'ghl_pipeline_id', 'ghl_pipeline_id');
    }
}
