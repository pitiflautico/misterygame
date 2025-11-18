<?php

namespace App\Services\Multimedia;

use Illuminate\Support\Facades\Http;

class ImageGenerator
{
    /**
     * Generate image using AI
     */
    public function generate(string $prompt, string $purpose = 'general'): array
    {
        $provider = config('services.image.provider', 'openai');

        // Enhance prompt based on purpose
        $enhancedPrompt = $this->enhancePrompt($prompt, $purpose);

        return match ($provider) {
            'openai' => $this->generateWithOpenAI($enhancedPrompt),
            default => throw new \Exception("Unsupported image provider: {$provider}"),
        };
    }

    /**
     * Generate image with OpenAI DALL-E
     */
    protected function generateWithOpenAI(string $prompt): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/images/generations', [
            'model' => config('services.image.model', 'dall-e-3'),
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'b64_json',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to generate image: ' . $response->body());
        }

        $data = $response->json();
        $imageData = $data['data'][0]['b64_json'] ?? null;

        if (!$imageData) {
            throw new \Exception('No image data in response');
        }

        return [
            'content' => base64_decode($imageData),
            'format' => 'png',
        ];
    }

    /**
     * Enhance prompt based on purpose
     */
    protected function enhancePrompt(string $prompt, string $purpose): string
    {
        $enhancements = [
            'pista' => 'highly detailed, mysterious, photorealistic',
            'atmosphere' => 'atmospheric, cinematic lighting, moody',
            'news' => 'journalistic style, professional news photography',
            'clue' => 'detective game clue, mysterious object, detailed',
            'general' => 'high quality, detailed',
        ];

        $enhancement = $enhancements[$purpose] ?? $enhancements['general'];

        return "{$prompt}, {$enhancement}";
    }
}
