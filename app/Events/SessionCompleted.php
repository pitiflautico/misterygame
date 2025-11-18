<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('session.' . $this->session->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'session_id' => $this->session->id,
            'status' => 'completed',
            'results' => $this->session->final_results,
            'message' => 'Session has been completed',
        ];
    }
}
