<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Users;
use App\Livewire\Admin\UserEdit;
use App\Livewire\Admin\Subscriptions;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Pricing;
use App\Livewire\Admin\ContentModeration;
use App\Livewire\Admin\Analytics;
use App\Livewire\Admin\Logs;
use App\Livewire\Admin\AdminUsers;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminGuest;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Secure admin panel routes with custom URL prefix for security.
| URL: /sd-control-panel (not guessable like /admin)
|
*/

// Guest routes (login)
Route::middleware(AdminGuest::class)->group(function () {
    Route::get('/login', Login::class)->name('admin.login');
});

// Authenticated admin routes
Route::middleware(AdminAuth::class)->group(function () {

    // Dashboard
    Route::get('/', Dashboard::class)->name('admin.dashboard');

    // User Management (admin+)
    Route::middleware(AdminAuth::class . ':admin')->group(function () {
        Route::get('/users', Users::class)->name('admin.users');
        Route::get('/users/{id}', UserEdit::class)->name('admin.users.edit');
        Route::get('/subscriptions', Subscriptions::class)->name('admin.subscriptions');
    });

    // Content Moderation (moderator+)
    Route::middleware(AdminAuth::class . ':moderator')->group(function () {
        Route::get('/content', ContentModeration::class)->name('admin.content');
    });

    // Analytics (admin+)
    Route::middleware(AdminAuth::class . ':admin')->group(function () {
        Route::get('/analytics', Analytics::class)->name('admin.analytics');
    });

    // Activity Logs (admin+)
    Route::middleware(AdminAuth::class . ':admin')->group(function () {
        Route::get('/logs', Logs::class)->name('admin.logs');
    });

    // Super Admin only
    Route::middleware(AdminAuth::class . ':super_admin')->group(function () {
        Route::get('/settings', Settings::class)->name('admin.settings');
        Route::get('/pricing', Pricing::class)->name('admin.pricing');
        Route::get('/team', AdminUsers::class)->name('admin.team');
    });

    // Logout
    Route::post('/logout', function () {
        $admin = auth('admin')->user();
        if ($admin) {
            $admin->logActivity('logout');
        }
        auth('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('admin.logout');
});
