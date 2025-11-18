<?php

namespace App\Services\GameEngine;

use App\Models\Session;
use Illuminate\Support\Facades\Cache;

class StateManager
{
    protected Session $session;
    protected string $cacheKey;
    protected int $cacheTTL = 7200; // 2 hours

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->cacheKey = "session_state_{$session->id}";
    }

    /**
     * Initialize game state
     */
    public function initialize(array $gameData): void
    {
        $initialState = [
            'flags' => [],
            'variables' => [],
            'discovered_clues' => [],
            'player_decisions' => [],
            'visited_scenes' => [],
            'inventory' => [],
            'timestamp' => now()->timestamp,
        ];

        // Set initial variables from game data
        if (isset($gameData['initial_state'])) {
            $initialState = array_merge($initialState, $gameData['initial_state']);
        }

        $this->setState($initialState);
    }

    /**
     * Get current state
     */
    public function getState(): array
    {
        // Try cache first
        $state = Cache::get($this->cacheKey);

        if ($state !== null) {
            return $state;
        }

        // Fallback to database
        $state = [
            'flags' => $this->session->flags ?? [],
            'variables' => $this->session->game_state ?? [],
            'discovered_clues' => $this->session->discovered_clues ?? [],
            'player_decisions' => $this->session->player_decisions ?? [],
            'visited_scenes' => [],
            'inventory' => [],
            'timestamp' => now()->timestamp,
        ];

        // Cache it
        Cache::put($this->cacheKey, $state, $this->cacheTTL);

        return $state;
    }

    /**
     * Set complete state
     */
    public function setState(array $state): void
    {
        // Update cache
        Cache::put($this->cacheKey, $state, $this->cacheTTL);

        // Update database (less frequently for performance)
        $this->session->update([
            'game_state' => $state['variables'] ?? [],
            'flags' => $state['flags'] ?? [],
            'discovered_clues' => $state['discovered_clues'] ?? [],
            'player_decisions' => $state['player_decisions'] ?? [],
        ]);
    }

    /**
     * Set a flag
     */
    public function setFlag(string $key, $value): void
    {
        $state = $this->getState();
        $state['flags'][$key] = $value;
        $state['timestamp'] = now()->timestamp;
        $this->setState($state);
    }

    /**
     * Get a flag value
     */
    public function getFlag(string $key, $default = null)
    {
        $state = $this->getState();
        return $state['flags'][$key] ?? $default;
    }

    /**
     * Set a variable
     */
    public function setVariable(string $key, $value): void
    {
        $state = $this->getState();
        $state['variables'][$key] = $value;
        $state['timestamp'] = now()->timestamp;
        $this->setState($state);
    }

    /**
     * Get a variable value
     */
    public function getVariable(string $key, $default = null)
    {
        $state = $this->getState();
        return $state['variables'][$key] ?? $default;
    }

    /**
     * Add discovered clue
     */
    public function addClue(string $clueId, array $clueData): void
    {
        $state = $this->getState();
        $state['discovered_clues'][$clueId] = array_merge($clueData, [
            'discovered_at' => now()->toIso8601String(),
        ]);
        $state['timestamp'] = now()->timestamp;
        $this->setState($state);
    }

    /**
     * Check if clue is discovered
     */
    public function hasClue(string $clueId): bool
    {
        $state = $this->getState();
        return isset($state['discovered_clues'][$clueId]);
    }

    /**
     * Mark scene as visited
     */
    public function markSceneVisited(string $sceneId): void
    {
        $state = $this->getState();
        if (!in_array($sceneId, $state['visited_scenes'])) {
            $state['visited_scenes'][] = $sceneId;
            $state['timestamp'] = now()->timestamp;
            $this->setState($state);
        }
    }

    /**
     * Check if scene was visited
     */
    public function hasVisitedScene(string $sceneId): bool
    {
        $state = $this->getState();
        return in_array($sceneId, $state['visited_scenes']);
    }

    /**
     * Add item to inventory
     */
    public function addToInventory(string $itemId, array $itemData): void
    {
        $state = $this->getState();
        $state['inventory'][$itemId] = $itemData;
        $state['timestamp'] = now()->timestamp;
        $this->setState($state);
    }

    /**
     * Check if item is in inventory
     */
    public function hasInInventory(string $itemId): bool
    {
        $state = $this->getState();
        return isset($state['inventory'][$itemId]);
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Persist state to database immediately
     */
    public function persist(): void
    {
        $state = $this->getState();
        $this->session->update([
            'game_state' => $state['variables'] ?? [],
            'flags' => $state['flags'] ?? [],
            'discovered_clues' => $state['discovered_clues'] ?? [],
            'player_decisions' => $state['player_decisions'] ?? [],
            'last_activity_at' => now(),
        ]);
    }
}
