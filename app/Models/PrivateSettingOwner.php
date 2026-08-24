<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateSettingOwner extends Model
{
      protected $fillable = [
        'private_setting_id',
        'value',
    ];

    public function relatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function privateSetting(): BelongsTo
    {
        return $this->belongsTo(PrivateSetting::class);
    }
}
