<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateSetting extends Model
{
     protected $fillable = [
        'key',
        'label',
        'category',
        'element',
        'type',
        'size',
        'roles',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
        'roles' => 'array',
    ];
}
