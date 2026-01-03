<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\AdminActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class UserEdit extends Component
{
    public ?User $user = null;
    public array $form = [];

    // Credit management
    public int $creditAmount = 0;
    public string $creditReason = '';

    public function mount(int $id)
    {
        $this->user = User::with(['subscriptions', 'tryOns'])->findOrFail($id);
        $this->form = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'credits' => $this->user->credits,
        ];
    }

    public function save()
    {
        $validated = $this->validate([
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|unique:users,email,' . $this->user->id,
        ]);

        $oldValues = $this->user->only(['name', 'email']);
        $this->user->update([
            'name' => $this->form['name'],
            'email' => $this->form['email'],
        ]);

        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_UPDATED,
            User::class,
            $this->user->id,
            $oldValues,
            ['name' => $this->form['name'], 'email' => $this->form['email']],
            'User details updated'
        );

        session()->flash('success', 'User updated successfully');
    }

    public function addCredits()
    {
        $this->validate([
            'creditAmount' => 'required|integer|min:1|max:10000',
            'creditReason' => 'nullable|string|max:255',
        ]);

        $oldCredits = $this->user->credits;
        $this->user->increment('credits', $this->creditAmount);
        $this->user->refresh();

        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_UPDATED,
            User::class,
            $this->user->id,
            ['credits' => $oldCredits],
            ['credits' => $this->user->credits],
            "Added {$this->creditAmount} credits" . ($this->creditReason ? ": {$this->creditReason}" : '')
        );

        session()->flash('success', "Added {$this->creditAmount} credits to user");
        $this->reset(['creditAmount', 'creditReason']);
    }

    public function deductCredits()
    {
        $this->validate([
            'creditAmount' => 'required|integer|min:1|max:' . $this->user->credits,
            'creditReason' => 'nullable|string|max:255',
        ], [
            'creditAmount.max' => 'Cannot deduct more than the user has (' . $this->user->credits . ' credits)',
        ]);

        $oldCredits = $this->user->credits;
        $this->user->decrement('credits', $this->creditAmount);
        $this->user->refresh();

        auth('admin')->user()->logActivity(
            AdminActivityLog::ACTION_UPDATED,
            User::class,
            $this->user->id,
            ['credits' => $oldCredits],
            ['credits' => $this->user->credits],
            "Deducted {$this->creditAmount} credits" . ($this->creditReason ? ": {$this->creditReason}" : '')
        );

        session()->flash('success', "Deducted {$this->creditAmount} credits from user");
        $this->reset(['creditAmount', 'creditReason']);
    }

    public function render()
    {
        return view('livewire.admin.user-edit')->title("Edit User: {$this->user->name}");
    }
}
