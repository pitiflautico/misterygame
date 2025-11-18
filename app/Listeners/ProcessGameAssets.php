<?php

namespace App\Listeners;

use App\Events\GameCreated;
use App\Jobs\ProcessMultimediaAssets;

class ProcessGameAssets
{
    /**
     * Handle the event.
     */
    public function handle(GameCreated $event): void
    {
        $game = $event->game;
        $modules = $game->game_data['modules'] ?? [];

        if (!empty($modules)) {
            ProcessMultimediaAssets::dispatch($game, $modules);
        }
    }
}
