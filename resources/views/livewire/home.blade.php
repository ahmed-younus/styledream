<div x-data="heroCarousel()" x-init="init()">
    {{-- Hero Section --}}
    <section class="relative min-h-[90vh] flex items-center overflow-hidden pt-20 pb-16 bg-background">
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
                    <div class="mt-10 sm:mt-12 flex items-center gap-6 sm:gap-8 justify-center lg:justify-start">
                        <div class="text-center lg:text-left">
                            <div class="text-2xl sm:text-3xl font-bold text-foreground mb-1">30s</div>
                            <div class="text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.tryon_time') }}</div>
                        </div>
                        <div class="w-px h-10 bg-border"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-2xl sm:text-3xl font-bold text-foreground mb-1">1000+</div>
                            <div class="text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.brands') }}</div>
                        </div>
                        <div class="w-px h-10 bg-border"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-2xl sm:text-3xl font-bold text-foreground mb-1">Free</div>
                            <div class="text-xs text-muted-foreground uppercase tracking-wider">{{ __('home.to_start') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Try-On Demo Carousel --}}
                <div class="relative order-1 lg:order-2">
                    <div class="relative aspect-[3/4] max-w-[280px] sm:max-w-[320px] mx-auto">
                        {{-- Main preview container --}}
                        <div class="relative h-full bg-secondary overflow-hidden shadow-2xl rounded-2xl border border-border">
                            {{-- Shimmer effect during transition --}}
                            <div x-show="isTransitioning"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 class="absolute inset-0 z-20 bg-gradient-to-r from-transparent via-background/30 to-transparent animate-shimmer"></div>

                            {{-- Model images --}}
                            <template x-for="(model, modelIndex) in models" :key="model.modelId">
                                <template x-for="(outfit, outfitIndex) in model.outfits" :key="outfit.id">
                                    <div x-show="currentModelIndex === modelIndex && currentOutfitIndex === outfitIndex"
                                         x-transition:enter="transition ease-out duration-400"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         class="absolute inset-0">
                                        <img :src="outfit.image"
                                             :alt="model.modelName + ' - ' + outfit.label"
                                             loading="eager"
                                             class="w-full h-full object-cover object-top">

                                        {{-- Clothing change indicator --}}
                                        <div x-show="!outfit.isBase" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                            <div class="w-24 h-24 border-2 border-foreground/20 rounded-full animate-ping-slow"></div>
                                        </div>
                                    </div>
                                </template>
                            </template>

                            {{-- Try-on indicator (top left) --}}
                            <div class="absolute top-3 left-3 flex items-center gap-1.5 px-2 py-1 bg-background/95 backdrop-blur-sm rounded-full shadow-sm">
                                <div :class="isTransitioning ? 'animate-pulse bg-yellow-500' : 'bg-green-500'"
                                     class="w-2 h-2 rounded-full transition-colors"></div>
                                <span class="text-[9px] font-medium text-muted-foreground" x-text="isTransitioning ? '{{ __('home.trying_on') }}' : '{{ __('home.live_demo') }}'"></span>
                            </div>

                            {{-- Style label (top right) --}}
                            <div class="absolute top-3 right-3 px-3 py-1.5 bg-foreground/90 text-background rounded-full">
                                <span class="text-[10px] font-medium tracking-wide" x-text="currentOutfit.label"></span>
                            </div>

                            {{-- Product tags (left side) --}}
                            <div class="absolute left-3 bottom-20 space-y-1">
                                <template x-for="(item, i) in currentOutfit.items" :key="i">
                                    <div class="flex items-center gap-2 px-2 py-1 bg-background/95 backdrop-blur-sm rounded shadow-sm text-left max-w-[140px]"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-x-4"
                                         x-transition:enter-end="opacity-100 translate-x-0">
                                        <div class="min-w-0">
                                            <p class="text-[8px] text-muted-foreground truncate" x-text="item.retailer"></p>
                                            <p class="text-[9px] text-foreground font-medium truncate" x-text="item.name"></p>
                                            <p class="text-[9px] font-bold text-primary" x-text="item.price"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Total price bar --}}
                            <div x-show="!currentOutfit.isBase"
                                 x-transition:enter="transition ease-out duration-300 delay-200"
                                 x-transition:enter-start="opacity-0 translate-y-4"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute bottom-10 left-3 right-3 px-3 py-2 bg-foreground/90 text-background rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] tracking-wide uppercase">{{ __('home.outfit_total') }}</span>
                                    <span class="text-sm font-bold" x-text="'$' + totalPrice"></span>
                                </div>
                            </div>

                            {{-- Progress dots --}}
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                                <template x-for="(_, i) in totalSlides" :key="i">
                                    <button @click="goToSlide(i)"
                                            :class="i === currentSlideIndex ? 'bg-background w-4' : 'bg-background/40 w-1.5 hover:bg-background/60'"
                                            class="h-1.5 rounded-full transition-all"></button>
                                </template>
                            </div>
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

    {{-- Alpine.js Carousel Script --}}
    <script>
    const TRANSLATIONS = {
        yourPhoto: '{{ __('home.your_photo') }}',
        yourWardrobe: '{{ __('home.your_wardrobe') }}'
    };

    function heroCarousel() {
        return {
            currentModelIndex: 0,
            currentOutfitIndex: 0,
            isTransitioning: false,
            intervalId: null,

            models: [
                {
                    modelId: 'black-male',
                    modelName: 'Marcus',
                    outfits: [
                        {
                            id: 0,
                            image: '/images/demo/demo-black-male-base.jpg',
                            label: TRANSLATIONS.yourPhoto,
                            items: [
                                { name: 'White T-Shirt', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'Light Jeans', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'White Sneakers', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                            ],
                            isBase: true,
                        },
                        {
                            id: 1,
                            image: '/images/demo/demo-black-male-outfit1.jpg',
                            label: 'Streetwear',
                            items: [
                                { name: 'Leather Jacket', retailer: 'AllSaints', price: '£349' },
                                { name: 'Grey Hoodie', retailer: 'Nike', price: '£65' },
                                { name: 'Dark Jeans', retailer: "Levi's", price: '£95' },
                            ],
                            isBase: false,
                        },
                        {
                            id: 2,
                            image: '/images/demo/demo-black-male-outfit2.jpg',
                            label: 'Smart Casual',
                            items: [
                                { name: 'Navy Suit', retailer: 'Reiss', price: '£495' },
                                { name: 'White Shirt', retailer: 'Charles Tyrwhitt', price: '£70' },
                                { name: 'Leather Loafers', retailer: 'Grenson', price: '£195' },
                            ],
                            isBase: false,
                        },
                    ],
                },
                {
                    modelId: 'asian-female',
                    modelName: 'Yuki',
                    outfits: [
                        {
                            id: 0,
                            image: '/images/demo/demo-asian-female-base.jpg',
                            label: TRANSLATIONS.yourPhoto,
                            items: [
                                { name: 'White Blouse', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'Beige Trousers', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'Nude Heels', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                            ],
                            isBase: true,
                        },
                        {
                            id: 1,
                            image: '/images/demo/demo-asian-female-outfit1.jpg',
                            label: 'Evening Glam',
                            items: [
                                { name: 'Black Midi Dress', retailer: 'Reformation', price: '£248' },
                                { name: 'Gold Necklace', retailer: 'Monica Vinader', price: '£125' },
                                { name: 'Strappy Heels', retailer: 'Stuart Weitzman', price: '£350' },
                            ],
                            isBase: false,
                        },
                        {
                            id: 2,
                            image: '/images/demo/demo-asian-female-outfit2.jpg',
                            label: 'Cozy Chic',
                            items: [
                                { name: 'Cable Knit Sweater', retailer: '& Other Stories', price: '£89' },
                                { name: 'High-Rise Jeans', retailer: 'Agolde', price: '£245' },
                                { name: 'White Trainers', retailer: 'Veja', price: '£120' },
                            ],
                            isBase: false,
                        },
                    ],
                },
                {
                    modelId: 'latina-female',
                    modelName: 'Sofia',
                    outfits: [
                        {
                            id: 0,
                            image: '/images/demo/demo-latina-female-base.jpg',
                            label: TRANSLATIONS.yourPhoto,
                            items: [
                                { name: 'Grey Crop Top', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'Black Leggings', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                                { name: 'Training Shoes', retailer: TRANSLATIONS.yourWardrobe, price: '—' },
                            ],
                            isBase: true,
                        },
                        {
                            id: 1,
                            image: '/images/demo/demo-latina-female-outfit1.jpg',
                            label: 'Active Vibes',
                            items: [
                                { name: 'Geometric Sports Bra', retailer: 'Nike', price: '£45' },
                                { name: 'Matching Leggings', retailer: 'Nike', price: '£65' },
                                { name: 'Air Max 90', retailer: 'Nike', price: '£130' },
                            ],
                            isBase: false,
                        },
                        {
                            id: 2,
                            image: '/images/demo/demo-latina-female-outfit2.jpg',
                            label: 'Boss Babe',
                            items: [
                                { name: 'Cropped Blazer', retailer: 'Zara', price: '£69' },
                                { name: 'Silk Blouse', retailer: 'Massimo Dutti', price: '£89' },
                                { name: 'Tailored Trousers', retailer: 'Arket', price: '£95' },
                            ],
                            isBase: false,
                        },
                    ],
                },
            ],

            get currentModel() {
                return this.models[this.currentModelIndex];
            },

            get currentOutfit() {
                return this.currentModel.outfits[this.currentOutfitIndex];
            },

            get totalSlides() {
                return this.models.reduce((acc, m) => acc + m.outfits.length, 0);
            },

            get currentSlideIndex() {
                return this.models.slice(0, this.currentModelIndex).reduce((acc, m) => acc + m.outfits.length, 0) + this.currentOutfitIndex;
            },

            get totalPrice() {
                return this.currentOutfit.items.reduce((sum, item) => {
                    const price = parseInt(item.price.replace(/[^0-9]/g, '')) || 0;
                    return sum + price;
                }, 0);
            },

            init() {
                this.startAutoPlay();
            },

            startAutoPlay() {
                this.intervalId = setInterval(() => {
                    this.nextSlide();
                }, 3000);
            },

            stopAutoPlay() {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
            },

            nextSlide() {
                this.isTransitioning = true;

                setTimeout(() => {
                    const nextOutfitIndex = this.currentOutfitIndex + 1;

                    if (nextOutfitIndex >= this.currentModel.outfits.length) {
                        this.currentModelIndex = (this.currentModelIndex + 1) % this.models.length;
                        this.currentOutfitIndex = 0;
                    } else {
                        this.currentOutfitIndex = nextOutfitIndex;
                    }

                    this.isTransitioning = false;
                }, 400);
            },

            goToSlide(slideIndex) {
                this.stopAutoPlay();

                let accumulated = 0;
                for (let modelIdx = 0; modelIdx < this.models.length; modelIdx++) {
                    const model = this.models[modelIdx];
                    if (slideIndex < accumulated + model.outfits.length) {
                        this.currentModelIndex = modelIdx;
                        this.currentOutfitIndex = slideIndex - accumulated;
                        break;
                    }
                    accumulated += model.outfits.length;
                }

                this.startAutoPlay();
            }
        };
    }
    </script>
</div>
