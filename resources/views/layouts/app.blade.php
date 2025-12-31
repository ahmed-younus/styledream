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
                    <a href="{{ route('studio') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Studio
                    </a>
                    <a href="{{ route('wardrobe') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Wardrobe
                    </a>
                    <a href="{{ route('pricing') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Pricing
                    </a>

                    <!-- Credits -->
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-secondary rounded-full">
                        <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.73-2.77-.01-2.2-1.9-2.96-3.66-3.42z"/>
                        </svg>
                        <span class="text-sm font-semibold text-foreground">{{ auth()->user()->credits }}</span>
                        <span class="text-xs text-muted-foreground">credits</span>
                    </div>

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
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-foreground hover:bg-secondary transition-colors">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-destructive hover:bg-secondary transition-colors">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('pricing') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Pricing
                    </a>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                        Get Started
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
                            <a href="{{ route('studio') }}" class="block py-2 text-foreground font-medium">Studio</a>
                            <a href="{{ route('wardrobe') }}" class="block py-2 text-foreground font-medium">Wardrobe</a>
                            <a href="{{ route('pricing') }}" class="block py-2 text-foreground font-medium">Pricing</a>
                            <a href="{{ route('profile') }}" class="block py-2 text-foreground font-medium">Profile</a>
                            <div class="pt-2 border-t border-border">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-destructive font-medium">Sign Out</button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="block py-2 text-foreground font-medium">Sign In</a>
                            <a href="{{ route('register') }}" class="block py-2 bg-primary text-primary-foreground text-center font-medium rounded-lg">Get Started</a>
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
                    <p class="text-sm text-background/70">AI-powered virtual try-on for the modern shopper.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Product</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="{{ route('studio') }}" class="hover:text-background transition-colors">Try-On Studio</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-background transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Company</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="#" class="hover:text-background transition-colors">About</a></li>
                        <li><a href="#" class="hover:text-background transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Legal</h4>
                    <ul class="space-y-2 text-sm text-background/70">
                        <li><a href="#" class="hover:text-background transition-colors">Privacy</a></li>
                        <li><a href="#" class="hover:text-background transition-colors">Terms</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-background/20 text-center text-sm text-background/50">
                &copy; {{ date('Y') }} StyleDream. All rights reserved.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
