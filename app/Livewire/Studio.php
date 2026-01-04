<?php

namespace App\Livewire;

use App\Models\TryOn;
use App\Models\WardrobeItem;
use App\Models\SavedOutfit;
use App\Models\OutfitPost;
use App\Models\ShareEvent;
use App\Models\Avatar;
use App\Jobs\ProcessTryOn;
use App\Services\CreditService;
use App\Services\PricingService;
use App\Services\ProductScraperService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Studio extends Component
{
    use WithFileUploads;

    public $bodyImage;
    public $garmentImages = [];
    public $uploadedGarments = [];
    public $bodyImagePreview;
    public $garmentPreviews = [];
    public $selectedWardrobeItems = [];
    public $resultImage;
    public $lastTryOnId = null;
    public $isProcessing = false;
    public $error = '';

    // Save outfit modal
    public $showSaveModal = false;
    public $saveOutfitName = '';
    public $saveOutfitNotes = '';

    // Post to feed modal
    public $showPostModal = false;
    public $postCaption = '';
    public $postVisibility = 'public';

    // Share modal
    public $showShareModal = false;

    // For history items
    public $historyTryOnId = null;
    public $historyImageUrl = null;

    // Wardrobe modal
    public $showWardrobeModal = false;

    // URL paste feature
    public $bodyImageUrl = '';
    public $garmentImageUrl = '';
    public $bodyImageBase64 = null;
    public $garmentBase64Array = [];

    // Category selector for garments
    public array $garmentCategories = [];

    // Product scraper preview
    public ?array $scrapedProduct = null;

    // Saved body images for quick reuse
    public $savedBodyImages = [];

    // User avatars
    public $avatars = [];

    // Queue status tracking
    public $currentJobId = null;
    public $currentJobStatus = null;
    public $showQueueStatus = false;
    public $showQueueModal = false;
    public $pendingJobs = [];
    public $completedJobsCount = 0;
    public $showResultReady = false;

    // Credit purchase modal
    public $showCreditModal = false;

    // User-managed queue
    public $queueItems = [];
    public $showQueuePanel = false;
    public $editingQueueItem = null;

    // Processing results (shown in Step 3)
    public $processingResults = [];

    // Lightbox modal
    public $showLightbox = false;
    public $lightboxImage = null;
    public $lightboxTryOnId = null;

    public function mount()
    {
        if (Auth::check()) {
            // Load user's avatars (max 3)
            $this->avatars = Auth::user()
                ->avatars()
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get()
                ->toArray();

            // Load last 5 unique body images from user's try-ons
            $this->savedBodyImages = Auth::user()
                ->tryOns()
                ->whereNotNull('body_image_url')
                ->where('body_image_url', '!=', '')
                ->latest()
                ->limit(10)
                ->pluck('body_image_url')
                ->unique()
                ->take(5)
                ->values()
                ->toArray();

            // Load pending/processing jobs
            $this->loadPendingJobs();

            // Load user's queue items
            $this->loadQueueItems();

            // Load processing results for display
            $this->loadProcessingResults();
        }
    }

    /**
     * Load user's pending and processing jobs.
     * Also checks for newly completed jobs and auto-loads results.
     */
    protected function loadPendingJobs(): void
    {
        // Track previous pending job IDs to detect completions
        $previousPendingIds = collect($this->pendingJobs)->pluck('id')->toArray();

        // Load current pending/processing jobs
        $this->pendingJobs = Auth::user()
            ->tryOns()
            ->whereIn('status', [TryOn::STATUS_PENDING, TryOn::STATUS_PROCESSING])
            ->latest()
            ->get()
            ->map(fn($job) => [
                'id' => $job->id,
                'status' => $job->status,
                'created_at' => $job->created_at->diffForHumans(),
                'garment_count' => count($job->getAllGarmentUrls()),
            ])
            ->toArray();

        // Check if any previously pending job is now completed
        if (!empty($previousPendingIds)) {
            $newlyCompleted = Auth::user()
                ->tryOns()
                ->whereIn('id', $previousPendingIds)
                ->where('status', TryOn::STATUS_COMPLETED)
                ->whereNotNull('result_image_url')
                ->latest()
                ->first();

            if ($newlyCompleted) {
                // Auto-load the result
                $this->resultImage = $newlyCompleted->result_image_url;
                $this->lastTryOnId = $newlyCompleted->id;
                $this->showResultReady = true;

                // Dispatch browser event for notification
                $this->dispatch('result-ready');
            }
        }

        // Count recently completed jobs (last 24 hours)
        $this->completedJobsCount = Auth::user()
            ->tryOns()
            ->completed()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Show queue status if there are pending jobs
        if (count($this->pendingJobs) > 0) {
            $this->showQueueStatus = true;
        }
    }

    /**
     * Get queue stats for the modal.
     */
    public function getQueueStatsProperty(): array
    {
        $pendingCount = collect($this->pendingJobs)->where('status', TryOn::STATUS_PENDING)->count();
        $processingCount = collect($this->pendingJobs)->where('status', TryOn::STATUS_PROCESSING)->count();

        return [
            'queued' => $pendingCount,
            'generating' => $processingCount,
            'ready' => $this->completedJobsCount,
        ];
    }

    public function useAvatar(int $avatarId)
    {
        $avatar = Avatar::where('id', $avatarId)
            ->where('user_id', auth()->id())
            ->first();

        if ($avatar) {
            $this->bodyImagePreview = $avatar->image_url;

            // Load base64 for generation
            $path = str_replace('/storage/', '', $avatar->image_url);
            if (Storage::disk('public')->exists($path)) {
                $this->bodyImageBase64 = base64_encode(
                    Storage::disk('public')->get($path)
                );
            }

            $this->error = '';
        }
    }

    public function useBodyImage(string $imageUrl)
    {
        $this->bodyImagePreview = $imageUrl;

        // Load base64 from stored file for generation
        $path = str_replace('/storage/', '', $imageUrl);
        if (Storage::disk('public')->exists($path)) {
            $this->bodyImageBase64 = base64_encode(Storage::disk('public')->get($path));
        }

        $this->error = '';
    }

    public function updatedBodyImage()
    {
        $this->validate(['bodyImage' => 'image|max:10240']);
        $this->bodyImagePreview = $this->bodyImage->temporaryUrl();
        $this->bodyImageBase64 = null; // Clear URL paste if file uploaded
    }

    public function updatedGarmentImages()
    {
        $this->validate(['garmentImages.*' => 'image|max:10240']);

        // Append new images - store file paths for later processing
        foreach ($this->garmentImages as $image) {
            // Use temporaryUrl for fast preview (don't convert to base64 yet)
            $this->garmentPreviews[] = $image->temporaryUrl();
            // Store the actual file for later base64 conversion during generate
            $this->uploadedGarments[] = $image;
            // Add default category (auto-detect)
            $this->garmentCategories[] = 'auto';
        }

        // Clear garmentImages array (files are now in uploadedGarments)
        $this->garmentImages = [];
    }

    public function removeGarment($index)
    {
        // Remove from preview array
        if (isset($this->garmentPreviews[$index])) {
            unset($this->garmentPreviews[$index]);
            $this->garmentPreviews = array_values($this->garmentPreviews);
        }

        // Remove from uploaded garments (new flow)
        if (isset($this->uploadedGarments[$index])) {
            unset($this->uploadedGarments[$index]);
            $this->uploadedGarments = array_values($this->uploadedGarments);
        }

        // Remove from base64 array (URL paste flow)
        if (isset($this->garmentBase64Array[$index])) {
            unset($this->garmentBase64Array[$index]);
            $this->garmentBase64Array = array_values($this->garmentBase64Array);
        }

        // Remove category
        if (isset($this->garmentCategories[$index])) {
            unset($this->garmentCategories[$index]);
            $this->garmentCategories = array_values($this->garmentCategories);
        }
    }

    public function toggleWardrobeItem($itemId)
    {
        if (in_array($itemId, $this->selectedWardrobeItems)) {
            $this->selectedWardrobeItems = array_diff($this->selectedWardrobeItems, [$itemId]);
        } else {
            $this->selectedWardrobeItems[] = $itemId;
        }
        $this->selectedWardrobeItems = array_values($this->selectedWardrobeItems);
    }

    public function addFromWardrobe()
    {
        $this->showWardrobeModal = false;
    }

    public function removeWardrobeItem($itemId)
    {
        $this->selectedWardrobeItems = array_diff($this->selectedWardrobeItems, [$itemId]);
        $this->selectedWardrobeItems = array_values($this->selectedWardrobeItems);
    }

    // ============ URL PASTE METHODS ============

    public function addBodyFromUrl()
    {
        $this->validate(['bodyImageUrl' => 'required|url']);
        $this->error = '';

        try {
            $imageData = $this->fetchImageFromUrl($this->bodyImageUrl);
            $this->bodyImagePreview = 'data:image/jpeg;base64,' . $imageData;
            $this->bodyImageBase64 = $imageData;
            $this->bodyImage = null; // Clear file upload
            $this->bodyImageUrl = '';
        } catch (\Exception $e) {
            $this->error = __('studio.invalid_image_url');
        }
    }

    public function addGarmentFromUrl()
    {
        $this->validate(['garmentImageUrl' => 'required|url']);
        $this->error = '';

        $url = $this->garmentImageUrl;
        $scraperService = app(ProductScraperService::class);

        // Check if it's a product page or direct image
        if ($scraperService->isProductUrl($url)) {
            // Try to scrape product page
            $result = $scraperService->scrapeProduct($url);

            if ($result['success'] && !empty($result['image'])) {
                $this->scrapedProduct = $result;
                // Don't add yet - wait for user confirmation
                return;
            }

            // Scraping failed, try as direct URL
            $this->error = __('studio.could_not_extract_image');
            return;
        }

        // Direct image URL - add immediately
        if (!$this->addDirectImageUrl($url, 'auto')) {
            // Error already set in addDirectImageUrl
            return;
        }
    }

    /**
     * Confirm and add scraped product image.
     */
    public function confirmScrapedProduct(): void
    {
        if (!$this->scrapedProduct) {
            return;
        }

        $category = $this->scrapedProduct['category'] ?? 'auto';
        $imageUrl = $this->scrapedProduct['image'] ?? '';

        if (empty($imageUrl)) {
            $this->error = __('studio.invalid_image_url');
            $this->scrapedProduct = null;
            return;
        }

        // Try to add the image - keep scrapedProduct if it fails
        $success = $this->addDirectImageUrl($imageUrl, $category);

        if ($success) {
            $this->scrapedProduct = null;
            $this->garmentImageUrl = '';
        }
    }

    /**
     * Cancel scraped product preview.
     */
    public function cancelScrapedProduct(): void
    {
        $this->scrapedProduct = null;
        $this->garmentImageUrl = '';
    }

    /**
     * Add a direct image URL as garment.
     * @return bool Success status
     */
    protected function addDirectImageUrl(string $url, string $category = 'auto'): bool
    {
        try {
            \Log::info('Fetching image from URL', ['url' => $url]);
            $imageData = $this->fetchImageFromUrl($url);

            if (empty($imageData)) {
                \Log::warning('Empty image data from URL', ['url' => $url]);
                $this->error = __('studio.invalid_image_url');
                return false;
            }

            $this->garmentPreviews[] = 'data:image/jpeg;base64,' . $imageData;
            $this->garmentBase64Array[] = $imageData;
            $this->garmentCategories[] = $category;
            $this->garmentImageUrl = '';
            \Log::info('Image added successfully', ['category' => $category]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch image', ['url' => $url, 'error' => $e->getMessage()]);
            $this->error = __('studio.invalid_image_url') . ' (' . $e->getMessage() . ')';
            return false;
        }
    }

    protected function fetchImageFromUrl(string $url): string
    {
        // Use full browser-like headers to avoid CDN blocks
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Sec-Fetch-Dest' => 'image',
                'Sec-Fetch-Mode' => 'no-cors',
                'Sec-Fetch-Site' => 'cross-site',
                'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . '/',
            ])
            ->withOptions(['verify' => false])
            ->get($url);

        if (!$response->successful()) {
            \Log::warning('Image fetch failed', [
                'url' => $url,
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to fetch image (HTTP ' . $response->status() . ')');
        }

        $contentType = $response->header('Content-Type');
        \Log::info('Image fetched', [
            'url' => $url,
            'content_type' => $contentType,
            'size' => strlen($response->body()),
        ]);

        // Allow image types and also octet-stream (some CDNs use this)
        if ($contentType && !str_starts_with($contentType, 'image/') && $contentType !== 'application/octet-stream') {
            throw new \Exception('URL is not an image (got: ' . $contentType . ')');
        }

        $body = $response->body();
        if (empty($body)) {
            throw new \Exception('Empty image response');
        }

        return base64_encode($body);
    }

    public function removeBodyImage()
    {
        $this->bodyImage = null;
        $this->bodyImagePreview = null;
        $this->bodyImageBase64 = null;
    }

    public function generate()
    {
        // Validate body image - either file upload or URL paste
        if (!$this->bodyImage && !$this->bodyImageBase64) {
            $this->error = __('studio.error_no_photo');
            return;
        }

        if ($this->bodyImage) {
            $this->validate(['bodyImage' => 'image|max:10240']);
        }

        $user = auth()->user();

        // Collect all garment URLs and categories (in same order)
        $garmentUrls = [];
        $garmentCategories = [];

        // Add garments from file uploads (convert to base64 and store)
        foreach ($this->uploadedGarments as $index => $garment) {
            if ($garment && method_exists($garment, 'getRealPath')) {
                $base64 = base64_encode(file_get_contents($garment->getRealPath()));
                $garmentUrls[] = $this->storeImage($base64, 'garment');
                $garmentCategories[] = $this->garmentCategories[$index] ?? 'auto';
            }
        }

        // Add garments from URL paste (already base64)
        $uploadedCount = count($this->uploadedGarments);
        foreach ($this->garmentBase64Array as $index => $base64) {
            $garmentUrls[] = $this->storeImage($base64, 'garment');
            $garmentCategories[] = $this->garmentCategories[$uploadedCount + $index] ?? 'auto';
        }

        // Add wardrobe items (default to 'auto' category)
        foreach ($this->selectedWardrobeItems as $itemId) {
            $item = WardrobeItem::find($itemId);
            if ($item) {
                $garmentUrls[] = $item->image_url;
                $garmentCategories[] = 'auto';
            }
        }

        if (empty($garmentUrls)) {
            $this->error = __('studio.error_no_clothing');
            return;
        }

        // 1 try-on session = 1 credit (regardless of number of items)
        if (!$user->hasCredits(1)) {
            $this->showCreditModal = true;
            return;
        }

        $this->error = '';

        try {
            // Get body image base64 - either from URL paste or file upload
            if ($this->bodyImageBase64) {
                $bodyBase64 = $this->bodyImageBase64;
            } else {
                $bodyBase64 = base64_encode(file_get_contents($this->bodyImage->getRealPath()));
            }

            $creditService = app(CreditService::class);

            // Store body image
            $bodyUrl = $this->storeImage($bodyBase64, 'body');

            // Create try-on record with PENDING status
            $tryOn = TryOn::create([
                'user_id' => $user->id,
                'body_image_url' => $bodyUrl,
                'garment_image_url' => $garmentUrls[0], // Primary garment (backwards compat)
                'garment_urls' => $garmentUrls, // All garments as JSON
                'garment_categories' => $garmentCategories, // Categories for each garment
                'status' => TryOn::STATUS_PENDING,
                'credits_used' => 1,
            ]);

            // Deduct 1 credit upfront
            $creditService->useCredits($user, 1, 'Virtual try-on (' . count($garmentUrls) . ' items)', (string) $tryOn->id);

            // Dispatch job to queue
            ProcessTryOn::dispatch($tryOn);

            // Track current job for UI
            $this->currentJobId = $tryOn->id;

            // Refresh the try-on to get updated status (important for sync queue driver)
            $tryOn->refresh();

            // If job completed synchronously, show the result immediately
            if ($tryOn->status === TryOn::STATUS_COMPLETED && $tryOn->result_image_url) {
                $this->resultImage = $tryOn->result_image_url;
                $this->lastTryOnId = $tryOn->id;
                $this->showResultReady = true;
            } else {
                $this->showQueueStatus = true;
            }

            // Reload pending jobs and processing results
            $this->loadPendingJobs();
            $this->loadProcessingResults();

            // Clear only garment inputs - keep body image for quick next generation
            $this->reset([
                'garmentPreviews',
                'garmentBase64Array',
                'uploadedGarments',
                'selectedWardrobeItems',
                'garmentCategories',
            ]);

            // Show success message only if still processing
            if ($tryOn->status !== TryOn::STATUS_COMPLETED) {
                session()->flash('message', __('studio.queued_success'));
            }

        } catch (\Exception $e) {
            $this->error = __('studio.queue_error') . ': ' . $e->getMessage();

            if (isset($tryOn)) {
                $tryOn->markAsFailed($e->getMessage());

                // Refund credits
                $creditService = app(CreditService::class);
                $creditService->refundCredits($user, 1, 'Try-on failed - refund', (string) $tryOn->id);
            }
        }
    }

    /**
     * Poll for job status updates.
     */
    public function pollJobStatus(): void
    {
        $this->loadPendingJobs();
        $this->loadProcessingResults();

        // Hide queue status if no pending jobs
        if (empty($this->pendingJobs) && empty($this->processingResults)) {
            $this->showQueueStatus = false;
        }
    }

    /**
     * Dismiss the queue status panel.
     */
    public function dismissQueueStatus(): void
    {
        $this->showQueueStatus = false;
    }

    /**
     * Clear all inputs for fresh start.
     */
    public function clearAll(): void
    {
        $this->reset([
            'bodyImage',
            'bodyImagePreview',
            'bodyImageBase64',
            'garmentPreviews',
            'garmentBase64Array',
            'uploadedGarments',
            'selectedWardrobeItems',
            'garmentCategories',
            'scrapedProduct',
            'resultImage',
            'lastTryOnId',
            'error',
            'showResultReady',
            'editingQueueItem',
        ]);
    }

    // ============ USER-MANAGED QUEUE ============

    /**
     * Load user's queued items.
     */
    public function loadQueueItems(): void
    {
        if (!Auth::check()) {
            $this->queueItems = [];
            return;
        }

        $this->queueItems = Auth::user()
            ->tryOns()
            ->queued()
            ->orderBy('queue_position')
            ->get()
            ->toArray();
    }

    /**
     * Add current outfit to queue.
     */
    public function addToQueue(): void
    {
        // Validate body image
        if (!$this->bodyImage && !$this->bodyImageBase64) {
            $this->error = __('studio.select_body_first');
            return;
        }

        $user = auth()->user();

        // Check queue limit (max 5)
        $queueCount = $user->tryOns()->queued()->count();
        if ($queueCount >= 5) {
            $this->error = __('studio.queue_full');
            return;
        }

        // Collect garment URLs and categories
        $garmentUrls = [];
        $garmentCategories = [];

        // From file uploads (convert to base64 and store)
        foreach ($this->uploadedGarments as $index => $garment) {
            if ($garment && method_exists($garment, 'getRealPath')) {
                $base64 = base64_encode(file_get_contents($garment->getRealPath()));
                $garmentUrls[] = $this->storeImage($base64, 'garment');
                $garmentCategories[] = $this->garmentCategories[$index] ?? 'auto';
            }
        }

        // From URL paste (already base64)
        $uploadedCount = count($this->uploadedGarments);
        foreach ($this->garmentBase64Array as $index => $base64) {
            $garmentUrls[] = $this->storeImage($base64, 'garment');
            $garmentCategories[] = $this->garmentCategories[$uploadedCount + $index] ?? 'auto';
        }

        // From wardrobe (default to 'auto' category)
        foreach ($this->selectedWardrobeItems as $itemId) {
            $item = WardrobeItem::find($itemId);
            if ($item) {
                $garmentUrls[] = $item->image_url;
                $garmentCategories[] = 'auto';
            }
        }

        if (empty($garmentUrls)) {
            $this->error = __('studio.error_no_clothing');
            return;
        }

        // Check credits
        if (!$user->hasCredits(1)) {
            $this->showCreditModal = true;
            return;
        }

        $this->error = '';

        try {
            // Get body image base64
            if ($this->bodyImageBase64) {
                $bodyBase64 = $this->bodyImageBase64;
            } else {
                $bodyBase64 = base64_encode(file_get_contents($this->bodyImage->getRealPath()));
            }

            // Store body image
            $bodyUrl = $this->storeImage($bodyBase64, 'body');

            // Create TryOn with QUEUED status
            $tryOn = TryOn::create([
                'user_id' => $user->id,
                'body_image_url' => $bodyUrl,
                'garment_image_url' => $garmentUrls[0],
                'garment_urls' => $garmentUrls,
                'garment_categories' => $garmentCategories,
                'status' => TryOn::STATUS_QUEUED,
                'queue_position' => $queueCount + 1,
                'credits_used' => 1,
            ]);

            // Deduct credit immediately
            $creditService = app(CreditService::class);
            $creditService->useCredits($user, 1, 'Queued outfit #' . $tryOn->id, (string) $tryOn->id);

            // Clear garments, keep body image
            $this->reset([
                'garmentPreviews',
                'garmentBase64Array',
                'uploadedGarments',
                'selectedWardrobeItems',
                'garmentCategories',
            ]);

            // Reload queue and show panel
            $this->loadQueueItems();
            $this->showQueuePanel = true;

            session()->flash('message', __('studio.added_to_queue'));

        } catch (\Exception $e) {
            $this->error = __('studio.queue_error') . ': ' . $e->getMessage();
        }
    }

    /**
     * Remove item from queue and refund credit.
     */
    public function removeFromQueue(int $id): void
    {
        $tryOn = Auth::user()->tryOns()
            ->queued()
            ->where('id', $id)
            ->first();

        if ($tryOn) {
            // Refund credit
            $creditService = app(CreditService::class);
            $creditService->refundCredits(Auth::user(), 1, 'Removed from queue', (string) $tryOn->id);

            // Delete the record
            $tryOn->delete();

            // Reorder remaining items
            $this->reorderQueuePositions();

            // Reload queue
            $this->loadQueueItems();

            session()->flash('message', __('studio.queue_item_removed'));
        }
    }

    /**
     * Process all queued items - dispatches jobs directly.
     */
    public function runQueue(): void
    {
        $queuedItems = Auth::user()->tryOns()
            ->queued()
            ->orderBy('queue_position')
            ->get();

        if ($queuedItems->isEmpty()) {
            return;
        }

        // IMMEDIATELY close sidebar and clear local queue
        $this->showQueuePanel = false;
        $this->queueItems = [];

        // Process each item directly (sync mode will run immediately)
        foreach ($queuedItems as $tryOn) {
            $tryOn->update([
                'status' => TryOn::STATUS_PENDING,
                'queue_position' => null,
            ]);

            // Dispatch job - with sync driver this runs immediately
            ProcessTryOn::dispatch($tryOn);
        }

        // Refresh results after processing
        $this->loadProcessingResults();
        $this->loadPendingJobs();
    }

    /**
     * Process all queued items - legacy method for backwards compatibility.
     */
    public function processQueueJobs(): void
    {
        $pendingItems = Auth::user()->tryOns()
            ->pending()
            ->get();

        foreach ($pendingItems as $tryOn) {
            ProcessTryOn::dispatch($tryOn);
        }

        // Refresh results after processing
        $this->loadProcessingResults();
        $this->loadPendingJobs();
    }

    /**
     * Load queue item for editing.
     */
    public function editQueueItem(int $id): void
    {
        $tryOn = Auth::user()->tryOns()
            ->queued()
            ->where('id', $id)
            ->first();

        if ($tryOn) {
            $this->editingQueueItem = $id;

            // Load body image
            $this->bodyImagePreview = $tryOn->body_image_url;
            $path = str_replace('/storage/', '', $tryOn->body_image_url);
            if (Storage::disk('public')->exists($path)) {
                $this->bodyImageBase64 = base64_encode(Storage::disk('public')->get($path));
            }

            // Load garments
            $this->garmentPreviews = [];
            $this->garmentBase64Array = [];
            foreach ($tryOn->getAllGarmentUrls() as $url) {
                $this->garmentPreviews[] = $url;
                // Load base64 for editing
                $path = str_replace('/storage/', '', $url);
                if (Storage::disk('public')->exists($path)) {
                    $this->garmentBase64Array[] = base64_encode(Storage::disk('public')->get($path));
                }
            }

            $this->showQueuePanel = false;
        }
    }

    /**
     * Save edits to queued item.
     */
    public function updateQueueItem(): void
    {
        if (!$this->editingQueueItem) {
            return;
        }

        $tryOn = Auth::user()->tryOns()
            ->queued()
            ->where('id', $this->editingQueueItem)
            ->first();

        if (!$tryOn) {
            $this->editingQueueItem = null;
            return;
        }

        // Validate
        if (!$this->bodyImage && !$this->bodyImageBase64) {
            $this->error = __('studio.select_body_first');
            return;
        }

        // Collect new garment URLs
        $garmentUrls = [];
        foreach ($this->garmentBase64Array as $base64) {
            $garmentUrls[] = $this->storeImage($base64, 'garment');
        }
        foreach ($this->selectedWardrobeItems as $itemId) {
            $item = WardrobeItem::find($itemId);
            if ($item) {
                $garmentUrls[] = $item->image_url;
            }
        }

        if (empty($garmentUrls)) {
            $this->error = __('studio.error_no_clothing');
            return;
        }

        try {
            // Get body image
            if ($this->bodyImageBase64) {
                $bodyBase64 = $this->bodyImageBase64;
            } else {
                $bodyBase64 = base64_encode(file_get_contents($this->bodyImage->getRealPath()));
            }
            $bodyUrl = $this->storeImage($bodyBase64, 'body');

            // Update the try-on
            $tryOn->update([
                'body_image_url' => $bodyUrl,
                'garment_image_url' => $garmentUrls[0],
                'garment_urls' => $garmentUrls,
            ]);

            $this->editingQueueItem = null;
            $this->clearAll();
            $this->loadQueueItems();
            $this->showQueuePanel = true;

            session()->flash('message', __('studio.queue_item_updated'));

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Cancel editing queue item.
     */
    public function cancelEditQueueItem(): void
    {
        $this->editingQueueItem = null;
        $this->clearAll();
        $this->loadQueueItems();
        $this->showQueuePanel = true;
    }

    /**
     * Clear entire queue and refund credits.
     */
    public function clearQueue(): void
    {
        $queuedItems = Auth::user()->tryOns()->queued()->get();
        $creditService = app(CreditService::class);

        foreach ($queuedItems as $tryOn) {
            $creditService->refundCredits(Auth::user(), 1, 'Queue cleared', (string) $tryOn->id);
            $tryOn->delete();
        }

        $this->queueItems = [];
        $this->showQueuePanel = false;

        session()->flash('message', __('studio.queue_cleared'));
    }

    /**
     * Reorder queue positions after removal.
     */
    protected function reorderQueuePositions(): void
    {
        $queuedItems = Auth::user()->tryOns()
            ->queued()
            ->orderBy('queue_position')
            ->get();

        $position = 1;
        foreach ($queuedItems as $item) {
            $item->update(['queue_position' => $position]);
            $position++;
        }
    }

    // ============ PROCESSING RESULTS ============

    /**
     * Load processing results for display in Step 3 area.
     */
    public function loadProcessingResults(): void
    {
        if (!Auth::check()) {
            $this->processingResults = [];
            return;
        }

        try {
            // Try with viewed_at column (if migration has run)
            $this->processingResults = Auth::user()->tryOns()
                ->whereIn('status', [
                    TryOn::STATUS_PENDING,
                    TryOn::STATUS_PROCESSING,
                    TryOn::STATUS_COMPLETED,
                    TryOn::STATUS_FAILED,
                ])
                ->where('created_at', '>=', now()->subHours(24))
                ->whereNull('viewed_at')
                ->orderByDesc('created_at')
                ->take(10)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            // Fallback without viewed_at (if migration hasn't run)
            $this->processingResults = Auth::user()->tryOns()
                ->whereIn('status', [
                    TryOn::STATUS_PENDING,
                    TryOn::STATUS_PROCESSING,
                    TryOn::STATUS_COMPLETED,
                    TryOn::STATUS_FAILED,
                ])
                ->where('created_at', '>=', now()->subHours(1))
                ->orderByDesc('created_at')
                ->take(10)
                ->get()
                ->toArray();
        }
    }

    /**
     * View a completed result (opens in lightbox modal).
     */
    public function viewResult(int $id): void
    {
        $tryOn = Auth::user()->tryOns()->completed()->find($id);
        if ($tryOn) {
            $this->lightboxImage = $tryOn->result_image_url;
            $this->lightboxTryOnId = $tryOn->id;
            $this->showLightbox = true;

            // Also set for action modals
            $this->historyTryOnId = $tryOn->id;
            $this->historyImageUrl = $tryOn->result_image_url;
        }
    }

    /**
     * Close the lightbox modal.
     */
    public function closeLightbox(): void
    {
        $this->showLightbox = false;
        $this->lightboxImage = null;
        $this->lightboxTryOnId = null;
    }

    /**
     * Dismiss lightbox and mark result as viewed.
     */
    public function dismissLightboxResult(): void
    {
        if ($this->lightboxTryOnId) {
            $tryOn = Auth::user()->tryOns()->find($this->lightboxTryOnId);
            if ($tryOn) {
                try {
                    $tryOn->update(['viewed_at' => now()]);
                } catch (\Exception $e) {
                    // viewed_at column might not exist yet
                }
            }
        }
        $this->closeLightbox();
        $this->loadProcessingResults();
    }

    /**
     * Dismiss a result (mark as viewed without loading).
     */
    public function dismissResult(int $id): void
    {
        $tryOn = Auth::user()->tryOns()->find($id);
        if ($tryOn) {
            try {
                $tryOn->update(['viewed_at' => now()]);
            } catch (\Exception $e) {
                // viewed_at column might not exist yet - just delete from results
                $tryOn->delete();
            }
            $this->loadProcessingResults();
        }
    }

    /**
     * Clear all processing results (mark all as viewed).
     */
    public function clearProcessingResults(): void
    {
        try {
            Auth::user()->tryOns()
                ->whereIn('status', [TryOn::STATUS_COMPLETED, TryOn::STATUS_FAILED])
                ->whereNull('viewed_at')
                ->update(['viewed_at' => now()]);
        } catch (\Exception $e) {
            // If viewed_at doesn't exist, just clear the array
        }

        $this->processingResults = [];
    }

    protected function storeImage(string $imageData, string $type): string
    {
        $base64 = $imageData;
        if (str_contains($imageData, ',')) {
            $base64 = explode(',', $imageData)[1];
        }

        $decoded = base64_decode($base64);
        $filename = "try-on/{$type}/" . uniqid() . '_' . time() . '.jpg';

        Storage::disk('public')->put($filename, $decoded);

        return '/storage/' . $filename;
    }

    public function clearResult()
    {
        $this->resultImage = null;
        $this->lastTryOnId = null;
        $this->showResultReady = false;
    }

    // ============ SAVE TO MY OUTFITS ============

    public function openSaveModal()
    {
        $this->saveOutfitName = '';
        $this->saveOutfitNotes = '';
        $this->showSaveModal = true;
    }

    public function closeSaveModal()
    {
        $this->showSaveModal = false;
        $this->saveOutfitName = '';
        $this->saveOutfitNotes = '';
    }

    public function saveToOutfits()
    {
        // Support both current result and history items
        $tryOnId = $this->historyTryOnId ?: $this->lastTryOnId;
        $imageUrl = $this->historyImageUrl ?: $this->resultImage;

        if (!$imageUrl || !$tryOnId) {
            return;
        }

        // Collect garment data for re-trying later
        $garmentData = [
            'wardrobe_items' => $this->selectedWardrobeItems,
        ];

        SavedOutfit::create([
            'user_id' => Auth::id(),
            'try_on_id' => $tryOnId,
            'image_url' => $imageUrl,
            'name' => $this->saveOutfitName ?: null,
            'notes' => $this->saveOutfitNotes ?: null,
            'garment_data' => $garmentData,
        ]);

        $this->closeSaveModal();
        $this->historyTryOnId = null;
        $this->historyImageUrl = null;
        session()->flash('message', __('outfits.saved'));
    }

    // ============ POST TO FEED ============

    public function openPostModal()
    {
        $this->postCaption = '';
        $this->postVisibility = 'public';
        $this->showPostModal = true;
    }

    public function closePostModal()
    {
        $this->showPostModal = false;
        $this->postCaption = '';
        $this->postVisibility = 'public';
    }

    public function postToFeed()
    {
        // Support both current result and history items
        $tryOnId = $this->historyTryOnId ?: $this->lastTryOnId;
        $imageUrl = $this->historyImageUrl ?: $this->resultImage;

        if (!$imageUrl || !$tryOnId) {
            return;
        }

        OutfitPost::create([
            'user_id' => Auth::id(),
            'try_on_id' => $tryOnId,
            'image_url' => $imageUrl,
            'caption' => $this->postCaption,
            'visibility' => $this->postVisibility,
        ]);

        $this->closePostModal();
        $this->historyTryOnId = null;
        $this->historyImageUrl = null;
        session()->flash('message', __('outfits.posted'));
    }

    // ============ SHARE ============

    public function openShareModal()
    {
        $this->showShareModal = true;
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->historyTryOnId = null;
        $this->historyImageUrl = null;
    }

    public function trackShare($platform)
    {
        $tryOnId = $this->historyTryOnId ?: $this->lastTryOnId;
        ShareEvent::record(
            Auth::id(),
            $platform,
            null, // No outfit post yet
            $tryOnId
        );
        $this->closeShareModal();
    }

    // ============ HISTORY ITEM ACTIONS ============

    public function openSaveModalForHistory($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->historyTryOnId = $tryOn->id;
            $this->historyImageUrl = $tryOn->result_image_url;
            $this->saveOutfitName = '';
            $this->saveOutfitNotes = '';
            $this->showSaveModal = true;
        }
    }

    public function openPostModalForHistory($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->historyTryOnId = $tryOn->id;
            $this->historyImageUrl = $tryOn->result_image_url;
            $this->postCaption = '';
            $this->postVisibility = 'public';
            $this->showPostModal = true;
        }
    }

    public function openShareModalForHistory($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->historyTryOnId = $tryOn->id;
            $this->historyImageUrl = $tryOn->result_image_url;
            $this->showShareModal = true;
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.studio', [
            'credits' => $user->credits,
            'history' => $user->tryOns()->completed()->latest()->take(6)->get(),
            'wardrobeItems' => $user->wardrobeItems()->latest()->get(),
            'selectedItems' => WardrobeItem::whereIn('id', $this->selectedWardrobeItems)->get(),
            'creditPacks' => PricingService::getCreditPacksForCurrency('usd'),
        ])->layout('layouts.app', ['title' => 'Try-On Studio']);
    }
}
