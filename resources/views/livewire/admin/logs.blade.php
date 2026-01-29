<div>
    <h2 class="text-sm font-medium text-[#697386] mb-6">Admin Activity Logs</h2>

    {{-- Filters --}}
    <div class="bg-white rounded-lg border border-[#e3e8ee] p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Search logs..."
                       class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-sm text-[#1a1f36] placeholder-[#697386] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors">
            </div>

            {{-- Action Filter --}}
            <select wire:model.live="actionFilter" class="px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-sm text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                @endforeach
            </select>

            {{-- Admin Filter --}}
            <select wire:model.live="adminFilter" class="px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-sm text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors">
                <option value="">All Admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-lg border border-[#e3e8ee] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#f6f8fa]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e8ee]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-[#f6f8fa] transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-[#f0f3f7] rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-[#1a1f36]">
                                            {{ substr($log->adminUser?->name ?? '?', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-[#1a1f36]">
                                        {{ $log->adminUser?->name ?? 'System' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-[#f0f3f7] text-[#1a1f36] rounded-full">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[#697386]">
                                @if($log->model_type)
                                    {{ class_basename($log->model_type) }}
                                    @if($log->model_id)
                                        <span class="text-[#697386]">#{{ $log->model_id }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-[#1a1f36] max-w-xs truncate">
                                    {{ $log->description ?? '-' }}
                                </p>
                                @if($log->changes && count($log->changes) > 0)
                                    <button x-data="{ open: false }"
                                            @click="open = !open"
                                            class="text-xs text-[#635bff] hover:text-[#5248f0] mt-1">
                                        <span x-text="open ? 'Hide changes' : 'View changes'"></span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-[#697386]">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-[#697386]">
                                <span title="{{ $log->created_at->format('M d, Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-[#697386] text-sm">
                                No activity logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-[#e3e8ee]">
            {{ $logs->links() }}
        </div>
    </div>
</div>
