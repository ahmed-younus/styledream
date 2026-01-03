<?php

namespace App\Livewire;

use App\Models\Avatar;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Onboarding extends Component
{
    use WithFileUploads;

    public $step = 1; // 1: Welcome, 2: Upload, 3: Complete
    public $avatarImage;
    public $avatarName = '';
    public $gender = 'unisex';
    public $imagePreview;
    public $createdAvatar = null;

    public function mount()
    {
        $user = Auth::user();

        // Check if user already has 3 avatars (maximum)
        if ($user->avatars()->count() >= 3) {
            session()->flash('message', __('onboarding.max_avatars_reached'));
            return redirect()->route('profile', [], false)->withFragment('avatars');
        }

        // If onboarding already completed, skip to step 2 (create avatar)
        if ($user->onboarding_completed) {
            $this->step = 2;
        }
    }

    public function updatedAvatarImage()
    {
        $this->validate(['avatarImage' => 'image|max:10240']);
        $this->imagePreview = $this->avatarImage->temporaryUrl();
    }

    public function nextStep()
    {
        $this->step = 2;
    }

    public function createAvatar()
    {
        $this->validate([
            'avatarImage' => 'required|image|max:10240',
            'gender' => 'required|in:men,women,unisex',
        ]);

        // Store image
        $filename = 'avatars/' . uniqid() . '_' . time() . '.jpg';
        $imageData = file_get_contents($this->avatarImage->getRealPath());
        Storage::disk('public')->put($filename, $imageData);

        // Create avatar (only set as default if user has no other avatars)
        $user = Auth::user();
        $isFirstAvatar = $user->avatars()->count() === 0;

        $this->createdAvatar = $user->avatars()->create([
            'name' => $this->avatarName ?: null,
            'image_url' => '/storage/' . $filename,
            'gender' => $this->gender,
            'is_default' => $isFirstAvatar,
        ]);

        // Mark onboarding complete
        Auth::user()->update(['onboarding_completed' => true]);

        $this->step = 3;
    }

    public function skip()
    {
        Auth::user()->update(['onboarding_completed' => true]);
        return redirect()->route('studio');
    }

    public function goToStudio()
    {
        return redirect()->route('studio');
    }

    public function render()
    {
        return view('livewire.onboarding')
            ->layout('layouts.app', ['title' => __('onboarding.title')]);
    }
}
