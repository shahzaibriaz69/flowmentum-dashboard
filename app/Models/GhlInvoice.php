<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlInvoice extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_invoice_id',
        'invoice_number',
        'status',
        'live_mode',
        'name',
        'title',
        'currency',
        'ghl_contact_id',
        'issue_date',
        'due_date',
        'discount_type',
        'discount_value',
        'subtotal',
        'total',
        'amount_paid',
        'amount_due',
        'notes',
    ];

    protected $casts = [
        'live_mode'      => 'boolean',
        'issue_date'     => 'date',
        'due_date'       => 'date',
        'subtotal'       => 'decimal:2',
        'total'          => 'decimal:2',
        'amount_paid'    => 'decimal:2',
        'amount_due'     => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contact() : BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function businessDetail() : HasOne
    {
        return $this->hasOne(GhlInvoiceBusinessDetail::class, 'ghl_invoice_id', 'ghl_invoice_id');
    }

    public function items() : HasMany
    {
        return $this->hasMany(GhlInvoiceItem::class, 'ghl_invoice_id', 'ghl_invoice_id');
    }
}
