<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [

        'code',
        'name',
        'brand_id',
        'category_id',
        'description',
        'image_path',
        'is_active',

    ];

    protected $casts = [

        'is_active' => 'boolean',

    ];
    protected $appends = [
        'image_url',
    ];
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return url('storage/' . $this->image_path);
    }
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function protocolApplicationProducts(): HasMany
    {
        return $this->hasMany(ProtocolApplicationProduct::class);
    }
}
