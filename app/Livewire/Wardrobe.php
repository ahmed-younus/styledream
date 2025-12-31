<?php

namespace App\Livewire;

use App\Models\WardrobeItem;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Wardrobe extends Component
{
    use WithFileUploads;

    public $showAddModal = false;
    public $image;
    public $imagePreview;
    public $name = '';
    public $category = 'tops';
    public $brand = '';

    public $categories = [
        'tops' => 'Tops & Shirts',
        'bottoms' => 'Pants & Bottoms',
        'dresses' => 'Dresses',
        'outerwear' => 'Jackets & Coats',
        'shoes' => 'Shoes',
        'accessories' => 'Accessories',
    ];

    public function updatedImage()
    {
        $this->validate(['image' => 'image|max:10240']);
        $this->imagePreview = $this->image->temporaryUrl();
    }

    public function addItem()
    {
        $this->validate([
            'image' => 'required|image|max:10240',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
        ]);

        $user = auth()->user();

        // Store image
        $imageData = base64_encode(file_get_contents($this->image->getRealPath()));
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
        ]);

        // Reset form
        $this->reset(['image', 'imagePreview', 'name', 'category', 'brand', 'showAddModal']);
        $this->category = 'tops';
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
