<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'tagline' => fake()->sentence(6),
            'short_description' => fake()->paragraph(2),
            'long_description' => fake()->paragraph(5),
            'game_type' => fake()->randomElement(['murder', 'mystery', 'investigation', 'horror']),
            'min_players' => 1,
            'max_players' => fake()->numberBetween(4, 10),
            'estimated_duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'price_tier' => 'free',
            'price' => 0,
            'status' => 'published',
            'is_featured' => fake()->boolean(20),
            'game_data' => $this->generateGameData(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Generate basic game data structure
     */
    protected function generateGameData(): array
    {
        return [
            'scenes' => [
                [
                    'scene_id' => 'intro',
                    'is_start' => true,
                    'type' => 'message',
                    'content' => 'The story begins...',
                    'actions' => [
                        [
                            'id' => 'start',
                            'label' => 'Begin',
                            'next_scene' => 'scene_01'
                        ]
                    ]
                ]
            ],
            'roles' => [],
            'modules' => [],
        ];
    }

    /**
     * Indicate that the game is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the game is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the game is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_tier' => 'premium',
            'price' => fake()->randomFloat(2, 4.99, 19.99),
        ]);
    }
}
