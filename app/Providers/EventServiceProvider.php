<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\GameCreated::class => [
            \App\Listeners\ProcessGameAssets::class,
            \App\Listeners\GenerateLandingPage::class,
        ],
        \App\Events\SessionStarted::class => [
            \App\Listeners\NotifySessionPlayers::class,
            \App\Listeners\InitializeGameState::class,
        ],
        \App\Events\SessionCompleted::class => [
            \App\Listeners\UpdateGameStatistics::class,
            \App\Listeners\NotifySessionResults::class,
        ],
        \App\Events\PurchaseCompleted::class => [
            \App\Listeners\IssueLicense::class,
            \App\Listeners\SendPurchaseReceipt::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
