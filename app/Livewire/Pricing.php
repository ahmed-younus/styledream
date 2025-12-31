<?php

namespace App\Livewire;

use Livewire\Component;

class Pricing extends Component
{
    public function render()
    {
        return view('livewire.pricing')
            ->layout('layouts.app', ['title' => 'Pricing']);
    }
}
