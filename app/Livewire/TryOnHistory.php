<?php

namespace App\Livewire;

use App\Models\TryOn;
use App\Models\SavedOutfit;
use App\Models\OutfitPost;
use App\Models\ShareEvent;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TryOnHistory extends Component
{
    use WithPagination;

    // Save outfit modal
    public $showSaveModal = false;
    public $saveOutfitName = '';
    public $saveOutfitNotes = '';
    public $selectedTryOnId = null;
    public $selectedImageUrl = null;

    // Post to feed modal
    public $showPostModal = false;
    public $postCaption = '';
    public $postVisibility = 'public';

    // Share modal
    public $showShareModal = false;

    // Delete confirmation
    public $showDeleteModal = false;
    public $deleteTargetId = null;

    // ============ SAVE TO MY OUTFITS ============

    public function openSaveModal($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->selectedTryOnId = $tryOn->id;
            $this->selectedImageUrl = $tryOn->result_image_url;
            $this->saveOutfitName = '';
            $this->saveOutfitNotes = '';
            $this->showSaveModal = true;
        }
    }

    public function closeSaveModal()
    {
        $this->showSaveModal = false;
        $this->saveOutfitName = '';
        $this->saveOutfitNotes = '';
        $this->selectedTryOnId = null;
        $this->selectedImageUrl = null;
    }

    public function saveToOutfits()
    {
        if (!$this->selectedImageUrl || !$this->selectedTryOnId) {
            return;
        }

        SavedOutfit::create([
            'user_id' => Auth::id(),
            'try_on_id' => $this->selectedTryOnId,
            'image_url' => $this->selectedImageUrl,
            'name' => $this->saveOutfitName ?: null,
            'notes' => $this->saveOutfitNotes ?: null,
            'garment_data' => [],
        ]);

        $this->closeSaveModal();
        session()->flash('message', __('outfits.saved'));
    }

    // ============ POST TO FEED ============

    public function openPostModal($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->selectedTryOnId = $tryOn->id;
            $this->selectedImageUrl = $tryOn->result_image_url;
            $this->postCaption = '';
            $this->postVisibility = 'public';
            $this->showPostModal = true;
        }
    }

    public function closePostModal()
    {
        $this->showPostModal = false;
        $this->postCaption = '';
        $this->postVisibility = 'public';
        $this->selectedTryOnId = null;
        $this->selectedImageUrl = null;
    }

    public function postToFeed()
    {
        if (!$this->selectedImageUrl || !$this->selectedTryOnId) {
            return;
        }

        OutfitPost::create([
            'user_id' => Auth::id(),
            'try_on_id' => $this->selectedTryOnId,
            'image_url' => $this->selectedImageUrl,
            'caption' => $this->postCaption,
            'visibility' => $this->postVisibility,
        ]);

        $this->closePostModal();
        session()->flash('message', __('outfits.posted'));
    }

    // ============ SHARE ============

    public function openShareModal($tryOnId)
    {
        $tryOn = TryOn::where('id', $tryOnId)->where('user_id', Auth::id())->first();
        if ($tryOn) {
            $this->selectedTryOnId = $tryOn->id;
            $this->selectedImageUrl = $tryOn->result_image_url;
            $this->showShareModal = true;
        }
    }

    public function closeShareModal()
    {
        $this->showShareModal = false;
        $this->selectedTryOnId = null;
        $this->selectedImageUrl = null;
    }

    public function trackShare($platform)
    {
        ShareEvent::record(
            Auth::id(),
            $platform,
            null,
            $this->selectedTryOnId
        );
        $this->closeShareModal();
    }

    // ============ DELETE ============

    public function confirmDelete($tryOnId)
    {
        $this->deleteTargetId = $tryOnId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
    }

    public function deleteTryOn()
    {
        if ($this->deleteTargetId) {
            TryOn::where('id', $this->deleteTargetId)
                ->where('user_id', Auth::id())
                ->delete();

            $this->closeDeleteModal();
            session()->flash('message', __('history.deleted'));
        }
    }

    public function render()
    {
        return view('livewire.try-on-history', [
            'tryOns' => Auth::user()->tryOns()
                ->completed()
                ->latest()
                ->paginate(12),
        ])->layout('layouts.app', ['title' => __('history.title')]);
    }
}
