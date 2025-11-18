<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'session_code' => strtoupper(Str::random(6)),
            'game_id' => Game::factory(),
            'host_user_id' => User::factory(),
            'max_players' => fake()->numberBetween(4, 10),
            'mode' => fake()->randomElement(['solo', 'group']),
            'is_public' => fake()->boolean(30),
            'status' => 'waiting',
        ];
    }

    /**
     * Indicate that the session is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'completed_at' => now(),
        ]);
    }
}
