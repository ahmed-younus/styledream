<div class="min-h-screen flex items-center justify-center bg-[#f5f5f7] p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-sm border border-[#d2d2d7] mb-4">
                <svg class="w-8 h-8 text-[#1d1d1f]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-semibold text-[#1d1d1f] tracking-tight">Stylely Admin</h1>
            <p class="text-[#86868b] mt-1">Sign in to your admin account</p>
        </div>

        {{-- Login Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#d2d2d7] p-8">
            @if($error)
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm text-red-600">{{ $error }}</p>
                </div>
            @endif

            <form wire:submit="login" class="space-y-6">
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-[#1d1d1f] mb-2">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full px-4 py-3 border border-[#d2d2d7] rounded-xl bg-white text-[#1d1d1f] placeholder-[#86868b] focus:ring-2 focus:ring-[#1d1d1f]/20 focus:border-[#1d1d1f] transition-colors outline-none"
                        placeholder="admin@example.com"
                        required
                        autofocus
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-[#1d1d1f] mb-2">Password</label>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        class="w-full px-4 py-3 border border-[#d2d2d7] rounded-xl bg-white text-[#1d1d1f] placeholder-[#86868b] focus:ring-2 focus:ring-[#1d1d1f]/20 focus:border-[#1d1d1f] transition-colors outline-none"
                        placeholder="••••••••"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        wire:model="remember"
                        class="w-4 h-4 text-[#1d1d1f] border-[#d2d2d7] rounded focus:ring-[#1d1d1f]/20 focus:ring-offset-0"
                    >
                    <label for="remember" class="ml-2 text-sm text-[#86868b]">Remember me</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-[#1d1d1f] text-white font-medium rounded-xl hover:bg-black focus:outline-none focus:ring-2 focus:ring-[#1d1d1f]/20 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Sign In</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center mt-6 text-[#86868b] text-sm">
            <a href="{{ url('/') }}" class="hover:text-[#1d1d1f] transition-colors">&larr; Back to website</a>
        </p>
    </div>
</div>
