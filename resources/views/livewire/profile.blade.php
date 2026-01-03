<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-3xl mx-auto">
        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg text-center">
                {{ session('message') }}
            </div>
        @endif

        {{-- Profile Header --}}
        <div class="bg-secondary rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-4">
                {{-- Avatar --}}
                <div class="relative">
                    @if($avatarPreview)
                        <img src="{{ $avatarPreview }}" alt="{{ $user->display_name }}" class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-3xl font-bold">
                            {{ strtoupper(substr($user->display_name ?? $user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-foreground">{{ $user->display_name ?? $user->name }}</h1>
                            <p class="text-muted-foreground">{{ $user->email }}</p>
                            @if($user->bio)
                                <p class="text-sm text-foreground mt-2">{{ $user->bio }}</p>
                            @endif
                            <p class="text-xs text-muted-foreground mt-2">{{ __('profile.member_since') }} {{ $user->created_at->format('M Y') }}</p>
                        </div>
                        <button wire:click="openEditModal" class="px-4 py-2 border border-border rounded-lg text-foreground hover:bg-background transition-colors text-sm">
                            {{ __('profile.edit_profile') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $user->credits }}</div>
                <div class="text-xs text-muted-foreground">{{ __('app.credits') }}</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['completed_tryons'] }}</div>
                <div class="text-xs text-muted-foreground">{{ __('profile.completed_tryons') }}</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['wardrobe_items'] }}</div>
                <div class="text-xs text-muted-foreground">{{ __('profile.wardrobe_items') }}</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['public_posts'] }}</div>
                <div class="text-xs text-muted-foreground">{{ __('profile.public_posts') }}</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['likes_received'] }}</div>
                <div class="text-xs text-muted-foreground">{{ __('profile.likes_received') }}</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['saved_outfits'] }}</div>
                <div class="text-xs text-muted-foreground">{{ __('outfits.title') }}</div>
            </div>
        </div>

        {{-- Body Avatars Section --}}
        <div id="avatars" class="bg-secondary rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-foreground">{{ __('profile.my_avatars') }}</h2>
                @if($bodyAvatars->count() < 3)
                    <a href="{{ route('onboarding') }}" class="text-sm text-primary hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('profile.add_avatar') }}
                    </a>
                @endif
            </div>

            @if($bodyAvatars->count() > 0)
                <div class="grid grid-cols-3 gap-4">
                    @foreach($bodyAvatars as $bodyAvatar)
                        <div class="relative group">
                            <div class="aspect-[3/4] rounded-xl overflow-hidden border-2 transition-all
                                {{ $bodyAvatar->is_default ? 'border-primary ring-2 ring-primary ring-offset-2 ring-offset-secondary' : 'border-border' }}">
                                <img src="{{ $bodyAvatar->image_url }}" alt="{{ $bodyAvatar->name ?? 'Avatar' }}" class="w-full h-full object-cover">
                            </div>

                            {{-- Avatar Info --}}
                            <div class="mt-2 text-center">
                                <p class="text-sm font-medium text-foreground truncate">{{ $bodyAvatar->name ?? __('onboarding.default_avatar_name') }}</p>
                                <p class="text-xs
                                    @if($bodyAvatar->gender === 'men') text-blue-500
                                    @elseif($bodyAvatar->gender === 'women') text-pink-500
                                    @else text-purple-500 @endif">
                                    @if($bodyAvatar->gender === 'men') ♂ {{ __('onboarding.men') }}
                                    @elseif($bodyAvatar->gender === 'women') ♀ {{ __('onboarding.women') }}
                                    @else ⚥ {{ __('onboarding.unisex') }} @endif
                                </p>
                                @if($bodyAvatar->is_default)
                                    <span class="inline-block mt-1 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full">{{ __('profile.default') }}</span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="mt-2 flex items-center justify-center gap-2">
                                @if(!$bodyAvatar->is_default)
                                    <button wire:click="setDefaultAvatar({{ $bodyAvatar->id }})"
                                            class="px-2 py-1 text-xs bg-primary/10 text-primary rounded-md hover:bg-primary/20 transition-colors"
                                            title="{{ __('profile.set_default') }}">
                                        {{ __('profile.set_default') }}
                                    </button>
                                @endif
                                <button wire:click="deleteAvatar({{ $bodyAvatar->id }})"
                                        wire:confirm="{{ __('profile.delete_avatar_confirm') }}"
                                        class="px-2 py-1 text-xs bg-destructive/10 text-destructive rounded-md hover:bg-destructive/20 transition-colors"
                                        title="{{ __('profile.delete') }}">
                                    {{ __('profile.delete') }}
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- Add More Placeholder (if < 3) --}}
                    @for($i = $bodyAvatars->count(); $i < 3; $i++)
                        <a href="{{ route('onboarding') }}" class="aspect-[3/4] rounded-xl border-2 border-dashed border-border hover:border-primary hover:bg-primary/5 transition-all flex flex-col items-center justify-center text-muted-foreground hover:text-primary">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-xs">{{ __('profile.add_avatar') }}</span>
                        </a>
                    @endfor
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <p class="text-muted-foreground mb-4">{{ __('profile.no_avatars') }}</p>
                    <a href="{{ route('onboarding') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('profile.create_first_avatar') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Recent Posts --}}
        @if($recentPosts->count() > 0)
            <div class="bg-secondary rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-foreground">{{ __('profile.tab_posts') }}</h2>
                    <a href="{{ route('feed') }}" class="text-sm text-primary hover:underline">{{ __('app.view') }} {{ __('nav.feed') }}</a>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($recentPosts as $post)
                        <div class="aspect-square rounded-lg overflow-hidden bg-background">
                            <img src="{{ $post->image_url }}" alt="Post" class="w-full h-full object-cover hover:scale-105 transition-transform">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Subscription --}}
        <div class="bg-secondary rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-bold text-foreground mb-4">Subscription</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-foreground capitalize">{{ $user->subscription_tier }} Plan</p>
                    @if($user->subscription_ends_at)
                        <p class="text-sm text-muted-foreground">Renews {{ $user->subscription_ends_at->format('M d, Y') }}</p>
                    @endif
                </div>
                <a href="{{ route('pricing') }}" class="px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition-colors">
                    {{ $user->subscription_tier === 'free' ? 'Upgrade' : 'Manage' }}
                </a>
            </div>
        </div>

        {{-- Daily Streak --}}
        <div class="bg-secondary rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-bold text-foreground mb-4">Daily Rewards</h2>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.73-2.77-.01-2.2-1.9-2.96-3.66-3.42z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-foreground">{{ $user->current_streak }} day streak</p>
                    <p class="text-sm text-muted-foreground">
                        @if($user->canClaimDailyCredit())
                            Claim your free daily credit!
                        @else
                            Come back tomorrow for your next credit
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <a href="{{ route('my-outfits') }}" class="bg-secondary rounded-xl p-4 flex items-center gap-3 hover:bg-secondary/80 transition-colors">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>
                <span class="font-medium text-foreground">{{ __('nav.my_outfits') }}</span>
            </a>
            <a href="{{ route('wardrobe') }}" class="bg-secondary rounded-xl p-4 flex items-center gap-3 hover:bg-secondary/80 transition-colors">
                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <span class="font-medium text-foreground">{{ __('nav.wardrobe') }}</span>
            </a>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-secondary rounded-2xl p-6">
            <h2 class="text-lg font-bold text-foreground mb-4">Account</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-destructive/10 text-destructive font-medium rounded-lg hover:bg-destructive/20 transition-colors">
                    {{ __('app.sign_out') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Edit Profile Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeEditModal">
            <div class="bg-background rounded-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-foreground mb-6">{{ __('profile.edit_profile') }}</h3>

                <div class="space-y-6">
                    {{-- Avatar --}}
                    <div class="text-center">
                        <div class="relative inline-block">
                            @if($avatarPreview)
                                <img src="{{ $avatarPreview }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover mx-auto">
                            @else
                                <div class="w-24 h-24 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-3xl font-bold mx-auto">
                                    {{ strtoupper(substr($displayName ?? $user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center justify-center gap-2">
                            <label class="px-3 py-1.5 bg-secondary text-foreground text-sm rounded-lg cursor-pointer hover:bg-secondary/80">
                                {{ __('profile.change_avatar') }}
                                <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                            </label>
                            @if($avatarPreview)
                                <button wire:click="removeAvatar" class="px-3 py-1.5 text-destructive text-sm hover:bg-destructive/10 rounded-lg">
                                    {{ __('profile.remove_avatar') }}
                                </button>
                            @endif
                        </div>
                        <div wire:loading wire:target="avatar" class="text-sm text-muted-foreground mt-2">
                            {{ __('app.loading') }}
                        </div>
                    </div>

                    {{-- Display Name --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('profile.display_name') }}</label>
                        <input type="text" wire:model="displayName" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground" maxlength="50">
                        @error('displayName') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('profile.bio') }}</label>
                        <textarea wire:model="bio" rows="3" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground resize-none" maxlength="500"></textarea>
                        @error('bio') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
                    </div>

                    {{-- Language --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('profile.language') }}</label>
                        <select wire:model="locale" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground">
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeEditModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="saveProfile" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        {{ __('profile.save_changes') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
