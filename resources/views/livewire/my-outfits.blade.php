<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('outfits.title') }}</h1>
            <p class="text-muted-foreground">{{ __('outfits.subtitle') }}</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg text-center">
                {{ session('message') }}
            </div>
        @endif

        {{-- Outfits Grid --}}
        @if($outfits->isEmpty())
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-muted-foreground mb-4">{{ __('outfits.empty') }}</p>
                <a href="{{ route('studio') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-full font-medium hover:bg-primary/90 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('nav.studio') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($outfits as $outfit)
                    <div class="group relative bg-card rounded-xl overflow-hidden border border-border shadow-sm">
                        {{-- Image --}}
                        <div class="aspect-[3/4] bg-secondary">
                            <img src="{{ $outfit->image_url }}" alt="{{ $outfit->name ?? 'Saved outfit' }}" class="w-full h-full object-cover">
                        </div>

                        {{-- Overlay on Hover --}}
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 p-4">
                            {{-- Try Again --}}
                            <button
                                wire:click="tryAgain({{ $outfit->id }})"
                                class="w-full py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                {{ __('outfits.try_again') }}
                            </button>

                            {{-- Post to Feed --}}
                            @if(!$outfit->outfit_post_id)
                                <button
                                    wire:click="openPostModal({{ $outfit->id }})"
                                    class="w-full py-2 bg-white text-foreground rounded-lg text-sm font-medium hover:bg-gray-100 flex items-center justify-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                    </svg>
                                    {{ __('outfits.post_to_feed') }}
                                </button>
                            @else
                                <span class="w-full py-2 bg-green-500/20 text-green-400 rounded-lg text-sm font-medium text-center">
                                    {{ __('feed.posted') }}
                                </span>
                            @endif

                            {{-- Delete --}}
                            <button
                                wire:click="deleteOutfit({{ $outfit->id }})"
                                class="w-full py-2 bg-destructive/20 text-destructive-foreground rounded-lg text-sm font-medium hover:bg-destructive/40 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                {{ __('outfits.delete_outfit') }}
                            </button>
                        </div>

                        {{-- Info --}}
                        <div class="p-3">
                            @if($outfit->name)
                                <p class="font-medium text-foreground truncate">{{ $outfit->name }}</p>
                            @endif
                            <p class="text-xs text-muted-foreground">{{ $outfit->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeDeleteModal">
            <div class="bg-card rounded-2xl p-6 max-w-sm w-full">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('outfits.delete_outfit') }}</h3>
                    <p class="text-muted-foreground">{{ __('outfits.delete_confirm') }}</p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="closeDeleteModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="confirmDelete" class="flex-1 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90">
                        {{ __('app.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Post to Feed Modal --}}
    @if($showPostModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closePostModal">
            <div class="bg-card rounded-2xl p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('outfits.post_title') }}</h3>

                {{-- Caption --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.post_caption') }}</label>
                    <textarea
                        wire:model="postCaption"
                        class="w-full p-3 border border-border rounded-lg bg-background text-foreground resize-none"
                        rows="3"
                        placeholder="{{ __('feed.caption_placeholder') }}"
                    ></textarea>
                </div>

                {{-- Visibility --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.post_visibility') }}</label>
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center gap-2 p-3 border border-border rounded-lg cursor-pointer {{ $postVisibility === 'public' ? 'border-primary bg-primary/5' : '' }}">
                            <input type="radio" wire:model="postVisibility" value="public" class="text-primary">
                            <div>
                                <p class="font-medium text-foreground">{{ __('outfits.post_public') }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('feed.visibility_public') }}</p>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-2 p-3 border border-border rounded-lg cursor-pointer {{ $postVisibility === 'private' ? 'border-primary bg-primary/5' : '' }}">
                            <input type="radio" wire:model="postVisibility" value="private" class="text-primary">
                            <div>
                                <p class="font-medium text-foreground">{{ __('outfits.post_private') }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('feed.visibility_private') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button wire:click="closePostModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="postToFeed" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        <span wire:loading.remove wire:target="postToFeed">{{ __('feed.post_to_feed') }}</span>
                        <span wire:loading wire:target="postToFeed">{{ __('outfits.posting') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
