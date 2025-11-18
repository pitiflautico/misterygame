<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'license_key' => $this->license_key,
            'type' => $this->type,
            'status' => $this->status,
            'is_valid' => $this->isValid(),
            'game' => new GameResource($this->whenLoaded('game')),
            'max_sessions' => $this->max_sessions,
            'sessions_used' => $this->sessions_used,
            'sessions_remaining' => $this->max_sessions ? ($this->max_sessions - $this->sessions_used) : null,
            'max_players' => $this->max_players,
            'valid_from' => $this->valid_from,
            'valid_until' => $this->valid_until,
            'amount_paid' => $this->amount_paid,
            'currency' => $this->currency,
            'created_at' => $this->created_at,
        ];
    }
}
