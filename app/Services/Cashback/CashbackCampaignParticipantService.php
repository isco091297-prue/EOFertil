<?php

namespace App\Services\Cashback;

use App\Models\Branch;
use App\Models\CashbackCampaign;
use App\Models\Warehouse;
use App\Models\Zone;

class CashbackCampaignParticipantService
{
    /**
     * Datos para la vista de participantes.
     */
    public function data(
        CashbackCampaign $campaign
    ): array {

        return [

            'warehouses' => Warehouse::query()
                ->orderBy('name')
                ->get(),

            'zones' => Zone::query()
                ->orderBy('name')
                ->get(),

            'branches' => Branch::query()
                ->orderBy('name')
                ->get(),

            'selectedWarehouses' => $campaign->scopes()
                ->whereNotNull('warehouse_id')
                ->pluck('warehouse_id')
                ->toArray(),

            'selectedZones' => $campaign->scopes()
                ->whereNotNull('zone_id')
                ->pluck('zone_id')
                ->toArray(),

            'selectedBranches' => $campaign->scopes()
                ->whereNotNull('branch_id')
                ->pluck('branch_id')
                ->toArray(),

        ];
    }

    /**
     * Guardar participantes.
     */
    public function save(
        CashbackCampaign $campaign,
        array $data
    ): void {

        $campaign->scopes()->delete();

        $participantType = $data['participant_type'] ?? 'all';

        if ($participantType === 'all') {
            return;
        }

        switch ($participantType) {

            case 'warehouse':

                $this->syncWarehouses(
                    $campaign,
                    $data['warehouse_ids'] ?? []
                );

                break;

            case 'zone':

                $this->syncZones(
                    $campaign,
                    $data['zone_ids'] ?? []
                );

                break;

            case 'branch':

                $this->syncBranches(
                    $campaign,
                    $data['branch_ids'] ?? []
                );

                break;
        }
    }

    /**
     * Guardar almacenes.
     */
    private function syncWarehouses(
        CashbackCampaign $campaign,
        array $ids
    ): void {

        foreach ($ids as $id) {

            $campaign->scopes()->create([

                'warehouse_id' => $id,

                'required' => true,

            ]);
        }
    }

    /**
     * Guardar zonas.
     */
    private function syncZones(
        CashbackCampaign $campaign,
        array $ids
    ): void {

        foreach ($ids as $id) {

            $campaign->scopes()->create([

                'zone_id' => $id,

                'required' => true,

            ]);
        }
    }

    /**
     * Guardar sucursales.
     */
    private function syncBranches(
        CashbackCampaign $campaign,
        array $ids
    ): void {

        foreach ($ids as $id) {

            $campaign->scopes()->create([

                'branch_id' => $id,

                'required' => true,

            ]);
        }
    }
}
