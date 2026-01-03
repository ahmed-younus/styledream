<div>
    {{-- Period Selector --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400">Performance Analytics</h2>
        <select wire:model.live="period" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
        </select>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- New Users Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">New Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($userStats['new']) }}</p>
                    <p class="text-xs mt-1 {{ $userStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $userStats['growth_rate'] >= 0 ? '+' : '' }}{{ $userStats['growth_rate'] }}% vs previous
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Revenue Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($revenueStats['total'], 2) }}</p>
                    <p class="text-xs mt-1 {{ $revenueStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $revenueStats['growth_rate'] >= 0 ? '+' : '' }}{{ $revenueStats['growth_rate'] }}% vs previous
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Try-Ons Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Try-Ons</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($tryOnStats['total']) }}</p>
                    <p class="text-xs mt-1 {{ $tryOnStats['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tryOnStats['growth_rate'] >= 0 ? '+' : '' }}{{ $tryOnStats['growth_rate'] }}% vs previous
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Success Rate Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $tryOnStats['success_rate'] }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Try-on completion rate</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- User Growth Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Growth</h3>
            <div class="h-64 flex items-end justify-between gap-1">
                @php
                    $maxUsers = max(array_column($userStats['chart'], 'count')) ?: 1;
                @endphp
                @foreach($userStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-blue-500 hover:bg-blue-600 rounded-t transition-all cursor-pointer"
                                 style="height: {{ max(4, ($day['count'] / $maxUsers) * 200) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                {{ $day['count'] }} users
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($userStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-gray-500 mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue</h3>
            <div class="h-64 flex items-end justify-between gap-1">
                @php
                    $maxRevenue = max(array_column($revenueStats['chart'], 'amount')) ?: 1;
                @endphp
                @foreach($revenueStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-green-500 hover:bg-green-600 rounded-t transition-all cursor-pointer"
                                 style="height: {{ max(4, ($day['amount'] / $maxRevenue) * 200) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                ${{ number_format($day['amount'], 2) }}
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($revenueStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-gray-500 mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Try-Ons & Credits Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Try-Ons Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Try-Ons</h3>
            <div class="h-64 flex items-end justify-between gap-1">
                @php
                    $maxTryOns = max(array_column($tryOnStats['chart'], 'count')) ?: 1;
                @endphp
                @foreach($tryOnStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-purple-500 hover:bg-purple-600 rounded-t transition-all cursor-pointer"
                                 style="height: {{ max(4, ($day['count'] / $maxTryOns) * 200) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                {{ $day['count'] }} try-ons
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($tryOnStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-gray-500 mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Credits Usage Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Credits Used</h3>
            <div class="h-64 flex items-end justify-between gap-1">
                @php
                    $maxCredits = max(array_column($creditStats['chart'], 'used')) ?: 1;
                @endphp
                @foreach($creditStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-orange-500 hover:bg-orange-600 rounded-t transition-all cursor-pointer"
                                 style="height: {{ max(4, ($day['used'] / $maxCredits) * 200) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                {{ $day['used'] }} credits
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($creditStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-gray-500 mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Credit Stats Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Credits Used</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($creditStats['used']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Credits Purchased</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($creditStats['purchased']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Credits Gifted/Bonus</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($creditStats['gifted']) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
