<?php

namespace App\Services\License;

use App\Models\Game;
use App\Models\License;
use App\Models\PurchaseLog;
use App\Models\User;
use Illuminate\Support\Str;

class LicenseService
{
    /**
     * Check if user has access to a game
     */
    public function hasAccess(User $user, Game $game): bool
    {
        // Free games are accessible to everyone
        if ($game->isFree()) {
            return true;
        }

        // Check premium subscription
        if ($user->hasPremiumAccess()) {
            return true;
        }

        // Check specific game license
        return $this->hasValidLicense($user, $game);
    }

    /**
     * Check if user has valid license for game
     */
    public function hasValidLicense(User $user, Game $game): bool
    {
        return $user->licenses()
            ->where('game_id', $game->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>', now());
            })
            ->exists();
    }

    /**
     * Purchase game
     */
    public function purchaseGame(
        User $user,
        Game $game,
        string $paymentProvider = 'internal',
        ?string $transactionId = null,
        array $paymentDetails = []
    ): License {
        // Create purchase log
        $purchase = PurchaseLog::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'transaction_id' => $transactionId ?? Str::uuid(),
            'provider' => $paymentProvider,
            'provider_transaction_id' => $paymentDetails['provider_transaction_id'] ?? null,
            'purchase_type' => 'game_purchase',
            'amount' => $game->price,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_details' => $paymentDetails,
        ]);

        try {
            // Process payment (would integrate with Stripe, etc.)
            // For now, assume payment successful

            // Create license
            $license = $this->createLicense($user, $game, 'individual_purchase', $game->price);

            // Link license to purchase
            $purchase->update([
                'license_id' => $license->id,
                'status' => 'completed',
            ]);

            $purchase->markAsFulfilled();

            return $license;
        } catch (\Exception $e) {
            $purchase->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a license
     */
    public function createLicense(
        User $user,
        ?Game $game = null,
        string $type = 'individual_purchase',
        float $amountPaid = 0,
        ?int $maxSessions = null,
        ?\DateTime $validUntil = null
    ): License {
        return License::create([
            'user_id' => $user->id,
            'game_id' => $game?->id,
            'type' => $type,
            'status' => 'active',
            'max_sessions' => $maxSessions,
            'sessions_used' => 0,
            'amount_paid' => $amountPaid,
            'valid_from' => now(),
            'valid_until' => $validUntil,
        ]);
    }

    /**
     * Create host ticket (allows host to invite players without purchase)
     */
    public function createHostTicket(
        User $user,
        Game $game,
        int $maxPlayers = 10,
        float $price = null
    ): License {
        $price = $price ?? ($game->price * 0.8); // 20% discount for host tickets

        return $this->createLicense(
            $user,
            $game,
            'host_ticket',
            $price,
            1 // One session
        );
    }

    /**
     * Grant premium subscription
     */
    public function grantPremiumSubscription(
        User $user,
        int $durationDays = 30,
        float $price = 9.99
    ): void {
        $validUntil = now()->addDays($durationDays);

        $user->update([
            'is_premium' => true,
            'premium_until' => $validUntil,
        ]);

        // Create license record
        $this->createLicense(
            $user,
            null, // Global license
            'monthly_subscription',
            $price,
            null, // Unlimited sessions
            $validUntil
        );
    }

    /**
     * Use license for a session
     */
    public function useLicense(License $license): bool
    {
        if (!$license->isValid()) {
            throw new \Exception('License is not valid');
        }

        return $license->useForSession();
    }

    /**
     * Revoke license
     */
    public function revokeLicense(License $license, string $reason = null): void
    {
        $license->revoke($reason);
    }

    /**
     * Refund purchase
     */
    public function refundPurchase(PurchaseLog $purchase): void
    {
        if ($purchase->status === 'refunded') {
            throw new \Exception('Purchase already refunded');
        }

        // Process refund with payment provider
        // For now, just mark as refunded

        $purchase->refund();
    }

    /**
     * Validate license key
     */
    public function validateLicenseKey(string $licenseKey): ?License
    {
        $license = License::where('license_key', $licenseKey)->first();

        if (!$license) {
            return null;
        }

        return $license->isValid() ? $license : null;
    }

    /**
     * Get user licenses
     */
    public function getUserLicenses(User $user, bool $activeOnly = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = $user->licenses()->with('game');

        if ($activeOnly) {
            $query->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                });
        }

        return $query->get();
    }
}
