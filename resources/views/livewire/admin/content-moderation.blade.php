<div>
    <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400 mb-6">Review reported content</h2>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Post</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reporter</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($report->outfitPost?->image_url)
                                    <img src="{{ $report->outfitPost->image_url }}" class="w-12 h-12 object-cover rounded-lg">
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->outfitPost?->user?->name ?? 'Deleted' }}</p>
                                    <p class="text-xs text-gray-500">Post #{{ $report->outfit_post_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">{{ ucfirst($report->reason) }}</span>
                            @if($report->description)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($report->description, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $report->reporter?->name ?? 'Anonymous' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="dismissReport({{ $report->id }})" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Dismiss</button>
                                <button wire:click="deletePost({{ $report->outfit_post_id }})" wire:confirm="Delete this post permanently?" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Delete Post</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No pending reports</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $reports->links() }}
        </div>
    </div>
</div>
