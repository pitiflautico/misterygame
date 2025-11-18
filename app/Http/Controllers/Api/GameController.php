<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\License\LicenseService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * List games (catalog)
     */
    public function index(Request $request)
    {
        $query = Game::published();

        // Filter by type
        if ($request->has('type')) {
            $query->where('game_type', $request->type);
        }

        // Filter by difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by price tier
        if ($request->has('price_tier')) {
            $query->where('price_tier', $request->price_tier);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'popular') {
            $query->orderBy('total_sessions', 'desc');
        } elseif ($sortBy === 'rating') {
            $query->orderBy('average_rating', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Featured first
        if ($request->get('featured_first', false)) {
            $query->orderBy('is_featured', 'desc');
        }

        $games = $query->paginate($request->get('per_page', 20));

        return GameResource::collection($games);
    }

    /**
     * Get game details
     */
    public function show(Request $request, $gameId)
    {
        $game = Game::with(['creator', 'assets'])
            ->where('id', $gameId)
            ->orWhere('slug', $gameId)
            ->firstOrFail();

        // Check if user has access (if authenticated)
        $hasAccess = false;
        if ($request->user()) {
            $hasAccess = $this->licenseService->hasAccess($request->user(), $game);
        } else {
            $hasAccess = $game->isFree();
        }

        return response()->json([
            'game' => new GameResource($game),
            'has_access' => $hasAccess,
        ]);
    }

    /**
     * Get game landing page
     */
    public function landingPage($gameId)
    {
        $game = Game::where('id', $gameId)
            ->orWhere('slug', $gameId)
            ->firstOrFail();

        return response()->json([
            'landing_page' => $game->landing_page_data,
            'game' => [
                'id' => $game->id,
                'title' => $game->title,
                'slug' => $game->slug,
                'price' => $game->price,
                'price_tier' => $game->price_tier,
            ],
        ]);
    }

    /**
     * Get featured games
     */
    public function featured()
    {
        $games = Game::published()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['games' => $games]);
    }

    /**
     * Get popular games
     */
    public function popular()
    {
        $games = Game::published()
            ->orderBy('total_sessions', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['games' => $games]);
    }

    /**
     * Get recommended games for user
     */
    public function recommended(Request $request)
    {
        $user = $request->user();

        // Simple recommendation: games of similar type to what user played
        $playedGameTypes = $user->sessionParticipations()
            ->with('session.game')
            ->get()
            ->pluck('session.game.game_type')
            ->unique();

        $games = Game::published()
            ->whereIn('game_type', $playedGameTypes)
            ->orderBy('average_rating', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['games' => $games]);
    }
}
