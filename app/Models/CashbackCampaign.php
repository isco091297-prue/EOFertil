<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashbackCampaign extends Model
{
    protected $table = 'cashback_campaigns';

    protected $fillable = [
        'nombre',
        'descripcion',
        'porcentaje',
        'valor_alerta_factura',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getEstadoAttribute(): string
    {
        if (! $this->activo) {
            return 'inactiva';
        }

        if (now()->lt($this->fecha_inicio)) {
            return 'proxima';
        }

        if (now()->isAfter($this->fecha_fin->copy()->endOfDay())) {
            return 'finalizada';
        }

        return 'vigente';
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function rankingRewards(): HasMany
    {
        return $this->hasMany(RankingReward::class);
    }
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(CashbackTransaction::class);
    }
}
