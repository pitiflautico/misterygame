<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('asset_id')->unique(); // e.g., "audio_01", "img_01", "vid_01"

            // Asset Type
            $table->enum('type', [
                'audio',
                'image',
                'video',
                'pdf',
                'fake_web',
                'push_notification',
                'fake_news'
            ]);

            // Storage
            $table->string('file_path')->nullable(); // S3 path or local path
            $table->string('file_url')->nullable(); // Public URL
            $table->string('file_mime')->nullable();
            $table->integer('file_size')->nullable(); // in bytes

            // Metadata (JSON)
            $table->json('metadata')->nullable(); // Different per type
            $table->text('description')->nullable();
            $table->text('ai_prompt')->nullable(); // Original AI prompt used to generate

            // TTS specific
            $table->text('tts_text')->nullable();
            $table->string('tts_voice')->nullable();
            $table->string('tts_effects')->nullable();

            // Image/Video specific
            $table->text('image_prompt')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Fake Web specific
            $table->string('web_type')->nullable(); // news, profile, article, blog
            $table->text('web_content')->nullable();

            // Processing status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['game_id', 'type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_assets');
    }
};
