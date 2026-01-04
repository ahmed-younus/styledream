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
     * @param string $bodyImageBase64 Base64 encoded body image
     * @param array $garmentImagesBase64 Array of base64 encoded garment images
     * @param array $categories Optional array of categories for each garment (top, bottom, dress, shoes, accessory, auto)
     */
    public function generateMultipleTryOn(string $bodyImageBase64, array $garmentImagesBase64, array $categories = []): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Google AI API key not configured. Please add GOOGLE_AI_API_KEY to your .env file.');
        }

        $model = config('services.google_ai.models.try_on');
        $prompt = $this->getMultipleTryOnPrompt(count($garmentImagesBase64), $categories);

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

    protected function getMultipleTryOnPrompt(int $garmentCount, array $categories = []): string
    {
        if ($garmentCount === 1 && (empty($categories) || ($categories[0] ?? 'auto') === 'auto')) {
            return $this->getTryOnPrompt();
        }

        $categoryInstructions = $this->buildCategoryInstructions($categories, $garmentCount);

        return <<<PROMPT
VIRTUAL CLOTHING TRY-ON TASK - MULTIPLE ITEMS

You are performing a virtual try-on with specific clothing items.

IMAGES PROVIDED:
- Image 1: The person (model)
- Images 2 to {$garmentCount} + 1: Clothing items to apply

CLOTHING CATEGORIES AND INSTRUCTIONS:
{$categoryInstructions}

CRITICAL INSTRUCTIONS:
1. ONLY apply the specified clothing type from each garment image - nothing else!
2. If a garment image shows a full outfit (person wearing multiple clothes), EXTRACT ONLY the specified category
3. PRESERVE ORIGINAL ITEMS: If only applying a "top", keep the person's ORIGINAL pants and shoes from Image 1. If only applying a "bottom", keep the person's ORIGINAL shirt and shoes from Image 1.
4. NEVER change shoes unless specifically instructed with "shoes" category
5. Keep the person's face, skin color, body shape, hair, and pose from Image 1 EXACTLY
6. Layer clothing naturally based on categories
7. Maintain realistic lighting and shadows
8. Keep the same pose and background from Image 1

REMEMBER: Only replace what is specified. Everything else stays from the original Image 1.

Generate ONE high-quality image showing the person wearing the specified clothing items combined into one cohesive outfit.
PROMPT;
    }

    /**
     * Build category-specific instructions for AI prompt
     */
    protected function buildCategoryInstructions(array $categories, int $garmentCount): string
    {
        $instructions = [];

        for ($i = 0; $i < $garmentCount; $i++) {
            $category = $categories[$i] ?? 'auto';
            $imageNum = $i + 2;

            $instructions[] = match ($category) {
                'top' => "- Image {$imageNum}: Extract ONLY the TOP/SHIRT/JACKET from this image. IGNORE and DO NOT APPLY: pants, shorts, skirts, shoes, footwear, or any lower body items. Keep the person's ORIGINAL pants and shoes from Image 1.",
                'bottom' => "- Image {$imageNum}: Extract ONLY the PANTS/TROUSERS/SHORTS/SKIRT from this image. IGNORE and DO NOT APPLY: shirts, jackets, shoes, footwear, or any upper body items. Keep the person's ORIGINAL shirt and shoes from Image 1.",
                'dress' => "- Image {$imageNum}: This is a DRESS/FULL BODY GARMENT - apply the entire garment as one piece. Keep the person's ORIGINAL shoes from Image 1 unless shoes are part of the outfit.",
                'shoes' => "- Image {$imageNum}: Extract ONLY the SHOES/FOOTWEAR from this image. IGNORE all clothing items. Keep the person's ORIGINAL clothing from Image 1.",
                'accessory' => "- Image {$imageNum}: This is an ACCESSORY (bag, hat, scarf, jewelry, etc.) - apply it appropriately. Keep ALL original clothing and shoes from Image 1.",
                default => "- Image {$imageNum}: Auto-detect the SINGLE most prominent clothing item from this image and apply only that item.",
            };
        }

        return implode("\n", $instructions);
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
