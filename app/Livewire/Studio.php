<?php

namespace App\Livewire;

use App\Models\TryOn;
use App\Models\WardrobeItem;
use App\Services\GoogleAIService;
use App\Services\CreditService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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
    public $isProcessing = false;
    public $error = '';

    public function updatedBodyImage()
    {
        $this->validate(['bodyImage' => 'image|max:10240']);
        $this->bodyImagePreview = $this->bodyImage->temporaryUrl();
    }

    public function removeBodyImage()
    {
        $this->bodyImage = null;
        $this->bodyImagePreview = null;
    }

    public function updatedGarmentImages()
    {
        $this->validate(['garmentImages.*' => 'image|max:10240']);

        // Append new images instead of replacing
        foreach ($this->garmentImages as $image) {
            $this->garmentPreviews[] = $image->temporaryUrl();
            $this->uploadedGarments[] = $image;
        }

        // Clear the input so new uploads can be detected
        $this->garmentImages = [];
    }

    public function removeGarment($index)
    {
        unset($this->uploadedGarments[$index]);
        unset($this->garmentPreviews[$index]);
        $this->uploadedGarments = array_values($this->uploadedGarments);
        $this->garmentPreviews = array_values($this->garmentPreviews);
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

    public $showWardrobeModal = false;

    public function removeWardrobeItem($itemId)
    {
        $this->selectedWardrobeItems = array_diff($this->selectedWardrobeItems, [$itemId]);
        $this->selectedWardrobeItems = array_values($this->selectedWardrobeItems);
    }

    public function generate()
    {
        $this->validate([
            'bodyImage' => 'required|image|max:10240',
        ]);

        $user = auth()->user();

        // Collect all garment base64 data
        $garmentBase64Array = [];
        $garmentUrls = [];

        // Add uploaded garments
        foreach ($this->uploadedGarments as $image) {
            $garmentBase64 = base64_encode(file_get_contents($image->getRealPath()));
            $garmentBase64Array[] = $garmentBase64;
            $garmentUrls[] = $this->storeImage($garmentBase64, 'garment');
        }

        // Add wardrobe items
        foreach ($this->selectedWardrobeItems as $itemId) {
            $item = WardrobeItem::find($itemId);
            if ($item) {
                $imagePath = str_replace('/storage/', '', $item->image_url);
                $garmentBase64 = base64_encode(Storage::disk('public')->get($imagePath));
                $garmentBase64Array[] = $garmentBase64;
                $garmentUrls[] = $item->image_url;
            }
        }

        if (empty($garmentBase64Array)) {
            $this->error = 'Please add at least one clothing item.';
            return;
        }

        // 1 try-on session = 1 credit (regardless of number of items)
        if (!$user->hasCredits(1)) {
            $this->error = "Insufficient credits. You need 1 credit to generate try-on.";
            return;
        }

        $this->isProcessing = true;
        $this->error = '';
        $this->resultImage = null;

        try {
            $bodyBase64 = base64_encode(file_get_contents($this->bodyImage->getRealPath()));
            $aiService = app(GoogleAIService::class);
            $creditService = app(CreditService::class);

            // Store body image
            $bodyUrl = $this->storeImage($bodyBase64, 'body');

            // Create try-on record
            $tryOn = TryOn::create([
                'user_id' => $user->id,
                'body_image_url' => $bodyUrl,
                'garment_image_url' => $garmentUrls[0], // Primary garment
                'status' => TryOn::STATUS_PROCESSING,
                'credits_used' => 1,
            ]);

            // Deduct 1 credit
            $creditService->useCredits($user, 1, 'Virtual try-on (' . count($garmentBase64Array) . ' items)', (string) $tryOn->id);

            $startTime = microtime(true);

            // Call AI service with ALL garments at once
            $result = $aiService->generateMultipleTryOn($bodyBase64, $garmentBase64Array);

            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            // Store result
            $resultUrl = $this->storeImage($result['image'], 'result');

            $tryOn->markAsCompleted($resultUrl, $processingTime);

            $this->resultImage = $resultUrl;

        } catch (\Exception $e) {
            $this->error = 'Failed to generate try-on: ' . $e->getMessage();

            if (isset($tryOn)) {
                $tryOn->markAsFailed($e->getMessage());

                // Refund credits
                $creditService = app(CreditService::class);
                $creditService->refundCredits($user, 1, 'Try-on failed - refund', (string) $tryOn->id);
            }
        }

        $this->isProcessing = false;
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
