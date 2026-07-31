<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingReward extends Model
{
    protected $table = 'ranking_rewards';

    protected $fillable = [

        'cashback_campaign_id',

        'reward_type_id',

        'posicion',

        'titulo',

        'descripcion',

        'valor_referencial',

        'multiplicador',

        'activo',
    ];

    protected $casts = [

        'valor_referencial' => 'decimal:2',

        'multiplicador' => 'decimal:2',

        'activo' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(
            CashbackCampaign::class,
            'cashback_campaign_id'
        );
    }

    public function rewardType(): BelongsTo
    {
        return $this->belongsTo(
            RewardType::class
        );
    }
}
