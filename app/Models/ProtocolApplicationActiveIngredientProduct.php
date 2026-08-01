<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolApplicationActiveIngredientProduct extends Model
{
    protected $fillable = [
        'protocol_application_active_ingredient_id',
        'product_id',
        'dose',
        'unit',
        'application_base',
    ];

    protected $casts = [
        'dose' => 'decimal:2',
    ];

    /**
     * Ingrediente activo de la aplicación al que pertenece.
     */
    public function applicationActiveIngredient(): BelongsTo
    {
        return $this->belongsTo(
            ProtocolApplicationActiveIngredient::class,
            'protocol_application_active_ingredient_id'
        );
    }

    /**
     * Producto recomendado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
