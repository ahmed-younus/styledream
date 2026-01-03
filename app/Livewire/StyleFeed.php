<?php

namespace App\Livewire;

use App\Models\OutfitPost;
use App\Models\OutfitLike;
use App\Models\OutfitComment;
use App\Models\OutfitRating;
use App\Models\FeedAd;
use App\Models\PostReport;
use App\Models\ShareEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Style Feed')]
class StyleFeed extends Component
{
    use WithPagination;

    public $sortBy = 'new';
    public $perPage = 10;

    // Comment input per post
    public $commentInputs = [];

    // Rating state
    public $showRatingModal = false;
    public $ratingPostId = null;
    public $selectedRating = 0;

    // Report state
    public $showReportModal = false;
    public $reportPostId = null;
    public $reportReason = '';
    public $reportDetails = '';

    // Share state
    public $showShareModal = false;
    public $sharePostId = null;

    protected $listeners = ['refreshFeed' => '$refresh'];

    public function mount()
    {
        $this->commentInputs = [];
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function toggleLike($postId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $post = OutfitPost::findOrFail($postId);

        $existingLike = OutfitLike::where('user_id', $user->id)
            ->where('outfit_post_id', $postId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            OutfitLike::create([
                'user_id' => $user->id,
                'outfit_post_id' => $postId,
            ]);
        }
    }

    public function addComment($postId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $content = $this->commentInputs[$postId] ?? '';

        if (empty(trim($content))) {
            return;
        }

        // Rate limit: max 50 comments per day
        $todayCount = OutfitComment::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 50) {
            session()->flash('error', 'Daily comment limit reached');
            return;
        }

        OutfitComment::create([
            'user_id' => Auth::id(),
            'outfit_post_id' => $postId,
            'content' => substr(trim($content), 0, 500), // Max 500 chars
        ]);

        $this->commentInputs[$postId] = '';
    }

    public function openRatingModal($postId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->ratingPostId = $postId;
        $this->selectedRating = Auth::user()->getRatingForPost(OutfitPost::find($postId)) ?? 0;
        $this->showRatingModal = true;
    }

    public function submitRating()
    {
        if (!Auth::check() || !$this->ratingPostId || $this->selectedRating < 1) {
            return;
        }

        OutfitRating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'outfit_post_id' => $this->ratingPostId,
            ],
            [
                'rating' => $this->selectedRating,
            ]
        );

        $this->closeRatingModal();
    }

    public function closeRatingModal()
    {
        $this->showRatingModal = false;
        $this->ratingPostId = null;
        $this->selectedRating = 0;
    }

    public function openReportModal($postId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->reportPostId = $postId;
        $this->reportReason = '';
        $this->reportDetails = '';
        $this->showReportModal = true;
    }

    public function submitReport()
    {
        if (!Auth::check() || !$this->reportPostId || empty($this->reportReason)) {
            return;
        }

        $post = OutfitPost::findOrFail($this->reportPostId);

        PostReport::reportPost(
            Auth::user(),
            $post,
            $this->reportReason,
            $this->reportDetails
        );

        $this->closeReportModal();
        session()->flash('message', __('feed.report_submitted'));
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->reportPostId = null;
        $this->reportReason = '';
        $this->reportDetails = '';
    }

    public function openShareModal($postId)
    {
        $this->sharePostId = $postId;
        $this->showShareModal = true;
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->sharePostId = null;
    }

    public function trackShare($platform)
    {
        if (Auth::check() && $this->sharePostId) {
            ShareEvent::record(
                Auth::id(),
                $platform,
                $this->sharePostId
            );
        }
        $this->closeShareModal();
    }

    public function tryOnFromPost($postId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $post = OutfitPost::with('tryOn')->findOrFail($postId);

        // Redirect to studio with the post data for re-trying
        return redirect()->route('studio', ['from_post' => $postId]);
    }

    public function render()
    {
        $query = OutfitPost::query()->feed();

        // Apply sorting
        switch ($this->sortBy) {
            case 'trending':
                $query->orderByDesc('likes_count')
                      ->orderByDesc('comments_count');
                break;
            case 'top_rated':
                $query->orderByDesc('avg_rating')
                      ->where('ratings_count', '>', 0);
                break;
            case 'new':
            default:
                $query->latest();
                break;
        }

        $posts = $query->take($this->perPage)->get();

        // Get active ads (insert every 5 posts)
        $ads = FeedAd::active()->ordered()->take(3)->get();

        // Mix posts with ads
        $feedItems = collect();
        $adIndex = 0;

        foreach ($posts as $index => $post) {
            $feedItems->push(['type' => 'post', 'data' => $post]);

            // Insert ad after every 5 posts
            if (($index + 1) % 5 === 0 && isset($ads[$adIndex])) {
                $ads[$adIndex]->recordImpression();
                $feedItems->push(['type' => 'ad', 'data' => $ads[$adIndex]]);
                $adIndex++;
            }
        }

        $hasMore = OutfitPost::public()->count() > $this->perPage;

        return view('livewire.style-feed', [
            'feedItems' => $feedItems,
            'hasMore' => $hasMore,
        ]);
    }
}
