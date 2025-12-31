<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Studio;
use App\Livewire\Wardrobe;
use App\Livewire\Pricing;
use App\Livewire\Profile;
use App\Http\Controllers\Auth\GoogleController;

// Public routes
Route::get('/', Home::class)->name('home');
Route::get('/pricing', Pricing::class)->name('pricing');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/studio', Studio::class)->name('studio');
    Route::get('/try-on', Studio::class)->name('try-on');
    Route::get('/wardrobe', Wardrobe::class)->name('wardrobe');
    Route::get('/profile', Profile::class)->name('profile');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
