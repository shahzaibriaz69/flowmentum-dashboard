<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlMessage extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_contact_id',
        'conversation_id',
        'ghl_message_id',
        'ghl_call_id',
        'user_id',
        'ghl_user_id',
        'type',
        'message_type',
        'direction',
        'status',
        'body',
        'attachments',
        'content_type',
        'source',
        'subject',
        'call_duration',
        'call_status',
        'call_recording_url',
        'email_message_id',
        'thread_id',
        'from_email',
        'to_email',
        'chat_widget_id',
        'conversation_provider_id',
        'assigned_to_ghl_user',
        'delete_in_ghl',
        'ghl_company_id',
        'date_added',
        'date_updated',
    ];

    protected $casts = [
        'attachments'   => 'array',
        'to_email'      => 'array',
        'delete_in_ghl' => 'boolean',
        'date_added'    => 'datetime',
        'date_updated'  => 'datetime',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contact() : BelongsTo
    {
        return $this->belongsTo(GhlContact::class, 'ghl_contact_id', 'ghl_contact_id');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
