<?php

namespace App\Services\AI;

use App\Models\Game;

class LandingPageGenerator
{
    /**
     * Generate landing page data for a game
     */
    public function generate(Game $game, array $gameData): array
    {
        return [
            'title' => $gameData['title'] ?? $game->title,
            'tagline' => $gameData['tagline'] ?? $this->generateTagline($game),
            'short_pitch' => $gameData['short_description'] ?? '',
            'long_description' => $gameData['long_description'] ?? '',

            'features' => $this->extractFeatures($game, $gameData),
            'modules_preview' => $this->extractModulesPreview($gameData),

            'game_info' => [
                'type' => $this->getGameTypeLabel($game->game_type),
                'players' => "{$game->min_players}-{$game->max_players}",
                'duration' => "{$game->estimated_duration_minutes} minutos",
                'difficulty' => $this->getDifficultyLabel($game->difficulty),
            ],

            'why_unique' => $this->generateWhyUnique($game, $gameData),

            'roles_preview' => $this->extractRolesPreview($gameData),

            'image_prompt' => $this->generateImagePrompt($game, $gameData),
            'audio_preview_text' => $this->generateAudioPreviewText($game, $gameData),

            'cta' => [
                'buy' => $this->generateBuyCTA($game),
                'invite' => 'Invita a tus amigos y comienza la aventura',
            ],

            'screenshots' => [],
            'testimonials' => [],
        ];
    }

    /**
     * Generate tagline
     */
    protected function generateTagline(Game $game): string
    {
        $taglines = [
            'murder' => 'Descubre quién es el asesino antes de que sea demasiado tarde',
            'mystery' => 'Resuelve el enigma oculto en las sombras',
            'liminal' => 'Explora espacios entre la realidad y lo desconocido',
            'investigation' => 'La verdad está ahí fuera, esperando ser descubierta',
            'horror' => 'Enfrenta tus miedos en esta experiencia escalofriante',
            'escape' => 'Encuentra la salida antes de que el tiempo se agote',
            'interactive_movie' => 'Vive una historia donde tú tomas las decisiones',
            'guided_adventure' => 'Embárcate en una aventura inolvidable',
        ];

        return $taglines[$game->game_type] ?? 'Una experiencia narrativa única';
    }

    /**
     * Extract features from game data
     */
    protected function extractFeatures(Game $game, array $gameData): array
    {
        $features = $gameData['features'] ?? [];

        // Add automatic features based on game structure
        $autoFeatures = [];

        if (!empty($gameData['roles']) && count($gameData['roles']) > 1) {
            $autoFeatures[] = 'Múltiples roles únicos con información secreta';
        }

        if (!empty($gameData['modules'])) {
            $moduleTypes = array_unique(array_column($gameData['modules'], 'module'));
            if (in_array('audio', $moduleTypes)) {
                $autoFeatures[] = 'Audio narrativo inmersivo';
            }
            if (in_array('image', $moduleTypes)) {
                $autoFeatures[] = 'Pistas visuales generadas por IA';
            }
            if (in_array('fake_web', $moduleTypes)) {
                $autoFeatures[] = 'Sitios web ficticios para investigar';
            }
        }

        if (!empty($gameData['scenes']) && count($gameData['scenes']) > 15) {
            $autoFeatures[] = 'Narrativa ramificada con múltiples caminos';
        }

        return array_merge($features, $autoFeatures);
    }

    /**
     * Extract modules preview
     */
    protected function extractModulesPreview(array $gameData): array
    {
        $modules = $gameData['modules'] ?? [];
        $preview = [];

        $moduleCounts = [
            'audio' => 0,
            'image' => 0,
            'video' => 0,
            'pdf' => 0,
            'fake_web' => 0,
        ];

        foreach ($modules as $module) {
            $type = $module['module'] ?? $module['type'] ?? 'unknown';
            if (isset($moduleCounts[$type])) {
                $moduleCounts[$type]++;
            }
        }

        foreach ($moduleCounts as $type => $count) {
            if ($count > 0) {
                $preview[] = [
                    'type' => $type,
                    'count' => $count,
                    'label' => $this->getModuleLabel($type, $count),
                ];
            }
        }

        return $preview;
    }

