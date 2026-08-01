<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolApplicationProduct extends Model
{
    protected $fillable = [
        'protocol_application_id',
        'product_id',
        'dose',
        'unit',
        'application_base',
    ];

    protected $casts = [
        'dose' => 'decimal:2',
    ];

    /**
     * Aplicación del receta a la que pertenece.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(
            ProtocolApplication::class,
            'protocol_application_id'
        );
    }

    /**
     * Producto EOFertil seleccionado.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
