<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRankingRewardRequest;
use App\Http\Requests\UpdateRankingRewardRequest;
use App\Models\CashbackCampaign;
use App\Models\RankingReward;
use App\Models\RewardType;
use Illuminate\Http\Request;

class RankingRewardController extends Controller
{
    /**
     * Mostrar listado.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $rankingRewards = RankingReward::with([
            'campaign',
            'rewardType',
        ])
            ->when($search, function ($query) use ($search) {

                $query->where('titulo', 'like', "%{$search}%")
                    ->orWhereHas('campaign', function ($q) use ($search) {

                        $q->where('nombre', 'like', "%{$search}%");
                    });
            })
            ->orderBy('cashback_campaign_id')
            ->orderBy('posicion')
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.incentivos.ranking_rewards.index',
            compact(
                'rankingRewards',
                'search'
            )
        );
    }

    /**
     * Formulario crear.
     */
    public function create()
    {
        $campaigns = CashbackCampaign::activas()
            ->orderBy('nombre')
            ->get();

        $rewardTypes = RewardType::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.incentivos.ranking_rewards.create',
            compact(
                'campaigns',
                'rewardTypes'
            )
        );
    }

    /**
     * Guardar.
     */
    public function store(StoreRankingRewardRequest $request)
    {
        RankingReward::create(
            $request->validated()
        );

        return redirect()
            ->route('ranking-rewards.index')
            ->with(
                'success',
                'Premio creado correctamente.'
            );
    }

    /**
     * Formulario editar.
     */
    public function edit(RankingReward $rankingReward)
    {
        $campaigns = CashbackCampaign::activas()
            ->orderBy('nombre')
            ->get();

        $rewardTypes = RewardType::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.incentivos.ranking_rewards.edit',
            compact(
                'rankingReward',
                'campaigns',
                'rewardTypes'
            )
        );
    }

    /**
     * Actualizar.
     */
    public function update(
        UpdateRankingRewardRequest $request,
        RankingReward $rankingReward
    ) {
        $rankingReward->update(
            $request->validated()
        );

        return redirect()
            ->route('ranking-rewards.index')
            ->with(
                'success',
                'Premio actualizado correctamente.'
            );
    }

    /**
     * Eliminar.
     */
    public function destroy(
        RankingReward $rankingReward
    ) {
        $rankingReward->delete();

        return redirect()
            ->route('ranking-rewards.index')
            ->with(
                'success',
                'Premio eliminado correctamente.'
            );
    }
}
