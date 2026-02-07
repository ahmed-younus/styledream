<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'credits' => 0, // Users start with 0 credits, get 5 after trial activation with card
        ]);

        Auth::login($user);

        return redirect()->route('onboarding');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.app', ['title' => 'Create Account']);
    }
}
