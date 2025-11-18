<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'short_description' => $this->short_description,
            'long_description' => $this->when($request->route()->named('games.show'), $this->long_description),
            'game_type' => $this->game_type,
            'game_type_label' => $this->getGameTypeLabel(),
            'min_players' => $this->min_players,
            'max_players' => $this->max_players,
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'difficulty' => $this->difficulty,
            'difficulty_label' => ucfirst($this->difficulty),
            'price_tier' => $this->price_tier,
            'price' => $this->price,
            'is_free' => $this->isFree(),
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'features' => $this->features,
            'roles_count' => $this->when(isset($this->roles), count($this->roles ?? [])),
            'scenes_count' => $this->when(isset($this->game_data['scenes']), count($this->game_data['scenes'] ?? [])),
            'stats' => [
                'total_sessions' => $this->total_sessions,
                'total_players' => $this->total_players,
                'average_rating' => $this->average_rating,
                'total_reviews' => $this->total_reviews,
            ],
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getGameTypeLabel(): string
    {
        $labels = config('platform.game_types', []);
        return $labels[$this->game_type] ?? ucfirst($this->game_type);
    }
}
