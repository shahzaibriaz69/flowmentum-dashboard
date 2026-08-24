<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhlState extends Model
{
    protected $fillable = [
        'abbr',
        'name',
        'area_codes',
    ];

    protected $casts = [
        'area_codes' => 'array',
    ];
}
