<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\License\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->middleware('auth:sanctum');
        $this->licenseService = $licenseService;
    }

    /**
     * Get user's licenses
     */
    public function index(Request $request)
    {
        $activeOnly = $request->get('active_only', false);
        $licenses = $this->licenseService->getUserLicenses($request->user(), $activeOnly);

        return response()->json(['licenses' => $licenses]);
    }

    /**
     * Purchase a game
     */
    public function purchaseGame(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'payment_provider' => 'sometimes|in:stripe,apple_iap,google_play,internal',
            'payment_details' => 'sometimes|array',
        ]);

        $game = Game::findOrFail($validated['game_id']);

        try {
            $license = $this->licenseService->purchaseGame(
                $request->user(),
                $game,
                $validated['payment_provider'] ?? 'internal',
                null,
                $validated['payment_details'] ?? []
            );

            return response()->json([
                'license' => $license,
                'message' => 'Game purchased successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Purchase failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Purchase host ticket
     */
    public function purchaseHostTicket(Request $request)
    {
        $validated = $request->validate([
            'game_id' => 'required|exists:games,id',
            'max_players' => 'sometimes|integer|min:1|max:20',
        ]);

        $game = Game::findOrFail($validated['game_id']);

        try {
            $license = $this->licenseService->createHostTicket(
                $request->user(),
                $game,
                $validated['max_players'] ?? 10
            );

            return response()->json([
                'license' => $license,
                'message' => 'Host ticket purchased successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Purchase failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Subscribe to premium
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:monthly,yearly',
            'payment_provider' => 'sometimes|in:stripe,apple_iap,google_play',
            'payment_details' => 'sometimes|array',
        ]);

        $duration = $validated['plan'] === 'monthly' ? 30 : 365;
        $price = config("platform.subscriptions.{$validated['plan']}.price", 9.99);

        try {
            $this->licenseService->grantPremiumSubscription(
                $request->user(),
                $duration,
                $price
            );

            return response()->json([
                'message' => 'Premium subscription activated',
                'user' => $request->user()->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Subscription failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Validate license key
     */
    public function validateKey(Request $request)
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = $this->licenseService->validateLicenseKey($validated['license_key']);

        if (!$license) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired license key',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'license' => $license->load('game'),
        ]);
    }

    /**
     * Check access to a game
     */
    public function checkAccess(Request $request, Game $game)
    {
        $hasAccess = $this->licenseService->hasAccess($request->user(), $game);

        return response()->json([
            'has_access' => $hasAccess,
            'game' => $game,
        ]);
    }
}
