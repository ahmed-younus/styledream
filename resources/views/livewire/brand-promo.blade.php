<div class="min-h-screen bg-background">
    {{-- Hero Section --}}
    <section class="relative pt-24 pb-16 px-4 bg-gradient-to-b from-primary/5 to-background overflow-hidden">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-6">
                {{ __('brands.title') }}
            </h1>
            <p class="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
                {{ __('brands.subtitle') }}
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#register" class="px-8 py-3 bg-primary text-primary-foreground rounded-full font-medium hover:bg-primary/90 transition-colors">
                    {{ __('brands.hero_cta') }}
                </a>
                <a href="#features" class="px-8 py-3 border border-border text-foreground rounded-full font-medium hover:bg-secondary transition-colors">
                    {{ __('brands.hero_secondary') }}
                </a>
            </div>
        </div>

        {{-- Decorative elements --}}
        <div class="absolute top-20 left-10 w-64 h-64 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
    </section>

    {{-- Stats Section --}}
    <section class="py-12 px-4 border-y border-border">
        <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-bold text-primary mb-2">100K+</p>
                <p class="text-muted-foreground">{{ __('brands.stat_users') }}</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-bold text-primary mb-2">50K+</p>
                <p class="text-muted-foreground">{{ __('brands.stat_tryons') }}</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-bold text-primary mb-2">200+</p>
                <p class="text-muted-foreground">{{ __('brands.stat_brands') }}</p>
            </div>
            <div class="text-center">
                <p class="text-3xl md:text-4xl font-bold text-primary mb-2">+35%</p>
                <p class="text-muted-foreground">{{ __('brands.stat_conversion') }}</p>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-bold text-foreground text-center mb-12">
                {{ __('brands.features_title') }}
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1: AI Try-On --}}
                <div class="bg-card p-6 rounded-2xl border border-border">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_tryon_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_tryon_desc') }}</p>
                </div>

                {{-- Feature 2: Direct Links --}}
                <div class="bg-card p-6 rounded-2xl border border-border">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_links_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_links_desc') }}</p>
                </div>

                {{-- Feature 3: Alerts --}}
                <div class="bg-card p-6 rounded-2xl border border-border">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_alerts_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_alerts_desc') }}</p>
                </div>

                {{-- Feature 4: Sponsored Try-Ons --}}
                <div class="bg-card p-6 rounded-2xl border border-primary/30 relative overflow-hidden">
                    <div class="absolute top-2 right-2 px-2 py-1 bg-primary text-primary-foreground text-xs rounded font-medium">USP</div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_sponsored_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_sponsored_desc') }}</p>
                </div>

                {{-- Feature 5: Data & Insights --}}
                <div class="bg-card p-6 rounded-2xl border border-primary/30 relative overflow-hidden">
                    <div class="absolute top-2 right-2 px-2 py-1 bg-primary text-primary-foreground text-xs rounded font-medium">USP</div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_data_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_data_desc') }}</p>
                </div>

                {{-- Feature 6: Low Risk --}}
                <div class="bg-card p-6 rounded-2xl border border-primary/30 relative overflow-hidden">
                    <div class="absolute top-2 right-2 px-2 py-1 bg-primary text-primary-foreground text-xs rounded font-medium">USP</div>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">{{ __('brands.feature_risk_title') }}</h3>
                    <p class="text-muted-foreground">{{ __('brands.feature_risk_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Registration Form Section --}}
    <section id="register" class="py-20 px-4 bg-secondary/30">
        <div class="max-w-xl mx-auto">
            @if($submitted)
                {{-- Success Message --}}
                <div class="bg-card p-8 rounded-2xl border border-border text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground mb-4">{{ __('brands.success_title') }}</h3>
                    <p class="text-muted-foreground mb-6">{{ __('brands.success_message') }}</p>
                    <button wire:click="resetForm" class="px-6 py-2 border border-border rounded-lg text-foreground hover:bg-secondary">
                        {{ __('app.back') }}
                    </button>
                </div>
            @else
                {{-- Form --}}
                <div class="bg-card p-8 rounded-2xl border border-border">
                    <h3 class="text-2xl font-bold text-foreground mb-2 text-center">{{ __('brands.form_title') }}</h3>
                    <p class="text-muted-foreground mb-8 text-center">{{ __('brands.form_subtitle') }}</p>

                    <form wire:submit="submit" class="space-y-6">
                        {{-- Brand Name --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.brand_name') }} *</label>
                            <input
                                type="text"
                                wire:model="brandName"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                            @error('brandName') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
                        </div>

                        {{-- Website --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.website') }} *</label>
                            <input
                                type="url"
                                wire:model="website"
                                placeholder="https://"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                            @error('website') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
                        </div>

                        {{-- Contact Email --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.contact_email') }} *</label>
                            <input
                                type="email"
                                wire:model="contactEmail"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent"
                                required
                            >
                            @error('contactEmail') <span class="text-sm text-destructive">{{ $message }}</span> @enderror
                        </div>

                        {{-- Contact Name --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.contact_name') }}</label>
                            <input
                                type="text"
                                wire:model="contactName"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.phone') }}</label>
                            <input
                                type="tel"
                                wire:model="phone"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent"
                            >
                        </div>

                        {{-- Message --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">{{ __('brands.message') }}</label>
                            <textarea
                                wire:model="message"
                                rows="4"
                                class="w-full px-4 py-3 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                            ></textarea>
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="w-full py-3 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors disabled:opacity-50"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>{{ __('brands.submit') }}</span>
                            <span wire:loading>{{ __('brands.submitting') }}</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</div>
