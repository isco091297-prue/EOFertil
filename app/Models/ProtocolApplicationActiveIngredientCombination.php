<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function activeIngredientCombination(): BelongsTo
    {
        return $this->belongsTo(
            ActiveIngredientCombination::class,
            'active_ingredient_combination_id'
        );
    }
}
