<?php

namespace App\Services\Session;

use App\Models\Game;
use App\Models\Session;
use App\Models\SessionPlayer;
use App\Models\User;
use App\Services\GameEngine\GameEngine;

class SessionService
{
    /**
     * Create a new game session
     */
    public function createSession(User $host, Game $game, array $options = []): Session
    {
        // Validate game is published
        if (!$game->isPublished() && !$host->isAdmin()) {
            throw new \Exception('This game is not published yet');
        }

        // Create session
        $session = Session::create([
            'game_id' => $game->id,
            'host_user_id' => $host->id,
            'max_players' => $options['max_players'] ?? $game->max_players,
            'mode' => $options['mode'] ?? 'group',
            'is_public' => $options['is_public'] ?? false,
            'status' => 'waiting',
        ]);

        // Add host as player
        $this->addPlayer($session, $host, true);

        // Increment game session count
        $game->incrementSessionCount();

        return $session;
    }

    /**
     * Join a session
     */
    public function joinSession(string $sessionCode, User $user): SessionPlayer
    {
        $session = Session::where('session_code', $sessionCode)->firstOrFail();

        if (!$session->canAcceptPlayers()) {
            throw new \Exception('Session is full or not accepting players');
        }

        // Check if user already in session
        $existing = $session->players()->where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        return $this->addPlayer($session, $user);
    }

    /**
     * Add player to session
     */
    public function addPlayer(Session $session, User $user, bool $isHost = false): SessionPlayer
    {
        $player = SessionPlayer::create([
            'session_id' => $session->id,
            'user_id' => $user->id,
            'status' => 'joined',
            'is_host' => $isHost,
        ]);

        // Assign role if available
        $this->assignRole($session, $player);

        return $player;
    }

    /**
     * Assign role to player
     */
    protected function assignRole(Session $session, SessionPlayer $player): void
    {
        $gameData = $session->game->game_data;
        $availableRoles = $gameData['roles'] ?? [];

        if (empty($availableRoles)) {
            return;
        }

        // Get assigned roles
        $assignedRoleIds = $session->players()
            ->whereNotNull('role_id')
            ->pluck('role_id')
            ->toArray();

        // Find first unassigned role
        foreach ($availableRoles as $role) {
            if (!in_array($role['role_id'], $assignedRoleIds)) {
                $player->update([
                    'role_id' => $role['role_id'],
                    'role_name' => $role['name'],
                    'role_data' => $role,
                    'secret_information' => $role['secret_information'] ?? null,
                ]);
                break;
            }
        }
    }

    /**
     * Start session
     */
    public function startSession(Session $session, User $user): array
    {
        // Verify user is host
        if ($session->host_user_id !== $user->id) {
            throw new \Exception('Only the host can start the session');
        }

        // Verify session is in waiting state
        if ($session->status !== 'waiting') {
            throw new \Exception('Session already started or completed');
        }

        // Start the session
        $session->start();

        // Initialize game engine
        $engine = new GameEngine($session);
        $firstScene = $engine->initialize();

        return [
            'session' => $session,
            'scene' => $firstScene,
        ];
    }

    /**
     * Get current scene for session
     */
    public function getCurrentScene(Session $session): ?array
    {
        $engine = new GameEngine($session);
        return $engine->getCurrentScene();
    }

    /**
     * Process player action
     */
    public function processAction(Session $session, User $user, string $actionId, array $data = []): array
    {
        $player = $session->players()->where('user_id', $user->id)->firstOrFail();

        $engine = new GameEngine($session);
        return $engine->processAction($player, $actionId, $data);
    }

    /**
     * Get session state
     */
    public function getSessionState(Session $session): array
    {
        $engine = new GameEngine($session);

        return [
            'session' => $session,
            'current_scene' => $engine->getCurrentScene(),
            'available_actions' => $engine->getAvailableActions(),
            'state' => $engine->getState(),
            'players' => $session->players,
            'is_completed' => $engine->isCompleted(),
        ];
    }

    /**
     * Complete session
     */
    public function completeSession(Session $session, array $results = []): void
    {
        $engine = new GameEngine($session);
        $engine->complete($results);
    }

    /**
     * Abandon session
     */
    public function abandonSession(Session $session): void
    {
        $session->update(['status' => 'abandoned']);
    }

    /**
     * Get active sessions for a user
     */
    public function getUserActiveSessions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Session::whereIn('status', ['waiting', 'in_progress'])
            ->where(function ($query) use ($user) {
                $query->where('host_user_id', $user->id)
                    ->orWhereHas('players', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['game', 'players'])
            ->get();
    }
}
