<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_session()
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['status' => 'published', 'price_tier' => 'free']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/session/create', [
                'game_id' => $game->id,
                'max_players' => 6,
                'mode' => 'group',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'session' => ['id', 'session_code', 'game'],
            ]);

        $this->assertDatabaseHas('sessions', [
            'game_id' => $game->id,
            'host_user_id' => $user->id,
        ]);
    }

    public function test_user_can_join_session_with_code()
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $game = Game::factory()->create(['status' => 'published']);

        $session = Session::factory()->create([
            'game_id' => $game->id,
            'host_user_id' => $host->id,
            'status' => 'waiting',
        ]);

        $token = $player->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/session/join', [
                'session_code' => $session->session_code,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('session_players', [
            'session_id' => $session->id,
            'user_id' => $player->id,
        ]);
    }

    public function test_host_can_start_session()
    {
        $host = User::factory()->create();
        $game = Game::factory()->create(['status' => 'published']);
        $session = Session::factory()->create([
            'game_id' => $game->id,
            'host_user_id' => $host->id,
            'status' => 'waiting',
        ]);

        $token = $host->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/session/{$session->id}/start");

        $response->assertStatus(200);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_non_host_cannot_start_session()
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $game = Game::factory()->create(['status' => 'published']);

        $session = Session::factory()->create([
            'game_id' => $game->id,
            'host_user_id' => $host->id,
            'status' => 'waiting',
        ]);

        $token = $player->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/session/{$session->id}/start");

        $response->assertStatus(400);
    }

    public function test_can_get_session_state()
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['status' => 'published']);
        $session = Session::factory()->create([
            'game_id' => $game->id,
            'host_user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/session/{$session->id}/state");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session',
                'current_scene',
                'available_actions',
            ]);
    }
}
