<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ghl_location_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * GHL Relationships
     */
    public function ghlAgency(): HasOne
    {
        return $this->hasOne(GhlAgency::class);
    }

    public function ghlLocation(): HasOne
    {
        return $this->hasOne(GhlLocation::class);
    }

    public function ghlUser(): HasOne
    {
        return $this->hasOne(GhlUser::class);
    }

    public function IsAgency(): bool
    {
        return $this->hasRole(RoleEnum::AGENCY->value);
    }

    public function IsLocation(): bool
    {
        return $this->hasRole(RoleEnum::LOCATION->value);
    }

    public function IsGhlUser(): bool
    {
        return $this->hasRole([RoleEnum::USER->value, RoleEnum::ADMIN->value]);
    }

    public function getLocationIdsAttribute(): array
    {
        if ($this->IsLocation()) {
            return [
                (GhlLocation::where('user_id', $this->getKey())
                    ->value('ghl_location_id') ?? '')
            ];
        }

        if ($this->IsGhlUser()) {
            return 
                (GhlUser::where('user_id', $this->getKey())
                    ->value('ghl_location_ids') ?? '');
        }

        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

}
