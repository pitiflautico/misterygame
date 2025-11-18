<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\Multimedia\MultimediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMultimediaAssets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Game $game;
    public array $modules;

    public function __construct(Game $game, array $modules)
    {
        $this->game = $game;
        $this->modules = $modules;
    }

    public function handle(MultimediaService $multimediaService): void
    {
        $multimediaService->processGameAssets($this->game, $this->modules);
    }
}
