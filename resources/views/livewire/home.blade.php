<div x-data="tryOnDemo()" x-init="init()">
    {{-- Hero Section --}}
    <section class="relative min-h-[90vh] flex items-center overflow-x-hidden pt-20 pb-16 bg-background">
        {{-- Background --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 -left-20 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-primary/5 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 w-full">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                {{-- Left: Text content --}}
                <div class="text-center lg:text-left order-2 lg:order-1">
                    <span class="inline-flex items-center gap-2 px-4 py-2 mb-6 sm:mb-8 text-xs font-medium tracking-widest uppercase text-muted-foreground border border-border bg-background rounded">
                        <svg class="w-3 h-3 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        {{ __('home.hero_badge') }}
                    </span>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight mb-6 sm:mb-8">
                        <span class="text-foreground">{{ __('home.hero_title_1') }}</span>
                        <br>
                        <span class="text-muted-foreground">{{ __('home.hero_title_2') }}</span>
                    </h1>

                    <p class="text-base sm:text-lg text-muted-foreground mb-6 sm:mb-8 max-w-md mx-auto lg:mx-0 leading-relaxed">
                        {{ __('home.hero_description') }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @auth
                            <a href="{{ route('studio') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                                {{ __('home.try_it_now') }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                                {{ __('home.try_it_free') }}
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-4 border border-border hover:bg-secondary text-foreground font-semibold rounded-lg transition-colors">
                                {{ __('app.sign_in') }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @endauth
                    </div>

                    {{-- Stats --}}
                    <div class="mt-10 sm:mt-12 flex items-center gap-4 sm:gap-8 justify-center lg:justify-start">
                        <div class="text-center lg:text-left">
                            <div class="text-xl sm:text-3xl font-bold text-foreground mb-1">30s</div>
                            <div class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.tryon_time') }}</div>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-border"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-xl sm:text-3xl font-bold text-foreground mb-1">1000+</div>
                            <div class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.brands') }}</div>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-border"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-xl sm:text-3xl font-bold text-foreground mb-1">Free</div>
                            <div class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.to_start') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Try-On Demo --}}
                <div class="relative order-1 lg:order-2 flex justify-center px-0 lg:px-0">
                    <div class="relative bg-card border border-border rounded-2xl shadow-xl w-full pb-4" style="max-width: min(320px, calc(100vw - 32px));">
                        {{-- Demo indicator header --}}
                        <div class="flex items-center justify-between px-4 pt-4 pb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-[10px] tracking-wider uppercase text-muted-foreground font-medium">Live Demo</span>
                            </div>
                            <span class="text-sm font-semibold text-foreground">Step <span x-text="currentStep + 1"></span>/3</span>
                        </div>

                        {{-- Step progress bar --}}
                        <div class="flex gap-1.5 px-4 pb-3">
                            <div class="flex-1 h-1 rounded-full bg-muted overflow-hidden">
                                <div class="h-full bg-foreground transition-all duration-500" :class="currentStep >= 0 ? 'w-full' : 'w-0'"></div>
                            </div>
                            <div class="flex-1 h-1 rounded-full bg-muted overflow-hidden">
                                <div class="h-full bg-foreground transition-all duration-500" :class="currentStep >= 1 ? 'w-full' : 'w-0'"></div>
                            </div>
                            <div class="flex-1 h-1 rounded-full bg-muted overflow-hidden">
                                <div class="h-full bg-foreground transition-all duration-500" :class="currentStep >= 2 ? 'w-full' : 'w-0'"></div>
                            </div>
                        </div>

                        {{-- Main Demo Image --}}
                        <div class="flex justify-center px-6">
                            <div class="relative bg-secondary rounded-xl overflow-hidden" style="width: 160px; height: 280px;">
                                <img :src="displayImage" alt="Try-on demo" class="w-full h-full object-cover transition-all duration-300"
                                     :class="imageTransitioning ? 'opacity-0 scale-105' : 'opacity-100 scale-100'">

                                {{-- Generating Overlay --}}
                                <div x-show="isGenerating"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-background/80 backdrop-blur-sm flex flex-col items-center justify-center">
                                    <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full mb-2 animate-spin"></div>
                                    <span class="text-xs font-medium">Generating...</span>
                                </div>

                                {{-- Outfit Preview overlay --}}
                                <div x-show="currentStage === 'select' && !isGenerating"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-x-2 scale-90"
                                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0 -translate-x-2 scale-90"
                                     class="absolute top-2 right-2 bg-background rounded-lg shadow-lg border-2 border-primary" style="width: 65px; height: 65px; overflow: hidden;">
                                    <img :src="currentSequence.outfit" alt="Selected outfit" class="w-full h-full object-cover">
                                    <div class="absolute bottom-0 left-0 right-0 bg-foreground/90 py-0.5 text-center">
                                        <span style="font-size: 8px;" class="text-background font-medium">Selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step Indicators at bottom --}}
                        <div class="demo-step-indicators flex items-center justify-center gap-1.5 px-4 py-4 mt-2">
                            <template x-for="(label, index) in stepLabels" :key="label">
                                <div :class="{
                                        'bg-foreground text-background': index === currentStep,
                                        'bg-secondary text-foreground': index < currentStep,
                                        'bg-secondary/50 text-muted-foreground': index > currentStep
                                     }"
                                     class="demo-step-btn flex items-center gap-1 px-2 py-1.5 rounded-full transition-all duration-300">
                                    <svg x-show="index === 0" class="demo-step-icon w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    <svg x-show="index === 1" class="demo-step-icon w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <svg x-show="index === 2" class="demo-step-icon w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                    <span class="demo-step-label text-[9px] font-medium whitespace-nowrap" x-text="label"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 hidden sm:block">
            <button onclick="document.getElementById('features').scrollIntoView({behavior: 'smooth'})"
                    class="flex flex-col items-center gap-2 text-muted-foreground hover:text-foreground transition-colors">
                <span class="text-[10px] tracking-widest uppercase">{{ __('home.scroll_to_learn') }}</span>
                <div class="w-6 h-10 border border-border rounded-full flex justify-center pt-2">
                    <div class="w-1 h-2 bg-muted-foreground rounded-full animate-bounce"></div>
                </div>
            </button>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-12 sm:py-20 bg-secondary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-16">
                <h2 class="text-2xl sm:text-3xl font-bold text-foreground">{{ __('home.how_it_works') }}</h2>
                <p class="mt-3 text-muted-foreground max-w-2xl mx-auto">{{ __('home.how_it_works_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                {{-- Step 1 --}}
                <div class="text-center p-6 rounded-2xl bg-background hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('home.step1_title') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ __('home.step1_description') }}</p>
                </div>

                {{-- Step 2 --}}
                <div class="text-center p-6 rounded-2xl bg-background hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('home.step2_title') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ __('home.step2_description') }}</p>
                </div>

                {{-- Step 3 --}}
                <div class="text-center p-6 rounded-2xl bg-background hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('home.step3_title') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ __('home.step3_description') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="py-12 sm:py-16 bg-background">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center">
                <div class="p-4">
                    <div class="text-2xl sm:text-4xl font-bold text-foreground">50K+</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ __('home.happy_users') }}</div>
                </div>
                <div class="p-4">
                    <div class="text-2xl sm:text-4xl font-bold text-foreground">1M+</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ __('home.tryons_done') }}</div>
                </div>
                <div class="p-4">
                    <div class="text-2xl sm:text-4xl font-bold text-foreground">98%</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ __('home.accuracy_rate') }}</div>
                </div>
                <div class="p-4">
                    <div class="text-2xl sm:text-4xl font-bold text-foreground">30s</div>
                    <div class="text-sm text-muted-foreground mt-1">{{ __('home.avg_processing') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-12 sm:py-20 bg-primary">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-primary-foreground mb-4">
                {{ __('home.cta_title') }}
            </h2>
            <p class="text-primary-foreground/80 mb-6 sm:mb-8 max-w-xl mx-auto">
                {{ __('home.cta_description') }}
            </p>
            @auth
                <a href="{{ route('studio') }}" class="inline-flex items-center px-6 sm:px-8 py-3 sm:py-4 bg-background hover:bg-secondary text-foreground font-semibold rounded-lg shadow-lg transition-colors">
                    {{ __('home.start_creating') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center px-6 sm:px-8 py-3 sm:py-4 bg-background hover:bg-secondary text-foreground font-semibold rounded-lg shadow-lg transition-colors">
                    {{ __('home.start_free_trial') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            @endauth
        </div>
    </section>

    {{-- Alpine.js Try-On Demo Script (matches TryOnDemo.tsx exactly) --}}
    <script>
    function tryOnDemo() {
        return {
            currentDemo: 0,
            currentStep: 0,
            isGenerating: false,
            imageTransitioning: false,
            isMounted: true,

            // Demo sequences - exactly like Hero.tsx (using outfit2 where available)
            demos: [
                {
                    base: '/images/demo/demo-asian-female-base.jpg',
                    outfit: '/images/demo/demo-asian-female-outfit2.jpg',
                    result: '/images/demo/demo-asian-female-result.jpg',
                },
                {
                    base: '/images/demo/demo-black-male-base.jpg',
                    outfit: '/images/demo/demo-black-male-outfit2.jpg',
                    result: '/images/demo/demo-black-male-result.jpg',
                },
                {
                    base: '/images/demo/demo-indian-female-base.jpg',
                    outfit: '/images/demo/demo-indian-female-outfit1.jpg',
                    result: '/images/demo/demo-indian-female-result.jpg',
                },
                {
                    base: '/images/demo/demo-white-male-base.jpg',
                    outfit: '/images/demo/demo-white-male-outfit1.jpg',
                    result: '/images/demo/demo-white-male-result.jpg',
                },
                {
                    base: '/images/demo/demo-black-female-base.jpg',
                    outfit: '/images/demo/demo-black-female-outfit1.jpg',
                    result: '/images/demo/demo-black-female-result.jpg',
                },
                {
                    base: '/images/demo/demo-latina-female-base.jpg',
                    outfit: '/images/demo/demo-latina-female-outfit2.jpg',
                    result: '/images/demo/demo-latina-female-result.jpg',
                },
            ],

            stepLabels: ['Upload Photo', 'Select Outfit', 'Try-On Complete!'],
            stepStages: ['upload', 'select', 'result'],

            get currentSequence() {
                return this.demos[this.currentDemo];
            },

            get currentStage() {
                return this.stepStages[this.currentStep];
            },

            get displayImage() {
                if (this.currentStage === 'upload' || this.currentStage === 'select') {
                    return this.currentSequence.base;
                }
                return this.currentSequence.result;
            },

            init() {
                this.isMounted = true;
                this.runSequence();
            },

            async runSequence() {
                if (!this.isMounted) return;

                // Reset state for new demo
                this.isGenerating = false;
                this.currentStep = 0;

                // Step 0: Upload (show for 2.5s)
                await this.sleep(2500);
                if (!this.isMounted) return;

                // Step 1: Select outfit (show for 2s)
                this.currentStep = 1;
                await this.sleep(2000);
                if (!this.isMounted) return;

                // Generating animation (show for 1.5s)
                this.isGenerating = true;
                await this.sleep(1500);
                if (!this.isMounted) return;

                // Step 2: Result (show for 3s)
                this.isGenerating = false;
                this.imageTransitioning = true;
                await this.sleep(50);
                this.currentStep = 2;
                this.imageTransitioning = false;
                await this.sleep(3000);
                if (!this.isMounted) return;

                // Move to next demo
                this.currentDemo = (this.currentDemo + 1) % this.demos.length;

                // Run next sequence
                this.runSequence();
            },

            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },

            destroy() {
                this.isMounted = false;
            }
        };
    }
    </script>
</div>
