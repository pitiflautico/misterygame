<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Game::class => \App\Policies\GamePolicy::class,
        \App\Models\Session::class => \App\Policies\SessionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('creator', function ($user) {
            return $user->isCreator();
        });

        Gate::define('manage-game', function ($user, $game) {
            return $user->isAdmin() || $game->created_by === $user->id;
        });

        Gate::define('manage-session', function ($user, $session) {
            return $session->host_user_id === $user->id;
        });

        Gate::define('access-game', function ($user, $game) {
            $licenseService = app(\App\Services\License\LicenseService::class);
            return $licenseService->hasAccess($user, $game);
        });
    }
}
