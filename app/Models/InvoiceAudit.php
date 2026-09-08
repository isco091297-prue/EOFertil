<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAudit extends Model
{
    protected $fillable = [
        'invoice_id',
        'admin_user_id',
        'accion',
        'motivo',
        'estado_anterior',
        'estado_nuevo',
        'datos_anteriores',
        'datos_nuevos',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Factura
    |--------------------------------------------------------------------------
    */

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Administrador
    |--------------------------------------------------------------------------
    */

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'admin_user_id'
        );
    }
}
