<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashbackBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cashback_total' => (float) $this['cashback_total'],

            'cashback_claimed' => (float) $this['cashback_claimed'],

            'cashback_available' => (float) $this['cashback_available'],
        ];
    }
}
