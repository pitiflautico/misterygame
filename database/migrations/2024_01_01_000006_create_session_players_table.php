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
        Schema::create('session_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Nullable for guest players
            $table->string('guest_name')->nullable();

            // Role Assignment
            $table->string('role_id')->nullable();
            $table->string('role_name')->nullable();
            $table->json('role_data')->nullable(); // Complete role information
            $table->json('secret_information')->nullable(); // Secret info for this role

            // Player State
            $table->enum('status', ['invited', 'joined', 'active', 'disconnected', 'left'])->default('invited');
            $table->boolean('is_host')->default(false);
            $table->json('player_state')->nullable(); // Player-specific state
            $table->json('inventory')->nullable();
            $table->json('notes')->nullable();

            // Stats
            $table->integer('actions_taken')->default(0);
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

            $table->unique(['session_id', 'user_id']);
            $table->index(['session_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_players');
    }
};
