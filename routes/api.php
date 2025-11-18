<?php

use App\Http\Controllers\Admin\GameManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Games - Public
Route::prefix('games')->group(function () {
    Route::get('/', [GameController::class, 'index']);
    Route::get('/featured', [GameController::class, 'featured']);
    Route::get('/popular', [GameController::class, 'popular']);
    Route::get('/{game}', [GameController::class, 'show']);
    Route::get('/{game}/landing', [GameController::class, 'landingPage']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // Games - Authenticated
    Route::get('/games/recommended', [GameController::class, 'recommended']);

    // Licenses & Purchases
    Route::prefix('licenses')->group(function () {
        Route::get('/', [LicenseController::class, 'index']);
        Route::post('/purchase-game', [LicenseController::class, 'purchaseGame']);
        Route::post('/purchase-host-ticket', [LicenseController::class, 'purchaseHostTicket']);
        Route::post('/subscribe', [LicenseController::class, 'subscribe']);
        Route::post('/validate-key', [LicenseController::class, 'validateKey']);
        Route::get('/check-access/{game}', [LicenseController::class, 'checkAccess']);
    });

    // Sessions
    Route::prefix('session')->group(function () {
        Route::post('/create', [SessionController::class, 'create']);
        Route::post('/join', [SessionController::class, 'join']);
        Route::get('/my-sessions', [SessionController::class, 'mySessions']);

        Route::prefix('{session}')->group(function () {
            Route::post('/start', [SessionController::class, 'start']);
            Route::get('/state', [SessionController::class, 'state']);
            Route::post('/action', [SessionController::class, 'processAction']);
            Route::post('/abandon', [SessionController::class, 'abandon']);
        });
    });

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::prefix('games')->group(function () {
            Route::get('/', [GameManagementController::class, 'index']);
            Route::post('/create-with-ai', [GameManagementController::class, 'createWithAI']);

            Route::prefix('{game}')->group(function () {
                Route::put('/', [GameManagementController::class, 'update']);
                Route::post('/publish', [GameManagementController::class, 'publish']);
                Route::post('/archive', [GameManagementController::class, 'archive']);
                Route::post('/regenerate', [GameManagementController::class, 'regeneratePart']);
                Route::get('/assets', [GameManagementController::class, 'assets']);
                Route::get('/statistics', [GameManagementController::class, 'statistics']);
            });
        });

        // Sessions management
        Route::get('/sessions', function () {
            return response()->json([
                'sessions' => \App\Models\Session::with(['game', 'host', 'players'])
                    ->whereIn('status', ['waiting', 'in_progress'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(50),
            ]);
        });

        // Users management
        Route::get('/users', function () {
            return response()->json([
                'users' => \App\Models\User::withCount(['hostedSessions', 'licenses'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(50),
            ]);
        });

        // Admin logs
        Route::get('/logs', function () {
            return response()->json([
                'logs' => \App\Models\AdminLog::with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(100),
            ]);
        });

        // Platform statistics
        Route::get('/statistics', function () {
            return response()->json([
                'total_games' => \App\Models\Game::count(),
                'published_games' => \App\Models\Game::where('status', 'published')->count(),
                'total_users' => \App\Models\User::count(),
                'premium_users' => \App\Models\User::where('is_premium', true)->count(),
                'total_sessions' => \App\Models\Session::count(),
                'active_sessions' => \App\Models\Session::whereIn('status', ['waiting', 'in_progress'])->count(),
                'total_revenue' => \App\Models\PurchaseLog::where('status', 'completed')->sum('amount'),
            ]);
        });
    });
});

// Webhook routes (no auth)
Route::prefix('webhooks')->group(function () {
    Route::post('/stripe', function () {
        // Handle Stripe webhooks
        return response()->json(['received' => true]);
    });
});
