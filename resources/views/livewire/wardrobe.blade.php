<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-foreground mb-2">My Wardrobe</h1>
                <p class="text-muted-foreground">{{ $items->count() }} items saved</p>
            </div>
            <button wire:click="$set('showAddModal', true)" class="px-6 py-3 bg-primary text-primary-foreground font-medium rounded-xl hover:bg-primary/90 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Item
            </button>
        </div>

        @if($items->count() > 0)
            {{-- Category Tabs --}}
            @if($groupedItems->count() > 1)
                <div class="mb-6 flex flex-wrap gap-2">
                    <span class="px-4 py-2 bg-primary text-primary-foreground rounded-full text-sm font-medium">
                        All ({{ $items->count() }})
                    </span>
                    @foreach($groupedItems as $category => $categoryItems)
                        <span class="px-4 py-2 bg-secondary text-foreground rounded-full text-sm">
                            {{ $categories[$category] ?? ucfirst($category) }} ({{ $categoryItems->count() }})
                        </span>
                    @endforeach
                </div>
            @endif

            {{-- Items Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($items as $item)
                    <div class="bg-secondary rounded-xl overflow-hidden group relative">
                        {{-- Image --}}
                        <div class="aspect-square overflow-hidden relative">
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">

                            {{-- Hover Actions --}}
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button wire:click="toggleFavorite({{ $item->id }})" class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors">
                                    @if($item->is_favorite)
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    @endif
                                </button>
                                <button wire:click="deleteItem({{ $item->id }})" wire:confirm="Are you sure you want to delete this item?" class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Favorite Badge --}}
                            @if($item->is_favorite)
                                <div class="absolute top-2 right-2">
                                    <svg class="w-5 h-5 text-red-500 drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-3">
                            @if($item->brand)
                                <p class="text-xs text-muted-foreground">{{ $item->brand }}</p>
                            @endif
                            <p class="text-sm font-medium text-foreground truncate">{{ $item->name }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ $categories[$item->category] ?? ucfirst($item->category) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h3 class="text-xl font-semibold text-foreground mb-2">Your wardrobe is empty</h3>
                <p class="text-muted-foreground mb-6 max-w-md mx-auto">Add your favorite clothing items to your wardrobe for quick access when creating outfits</p>
                <button wire:click="$set('showAddModal', true)" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground font-medium rounded-xl hover:bg-primary/90 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Your First Item
                </button>
            </div>
        @endif
    </div>

    {{-- Add Item Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showAddModal', false)">
            <div class="bg-background rounded-2xl max-w-md w-full overflow-hidden">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Add to Wardrobe</h3>
                    <button wire:click="$set('showAddModal', false)" class="p-2 hover:bg-secondary rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="addItem" class="p-6 space-y-4">
                    {{-- Image Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Image *</label>
                        <label class="block cursor-pointer">
                            <div class="aspect-square max-w-[200px] mx-auto rounded-xl border-2 border-dashed border-border hover:border-primary transition-colors overflow-hidden relative bg-secondary">
                                @if($imagePreview)
                                    <img src="{{ $imagePreview }}" alt="Preview" class="w-full h-full object-cover">
                                    <button type="button" wire:click="$set('image', null); $set('imagePreview', null)" class="absolute top-2 right-2 p-1 bg-destructive text-destructive-foreground rounded-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @else
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
                                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm">Upload image</span>
                                    </div>
                                @endif

                                <div wire:loading wire:target="image" class="absolute inset-0 bg-background/90 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <input type="file" wire:model="image" accept="image/*" class="hidden">
                        </label>
                        @error('image') <span class="text-destructive text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Name *</label>
                        <input type="text" wire:model="name" placeholder="e.g., Blue Denim Jacket" class="w-full px-4 py-3 bg-secondary border border-border rounded-xl text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                        @error('name') <span class="text-destructive text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Category *</label>
                        <select wire:model="category" class="w-full px-4 py-3 bg-secondary border border-border rounded-xl text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand (Optional) --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Brand (optional)</label>
                        <input type="text" wire:model="brand" placeholder="e.g., Zara, H&M" class="w-full px-4 py-3 bg-secondary border border-border rounded-xl text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="w-full py-3 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="addItem">Add to Wardrobe</span>
                        <span wire:loading wire:target="addItem" class="flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Adding...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
