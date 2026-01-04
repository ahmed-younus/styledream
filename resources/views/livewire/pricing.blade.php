<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-5xl mx-auto">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-500 text-center">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 rounded-xl text-destructive text-center">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-500 text-center">
                {{ session('info') }}
            </div>
        @endif

        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-4">{{ __('pricing.title') }}</h1>
            <p class="text-muted-foreground max-w-xl mx-auto">{{ __('pricing.subtitle') }}</p>

            {{-- Currency Selector --}}
            <div class="flex items-center justify-center gap-2 mt-6">
                @foreach($currencies as $code => $currencyData)
                    <button wire:click="setCurrency('{{ $code }}')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ $currency === $code ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground hover:bg-secondary/80' }}">
                        {{ $currencyData['symbol'] }} {{ strtoupper($code) }}
                    </button>
                @endforeach
            </div>

            {{-- Payment Methods Badge --}}
            <div class="flex items-center justify-center gap-4 mt-6">
                <span class="text-sm text-muted-foreground">{{ __('pricing.we_accept') }}</span>
                <div class="flex items-center gap-2">
                    {{-- Visa --}}
                    <div class="h-8 px-3 rounded flex items-center justify-center" style="background-color: #1A1F71;">
                        <span class="text-white font-bold text-sm italic">VISA</span>
                    </div>
                    {{-- Mastercard --}}
                    <div class="h-8 px-2 bg-white rounded flex items-center justify-center border border-gray-200">
                        <div class="flex" style="margin-left: -4px;">
                            <div class="w-5 h-5 rounded-full" style="background-color: #EB001B;"></div>
                            <div class="w-5 h-5 rounded-full" style="background-color: #F79E1B; margin-left: -8px;"></div>
                        </div>
                    </div>
                    {{-- Apple Pay --}}
                    <div class="h-8 px-3 bg-black rounded flex items-center justify-center gap-1">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                        <span class="text-white text-xs font-medium">Pay</span>
                    </div>
                    {{-- Google Pay --}}
                    <div class="h-8 px-3 bg-white rounded flex items-center justify-center border border-gray-200 gap-1">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Pay</span>
                    </div>
                </div>
            </div>
        </div>

        @php
            $activeSubscription = auth()->check() ? auth()->user()->activeSubscription() : null;
            $currentPlan = $activeSubscription ? $activeSubscription->plan : 'free';
            $planCount = count($plans);
            $gridCols = $planCount <= 3 ? 'md:grid-cols-' . $planCount : 'md:grid-cols-4';
        @endphp

        <div class="grid {{ $planCount === 1 ? 'md:grid-cols-1 max-w-md mx-auto' : ($planCount === 2 ? 'md:grid-cols-2 max-w-2xl mx-auto' : ($planCount === 3 ? 'md:grid-cols-3' : ($planCount === 4 ? 'md:grid-cols-4' : 'md:grid-cols-3 lg:grid-cols-4'))) }} gap-6">
            @foreach($plans as $planKey => $plan)
                @php
                    $isPopular = ($plan['badge'] ?? '') === 'Popular' || ($plan['badge'] ?? '') === 'popular';
                    $isBestValue = ($plan['badge'] ?? '') === 'Best Value' || ($plan['badge'] ?? '') === 'best_value';
                    $isCurrentPlan = $currentPlan === $planKey;
                    $isFree = ($plan['price'] ?? 0) == 0;
                    $features = $plan['features'] ?? [];
                @endphp

                @if($isPopular)
                    {{-- Popular Plan - Primary colored card --}}
                    <div class="bg-primary rounded-2xl p-6 text-primary-foreground relative {{ $isCurrentPlan ? 'ring-4 ring-yellow-400' : '' }}">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-background text-foreground text-xs font-bold rounded-full">{{ $plan['badge'] }}</div>
                        <h3 class="text-lg font-bold mb-2">{{ $plan['name'] }}</h3>
                        <div class="mb-4">
                            @if($isFree)
                                <span class="text-4xl font-bold">{{ $currencySymbol }}0</span>
                            @else
                                <span class="text-4xl font-bold">{{ $currencySymbol }}{{ number_format($plan['price'] / 100, 2) }}</span>
                            @endif
                            <span class="text-primary-foreground/70">{{ __('pricing.per_month') }}</span>
                        </div>
                        @if(!empty($plan['description']))
                            <p class="text-sm text-primary-foreground/80 mb-6">{{ $plan['description'] }}</p>
                        @endif
                        <ul class="space-y-3 mb-6">
                            @foreach($features as $feature)
                                <li class="flex items-center gap-2 text-sm">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        @auth
                            @if($isCurrentPlan)
                                <a href="{{ route('billing') }}" class="block w-full py-3 text-center bg-background text-foreground font-semibold rounded-lg hover:bg-secondary transition-colors">{{ __('pricing.manage_subscription') }}</a>
                            @elseif($isFree)
                                <span class="block w-full py-3 text-center bg-background/50 text-foreground/70 font-medium rounded-lg">{{ $plan['button_text'] ?? __('pricing.free_tier') }}</span>
                            @else
                                <form action="{{ route('checkout.subscription') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $planKey }}">
                                    <input type="hidden" name="currency" value="{{ $currency }}">
                                    <button type="submit" class="w-full py-3 bg-background text-foreground font-semibold rounded-lg hover:bg-secondary transition-colors cursor-pointer">{{ $plan['button_text'] ?? __('pricing.upgrade_now') }}</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block w-full py-3 text-center bg-background text-foreground font-semibold rounded-lg hover:bg-secondary transition-colors">{{ __('pricing.login_to_upgrade') }}</a>
                        @endauth
                    </div>
                @else
                    {{-- Regular Plan - Secondary colored card --}}
                    <div class="bg-secondary rounded-2xl p-6 border border-border relative {{ $isCurrentPlan ? 'ring-2 ring-primary' : '' }} {{ $isBestValue ? 'ring-2 ring-green-500' : '' }}">
                        @if(!empty($plan['badge']) && !$isPopular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 {{ $isBestValue ? 'bg-green-500 text-white' : 'bg-primary text-primary-foreground' }} text-xs font-bold rounded-full">{{ $plan['badge'] }}</div>
                        @endif
                        <h3 class="text-lg font-bold text-foreground mb-2">{{ $plan['name'] }}</h3>
                        <div class="mb-4">
                            @if($isFree)
                                <span class="text-4xl font-bold text-foreground">{{ $currencySymbol }}0</span>
                            @else
                                <span class="text-4xl font-bold text-foreground">{{ $currencySymbol }}{{ number_format($plan['price'] / 100, 2) }}</span>
                            @endif
                            <span class="text-muted-foreground">{{ __('pricing.per_month') }}</span>
                        </div>
                        @if(!empty($plan['description']))
                            <p class="text-sm text-muted-foreground mb-6">{{ $plan['description'] }}</p>
                        @endif
                        <ul class="space-y-3 mb-6">
                            @foreach($features as $feature)
                                <li class="flex items-center gap-2 text-sm text-foreground">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        @auth
                            @if($isCurrentPlan && $isFree)
                                <span class="block w-full py-3 text-center bg-primary/10 text-primary font-medium rounded-lg border border-primary">{{ __('pricing.current_plan') }}</span>
                            @elseif($isCurrentPlan)
                                <a href="{{ route('billing') }}" class="block w-full py-3 text-center bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors">{{ __('pricing.manage_subscription') }}</a>
                            @elseif($isFree)
                                <span class="block w-full py-3 text-center bg-secondary text-muted-foreground font-medium rounded-lg border border-border">{{ $plan['button_text'] ?? __('pricing.free_tier') }}</span>
                            @else
                                <form action="{{ route('checkout.subscription') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $planKey }}">
                                    <input type="hidden" name="currency" value="{{ $currency }}">
                                    <button type="submit" class="w-full py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors cursor-pointer">{{ $plan['button_text'] ?? __('pricing.upgrade_now') }}</button>
                                </form>
                            @endif
                        @else
                            @if($isFree)
                                <a href="{{ route('register') }}" class="block w-full py-3 text-center bg-secondary text-foreground font-medium rounded-lg border border-border hover:bg-background transition-colors">{{ __('pricing.get_started') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="block w-full py-3 text-center bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors">{{ __('pricing.login_to_upgrade') }}</a>
                            @endif
                        @endauth
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Active Subscription Info --}}
        @auth
            @if($activeSubscription)
                <div class="mt-8 p-4 bg-primary/5 border border-primary/20 rounded-xl">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-foreground">{{ __('pricing.active_subscription', ['plan' => ucfirst($activeSubscription->plan)]) }}</p>
                                <p class="text-sm text-muted-foreground">{{ __('pricing.renews_on', ['date' => $activeSubscription->current_period_end->format('M d, Y')]) }}</p>
                            </div>
                        </div>
                        <a href="{{ route('billing') }}" class="px-4 py-2 bg-secondary text-foreground text-sm font-medium rounded-lg hover:bg-secondary/80 transition-colors">
                            {{ __('pricing.manage_billing') }}
                        </a>
                    </div>
                </div>
            @endif
        @endauth

        {{-- Credit Packs Section --}}
        <div class="mt-16">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-foreground mb-3">{{ __('pricing.buy_credits') }}</h2>
                <p class="text-muted-foreground max-w-lg mx-auto">{{ __('pricing.credits_description') }}</p>
            </div>

            @php
                $packCount = count($creditPacks);
            @endphp

            <div class="grid {{ $packCount === 1 ? 'grid-cols-1 max-w-xs mx-auto' : ($packCount === 2 ? 'grid-cols-2 max-w-md mx-auto' : ($packCount === 3 ? 'grid-cols-3 max-w-2xl mx-auto' : 'grid-cols-2 md:grid-cols-4')) }} gap-4">
                @foreach($creditPacks as $key => $pack)
                    <div class="bg-secondary rounded-2xl p-5 border border-border relative {{ isset($pack['popular']) && $pack['popular'] ? 'ring-2 ring-primary' : '' }} {{ isset($pack['best_value']) && $pack['best_value'] ? 'ring-2 ring-green-500' : '' }}">
                        @if(isset($pack['popular']) && $pack['popular'])
                            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-primary text-primary-foreground text-xs font-bold rounded-full">{{ __('pricing.popular') }}</div>
                        @endif
                        @if(isset($pack['best_value']) && $pack['best_value'])
                            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-green-500 text-white text-xs font-bold rounded-full">{{ __('pricing.best_value') }}</div>
                        @endif

                        <div class="text-center">
                            <div class="text-3xl sm:text-4xl font-bold text-primary mb-1">{{ $pack['credits'] }}</div>
                            <div class="text-sm text-muted-foreground mb-3">{{ __('pricing.credits') }}</div>
                            <div class="text-xl sm:text-2xl font-bold text-foreground mb-1">{{ $currencySymbol }}{{ number_format($pack['price'] / 100, 2) }}</div>
                            <div class="text-xs text-muted-foreground mb-4">{{ $pack['per_credit'] }}</div>

                            @auth
                                <form action="{{ route('checkout.credits') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="pack" value="{{ $key }}">
                                    <input type="hidden" name="currency" value="{{ $currency }}">
                                    <button type="submit" class="w-full py-2.5 bg-primary text-primary-foreground text-sm font-semibold rounded-lg hover:bg-primary/90 transition-colors cursor-pointer">
                                        {{ __('pricing.buy_now') }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="block w-full py-2.5 bg-primary text-primary-foreground text-sm font-semibold rounded-lg hover:bg-primary/90 transition-colors text-center">
                                    {{ __('pricing.login_to_buy') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>

            @auth
                <p class="text-center text-sm text-muted-foreground mt-6">
                    {{ __('pricing.current_credits', ['credits' => auth()->user()->credits]) }}
                </p>
            @endauth
        </div>

        {{-- Secure Payment Notice --}}
        <div class="mt-12 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-secondary rounded-full">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-sm text-muted-foreground">{{ __('pricing.secure_payment') }}</span>
            </div>
        </div>
    </div>
</div>
