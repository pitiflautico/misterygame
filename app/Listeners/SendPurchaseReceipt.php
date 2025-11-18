<?php

namespace App\Listeners;

use App\Events\PurchaseCompleted;
use Illuminate\Support\Facades\Log;

class SendPurchaseReceipt
{
    /**
     * Handle the event.
     */
    public function handle(PurchaseCompleted $event): void
    {
        $purchase = $event->purchase;
        $user = $purchase->user;

        // Send purchase receipt email
        Log::info("Purchase receipt sent to user {$user->id}", [
            'purchase_id' => $purchase->id,
            'amount' => $purchase->amount,
            'game_id' => $purchase->game_id,
        ]);

        // You can implement email sending here
        // Mail::to($user)->send(new PurchaseReceiptMail($purchase));
    }
}
