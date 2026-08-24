<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlInvoiceItem extends Model
{
    protected $fillable = [
        'ghl_invoice_id',
        'ghl_product_id',
        'ghl_price_id',
        'currency',
        'name',
        'qty',
        'amount',
        'taxes',
    ];

    protected $casts = [
        'taxes'  => 'array',
        'amount' => 'decimal:2',
    ];

    public function invoice() : BelongsTo
    {
        return $this->belongsTo(GhlInvoice::class, 'ghl_invoice_id', 'ghl_invoice_id');
    }
}
