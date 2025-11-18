<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample free game
        Game::create([
            'title' => 'The Mystery of Echo Manor',
            'slug' => 'mystery-echo-manor',
            'tagline' => 'A beginner-friendly mystery adventure',
            'short_description' => 'Investigate a disappearance in an old manor house.',
            'long_description' => 'Someone has vanished from Echo Manor, and the police have found nothing. As a private investigator, you must uncover the truth hidden within the mansion walls.',
            'game_type' => 'mystery',
            'min_players' => 1,
            'max_players' => 4,
            'estimated_duration_minutes' => 45,
            'difficulty' => 'easy',
            'price_tier' => 'free',
            'price' => 0,
            'status' => 'published',
            'is_featured' => true,
            'game_data' => [
                'scenes' => [
                    [
                        'scene_id' => 'intro',
                        'is_start' => true,
                        'type' => 'message',
                        'content' => 'Welcome to Echo Manor. The investigation begins...',
                        'actions' => [
                            ['id' => 'start', 'label' => 'Begin Investigation', 'next_scene' => 'scene_01']
                        ]
                    ]
                ],
                'roles' => [],
                'modules' => []
            ],
        ]);

        // Create premium game example
        Game::create([
            'title' => 'Murder at Blackwood Estate',
            'slug' => 'murder-blackwood-estate',
            'tagline' => 'A complex Victorian murder mystery',
            'short_description' => 'Lord Blackwood is dead. Find the killer among the guests.',
            'long_description' => 'In this intricate murder mystery, you must interrogate suspects, analyze evidence, and uncover dark secrets to identify the killer before they strike again.',
            'game_type' => 'murder',
            'min_players' => 4,
            'max_players' => 8,
            'estimated_duration_minutes' => 120,
            'difficulty' => 'hard',
            'price_tier' => 'premium',
            'price' => 9.99,
            'status' => 'published',
            'is_featured' => true,
            'game_data' => [
                'scenes' => [],
                'roles' => [],
                'modules' => []
            ],
        ]);
    }
}
