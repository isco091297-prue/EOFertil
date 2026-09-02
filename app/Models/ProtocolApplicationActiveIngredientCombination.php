<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProtocolApplicationActiveIngredientCombination extends Model
{
    protected $fillable = [
        'protocol_application_id',
        'active_ingredient_combination_id',
        'dose',
        'unit',
        'application_base',
    ];

    /**
     * Aplicación a la que pertenece la combinación.
     */
    public function protocolApplication(): BelongsTo
    {
        return $this->belongsTo(
            ProtocolApplication::class,
            'protocol_application_id'
        );
    }

    /**
     * Combinación de ingredientes activos.
     */
    public function products(): HasMany
    {
        return $this->hasMany(
            ProtocolApplicationActiveIngredientCombinationProduct::class,
            'protocol_application_active_ingredient_combination_id'
        );
    }

    /**
     * Combinación de ingredientes activos.
     */
    public function activeIngredientCombination(): BelongsTo
    {
        return $this->belongsTo(
            ActiveIngredientCombination::class,
            'active_ingredient_combination_id'
        );
    }
}
