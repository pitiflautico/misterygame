<?php

namespace App\Listeners;

use App\Events\GameCreated;
use App\Services\AI\LandingPageGenerator;

class GenerateLandingPage
{
    protected LandingPageGenerator $generator;

    public function __construct(LandingPageGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Handle the event.
     */
    public function handle(GameCreated $event): void
    {
        $game = $event->game;

        // Generate landing page if not already present
        if (empty($game->landing_page_data)) {
            $landingPage = $this->generator->generate($game, $game->game_data ?? []);
            $game->update(['landing_page_data' => $landingPage]);
        }
    }
}
