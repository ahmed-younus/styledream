<div class="pt-20">
    {{-- Hero Section --}}
    <section class="py-16 sm:py-24 bg-background">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-foreground mb-6">
                About StyleDream
            </h1>
            <p class="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto">
                We're on a mission to transform the way you shop for clothes online with AI-powered virtual try-ons.
            </p>
        </div>
    </section>

    {{-- Mission Section --}}
    <section class="py-16 bg-secondary">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-foreground mb-6">Our Mission</h2>
                    <p class="text-muted-foreground mb-4">
                        At StyleDream, we believe everyone deserves to know how clothes will look on them before making a purchase. No more guessing, no more returns, no more disappointment.
                    </p>
                    <p class="text-muted-foreground">
                        Using cutting-edge AI technology, we've created a virtual try-on experience that's fast, accurate, and incredibly easy to use. Simply upload your photo, choose any clothing item, and see yourself wearing it in seconds.
                    </p>
                </div>
                <div class="flex justify-center">
                    <div class="w-64 h-64 bg-primary/10 rounded-full flex items-center justify-center">
                        <svg class="w-32 h-32 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Values Section --}}
    <section class="py-16 bg-background">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <h2 class="text-3xl font-bold text-foreground text-center mb-12">Our Values</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Innovation --}}
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">Innovation</h3>
                    <p class="text-muted-foreground">We push the boundaries of AI technology to deliver the most realistic virtual try-on experience.</p>
                </div>

                {{-- Simplicity --}}
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">Simplicity</h3>
                    <p class="text-muted-foreground">Complex technology, simple experience. Anyone can use StyleDream in seconds.</p>
                </div>

                {{-- Trust --}}
                <div class="text-center p-6">
                    <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-foreground mb-2">Privacy & Trust</h3>
                    <p class="text-muted-foreground">Your privacy matters. We never share your photos and delete them when you ask.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-16 bg-primary">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-primary-foreground mb-4">
                Ready to transform your shopping experience?
            </h2>
            <p class="text-primary-foreground/80 mb-8">
                Join thousands of users who shop smarter with StyleDream.
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-background hover:bg-secondary text-foreground font-semibold rounded-lg transition-colors">
                Get Started Free
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </section>
</div>
