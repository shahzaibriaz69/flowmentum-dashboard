<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlInvoiceBusinessDetail extends Model
{
    protected $fillable = [
        'ghl_invoice_id',
        'name',
        'address',
        'phone_no',
        'website',
        'logo_url',
        'custom_values',
    ];

    protected $casts = [
        'address'       => 'array',
        'custom_values' => 'array',
    ];

    public function invoice() : BelongsTo
    {
        return $this->belongsTo(GhlInvoice::class, 'ghl_invoice_id', 'ghl_invoice_id');
    }
}
