<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAIService
{
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.google_ai.api_key');
        $this->baseUrl = config('services.google_ai.base_url') ?? 'https://generativelanguage.googleapis.com/v1beta';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Generate try-on with multiple garments in a single API call
     */
    public function generateMultipleTryOn(string $bodyImageBase64, array $garmentImagesBase64): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Google AI API key not configured. Please add GOOGLE_AI_API_KEY to your .env file.');
        }

        $model = config('services.google_ai.models.try_on');
        $prompt = $this->getMultipleTryOnPrompt(count($garmentImagesBase64));

        // Build parts array with body image and all garment images
        $parts = [
            ['text' => $prompt],
            [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => $this->cleanBase64($bodyImageBase64),
                ],
            ],
        ];

        // Add all garment images
        foreach ($garmentImagesBase64 as $garmentBase64) {
            $parts[] = [
                'inlineData' => [
                    'mimeType' => 'image/jpeg',
                    'data' => $this->cleanBase64($garmentBase64),
                ],
            ];
        }

        $contents = [
            [
                'role' => 'user',
                'parts' => $parts,
            ],
        ];

        try {
            $response = Http::timeout(180) // Longer timeout for multiple items
                ->post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
                    'contents' => $contents,
                    'generationConfig' => [
                        'responseModalities' => ['TEXT', 'IMAGE'],
                    ],
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                $errorMessage = $body['error']['message'] ?? $response->body();
                Log::error('Google AI try-on failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('AI error: ' . $errorMessage);
            }

            $data = $response->json();
            return $this->parseTryOnResponse($data);

        } catch (Exception $e) {
            Log::error('Google AI try-on exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Single garment try-on (legacy method)
     */
    public function generateTryOn(string $bodyImageBase64, string $garmentImageBase64): array
    {
        return $this->generateMultipleTryOn($bodyImageBase64, [$garmentImageBase64]);
    }

    protected function parseTryOnResponse(array $data): array
    {
        $candidate = $data['candidates'][0] ?? null;
        if (!$candidate) {
            throw new Exception('No response from AI');
        }

        $parts = $candidate['content']['parts'] ?? [];
        $result = ['image' => null, 'text' => null];

        foreach ($parts as $part) {
            if (isset($part['inlineData'])) {
                $mimeType = $part['inlineData']['mimeType'];
                $imageData = $part['inlineData']['data'];
                $result['image'] = "data:{$mimeType};base64,{$imageData}";
            }
            if (isset($part['text'])) {
                $result['text'] = $part['text'];
            }
        }

        if (!$result['image']) {
            throw new Exception('No image generated');
        }

        return $result;
    }

    protected function cleanBase64(string $base64): string
    {
        if (str_contains($base64, ',')) {
            return explode(',', $base64)[1];
        }
        return $base64;
    }

    protected function getMultipleTryOnPrompt(int $garmentCount): string
    {
        if ($garmentCount === 1) {
            return $this->getTryOnPrompt();
        }

        return <<<PROMPT
VIRTUAL CLOTHING TRY-ON TASK - MULTIPLE ITEMS

You are performing a virtual try-on with multiple clothing items.

IMAGES PROVIDED:
- Image 1: The person (model)
- Images 2 to {$garmentCount} + 1: Different clothing items to wear

YOUR TASK:
Create a single image showing the person from Image 1 wearing ALL the clothing items from the other images combined into one complete outfit.

REQUIREMENTS:
1. Keep the person's face, skin color, body shape, and hair EXACTLY as they appear in Image 1
2. Combine ALL clothing items into one cohesive outfit on the person
3. If there are multiple tops, use the most prominent one or layer them naturally
4. If there are pants/bottoms and tops, dress the person in the complete outfit
5. Make all clothing fit naturally on the person's body
6. Maintain realistic lighting and shadows
7. Keep the same pose and background from Image 1

Generate ONE high-quality image showing the person wearing the complete outfit.
PROMPT;
    }

    protected function getTryOnPrompt(): string
    {
        return <<<'PROMPT'
VIRTUAL CLOTHING TRY-ON TASK

You are performing a virtual try-on. Your task is to show the person from Image 1 wearing the clothing from Image 2.

REQUIREMENTS:
1. Keep the person's face, skin color, body shape, and hair EXACTLY as they appear in Image 1
2. Only change their clothing to match Image 2
3. Make the clothing fit naturally on the person's body
4. Maintain realistic lighting and shadows
5. Keep the same pose and background from Image 1

Generate a high-quality image showing the result.
PROMPT;
    }
}
