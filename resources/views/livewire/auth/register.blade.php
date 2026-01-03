<div class="min-h-screen flex items-center justify-center pt-20 pb-12 px-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('auth.create_account') }}</h1>
            <p class="text-muted-foreground">{{ __('auth.start_free_credits') }}</p>
        </div>

        <!-- Google Sign Up -->
        <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-background border border-border rounded-lg hover:bg-secondary transition-colors mb-6">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span class="font-medium text-foreground">{{ __('auth.continue_with_google') }}</span>
        </a>

        <!-- Divider -->
        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-border"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-background text-muted-foreground">{{ __('auth.or_signup_email') }}</span>
            </div>
        </div>

        <!-- Register Form -->
        <form wire:submit="register" class="space-y-4">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-foreground mb-1.5">{{ __('auth.full_name') }}</label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    class="w-full px-4 py-3 bg-background border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-foreground placeholder-muted-foreground"
                    placeholder="{{ __('auth.name_placeholder') }}"
                >
                @error('name')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-foreground mb-1.5">{{ __('auth.email') }}</label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="w-full px-4 py-3 bg-background border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-foreground placeholder-muted-foreground"
                    placeholder="{{ __('auth.email_placeholder') }}"
                >
                @error('email')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-foreground mb-1.5">{{ __('auth.password') }}</label>
                <input
                    type="password"
                    id="password"
                    wire:model="password"
                    class="w-full px-4 py-3 bg-background border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-foreground placeholder-muted-foreground"
                    placeholder="{{ __('auth.password_min_chars') }}"
                >
                @error('password')
                    <p class="mt-1 text-sm text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-foreground mb-1.5">{{ __('auth.confirm_password') }}</label>
                <input
                    type="password"
                    id="password_confirmation"
                    wire:model="password_confirmation"
                    class="w-full px-4 py-3 bg-background border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-foreground placeholder-muted-foreground"
                    placeholder="{{ __('auth.confirm_password_placeholder') }}"
                >
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="w-full py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors flex items-center justify-center gap-2"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-70 cursor-wait"
            >
                <span wire:loading.remove>{{ __('auth.create_account_btn') }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('auth.creating_account') }}
                </span>
            </button>
        </form>

        <!-- Terms -->
        <p class="mt-4 text-center text-xs text-muted-foreground">
            {{ __('auth.terms_agreement') }}
            <a href="#" class="text-primary hover:underline">{{ __('auth.terms_of_service') }}</a>
            {{ __('auth.and') }}
            <a href="#" class="text-primary hover:underline">{{ __('auth.privacy_policy') }}</a>
        </p>

        <!-- Sign In Link -->
        <p class="mt-6 text-center text-sm text-muted-foreground">
            {{ __('auth.already_have_account') }}
            <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">{{ __('app.sign_in') }}</a>
        </p>
    </div>
</div>
