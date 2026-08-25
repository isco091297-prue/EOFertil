<?php

namespace App\Http\Controllers;

use App\Models\CashbackCampaign;
use App\Models\CashbackCampaignWinner;
use App\Services\Ranking\RankingWinnerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CashbackCampaignWinnerController extends Controller
{
    public function __construct(
        private readonly RankingWinnerService $service
    ) {}

    /**
     * Ganadores de la campaña.
     */
    public function index(
        CashbackCampaign $cashbackCampaign
    ): View {

        return view(
            'admin.incentivos.cashback_campaigns.winners.index',
            [
                'cashbackCampaign' => $cashbackCampaign,

                'winners' => $this->service->winners(
                    $cashbackCampaign
                ),
            ]
        );
    }

    /**
     * Marcar premio como entregado.
     */
    public function deliver(
        CashbackCampaign $cashbackCampaign,
        CashbackCampaignWinner $winner
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Seguridad
        |--------------------------------------------------------------------------
        |
        | Nos aseguramos de que el ganador realmente pertenezca
        | a la campaña que estamos visualizando.
        |
        */

        if (
            $winner->cashback_campaign_id !==
            $cashbackCampaign->id
        ) {
            abort(404);
        }

        $this->service->markAsDelivered(
            $winner
        );

        return redirect()
            ->route(
                'cashback-campaigns.winners',
                $cashbackCampaign
            )
            ->with(
                'success',
                'El premio fue marcado como entregado correctamente.'
            );
    }
}
