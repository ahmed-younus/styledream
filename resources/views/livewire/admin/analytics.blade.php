<div>
    {{-- Period Selector --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-sm font-medium text-[#697386]">Performance Analytics</h2>
        <select wire:model.live="period" class="px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-sm text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors">
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
        </select>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- New Users Card --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#697386]">New Users</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $userStats['growth_rate'] >= 0 ? 'text-[#30b566] bg-[#30b566]/10' : 'text-[#df1b41] bg-[#df1b41]/10' }}">
                    {{ $userStats['growth_rate'] >= 0 ? '+' : '' }}{{ $userStats['growth_rate'] }}%
                </span>
            </div>
            <p class="text-3xl font-semibold text-[#1a1f36] mt-3 tabular-nums">{{ number_format($userStats['new']) }}</p>
            <p class="text-sm text-[#697386] mt-2">vs previous period</p>
        </div>

        {{-- Revenue Card --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#697386]">Revenue</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $revenueStats['growth_rate'] >= 0 ? 'text-[#30b566] bg-[#30b566]/10' : 'text-[#df1b41] bg-[#df1b41]/10' }}">
                    {{ $revenueStats['growth_rate'] >= 0 ? '+' : '' }}{{ $revenueStats['growth_rate'] }}%
                </span>
            </div>
            <p class="text-3xl font-semibold text-[#1a1f36] mt-3 tabular-nums">${{ number_format($revenueStats['total'], 2) }}</p>
            <p class="text-sm text-[#697386] mt-2">vs previous period</p>
        </div>

        {{-- Try-Ons Card --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-[#697386]">Try-Ons</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $tryOnStats['growth_rate'] >= 0 ? 'text-[#30b566] bg-[#30b566]/10' : 'text-[#df1b41] bg-[#df1b41]/10' }}">
                    {{ $tryOnStats['growth_rate'] >= 0 ? '+' : '' }}{{ $tryOnStats['growth_rate'] }}%
                </span>
            </div>
            <p class="text-3xl font-semibold text-[#1a1f36] mt-3 tabular-nums">{{ number_format($tryOnStats['total']) }}</p>
            <p class="text-sm text-[#697386] mt-2">vs previous period</p>
        </div>

        {{-- Success Rate Card --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <p class="text-sm font-medium text-[#697386]">Success Rate</p>
            <p class="text-3xl font-semibold text-[#1a1f36] mt-3 tabular-nums">{{ $tryOnStats['success_rate'] }}%</p>
            <p class="text-sm text-[#697386] mt-2">Try-on completion rate</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- User Growth Chart --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-medium text-[#1a1f36]">User Growth</h3>
                <span class="text-xs text-[#697386]">Trend</span>
            </div>
            <div class="h-52 flex items-end justify-between gap-1">
                @php
                    $maxUsers = max(array_column($userStats['chart'], 'count')) ?: 1;
                @endphp
                @foreach($userStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-[#635bff] hover:bg-[#5248f0] rounded-sm transition-colors cursor-pointer"
                                 style="height: {{ max(4, ($day['count'] / $maxUsers) * 180) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-[#1a1f36] text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                {{ $day['count'] }} users
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($userStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-[#697386] mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-medium text-[#1a1f36]">Revenue</h3>
                <span class="text-xs text-[#697386]">Trend</span>
            </div>
            <div class="h-52 flex items-end justify-between gap-1">
                @php
                    $maxRevenue = max(array_column($revenueStats['chart'], 'amount')) ?: 1;
                @endphp
                @foreach($revenueStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-[#30b566] hover:bg-[#28a058] rounded-sm transition-colors cursor-pointer"
                                 style="height: {{ max(4, ($day['amount'] / $maxRevenue) * 180) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-[#1a1f36] text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                ${{ number_format($day['amount'], 2) }}
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($revenueStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-[#697386] mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Try-Ons & Credits Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Try-Ons Chart --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-medium text-[#1a1f36]">Try-Ons</h3>
                <span class="text-xs text-[#697386]">Trend</span>
            </div>
            <div class="h-52 flex items-end justify-between gap-1">
                @php
                    $maxTryOns = max(array_column($tryOnStats['chart'], 'count')) ?: 1;
                @endphp
                @foreach($tryOnStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-[#635bff] hover:bg-[#5248f0] rounded-sm transition-colors cursor-pointer"
                                 style="height: {{ max(4, ($day['count'] / $maxTryOns) * 180) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-[#1a1f36] text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                {{ $day['count'] }} try-ons
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($tryOnStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-[#697386] mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Credits Usage Chart --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-medium text-[#1a1f36]">Credits Used</h3>
                <span class="text-xs text-[#697386]">Trend</span>
            </div>
            <div class="h-52 flex items-end justify-between gap-1">
                @php
                    $maxCredits = max(array_column($creditStats['chart'], 'used')) ?: 1;
                @endphp
                @foreach($creditStats['chart'] as $day)
                    <div class="flex-1 flex flex-col items-center group">
                        <div class="relative w-full">
                            <div class="w-full bg-[#f5a623] hover:bg-[#e09620] rounded-sm transition-colors cursor-pointer"
                                 style="height: {{ max(4, ($day['used'] / $maxCredits) * 180) }}px">
                            </div>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-[#1a1f36] text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                {{ $day['used'] }} credits
                            </div>
                        </div>
                        @if($loop->index % max(1, floor(count($creditStats['chart']) / 7)) === 0)
                            <p class="text-[10px] text-[#697386] mt-2 truncate max-w-full">{{ $day['date'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Credit Stats Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <p class="text-sm font-medium text-[#697386]">Credits Used</p>
            <p class="text-2xl font-semibold text-[#1a1f36] mt-2 tabular-nums">{{ number_format($creditStats['used']) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <p class="text-sm font-medium text-[#697386]">Credits Purchased</p>
            <p class="text-2xl font-semibold text-[#1a1f36] mt-2 tabular-nums">{{ number_format($creditStats['purchased']) }}</p>
        </div>

        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <p class="text-sm font-medium text-[#697386]">Credits Gifted/Bonus</p>
            <p class="text-2xl font-semibold text-[#1a1f36] mt-2 tabular-nums">{{ number_format($creditStats['gifted']) }}</p>
        </div>
    </div>
</div>
