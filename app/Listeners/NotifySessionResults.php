<?php

namespace App\Listeners;

use App\Events\SessionCompleted;
use Illuminate\Support\Facades\Log;

class NotifySessionResults
{
    /**
     * Handle the event.
     */
    public function handle(SessionCompleted $event): void
    {
        $session = $event->session;
        $players = $session->players()->with('user')->get();

        foreach ($players as $player) {
            if ($player->user) {
                // Send completion notification with results
                Log::info("Session completed notification sent to user {$player->user_id}", [
                    'session_id' => $session->id,
                    'mystery_solved' => $session->mystery_solved,
                    'score' => $session->score,
                ]);

                // You can implement email/push notification here
                // $player->user->notify(new SessionCompletedNotification($session));
            }
        }
    }
}
