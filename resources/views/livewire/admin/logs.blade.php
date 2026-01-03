<div>
    <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-6">Admin Activity Logs</h2>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Search logs..."
                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            {{-- Action Filter --}}
            <select wire:model.live="actionFilter" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                @endforeach
            </select>

            {{-- Admin Filter --}}
            <select wire:model.live="adminFilter" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-purple-600 dark:text-purple-400">
                                            {{ substr($log->adminUser?->name ?? '?', 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $log->adminUser?->name ?? 'System' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $color = $this->getActionColor($log->action);
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400 rounded-full">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($log->model_type)
                                    {{ class_basename($log->model_type) }}
                                    @if($log->model_id)
                                        <span class="text-gray-400">#{{ $log->model_id }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                    {{ $log->description ?? '-' }}
                                </p>
                                @if($log->changes && count($log->changes) > 0)
                                    <button x-data="{ open: false }"
                                            @click="open = !open"
                                            class="text-xs text-purple-600 hover:text-purple-700 mt-1">
                                        <span x-text="open ? 'Hide changes' : 'View changes'"></span>
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span title="{{ $log->created_at->format('M d, Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No activity logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
    </div>
</div>
