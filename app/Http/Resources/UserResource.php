<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($this->id === $request->user()?->id, $this->email),
            'role' => $this->role,
            'is_premium' => $this->is_premium,
            'premium_until' => $this->when($this->is_premium, $this->premium_until),
            'has_premium_access' => $this->hasPremiumAccess(),
            'created_at' => $this->created_at,
        ];
    }
}
