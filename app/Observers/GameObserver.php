<?php

namespace App\Observers;

use App\Models\Game;
use App\Events\GameCreated;

class GameObserver
{
    /**
     * Handle the Game "created" event.
     */
    public function created(Game $game): void
    {
        // Dispatch event for async processing
        event(new GameCreated($game));
    }

    /**
     * Handle the Game "updated" event.
     */
    public function updated(Game $game): void
    {
        // Clear cache if needed
        \Cache::forget("game_{$game->id}");
    }

    /**
     * Handle the Game "deleted" event.
     */
    public function deleted(Game $game): void
    {
        // Clean up related assets
        $game->assets()->delete();

        // Clear cache
        \Cache::forget("game_{$game->id}");
    }
}
