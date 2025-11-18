<?php

namespace App\Services\AI;

use App\Models\Game;
use App\Services\Multimedia\MultimediaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GameCreatorService
{
    protected string $aiProvider;
    protected MultimediaService $multimediaService;
    protected GameValidator $validator;
    protected LandingPageGenerator $landingGenerator;

    public function __construct()
    {
        $this->aiProvider = config('services.ai.provider', 'openai');
        $this->multimediaService = new MultimediaService();
        $this->validator = new GameValidator();
        $this->landingGenerator = new LandingPageGenerator();
    }

    /**
     * Generate a complete game from a prompt
     */
    public function generateGame(string $prompt, array $options = []): Game
    {
        // Extract parameters
        $gameType = $options['game_type'] ?? 'mystery';
        $players = $options['players'] ?? ['min' => 1, 'max' => 6];
        $duration = $options['duration'] ?? 60;
        $difficulty = $options['difficulty'] ?? 'medium';

        // Generate game structure with AI
        $gameData = $this->generateGameStructure($prompt, $gameType, $players, $duration, $difficulty);

        // Validate and fix if needed
        $gameData = $this->validateAndFix($gameData);

        // Create game record
        $game = Game::create([
            'title' => $gameData['title'],
            'slug' => Str::slug($gameData['title']),
            'tagline' => $gameData['tagline'] ?? '',
            'short_description' => $gameData['short_description'],
            'long_description' => $gameData['long_description'] ?? '',
            'game_type' => $gameType,
            'min_players' => $players['min'],
            'max_players' => $players['max'],
            'estimated_duration_minutes' => $duration,
            'difficulty' => $difficulty,
            'game_data' => $gameData,
            'roles' => $gameData['roles'] ?? [],
            'features' => $gameData['features'] ?? [],
            'ai_model_used' => $this->aiProvider,
            'ai_generated_at' => now(),
            'status' => 'draft',
        ]);

        // Generate landing page
        $landingPage = $this->landingGenerator->generate($game, $gameData);
        $game->update(['landing_page_data' => $landingPage]);

        // Process multimedia modules
        if (!empty($gameData['modules'])) {
            $this->multimediaService->processGameAssets($game, $gameData['modules']);
        }

        return $game;
    }

    /**
     * Generate game structure using AI
     */
    protected function generateGameStructure(
        string $prompt,
        string $gameType,
        array $players,
        int $duration,
        string $difficulty
    ): array {
        $systemPrompt = $this->getSystemPrompt($gameType);
        $userPrompt = $this->buildUserPrompt($prompt, $gameType, $players, $duration, $difficulty);

        $response = $this->callAI($systemPrompt, $userPrompt);

        return $this->parseAIResponse($response);
    }

    /**
     * Get system prompt for AI
     */
    protected function getSystemPrompt(string $gameType): string
    {
        return <<<PROMPT
You are an expert narrative game designer specializing in creating immersive {$gameType} games.

Your task is to generate COMPLETE, DETAILED game structures in JSON format following this specification:

## Required Structure:

{
  "title": "Game Title",
  "tagline": "Short catchy tagline",
  "short_description": "2-3 sentences describing the game",
  "long_description": "Detailed description (3-5 paragraphs)",
  "features": ["feature1", "feature2", "feature3"],

  "roles": [
    {
      "role_id": "role_1",
      "name": "Role Name",
      "description": "Role description",
      "secret_information": "Secret info only for this role",
      "objectives": ["objective1", "objective2"]
    }
  ],

  "scenes": [
    {
      "scene_id": "scene_1",
      "is_start": true,
      "type": "message|choice|reveal|puzzle",
      "content": "Narrative text with {variable} support",
      "actions": [
        {
          "id": "action_1",
          "label": "Action label",
          "type": "choice|confirm",
          "next_scene": "scene_2",
          "flags": {"flag_name": value},
          "reveals_clue": "clue_id",
          "clue_data": {}
        }
      ],
      "media": {
        "audio_url": null,
        "image_url": null,
        "video_url": null
      },
      "flags_trigger": [],
      "is_ending": false
    }
  ],

  "modules": [
    {
      "module": "audio|image|video|pdf|fake_web|fake_news",
      "id": "module_id",
      "tts_text": "Text for audio",
      "prompt": "Prompt for image/video generation",
      "content": "Content for PDF",
      "type": "news|profile|article for fake_web"
    }
  ],

  "initial_state": {
    "flags": {},
    "variables": {}
  }
}

## CRITICAL REQUIREMENTS:
1. Generate AT LEAST 10-20 scenes for a complete experience
2. Create meaningful branching paths based on player choices
3. Include red herrings and false clues
4. Ensure logical consistency in the narrative
5. Create compelling character motivations
6. Add atmospheric multimedia modules
7. Make endings satisfying and multiple if possible
8. Validate all scene connections (no dead ends unless intentional endings)

## SECURITY RULES:
- NEVER request dangerous real-world actions
- NEVER involve real people or authorities
- ALL content must be clearly fictional
- NO personal data collection

Return ONLY valid JSON, no additional text.
PROMPT;
    }

    /**
     * Build user prompt
     */
    protected function buildUserPrompt(
        string $prompt,
        string $gameType,
        array $players,
        int $duration,
        string $difficulty
    ): string {
        return <<<PROMPT
Generate a complete {$gameType} game with these specifications:

**User Request:** {$prompt}

**Parameters:**
- Game Type: {$gameType}
- Players: {$players['min']} to {$players['max']}
- Duration: {$duration} minutes
- Difficulty: {$difficulty}

Create a rich, immersive narrative with:
- Compelling mystery/story
- Multiple suspects/leads (if applicable)
- Clues and red herrings
- Branching narrative paths
- Atmospheric multimedia elements
- Satisfying conclusion(s)

Generate the complete JSON structure now.
PROMPT;
    }

    /**
     * Call AI API
     */
    protected function callAI(string $systemPrompt, string $userPrompt): string
    {
        return match ($this->aiProvider) {
            'openai' => $this->callOpenAI($systemPrompt, $userPrompt),
            'anthropic' => $this->callClaude($systemPrompt, $userPrompt),
            default => throw new \Exception("Unsupported AI provider: {$this->aiProvider}"),
        };
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.8,
            'max_tokens' => 16000,
            'response_format' => ['type' => 'json_object'],
        ]);

        if (!$response->successful()) {
            throw new \Exception('AI API call failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'];
    }

    /**
     * Call Claude API
     */
    protected function callClaude(string $systemPrompt, string $userPrompt): string
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-3-sonnet-20240229'),
            'max_tokens' => 16000,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API call failed: ' . $response->body());
        }

        $data = $response->json();
        return $data['content'][0]['text'];
    }

    /**
     * Parse AI response
     */
    protected function parseAIResponse(string $response): array
    {
        // Try to extract JSON if wrapped in markdown code blocks
        if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
            $response = $matches[1];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse AI response as JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Validate and fix game data
     */
    protected function validateAndFix(array $gameData): array
    {
        // Validate structure
        $issues = $this->validator->validate($gameData);

        // If issues found, attempt to fix
        if (!empty($issues)) {
            $gameData = $this->validator->autoFix($gameData, $issues);
        }

        return $gameData;
    }

    /**
     * Regenerate specific part of the game
     */
    public function regeneratePart(Game $game, string $part, ?string $specificPrompt = null): array
    {
        $gameData = $game->game_data;

        $systemPrompt = "You are a narrative game designer. Regenerate the '{$part}' section of a game.";
        $userPrompt = $specificPrompt ?? "Regenerate the {$part} section to be more engaging and detailed.";

        if ($part === 'scenes') {
            $userPrompt .= "\n\nCurrent game context:\n" . json_encode([
                'title' => $game->title,
                'type' => $game->game_type,
                'existing_roles' => $gameData['roles'] ?? [],
            ]);
        }

        $response = $this->callAI($systemPrompt, $userPrompt);
        $newPart = $this->parseAIResponse($response);

        // Update game data
        $gameData[$part] = $newPart;
        $game->update(['game_data' => $gameData]);

        return $newPart;
    }
}
