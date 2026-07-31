<?php

namespace App\Http\Requests;

use App\Models\RewardType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRankingRewardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rankingReward = $this->route('rankingReward')
            ?? $this->route('ranking_reward');

        return [

            'cashback_campaign_id' => [
                'required',
                'exists:cashback_campaigns,id',
            ],

            'reward_type_id' => [
                'required',
                'exists:reward_types,id',
            ],

            'posicion' => [

                'required',

                'integer',

                'min:1',

                'max:100',

                Rule::unique('ranking_rewards')
                    ->ignore($rankingReward)
                    ->where(function ($query) {

                        return $query->where(
                            'cashback_campaign_id',
                            $this->cashback_campaign_id
                        );
                    }),

            ],

            'titulo' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'valor_referencial' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'multiplicador' => [
                'nullable',
                'numeric',
                'gt:0',
                'max:100',
            ],

            'activo' => [
                'required',
                'boolean',
            ],

        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $rewardType = RewardType::find(
                $this->reward_type_id
            );

            if (!$rewardType) {
                return;
            }

            if ($rewardType->codigo === 'cashback_multiplier') {

                if (empty($this->multiplicador)) {

                    $validator->errors()->add(
                        'multiplicador',
                        'El multiplicador es obligatorio.'
                    );
                }
            } else {

                if (empty($this->valor_referencial)) {

                    $validator->errors()->add(
                        'valor_referencial',
                        'El valor referencial es obligatorio.'
                    );
                }
            }
        });
    }
}
