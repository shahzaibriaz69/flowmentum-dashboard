<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GhlLocation extends Model
{
    protected $fillable = [
        'user_id',
        'ghl_location_id',
        'name',

        'access_token',
        'refresh_token',
        'expires_at',
        'auth_type',

        'ghl_company_id',

        'domain',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'timezone',
        'logo_url',

        'email',
        'phone',
        'website',

        'first_name',
        'last_name',

        'snapshot_id',

        'settings',

        'ghl_date_added',
        'ghl_date_updated',

        'is_active',

        'raw_data',
    ];

    protected $casts = [
        'settings'          => 'array',
        'raw_data'          => 'array',
        'mt_last_synced_at' => 'datetime',
        'expires_at'        => 'datetime',

        'ghl_date_added'   => 'datetime',
        'ghl_date_updated' => 'datetime',

        'is_active' => 'boolean',
    ];

    public function privateSettingValue(string $key): ?string
    {
        $setting = PrivateSetting::where('key', $key)->first();

        if (! $setting) {
            return null;
        }

        return PrivateSettingOwner::where('private_setting_id', $setting->id)
            ->where('relatable_id', $this->id)
            ->where('relatable_type', $this->getMorphClass())
            ->value('value');
    }

    /**
     * Whether this location has the two credentials required to sync
     * MarketTime orders (group id + api key).
     */
    public function hasMarketTimeCredentials(): bool
    {
        return filled($this->privateSettingValue('sync_markettime_group_id'))
            && filled($this->privateSettingValue('sync_markettime_api_key'));
    }
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts() : HasMany
    {
        return $this->hasMany(GhlContact::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function calendars() : HasMany
    {
        return $this->hasMany(GhlCalendar::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function ghlUsers() : HasMany
    {
        return $this->hasMany(GhlUser::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function settings() : MorphMany
    {
        return $this->morphMany(PrivateSettingOwner::class, 'relatable')->with('privateSetting');
    }
}
