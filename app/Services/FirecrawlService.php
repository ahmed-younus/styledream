<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FirecrawlService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.firecrawl.dev/v1';

    public function __construct()
    {
        $this->apiKey = config('services.firecrawl.api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Scrape a URL and extract product data
     */
    public function scrapeProduct(string $url): array
    {
        if (!$this->isConfigured()) {
            Log::warning('Firecrawl not configured');
            return $this->failedResult();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->baseUrl . '/scrape', [
                'url' => $url,
                'formats' => ['markdown', 'html'],
                'onlyMainContent' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('Firecrawl request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->failedResult();
            }

            $data = $response->json();

            if (!isset($data['success']) || !$data['success']) {
                Log::warning('Firecrawl scrape unsuccessful', ['url' => $url, 'data' => $data]);
                return $this->failedResult();
            }

            return $this->extractProductFromFirecrawl($data, $url);

        } catch (Exception $e) {
            Log::error('Firecrawl exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return $this->failedResult();
        }
    }

    /**
     * Extract product info from Firecrawl response
     */
    protected function extractProductFromFirecrawl(array $data, string $url): array
    {
        $metadata = $data['data']['metadata'] ?? [];
        $html = $data['data']['html'] ?? '';

        // Get image from og:image or other sources
        $image = $metadata['ogImage'] ?? $metadata['image'] ?? '';

        // If no image in metadata, try to find in HTML
        if (empty($image) && !empty($html)) {
            $image = $this->extractMainImageFromHtml($html);
        }

        // Get product name
        $name = $metadata['ogTitle'] ?? $metadata['title'] ?? '';

        // Clean up title (remove site name suffix)
        $name = preg_replace('/\s*[||-]\s*(ZARA|ASOS|H&M|Mango|Amazon).*$/i', '', $name);

        // Detect category from name
        $category = $this->detectCategory($name);

        // Detect source/brand from URL
        $host = parse_url($url, PHP_URL_HOST);
        $source = $this->detectSource($host);

        Log::info('Firecrawl product extracted', [
            'url' => $url,
            'name' => $name,
            'image' => $image,
            'category' => $category,
        ]);

        return [
            'success' => !empty($image),
            'name' => trim($name),
            'image' => $image,
            'brand' => $source,
            'category' => $category,
            'source' => $source,
        ];
    }

    /**
     * Extract main product image from HTML
     */
    protected function extractMainImageFromHtml(string $html): string
    {
        // Try JSON-LD first
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            $jsonData = json_decode($matches[1], true);
            if (isset($jsonData['@type']) && $jsonData['@type'] === 'Product') {
                $img = $jsonData['image'] ?? '';
                if (is_array($img)) {
                    $img = $img[0] ?? '';
                }
                if (!empty($img)) {
                    return $img;
                }
            }
        }

        // Try og:image
        if (preg_match('/<meta[^>]*property="og:image"[^>]*content="([^"]+)"/i', $html, $match)) {
            return $match[1];
        }

        // Try twitter:image
        if (preg_match('/<meta[^>]*name="twitter:image"[^>]*content="([^"]+)"/i', $html, $match)) {
            return $match[1];
        }

        return '';
    }

    /**
     * Detect clothing category from product name
     */
    protected function detectCategory(string $name): string
    {
        $name = strtolower($name);

        if (preg_match('/(shirt|top|blouse|tee|t-shirt|sweater|hoodie|jacket|coat|blazer|cardigan|polo|tank|vest|pullover|sweatshirt|jersey|crop)/i', $name)) {
            return 'top';
        }

        if (preg_match('/(trouser|pant|jean|short|skirt|legging|jogger|chino|cargo|denim|culottes)/i', $name)) {
            return 'bottom';
        }

        if (preg_match('/(dress|gown|jumpsuit|romper|playsuit|bodysuit|overall)/i', $name)) {
            return 'dress';
        }

        if (preg_match('/(shoe|sneaker|boot|sandal|trainer|heel|loafer|slipper|mule|flat|oxford|pump|espadrille)/i', $name)) {
            return 'shoes';
        }

        if (preg_match('/(bag|hat|cap|belt|scarf|watch|jewelry|sunglasses|wallet|earring|necklace|bracelet|ring|glove)/i', $name)) {
            return 'accessory';
        }

        return 'auto';
    }

    /**
     * Detect source/brand from hostname
     */
    protected function detectSource(string $host): string
    {
        return match (true) {
            str_contains($host, 'zara') => 'Zara',
            str_contains($host, 'asos') => 'ASOS',
            str_contains($host, 'hm.com') => 'H&M',
            str_contains($host, 'mango') => 'Mango',
            str_contains($host, 'uniqlo') => 'Uniqlo',
            str_contains($host, 'amazon') => 'Amazon',
            str_contains($host, 'nordstrom') => 'Nordstrom',
            str_contains($host, 'net-a-porter') => 'Net-a-Porter',
            str_contains($host, 'farfetch') => 'Farfetch',
            str_contains($host, 'ssense') => 'SSENSE',
            default => ucfirst(explode('.', $host)[0] ?? 'Unknown'),
        };
    }

    protected function failedResult(): array
    {
        return [
            'success' => false,
            'name' => '',
            'image' => '',
            'brand' => '',
            'category' => 'auto',
            'source' => 'firecrawl',
        ];
    }
}
