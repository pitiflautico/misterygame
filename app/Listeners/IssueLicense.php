<?php

namespace App\Listeners;

use App\Events\PurchaseCompleted;
use App\Services\License\LicenseService;

class IssueLicense
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseCompleted $event): void
    {
        $purchase = $event->purchase;

        // If license doesn't exist yet, create it
        if (!$purchase->license_id && $purchase->game_id) {
            $license = $this->licenseService->createLicense(
                $purchase->user,
                $purchase->game,
                'individual_purchase',
                $purchase->amount
            );

            $purchase->update(['license_id' => $license->id]);
            $purchase->markAsFulfilled();
        }
    }
}
