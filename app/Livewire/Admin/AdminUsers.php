<?php

namespace App\Livewire\Admin;

use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Admin Users')]
class AdminUsers extends Component
{
    use WithPagination;

    public string $search = '';

    // Create/Edit Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = AdminUser::ROLE_ADMIN;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'role']);
        $this->role = AdminUser::ROLE_ADMIN;
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $admin = AdminUser::findOrFail($id);

        $this->editingId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->role = $admin->role;
        $this->password = '';

        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admin_users,email' . ($this->editingId ? ",{$this->editingId}" : ''),
            'role' => 'required|in:' . implode(',', [AdminUser::ROLE_SUPER_ADMIN, AdminUser::ROLE_ADMIN, AdminUser::ROLE_MODERATOR]),
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $admin = AdminUser::findOrFail($this->editingId);
            $admin->update($data);

            Auth::guard('admin')->user()->logActivity(
                'update',
                AdminUser::class,
                $admin->id,
                "Updated admin user: {$admin->name}"
            );

            session()->flash('success', 'Admin user updated successfully.');
        } else {
            $admin = AdminUser::create($data);

            Auth::guard('admin')->user()->logActivity(
                'create',
                AdminUser::class,
                $admin->id,
                "Created new admin user: {$admin->name} ({$admin->role})"
            );

            session()->flash('success', 'Admin user created successfully.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'email', 'password', 'role']);
    }

    public function delete(int $id)
    {
        $admin = AdminUser::findOrFail($id);

        // Can't delete yourself
        if ($admin->id === Auth::guard('admin')->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        // Can't delete other super admins unless you're super admin
        if ($admin->isSuperAdmin() && !Auth::guard('admin')->user()->isSuperAdmin()) {
            session()->flash('error', 'Only super admins can delete other super admins.');
            return;
        }

        Auth::guard('admin')->user()->logActivity(
            'delete',
            AdminUser::class,
            $admin->id,
            "Deleted admin user: {$admin->name}"
        );

        $admin->delete();

        session()->flash('success', 'Admin user deleted successfully.');
    }

    public function render()
    {
        $query = AdminUser::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('role')
            ->orderBy('name');

        return view('livewire.admin.admin-users', [
            'admins' => $query->paginate(20),
            'roles' => [
                AdminUser::ROLE_SUPER_ADMIN => 'Super Admin',
                AdminUser::ROLE_ADMIN => 'Admin',
                AdminUser::ROLE_MODERATOR => 'Moderator',
            ],
        ]);
    }
}
