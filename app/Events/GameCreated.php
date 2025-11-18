<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Game $game;

    /**
     * Create a new event instance.
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
