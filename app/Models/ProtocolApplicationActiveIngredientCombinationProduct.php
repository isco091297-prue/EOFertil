<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolApplicationActiveIngredientCombinationProduct extends Model
{
    protected $fillable = [
        'protocol_application_active_ingredient_combination_id',
        'product_id',
        'dose',
        'unit',
        'application_base',
    ];

    public function combination(): BelongsTo
    {
        return $this->belongsTo(
            ProtocolApplicationActiveIngredientCombination::class,
            'protocol_application_active_ingredient_combination_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
