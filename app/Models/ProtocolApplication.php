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

    /**
     * Receta a la que pertenece la aplicación.
     */
    public function protocol(): BelongsTo
    {
        return $this->belongsTo(Protocol::class);
    }

    /**
     * Productos EOFertil agregados directamente
     * a la aplicación.
     */
    public function products(): HasMany
    {
        return $this->hasMany(
            ProtocolApplicationProduct::class
        );
    }

    /**
     * Ingredientes activos agregados
     * individualmente a la aplicación.
     */
    public function activeIngredients(): HasMany
    {
        return $this->hasMany(
            ProtocolApplicationActiveIngredient::class,
            'protocol_application_id'
        );
    }

    /**
     * Combinaciones de ingredientes activos
     * agregadas a la aplicación.
     */
    public function activeIngredientCombinations(): HasMany
    {
        return $this->hasMany(
            ProtocolApplicationActiveIngredientCombination::class,
            'protocol_application_id'
        );
    }
}
