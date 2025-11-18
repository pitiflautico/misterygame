<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'enable_action_validation' => env('SECURITY_ENABLE_ACTION_VALIDATION', true),

    'enable_content_moderation' => env('SECURITY_ENABLE_CONTENT_MODERATION', true),

    'block_real_authorities' => env('SECURITY_BLOCK_REAL_AUTHORITIES', true),

    'block_dangerous_content' => false, // Log only by default, don't block

    /*
    |--------------------------------------------------------------------------
    | Blocked Keywords
    |--------------------------------------------------------------------------
    */

    'dangerous_keywords' => [
        'police',
        'emergency',
        '911',
        '112', // European emergency number
        'fbi',
        'cia',
        'bomb',
        'weapon',
        'gun',
        'knife attack',
        'real address',
        'real phone',
        'social security',
        'credit card',
        'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Safe Action Types
    |--------------------------------------------------------------------------
    */

    'safe_action_types' => [
        'choice',
        'confirm',
        'reveal',
        'puzzle',
        'message',
    ],

    /*
    |--------------------------------------------------------------------------
    | Restricted Action Types (require admin approval)
    |--------------------------------------------------------------------------
    */

    'restricted_action_types' => [
        'take_photo',
        'scan_qr',
        'visit_location',
        'real_world_interaction',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limits' => [
        'game_creation' => [
            'max_attempts' => 10,
            'decay_minutes' => 60,
        ],
        'session_creation' => [
            'max_attempts' => 50,
            'decay_minutes' => 60,
        ],
        'api_calls' => [
            'max_attempts' => 1000,
            'decay_minutes' => 1,
        ],
    ],

];
