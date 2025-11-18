<?php

namespace App\Services\Multimedia;

use App\Models\Game;
use App\Models\GameAsset;
use Illuminate\Support\Facades\Storage;

class MultimediaService
{
    protected AudioGenerator $audioGenerator;
    protected ImageGenerator $imageGenerator;
    protected VideoGenerator $videoGenerator;
    protected PdfGenerator $pdfGenerator;
    protected FakeWebGenerator $fakeWebGenerator;

    public function __construct()
    {
        $this->audioGenerator = new AudioGenerator();
        $this->imageGenerator = new ImageGenerator();
        $this->videoGenerator = new VideoGenerator();
        $this->pdfGenerator = new PdfGenerator();
        $this->fakeWebGenerator = new FakeWebGenerator();
    }

    /**
     * Process all multimedia modules for a game
     */
    public function processGameAssets(Game $game, array $modules): array
    {
        $results = [];

        foreach ($modules as $module) {
            $result = $this->processModule($game, $module);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Process a single module
     */
    public function processModule(Game $game, array $module): GameAsset
    {
        $type = $module['module'] ?? $module['type'] ?? 'unknown';
        $assetId = $module['id'] ?? uniqid('asset_');

        // Create asset record
        $asset = GameAsset::create([
            'game_id' => $game->id,
            'asset_id' => $assetId,
            'type' => $type,
            'metadata' => $module,
            'description' => $module['description'] ?? null,
            'ai_prompt' => $module['prompt'] ?? $module['image_prompt'] ?? null,
            'status' => 'pending',
        ]);

        // Dispatch generation job
        dispatch(function () use ($asset, $module) {
            $this->generateAsset($asset, $module);
        });

        return $asset;
    }

    /**
     * Generate asset based on type
     */
    protected function generateAsset(GameAsset $asset, array $module): void
    {
        try {
            $asset->update(['status' => 'processing']);

            switch ($asset->type) {
                case 'audio':
                    $this->generateAudioAsset($asset, $module);
                    break;

                case 'image':
                    $this->generateImageAsset($asset, $module);
                    break;

                case 'video':
                    $this->generateVideoAsset($asset, $module);
                    break;

                case 'pdf':
                    $this->generatePdfAsset($asset, $module);
                    break;

                case 'fake_web':
                    $this->generateFakeWebAsset($asset, $module);
                    break;

                case 'fake_news':
                    $this->generateFakeNewsAsset($asset, $module);
                    break;

                case 'push_notification':
                    $this->processPushNotification($asset, $module);
                    break;

                default:
                    throw new \Exception("Unknown asset type: {$asset->type}");
            }

            $asset->update(['status' => 'completed']);
        } catch (\Exception $e) {
            $asset->markAsFailed($e->getMessage());
        }
    }

    /**
     * Generate audio asset
     */
    protected function generateAudioAsset(GameAsset $asset, array $module): void
    {
        $ttsText = $module['tts_text'] ?? '';
        $voice = $module['voice'] ?? config('services.tts.default_voice', 'alloy');
        $effects = $module['effects'] ?? null;

        // Update asset with TTS info
        $asset->update([
            'tts_text' => $ttsText,
            'tts_voice' => $voice,
            'tts_effects' => $effects,
        ]);

        // Generate audio file
        $audioData = $this->audioGenerator->generate($ttsText, $voice, $effects);

        // Save to storage
        $filePath = "games/{$asset->game_id}/audio/{$asset->asset_id}.mp3";
        Storage::disk('s3')->put($filePath, $audioData['content']);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'audio/mpeg',
            'file_size' => strlen($audioData['content']),
            'duration_seconds' => $audioData['duration'] ?? null,
        ]);
    }

