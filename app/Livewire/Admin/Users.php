<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\AdminActivityLog;
use App\Services\CreditService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Users')]
class Users extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'all'; // all, subscribed, free

    public string $sortBy = 'created_at';
    public string $sortDir = 'desc';

    // Modal states
    public bool $showCreditsModal = false;
    public ?int $selectedUserId = null;
    public int $creditsToAdd = 0;
    public string $creditReason = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function openCreditsModal(int $userId)
    {
        $this->selectedUserId = $userId;
        $this->creditsToAdd = 0;
        $this->creditReason = '';
        $this->showCreditsModal = true;
    }

    public function addCredits()
    {
        $this->validate([
            'creditsToAdd' => 'required|integer|min:1|max:10000',
            'creditReason' => 'required|string|max:255',
        ]);

        $user = User::find($this->selectedUserId);
        if (!$user) {
            return;
        }

        $creditService = app(CreditService::class);
        $creditService->addCredits($user, $this->creditsToAdd, 'admin', $this->creditReason);

        // Log activity
        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_CREDITS_ADDED,
            User::class,
            $user->id,
            ['credits' => $user->credits - $this->creditsToAdd],
            ['credits' => $user->credits],
            "Added {$this->creditsToAdd} credits: {$this->creditReason}"
        );

        $this->showCreditsModal = false;
        $this->dispatch('notify', message: "Added {$this->creditsToAdd} credits to {$user->name}");
    }

    public function toggleBan(int $userId)
    {
        $user = User::find($userId);
        if (!$user) return;

        $user->update(['is_banned' => !$user->is_banned]);

        $action = $user->is_banned ? AdminActivityLog::ACTION_USER_BANNED : AdminActivityLog::ACTION_USER_UNBANNED;
        auth('admin')->user()->logActivity($action, User::class, $user->id, null, null, $user->is_banned ? 'User banned' : 'User unbanned');
    }

    public function render()
    {
        $query = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filter === 'subscribed', fn($q) => $q->whereHas('subscriptions', fn($q) => $q->where('status', 'active')))
            ->when($this->filter === 'free', fn($q) => $q->whereDoesntHave('subscriptions', fn($q) => $q->where('status', 'active')))
            ->orderBy($this->sortBy, $this->sortDir);

        return view('livewire.admin.users', [
            'users' => $query->paginate(20),
        ]);
    }
}
