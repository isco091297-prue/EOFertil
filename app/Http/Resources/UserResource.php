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

            'username' => $this->username,

            'email' => $this->email,

            'is_active' => $this->is_active,

            'last_login' => $this->last_login,

            'role' => [

                'id' => $this->role?->id,

                'name' => $this->role?->name,

            ],
            'welcome_completed_at' => $this->welcome_completed_at,

        ];
    }
}
