<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityValidator
{
    /**
     * Dangerous keywords that should be blocked
     */
    protected array $dangerousKeywords = [
        'police',
        'emergency',
        '911',
        'bomb',
        'weapon',
        'kill',
        'suicide',
        'real address',
        'real phone',
        'social security',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('security.enable_action_validation', true)) {
            return $next($request);
        }

        // Validate game creation/update requests
        if ($request->isMethod('post') || $request->isMethod('put')) {
            $content = json_encode($request->all());

            foreach ($this->dangerousKeywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    \Log::warning('Potentially dangerous content detected', [
                        'keyword' => $keyword,
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                    ]);

                    // Could block or just log depending on configuration
                    if (config('security.block_dangerous_content', false)) {
                        return response()->json([
                            'error' => 'Content validation failed. Please avoid real-world dangerous references.',
                        ], 400);
                    }
                }
            }
        }

        return $next($request);
    }
}
