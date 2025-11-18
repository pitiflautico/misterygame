<?php

namespace App\Jobs;

use App\Models\GameAsset;
use App\Services\Multimedia\AudioGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateAudioAsset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public GameAsset $asset;

    public function __construct(GameAsset $asset)
    {
        $this->asset = $asset;
    }

    public function handle(AudioGenerator $generator): void
    {
        try {
            $this->asset->update(['status' => 'processing']);

            $audioData = $generator->generate(
                $this->asset->tts_text,
                $this->asset->tts_voice ?? 'alloy',
                $this->asset->tts_effects
            );

            $filePath = "games/{$this->asset->game_id}/audio/{$this->asset->asset_id}.mp3";
            Storage::disk('s3')->put($filePath, $audioData['content']);

            $this->asset->markAsCompleted(
                $filePath,
                Storage::disk('s3')->url($filePath)
            );
        } catch (\Exception $e) {
            $this->asset->markAsFailed($e->getMessage());
        }
    }
}
