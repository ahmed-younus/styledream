<?php

namespace App\Jobs;

use App\Models\TryOn;
use App\Services\GoogleAIService;
use App\Services\CreditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessTryOn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public TryOn $tryOn
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GoogleAIService $aiService, CreditService $creditService): void
    {
        // Increment attempts
        $this->tryOn->incrementAttempts();

        // Mark as processing
        $this->tryOn->markAsProcessing();

        Log::info('ProcessTryOn started', [
            'try_on_id' => $this->tryOn->id,
            'user_id' => $this->tryOn->user_id,
            'attempt' => $this->tryOn->attempts,
        ]);

        try {
            // Load garment images as base64
            $garmentBase64Array = $this->loadGarmentImages();

            if (empty($garmentBase64Array)) {
                throw new \Exception('No garment images found');
            }

            // Load body image as base64
            $bodyBase64 = $this->loadImageAsBase64($this->tryOn->body_image_url);

            if (!$bodyBase64) {
                throw new \Exception('Body image not found');
            }

            $startTime = microtime(true);

            // Call AI service
            $result = $aiService->generateMultipleTryOn($bodyBase64, $garmentBase64Array);

            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            // Store result image
            $resultUrl = $this->storeResultImage($result['image']);

            // Mark as completed
            $this->tryOn->markAsCompleted($resultUrl, $processingTime);

            Log::info('ProcessTryOn completed', [
                'try_on_id' => $this->tryOn->id,
                'processing_time_ms' => $processingTime,
            ]);

        } catch (\Exception $e) {
            Log::error('ProcessTryOn failed', [
                'try_on_id' => $this->tryOn->id,
                'error' => $e->getMessage(),
                'attempt' => $this->tryOn->attempts,
            ]);

            // Check if we should retry
            if ($this->tryOn->canRetry($this->tries)) {
                // Let Laravel handle the retry
                throw $e;
            }

            // Max attempts reached - mark as permanently failed
            $this->markAsPermanentlyFailed($e, $creditService);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessTryOn job permanently failed', [
            'try_on_id' => $this->tryOn->id,
            'error' => $exception->getMessage(),
        ]);

        // If max retries exceeded, mark as failed and refund
        $creditService = app(CreditService::class);
        $this->markAsPermanentlyFailed($exception, $creditService);
    }

    /**
     * Mark the try-on as permanently failed and refund credits.
     */
    protected function markAsPermanentlyFailed(\Throwable $exception, CreditService $creditService): void
    {
        $this->tryOn->markAsFailed($exception->getMessage());

        // Refund credits
        $user = $this->tryOn->user;
        if ($user && $this->tryOn->credits_used > 0) {
            $creditService->refundCredits(
                $user,
                $this->tryOn->credits_used,
                'Try-on failed - automatic refund',
                (string) $this->tryOn->id
            );
        }
    }

    /**
     * Load all garment images as base64.
     */
    protected function loadGarmentImages(): array
    {
        $garmentBase64Array = [];
        $urls = $this->tryOn->getAllGarmentUrls();

        foreach ($urls as $url) {
            $base64 = $this->loadImageAsBase64($url);
            if ($base64) {
                $garmentBase64Array[] = $base64;
            }
        }

        return $garmentBase64Array;
    }

    /**
     * Load an image from storage URL as base64.
     */
    protected function loadImageAsBase64(string $url): ?string
    {
        // Handle /storage/ URLs
        $path = str_replace('/storage/', '', $url);

        if (Storage::disk('public')->exists($path)) {
            return base64_encode(Storage::disk('public')->get($path));
        }

        // Handle full URLs (external images)
        if (str_starts_with($url, 'http')) {
            try {
                $content = file_get_contents($url);
                if ($content !== false) {
                    return base64_encode($content);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to load external image', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Store the result image.
     */
    protected function storeResultImage(string $imageData): string
    {
        $base64 = $imageData;
        if (str_contains($imageData, ',')) {
            $base64 = explode(',', $imageData)[1];
        }

        $decoded = base64_decode($base64);
        $filename = "try-on/result/" . uniqid() . '_' . time() . '.jpg';

        Storage::disk('public')->put($filename, $decoded);

        return '/storage/' . $filename;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'try-on',
            'user:' . $this->tryOn->user_id,
            'try-on:' . $this->tryOn->id,
        ];
    }
}
