<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Session;
use App\Services\License\LicenseService;
use App\Services\Session\SessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    protected SessionService $sessionService;
    protected LicenseService $licenseService;

    public function __construct(SessionService $sessionService, LicenseService $licenseService)
    {
        $this->sessionService = $sessionService;
        $this->licenseService = $licenseService;
    }

    /**
     * Create a new session
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'max_players' => 'sometimes|integer|min:1|max:20',
            'mode' => 'sometimes|in:solo,group',
            'is_public' => 'sometimes|boolean',
        ]);

        $game = Game::findOrFail($validated['game_id']);
        $user = $request->user();

        // Check access
        if (!$this->licenseService->hasAccess($user, $game)) {
            return response()->json([
                'error' => 'You do not have access to this game',
            ], 403);
        }

        $session = $this->sessionService->createSession($user, $game, $validated);

        return response()->json([
            'session' => $session->load('game', 'players'),
            'message' => 'Session created successfully',
        ], 201);
    }

    /**
     * Join a session
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'session_code' => 'required|string|size:6',
        ]);

        try {
            $player = $this->sessionService->joinSession(
                $validated['session_code'],
                $request->user()
            );

            return response()->json([
                'player' => $player->load('session.game'),
                'message' => 'Joined session successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start a session
     */
    public function start(Request $request, Session $session)
    {
        try {
            $result = $this->sessionService->startSession($session, $request->user());

            return response()->json([
                'session' => $result['session'],
                'scene' => $result['scene'],
                'message' => 'Session started',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get session state
     */
    public function state(Request $request, Session $session)
    {
        // Verify user is part of session
        $isPlayer = $session->players()->where('user_id', $request->user()->id)->exists();

        if (!$isPlayer && $session->host_user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $state = $this->sessionService->getSessionState($session);

        return response()->json($state);
    }

    /**
     * Process action
     */
    public function processAction(Request $request, Session $session)
    {
        $validated = $request->validate([
            'action_id' => 'required|string',
            'data' => 'sometimes|array',
        ]);

        try {
            $result = $this->sessionService->processAction(
                $session,
                $request->user(),
                $validated['action_id'],
                $validated['data'] ?? []
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user's active sessions
     */
    public function mySessions(Request $request)
    {
        $sessions = $this->sessionService->getUserActiveSessions($request->user());

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Abandon session
     */
    public function abandon(Request $request, Session $session)
    {
        // Only host can abandon
        if ($session->host_user_id !== $request->user()->id) {
            return response()->json(['error' => 'Only host can abandon session'], 403);
        }

        $this->sessionService->abandonSession($session);

        return response()->json([
            'message' => 'Session abandoned',
        ]);
    }
}
