<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRankingRewardRequest;
use App\Http\Requests\UpdateRankingRewardRequest;
use App\Models\CashbackCampaign;
use App\Models\RankingReward;
use App\Models\RewardType;
use App\Services\Ranking\RankingRewardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RankingRewardController extends Controller
{
    public function __construct(
        private readonly RankingRewardService $service
    ) {}

    public function index(
        Request $request,
        CashbackCampaign $cashbackCampaign
    ): View {

        $search = trim(
            $request->get('search')
        );

        $rankingRewards = $this->service->list(
            $cashbackCampaign,
            $search
        );

        return view(
            'admin.incentivos.ranking_rewards.index',
            compact(
                'cashbackCampaign',
                'rankingRewards',
                'search'
            )
        );
    }

    public function create(
        CashbackCampaign $cashbackCampaign
    ): View {

        $rewardTypes = RewardType::where(
            'activo',
            true
        )
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.incentivos.ranking_rewards.create',
            compact(
                'cashbackCampaign',
                'rewardTypes'
            )
        );
    }

    public function store(
        StoreRankingRewardRequest $request,
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {

        $this->service->store(
            $cashbackCampaign,
            $request->validated()
        );

        return redirect()
            ->route(
                'ranking-rewards.index',
                $cashbackCampaign
            )
            ->with(
                'success',
                'Premio creado correctamente.'
            );
    }

    public function edit(
        CashbackCampaign $cashbackCampaign,
        RankingReward $rankingReward
    ): View {

        $rewardTypes = RewardType::where(
            'activo',
            true
        )
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.incentivos.ranking_rewards.edit',
            compact(
                'cashbackCampaign',
                'rankingReward',
                'rewardTypes'
            )
        );
    }

    public function update(
        UpdateRankingRewardRequest $request,
        CashbackCampaign $cashbackCampaign,
        RankingReward $rankingReward
    ): RedirectResponse {

        $this->service->update(
            $rankingReward,
            $request->validated()
        );

        return redirect()
            ->route(
                'ranking-rewards.index',
                $cashbackCampaign
            )
            ->with(
                'success',
                'Premio actualizado correctamente.'
            );
    }

    public function destroy(
        CashbackCampaign $cashbackCampaign,
        RankingReward $rankingReward
    ): RedirectResponse {

        $this->service->delete(
            $rankingReward
        );

        return redirect()
            ->route(
                'ranking-rewards.index',
                $cashbackCampaign
            )
            ->with(
                'success',
                'Premio eliminado correctamente.'
            );
    }
}
