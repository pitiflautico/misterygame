<?php

namespace App\Services\GameEngine;

use App\Models\Session;
use App\Models\SessionEvent;
use App\Models\SessionPlayer;
use Illuminate\Support\Facades\Cache;

class GameEngine
{
    protected Session $session;
    protected array $gameData;
    protected SceneProcessor $sceneProcessor;
    protected StateManager $stateManager;
    protected FlagEvaluator $flagEvaluator;

    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->gameData = $session->game->game_data ?? [];
        $this->sceneProcessor = new SceneProcessor($this);
        $this->stateManager = new StateManager($session);
        $this->flagEvaluator = new FlagEvaluator($this->stateManager);
    }

    /**
     * Initialize the game session
     */
    public function initialize(): array
    {
        // Set initial state
        $this->stateManager->initialize($this->gameData);

        // Get first scene
        $firstScene = $this->getFirstScene();

        if (!$firstScene) {
            throw new \Exception('No initial scene found in game data');
        }

        // Set current scene
        $this->session->update(['current_scene_id' => $firstScene['scene_id']]);

        // Log event
        SessionEvent::log(
            $this->session->id,
            'system_event',
            null,
            $firstScene['scene_id'],
            null,
            ['action' => 'game_initialized'],
            'Game session initialized'
        );

        return $this->sceneProcessor->process($firstScene);
    }

    /**
     * Get current scene
     */
    public function getCurrentScene(): ?array
    {
        $sceneId = $this->session->current_scene_id;

        if (!$sceneId) {
            return null;
        }

        return $this->findScene($sceneId);
    }

    /**
     * Process player action
     */
    public function processAction(SessionPlayer $player, string $actionId, array $additionalData = []): array
    {
        $currentScene = $this->getCurrentScene();

        if (!$currentScene) {
            throw new \Exception('No current scene');
        }

        // Find the action in current scene
        $action = $this->findAction($currentScene, $actionId);

        if (!$action) {
            throw new \Exception('Action not found in current scene');
        }

        // Validate real action if required
        if ($currentScene['requires_real_action'] ?? false) {
            $this->validateRealAction($currentScene, $additionalData);
        }

        // Record the decision
        $this->session->recordDecision(
            $currentScene['scene_id'],
            $actionId,
            $player->user_id
        );

        // Update flags based on action
        if (isset($action['flags'])) {
            foreach ($action['flags'] as $key => $value) {
                $this->stateManager->setFlag($key, $value);
            }
        }

        // Process action effects
        $result = $this->processActionEffects($action, $player, $additionalData);

        // Log event
        SessionEvent::log(
            $this->session->id,
            'player_action',
            $player->user_id,
            $currentScene['scene_id'],
            $actionId,
            array_merge($additionalData, ['action' => $action]),
            $player->getDisplayName() . ' performed action: ' . ($action['label'] ?? $actionId)
        );

        // Get next scene
        $nextSceneId = $action['next_scene'] ?? null;

        if ($nextSceneId) {
            return $this->transitionToScene($nextSceneId);
        }

        return $result;
    }

    /**
     * Transition to a new scene
     */
    public function transitionToScene(string $sceneId): array
    {
        $scene = $this->findScene($sceneId);

        if (!$scene) {
            throw new \Exception("Scene not found: {$sceneId}");
        }

        // Check if scene is accessible (flags/conditions)
        if (!$this->isSceneAccessible($scene)) {
            throw new \Exception('Scene conditions not met');
        }

        // Update current scene
        $this->session->update(['current_scene_id' => $sceneId]);
        $this->session->updateActivity();

        // Log scene change
        SessionEvent::log(
            $this->session->id,
            'scene_change',
            null,
            $sceneId,
            null,
            ['previous_scene' => $this->session->current_scene_id],
            'Scene changed to: ' . $sceneId
        );

        // Process scene
        return $this->sceneProcessor->process($scene);
    }

    /**
     * Check if scene is accessible based on conditions
     */
    protected function isSceneAccessible(array $scene): bool
    {
        if (!isset($scene['flags_trigger']) || empty($scene['flags_trigger'])) {
            return true;
        }

        return $this->flagEvaluator->evaluate($scene['flags_trigger']);
    }

    /**
     * Find a scene by ID
     */
    protected function findScene(string $sceneId): ?array
    {
        $scenes = $this->gameData['scenes'] ?? [];

        foreach ($scenes as $scene) {
            if ($scene['scene_id'] === $sceneId) {
                return $scene;
            }
        }

        return null;
    }

    /**
     * Get first scene
     */
    protected function getFirstScene(): ?array
    {
        $scenes = $this->gameData['scenes'] ?? [];

        // Look for a scene marked as 'start' or get first scene
        foreach ($scenes as $scene) {
            if (($scene['is_start'] ?? false) === true) {
                return $scene;
            }
        }

        return $scenes[0] ?? null;
    }

    /**
     * Find action in scene
     */
    protected function findAction(array $scene, string $actionId): ?array
    {
        $actions = $scene['actions'] ?? [];

        foreach ($actions as $action) {
            if ($action['id'] === $actionId) {
                return $action;
            }
        }

        return null;
    }

    /**
     * Process action effects
     */
    protected function processActionEffects(array $action, SessionPlayer $player, array $additionalData): array
    {
        $result = [
            'success' => true,
            'message' => $action['result_message'] ?? 'Action completed',
            'effects' => [],
        ];

        // Add to inventory
        if (isset($action['add_to_inventory'])) {
            foreach ($action['add_to_inventory'] as $itemId => $itemData) {
                $player->addToInventory($itemId, $itemData);
                $result['effects'][] = 'Item added to inventory: ' . $itemId;
            }
        }

        // Discover clues
        if (isset($action['reveals_clue'])) {
            $clueId = $action['reveals_clue'];
            $clueData = $action['clue_data'] ?? [];
            $this->session->addClue($clueId, $clueData);

            SessionEvent::log(
                $this->session->id,
                'clue_discovered',
                $player->user_id,
                $this->session->current_scene_id,
                null,
                ['clue_id' => $clueId, 'clue_data' => $clueData],
                'Clue discovered: ' . $clueId
            );

            $result['effects'][] = 'Clue discovered: ' . $clueId;
        }

        // Update player state
        if (isset($action['update_player_state'])) {
            $playerState = $player->player_state ?? [];
            $playerState = array_merge($playerState, $action['update_player_state']);
            $player->update(['player_state' => $playerState]);
        }

        // Record action taken
        $player->recordAction();

        return $result;
    }

    /**
     * Validate real action (photos, QR codes, etc.)
     */
    protected function validateRealAction(array $scene, array $data): void
    {
        $expectedAction = $scene['expected_action'] ?? [];

        if (empty($expectedAction)) {
            return;
        }

        // This would integrate with actual validation logic
        // For now, we'll assume validation passes
        // In production, this would check QR codes, photo analysis, etc.
    }

    /**
     * Get session state
     */
    public function getState(): array
    {
        return $this->stateManager->getState();
    }

    /**
     * Get available actions for current scene
     */
    public function getAvailableActions(): array
    {
        $scene = $this->getCurrentScene();

        if (!$scene) {
            return [];
        }

        return $scene['actions'] ?? [];
    }

    /**
     * Check if game is completed
     */
    public function isCompleted(): bool
    {
        $currentScene = $this->getCurrentScene();

        if (!$currentScene) {
            return false;
        }

        return ($currentScene['is_ending'] ?? false) === true;
    }

    /**
     * Complete the game
     */
    public function complete(array $finalResults = []): void
    {
        $this->session->complete($finalResults);

        SessionEvent::log(
            $this->session->id,
            'system_event',
            null,
            $this->session->current_scene_id,
            null,
            ['results' => $finalResults],
            'Game completed'
        );
    }
}
