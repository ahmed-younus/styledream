<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\CreditTransaction;
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
            'credits' => 5,
        ]);

        // Record signup bonus
        CreditTransaction::record(
            $user,
            5,
            CreditTransaction::TYPE_SIGNUP_BONUS,
            'Welcome bonus - 5 free credits'
        );

        Auth::login($user);

        return redirect()->route('onboarding');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.app', ['title' => 'Create Account']);
    }
}
