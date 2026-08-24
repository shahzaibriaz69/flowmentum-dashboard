<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketTimeLog extends Model
{
    protected $table = 'markettime_logs';

    protected $fillable = [
        'status',
        'error',
    ];
}
