<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'display_name' => $this->getDisplayName(),
            'role_name' => $this->role_name,
            'is_host' => $this->is_host,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'actions_taken' => $this->actions_taken,
            'joined_at' => $this->joined_at,
            'last_action_at' => $this->last_action_at,
        ];
    }
}
