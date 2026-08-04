<?php

namespace App\Http\Controllers;

use App\Models\CashbackCampaign;
use App\Services\Ranking\CampaignUserRankingService;
use Illuminate\View\View;

class CashbackCampaignRankingController extends Controller
{
    public function __construct(
        private readonly CampaignUserRankingService $service
    ) {}

    /**
     * Ranking de la campaña.
     */
    public function index(
        CashbackCampaign $cashbackCampaign
    ): View {

        return view(
            'admin.incentivos.cashback_campaigns.ranking.index',
            [
                'cashbackCampaign' => $cashbackCampaign,
                'ranking' => $this->service->getRanking(
                    $cashbackCampaign
                ),
            ]
        );
    }
}
