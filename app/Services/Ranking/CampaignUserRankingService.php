<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use App\Models\Invoice;
use App\Models\User;
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

        /*
        |--------------------------------------------------------------------------
        | Ranking acumulado
        |--------------------------------------------------------------------------
        */

        if (
            $campaign->campaign_type ===
            'ranking_accumulated'
        ) {

            $query
                ->orderByDesc('sales_total')
                ->orderByDesc('invoice_count');
        } else {

            /*
            |--------------------------------------------------------------------------
            | Ranking Cashback
            |--------------------------------------------------------------------------
            */

            $query
                ->orderByDesc('cashback_total')
                ->orderByDesc('sales_total')
                ->orderBy('invoice_count');
        }

        return $query->get();
    }

    /**
     * Obtener ranking Cashback para la aplicación móvil.
     *
     * IMPORTANTE:
     *
     * El ranking Cashback es independiente del ranking acumulado.
     *
     * Si la campaña tiene:
     *
     *     participant_type = warehouse
     *
     * solamente participan los usuarios pertenecientes
     * al mismo almacén del usuario autenticado.
     *
     * Las zonas y sucursales NO separan el ranking.
     */
    public function getMobileRanking(
        ?int $userId = null
    ): array {

        /*
    |--------------------------------------------------------------------------
    | Usuario actual
    |--------------------------------------------------------------------------
    */

        $user = null;

        if ($userId !== null) {

            $user = User::query()
                ->select([
                    'id',
                    'warehouse_id',
                    'zone_id',
                    'branch_id',
                ])
                ->find($userId);
        }

        /*
    |--------------------------------------------------------------------------
    | Buscar solamente campañas Cashback vigentes
    |--------------------------------------------------------------------------
    |
    | El ranking acumulado tiene su propio endpoint:
    |
    |     getMobileAccumulatedRanking()
    |
    */

        $campaigns = CashbackCampaign::query()

            ->where(
                'campaign_type',
                'cashback'
            )

            ->where(
                'activo',
                true
            )

            ->where(
                'ranking_enabled',
                true
            )

            ->whereDate(
                'fecha_inicio',
                '<=',
                now()->toDateString()
            )

            ->whereDate(
                'fecha_fin',
                '>=',
                now()->toDateString()
            )

            ->orderBy(
                'fecha_fin'
            )

            ->get();

        $result = [];

        foreach ($campaigns as $campaign) {

            /*
        |--------------------------------------------------------------------------
        | Ranking de la campaña
        |--------------------------------------------------------------------------
        */

            $rankingQuery = CampaignUserRanking::query()

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

            /*
        |--------------------------------------------------------------------------
        | PARTICIPANTES
        |--------------------------------------------------------------------------
        |
        | La campaña define cómo participan los usuarios.
        |
        | Para:
        |
        |     participant_type = warehouse
        |
        | usamos el warehouse_id del usuario autenticado.
        |
        | Esto permite que:
        |
        |     Almacén 2
        |       ├── Zona 1
        |       ├── Zona 2
        |       ├── Sucursal 1
        |       └── Sucursal 2
        |
        | participen juntos.
        |
        */

            if (
                $campaign->participant_type === 'warehouse'
                && $user?->warehouse_id !== null
            ) {

                $rankingQuery->where(
                    'warehouse_id',
                    $user->warehouse_id
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Orden del ranking Cashback
        |--------------------------------------------------------------------------
        */

            if (
                $campaign->ranking_type === 'sales'
            ) {

                $rankingQuery
                    ->orderByDesc('sales_total')
                    ->orderByDesc('invoice_count')
                    ->orderBy('user_id');
            } else {

                $rankingQuery
                    ->orderByDesc('cashback_total')
                    ->orderByDesc('sales_total')
                    ->orderBy('invoice_count')
                    ->orderBy('user_id');
            }

            $rankings = $rankingQuery->get();

            /*
        |--------------------------------------------------------------------------
        | Premios
        |--------------------------------------------------------------------------
        */

            $rewards = $campaign
                ->rankingRewards()
                ->with('rewardType')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'posicion'
                )
                ->get();

            $rewardsByPosition = $rewards
                ->keyBy('posicion');

            /*
        |--------------------------------------------------------------------------
        | Construir ranking para móvil
        |--------------------------------------------------------------------------
        */

            $rankingData = [];

            $position = 1;

            foreach ($rankings as $ranking) {

                $reward = $rewardsByPosition->get(
                    $position
                );

                $rankingData[] = [

                    'position' =>
                    $position,

                    'user_id' =>
                    $ranking->user_id,

                    'first_name' =>
                    $ranking->user?->first_name,

                    'last_name' =>
                    $ranking->user?->last_name,

                    'name' =>
                    trim(
                        ($ranking->user?->first_name ?? '')
                            . ' '
                            . ($ranking->user?->last_name ?? '')
                    ),

                    'warehouse_id' =>
                    $ranking->warehouse_id,

                    'zone_id' =>
                    $ranking->zone_id,

                    'branch_id' =>
                    $ranking->branch_id,

                    'sales_total' =>
                    (float) $ranking->sales_total,

                    'cashback_total' =>
                    (float) $ranking->cashback_total,

                    'invoice_count' =>
                    (int) $ranking->invoice_count,

                    'is_me' =>
                    $userId !== null &&
                        (int) $ranking->user_id ===
                        (int) $userId,

                    'reward' => $reward
                        ? [

                            'id' =>
                            $reward->id,

                            'position' =>
                            $reward->posicion,

                            'title' =>
                            $reward->titulo,

                            'description' =>
                            $reward->descripcion,

                            'reward_type_id' =>
                            $reward->reward_type_id,

                            'reward_type' =>
                            $reward->rewardType?->nombre,

                            'reward_type_code' =>
                            $reward->rewardType?->codigo,

                            'value' =>
                            $reward->valor_referencial !== null
                                ? (float) $reward->valor_referencial
                                : null,

                            'multiplier' =>
                            $reward->multiplicador !== null
                                ? (float) $reward->multiplicador
                                : null,

                        ]
                        : null,
                ];

                $position++;
            }

            /*
        |--------------------------------------------------------------------------
        | Mi posición
        |--------------------------------------------------------------------------
        */

            $myRanking = null;

            if ($userId !== null) {

                foreach ($rankingData as $item) {

                    if (
                        (int) $item['user_id'] ===
                        (int) $userId
                    ) {

                        $myRanking = $item;

                        break;
                    }
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Tipo de ranking
        |--------------------------------------------------------------------------
        */

            $rankingMetric =
                $campaign->ranking_type === 'sales'
                ? 'sales'
                : 'cashback';

            $rankingLabel =
                $rankingMetric === 'sales'
                ? 'Mayor valor de ventas'
                : 'Mayor Cashback';

            /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

            $result[] = [

                'campaign' => [

                    'id' =>
                    $campaign->id,

                    'nombre' =>
                    $campaign->nombre,

                    'descripcion' =>
                    $campaign->descripcion,

                    'campaign_type' =>
                    $campaign->campaign_type,

                    'ranking_enabled' =>
                    (bool) $campaign->ranking_enabled,

                    'ranking_type' =>
                    $campaign->ranking_type,

                    'ranking_metric' =>
                    $rankingMetric,

                    'ranking_label' =>
                    $rankingLabel,

                    'fecha_inicio' =>
                    optional(
                        $campaign->fecha_inicio
                    )->format('Y-m-d'),

                    'fecha_fin' =>
                    optional(
                        $campaign->fecha_fin
                    )->format('Y-m-d'),
                ],

                'rewards' =>
                $rewards
                    ->map(
                        function ($reward) {

                            return [

                                'id' =>
                                $reward->id,

                                'position' =>
                                $reward->posicion,

                                'title' =>
                                $reward->titulo,

                                'description' =>
                                $reward->descripcion,

                                'reward_type_id' =>
                                $reward->reward_type_id,

                                'reward_type' =>
                                $reward->rewardType?->nombre,

                                'reward_type_code' =>
                                $reward->rewardType?->codigo,

                                'value' =>
                                $reward->valor_referencial !== null
                                    ? (float) $reward->valor_referencial
                                    : null,

                                'multiplier' =>
                                $reward->multiplicador !== null
                                    ? (float) $reward->multiplicador
                                    : null,
                            ];
                        }
                    )
                    ->values()
                    ->all(),

                'ranking' =>
                $rankingData,

                'my_ranking' =>
                $myRanking,

                'total_participants' =>
                count($rankingData),
            ];
        }

        return $result;
    }
    /**
     * Obtener ranking acumulado para la aplicación móvil.
     *
     * Exclusivo para campañas:
     *
     *     campaign_type = ranking_accumulated
     *
     * No modifica ni consulta el ranking Cashback.
     */
    public function getMobileAccumulatedRanking(
        ?int $userId = null
    ): array {

        $campaigns = CashbackCampaign::query()

            ->where(
                'campaign_type',
                'ranking_accumulated'
            )

            ->where(
                'activo',
                true
            )

            ->whereDate(
                'fecha_inicio',
                '<=',
                now()->toDateString()
            )

            ->whereDate(
                'fecha_fin',
                '>=',
                now()->toDateString()
            )

            ->orderBy(
                'fecha_fin'
            )

            ->get();

        $result = [];

        foreach ($campaigns as $campaign) {

            /*
        |--------------------------------------------------------------------------
        | Ranking
        |--------------------------------------------------------------------------
        |
        | Ranking acumulado = mayor cantidad de ventas acumuladas.
        |
        */

            $rankings = CampaignUserRanking::query()

                ->with([
                    'user',
                    'warehouse',
                    'zone',
                    'branch',
                ])

                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )

                ->orderByDesc(
                    'sales_total'
                )

                ->orderByDesc(
                    'invoice_count'
                )

                ->orderBy(
                    'user_id'
                )

                ->get();

            /*
        |--------------------------------------------------------------------------
        | Premios
        |--------------------------------------------------------------------------
        */

            $rewards = $campaign
                ->rankingRewards()
                ->with('rewardType')
                ->where(
                    'activo',
                    true
                )
                ->orderBy(
                    'posicion'
                )
                ->get();

            $rewardsByPosition = $rewards
                ->keyBy('posicion');

            /*
        |--------------------------------------------------------------------------
        | Construir ranking
        |--------------------------------------------------------------------------
        */

            $rankingData = [];

            $position = 1;

            foreach ($rankings as $ranking) {

                $reward = $rewardsByPosition->get(
                    $position
                );

                $rankingData[] = [

                    'position' =>
                    $position,

                    'user_id' =>
                    $ranking->user_id,

                    'first_name' =>
                    $ranking->user?->first_name,

                    'last_name' =>
                    $ranking->user?->last_name,

                    'name' =>
                    trim(
                        ($ranking->user?->first_name ?? '')
                            . ' '
                            . ($ranking->user?->last_name ?? '')
                    ),

                    'warehouse_id' =>
                    $ranking->warehouse_id,

                    'zone_id' =>
                    $ranking->zone_id,

                    'branch_id' =>
                    $ranking->branch_id,

                    /*
                |--------------------------------------------------------------------------
                | Datos propios del acumulado
                |--------------------------------------------------------------------------
                */

                    'sales_total' =>
                    (float) $ranking->sales_total,

                    'cashback_total' =>
                    (float) $ranking->cashback_total,

                    'invoice_count' =>
                    (int) $ranking->invoice_count,

                    'is_me' =>
                    $userId !== null &&
                        (int) $ranking->user_id ===
                        (int) $userId,

                    /*
                |--------------------------------------------------------------------------
                | Premio
                |--------------------------------------------------------------------------
                */

                    'reward' => $reward
                        ? [

                            'id' =>
                            $reward->id,

                            'position' =>
                            $reward->posicion,

                            'title' =>
                            $reward->titulo,

                            'description' =>
                            $reward->descripcion,

                            'reward_type_id' =>
                            $reward->reward_type_id,

                            'reward_type' =>
                            $reward->rewardType?->nombre,

                            'reward_type_code' =>
                            $reward->rewardType?->codigo,

                            'value' =>
                            $reward->valor_referencial !== null
                                ? (float) $reward->valor_referencial
                                : null,

                            'multiplier' =>
                            null,

                        ]
                        : null,
                ];

                $position++;
            }

            /*
        |--------------------------------------------------------------------------
        | Mi posición
        |--------------------------------------------------------------------------
        */

            $myRanking = null;

            if ($userId !== null) {

                foreach ($rankingData as $item) {

                    if (
                        (int) $item['user_id'] ===
                        (int) $userId
                    ) {

                        $myRanking = $item;

                        break;
                    }
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Resultado
        |--------------------------------------------------------------------------
        */

            $result[] = [

                'campaign' => [

                    'id' =>
                    $campaign->id,

                    'nombre' =>
                    $campaign->nombre,

                    'descripcion' =>
                    $campaign->descripcion,

                    'campaign_type' =>
                    $campaign->campaign_type,

                    'ranking_enabled' =>
                    true,

                    'ranking_type' =>
                    'sales',

                    'ranking_metric' =>
                    'sales',

                    'ranking_label' =>
                    'Mayor valor acumulado',

                    'fecha_inicio' =>
                    optional(
                        $campaign->fecha_inicio
                    )->format('Y-m-d'),

                    'fecha_fin' =>
                    optional(
                        $campaign->fecha_fin
                    )->format('Y-m-d'),
                ],

                /*
            |--------------------------------------------------------------------------
            | Premios
            |--------------------------------------------------------------------------
            */

                'rewards' =>
                $rewards
                    ->map(
                        function ($reward) {

                            return [

                                'id' =>
                                $reward->id,

                                'position' =>
                                $reward->posicion,

                                'title' =>
                                $reward->titulo,

                                'description' =>
                                $reward->descripcion,

                                'reward_type_id' =>
                                $reward->reward_type_id,

                                'reward_type' =>
                                $reward->rewardType?->nombre,

                                'reward_type_code' =>
                                $reward->rewardType?->codigo,

                                'value' =>
                                $reward->valor_referencial !== null
                                    ? (float) $reward->valor_referencial
                                    : null,

                                'multiplier' =>
                                null,
                            ];
                        }
                    )
                    ->values()
                    ->all(),

                /*
            |--------------------------------------------------------------------------
            | Ranking
            |--------------------------------------------------------------------------
            */

                'ranking' =>
                $rankingData,

                /*
            |--------------------------------------------------------------------------
            | Mi ranking
            |--------------------------------------------------------------------------
            */

                'my_ranking' =>
                $myRanking,

                /*
            |--------------------------------------------------------------------------
            | Total participantes
            |--------------------------------------------------------------------------
            */

                'total_participants' =>
                count($rankingData),
            ];
        }

        return $result;
    }
    /**
     * Actualizar ranking a partir de una factura.
     */
    public function updateFromInvoice(
        CashbackCampaign $campaign,
        Invoice $invoice,
        float $salesTotal,
        float $cashbackTotal
    ): CampaignUserRanking {

        $invoice->loadMissing([
            'user',
            'branch',
        ]);

        $user = $invoice->user;

        if (!$user) {

            throw new \RuntimeException(
                'No fue posible identificar al usuario de la factura.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ubicación del usuario
        |--------------------------------------------------------------------------
        */

        $warehouseId = $user->warehouse_id;

        $zoneId = $user->zone_id;

        $branchId = $invoice->branch_id;

        /*
        |--------------------------------------------------------------------------
        | Buscar o crear ranking del usuario
        |--------------------------------------------------------------------------
        */

        $ranking = CampaignUserRanking::query()
            ->firstOrCreate(
                [
                    'cashback_campaign_id' =>
                    $campaign->id,

                    'user_id' =>
                    $user->id,
                ],
                [
                    'warehouse_id' =>
                    $warehouseId,

                    'zone_id' =>
                    $zoneId,

                    'branch_id' =>
                    $branchId,

                    'sales_total' =>
                    0,

                    'cashback_total' =>
                    0,

                    'invoice_count' =>
                    0,

                    'position' =>
                    0,
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Actualizar ubicación
        |--------------------------------------------------------------------------
        */

        $ranking->warehouse_id =
            $warehouseId;

        $ranking->zone_id =
            $zoneId;

        $ranking->branch_id =
            $branchId;

        /*
        |--------------------------------------------------------------------------
        | Acumulados
        |--------------------------------------------------------------------------
        */

        $ranking->sales_total =
            (float) $ranking->sales_total
            + $salesTotal;

        $ranking->cashback_total =
            (float) $ranking->cashback_total
            + $cashbackTotal;

        $ranking->invoice_count =
            (int) $ranking->invoice_count
            + 1;

        /*
        |--------------------------------------------------------------------------
        | La posición se recalcula al procesar el ranking.
        |--------------------------------------------------------------------------
        */

        $ranking->position = 0;

        $ranking->save();

        return $ranking->fresh([
            'user',
            'warehouse',
            'zone',
            'branch',
        ]);
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
