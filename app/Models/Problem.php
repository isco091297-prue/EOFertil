<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Problem extends Model
{
    protected $fillable = [

        'code',
        'name',
        'crop_id',
        'image_path',
        'description',
        'is_active',

    ];

    protected $casts = [

        'is_active'=>'boolean',

    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

}
