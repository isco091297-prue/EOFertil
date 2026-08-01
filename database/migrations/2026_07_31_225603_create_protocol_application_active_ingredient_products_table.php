<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolApplicationActiveIngredient extends Model
{
    protected $fillable = [
        'protocol_application_id',
        'active_ingredient_id',
    ];

    /**
     * Aplicación del protocolo a la que pertenece.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(
            ProtocolApplication::class,
            'protocol_application_id'
        );
    }

    /**
     * Ingrediente activo seleccionado.
     */
    public function activeIngredient(): BelongsTo
    {
        return $this->belongsTo(
            ActiveIngredient::class,
            'active_ingredient_id'
        );
    }

    /**
     * Productos recomendados para este ingrediente activo
     * dentro de esta aplicación.
     */
    public function products(): HasMany
    {
        return $this->hasMany(
            ProtocolApplicationActiveIngredientProduct::class,
            'protocol_application_active_ingredient_id'
        );
    }
}