    /**
     * Get module label
     */
    protected function getModuleLabel(string $type, int $count): string
    {
        $labels = [
            'audio' => $count === 1 ? '1 audio narrativo' : "{$count} audios narrativos",
            'image' => $count === 1 ? '1 pista visual' : "{$count} pistas visuales",
            'video' => $count === 1 ? '1 video' : "{$count} videos",
            'pdf' => $count === 1 ? '1 documento' : "{$count} documentos",
            'fake_web' => $count === 1 ? '1 sitio web ficticio' : "{$count} sitios web ficticios",
        ];

        return $labels[$type] ?? "{$count} elementos";
    }

    /**
     * Generate "why unique" section
     */
    protected function generateWhyUnique(Game $game, array $gameData): array
    {
        $unique = [];

        // Analyze game structure
        $sceneCount = count($gameData['scenes'] ?? []);
        $roleCount = count($gameData['roles'] ?? []);
        $moduleCount = count($gameData['modules'] ?? []);

        if ($sceneCount > 20) {
            $unique[] = "Una narrativa extensa con más de {$sceneCount} escenas únicas";
        }

        if ($roleCount > 1) {
            $unique[] = "Experimenta la historia desde {$roleCount} perspectivas diferentes";
        }

        if ($moduleCount > 5) {
            $unique[] = "Rica en contenido multimedia con {$moduleCount} elementos inmersivos";
        }

        // Add type-specific unique points
        $typeUnique = [
            'murder' => 'Sistema de pistas y sospechosos cuidadosamente diseñado',
            'mystery' => 'Múltiples giros argumentales y revelaciones impactantes',
            'liminal' => 'Atmósfera única que desafía la percepción',
            'horror' => 'Tensión psicológica creciente',
        ];

        if (isset($typeUnique[$game->game_type])) {
            $unique[] = $typeUnique[$game->game_type];
        }

        return $unique;
    }

    /**
     * Extract roles preview (without spoilers)
     */
    protected function extractRolesPreview(array $gameData): array
    {
        $roles = $gameData['roles'] ?? [];
        $preview = [];

        foreach ($roles as $role) {
            $preview[] = [
                'name' => $role['name'] ?? 'Unnamed',
                'description' => $role['description'] ?? '',
                // Exclude secret information
            ];
        }

        return $preview;
    }

    /**
     * Generate image prompt for landing page
     */
    protected function generateImagePrompt(Game $game, array $gameData): string
    {
        $title = $gameData['title'] ?? $game->title;

        $prompts = [
            'murder' => "Dark detective noir atmosphere, crime scene investigation, mysterious shadows, cinematic lighting, for '{$title}'",
            'mystery' => "Mysterious atmosphere, hidden clues, foggy environment, suspenseful mood, for '{$title}'",
            'liminal' => "Liminal space, eerie empty corridors, dreamlike atmosphere, unsettling ambiance, for '{$title}'",
            'horror' => "Horror game atmosphere, dark shadows, suspenseful lighting, creepy environment, for '{$title}'",
        ];

        return $prompts[$game->game_type] ?? "Atmospheric game scene for '{$title}', mysterious and engaging";
    }

    /**
     * Generate audio preview text
     */
    protected function generateAudioPreviewText(Game $game, array $gameData): string
    {
        $firstScene = $gameData['scenes'][0] ?? null;

        if ($firstScene && !empty($firstScene['content'])) {
            // Take first 200 characters
            return substr($firstScene['content'], 0, 200);
        }

        return $gameData['short_description'] ?? '';
    }

    /**
     * Generate buy CTA
     */
    protected function generateBuyCTA(Game $game): string
    {
        if ($game->isFree()) {
            return '¡Jugar Gratis Ahora!';
        }

        return "Comprar por \${$game->price}";
    }

    /**
     * Get game type label
     */
    protected function getGameTypeLabel(string $type): string
    {
        $labels = [
            'murder' => 'Misterio de Asesinato',
            'mystery' => 'Misterio',
            'liminal' => 'Experiencia Liminal',
            'investigation' => 'Investigación',
            'horror' => 'Terror Psicológico',
            'escape' => 'Escape Room Narrativo',
            'interactive_movie' => 'Película Interactiva',
            'guided_adventure' => 'Aventura Guiada',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Get difficulty label
     */
    protected function getDifficultyLabel(string $difficulty): string
    {
        $labels = [
            'easy' => 'Fácil',
            'medium' => 'Medio',
            'hard' => 'Difícil',
            'expert' => 'Experto',
        ];

        return $labels[$difficulty] ?? ucfirst($difficulty);
    }
}
