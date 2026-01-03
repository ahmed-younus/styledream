<?php

namespace App\Livewire\Admin;

use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Subscriptions')]
class Subscriptions extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'all';

    public function render()
    {
        $query = Subscription::with('user')
            ->when($this->search, fn($q) => $q->whereHas('user', fn($q) => $q->where('email', 'like', "%{$this->search}%")))
            ->when($this->filter !== 'all', fn($q) => $q->where('status', $this->filter))
            ->latest();

        return view('livewire.admin.subscriptions', [
            'subscriptions' => $query->paginate(20),
        ]);
    }
}