    /**
     * Generate image asset
     */
    protected function generateImageAsset(GameAsset $asset, array $module): void
    {
        $prompt = $module['prompt'] ?? $module['image_prompt'] ?? '';
        $purpose = $module['purpose'] ?? 'general';

        // Update asset
        $asset->update(['image_prompt' => $prompt]);

        // Generate image
        $imageData = $this->imageGenerator->generate($prompt, $purpose);

        // Save to storage
        $filePath = "games/{$asset->game_id}/images/{$asset->asset_id}.png";
        Storage::disk('s3')->put($filePath, $imageData['content']);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'image/png',
            'file_size' => strlen($imageData['content']),
        ]);
    }

    /**
     * Generate video asset
     */
    protected function generateVideoAsset(GameAsset $asset, array $module): void
    {
        $prompt = $module['prompt'] ?? '';
        $duration = $module['duration'] ?? 6;

        // Generate video (placeholder - would integrate with actual video API)
        $videoData = $this->videoGenerator->generate($prompt, $duration);

        // Save to storage
        $filePath = "games/{$asset->game_id}/videos/{$asset->asset_id}.mp4";
        Storage::disk('s3')->put($filePath, $videoData['content']);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'video/mp4',
            'file_size' => strlen($videoData['content']),
            'duration_seconds' => $duration,
        ]);
    }

    /**
     * Generate PDF asset
     */
    protected function generatePdfAsset(GameAsset $asset, array $module): void
    {
        $content = $module['content'] ?? '';
        $layout = $module['layout'] ?? 'document';

        // Generate PDF
        $pdfData = $this->pdfGenerator->generate($content, $layout);

        // Save to storage
        $filePath = "games/{$asset->game_id}/pdfs/{$asset->asset_id}.pdf";
        Storage::disk('s3')->put($filePath, $pdfData['content']);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'application/pdf',
            'file_size' => strlen($pdfData['content']),
        ]);
    }

    /**
     * Generate fake web asset
     */
    protected function generateFakeWebAsset(GameAsset $asset, array $module): void
    {
        $webType = $module['type'] ?? 'article';
        $title = $module['title'] ?? '';
        $body = $module['body'] ?? '';
        $relatedLinks = $module['related_links'] ?? [];

        // Generate HTML
        $html = $this->fakeWebGenerator->generate($webType, $title, $body, $relatedLinks);

        // Save to storage
        $filePath = "games/{$asset->game_id}/web/{$asset->asset_id}.html";
        Storage::disk('s3')->put($filePath, $html);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'web_type' => $webType,
            'web_content' => $body,
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'text/html',
            'file_size' => strlen($html),
        ]);
    }

    /**
     * Generate fake news asset
     */
    protected function generateFakeNewsAsset(GameAsset $asset, array $module): void
    {
        $headline = $module['headline'] ?? '';
        $body = $module['body'] ?? '';
        $imagePrompt = $module['image_prompt'] ?? null;

        // Generate news image if prompt provided
        $imageUrl = null;
        if ($imagePrompt) {
            $imageData = $this->imageGenerator->generate($imagePrompt, 'news');
            $imagePath = "games/{$asset->game_id}/news/{$asset->asset_id}_img.png";
            Storage::disk('s3')->put($imagePath, $imageData['content']);
            $imageUrl = Storage::disk('s3')->url($imagePath);
        }

        // Generate news HTML
        $html = $this->fakeWebGenerator->generateNews($headline, $body, $imageUrl);

        // Save to storage
        $filePath = "games/{$asset->game_id}/news/{$asset->asset_id}.html";
        Storage::disk('s3')->put($filePath, $html);

        $fileUrl = Storage::disk('s3')->url($filePath);

        $asset->update([
            'web_type' => 'news',
            'web_content' => $body,
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_mime' => 'text/html',
            'file_size' => strlen($html),
        ]);
    }

    /**
     * Process push notification
     */
    protected function processPushNotification(GameAsset $asset, array $module): void
    {
        $text = $module['text'] ?? '';
        $triggerScene = $module['trigger_scene'] ?? null;

        $asset->update([
            'description' => $text,
            'metadata' => array_merge($module, [
                'trigger_scene' => $triggerScene,
            ]),
        ]);
    }

    /**
     * Get asset by ID
     */
    public function getAsset(string $assetId): ?GameAsset
    {
        return GameAsset::where('asset_id', $assetId)->first();
    }

    /**
     * Get all assets for a game
     */
    public function getGameAssets(Game $game, ?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $game->assets();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }
}
