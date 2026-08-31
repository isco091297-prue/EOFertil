<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActiveIngredientCombination extends Model
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

    /**
     * Ingredientes activos que forman la combinación.
     */
    public function activeIngredients(): BelongsToMany
    {
        return $this->belongsToMany(
            ActiveIngredient::class,
            'active_ingredient_combination_ingredient',
            'active_ingredient_combination_id',
            'active_ingredient_id'
        )->withTimestamps();
    }

    /**
     * Productos seleccionados para esta combinación.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'active_ingredient_combination_product',
            'active_ingredient_combination_id',
            'product_id'
        )->withTimestamps();
    }
}
