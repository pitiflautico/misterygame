<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register service bindings
        $this->app->singleton(\App\Services\GameEngine\GameEngine::class, function ($app) {
            return new \App\Services\GameEngine\GameEngine(
                $app->make(\App\Models\Session::class)
            );
        });

        $this->app->singleton(\App\Services\AI\GameCreatorService::class, function ($app) {
            return new \App\Services\AI\GameCreatorService();
        });

        $this->app->singleton(\App\Services\Multimedia\MultimediaService::class, function ($app) {
            return new \App\Services\Multimedia\MultimediaService();
        });

        $this->app->singleton(\App\Services\Session\SessionService::class, function ($app) {
            return new \App\Services\Session\SessionService();
        });

        $this->app->singleton(\App\Services\License\LicenseService::class, function ($app) {
            return new \App\Services\License\LicenseService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for MySQL string length
        Schema::defaultStringLength(191);

        // Register model observers
        \App\Models\Game::observe(\App\Observers\GameObserver::class);
        \App\Models\Session::observe(\App\Observers\SessionObserver::class);
        \App\Models\License::observe(\App\Observers\LicenseObserver::class);
    }
}
