<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Configuration
    |--------------------------------------------------------------------------
    */

    'max_players_per_session' => env('PLATFORM_MAX_PLAYERS_PER_SESSION', 20),

    'session_timeout' => env('PLATFORM_SESSION_TIMEOUT', 7200), // 2 hours in seconds

    'free_games_limit' => env('PLATFORM_FREE_GAMES_LIMIT', 3),

    /*
    |--------------------------------------------------------------------------
    | Game Types
    |--------------------------------------------------------------------------
    */

    'game_types' => [
        'murder' => 'Murder Mystery',
        'mystery' => 'Mystery',
        'liminal' => 'Liminal Experience',
        'investigation' => 'Investigation',
        'horror' => 'Soft Horror',
        'escape' => 'Narrative Escape',
        'interactive_movie' => 'Interactive Movie',
        'guided_adventure' => 'Guided Adventure',
    ],

    /*
    |--------------------------------------------------------------------------
    | Difficulty Levels
    |--------------------------------------------------------------------------
    */

    'difficulty_levels' => [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
        'expert' => 'Expert',
    ],

    /*
    |--------------------------------------------------------------------------
    | Price Tiers
    |--------------------------------------------------------------------------
    */

    'price_tiers' => [
        'free' => [
            'label' => 'Free',
            'price' => 0,
        ],
        'basic' => [
            'label' => 'Basic',
            'price' => 4.99,
        ],
        'premium' => [
            'label' => 'Premium',
            'price' => 9.99,
        ],
        'exclusive' => [
            'label' => 'Exclusive',
            'price' => 19.99,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    */

    'subscriptions' => [
        'monthly' => [
            'price' => 9.99,
            'duration_days' => 30,
            'benefits' => [
                'Unlimited access to all games',
                'Early access to new releases',
                'Priority support',
            ],
        ],
        'yearly' => [
            'price' => 99.99,
            'duration_days' => 365,
            'benefits' => [
                'Unlimited access to all games',
                'Early access to new releases',
                'Priority support',
                'Exclusive content',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Generation
    |--------------------------------------------------------------------------
    */

    'content_generation' => [
        'min_scenes' => 10,
        'recommended_scenes' => 20,
        'min_roles' => 1,
        'max_roles' => 10,
    ],

];
