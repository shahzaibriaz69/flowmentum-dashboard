<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GhlAgency extends Model
{
    protected $fillable = [
        'user_id',
        'ghl_company_id',
        'private_integration_token',
        'agency_name',
        'email',
        'phone',
        'site_link',
        'logo',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locations() : HasMany
    {
        return $this->hasMany(GhlLocation::class, 'ghl_company_id', 'ghl_company_id');
    }

    public function settings() : MorphMany{
        return $this->morphMany(PrivateSettingOwner::class,'relatable')->with('privateSetting');
    }
}
