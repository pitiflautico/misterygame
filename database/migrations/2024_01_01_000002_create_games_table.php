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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('tagline')->nullable();
            $table->text('short_description');
            $table->longText('long_description')->nullable();

            // Game Type
            $table->enum('game_type', [
                'murder',
                'mystery',
                'liminal',
                'investigation',
                'horror',
                'escape',
                'interactive_movie',
                'guided_adventure'
            ])->default('mystery');

            // Game Configuration
            $table->integer('min_players')->default(1);
            $table->integer('max_players')->default(10);
            $table->integer('estimated_duration_minutes')->default(60);
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('medium');

            // Game Content (JSON)
            $table->json('game_data')->nullable(); // Complete game structure
            $table->json('landing_page_data')->nullable(); // Landing page content
            $table->json('roles')->nullable(); // Available roles
            $table->json('features')->nullable(); // List of features

            // Monetization
            $table->enum('price_tier', ['free', 'basic', 'premium', 'exclusive'])->default('free');
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('requires_code')->default(false);

            // Status
            $table->enum('status', ['draft', 'testing', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);

            // AI Generation Metadata
            $table->string('ai_model_used')->nullable();
            $table->timestamp('ai_generated_at')->nullable();
            $table->integer('ai_generation_version')->default(1);

            // Stats
            $table->integer('total_sessions')->default(0);
            $table->integer('total_players')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);

            // Creator
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['game_type', 'status']);
            $table->index('price_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
