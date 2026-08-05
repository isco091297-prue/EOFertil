<?php

namespace App\Http\Controllers;

use App\Models\CashbackCampaign;
use App\Services\Cashback\CashbackCampaignParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashbackCampaignParticipantController extends Controller
{
    public function __construct(
        private readonly CashbackCampaignParticipantService $service
    ) {}

    /**
     * Mostrar participantes.
     */
    public function index(
        CashbackCampaign $cashbackCampaign
    ): View {

        return view(
            'admin.incentivos.cashback_campaigns.participants.index',
            array_merge(
                [
                    'cashbackCampaign' => $cashbackCampaign,
                ],
                $this->service->data(
                    $cashbackCampaign
                )
            )
        );
    }

    /**
     * Guardar participantes.
     */
    public function store(
        Request $request,
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {

        $data = $request->validate([

            'participant_type' => [
                'required',
                'in:all,warehouse,zone,branch',
            ],

            'warehouse_ids' => [
                'nullable',
                'array',
            ],

            'warehouse_ids.*' => [
                'integer',
                'exists:warehouses,id',
            ],

            'zone_ids' => [
                'nullable',
                'array',
            ],

            'zone_ids.*' => [
                'integer',
                'exists:zones,id',
            ],

            'branch_ids' => [
                'nullable',
                'array',
            ],

            'branch_ids.*' => [
                'integer',
                'exists:branches,id',
            ],

        ]);

        $this->service->save(
            $cashbackCampaign,
            $data
        );

        return redirect()
            ->route(
                'cashback-campaigns.participants',
                $cashbackCampaign
            )
            ->with(
                'success',
                'Participantes actualizados correctamente.'
            );
    }
}
