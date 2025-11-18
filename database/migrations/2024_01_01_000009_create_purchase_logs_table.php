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
        Schema::create('purchase_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('license_id')->nullable()->constrained()->onDelete('set null');

            // Transaction Details
            $table->string('transaction_id')->unique();
            $table->enum('provider', ['stripe', 'apple_iap', 'google_play', 'internal'])->default('internal');
            $table->string('provider_transaction_id')->nullable();

            // Purchase Details
            $table->enum('purchase_type', [
                'game_purchase',
                'host_ticket',
                'subscription',
                'game_pack',
                'credits'
            ]);

            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');

            // Payment Method
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable(); // Sanitized payment info

            // Fulfillment
            $table->boolean('fulfilled')->default(false);
            $table->timestamp('fulfilled_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('transaction_id');
            $table->index(['provider', 'provider_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_logs');
    }
};
