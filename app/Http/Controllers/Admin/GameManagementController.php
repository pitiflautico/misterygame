<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Game;
use App\Services\AI\GameCreatorService;
use App\Services\Multimedia\MultimediaService;
use Illuminate\Http\Request;

class GameManagementController extends Controller
{
    protected GameCreatorService $gameCreator;
    protected MultimediaService $multimediaService;

    public function __construct(
        GameCreatorService $gameCreator,
        MultimediaService $multimediaService
    ) {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->gameCreator = $gameCreator;
        $this->multimediaService = $multimediaService;
    }

    /**
     * List all games (admin view)
     */
    public function index(Request $request)
    {
        $query = Game::with(['creator', 'sessions']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $games = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($games);
    }

    /**
     * Create game with AI
     */
    public function createWithAI(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|min:10',
            'game_type' => 'required|in:murder,mystery,liminal,investigation,horror,escape,interactive_movie,guided_adventure',
            'min_players' => 'sometimes|integer|min:1',
            'max_players' => 'sometimes|integer|min:1',
            'duration' => 'sometimes|integer|min:15',
            'difficulty' => 'sometimes|in:easy,medium,hard,expert',
        ]);

        try {
            $game = $this->gameCreator->generateGame($validated['prompt'], [
                'game_type' => $validated['game_type'],
                'players' => [
                    'min' => $validated['min_players'] ?? 1,
                    'max' => $validated['max_players'] ?? 6,
                ],
                'duration' => $validated['duration'] ?? 60,
                'difficulty' => $validated['difficulty'] ?? 'medium',
            ]);

            $game->update(['created_by' => $request->user()->id]);

            // Log admin action
            AdminLog::logAction(
                $request->user()->id,
                'game_created',
                "Created game '{$game->title}' with AI",
                'game',
                $game->id,
                null,
                ['title' => $game->title, 'type' => $game->game_type]
            );

            return response()->json([
                'game' => $game->load('assets'),
                'message' => 'Game created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create game: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update game
     */
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'short_description' => 'sometimes|string',
            'long_description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'price_tier' => 'sometimes|in:free,basic,premium,exclusive',
            'status' => 'sometimes|in:draft,testing,published,archived',
            'is_featured' => 'sometimes|boolean',
            'game_data' => 'sometimes|array',
        ]);

        $oldValues = $game->only(array_keys($validated));
        $game->update($validated);

        AdminLog::logAction(
            $request->user()->id,
            'game_updated',
            "Updated game '{$game->title}'",
            'game',
            $game->id,
            $oldValues,
            $validated
        );

        return response()->json([
            'game' => $game,
            'message' => 'Game updated successfully',
        ]);
    }

    /**
     * Publish game
     */
    public function publish(Request $request, Game $game)
    {
        $game->update(['status' => 'published']);

        AdminLog::logAction(
            $request->user()->id,
            'game_published',
            "Published game '{$game->title}'",
            'game',
            $game->id
        );

        return response()->json([
            'game' => $game,
            'message' => 'Game published successfully',
        ]);
    }

    /**
     * Archive game
     */
    public function archive(Request $request, Game $game)
    {
        $game->update(['status' => 'archived']);

        AdminLog::logAction(
            $request->user()->id,
            'game_deleted',
            "Archived game '{$game->title}'",
            'game',
            $game->id
        );

        return response()->json([
            'message' => 'Game archived successfully',
        ]);
    }

    /**
     * Regenerate part of game with AI
     */
    public function regeneratePart(Request $request, Game $game)
    {
        $validated = $request->validate([
            'part' => 'required|in:scenes,roles,modules,landing_page',
            'prompt' => 'sometimes|string',
        ]);

        try {
            $newPart = $this->gameCreator->regeneratePart(
                $game,
                $validated['part'],
                $validated['prompt'] ?? null
            );

            AdminLog::logAction(
                $request->user()->id,
                'game_updated',
                "Regenerated {$validated['part']} for game '{$game->title}'",
                'game',
                $game->id
            );

            return response()->json([
                'part' => $validated['part'],
                'data' => $newPart,
                'message' => 'Part regenerated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to regenerate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get game assets
     */
    public function assets(Game $game)
    {
        $assets = $game->assets()->orderBy('created_at', 'desc')->get();

        return response()->json(['assets' => $assets]);
    }

    /**
     * Get game statistics
     */
    public function statistics(Game $game)
    {
        $stats = [
            'total_sessions' => $game->total_sessions,
            'active_sessions' => $game->getActiveSessionsCount(),
            'total_players' => $game->total_players,
            'average_rating' => $game->average_rating,
            'total_reviews' => $game->total_reviews,
            'completion_rate' => $this->calculateCompletionRate($game),
        ];

        return response()->json($stats);
    }

    protected function calculateCompletionRate(Game $game): float
    {
        $totalSessions = $game->sessions()->count();
        if ($totalSessions === 0) {
            return 0;
        }

        $completedSessions = $game->sessions()
            ->where('status', 'completed')
            ->count();

        return round(($completedSessions / $totalSessions) * 100, 2);
    }
}
