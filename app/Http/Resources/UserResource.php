<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'full_name' => trim($this->first_name . ' ' . $this->last_name),

            'identification' => $this->identification,

            'username' => $this->username,

            'email' => $this->email,

            'phone' => $this->phone,

            'bank' => $this->bank,

            'account_type' => $this->account_type,

            'account_number' => $this->account_number,

            'is_active' => $this->is_active,

            'last_login' => $this->last_login,

            // Preparado para cuando agreguemos fotos
            'photo' => null,

            'role' => new RoleResource($this->whenLoaded('role')),

            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),

            'zone' => new ZoneResource($this->whenLoaded('zone')),

            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}
