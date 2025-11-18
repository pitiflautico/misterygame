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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->nullable()->constrained()->onDelete('cascade'); // Null for global licenses

            // License Type
            $table->enum('type', [
                'individual_purchase',
                'host_ticket',
                'monthly_subscription',
                'game_pack',
                'lifetime_access',
                'promotional'
            ]);

            // License Details
            $table->string('license_key')->unique();
            $table->enum('status', ['active', 'expired', 'revoked', 'consumed'])->default('active');

            // Usage Limits
            $table->integer('max_sessions')->nullable(); // Null = unlimited
            $table->integer('sessions_used')->default(0);
            $table->integer('max_players')->nullable();

            // Validity
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            // Payment Information
            $table->string('payment_provider')->nullable(); // stripe, apple, google
            $table->string('payment_id')->nullable();
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['game_id', 'status']);
            $table->index('license_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
