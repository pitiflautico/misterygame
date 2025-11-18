<?php

if (!function_exists('game_type_label')) {
    /**
     * Get human-readable label for game type
     */
    function game_type_label(string $type): string
    {
        return config("platform.game_types.{$type}", ucfirst($type));
    }
}

if (!function_exists('difficulty_label')) {
    /**
     * Get human-readable label for difficulty
     */
    function difficulty_label(string $difficulty): string
    {
        return config("platform.difficulty_levels.{$difficulty}", ucfirst($difficulty));
    }
}

if (!function_exists('format_duration')) {
    /**
     * Format duration in minutes to human-readable format
     */
    function format_duration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} minutos";
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return "{$hours} " . ($hours === 1 ? 'hora' : 'horas');
        }

        return "{$hours}h {$mins}m";
    }
}

if (!function_exists('generate_session_code')) {
    /**
     * Generate a unique 6-character session code
     */
    function generate_session_code(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(6));
        } while (\App\Models\Session::where('session_code', $code)->exists());

        return $code;
    }
}

if (!function_exists('sanitize_game_content')) {
    /**
     * Sanitize game content to prevent XSS
     */
    function sanitize_game_content(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('check_dangerous_keywords')) {
    /**
     * Check if content contains dangerous keywords
     */
    function check_dangerous_keywords(string $content): array
    {
        $keywords = config('security.dangerous_keywords', []);
        $found = [];

        foreach ($keywords as $keyword) {
            if (stripos($content, $keyword) !== false) {
                $found[] = $keyword;
            }
        }

        return $found;
    }
}

if (!function_exists('is_production')) {
    /**
     * Check if app is in production
     */
    function is_production(): bool
    {
        return app()->environment('production');
    }
}

if (!function_exists('log_admin_action')) {
    /**
     * Log an admin action
     */
    function log_admin_action(
        int $userId,
        string $actionType,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): void {
        \App\Models\AdminLog::logAction(
            $userId,
            $actionType,
            $description,
            $entityType,
            $entityId,
            null,
            null,
            $metadata
        );
    }
}

if (!function_exists('format_price')) {
    /**
     * Format price with currency
     */
    function format_price(float $price, string $currency = 'USD'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? $currency;

        return $symbol . number_format($price, 2);
    }
}

if (!function_exists('player_count_label')) {
    /**
     * Format player count label
     */
    function player_count_label(int $min, int $max): string
    {
        if ($min === $max) {
            return "{$min} " . ($min === 1 ? 'jugador' : 'jugadores');
        }

        return "{$min}-{$max} jugadores";
    }
}
