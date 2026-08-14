<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use Illuminate\Database\Eloquent\Collection;

class CampaignUserRankingService
{
    /**
     * Obtener ranking completo.
     */
    public function getRanking(
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

        if (
            $campaign->campaign_type ===
            'ranking_accumulated'
        ) {

            $query

                ->orderByDesc('sales_total')

                ->orderByDesc('cashback_total');
        } else {

            $query

                ->orderByDesc('cashback_total')

                ->orderByDesc('sales_total');
        }

        return $query

            ->orderBy('invoice_count')

            ->get();
    }

    /**
     * Ranking por almacén.
     */
    public function getWarehouseRanking(
        CashbackCampaign $campaign,
        int $warehouseId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'warehouse_id',
                $warehouseId
            )

            ->values();
    }

    /**
     * Ranking por zona.
     */
    public function getZoneRanking(
        CashbackCampaign $campaign,
        int $zoneId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'zone_id',
                $zoneId
            )

            ->values();
    }

    /**
     * Ranking por sucursal.
     */
    public function getBranchRanking(
        CashbackCampaign $campaign,
        int $branchId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'branch_id',
                $branchId
            )

            ->values();
    }

    /**
     * Buscar participante.
     */
    public function find(
        CashbackCampaign $campaign,
        int $userId
    ): ?CampaignUserRanking {

        return CampaignUserRanking::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'user_id',
                $userId
            )

            ->first();
    }

    /**
     * Obtener ganador.
     */
    public function getWinner(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return $this->getRanking(
            $campaign
        )->first();
    }


    /**
     * Actualizar el acumulado del ranking a partir de una factura.
     */
    public function updateFromInvoice(
        CashbackCampaign $campaign,
        \App\Models\Invoice $invoice,
        float $salesTotal,
        float $cashbackTotal
    ): CampaignUserRanking {

        $ranking = CampaignUserRanking::firstOrNew([
            'cashback_campaign_id' => $campaign->id,
            'user_id' => $invoice->user_id,
        ]);

        if (! $ranking->exists) {
            $ranking->warehouse_id = $invoice->user->warehouse_id;
            $ranking->zone_id = $invoice->user->zone_id;
            $ranking->branch_id = $invoice->branch_id;
            $ranking->sales_total = 0;
            $ranking->cashback_total = 0;
            $ranking->invoice_count = 0;
        }

        $ranking->sales_total =
            (float) $ranking->sales_total + $salesTotal;

        $ranking->cashback_total =
            (float) $ranking->cashback_total + $cashbackTotal;

        $ranking->invoice_count =
            (int) $ranking->invoice_count + 1;

        $ranking->position = null;

        $ranking->save();

        return $ranking->fresh();
    }

    /**
     * Datos del ranking que consume la aplicación móvil.
     *
     * Devuelve los primeros participantes y, por separado,
     * la posición actual del usuario autenticado.
     */
    public function getMobileRanking(
        \App\Models\User $user,
        int $limit = 10
    ): array {

        $campaign = CashbackCampaign::query()
            ->where('activo', true)
            ->whereDate('fecha_inicio', '<=', now())
            ->whereDate('fecha_fin', '>=', now())
            ->where(function ($query) {
                $query
                    ->where('campaign_type', 'ranking_accumulated')
                    ->orWhere(function ($q) {
                        $q
                            ->where('campaign_type', 'cashback')
                            ->where('ranking_enabled', true);
                    });
            })
            ->latest('fecha_inicio')
            ->first();

        if (! $campaign) {
            return [
                'campaign' => null,
                'ranking' => [],
                'my_ranking' => null,
                'total_participants' => 0,
            ];
        }

        $query = CampaignUserRanking::query()
            ->with([
                'user:id,first_name,last_name',
                'warehouse:id,name',
                'zone:id,name',
                'branch:id,name',
            ])
            ->where('cashback_campaign_id', $campaign->id);

        if ($campaign->ranking_type === 'sales') {
            $query
                ->orderByDesc('sales_total')
                ->orderByDesc('cashback_total');
        } else {
            $query
                ->orderByDesc('cashback_total')
                ->orderByDesc('sales_total');
        }

        $query
            ->orderBy('invoice_count')
            ->orderBy('id');

        $all = $query->get();

        $totalParticipants = $all->count();

        $rewards = $campaign->rankingRewards()
            ->where('activo', true)
            ->orderBy('posicion')
            ->get()
            ->keyBy('posicion');

        $myRanking = null;

        foreach ($all as $index => $participant) {
            $position = $index + 1;

            if ((int) $participant->user_id === (int) $user->id) {
                $myRanking = $this->mobileParticipant(
                    $participant,
                    $position,
                    true,
                    $rewards
                );
                break;
            }
        }

        $ranking = $all
            ->take(max(1, min($limit, 50)))
            ->values()
            ->map(function (CampaignUserRanking $participant, int $index) {
                return $this->mobileParticipant(
                    $participant,
                    $index + 1,
                    false,
                    $rewards
                );
            })
            ->all();

        return [
            'campaign' => [
                'id' => $campaign->id,
                'nombre' => $campaign->nombre,
                'descripcion' => $campaign->descripcion,
                'fecha_inicio' => optional($campaign->fecha_inicio)->toDateString(),
                'fecha_fin' => optional($campaign->fecha_fin)->toDateString(),
                'ranking_type' => $campaign->ranking_type,
                'participant_type' => $campaign->participant_type,
            ],
            'ranking' => $ranking,
            'my_ranking' => $myRanking,
            'total_participants' => $totalParticipants,
        ];
    }

    /**
     * Convertir un participante a la estructura pública del móvil.
     */
    private function mobileParticipant(
        CampaignUserRanking $participant,
        int $position,
        bool $isMe,
        $rewards
    ): array {

        $name = trim(
            ($participant->user?->first_name ?? '') .
            ' ' .
            ($participant->user?->last_name ?? '')
        );

        $reward = $rewards->get($position);

        return [
            'position' => $position,
            'user_id' => $participant->user_id,
            'name' => $name !== '' ? $name : 'Participante',
            'sales_total' => (float) $participant->sales_total,
            'cashback_total' => (float) $participant->cashback_total,
            'invoice_count' => (int) $participant->invoice_count,
            'is_me' => $isMe,
            'reward' => $reward ? [
                'titulo' => $reward->titulo,
                'descripcion' => $reward->descripcion,
                'valor_referencial' => $reward->valor_referencial !== null
                    ? (float) $reward->valor_referencial
                    : null,
                'multiplicador' => $reward->multiplicador !== null
                    ? (float) $reward->multiplicador
                    : null,
            ] : null,
        ];
    }

    /**
     * Reiniciar ranking.
     */
    public function reset(
        CashbackCampaign $campaign
    ): void {

        CampaignUserRanking::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->delete();
    }
}
