<?php

namespace App\Livewire;

use App\Models\WardrobeItem;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class Wardrobe extends Component
{
    use WithFileUploads;

    public $showAddModal = false;
    public $image;
    public $imagePreview;
    public $imageUrl = '';
    public $name = '';
    public $category = 't-shirt';
    public $brand = '';
    public $error = '';

    // Expanded categories for better AI understanding
    public $categories = [
        // Tops
        't-shirt' => 'T-Shirt',
        'shirt' => 'Shirt',
        'polo' => 'Polo Shirt',
        'sweater' => 'Sweater',
        'hoodie' => 'Hoodie',
        'blouse' => 'Blouse',
        'crop-top' => 'Crop Top',
        'tank-top' => 'Tank Top',

        // Bottoms
        'jeans' => 'Jeans',
        'pants' => 'Pants',
        'shorts' => 'Shorts',
        'skirt' => 'Skirt',
        'leggings' => 'Leggings',

        // Full Body
        'dress' => 'Dress',
        'jumpsuit' => 'Jumpsuit',
        'tracksuit' => 'Tracksuit',
        'romper' => 'Romper',

        // Traditional
        'kurta' => 'Kurta',
        'kurti' => 'Kurti',
        'shalwar-kameez' => 'Shalwar Kameez',
        'saree' => 'Saree',
        'lehenga' => 'Lehenga',
        'abaya' => 'Abaya',

        // Outerwear
        'jacket' => 'Jacket',
        'coat' => 'Coat',
        'blazer' => 'Blazer',
        'cardigan' => 'Cardigan',
        'vest' => 'Vest',

        // Footwear
        'sneakers' => 'Sneakers',
        'heels' => 'Heels',
        'boots' => 'Boots',
        'sandals' => 'Sandals',
        'loafers' => 'Loafers',

        // Accessories
        'handbag' => 'Handbag',
        'backpack' => 'Backpack',
        'scarf' => 'Scarf',
        'hat' => 'Hat',
        'belt' => 'Belt',
        'watch' => 'Watch',
        'jewelry' => 'Jewelry',
    ];

    // Category groups for organized dropdown
    public $categoryGroups = [
        'Tops' => ['t-shirt', 'shirt', 'polo', 'sweater', 'hoodie', 'blouse', 'crop-top', 'tank-top'],
        'Bottoms' => ['jeans', 'pants', 'shorts', 'skirt', 'leggings'],
        'Full Body' => ['dress', 'jumpsuit', 'tracksuit', 'romper'],
        'Traditional' => ['kurta', 'kurti', 'shalwar-kameez', 'saree', 'lehenga', 'abaya'],
        'Outerwear' => ['jacket', 'coat', 'blazer', 'cardigan', 'vest'],
        'Footwear' => ['sneakers', 'heels', 'boots', 'sandals', 'loafers'],
        'Accessories' => ['handbag', 'backpack', 'scarf', 'hat', 'belt', 'watch', 'jewelry'],
    ];

    public function updatedImage()
    {
        $this->validate(['image' => 'image|max:10240']);
        $this->imagePreview = $this->image->temporaryUrl();
        $this->imageUrl = ''; // Clear URL when file uploaded
    }

    public function loadFromUrl()
    {
        $this->validate(['imageUrl' => 'required|url']);
        $this->error = '';

        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 StyleDream/1.0'])
                ->get($this->imageUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch image');
            }

            $contentType = $response->header('Content-Type');
            if ($contentType && !str_starts_with($contentType, 'image/')) {
                throw new \Exception('URL is not an image');
            }

            $base64 = base64_encode($response->body());
            $this->imagePreview = 'data:image/jpeg;base64,' . $base64;
            $this->image = null; // Clear file upload
        } catch (\Exception $e) {
            $this->error = __('wardrobe.invalid_url');
        }
    }

    public function addItem()
    {
        // Validate - either file or URL must provide an image
        if (!$this->image && !$this->imagePreview) {
            $this->error = __('wardrobe.image_required');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        if ($this->image) {
            $this->validate(['image' => 'image|max:10240']);
        }

        $user = auth()->user();
        $originalUrl = null;

        // Handle image from file upload or URL
        if ($this->image) {
            $imageData = base64_encode(file_get_contents($this->image->getRealPath()));
        } else {
            // Image from URL - extract base64 from preview
            $imageData = str_replace('data:image/jpeg;base64,', '', $this->imagePreview);
            $originalUrl = $this->imageUrl;
        }

        // Store image
        $filename = "wardrobe/" . uniqid() . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, base64_decode($imageData));
        $imageUrl = '/storage/' . $filename;

        // Create wardrobe item
        WardrobeItem::create([
            'user_id' => $user->id,
            'name' => $this->name,
            'category' => $this->category,
            'brand' => $this->brand ?: null,
            'image_url' => $imageUrl,
            'original_url' => $originalUrl,
        ]);

        // Reset form
        $this->resetForm();
        session()->flash('message', __('wardrobe.item_added'));
    }

    public function resetForm()
    {
        $this->reset(['image', 'imagePreview', 'imageUrl', 'name', 'brand', 'showAddModal', 'error']);
        $this->category = 't-shirt';
    }

    public function deleteItem($itemId)
    {
        $item = WardrobeItem::where('id', $itemId)
            ->where('user_id', auth()->id())
            ->first();

        if ($item) {
            // Delete image file
            $imagePath = str_replace('/storage/', '', $item->image_url);
            Storage::disk('public')->delete($imagePath);

            $item->delete();
        }
    }

    public function toggleFavorite($itemId)
    {
        $item = WardrobeItem::where('id', $itemId)
            ->where('user_id', auth()->id())
            ->first();

        if ($item) {
            $item->is_favorite = !$item->is_favorite;
            $item->save();
        }
    }

    public function render()
    {
        $user = auth()->user();
        $items = $user->wardrobeItems()->latest()->get();

        // Group by category
        $groupedItems = $items->groupBy('category');

        return view('livewire.wardrobe', [
            'items' => $items,
            'groupedItems' => $groupedItems,
        ])->layout('layouts.app', ['title' => 'My Wardrobe']);
    }
}
