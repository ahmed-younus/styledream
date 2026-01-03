<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Studio;
use App\Livewire\Wardrobe;
use App\Livewire\Pricing;
use App\Livewire\Profile;
use App\Livewire\StyleFeed;
use App\Livewire\MyOutfits;
use App\Livewire\BrandPromo;
use App\Livewire\TryOnHistory;
use App\Livewire\Terms;
use App\Livewire\Privacy;
use App\Livewire\Onboarding;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PaymentController;

// Public routes
Route::get('/', Home::class)->name('home');
Route::get('/pricing', Pricing::class)->name('pricing');
Route::get('/feed', StyleFeed::class)->name('feed');
Route::get('/brands', BrandPromo::class)->name('brands');
Route::get('/terms', Terms::class)->name('terms');
Route::get('/privacy', Privacy::class)->name('privacy');

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
    Route::get('/onboarding', Onboarding::class)->name('onboarding');
    Route::get('/studio', Studio::class)->name('studio');
    Route::get('/try-on', Studio::class)->name('try-on');
    Route::get('/wardrobe', Wardrobe::class)->name('wardrobe');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/my-outfits', MyOutfits::class)->name('my-outfits');
    Route::get('/history', TryOnHistory::class)->name('history');

    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Payment routes - Credit Packs
    Route::post('/checkout/credits', [PaymentController::class, 'createCheckout'])->name('checkout.credits');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');

    // Subscription routes
    Route::post('/checkout/subscription', [PaymentController::class, 'createSubscription'])->name('checkout.subscription');
    Route::get('/subscription/success', [PaymentController::class, 'subscriptionSuccess'])->name('subscription.success');
    Route::get('/billing', [PaymentController::class, 'billingPortal'])->name('billing');
});

// Stripe webhook (CSRF excluded in bootstrap/app.php)
Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook'])->name('webhook.stripe');
