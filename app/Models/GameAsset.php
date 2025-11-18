<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'asset_id',
        'type',
        'file_path',
        'file_url',
        'file_mime',
        'file_size',
        'metadata',
        'description',
        'ai_prompt',
        'tts_text',
        'tts_voice',
        'tts_effects',
        'image_prompt',
        'duration_seconds',
        'web_type',
        'web_content',
        'status',
        'error_message',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Check if asset is ready
     */
    public function isReady(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if asset is audio
     */
    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    /**
     * Check if asset is image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if asset is video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $filePath, string $fileUrl): void
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
