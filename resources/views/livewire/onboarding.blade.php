<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="w-full max-w-md mx-auto">
        {{-- Step Indicators --}}
        <div class="flex justify-center gap-2 mb-8">
            @for($i = 1; $i <= 3; $i++)
                <div class="w-2 h-2 rounded-full transition-colors {{ $step >= $i ? 'bg-primary' : 'bg-border' }}"></div>
            @endfor
        </div>

        {{-- Step 1: Welcome --}}
        @if($step === 1)
            <div class="text-center">
                <div class="mb-6">
                    <div class="w-20 h-20 mx-auto bg-primary/10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-foreground mb-2">{{ __('onboarding.welcome_title') }}</h1>
                    <p class="text-muted-foreground">{{ __('onboarding.welcome_subtitle') }}</p>
                </div>

                <div class="bg-secondary rounded-2xl p-6 border border-border mb-6">
                    <h3 class="font-semibold text-foreground mb-4">{{ __('onboarding.what_is_avatar') }}</h3>
                    <ul class="space-y-3 text-left text-sm text-muted-foreground">
                        <li class="flex items-start gap-3">
                            <span class="text-primary mt-0.5">1</span>
                            <span>{{ __('onboarding.benefit_1') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-primary mt-0.5">2</span>
                            <span>{{ __('onboarding.benefit_2') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-primary mt-0.5">3</span>
                            <span>{{ __('onboarding.benefit_3') }}</span>
                        </li>
                    </ul>
                </div>

                <button wire:click="nextStep"
                        class="w-full py-3 bg-primary text-primary-foreground rounded-xl font-semibold hover:bg-primary/90 transition-colors">
                    {{ __('onboarding.get_started') }}
                </button>

                <button wire:click="skip"
                        class="w-full mt-3 py-3 text-muted-foreground hover:text-foreground transition-colors text-sm">
                    {{ __('onboarding.skip') }}
                </button>
            </div>
        @endif

        {{-- Step 2: Create Avatar --}}
        @if($step === 2)
            <div>
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-foreground mb-2">{{ __('onboarding.create_avatar_title') }}</h1>
                    <p class="text-muted-foreground text-sm">{{ __('onboarding.create_avatar_subtitle') }}</p>
                </div>

                <div class="bg-secondary rounded-2xl p-6 border border-border">
                    {{-- Image Upload --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('onboarding.upload_body') }}</label>
                        <div class="relative">
                            @if($imagePreview)
                                <div class="relative aspect-[3/4] rounded-xl overflow-hidden bg-background">
                                    <img src="{{ $imagePreview }}" alt="Avatar preview" class="w-full h-full object-cover">
                                    <button wire:click="$set('avatarImage', null); $set('imagePreview', null)"
                                            class="absolute top-2 right-2 p-1.5 bg-black/50 rounded-full text-white hover:bg-black/70 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <label class="flex flex-col items-center justify-center aspect-[3/4] rounded-xl border-2 border-dashed border-border bg-background hover:border-primary hover:bg-primary/5 transition-colors cursor-pointer">
                                    <input type="file" wire:model="avatarImage" accept="image/*" class="hidden">
                                    {{-- Body Silhouette --}}
                                    <svg class="w-16 h-24 mb-3 text-muted-foreground opacity-40" viewBox="0 0 100 150" fill="currentColor">
                                        <ellipse cx="50" cy="14" rx="11" ry="13"/>
                                        <rect x="44" y="27" width="12" height="8" rx="2"/>
                                        <path d="M32 35 C32 35 28 38 28 45 L28 75 C28 80 32 85 38 85 L38 140 C38 145 42 148 47 148 L53 148 C58 148 62 145 62 140 L62 85 C68 85 72 80 72 75 L72 45 C72 38 68 35 68 35 L60 35 L60 42 C60 47 55 52 50 52 C45 52 40 47 40 42 L40 35 Z"/>
                                        <ellipse cx="23" cy="60" rx="6" ry="20" transform="rotate(-15 23 60)"/>
                                        <ellipse cx="77" cy="60" rx="6" ry="20" transform="rotate(15 77 60)"/>
                                    </svg>
                                    <span class="text-sm text-muted-foreground">{{ __('onboarding.tap_to_upload') }}</span>
                                    <span class="text-xs text-muted-foreground mt-1">{{ __('onboarding.full_body_tip') }}</span>
                                </label>
                            @endif

                            @error('avatarImage')
                                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Gender Selection --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground mb-3">{{ __('onboarding.select_gender') }}</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="$set('gender', 'women')"
                                    class="flex flex-col items-center justify-center gap-1 p-3 rounded-xl border-2 transition-all
                                           {{ $gender === 'women' ? 'border-pink-500 bg-pink-500/10' : 'border-border hover:border-pink-500/50' }}">
                                <span class="text-lg">&#9792;</span>
                                <span class="text-sm font-medium">{{ __('onboarding.women') }}</span>
                            </button>
                            <button type="button" wire:click="$set('gender', 'men')"
                                    class="flex flex-col items-center justify-center gap-1 p-3 rounded-xl border-2 transition-all
                                           {{ $gender === 'men' ? 'border-blue-500 bg-blue-500/10' : 'border-border hover:border-blue-500/50' }}">
                                <span class="text-lg">&#9794;</span>
                                <span class="text-sm font-medium">{{ __('onboarding.men') }}</span>
                            </button>
                            <button type="button" wire:click="$set('gender', 'unisex')"
                                    class="flex flex-col items-center justify-center gap-1 p-3 rounded-xl border-2 transition-all
                                           {{ $gender === 'unisex' ? 'border-purple-500 bg-purple-500/10' : 'border-border hover:border-purple-500/50' }}">
                                <span class="text-lg">&#9893;</span>
                                <span class="text-sm font-medium">{{ __('onboarding.unisex') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Optional Name --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('onboarding.avatar_name') }}</label>
                        <input type="text"
                               wire:model="avatarName"
                               placeholder="{{ __('onboarding.avatar_name_placeholder') }}"
                               class="w-full px-4 py-3 bg-background border border-border rounded-xl text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    {{-- Submit Button --}}
                    <button wire:click="createAvatar"
                            wire:loading.attr="disabled"
                            wire:target="createAvatar"
                            class="w-full py-3 bg-primary text-primary-foreground rounded-xl font-semibold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="createAvatar">{{ __('onboarding.create_avatar') }}</span>
                        <span wire:loading wire:target="createAvatar" class="flex items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <span>{{ __('onboarding.creating') }}</span>
                        </span>
                    </button>
                </div>

                <button wire:click="skip"
                        class="w-full mt-4 py-3 text-muted-foreground hover:text-foreground transition-colors text-sm">
                    {{ __('onboarding.skip') }}
                </button>
            </div>
        @endif

        {{-- Step 3: Success --}}
        @if($step === 3)
            <div class="text-center">
                <div class="mb-6">
                    <div class="w-20 h-20 mx-auto bg-green-500/10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-foreground mb-2">{{ __('onboarding.success_title') }}</h1>
                    <p class="text-muted-foreground">{{ __('onboarding.success_subtitle') }}</p>
                </div>

                @if($createdAvatar)
                    <div class="bg-secondary rounded-2xl p-6 border border-border mb-6">
                        <div class="w-24 h-24 mx-auto rounded-xl overflow-hidden mb-4 ring-4
                            @if($createdAvatar->gender === 'men') ring-blue-500/30
                            @elseif($createdAvatar->gender === 'women') ring-pink-500/30
                            @else ring-purple-500/30 @endif">
                            <img src="{{ $createdAvatar->image_url }}" alt="Your avatar" class="w-full h-full object-cover">
                        </div>
                        <p class="font-medium text-foreground">{{ $createdAvatar->name ?? __('onboarding.default_avatar_name') }}</p>
                        <p class="text-sm text-muted-foreground">
                            @if($createdAvatar->gender === 'men') {{ __('onboarding.men') }}
                            @elseif($createdAvatar->gender === 'women') {{ __('onboarding.women') }}
                            @else {{ __('onboarding.unisex') }} @endif
                        </p>
                    </div>
                @endif

                <button wire:click="goToStudio"
                        class="w-full py-3 bg-primary text-primary-foreground rounded-xl font-semibold hover:bg-primary/90 transition-colors">
                    {{ __('onboarding.go_to_studio') }}
                </button>

                <p class="text-xs text-muted-foreground mt-4">{{ __('onboarding.can_add_more') }}</p>
            </div>
        @endif
    </div>
</div>
