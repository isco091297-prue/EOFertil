<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use Illuminate\Support\Collection;

class RankingWinnerService
{
    /**
     * Obtiene el ranking ordenado
     * según el tipo de campaña.
     */
    public function ranking(
        CashbackCampaign $campaign
    ): Collection {

        $query = CampaignUserRanking::query()

            ->with([
                'user',
                'warehouse',
                'zone',
                'branch',
            ])

            ->where(
                'cashback_campaign_id',
                $campaign->id
            );

        switch ($campaign->campaign_type) {

            case 'ranking_cashback':

                $query

                    ->orderByDesc('cashback_total')

                    ->orderByDesc('sales_total')

                    ->orderBy('invoice_count');

                break;

            case 'ranking_accumulated':

                $query

                    ->orderByDesc('sales_total')

                    ->orderByDesc('cashback_total')

                    ->orderBy('invoice_count');

                break;

            default:

                $query

                    ->orderByDesc('cashback_total');

                break;
        }

        return $query->get();
    }

    /**
     * Obtiene los ganadores.
     */
    public function winners(
        CashbackCampaign $campaign
    ): Collection {

        $ranking = $this->ranking(
            $campaign
        );

        $rewards = $campaign->rankingRewards()

            ->where('activo', true)

            ->orderBy('posicion')

            ->get();

        $winners = collect();

        foreach ($rewards as $reward) {

            $participant = $ranking->get(
                $reward->posicion - 1
            );

            if (! $participant) {
                continue;
            }

            $participant->setRelation(
                'reward',
                $reward
            );

            $winners->push(
                $participant
            );
        }

        return $winners;
    }

    /**
     * Obtiene un ganador
     * por posición.
     */
    public function winner(
        CashbackCampaign $campaign,
        int $position
    ): ?CampaignUserRanking {

        return $this->winners(
            $campaign
        )->get(
            $position - 1
        );
    }

    /**
     * Primer lugar.
     */
    public function first(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return $this->winner(
            $campaign,
            1
        );
    }

    /**
     * Segundo lugar.
     */
    public function second(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return $this->winner(
            $campaign,
            2
        );
    }

    /**
     * Tercer lugar.
     */
    public function third(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return $this->winner(
            $campaign,
            3
        );
    }
}
