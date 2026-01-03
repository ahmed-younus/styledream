<?php

namespace App\Livewire;

use App\Models\SavedOutfit;
use App\Models\OutfitPost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('My Outfits')]
class MyOutfits extends Component
{
    // Delete modal
    public $showDeleteModal = false;
    public $deleteOutfitId = null;

    // Post to feed modal
    public $showPostModal = false;
    public $postOutfitId = null;
    public $postCaption = '';
    public $postVisibility = 'public';

    public function deleteOutfit($id)
    {
        $this->deleteOutfitId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if (!$this->deleteOutfitId) return;

        $outfit = SavedOutfit::where('id', $this->deleteOutfitId)
            ->where('user_id', Auth::id())
            ->first();

        if ($outfit) {
            $outfit->delete();
            session()->flash('message', __('outfits.deleted'));
        }

        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteOutfitId = null;
    }

    public function openPostModal($id)
    {
        $this->postOutfitId = $id;
        $this->postCaption = '';
        $this->postVisibility = 'public';
        $this->showPostModal = true;
    }

    public function closePostModal()
    {
        $this->showPostModal = false;
        $this->postOutfitId = null;
        $this->postCaption = '';
        $this->postVisibility = 'public';
    }

    public function postToFeed()
    {
        if (!$this->postOutfitId) return;

        $outfit = SavedOutfit::where('id', $this->postOutfitId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$outfit) return;

        // Create the post
        $post = OutfitPost::create([
            'user_id' => Auth::id(),
            'try_on_id' => $outfit->try_on_id,
            'image_url' => $outfit->image_url,
            'caption' => $this->postCaption,
            'visibility' => $this->postVisibility,
        ]);

        // Link the outfit to the post
        $outfit->update(['outfit_post_id' => $post->id]);

        session()->flash('message', __('outfits.posted'));
        $this->closePostModal();
    }

    public function tryAgain($id)
    {
        $outfit = SavedOutfit::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($outfit && $outfit->try_on_id) {
            return redirect()->route('studio', ['retry' => $outfit->try_on_id]);
        }

        return redirect()->route('studio');
    }

    public function render()
    {
        $outfits = SavedOutfit::where('user_id', Auth::id())
            ->with(['outfitPost', 'tryOn'])
            ->latest()
            ->get();

        return view('livewire.my-outfits', [
            'outfits' => $outfits,
        ]);
    }
}
