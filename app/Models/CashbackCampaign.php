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

        'campaign_type',

        'participant_type',

        'ranking_enabled',

        'ranking_processed',

        'ranking_type',

        'porcentaje',

        'valor_alerta_factura',

        'fecha_inicio',

        'fecha_fin',

        'activo',

    ];

    protected $casts = [

        'activo' => 'boolean',

        'ranking_enabled' => 'boolean',

        'ranking_processed' => 'boolean',

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

    public function scopeCashback(
        Builder $query
    ): Builder {

        return $query->where(
            'campaign_type',
            'cashback'
        );
    }

    public function scopeRankingAccumulated(
        Builder $query
    ): Builder {

        return $query->where(
            'campaign_type',
            'ranking_accumulated'
        );
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

        if (
            now()->isAfter(
                $this->fecha_fin->copy()->endOfDay()
            )
        ) {
            return 'finalizada';
        }

        return 'vigente';
    }

    public function getCampaignTypeLabelAttribute(): string
    {
        return match ($this->campaign_type) {

            'cashback' =>
            'Cashback',

            'ranking_accumulated' =>
            'Ranking Acumulado',

            default =>
            'Sin definir',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function rankingRewards(): HasMany
    {
        return $this->hasMany(
            RankingReward::class
        );
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(
            Invoice::class
        );
    }

    public function cashbackTransactions(): HasMany
    {
        return $this->hasMany(
            CashbackTransaction::class
        );
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(
            CashbackCampaignScope::class
        );
    }
}
