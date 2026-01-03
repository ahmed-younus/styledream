<?php

namespace App\Livewire;

use Livewire\Component;

class Pricing extends Component
{
    public function render()
    {
        return view('livewire.pricing', [
            'creditPacks' => config('credits.packs', []),
        ])->layout('layouts.app', ['title' => 'Pricing']);
    }
}
