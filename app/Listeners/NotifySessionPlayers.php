<?php

namespace App\Listeners;

use App\Events\SessionStarted;
use Illuminate\Support\Facades\Log;

class NotifySessionPlayers
{
    /**
     * Handle the event.
     */
    public function handle(SessionStarted $event): void
    {
        $session = $event->session;

        // Load players
        $players = $session->players()->with('user')->get();

        foreach ($players as $player) {
            if ($player->user) {
                // Send notification (email, push, etc.)
                // This would integrate with your notification service
                Log::info("Session started notification sent to user {$player->user_id}");

                // You can use Laravel's notification system here:
                // $player->user->notify(new SessionStartedNotification($session));
            }
        }
    }
}
