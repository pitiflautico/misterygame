<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    /**
     * Determine if the user can view the session.
     */
    public function view(User $user, Session $session): bool
    {
        return $session->host_user_id === $user->id ||
               $session->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can start the session.
     */
    public function start(User $user, Session $session): bool
    {
        return $session->host_user_id === $user->id;
    }

    /**
     * Determine if the user can abandon the session.
     */
    public function abandon(User $user, Session $session): bool
    {
        return $session->host_user_id === $user->id;
    }
}
