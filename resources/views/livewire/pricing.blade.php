<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-4">Simple, Transparent Pricing</h1>
            <p class="text-muted-foreground max-w-xl mx-auto">Choose the plan that works for you. Start free and upgrade anytime.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            {{-- Free Plan --}}
            <div class="bg-secondary rounded-2xl p-6 border border-border">
                <h3 class="text-lg font-bold text-foreground mb-2">Free</h3>
                <div class="mb-4">
                    <span class="text-4xl font-bold text-foreground">$0</span>
                    <span class="text-muted-foreground">/month</span>
                </div>
                <p class="text-sm text-muted-foreground mb-6">Perfect for trying out StyleDream</p>
                <ul class="space-y-3 mb-6">
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        5 credits on signup
                    </li>
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        1 free credit daily
                    </li>
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Basic AI try-on
                    </li>
                </ul>
                @auth
                    <span class="block w-full py-3 text-center bg-secondary text-foreground font-medium rounded-lg border border-border">Current Plan</span>
                @else
                    <a href="{{ route('register') }}" class="block w-full py-3 text-center bg-secondary text-foreground font-medium rounded-lg border border-border hover:bg-background transition-colors">Get Started</a>
                @endauth
            </div>

            {{-- Pro Plan --}}
            <div class="bg-primary rounded-2xl p-6 text-primary-foreground relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-background text-foreground text-xs font-bold rounded-full">POPULAR</div>
                <h3 class="text-lg font-bold mb-2">Pro</h3>
                <div class="mb-4">
                    <span class="text-4xl font-bold">$9.99</span>
                    <span class="text-primary-foreground/70">/month</span>
                </div>
                <p class="text-sm text-primary-foreground/80 mb-6">For fashion enthusiasts</p>
                <ul class="space-y-3 mb-6">
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        100 credits/month
                    </li>
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        HD quality results
                    </li>
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Priority processing
                    </li>
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Unlimited wardrobe
                    </li>
                </ul>
                <button class="w-full py-3 bg-background text-foreground font-semibold rounded-lg hover:bg-secondary transition-colors">Upgrade to Pro</button>
            </div>

            {{-- Premium Plan --}}
            <div class="bg-secondary rounded-2xl p-6 border border-border">
                <h3 class="text-lg font-bold text-foreground mb-2">Premium</h3>
                <div class="mb-4">
                    <span class="text-4xl font-bold text-foreground">$24.99</span>
                    <span class="text-muted-foreground">/month</span>
                </div>
                <p class="text-sm text-muted-foreground mb-6">For power users</p>
                <ul class="space-y-3 mb-6">
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Unlimited credits
                    </li>
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        4K quality results
                    </li>
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        API access
                    </li>
                    <li class="flex items-center gap-2 text-sm text-foreground">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Priority support
                    </li>
                </ul>
                <button class="w-full py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors">Go Premium</button>
            </div>
        </div>
    </div>
</div>
