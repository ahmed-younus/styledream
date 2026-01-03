<?php

namespace App\Livewire\Admin;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Admin Login')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $error = '';

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
        ];

        if (Auth::guard('admin')->attempt($credentials, $this->remember)) {
            $admin = Auth::guard('admin')->user();

            // Update last login info
            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Log activity
            $admin->logActivity(AdminActivityLog::ACTION_LOGIN, null, null, null, null, 'Admin logged in');

            session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        $this->error = 'Invalid credentials or account is deactivated.';
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
