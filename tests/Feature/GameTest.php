<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_games()
    {
        Game::factory()->count(5)->create(['status' => 'published']);
        Game::factory()->count(2)->create(['status' => 'draft']);

        $response = $this->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_can_get_game_details()
    {
        $game = Game::factory()->create(['status' => 'published']);

        $response = $this->getJson("/api/games/{$game->id}");

        $response->assertStatus(200)
            ->assertJson([
                'game' => [
                    'id' => $game->id,
                    'title' => $game->title,
                ],
            ]);
    }

    public function test_can_get_featured_games()
    {
        Game::factory()->count(3)->create([
            'status' => 'published',
            'is_featured' => true,
        ]);

        Game::factory()->count(2)->create([
            'status' => 'published',
            'is_featured' => false,
        ]);

        $response = $this->getJson('/api/games/featured');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'games');
    }

    public function test_admin_can_create_game_with_ai()
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/games/create-with-ai', [
                'prompt' => 'Create a murder mystery in a Victorian mansion',
                'game_type' => 'murder',
                'min_players' => 4,
                'max_players' => 8,
                'duration' => 90,
                'difficulty' => 'medium',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'game' => ['id', 'title', 'game_type'],
            ]);
    }

    public function test_regular_user_cannot_create_game()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/admin/games/create-with-ai', [
                'prompt' => 'Create a murder mystery',
                'game_type' => 'murder',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_search_games_by_type()
    {
        Game::factory()->create(['status' => 'published', 'game_type' => 'murder']);
        Game::factory()->create(['status' => 'published', 'game_type' => 'mystery']);

        $response = $this->getJson('/api/games?type=murder');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
