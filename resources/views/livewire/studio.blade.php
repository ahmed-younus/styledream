<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background" @if(count($pendingJobs) > 0) wire:poll.3s="pollJobStatus" @endif>
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-foreground mb-2">{{ __('studio.title') }}</h1>
            <p class="text-muted-foreground">{{ __('studio.subtitle') }}</p>
            <a href="{{ route('pricing') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-secondary hover:bg-secondary/80 rounded-full transition-all border border-border group">
                <svg class="w-4 h-4 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v12M8 10h8M8 14h8"/>
                </svg>
                <span class="font-semibold text-foreground">{{ $credits }}</span>
                <span class="text-muted-foreground">{{ __('studio.credits_available') }}</span>
                <span class="text-[10px] font-semibold text-primary-foreground bg-primary px-2 py-0.5 rounded-full group-hover:bg-primary/90 transition-colors">GET MORE</span>
            </a>
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
                <div class="bg-secondary rounded-2xl p-6 overflow-hidden">
                    <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">1</span>
                        {{ __('studio.step_photo') }}
                    </h3>

                    {{-- My Avatars Section --}}
                    @auth
                        @if(count($avatars) > 0 && !$bodyImagePreview)
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-medium text-foreground">{{ __('studio.my_avatars') }}</p>
                                    <a href="{{ route('profile') }}#avatars" class="text-xs text-primary hover:underline">
                                        {{ __('studio.manage') }}
                                    </a>
                                </div>
                                <div class="flex gap-2 overflow-x-auto">
                                    @foreach($avatars as $avatar)
                                        <button wire:click="useAvatar({{ $avatar['id'] }})"
                                                class="flex-shrink-0">
                                            <div class="w-16 h-16 rounded-lg overflow-hidden border-2 {{ $avatar['is_default'] ? 'border-primary' : 'border-border' }} hover:border-primary transition-colors bg-background">
                                                <img src="{{ $avatar['image_url'] }}" alt="{{ $avatar['name'] ?? 'Avatar' }}" class="w-full h-full object-cover object-top">
                                            </div>
                                        </button>
                                    @endforeach

                                    {{-- Add Avatar Button (if < 3) --}}
                                    @if(count($avatars) < 3)
                                        <a href="{{ route('onboarding') }}"
                                           class="flex-shrink-0 w-16 h-16 rounded-lg border-2 border-dashed border-border hover:border-primary transition-colors flex items-center justify-center text-muted-foreground hover:text-primary">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @elseif(count($avatars) === 0 && !$bodyImagePreview)
                            {{-- No avatars - prompt to create --}}
                            <div class="mb-4 p-3 bg-primary/5 rounded-xl border border-primary/10">
                                <p class="text-xs text-muted-foreground mb-2">{{ __('studio.no_avatars_yet') }}</p>
                                <a href="{{ route('onboarding') }}" class="text-xs text-primary font-medium hover:underline">
                                    {{ __('studio.create_avatar') }} &rarr;
                                </a>
                            </div>
                        @endif
                    @endauth

                    {{-- Saved Body Images --}}
                    @auth
                        @if(count($savedBodyImages) > 0 && !$bodyImagePreview)
                            <div class="mb-4">
                                <p class="text-xs text-muted-foreground mb-2">{{ __('studio.recent_photos') }}</p>
                                <div class="flex gap-2 overflow-x-auto">
                                    @foreach($savedBodyImages as $img)
                                        <button wire:click="useBodyImage('{{ $img }}')"
                                                class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden border-2 border-border hover:border-primary transition-colors bg-background">
                                            <img src="{{ $img }}" alt="Previous photo" class="w-full h-full object-cover object-top">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endauth

                    <div class="aspect-[3/4] rounded-xl border-2 border-dashed border-border hover:border-primary transition-colors overflow-hidden relative bg-background">
                        @if($bodyImagePreview)
                            <img src="{{ $bodyImagePreview }}" alt="Body preview" class="w-full h-full object-cover">
                            <button type="button" wire:click="removeBodyImage" class="absolute top-2 right-2 p-1.5 bg-destructive text-destructive-foreground rounded-full hover:bg-destructive/90 z-10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @else
                            <label class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground cursor-pointer p-4">
                                {{-- Body Silhouette SVG --}}
                                <svg class="w-20 h-28 mb-3 opacity-30" viewBox="0 0 100 150" fill="currentColor">
                                    {{-- Head --}}
                                    <ellipse cx="50" cy="14" rx="11" ry="13"/>
                                    {{-- Neck --}}
                                    <rect x="44" y="27" width="12" height="8" rx="2"/>
                                    {{-- Body/Torso --}}
                                    <path d="M32 35 C32 35 28 38 28 45 L28 75 C28 80 32 85 38 85 L62 85 C68 85 72 80 72 75 L72 45 C72 38 68 35 68 35 L32 35 Z"/>
                                    {{-- Arms --}}
                                    <path d="M28 38 L18 65 C16 70 18 73 22 74 L26 75 L32 50 L28 38 Z"/>
                                    <path d="M72 38 L82 65 C84 70 82 73 78 74 L74 75 L68 50 L72 38 Z"/>
                                    {{-- Legs --}}
                                    <path d="M38 85 L35 140 C35 144 38 147 42 147 L48 147 L50 95 L38 85 Z"/>
                                    <path d="M62 85 L65 140 C65 144 62 147 58 147 L52 147 L50 95 L62 85 Z"/>
                                </svg>
                                <span class="text-sm font-medium">{{ __('studio.upload_photo') }}</span>
                                <span class="text-xs text-center mt-1">{{ __('studio.full_body_tip') }}</span>
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

                    {{-- URL Paste Option --}}
                    @if(!$bodyImagePreview)
                        <div class="mt-3">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex-1 h-px bg-border"></div>
                                <span class="text-xs text-muted-foreground">{{ __('studio.or_paste_link') }}</span>
                                <div class="flex-1 h-px bg-border"></div>
                            </div>
                            <div class="flex gap-2">
                                <input type="url" wire:model="bodyImageUrl"
                                       placeholder="https://example.com/image.jpg"
                                       class="flex-1 px-3 py-2 text-sm bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button wire:click="addBodyFromUrl"
                                        wire:loading.attr="disabled"
                                        class="px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="addBodyFromUrl">{{ __('studio.add') }}</span>
                                    <svg wire:loading wire:target="addBodyFromUrl" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Tips Section --}}
                    <div class="mt-4 p-3 bg-primary/5 rounded-xl border border-primary/10">
                        <p class="text-xs font-semibold text-primary mb-2">{{ __('studio.tips_title') }}</p>
                        <div class="grid grid-cols-2 gap-2 text-xs text-muted-foreground">
                            <div class="flex items-center gap-1.5">
                                <span>💡</span>
                                <span>{{ __('studio.tip_lighting') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span>🧍</span>
                                <span>{{ __('studio.tip_pose') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span>👕</span>
                                <span>{{ __('studio.tip_clothes') }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span>📏</span>
                                <span>{{ __('studio.tip_fullbody') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Middle Column: Clothing Items --}}
            <div class="space-y-6">
                <div class="bg-secondary rounded-2xl p-6">
                    <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">2</span>
                        {{ __('studio.step_clothing') }}
                        <span class="ml-auto text-sm text-muted-foreground">{{ count($garmentPreviews) + count($selectedWardrobeItems) }} {{ __('studio.selected') }}</span>
                    </h3>

                    {{-- Upload Multiple --}}
                    <label class="block cursor-pointer mb-3">
                        <div class="border-2 border-dashed border-border hover:border-primary transition-colors rounded-xl p-4 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-sm text-muted-foreground">{{ __('studio.upload_clothing') }}</span>
                            <span class="block text-xs text-muted-foreground mt-1">{{ __('studio.select_multiple') }}</span>
                        </div>
                        <input type="file" wire:model="garmentImages" accept="image/*" multiple class="hidden">
                    </label>

                    {{-- URL Paste for Garments --}}
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex-1 h-px bg-border"></div>
                            <span class="text-xs text-muted-foreground">{{ __('studio.or_paste_link') }}</span>
                            <div class="flex-1 h-px bg-border"></div>
                        </div>
                        <div class="flex gap-2">
                            <input type="url" wire:model="garmentImageUrl"
                                   placeholder="https://example.com/clothing.jpg"
                                   class="flex-1 px-3 py-2 text-sm bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <button wire:click="addGarmentFromUrl"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50">
                                <span wire:loading.remove wire:target="addGarmentFromUrl">{{ __('studio.add') }}</span>
                                <svg wire:loading wire:target="addGarmentFromUrl" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Or select from wardrobe --}}
                    @if($wardrobeItems->count() > 0)
                        <button wire:click="$set('showWardrobeModal', true)" class="w-full py-3 border-2 border-primary/30 text-primary rounded-xl hover:bg-primary/10 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            {{ __('studio.select_wardrobe') }}
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
                            {{ __('studio.all_combined') }}
                        </p>
                    @endif

                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="garmentImages" class="mt-4 flex items-center justify-center py-4">
                        <svg class="w-6 h-6 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="ml-2 text-sm text-muted-foreground">{{ __('studio.uploading') }}</span>
                    </div>
                </div>

                {{-- Generate Button --}}
                <button
                    wire:click="generate"
                    wire:loading.attr="disabled"
                    wire:target="generate"
                    @if(!$bodyImagePreview || (count($garmentPreviews) == 0 && count($selectedWardrobeItems) == 0)) disabled @endif
                    class="w-full py-4 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3"
                >
                    <span wire:loading.remove wire:target="generate" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                        </svg>
                        {{ __('studio.generate_credit') }}
                    </span>
                    <span wire:loading wire:target="generate" class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ __('studio.queueing') }}
                    </span>
                </button>

                {{-- Success Message with Generate Another option --}}
                @if(session()->has('message'))
                    <div class="mt-3 p-3 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <p class="text-sm font-medium text-green-700 dark:text-green-300">{{ session('message') }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-2 ml-7">
                            <p class="text-xs text-green-600 dark:text-green-400">{{ __('studio.add_more_clothes') }}</p>
                            <button wire:click="clearAll" class="text-xs text-primary hover:underline font-medium">
                                {{ __('studio.start_fresh') }} &rarr;
                            </button>
                        </div>
                    </div>
                @endif

                {{-- View Queue Button --}}
                @if(count($pendingJobs) > 0)
                    <button wire:click="$set('showQueueModal', true)"
                            class="w-full mt-3 py-3 bg-secondary hover:bg-secondary/80 text-foreground font-medium rounded-xl transition-colors flex items-center justify-center gap-2 border border-border">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        {{ __('studio.view_queue') }}
                        <span class="px-2 py-0.5 bg-primary text-primary-foreground text-xs font-semibold rounded-full">{{ count($pendingJobs) }}</span>
                    </button>
                @endif
            </div>

            {{-- Right Column: Result --}}
            <div class="bg-secondary rounded-2xl p-6">
                <h3 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-primary text-primary-foreground rounded-full flex items-center justify-center text-sm">3</span>
                    {{ __('studio.step_result') }}
                </h3>
                <div class="aspect-[3/4] rounded-xl border-2 border-dashed border-border overflow-hidden relative bg-background">
                    {{-- Loading state - shows only when generate is clicked --}}
                    <div wire:loading.flex wire:target="generate" class="absolute inset-0 bg-background z-10 flex-col items-center justify-center">
                        <svg class="w-12 h-12 mb-6" style="animation: spin 1s linear infinite;" viewBox="0 0 50 50">
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" class="text-border"></circle>
                            <circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="31.4, 125.6" class="text-primary"></circle>
                        </svg>
                        <p class="text-base font-medium text-foreground text-center m-0">{{ __('studio.creating_outfit') }}</p>
                        <p class="text-sm text-muted-foreground text-center mt-1">{{ __('studio.processing_time') }}</p>
                    </div>

                    {{-- Processing State - Timeline Progress --}}
                    @if(count($pendingJobs) > 0 && !$resultImage)
                        @php
                            $currentJob = collect($pendingJobs)->first();
                            $isProcessing = $currentJob && $currentJob['status'] === 'processing';
                        @endphp
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-background p-6">
                            {{-- Timeline Progress --}}
                            <div class="w-full max-w-xs mb-8">
                                <div class="flex items-center justify-between relative">
                                    {{-- Progress Line --}}
                                    <div class="absolute top-4 left-6 right-6 h-0.5 bg-border"></div>
                                    <div class="absolute top-4 left-6 h-0.5 bg-primary transition-all duration-500" style="width: {{ $isProcessing ? '50%' : '0%' }};"></div>

                                    {{-- Step 1: Queued --}}
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ !$isProcessing ? 'bg-primary text-primary-foreground' : 'bg-primary text-primary-foreground' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-xs text-muted-foreground mt-2 font-medium">{{ __('studio.queued_stat') }}</span>
                                    </div>

                                    {{-- Step 2: Generating --}}
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $isProcessing ? 'bg-primary text-primary-foreground' : 'bg-secondary text-muted-foreground border-2 border-border' }}">
                                            @if($isProcessing)
                                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                            @else
                                                <span class="text-xs font-bold">2</span>
                                            @endif
                                        </div>
                                        <span class="text-xs mt-2 font-medium {{ $isProcessing ? 'text-primary' : 'text-muted-foreground' }}">{{ __('studio.generating_stat') }}</span>
                                    </div>

                                    {{-- Step 3: Ready --}}
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full bg-secondary text-muted-foreground border-2 border-border flex items-center justify-center">
                                            <span class="text-xs font-bold">3</span>
                                        </div>
                                        <span class="text-xs text-muted-foreground mt-2 font-medium">{{ __('studio.ready_stat') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Status Text --}}
                            <div class="text-center">
                                <p class="text-lg font-semibold text-foreground">
                                    {{ $isProcessing ? __('studio.creating_outfit') : __('studio.job_pending') }}
                                </p>
                                <p class="text-sm text-muted-foreground mt-1">
                                    {{ count($pendingJobs) }} {{ __('studio.in_queue') }}
                                </p>
                            </div>

                            {{-- Progress Bar --}}
                            @if($isProcessing)
                                <div class="w-full max-w-xs mt-6">
                                    <div class="h-1.5 bg-secondary rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full animate-pulse" style="width: 60%;"></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Start Fresh Link --}}
                            <button wire:click="clearAll" class="mt-6 text-sm text-muted-foreground hover:text-foreground transition-colors">
                                {{ __('studio.start_fresh') }}
                            </button>
                        </div>
                    @elseif($resultImage)
                        <img src="{{ $resultImage }}" alt="Try-on result" class="w-full h-full object-cover">
                        {{-- Action buttons overlay --}}
                        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/70 to-transparent">
                            <div class="grid grid-cols-4 gap-2 mb-2">
                                {{-- Save --}}
                                <button wire:click="openSaveModal" class="flex flex-col items-center gap-1 p-2 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition-colors cursor-pointer" title="{{ __('studio.save_to_outfits') }}">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span class="text-[10px] text-white">{{ __('studio.save') }}</span>
                                </button>
                                {{-- Share to Feed --}}
                                <button wire:click="openPostModal" class="flex flex-col items-center gap-1 p-2 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition-colors cursor-pointer" title="{{ __('feed.post_to_feed') }}">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span class="text-[10px] text-white">{{ __('studio.post') }}</span>
                                </button>
                                {{-- Share to Social --}}
                                <button wire:click="openShareModal" class="flex flex-col items-center gap-1 p-2 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition-colors cursor-pointer" title="{{ __('studio.share') }}">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                    </svg>
                                    <span class="text-[10px] text-white">{{ __('studio.share') }}</span>
                                </button>
                                {{-- Download --}}
                                <a href="{{ $resultImage }}" download class="flex flex-col items-center gap-1 p-2 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition-colors" title="{{ __('studio.download') }}">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    <span class="text-[10px] text-white">{{ __('studio.download') }}</span>
                                </a>
                            </div>
                            <button wire:click="clearResult" class="w-full py-2 bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-lg hover:bg-white/30 transition-colors cursor-pointer">
                                {{ __('studio.try_again') }}
                            </button>
                        </div>
                    @else
                        <div wire:loading.remove wire:target="generate" class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            <span class="text-sm font-medium">{{ __('studio.result_placeholder') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- History Section --}}
        @if($history->count() > 0)
            <div class="mt-12">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-foreground">{{ __('studio.recent_tryons') }}</h2>
                    <a href="{{ route('history') }}" class="text-sm text-primary hover:text-primary/80 font-medium flex items-center gap-1">
                        {{ __('studio.view_all') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($history as $tryOn)
                        <div class="aspect-[3/4] rounded-xl overflow-hidden bg-secondary relative group cursor-pointer">
                            <img src="{{ $tryOn->result_image_url }}" alt="Try-on result" class="w-full h-full object-cover">
                            {{-- Bottom gradient overlay with actions - always visible --}}
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/70 to-transparent">
                                <div class="grid grid-cols-4 gap-1">
                                    {{-- Save --}}
                                    <button wire:click="openSaveModalForHistory({{ $tryOn->id }})" class="flex flex-col items-center gap-0.5 p-1.5 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/40 transition-colors cursor-pointer" title="{{ __('studio.save') }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                        <span class="text-[8px] text-white">{{ __('studio.save') }}</span>
                                    </button>
                                    {{-- Post --}}
                                    <button wire:click="openPostModalForHistory({{ $tryOn->id }})" class="flex flex-col items-center gap-0.5 p-1.5 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/40 transition-colors cursor-pointer" title="{{ __('studio.post') }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span class="text-[8px] text-white">{{ __('studio.post') }}</span>
                                    </button>
                                    {{-- Share --}}
                                    <button wire:click="openShareModalForHistory({{ $tryOn->id }})" class="flex flex-col items-center gap-0.5 p-1.5 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/40 transition-colors cursor-pointer" title="{{ __('studio.share') }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                        </svg>
                                        <span class="text-[8px] text-white">{{ __('studio.share') }}</span>
                                    </button>
                                    {{-- Download --}}
                                    <a href="{{ $tryOn->result_image_url }}" download class="flex flex-col items-center gap-0.5 p-1.5 bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/40 transition-colors" title="{{ __('studio.download') }}">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span class="text-[8px] text-white">{{ __('studio.download') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Generation Queue Modal --}}
    @if($showQueueModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showQueueModal', false)">
            <div class="bg-background rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
                {{-- Header --}}
                <div class="p-5 border-b border-border flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-foreground">{{ __('studio.generation_queue') }}</h3>
                            <p class="text-xs text-muted-foreground">{{ __('studio.queue_info') }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showQueueModal', false)" class="p-2 hover:bg-secondary rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Stats Cards --}}
                <div class="p-5 border-b border-border">
                    <div class="grid grid-cols-3 gap-3">
                        {{-- Queued --}}
                        <div class="bg-secondary rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-foreground mb-1">{{ $this->queueStats['queued'] }}</div>
                            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ __('studio.queued_stat') }}</div>
                        </div>
                        {{-- Generating --}}
                        <div class="bg-secondary rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-blue-500 mb-1">{{ $this->queueStats['generating'] }}</div>
                            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ __('studio.generating_stat') }}</div>
                        </div>
                        {{-- Ready --}}
                        <div class="bg-secondary rounded-xl p-4 text-center">
                            <div class="text-3xl font-bold text-green-500 mb-1">{{ $this->queueStats['ready'] }}</div>
                            <div class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ __('studio.ready_stat') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Queue Items --}}
                <div class="p-5 max-h-72 overflow-y-auto">
                    @if(count($pendingJobs) > 0)
                        <div class="space-y-3">
                            @foreach($pendingJobs as $job)
                                <div class="flex items-center gap-3 p-3 bg-secondary/50 rounded-xl">
                                    @if($job['status'] === 'pending')
                                        <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ __('studio.job_pending') }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $job['garment_count'] }} {{ __('studio.items') }} &middot; {{ $job['created_at'] }}</p>
                                        </div>
                                        <div class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 text-xs font-medium rounded-full">
                                            {{ __('studio.queued_stat') }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ __('studio.job_processing') }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $job['garment_count'] }} {{ __('studio.items') }} &middot; {{ $job['created_at'] }}</p>
                                        </div>
                                        <div class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-full">
                                            {{ __('studio.generating_stat') }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-10">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-secondary flex items-center justify-center">
                                <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75l-5.571-3m11.142 0l4.179 2.25L12 17.25l-9.75-5.25 4.179-2.25m11.142 0l4.179 2.25L12 21.75l-9.75-5.25 4.179-2.25"/>
                                </svg>
                            </div>
                            <h4 class="text-base font-semibold text-foreground mb-1">{{ __('studio.no_generations') }}</h4>
                            <p class="text-sm text-muted-foreground max-w-xs mx-auto">{{ __('studio.add_outfits_info') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="p-4 bg-secondary/30 border-t border-border">
                    <button wire:click="$set('showQueueModal', false)" class="w-full py-2.5 bg-primary text-primary-foreground font-medium rounded-xl hover:bg-primary/90 transition-colors">
                        {{ __('app.close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Wardrobe Modal --}}
    @if($showWardrobeModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showWardrobeModal', false)">
            <div class="bg-background rounded-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">{{ __('studio.wardrobe_title') }}</h3>
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
                            <p>{{ __('studio.wardrobe_empty') }}</p>
                            <a href="{{ route('wardrobe') }}" class="text-primary hover:underline">{{ __('studio.add_to_wardrobe') }}</a>
                        </div>
                    @endif
                </div>
                <div class="p-6 border-t border-border flex justify-end gap-3">
                    <button wire:click="$set('showWardrobeModal', false)" class="px-4 py-2 text-muted-foreground hover:text-foreground">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="addFromWardrobe" class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90">
                        {{ __('studio.done') }} ({{ count($selectedWardrobeItems) }} {{ __('studio.selected') }})
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Save Outfit Modal --}}
    @if($showSaveModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeSaveModal">
            <div class="bg-background rounded-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-foreground mb-4">{{ __('outfits.save_title') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.outfit_name') }}</label>
                        <input type="text" wire:model="saveOutfitName" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground" placeholder="My awesome outfit">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.outfit_notes') }}</label>
                        <textarea wire:model="saveOutfitNotes" rows="3" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground resize-none" placeholder="Any notes about this outfit..."></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="closeSaveModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="saveToOutfits" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        {{ __('outfits.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Post to Feed Modal --}}
    @if($showPostModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closePostModal">
            <div class="bg-background rounded-2xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-foreground mb-4">{{ __('outfits.post_title') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.post_caption') }}</label>
                        <textarea wire:model="postCaption" rows="3" class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground resize-none" placeholder="{{ __('feed.caption_placeholder') }}"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">{{ __('outfits.post_visibility') }}</label>
                        <div class="flex gap-3">
                            <label class="flex-1 flex items-center gap-2 p-3 border border-border rounded-lg cursor-pointer {{ $postVisibility === 'public' ? 'border-primary bg-primary/5' : '' }}">
                                <input type="radio" wire:model="postVisibility" value="public" class="text-primary">
                                <span class="text-foreground">{{ __('outfits.post_public') }}</span>
                            </label>
                            <label class="flex-1 flex items-center gap-2 p-3 border border-border rounded-lg cursor-pointer {{ $postVisibility === 'private' ? 'border-primary bg-primary/5' : '' }}">
                                <input type="radio" wire:model="postVisibility" value="private" class="text-primary">
                                <span class="text-foreground">{{ __('outfits.post_private') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button wire:click="closePostModal" class="flex-1 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.cancel') }}
                    </button>
                    <button wire:click="postToFeed" class="flex-1 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                        {{ __('feed.post_to_feed') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Share Modal --}}
    @if($showShareModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeShareModal">
            <div class="bg-background rounded-2xl max-w-sm w-full p-6">
                <h3 class="text-lg font-bold text-foreground mb-4">{{ __('feed.share_to') }}</h3>
                <div class="grid grid-cols-3 gap-4">
                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/" target="_blank" wire:click="trackShare('instagram')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">Instagram</span>
                    </a>
                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/" target="_blank" wire:click="trackShare('facebook')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">Facebook</span>
                    </a>
                    {{-- X/Twitter --}}
                    <a href="https://twitter.com/intent/tweet" target="_blank" wire:click="trackShare('twitter')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-black flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">X</span>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/?text=Check%20out%20my%20outfit!" target="_blank" wire:click="trackShare('whatsapp')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
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
                    <a href="{{ $resultImage }}" download wire:click="trackShare('download')" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-secondary transition-colors">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ __('feed.download') }}</span>
                    </a>
                </div>
                <button wire:click="closeShareModal" class="w-full mt-4 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                    {{ __('app.close') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Corner Toast Notification --}}
    @if($showResultReady)
        <div x-data="{ show: true }"
             x-init="setTimeout(() => { show = false; $wire.set('showResultReady', false); }, 4000)"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-6 right-6 z-50 max-w-xs">
            <div class="bg-background border border-border rounded-xl shadow-lg p-4 flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-foreground">{{ __('studio.result_ready') }}</p>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ __('studio.scroll_to_see') }}</p>
                </div>
                <button @click="show = false; $wire.set('showResultReady', false)" class="flex-shrink-0 text-muted-foreground hover:text-foreground transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Get Credits Modal --}}
    @if($showCreditModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="$set('showCreditModal', false)">
            <div class="bg-background rounded-2xl max-w-md w-full overflow-hidden"
                 x-data
                 x-init="$el.querySelector('button')?.focus()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Header --}}
                <div class="p-6 text-center border-b border-border">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-primary/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-foreground mb-1">{{ __('studio.get_credits_title') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ __('studio.get_credits_subtitle') }}</p>
                </div>

                {{-- Credit Packs --}}
                <div class="p-6 space-y-3">
                    @php $packs = config('credits.packs'); @endphp

                    {{-- Quick Pack Options - Show 2 best options --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Medium Pack (Popular) --}}
                        <form action="{{ route('checkout.credits') }}" method="POST">
                            @csrf
                            <input type="hidden" name="pack" value="medium">
                            <button type="submit" class="w-full p-4 bg-primary/5 border-2 border-primary rounded-xl hover:bg-primary/10 transition-all group cursor-pointer text-left">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-2xl font-bold text-foreground">50</span>
                                    <span class="px-2 py-0.5 bg-primary text-primary-foreground text-[10px] font-semibold rounded-full uppercase">{{ __('pricing.popular') }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground mb-2">{{ __('pricing.credits') }}</div>
                                <div class="text-lg font-bold text-primary">${{ number_format($packs['medium']['price'] / 100, 2) }}</div>
                                <div class="text-[10px] text-muted-foreground">{{ $packs['medium']['per_credit'] }}</div>
                            </button>
                        </form>

                        {{-- Large Pack --}}
                        <form action="{{ route('checkout.credits') }}" method="POST">
                            @csrf
                            <input type="hidden" name="pack" value="large">
                            <button type="submit" class="w-full p-4 bg-secondary border-2 border-border rounded-xl hover:border-primary/50 transition-all group cursor-pointer text-left">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-2xl font-bold text-foreground">100</span>
                                </div>
                                <div class="text-xs text-muted-foreground mb-2">{{ __('pricing.credits') }}</div>
                                <div class="text-lg font-bold text-foreground">${{ number_format($packs['large']['price'] / 100, 2) }}</div>
                                <div class="text-[10px] text-muted-foreground">{{ $packs['large']['per_credit'] }}</div>
                            </button>
                        </form>
                    </div>

                    {{-- Subscription Promo --}}
                    <a href="{{ route('pricing') }}" class="block p-4 bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 rounded-xl hover:border-primary/40 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-foreground">{{ __('studio.subscribe_promo') }}</p>
                                <p class="text-xs text-muted-foreground">{{ __('studio.subscribe_promo_desc') }}</p>
                            </div>
                            <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                </div>

                {{-- Footer --}}
                <div class="p-6 pt-4 flex justify-center">
                    <button wire:click="$set('showCreditModal', false)" class="px-8 py-2.5 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-secondary rounded-full transition-all">
                        {{ __('studio.maybe_later') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
