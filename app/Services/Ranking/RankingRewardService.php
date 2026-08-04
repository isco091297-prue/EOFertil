<?php

namespace App\Services\Ranking;
use App\Models\CashbackCampaign;
use App\Models\RankingReward;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RankingRewardService
{
    public function list(
        CashbackCampaign $campaign,
        ?string $search = null
    ): LengthAwarePaginator {

        return RankingReward::query()
            ->with('rewardType')
            ->where(
                'cashback_campaign_id',
                $campaign->id
            )
            ->when(
                $search,
                function ($query) use ($search) {

                    $query->where(function ($q) use ($search) {

                        $q->where(
                            'titulo',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'descripcion',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->orderBy('posicion')
            ->paginate(15)
            ->withQueryString();
    }

    public function store(
        CashbackCampaign $campaign,
        array $data
    ): RankingReward {

        $data['cashback_campaign_id'] = $campaign->id;

        return RankingReward::create(
            $data
        );
    }

    public function update(
        RankingReward $reward,
        array $data
    ): RankingReward {

        $reward->update(
            $data
        );

        return $reward;
    }

    public function delete(
        RankingReward $reward
    ): void {

        $reward->delete();
    }
}
