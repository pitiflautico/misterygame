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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique(); // 6-digit code for joining
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('host_user_id')->constrained('users')->onDelete('cascade');

            // Session Configuration
            $table->integer('max_players');
            $table->enum('mode', ['solo', 'group'])->default('group');
            $table->boolean('is_public')->default(false);

            // Session State
            $table->enum('status', ['waiting', 'in_progress', 'paused', 'completed', 'abandoned'])->default('waiting');
            $table->string('current_scene_id')->nullable();
            $table->json('game_state')->nullable(); // Current state of the game
            $table->json('flags')->nullable(); // Game flags/variables
            $table->json('discovered_clues')->nullable();
            $table->json('player_decisions')->nullable();

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('total_play_time_seconds')->default(0);

            // Results
            $table->json('final_results')->nullable();
            $table->boolean('mystery_solved')->default(false);
            $table->integer('score')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('session_code');
            $table->index(['status', 'game_id']);
            $table->index('host_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
