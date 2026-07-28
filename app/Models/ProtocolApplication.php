<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolApplication extends Model
{
    protected $fillable = [
        'protocol_id',
        'application_number',
        'description',
        'application_type',
    ];

    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ProtocolApplicationProduct::class);
    }
}
