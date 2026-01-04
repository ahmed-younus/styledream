<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('history.title') }}</h1>
            <p class="text-muted-foreground">{{ __('history.subtitle') }}</p>
        </div>

        {{-- Flash Message --}}
        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-primary/10 text-primary border border-primary/20 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('message') }}
            </div>
        @endif

        {{-- Grid --}}
        @if($tryOns->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($tryOns as $tryOn)
                    <button wire:click="openLightbox({{ $tryOn->id }})"
                            class="aspect-[3/4] rounded-xl overflow-hidden shadow-sm border border-border relative group cursor-pointer hover:ring-2 hover:ring-primary transition-all">
                        <img src="{{ $tryOn->result_image_url }}" alt="Try-on result" class="w-full h-full object-cover">
                        {{-- Date Badge --}}
                        <div class="absolute top-2 left-2 px-2 py-1 bg-black/60 rounded-full">
                            <span class="text-[10px] text-white font-medium">{{ $tryOn->created_at->format('M d') }}</span>
                        </div>
                        {{-- Hover Indicator --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-white/90 rounded-full p-2">
                                <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $tryOns->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('history.empty_title') }}</h3>
                <p class="text-muted-foreground mb-6">{{ __('history.empty_subtitle') }}</p>
                <a href="{{ route('studio') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('history.create_first') }}
                </a>
            </div>
        @endif
    </div>

    {{-- Save Modal --}}
    @if($showSaveModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-background rounded-2xl p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('outfits.save_title') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ __('outfits.name_label') }}</label>
                        <input type="text" wire:model="saveOutfitName" class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground" placeholder="{{ __('outfits.name_placeholder') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ __('outfits.notes_label') }}</label>
                        <textarea wire:model="saveOutfitNotes" rows="3" class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground" placeholder="{{ __('outfits.notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="closeSaveModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="saveToOutfits" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        {{ __('outfits.save_button') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Post Modal --}}
    @if($showPostModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-background rounded-2xl p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('feed.post_to_feed') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ __('feed.caption') }}</label>
                        <textarea wire:model="postCaption" rows="3" class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground" placeholder="{{ __('feed.caption_placeholder') }}"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">{{ __('feed.visibility') }}</label>
                        <select wire:model="postVisibility" class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground">
                            <option value="public">{{ __('feed.public') }}</option>
                            <option value="private">{{ __('feed.private') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="closePostModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="postToFeed" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        {{ __('feed.post_button') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Share Modal --}}
    @if($showShareModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-background rounded-2xl p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('share.title') }}</h3>
                <div class="grid grid-cols-4 gap-3">
                    <a wire:click="trackShare('instagram')" href="https://www.instagram.com/" target="_blank" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-secondary transition-colors cursor-pointer">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </div>
                        <span class="text-xs text-foreground">Instagram</span>
                    </a>
                    <a wire:click="trackShare('facebook')" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/')) }}" target="_blank" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-secondary transition-colors cursor-pointer">
                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <span class="text-xs text-foreground">Facebook</span>
                    </a>
                    <a wire:click="trackShare('twitter')" href="https://twitter.com/intent/tweet?text=Check%20out%20my%20outfit!&url={{ urlencode(url('/')) }}" target="_blank" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-secondary transition-colors cursor-pointer">
                        <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                        <span class="text-xs text-foreground">X</span>
                    </a>
                    <a wire:click="trackShare('whatsapp')" href="https://wa.me/?text=Check%20out%20my%20outfit%20on%20StyleDream!" target="_blank" class="flex flex-col items-center gap-2 p-3 rounded-lg hover:bg-secondary transition-colors cursor-pointer">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <span class="text-xs text-foreground">WhatsApp</span>
                    </a>
                </div>
                <button wire:click="closeShareModal" class="w-full mt-4 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                    {{ __('app.close') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-background rounded-2xl p-6 max-w-sm w-full">
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('history.delete_title') }}</h3>
                    <p class="text-muted-foreground text-sm mb-6">{{ __('history.delete_confirm') }}</p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="closeDeleteModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="deleteTryOn" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ __('history.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Lightbox Modal --}}
    @if($showLightbox && $lightboxImage)
        <div class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
             wire:click.self="closeLightbox">
            <div class="relative max-w-[90vw] sm:max-w-md bg-background rounded-2xl overflow-hidden shadow-2xl">
                {{-- Close Button --}}
                <button wire:click="closeLightbox"
                        class="absolute top-3 right-3 z-10 p-2 bg-black/50 hover:bg-black/70 rounded-full text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Image --}}
                <div class="relative">
                    <img src="{{ $lightboxImage }}"
                         alt="Try-on result"
                         class="w-auto h-auto max-h-[60vh] sm:max-h-[65vh] max-w-full mx-auto block">
                </div>

                {{-- Actions --}}
                <div class="p-3 sm:p-4 bg-background border-t border-border">
                    <div class="flex items-center justify-center gap-3">
                        {{-- Save --}}
                        <button wire:click="openSaveModal({{ $lightboxTryOnId }})"
                                class="p-3 rounded-full bg-secondary hover:bg-secondary/80 transition-colors"
                                title="{{ __('studio.save') }}">
                            <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                        </button>

                        {{-- Post --}}
                        <button wire:click="openPostModal({{ $lightboxTryOnId }})"
                                class="p-3 rounded-full bg-secondary hover:bg-secondary/80 transition-colors"
                                title="{{ __('studio.post') }}">
                            <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </button>

                        {{-- Share --}}
                        <button wire:click="openShareModal({{ $lightboxTryOnId }})"
                                class="p-3 rounded-full bg-secondary hover:bg-secondary/80 transition-colors"
                                title="{{ __('studio.share') }}">
                            <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </button>

                        {{-- Download --}}
                        <a href="{{ $lightboxImage }}" download
                           class="p-3 rounded-full bg-secondary hover:bg-secondary/80 transition-colors"
                           title="{{ __('studio.download') }}">
                            <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>

                        {{-- Delete --}}
                        <button wire:click="confirmDelete({{ $lightboxTryOnId }})"
                                class="p-3 rounded-full bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                                title="{{ __('history.delete') }}">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
