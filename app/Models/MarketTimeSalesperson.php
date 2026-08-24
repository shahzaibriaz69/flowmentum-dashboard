<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketTimeSalesperson extends Model
{
    protected $table = 'markettime_salespersons';

    protected $fillable = [
        'record_id',
        'name',
        'abbreviation',
        'address1',
        'address2',
        'city',
        'state',
        'ghl_user_id',
        'zip',
        'country',
        'cell_country_code',
        'cell',
        'phone_country_code',
        'phone',
        'phone_extension',
        'fax_country_code',
        'fax',
        'fax_extension',
        'email',
        'active',
        'status',
        'notes',
        'image_path',
        'approved',
        'date_added',
        'user_added',
        'date_modified',
        'user_modified',
        'record_deleted',
        'latitude',
        'longitude',
        'po_prefix',
        'order_code',
        'external_id',
        'external_id2',
        'manufacturers_commission_data',
        'salesperson_group_mappings',
    ];

    protected function casts() : array
    {
        return [
            'active'                        => 'boolean',
            'approved'                      => 'boolean',
            'record_deleted'                => 'boolean',
            'manufacturers_commission_data' => 'array',
            'salesperson_group_mappings'    => 'array',
        ];
    }
}
