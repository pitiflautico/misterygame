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
        Schema::create('session_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Event Details
            $table->enum('event_type', [
                'scene_change',
                'player_action',
                'flag_update',
                'clue_discovered',
                'choice_made',
                'puzzle_solved',
                'timer_expired',
                'multimedia_played',
                'message_sent',
                'system_event'
            ]);

            $table->string('scene_id')->nullable();
            $table->string('action_id')->nullable();
            $table->json('event_data')->nullable(); // Full event payload
            $table->text('description')->nullable();

            // Results
            $table->boolean('success')->default(true);
            $table->text('result_message')->nullable();

            $table->timestamp('created_at');

            $table->index(['session_id', 'created_at']);
            $table->index(['session_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_events');
    }
};
