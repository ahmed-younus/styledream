<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl mb-4">
                <span class="text-white font-bold text-2xl">SD</span>
            </div>
            <h1 class="text-2xl font-bold text-white">StyleDream Admin</h1>
            <p class="text-gray-400 mt-1">Sign in to your admin account</p>
        </div>

        {{-- Login Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
            @if($error)
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                </div>
            @endif

            <form wire:submit="login" class="space-y-6">
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
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
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
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
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
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
        <p class="text-center mt-6 text-gray-500 text-sm">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">&larr; Back to website</a>
        </p>
    </div>
</div>
