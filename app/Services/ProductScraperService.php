<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductScraperService
{
    protected ?FirecrawlService $firecrawl = null;

    public function __construct()
    {
        $this->firecrawl = app(FirecrawlService::class);
    }

    /**
     * Scrape product details from a URL
     * First tries basic scraping, then falls back to Firecrawl if that fails
     */
    public function scrapeProduct(string $url): array
    {
        // Try basic scraping first
        $result = $this->scrapeBasic($url);

        // If basic scraping succeeded, return it
        if ($result['success']) {
            return $result;
        }

        // Fallback to Firecrawl for JavaScript-heavy sites
        Log::info('Basic scraping failed, trying Firecrawl', ['url' => $url]);

        if ($this->firecrawl && $this->firecrawl->isConfigured()) {
            $firecrawlResult = $this->firecrawl->scrapeProduct($url);
            if ($firecrawlResult['success']) {
                return $firecrawlResult;
            }
        }

        // Return the original failed result
        return $result;
    }

    /**
     * Basic HTTP scraping (works for simple sites)
     */
    protected function scrapeBasic(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);

        return match (true) {
            str_contains($host, 'asos.com') => $this->scrapeAsos($url),
            str_contains($host, 'zara.com') => $this->scrapeZara($url),
            str_contains($host, 'hm.com') || str_contains($host, 'h&m') => $this->scrapeHM($url),
            str_contains($host, 'amazon') => $this->scrapeAmazon($url),
            str_contains($host, 'uniqlo') => $this->scrapeUniqlo($url),
            str_contains($host, 'mango.com') => $this->scrapeMango($url),
            default => $this->scrapeGeneric($url),
        };
    }

    /**
     * Check if URL is a product page (not direct image)
     */
    public function isProductUrl(string $url): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return !in_array($extension, $imageExtensions);
    }

    /**
     * ASOS Scraper
     */
    protected function scrapeAsos(string $url): array
    {
        $html = $this->fetchPage($url);

        if (empty($html)) {
            return $this->failedResult('asos');
        }

        // ASOS uses JSON-LD for product data
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            $data = json_decode($matches[1], true);
            if (isset($data['@type']) && $data['@type'] === 'Product') {
                $image = $data['image'] ?? '';
                // Handle array of images
                if (is_array($image)) {
                    $image = $image[0] ?? '';
                }

                return [
                    'success' => !empty($image),
                    'name' => $data['name'] ?? '',
                    'image' => $image,
                    'brand' => $data['brand']['name'] ?? '',
                    'category' => $this->detectCategory($data['name'] ?? ''),
                    'source' => 'asos',
                ];
            }
        }

        // Fallback: Parse HTML
        return $this->parseAsosHtml($html);
    }

    protected function parseAsosHtml(string $html): array
    {
        // Get main product image from og:image
        preg_match('/<meta[^>]*property="og:image"[^>]*content="([^"]+)"/', $html, $imgMatch);

        // Get product name
        preg_match('/<meta[^>]*property="og:title"[^>]*content="([^"]+)"/', $html, $nameMatch);

        $image = $imgMatch[1] ?? '';
        $name = html_entity_decode($nameMatch[1] ?? '');

        // ASOS image optimization - get higher resolution
        if ($image) {
            $image = preg_replace('/\$[^$]+\$/', '$n_640w$', $image);
        }

        return [
            'success' => !empty($image),
            'name' => trim($name),
            'image' => $image,
            'brand' => '',
            'category' => $this->detectCategory($name),
            'source' => 'asos',
        ];
    }

    /**
     * Zara Scraper
     */
    protected function scrapeZara(string $url): array
    {
        $html = $this->fetchPage($url);

        if (empty($html)) {
            return $this->failedResult('zara');
        }

        // Try JSON-LD first
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            $data = json_decode($matches[1], true);
            if (isset($data['@type']) && $data['@type'] === 'Product') {
                $image = $data['image'] ?? '';
                if (is_array($image)) {
                    $image = $image[0] ?? '';
                }

                return [
                    'success' => !empty($image),
                    'name' => $data['name'] ?? '',
                    'image' => $image,
                    'brand' => 'Zara',
                    'category' => $this->detectCategory($data['name'] ?? ''),
                    'source' => 'zara',
                ];
            }
        }

        return $this->scrapeGeneric($url, 'zara');
    }

    /**
     * H&M Scraper
     */
    protected function scrapeHM(string $url): array
    {
        $html = $this->fetchPage($url);

        if (empty($html)) {
            return $this->failedResult('hm');
        }

        // H&M also uses JSON-LD
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            $data = json_decode($matches[1], true);
            if (isset($data['@type']) && $data['@type'] === 'Product') {
                $image = $data['image'] ?? '';
                if (is_array($image)) {
                    $image = $image[0] ?? '';
                }

                return [
                    'success' => !empty($image),
                    'name' => $data['name'] ?? '',
                    'image' => $image,
                    'brand' => 'H&M',
                    'category' => $this->detectCategory($data['name'] ?? ''),
                    'source' => 'hm',
                ];
            }
        }

        return $this->scrapeGeneric($url, 'hm');
    }

    /**
     * Amazon Scraper
     */
    protected function scrapeAmazon(string $url): array
    {
        $html = $this->fetchPage($url);

        if (empty($html)) {
            return $this->failedResult('amazon');
        }

        // Amazon main product image
        preg_match('/data-old-hires="([^"]+)"/', $html, $imgMatch);

        if (empty($imgMatch[1])) {
            preg_match('/<img[^>]*id="landingImage"[^>]*src="([^"]+)"/', $html, $imgMatch);
        }

        // Product title
        preg_match('/<span[^>]*id="productTitle"[^>]*>([^<]+)</', $html, $nameMatch);

        $image = $imgMatch[1] ?? '';
        $name = trim($nameMatch[1] ?? '');

        if (empty($image)) {
            return $this->scrapeGeneric($url, 'amazon');
        }

        return [
            'success' => !empty($image),
            'name' => $name,
            'image' => $image,
            'brand' => '',
            'category' => $this->detectCategory($name),
            'source' => 'amazon',
        ];
    }

    /**
     * Uniqlo Scraper
     */
    protected function scrapeUniqlo(string $url): array
    {
        return $this->scrapeGeneric($url, 'uniqlo');
    }

    /**
     * Mango Scraper
     */
    protected function scrapeMango(string $url): array
    {
        return $this->scrapeGeneric($url, 'mango');
    }

    /**
     * Generic scraper using Open Graph / meta tags
     */
    protected function scrapeGeneric(string $url, string $source = 'generic'): array
    {
        $html = $this->fetchPage($url);

        if (empty($html)) {
            return $this->failedResult($source);
        }

        // Try JSON-LD first (most reliable)
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches)) {
            $data = json_decode($matches[1], true);
            if (isset($data['@type']) && $data['@type'] === 'Product') {
                $image = $data['image'] ?? '';
                if (is_array($image)) {
                    $image = $image[0] ?? '';
                }

                if (!empty($image)) {
                    return [
                        'success' => true,
                        'name' => $data['name'] ?? '',
                        'image' => $image,
                        'brand' => $data['brand']['name'] ?? ($data['brand'] ?? ''),
                        'category' => $this->detectCategory($data['name'] ?? ''),
                        'source' => $source,
                    ];
                }
            }
        }

        // Try Open Graph image
        preg_match('/<meta[^>]*property="og:image"[^>]*content="([^"]+)"/i', $html, $ogImage);
        if (empty($ogImage[1])) {
            preg_match('/<meta[^>]*content="([^"]+)"[^>]*property="og:image"/i', $html, $ogImage);
        }

        preg_match('/<meta[^>]*property="og:title"[^>]*content="([^"]+)"/i', $html, $ogTitle);
        if (empty($ogTitle[1])) {
            preg_match('/<meta[^>]*content="([^"]+)"[^>]*property="og:title"/i', $html, $ogTitle);
        }

        // Fallback to Twitter card
        if (empty($ogImage[1])) {
            preg_match('/<meta[^>]*name="twitter:image"[^>]*content="([^"]+)"/i', $html, $ogImage);
        }

        // Fallback to any product image
        if (empty($ogImage[1])) {
            preg_match_all('/<img[^>]*src="([^"]+)"[^>]*>/i', $html, $allImages);
            foreach ($allImages[1] ?? [] as $img) {
                if (!str_contains(strtolower($img), 'logo') &&
                    !str_contains(strtolower($img), 'icon') &&
                    !str_contains(strtolower($img), 'sprite') &&
                    (str_contains($img, 'product') || str_contains($img, 'image'))) {
                    $ogImage[1] = $img;
                    break;
                }
            }
        }

        $image = $ogImage[1] ?? '';
        $name = html_entity_decode($ogTitle[1] ?? '');

        // Make relative URLs absolute
        if ($image && !str_starts_with($image, 'http')) {
            $parsedUrl = parse_url($url);
            $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
            $image = $baseUrl . (str_starts_with($image, '/') ? '' : '/') . $image;
        }

        return [
            'success' => !empty($image),
            'name' => trim($name),
            'image' => $image,
            'brand' => '',
            'category' => $this->detectCategory($name),
            'source' => $source,
        ];
    }

    /**
     * Detect clothing category from product name
     */
    public function detectCategory(string $name): string
    {
        $name = strtolower($name);

        // Tops
        if (preg_match('/(shirt|top|blouse|tee|t-shirt|sweater|hoodie|jacket|coat|blazer|cardigan|polo|tank|vest|pullover|sweatshirt|jersey|crop)/i', $name)) {
            return 'top';
        }

        // Bottoms
        if (preg_match('/(trouser|pant|jean|short|skirt|legging|jogger|chino|cargo|denim|culottes)/i', $name)) {
            return 'bottom';
        }

        // Dress
        if (preg_match('/(dress|gown|jumpsuit|romper|playsuit|bodysuit|overall)/i', $name)) {
            return 'dress';
        }

        // Shoes
        if (preg_match('/(shoe|sneaker|boot|sandal|trainer|heel|loafer|slipper|mule|flat|oxford|pump|espadrille)/i', $name)) {
            return 'shoes';
        }

        // Accessories
        if (preg_match('/(bag|hat|cap|belt|scarf|watch|jewelry|sunglasses|wallet|earring|necklace|bracelet|ring|glove)/i', $name)) {
            return 'accessory';
        }

        return 'auto';
    }

    protected function fetchPage(string $url): string
    {
        try {
            // Try with different user agents if needed
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Cache-Control' => 'max-age=0',
            ])
                ->timeout(20)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $body = $response->body();
                Log::info('ProductScraper fetched page', [
                    'url' => $url,
                    'body_length' => strlen($body),
                    'has_og_image' => str_contains($body, 'og:image'),
                ]);
                return $body;
            }

            Log::warning('ProductScraper failed to fetch page', [
                'url' => $url,
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 500),
            ]);
        } catch (Exception $e) {
            Log::warning('ProductScraper exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return '';
    }

    protected function failedResult(string $source): array
    {
        return [
            'success' => false,
            'name' => '',
            'image' => '',
            'brand' => '',
            'category' => 'auto',
            'source' => $source,
        ];
    }
}
