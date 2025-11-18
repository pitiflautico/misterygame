<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'license_id',
        'transaction_id',
        'provider',
        'provider_transaction_id',
        'purchase_type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_details',
        'fulfilled',
        'fulfilled_at',
        'metadata',
        'error_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'metadata' => 'array',
        'fulfilled' => 'boolean',
        'fulfilled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
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

    /**
     * Mark as fulfilled
     */
    public function markAsFulfilled(): void
    {
        $this->update([
            'fulfilled' => true,
            'fulfilled_at' => now(),
        ]);
    }

    /**
     * Refund the purchase
     */
    public function refund(): void
    {
        $this->update(['status' => 'refunded']);

        // Revoke associated license if exists
        if ($this->license) {
            $this->license->revoke('Purchase refunded');
        }
    }
}
