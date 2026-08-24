<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'was_allowed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
