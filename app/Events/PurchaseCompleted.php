<?php

namespace App\Events;

use App\Models\PurchaseLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseCompleted
{
    use Dispatchable, SerializesModels;

    public PurchaseLog $purchase;

    public function __construct(PurchaseLog $purchase)
    {
        $this->purchase = $purchase;
    }
}
