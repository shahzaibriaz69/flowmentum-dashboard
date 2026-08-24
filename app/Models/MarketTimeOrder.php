<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketTimeOrder extends Model
{
    protected $table = 'markettime_orders';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts() : array
    {
        return [
            'order_details'          => 'array',
            'bill_to_address'        => 'array',
            'ship_to_address'        => 'array',
            'order_payments'         => 'array',
            'order_promotions'       => 'array',
            'rep_group_data'         => 'array',
            'notes_and_instructions' => 'array',
            'buyer_info'             => 'array',
            'order_metadata'         => 'array',
            'order_date'             => 'date',
            'request_date'           => 'date',
            'ship_date'              => 'date',
            'cancel_date'            => 'date',
            'date_added'             => 'datetime',
            'date_modified'          => 'datetime',
            'last_synced_at'         => 'datetime',
            'ghl_synced_at'          => 'datetime',
        ];
    }
}
