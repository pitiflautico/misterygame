<?php

namespace App\Listeners;

use App\Events\SessionStarted;
use App\Services\GameEngine\StateManager;

class InitializeGameState
{
    /**
     * Handle the event.
     */
    public function handle(SessionStarted $event): void
    {
        $session = $event->session;
        $stateManager = new StateManager($session);

        // Initialize game state with initial values
        $gameData = $session->game->game_data ?? [];
        $stateManager->initialize($gameData);

        // Persist initial state
        $stateManager->persist();
    }
}
