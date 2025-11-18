<?php

namespace App\Observers;

use App\Models\License;

class LicenseObserver
{
    /**
     * Handle the License "created" event.
     */
    public function created(License $license): void
    {
        // Log license creation for audit
        \Log::info('License created', [
            'license_id' => $license->id,
            'user_id' => $license->user_id,
            'game_id' => $license->game_id,
            'type' => $license->type,
        ]);
    }

    /**
     * Handle the License "updated" event.
     */
    public function updated(License $license): void
    {
        // Check if license was revoked
        if ($license->wasChanged('status') && $license->status === 'revoked') {
            \Log::warning('License revoked', [
                'license_id' => $license->id,
                'user_id' => $license->user_id,
                'reason' => $license->metadata['revoked_reason'] ?? 'Unknown',
            ]);
        }
    }
}
