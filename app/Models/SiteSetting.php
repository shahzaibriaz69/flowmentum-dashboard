<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'category',
        'element',
        'type',
        'size',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
