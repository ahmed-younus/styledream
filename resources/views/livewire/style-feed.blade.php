<div class="min-h-screen pt-20 md:pt-24 pb-12 bg-background">
    <div class="max-w-6xl mx-auto px-4">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('feed.title') }}</h1>
            <p class="text-muted-foreground">{{ __('feed.subtitle') }}</p>
        </div>

        {{-- Sort Tabs --}}
        <div class="flex justify-center gap-2 mb-8">
            <button
                wire:click="setSortBy('new')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $sortBy === 'new' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80' }}"
            >
                {{ __('feed.filter_new') }}
            </button>
            <button
                wire:click="setSortBy('trending')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $sortBy === 'trending' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80' }}"
            >
                {{ __('feed.filter_trending') }}
            </button>
            <button
                wire:click="setSortBy('top_rated')"
                class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $sortBy === 'top_rated' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80' }}"
            >
                {{ __('feed.filter_top_rated') }}
            </button>
        </div>

        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        {{-- Feed --}}
        @if($feedItems->isEmpty())
            <div class="text-center py-16">
                <svg class="w-16 h-16 mx-auto text-muted-foreground mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-muted-foreground">{{ __('feed.empty') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($feedItems as $item)
                    @if($item['type'] === 'post')
                        @php $post = $item['data']; @endphp
                        <article class="bg-card rounded-2xl overflow-hidden shadow-sm border border-border">
                            {{-- Post Header --}}
                            <div class="p-4 flex items-center gap-3">
                                @if($post->user->avatar_url)
                                    <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->display_name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center">
                                        <span class="text-primary-foreground font-semibold text-sm">{{ strtoupper(substr($post->user->display_name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-semibold text-foreground">{{ $post->user->display_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                                @if($post->is_sponsored)
                                    <span class="text-xs text-muted-foreground bg-secondary px-2 py-1 rounded">{{ __('feed.sponsored') }}</span>
                                @endif
                            </div>

                            {{-- Post Image --}}
                            <div class="relative aspect-[3/4] bg-secondary/50">
                                <img src="{{ $post->image_url }}" alt="Outfit" class="w-full h-full object-contain">
                            </div>

                            {{-- Actions --}}
                            <div class="p-4">
                                <div class="flex items-center gap-4 mb-3">
                                    {{-- Like --}}
                                    <button wire:click="toggleLike({{ $post->id }})" class="flex items-center gap-1 text-muted-foreground hover:text-destructive transition-colors">
                                        @if(auth()->check() && auth()->user()->hasLikedPost($post))
                                            <svg class="w-6 h-6 text-destructive fill-current" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        @endif
                                        <span class="text-sm">{{ $post->likes_count }}</span>
                                    </button>

                                    {{-- Comment --}}
                                    <button class="flex items-center gap-1 text-muted-foreground hover:text-foreground transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        <span class="text-sm">{{ $post->comments_count }}</span>
                                    </button>

                                    {{-- Rating --}}
                                    <button wire:click="openRatingModal({{ $post->id }})" class="flex items-center gap-1 text-muted-foreground hover:text-yellow-500 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                        @if($post->avg_rating > 0)
                                            <span class="text-sm">{{ number_format($post->avg_rating, 1) }}</span>
                                        @endif
                                    </button>

                                    <div class="flex-1"></div>

                                    {{-- Share --}}
                                    <button wire:click="openShareModal({{ $post->id }})" class="text-muted-foreground hover:text-foreground transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                        </svg>
                                    </button>

                                    {{-- Try On You --}}
                                    @auth
                                        <button wire:click="tryOnFromPost({{ $post->id }})" class="text-primary hover:text-primary/80 transition-colors" title="{{ __('feed.try_on_you') }}">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </button>
                                    @endauth

                                    {{-- Report --}}
                                    <button wire:click="openReportModal({{ $post->id }})" class="text-muted-foreground hover:text-destructive transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Caption --}}
                                @if($post->caption)
                                    <p class="text-foreground mb-3">
                                        <span class="font-semibold">{{ $post->user->display_name }}</span>
                                        {{ $post->caption }}
                                    </p>
                                @endif

                                {{-- Comments Preview --}}
                                @if($post->comments_count > 0)
                                    <button class="text-sm text-muted-foreground mb-2">
                                        {{ __('feed.view_all_comments', ['count' => $post->comments_count]) }}
                                    </button>
                                @endif

                                {{-- Add Comment --}}
                                @auth
                                    <div class="flex items-center gap-2 pt-3 border-t border-border">
                                        <input
                                            type="text"
                                            wire:model.defer="commentInputs.{{ $post->id }}"
                                            wire:keydown.enter="addComment({{ $post->id }})"
                                            placeholder="{{ __('feed.add_comment') }}"
                                            class="flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground"
                                        >
                                        <button
                                            wire:click="addComment({{ $post->id }})"
                                            class="text-primary font-semibold text-sm hover:text-primary/80"
                                        >
                                            {{ __('app.submit') }}
                                        </button>
                                    </div>
                                @endauth
                            </div>
                        </article>
                    @else
                        {{-- Ad --}}
                        @php $ad = $item['data']; @endphp
                        <div class="bg-gradient-to-r from-primary/5 to-primary/10 rounded-2xl overflow-hidden border border-primary/20 sm:col-span-2 lg:col-span-3">
                            <div class="p-4">
                                <span class="text-xs text-muted-foreground bg-secondary px-2 py-1 rounded mb-3 inline-block">{{ __('feed.ad') }}</span>
                                <div class="flex items-center gap-4">
                                    @if($ad->image_url)
                                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" class="w-20 h-20 rounded-lg object-cover">
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-foreground">{{ $ad->title }}</h3>
                                        @if($ad->tagline)
                                            <p class="text-sm text-muted-foreground">{{ $ad->tagline }}</p>
                                        @endif
                                    </div>
                                    <a
                                        href="{{ $ad->cta_url }}"
                                        onclick="Livewire.find('{{ $this->getId() }}').call('recordAdClick', {{ $ad->id }})"
                                        class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90"
                                    >
                                        {{ $ad->cta_text }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Load More --}}
            @if($hasMore)
                <div class="text-center mt-8">
                    <button
                        wire:click="loadMore"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-secondary text-secondary-foreground rounded-full font-medium hover:bg-secondary/80 transition-colors"
                    >
                        <span wire:loading.remove>{{ __('app.show_more') }}</span>
                        <span wire:loading>{{ __('app.loading') }}</span>
                    </button>
                </div>
            @endif
        @endif
    </div>

    {{-- Rating Modal --}}
    @if($showRatingModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeRatingModal">
            <div class="bg-card rounded-2xl p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('feed.rate_outfit') }}</h3>
                <div class="flex justify-center gap-2 mb-6">
                    @for($i = 1; $i <= 5; $i++)
                        <button
                            wire:click="$set('selectedRating', {{ $i }})"
                            class="text-3xl transition-transform hover:scale-110 {{ $selectedRating >= $i ? 'text-yellow-500' : 'text-gray-300' }}"
                        >
                            ★
                        </button>
                    @endfor
                </div>
                <div class="flex gap-3">
                    <button wire:click="closeRatingModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="submitRating" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90" {{ $selectedRating < 1 ? 'disabled' : '' }}>
                        {{ __('app.submit') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Report Modal --}}
    @if($showReportModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeReportModal">
            <div class="bg-card rounded-2xl p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('feed.report_title') }}</h3>
                <p class="text-sm text-muted-foreground mb-4">{{ __('feed.report_reason') }}</p>
                <div class="space-y-2 mb-4">
                    @foreach(['spam', 'inappropriate', 'harassment', 'other'] as $reason)
                        <label class="flex items-center gap-3 p-3 border border-border rounded-lg cursor-pointer hover:bg-secondary {{ $reportReason === $reason ? 'border-primary bg-primary/5' : '' }}">
                            <input type="radio" wire:model="reportReason" value="{{ $reason }}" class="text-primary">
                            <span>{{ __('feed.report_' . $reason) }}</span>
                        </label>
                    @endforeach
                </div>
                <textarea
                    wire:model="reportDetails"
                    placeholder="{{ __('feed.report_details') }}"
                    class="w-full p-3 border border-border rounded-lg bg-background text-foreground resize-none mb-4"
                    rows="3"
                ></textarea>
                <div class="flex gap-3">
                    <button wire:click="closeReportModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="submitReport" class="flex-1 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90" {{ empty($reportReason) ? 'disabled' : '' }}>
                        {{ __('feed.report') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Share Modal --}}
    @if($showShareModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="closeShareModal">
            <div class="bg-card rounded-2xl p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-foreground mb-4">{{ __('feed.share_to') }}</h3>
                <div class="grid grid-cols-3 gap-4">
                    {{-- Instagram --}}
                    <a href="#" wire:click.prevent="trackShare('instagram')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">Instagram</span>
                    </a>

                    {{-- Facebook --}}
                    <a href="#" wire:click.prevent="trackShare('facebook')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">Facebook</span>
                    </a>

                    {{-- Twitter/X --}}
                    <a href="#" wire:click.prevent="trackShare('twitter')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-black flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">X</span>
                    </a>

                    {{-- WhatsApp --}}
                    <a href="#" wire:click.prevent="trackShare('whatsapp')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">WhatsApp</span>
                    </a>

                    {{-- Copy Link --}}
                    <button wire:click="trackShare('copy_link')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-gray-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ __('feed.copy_link') }}</span>
                    </button>

                    {{-- Download --}}
                    <button wire:click="trackShare('download')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ __('feed.download') }}</span>
                    </button>
                </div>
                <button wire:click="closeShareModal" class="w-full mt-4 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                    {{ __('app.close') }}
                </button>
            </div>
        </div>
    @endif
</div>
