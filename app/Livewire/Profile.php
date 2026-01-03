<?php

namespace App\Livewire;

use App\Models\Avatar;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Profile extends Component
{
    use WithFileUploads;

    public $displayName = '';
    public $bio = '';
    public $locale = 'en';
    public $avatar;
    public $avatarPreview;

    public $showEditModal = false;

    protected $rules = [
        'displayName' => 'nullable|string|max:50',
        'bio' => 'nullable|string|max:500',
        'locale' => 'required|string|in:en,es,fr,de,it,pt,nl',
        'avatar' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->displayName = $user->display_name ?? $user->name;
        $this->bio = $user->bio ?? '';
        $this->locale = $user->locale ?? 'en';
        $this->avatarPreview = $user->avatar_url;
    }

    public function updatedAvatar()
    {
        $this->validate(['avatar' => 'image|max:2048']);
        $this->avatarPreview = $this->avatar->temporaryUrl();
    }

    public function openEditModal()
    {
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->avatar = null;
        $this->mount(); // Reset to original values
    }

    public function saveProfile()
    {
        $this->validate();

        $user = Auth::user();

        // Handle avatar upload
        if ($this->avatar) {
            // Delete old avatar if it's a local file
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            // Store new avatar
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar_url = '/storage/' . $path;
        }

        $user->display_name = $this->displayName ?: null;
        $user->bio = $this->bio ?: null;
        $user->locale = $this->locale;
        $user->save();

        // Update locale for current session
        app()->setLocale($this->locale);
        session(['locale' => $this->locale]);

        $this->avatarPreview = $user->avatar_url;
        $this->closeEditModal();
        session()->flash('message', __('profile.changes_saved'));
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            Storage::disk('public')->delete($oldPath);
        }

        $user->avatar_url = null;
        $user->save();

        $this->avatarPreview = null;
        $this->avatar = null;
    }

    // ============ BODY AVATAR MANAGEMENT ============

    public function setDefaultAvatar(int $avatarId)
    {
        $user = Auth::user();

        // Reset all avatars to non-default
        $user->avatars()->update(['is_default' => false]);

        // Set the selected one as default
        $user->avatars()->where('id', $avatarId)->update(['is_default' => true]);
    }

    public function deleteAvatar(int $avatarId)
    {
        $avatar = Avatar::where('id', $avatarId)
            ->where('user_id', Auth::id())
            ->first();

        if ($avatar) {
            // Delete image file
            $path = str_replace('/storage/', '', $avatar->image_url);
            Storage::disk('public')->delete($path);

            // If this was default, make another one default
            $wasDefault = $avatar->is_default;
            $avatar->delete();

            if ($wasDefault) {
                $firstAvatar = Auth::user()->avatars()->first();
                if ($firstAvatar) {
                    $firstAvatar->update(['is_default' => true]);
                }
            }
        }
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.profile', [
            'user' => $user,
            'stats' => [
                'total_tryons' => $user->tryOns()->count(),
                'completed_tryons' => $user->tryOns()->completed()->count(),
                'wardrobe_items' => $user->wardrobeItems()->count(),
                'public_posts' => $user->public_posts_count,
                'likes_received' => $user->total_likes_received,
                'saved_outfits' => $user->savedOutfits()->count(),
            ],
            'recentPosts' => $user->outfitPosts()->public()->latest()->take(6)->get(),
            'bodyAvatars' => $user->avatars()->orderByDesc('is_default')->orderByDesc('created_at')->get(),
            'languages' => [
                'en' => 'English',
                'es' => 'Español',
                'fr' => 'Français',
                'de' => 'Deutsch',
                'it' => 'Italiano',
                'pt' => 'Português',
                'nl' => 'Nederlands',
            ],
        ])->layout('layouts.app', ['title' => 'Profile']);
    }
}
