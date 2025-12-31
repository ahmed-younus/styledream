<?php

namespace App\Livewire;

use Livewire\Component;

class Profile extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.profile', [
            'user' => $user,
            'stats' => [
                'total_tryons' => $user->tryOns()->count(),
                'completed_tryons' => $user->tryOns()->completed()->count(),
                'wardrobe_items' => $user->wardrobeItems()->count(),
            ],
        ])->layout('layouts.app', ['title' => 'Profile']);
    }
}
