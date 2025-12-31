<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">Try-On Studio</h1>
            <p class="text-muted-foreground">Upload your photo and select clothing items to create your outfit</p>
            <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-secondary rounded-full">
                <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <span class="font-semibold text-foreground">{{ $credits }}</span>
                <span class="text-muted-foreground">credits available</span>
            </div>
        </div>

        {{-- Error Message --}}
        @if($error)
            <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive text-center">
                {{ $error }}
            </div>
        @endif

        {{-- Main Content --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left Column: Body Image --}}
            <div class="space-y-6">
                {{-- Body Image Upload --}}
                <div class="bg-secondary rounded-2xl p-6">
                    <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
                        Your Photo
                    </h3>
                    <div class="aspect-[3/4] rounded-xl border-2 border-dashed border-border hover:border-primary transition-colors overflow-hidden relative bg-background">
                        @if($bodyImagePreview)
                            <img src="{{ $bodyImagePreview }}" alt="Body preview" class="w-full h-full object-cover">
                            <button type="button" wire:click="removeBodyImage" class="absolute top-2 right-2 p-1.5 bg-destructive text-destructive-foreground rounded-full hover:bg-destructive/90 z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @else
                            <label class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground cursor-pointer">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium">Upload your photo</span>
                                <span class="text-xs">Full body shot works best</span>
                                <input type="file" wire:model="bodyImage" accept="image/*" class="hidden">
                            </label>
                        @endif

                        {{-- Loading overlay --}}
                        <div wire:loading wire:target="bodyImage" class="absolute inset-0 bg-background/90 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Middle Column: Clothing Items --}}
            <div class="space-y-6">
                <div class="bg-secondary rounded-2xl p-6">
                    <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">2</span>
                        Clothing Items
                        <span class="ml-auto text-sm text-muted-foreground">{{ count($garmentPreviews) + count($selectedWardrobeItems) }} selected</span>
                    </h3>

                    {{-- Upload Multiple --}}
                    <label class="block cursor-pointer mb-4">
                        <div class="border-2 border-dashed border-border hover:border-primary transition-colors rounded-xl p-4 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-sm text-muted-foreground">Upload clothing images</span>
                            <span class="block text-xs text-muted-foreground mt-1">Select multiple files</span>
                        </div>
                        <input type="file" wire:model="garmentImages" accept="image/*" multiple class="hidden">
                    </label>

                    {{-- Or select from wardrobe --}}
                    @if($wardrobeItems->count() > 0)
                        <button wire:click="$set('showWardrobeModal', true)" class="w-full py-3 border-2 border-primary/30 text-primary rounded-xl hover:bg-primary/10 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Select from Wardrobe
                        </button>
                    @endif

                    {{-- Selected Items Preview --}}
                    @if(count($garmentPreviews) > 0 || count($selectedWardrobeItems) > 0)
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            {{-- Uploaded garments --}}
                            @foreach($garmentPreviews as $index => $preview)
                                <div class="relative aspect-square rounded-lg border border-border">
                                    <img src="{{ $preview }}" alt="Garment" class="w-full h-full object-cover rounded-lg">
                                    <button type="button" wire:click="removeGarment({{ $index }})" style="position: absolute; top: 4px; right: 4px; width: 20px; height: 20px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                                        <svg style="width: 12px; height: 12px; color: white;" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach

                            {{-- Wardrobe items --}}
                            @foreach($selectedItems as $item)
                                <div class="relative aspect-square rounded-lg border border-border">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover rounded-lg">
                                    <button type="button" wire:click="removeWardrobeItem({{ $item->id }})" style="position: absolute; top: 4px; right: 4px; width: 20px; height: 20px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                                        <svg style="width: 12px; height: 12px; color: white;" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-3 text-xs text-muted-foreground text-center">
                            All items will be combined into one outfit
                        </p>
                    @endif

                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="garmentImages" class="mt-4 flex items-center justify-center py-4">
                        <svg class="w-6 h-6 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="ml-2 text-sm text-muted-foreground">Uploading...</span>
                    </div>
                </div>

                {{-- Generate Button --}}
                <button
                    wire:click="generate"
                    wire:loading.attr="disabled"
                    @if(!$bodyImagePreview || (count($garmentPreviews) == 0 && count($selectedWardrobeItems) == 0)) disabled @endif
                    class="w-full py-4 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    @if($isProcessing)
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>Generating outfit...</span>
                    @else
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        <span>Generate Outfit (1 credit)</span>
                    @endif
                </button>
            </div>

            {{-- Right Column: Result --}}
            <div class="bg-secondary rounded-2xl p-6">
                <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">3</span>
                    Result
                </h3>
                <div class="aspect-[3/4] rounded-xl border-2 border-dashed border-border overflow-hidden relative bg-background">
                    @if($resultImage)
                        <img src="{{ $resultImage }}" alt="Try-on result" class="w-full h-full object-cover">
                        <div class="absolute bottom-3 left-3 right-3 flex gap-2">
                            <a href="{{ $resultImage }}" download class="flex-1 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg text-center hover:bg-primary/90">
                                Download
                            </a>
                            <button wire:click="clearResult" class="px-4 py-2 bg-secondary text-foreground text-sm font-medium rounded-lg hover:bg-secondary/80 border border-border">
                                Clear
                            </button>
                        </div>
                    @elseif($isProcessing)
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-primary animate-spin mb-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-foreground font-medium">Creating your outfit...</span>
                            <span class="text-sm text-muted-foreground">This may take up to 60 seconds</span>
                        </div>
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="text-sm font-medium">Your outfit will appear here</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- History Section --}}
        @if($history->count() > 0)
            <div class="mt-12">
                <h2 class="text-xl font-bold text-foreground mb-4">Recent Try-Ons</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                    @foreach($history as $tryOn)
                        <div class="aspect-[3/4] rounded-xl overflow-hidden bg-secondary">
                            <img src="{{ $tryOn->result_image_url }}" alt="Try-on result" class="w-full h-full object-cover hover:scale-105 transition-transform cursor-pointer">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Wardrobe Modal --}}
    @if($showWardrobeModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showWardrobeModal', false)">
            <div class="bg-background rounded-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Select from Wardrobe</h3>
                    <button wire:click="$set('showWardrobeModal', false)" class="p-2 hover:bg-secondary rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    @if($wardrobeItems->count() > 0)
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                            @foreach($wardrobeItems as $item)
                                <button wire:click="toggleWardrobeItem({{ $item->id }})" class="relative aspect-square rounded-xl overflow-hidden border-2 {{ in_array($item->id, $selectedWardrobeItems) ? 'border-primary' : 'border-transparent' }} hover:border-primary/50 transition-colors">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    @if(in_array($item->id, $selectedWardrobeItems))
                                        <div class="absolute inset-0 bg-primary/20 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="absolute bottom-1 left-1 right-1 text-xs bg-black/50 text-white px-2 py-1 rounded truncate">{{ $item->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-muted-foreground">
                            <p>Your wardrobe is empty.</p>
                            <a href="{{ route('wardrobe') }}" class="text-primary hover:underline">Add items to your wardrobe</a>
                        </div>
                    @endif
                </div>
                <div class="p-6 border-t border-border flex justify-end gap-3">
                    <button wire:click="$set('showWardrobeModal', false)" class="px-4 py-2 text-muted-foreground hover:text-foreground">
                        Cancel
                    </button>
                    <button wire:click="addFromWardrobe" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90">
                        Done ({{ count($selectedWardrobeItems) }} selected)
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
