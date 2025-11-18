<?php

namespace App\Observers;

use App\Models\Session;
use App\Events\SessionStarted;
use App\Events\SessionCompleted;

class SessionObserver
{
    /**
     * Handle the Session "updated" event.
     */
    public function updated(Session $session): void
    {
        // Check if session was started
        if ($session->wasChanged('status')) {
            if ($session->status === 'in_progress' && $session->getOriginal('status') === 'waiting') {
                event(new SessionStarted($session));
            }

            if ($session->status === 'completed') {
                event(new SessionCompleted($session));
            }
        }
    }

    /**
     * Handle the Session "deleted" event.
     */
    public function deleted(Session $session): void
    {
        // Clean up session state cache
        \Cache::forget("session_state_{$session->id}");
    }
}
