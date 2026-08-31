<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActiveIngredient extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'active_ingredient_product',
            'active_ingredient_id',
            'product_id'
        )->withTimestamps();
    }

    /**
     * Combinaciones a las que pertenece este ingrediente.
     */
    public function combinations(): BelongsToMany
    {
        return $this->belongsToMany(
            ActiveIngredientCombination::class,
            'active_ingredient_combination_ingredient',
            'active_ingredient_id',
            'active_ingredient_combination_id'
        )->withTimestamps();
    }
}
