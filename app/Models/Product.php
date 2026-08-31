<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function activeIngredients(): BelongsToMany
    {
        return $this->belongsToMany(
            ActiveIngredient::class,
            'active_ingredient_product',
            'product_id',
            'active_ingredient_id'
        )->withTimestamps();
    }

    public function activeIngredientCombinations(): BelongsToMany
    {
        return $this->belongsToMany(
            ActiveIngredientCombination::class,
            'active_ingredient_combination_product',
            'product_id',
            'active_ingredient_combination_id'
        )->withTimestamps();
    }
}
