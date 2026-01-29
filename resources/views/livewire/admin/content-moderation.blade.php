<div>
    <h2 class="text-sm font-medium text-[#697386] mb-6">Review reported content</h2>

    <div class="bg-white rounded-lg border border-[#e3e8ee] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#f6f8fa]">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Post</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Reason</th>
                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Reporter</th>
                        <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Date</th>
                        <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-[#697386] uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e8ee]">
                    @forelse($reports as $report)
                        <tr class="hover:bg-[#f6f8fa] transition-colors">
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    @if($report->outfitPost?->image_url)
                                        <img src="{{ $report->outfitPost->image_url }}" class="w-10 h-10 sm:w-12 sm:h-12 object-cover rounded-lg flex-shrink-0">
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-[#1a1f36] truncate max-w-[100px] sm:max-w-none">{{ $report->outfitPost?->user?->name ?? 'Deleted' }}</p>
                                        <p class="text-xs text-[#697386]">Post #{{ $report->outfit_post_id }}</p>
                                        {{-- Show date on mobile --}}
                                        <p class="text-xs text-[#697386] sm:hidden">{{ $report->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-[#df1b41]/10 text-[#df1b41] rounded-full">{{ ucfirst($report->reason) }}</span>
                                @if($report->description)
                                    <p class="text-xs text-[#697386] mt-1 hidden sm:block">{{ Str::limit($report->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 text-sm text-[#697386]">{{ $report->reporter?->name ?? 'Anonymous' }}</td>
                            <td class="hidden sm:table-cell px-6 py-4 text-sm text-[#697386]">{{ $report->created_at->diffForHumans() }}</td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 text-right">
                                <div class="flex items-center justify-end gap-1 sm:gap-2">
                                    <button wire:click="dismissReport({{ $report->id }})" class="px-2 sm:px-3 py-1 text-xs font-medium bg-[#f0f3f7] text-[#1a1f36] rounded-lg hover:bg-[#e3e8ee] transition-colors">Dismiss</button>
                                    <button wire:click="deletePost({{ $report->outfit_post_id }})" wire:confirm="Delete this post permanently?" class="px-2 sm:px-3 py-1 text-xs font-medium bg-[#df1b41]/10 text-[#df1b41] rounded-lg hover:bg-[#df1b41]/20 transition-colors">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-[#697386] text-sm">No pending reports</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-[#e3e8ee]">
            {{ $reports->links() }}
        </div>
    </div>
</div>
