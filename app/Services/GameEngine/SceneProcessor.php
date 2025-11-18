<?php

namespace App\Services\GameEngine;

class SceneProcessor
{
    protected GameEngine $engine;

    public function __construct(GameEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Process a scene and prepare it for display
     */
    public function process(array $scene): array
    {
        $processed = [
            'scene_id' => $scene['scene_id'],
            'type' => $scene['type'] ?? 'message',
            'content' => $this->processContent($scene['content'] ?? ''),
            'actions' => $this->filterActions($scene['actions'] ?? []),
            'media' => $scene['media'] ?? null,
            'timer_seconds' => $scene['timer_seconds'] ?? null,
            'requires_real_action' => $scene['requires_real_action'] ?? false,
            'is_ending' => $scene['is_ending'] ?? false,
        ];

        // Process module injections
        if (!empty($scene['module_injections'])) {
            $processed['modules'] = $this->processModules($scene['module_injections']);
        }

        // Process conditional content
        if (!empty($scene['conditional_content'])) {
            $processed['conditional_content'] = $this->processConditionalContent(
                $scene['conditional_content']
            );
        }

        return $processed;
    }

    /**
     * Process scene content (replace variables, etc.)
     */
    protected function processContent(string $content): string
    {
        $state = $this->engine->getState();

        // Replace variables in content {variable_name}
        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($state) {
            $varName = $matches[1];
            return $state['variables'][$varName] ?? $matches[0];
        }, $content);
    }

    /**
     * Filter actions based on conditions
     */
    protected function filterActions(array $actions): array
    {
        $filtered = [];

        foreach ($actions as $action) {
            // Check if action should be shown based on conditions
            if ($this->shouldShowAction($action)) {
                $filtered[] = $action;
            }
        }

        return $filtered;
    }

    /**
     * Check if action should be shown
     */
    protected function shouldShowAction(array $action): bool
    {
        // If no conditions, always show
        if (empty($action['show_if'])) {
            return true;
        }

        // Evaluate show_if conditions
        $flagEvaluator = new FlagEvaluator(new StateManager($this->engine->getState()));
        return $flagEvaluator->evaluate($action['show_if']);
    }

    /**
     * Process module injections
     */
    protected function processModules(array $moduleIds): array
    {
        $modules = [];

        foreach ($moduleIds as $moduleId) {
            // Load module data from game assets
            $module = $this->loadModule($moduleId);
            if ($module) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * Load module data
     */
    protected function loadModule(string $moduleId): ?array
    {
        // This would load from GameAsset model
        // For now, return placeholder
        return [
            'module_id' => $moduleId,
            'type' => 'placeholder',
        ];
    }

    /**
     * Process conditional content
     */
    protected function processConditionalContent(array $conditionalContent): array
    {
        $processed = [];
        $flagEvaluator = new FlagEvaluator(new StateManager($this->engine->getState()));

        foreach ($conditionalContent as $condition) {
            if ($flagEvaluator->evaluate($condition['if'] ?? [])) {
                $processed[] = [
                    'content' => $this->processContent($condition['content'] ?? ''),
                    'type' => $condition['type'] ?? 'text',
                ];
            }
        }

        return $processed;
    }
}
