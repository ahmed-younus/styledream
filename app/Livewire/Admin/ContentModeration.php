<?php

namespace App\Livewire\Admin;

use App\Models\PostReport;
use App\Models\OutfitPost;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Content Moderation')]
class ContentModeration extends Component
{
    use WithPagination;

    public string $tab = 'reports';

    public function dismissReport(int $reportId)
    {
        PostReport::find($reportId)?->update(['status' => 'dismissed']);
    }

    public function deletePost(int $postId)
    {
        OutfitPost::find($postId)?->delete();
        PostReport::where('outfit_post_id', $postId)->update(['status' => 'resolved']);
    }

    public function render()
    {
        $reports = PostReport::with(['outfitPost.user', 'reporter'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.content-moderation', [
            'reports' => $reports,
        ]);
    }
}
