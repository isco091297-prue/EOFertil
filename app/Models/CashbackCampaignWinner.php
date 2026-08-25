<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashbackCampaignWinner extends Model
{
    protected $table = 'cashback_campaign_winners';

    protected $fillable = [

        'cashback_campaign_id',

        'user_id',

        'warehouse_id',

        'zone_id',

        'branch_id',

        'ranking_position',

        'sales_total',

        'cashback_total',

        'reward_type_id',

        'ranking_reward_id',

        'reward_title',

        'reward_value',

        'reward_multiplier',

        'processed_at',

        'reward_delivered',

        'reward_delivered_at',

    ];

    protected $casts = [

        'sales_total' => 'decimal:2',

        'cashback_total' => 'decimal:2',

        'reward_value' => 'decimal:2',

        'reward_multiplier' => 'decimal:2',

        'processed_at' => 'datetime',

        'reward_delivered' => 'boolean',

        'reward_delivered_at' => 'datetime',

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(
            Zone::class
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class
        );
    }

    public function rewardType(): BelongsTo
    {
        return $this->belongsTo(
            RewardType::class
        );
    }

    public function rankingReward(): BelongsTo
    {
        return $this->belongsTo(
            RankingReward::class
        );
    }
}
