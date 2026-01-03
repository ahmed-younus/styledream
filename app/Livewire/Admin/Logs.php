<?php

namespace App\Livewire\Admin;

use App\Models\AdminActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Activity Logs')]
class Logs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public string $adminFilter = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedActionFilter()
    {
        $this->resetPage();
    }

    public function updatedAdminFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = AdminActivityLog::with('adminUser')
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhere('model_type', 'like', "%{$this->search}%")
                    ->orWhereHas('adminUser', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            });
        }

        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->adminFilter) {
            $query->where('admin_user_id', $this->adminFilter);
        }

        $logs = $query->paginate(50);

        $admins = \App\Models\AdminUser::orderBy('name')->get(['id', 'name']);
        $actions = AdminActivityLog::distinct()->pluck('action')->sort()->values();

        return view('livewire.admin.logs', [
            'logs' => $logs,
            'admins' => $admins,
            'actions' => $actions,
        ]);
    }

    public function getActionColor(string $action): string
    {
        return match ($action) {
            'create' => 'green',
            'update' => 'blue',
            'delete' => 'red',
            'login' => 'purple',
            'logout' => 'gray',
            'export' => 'yellow',
            'import' => 'indigo',
            default => 'gray',
        };
    }
}
