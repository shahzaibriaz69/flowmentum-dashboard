<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GhlTag extends Model
{
    protected $fillable = [
        'ghl_location_id',
        'ghl_tag_id', // GHL tags are sometimes just strings, but they have IDs in newer APIs
        'name',
    ];

    public function location() : BelongsTo
    {
        return $this->belongsTo(GhlLocation::class, 'ghl_location_id', 'ghl_location_id');
    }

    public function contacts() : BelongsToMany
    {
        return $this->belongsToMany(GhlContact::class, 'ghl_contact_tags', 'ghl_tag_id', 'ghl_contact_id', 'ghl_tag_id', 'ghl_contact_id');
    }
}
