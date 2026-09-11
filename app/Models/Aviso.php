<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';

    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo',
        'enlace',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}