<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'StyleDream' }} - AI Virtual Try-On</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-[60] px-4 md:px-6 py-3 md:py-4 bg-background/95 backdrop-blur-sm border-b border-border">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="font-display text-lg md:text-xl font-bold tracking-tight text-foreground uppercase">
                StyleDream
            </a>

            <!-- Desktop Nav -->
            <div class="hidden md:flex items-center gap-6">
                @auth
                    <a href="{{ route('feed') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.feed') }}
                    </a>
                    <a href="{{ route('studio') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.studio') }}
                    </a>
                    <a href="{{ route('wardrobe') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.wardrobe') }}
                    </a>
                    <a href="{{ route('my-outfits') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.my_outfits') }}
                    </a>

                    <!-- Credits with Upgrade -->
                    <a href="{{ route('pricing') }}" class="flex items-center gap-2 px-3 py-1.5 bg-secondary hover:bg-secondary/80 rounded-full transition-all border border-border group">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 6v12M8 10h8M8 14h8"/>
                            </svg>
                            <span class="text-sm font-bold text-foreground">{{ auth()->user()->credits }}</span>
                        </div>
                        <span class="text-[10px] font-semibold text-primary-foreground bg-primary px-2 py-0.5 rounded-full group-hover:bg-primary/90 transition-colors">GET MORE</span>
                    </a>

                    <!-- User Menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-full hover:bg-secondary transition-colors">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-semibold">
                                    {{ substr(auth()->user()->name, 0, 2) }}
                                </div>
                            @endif
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-background border border-border rounded-lg shadow-lg py-1 z-50">
                            <div class="px-4 py-2 border-b border-border">
                                <p class="text-sm font-medium text-foreground">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors">{{ __('nav.profile') }}</a>
                            <div class="border-t border-border my-1"></div>
                            <div class="px-4 py-2 text-xs text-muted-foreground uppercase">{{ __('profile.language') }}</div>
                            <div class="grid grid-cols-2 gap-1 px-2 pb-2">
                                <a href="?lang=en" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'en' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">EN</a>
                                <a href="?lang=es" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'es' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">ES</a>
                                <a href="?lang=fr" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'fr' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">FR</a>
                                <a href="?lang=de" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'de' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">DE</a>
                                <a href="?lang=it" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'it' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">IT</a>
                                <a href="?lang=pt" class="px-2 py-1 text-xs text-center rounded {{ app()->getLocale() === 'pt' ? 'bg-primary text-primary-foreground' : 'hover:bg-secondary' }}">PT</a>
                            </div>
                            <div class="border-t border-border my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-destructive hover:bg-secondary transition-colors">
                                    {{ __('app.sign_out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('feed') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.feed') }}
                    </a>
                    <a href="{{ route('brands') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.brands') }}
                    </a>
                    <a href="{{ route('pricing') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('nav.pricing') }}
                    </a>

                    <!-- Language Selector -->
                    <div x-data="{ langOpen: false }" class="relative">
                        <button @click="langOpen = !langOpen" class="flex items-center gap-1 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                            <span class="uppercase">{{ app()->getLocale() }}</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="langOpen" @click.away="langOpen = false" x-transition
                             class="absolute right-0 mt-2 w-40 bg-background border border-border rounded-lg shadow-lg py-1 z-50">
                            <a href="?lang=en" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'en' ? 'bg-secondary' : '' }}">English</a>
                            <a href="?lang=es" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'es' ? 'bg-secondary' : '' }}">Español</a>
                            <a href="?lang=fr" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'fr' ? 'bg-secondary' : '' }}">Français</a>
                            <a href="?lang=de" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'de' ? 'bg-secondary' : '' }}">Deutsch</a>
                            <a href="?lang=it" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'it' ? 'bg-secondary' : '' }}">Italiano</a>
                            <a href="?lang=pt" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'pt' ? 'bg-secondary' : '' }}">Português</a>
                            <a href="?lang=nl" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors {{ app()->getLocale() === 'nl' ? 'bg-secondary' : '' }}">Nederlands</a>
                        </div>
                    </div>

                    <a href="{{ route('login') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        {{ __('app.sign_in') }}
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                        {{ __('app.get_started') }}
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden" x-data="{ mobileOpen: false }">
                <button @click="mobileOpen = !mobileOpen" class="p-2 text-foreground">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Mobile Menu -->
                <div x-show="mobileOpen" x-transition class="absolute top-full left-0 right-0 bg-background border-b border-border shadow-lg">
                    <div class="px-4 py-4 space-y-3">
                        @auth
                            <!-- Mobile Credits -->
                            <a href="{{ route('pricing') }}" class="flex items-center justify-between py-3 px-4 -mx-4 bg-secondary border-b border-border">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v12M8 10h8M8 14h8"/>
                                    </svg>
                                    <span class="font-bold text-foreground">{{ auth()->user()->credits }} credits</span>
                                </div>
                                <span class="text-xs font-semibold text-primary-foreground bg-primary px-3 py-1 rounded-full">GET MORE</span>
                            </a>

                            <a href="{{ route('feed') }}" class="block py-2 text-foreground font-medium">{{ __('nav.feed') }}</a>
                            <a href="{{ route('studio') }}" class="block py-2 text-foreground font-medium">{{ __('nav.studio') }}</a>
                            <a href="{{ route('wardrobe') }}" class="block py-2 text-foreground font-medium">{{ __('nav.wardrobe') }}</a>
                            <a href="{{ route('my-outfits') }}" class="block py-2 text-foreground font-medium">{{ __('nav.my_outfits') }}</a>
                            <a href="{{ route('profile') }}" class="block py-2 text-foreground font-medium">{{ __('nav.profile') }}</a>
                            <div class="pt-2 border-t border-border">
                                <p class="text-xs text-muted-foreground uppercase mb-2">{{ __('profile.language') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="?lang=en" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'en' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">EN</a>
                                    <a href="?lang=es" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'es' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">ES</a>
                                    <a href="?lang=fr" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'fr' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">FR</a>
                                    <a href="?lang=de" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'de' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">DE</a>
                                    <a href="?lang=it" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'it' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">IT</a>
                                    <a href="?lang=pt" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'pt' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">PT</a>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-border mt-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-destructive font-medium">{{ __('app.sign_out') }}</button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('feed') }}" class="block py-2 text-foreground font-medium">{{ __('nav.feed') }}</a>
                            <a href="{{ route('brands') }}" class="block py-2 text-foreground font-medium">{{ __('nav.brands') }}</a>
                            <div class="pt-2 border-t border-border">
                                <p class="text-xs text-muted-foreground uppercase mb-2">{{ __('profile.language') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    <a href="?lang=en" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'en' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">EN</a>
                                    <a href="?lang=es" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'es' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">ES</a>
                                    <a href="?lang=fr" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'fr' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">FR</a>
                                    <a href="?lang=de" class="px-3 py-1 text-xs rounded-full {{ app()->getLocale() === 'de' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground' }}">DE</a>
                                </div>
                            </div>
                            <div class="pt-3 space-y-2">
                                <a href="{{ route('login') }}" class="block py-2 text-foreground font-medium">{{ __('app.sign_in') }}</a>
                                <a href="{{ route('register') }}" class="block py-2 bg-primary text-primary-foreground text-center font-medium rounded-lg">{{ __('app.get_started') }}</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-foreground text-background py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <h3 class="font-display text-lg font-bold tracking-tight uppercase mb-4">StyleDream</h3>
                    <p class="text-sm text-background/70">{{ __('home.footer_tagline') }}</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">{{ __('home.footer_product') }}</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="{{ route('studio') }}" class="hover:text-background transition-colors">{{ __('home.footer_studio') }}</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-background transition-colors">{{ __('home.footer_pricing') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">{{ __('home.footer_company') }}</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="#" class="hover:text-background transition-colors">{{ __('home.footer_about') }}</a></li>
                        <li><a href="#" class="hover:text-background transition-colors">{{ __('home.footer_contact') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">{{ __('home.footer_legal') }}</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="{{ route('privacy') }}" class="hover:text-background transition-colors">{{ __('home.footer_privacy') }}</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-background transition-colors">{{ __('home.footer_terms') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-background/20 text-center text-sm text-background/50">
                &copy; {{ date('Y') }} StyleDream. {{ __('home.footer_copyright') }}
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
