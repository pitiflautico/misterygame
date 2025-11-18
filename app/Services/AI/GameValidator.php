<?php

namespace App\Services\AI;

class GameValidator
{
    /**
     * Validate game data structure
     */
    public function validate(array $gameData): array
    {
        $issues = [];

        // Check required fields
        if (empty($gameData['title'])) {
            $issues[] = 'Missing title';
        }

        if (empty($gameData['short_description'])) {
            $issues[] = 'Missing short_description';
        }

        if (empty($gameData['scenes']) || !is_array($gameData['scenes'])) {
            $issues[] = 'Missing or invalid scenes array';
        } else {
            // Validate scenes
            $sceneIssues = $this->validateScenes($gameData['scenes']);
            $issues = array_merge($issues, $sceneIssues);
        }

        if (!empty($gameData['roles']) && is_array($gameData['roles'])) {
            // Validate roles
            $roleIssues = $this->validateRoles($gameData['roles']);
            $issues = array_merge($issues, $roleIssues);
        }

        return $issues;
    }

    /**
     * Validate scenes
     */
    protected function validateScenes(array $scenes): array
    {
        $issues = [];
        $sceneIds = [];
        $hasStart = false;

        foreach ($scenes as $index => $scene) {
            $sceneId = $scene['scene_id'] ?? "scene_{$index}";
            $sceneIds[] = $sceneId;

            // Check for duplicate IDs
            if (count(array_keys($sceneIds, $sceneId)) > 1) {
                $issues[] = "Duplicate scene ID: {$sceneId}";
            }

            // Check if there's a start scene
            if (($scene['is_start'] ?? false) === true) {
                $hasStart = true;
            }

            // Validate scene structure
            if (empty($scene['content']) && empty($scene['actions'])) {
                $issues[] = "Scene {$sceneId} has no content or actions";
            }

            // Validate actions
            if (!empty($scene['actions'])) {
                foreach ($scene['actions'] as $action) {
                    if (empty($action['id'])) {
                        $issues[] = "Scene {$sceneId} has action without ID";
                    }
                    if (empty($action['label'])) {
                        $issues[] = "Scene {$sceneId} action {$action['id']} has no label";
                    }
                }
            }
        }

        if (!$hasStart && !empty($scenes)) {
            $issues[] = 'No start scene defined';
        }

        // Validate scene connections
        $this->validateSceneConnections($scenes, $sceneIds, $issues);

        return $issues;
    }

    /**
     * Validate scene connections
     */
    protected function validateSceneConnections(array $scenes, array $sceneIds, array &$issues): void
    {
        foreach ($scenes as $scene) {
            $sceneId = $scene['scene_id'] ?? 'unknown';

            if (!empty($scene['actions'])) {
                foreach ($scene['actions'] as $action) {
                    $nextScene = $action['next_scene'] ?? null;

                    // If next_scene is defined, check if it exists
                    if ($nextScene && !in_array($nextScene, $sceneIds)) {
                        $issues[] = "Scene {$sceneId} references non-existent scene: {$nextScene}";
                    }
                }
            }
        }
    }

    /**
     * Validate roles
     */
    protected function validateRoles(array $roles): array
    {
        $issues = [];
        $roleIds = [];

        foreach ($roles as $index => $role) {
            $roleId = $role['role_id'] ?? "role_{$index}";
            $roleIds[] = $roleId;

            if (count(array_keys($roleIds, $roleId)) > 1) {
                $issues[] = "Duplicate role ID: {$roleId}";
            }

            if (empty($role['name'])) {
                $issues[] = "Role {$roleId} has no name";
            }

            if (empty($role['description'])) {
                $issues[] = "Role {$roleId} has no description";
            }
        }

        return $issues;
    }

    /**
     * Auto-fix issues
     */
    public function autoFix(array $gameData, array $issues): array
    {
        // Add default title if missing
        if (in_array('Missing title', $issues)) {
            $gameData['title'] = 'Untitled Mystery Game';
        }

        // Add default description if missing
        if (in_array('Missing short_description', $issues)) {
            $gameData['short_description'] = 'An intriguing mystery awaits...';
        }

        // Fix scenes if needed
        if (!empty($gameData['scenes'])) {
            $gameData['scenes'] = $this->fixScenes($gameData['scenes']);
        }

        // Fix roles if needed
        if (!empty($gameData['roles'])) {
            $gameData['roles'] = $this->fixRoles($gameData['roles']);
        }

        return $gameData;
    }

    /**
     * Fix scenes
     */
    protected function fixScenes(array $scenes): array
    {
        $hasStart = false;

        foreach ($scenes as &$scene) {
            // Ensure scene_id exists
            if (empty($scene['scene_id'])) {
                $scene['scene_id'] = 'scene_' . uniqid();
            }

            // Ensure at least one start scene
            if (!$hasStart && empty($scene['is_start'])) {
                $scene['is_start'] = true;
                $hasStart = true;
            }

            // Ensure content exists
            if (empty($scene['content'])) {
                $scene['content'] = 'The story continues...';
            }

            // Ensure type exists
            if (empty($scene['type'])) {
                $scene['type'] = 'message';
            }

            // Fix actions
            if (!empty($scene['actions'])) {
                foreach ($scene['actions'] as &$action) {
                    if (empty($action['id'])) {
                        $action['id'] = 'action_' . uniqid();
                    }
                    if (empty($action['label'])) {
                        $action['label'] = 'Continue';
                    }
                }
            }
        }

        return $scenes;
    }

    /**
     * Fix roles
     */
    protected function fixRoles(array $roles): array
    {
        foreach ($roles as &$role) {
            if (empty($role['role_id'])) {
                $role['role_id'] = 'role_' . uniqid();
            }
            if (empty($role['name'])) {
                $role['name'] = 'Unnamed Role';
            }
            if (empty($role['description'])) {
                $role['description'] = 'A mysterious participant';
            }
        }

        return $roles;
    }
}
