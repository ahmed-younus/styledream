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

        // Append new images - convert to base64 for unified handling
        foreach ($this->garmentImages as $image) {
            $base64 = base64_encode(file_get_contents($image->getRealPath()));
            $this->garmentPreviews[] = 'data:image/jpeg;base64,' . $base64;
            $this->garmentBase64Array[] = $base64;
        }

        // Clear uploadedGarments since we now use garmentBase64Array
        $this->uploadedGarments = [];
        $this->garmentImages = [];
    }

    public function removeGarment($index)
    {
        // Remove from both arrays
        if (isset($this->garmentPreviews[$index])) {
            unset($this->garmentPreviews[$index]);
            $this->garmentPreviews = array_values($this->garmentPreviews);
        }

        if (isset($this->garmentBase64Array[$index])) {
            unset($this->garmentBase64Array[$index]);
            $this->garmentBase64Array = array_values($this->garmentBase64Array);
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

        try {
            $imageData = $this->fetchImageFromUrl($this->garmentImageUrl);
            $this->garmentPreviews[] = 'data:image/jpeg;base64,' . $imageData;
            $this->garmentBase64Array[] = $imageData;
            $this->garmentImageUrl = '';
        } catch (\Exception $e) {
            $this->error = __('studio.invalid_image_url');
        }
    }

    protected function fetchImageFromUrl(string $url): string
    {
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 StyleDream/1.0'])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch image');
        }

        $contentType = $response->header('Content-Type');
        if ($contentType && !str_starts_with($contentType, 'image/')) {
            throw new \Exception('URL is not an image');
        }

        return base64_encode($response->body());
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

        // Collect all garment URLs (store images first)
        $garmentUrls = [];

        // Add garments from uploads and URL paste (unified in garmentBase64Array)
        foreach ($this->garmentBase64Array as $base64) {
            $garmentUrls[] = $this->storeImage($base64, 'garment');
        }

        // Add wardrobe items
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
                'status' => TryOn::STATUS_PENDING,
                'credits_used' => 1,
            ]);

            // Deduct 1 credit upfront
            $creditService->useCredits($user, 1, 'Virtual try-on (' . count($garmentUrls) . ' items)', (string) $tryOn->id);

            // Dispatch job to queue
            ProcessTryOn::dispatch($tryOn);

            // Track current job for UI
            $this->currentJobId = $tryOn->id;
            $this->showQueueStatus = true;

            // Reload pending jobs
            $this->loadPendingJobs();

            // Clear only garment inputs - keep body image for quick next generation
            $this->reset([
                'garmentPreviews',
                'garmentBase64Array',
                'selectedWardrobeItems',
            ]);

            // Show success message
            session()->flash('message', __('studio.queued_success'));

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

        // Check if any jobs just completed
        $completedJobs = Auth::user()
            ->tryOns()
            ->completed()
            ->whereNotNull('result_image_url')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->latest()
            ->get();

        // If we have a recently completed job and no result showing, show it
        if ($completedJobs->isNotEmpty() && !$this->resultImage) {
            $latestCompleted = $completedJobs->first();
            $this->resultImage = $latestCompleted->result_image_url;
            $this->lastTryOnId = $latestCompleted->id;
        }

        // Hide queue status if no pending jobs
        if (empty($this->pendingJobs)) {
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
            'selectedWardrobeItems',
            'resultImage',
            'lastTryOnId',
            'error',
            'showResultReady',
        ]);
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
        ])->layout('layouts.app', ['title' => 'Try-On Studio']);
    }
}
