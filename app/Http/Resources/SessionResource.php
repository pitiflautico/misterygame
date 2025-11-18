<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_code' => $this->session_code,
            'game' => new GameResource($this->whenLoaded('game')),
            'host' => new UserResource($this->whenLoaded('host')),
            'max_players' => $this->max_players,
            'current_players' => $this->players()->count(),
            'mode' => $this->mode,
            'is_public' => $this->is_public,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'can_join' => $this->canAcceptPlayers(),
            'current_scene_id' => $this->current_scene_id,
            'players' => SessionPlayerResource::collection($this->whenLoaded('players')),
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'last_activity_at' => $this->last_activity_at,
            'total_play_time_minutes' => round($this->total_play_time_seconds / 60),
            'mystery_solved' => $this->mystery_solved,
            'score' => $this->score,
            'created_at' => $this->created_at,
        ];
    }
}
