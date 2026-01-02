<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-3xl mx-auto">
        {{-- Profile Header --}}
        <div class="bg-secondary rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-4">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover">
                @else
                    <div class="w-20 h-20 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-2xl font-bold">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ $user->name }}</h1>
                    <p class="text-muted-foreground">{{ $user->email }}</p>
                    <p class="text-sm text-muted-foreground mt-1">Member since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $user->credits }}</div>
                <div class="text-sm text-muted-foreground">Credits</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['completed_tryons'] }}</div>
                <div class="text-sm text-muted-foreground">Try-Ons</div>
            </div>
            <div class="bg-secondary rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-foreground">{{ $stats['wardrobe_items'] }}</div>
                <div class="text-sm text-muted-foreground">Wardrobe</div>
            </div>
        </div>

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

        {{-- Danger Zone --}}
        <div class="bg-secondary rounded-2xl p-6">
            <h2 class="text-lg font-bold text-foreground mb-4">Account</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-destructive/10 text-destructive font-medium rounded-lg hover:bg-destructive/20 transition-colors">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
