<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up abandoned sessions
        $schedule->call(function () {
            \App\Models\Session::where('status', 'in_progress')
                ->where('last_activity_at', '<', now()->subHours(2))
                ->update(['status' => 'abandoned']);
        })->hourly();

        // Expire old licenses
        $schedule->call(function () {
            \App\Models\License::where('status', 'active')
                ->whereNotNull('valid_until')
                ->where('valid_until', '<', now())
                ->update(['status' => 'expired']);
        })->daily();

        // Clean up old session events (keep last 30 days)
        $schedule->call(function () {
            \App\Models\SessionEvent::where('created_at', '<', now()->subDays(30))
                ->delete();
        })->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
