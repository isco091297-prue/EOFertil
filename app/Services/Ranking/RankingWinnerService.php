<?php

namespace App\Services\Ranking;

use App\Models\CashbackCampaign;
use App\Models\CashbackCampaignWinner;
use Illuminate\Database\Eloquent\Collection;

class RankingWinnerService
{
    /**
     * Obtener los ganadores de una campaña.
     */
    public function winners(
        CashbackCampaign $campaign
    ): Collection {

        return CashbackCampaignWinner::query()

            ->with([
                'user',
                'warehouse',
                'zone',
                'branch',
                'rewardType',
                'rankingReward',
            ])

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->orderBy(
                'ranking_position'
            )

            ->get();
    }

    /**
     * Marcar un premio como entregado.
     */
    public function markAsDelivered(
        CashbackCampaignWinner $winner
    ): CashbackCampaignWinner {

        if ($winner->reward_delivered) {
            return $winner;
        }

        $winner->update([
            'reward_delivered' => true,
            'reward_delivered_at' => now(),
        ]);

        return $winner->fresh([
            'user',
            'warehouse',
            'zone',
            'branch',
            'rewardType',
            'rankingReward',
        ]);
    }

    /**
     * Obtener un ganador específico.
     */
    public function find(
        CashbackCampaign $campaign,
        int $winnerId
    ): ?CashbackCampaignWinner {

        return CashbackCampaignWinner::query()

            ->with([
                'user',
                'warehouse',
                'zone',
                'branch',
                'rewardType',
                'rankingReward',
            ])

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'id',
                $winnerId
            )

            ->first();
    }
}
