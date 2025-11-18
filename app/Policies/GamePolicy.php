<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    /**
     * Determine if the user can view the game.
     */
    public function view(User $user, Game $game): bool
    {
        return $game->isPublished() || $user->isAdmin() || $game->created_by === $user->id;
    }

    /**
     * Determine if the user can update the game.
     */
    public function update(User $user, Game $game): bool
    {
        return $user->isAdmin() || $game->created_by === $user->id;
    }

    /**
     * Determine if the user can delete the game.
     */
    public function delete(User $user, Game $game): bool
    {
        return $user->isAdmin() || $game->created_by === $user->id;
    }

    /**
     * Determine if the user can publish the game.
     */
    public function publish(User $user, Game $game): bool
    {
        return $user->isAdmin();
    }
}
