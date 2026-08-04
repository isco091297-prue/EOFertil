<?php

namespace App\Http\Controllers;

use App\Models\CashbackCampaign;
use App\Services\Ranking\RankingWinnerService;
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
}
