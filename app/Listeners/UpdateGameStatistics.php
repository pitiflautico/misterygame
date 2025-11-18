<?php

namespace App\Listeners;

use App\Events\SessionCompleted;

class UpdateGameStatistics
{
    /**
     * Handle the event.
     */
    public function handle(SessionCompleted $event): void
    {
        $session = $event->session;
        $game = $session->game;

        // Increment player count
        $playerCount = $session->players()->count();
        $game->incrementPlayerCount($playerCount);

        // You could also update average rating, completion rate, etc.
        // This is a placeholder for more complex statistics
    }
}
