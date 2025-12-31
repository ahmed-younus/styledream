<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'google_id',
        'credits',
        'subscription_tier',
        'subscription_ends_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'current_streak',
        'last_credit_claimed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'last_credit_claimed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tryOns(): HasMany
    {
        return $this->hasMany(TryOn::class);
    }

    public function wardrobeItems(): HasMany
    {
        return $this->hasMany(WardrobeItem::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function hasCredits(int $amount = 1): bool
    {
        return $this->credits >= $amount;
    }

    public function useCredits(int $amount = 1): bool
    {
        if (!$this->hasCredits($amount)) {
            return false;
        }
        $this->decrement('credits', $amount);
        return true;
    }

    public function addCredits(int $amount): void
    {
        $this->increment('credits', $amount);
    }

    public function canClaimDailyCredit(): bool
    {
        if (!$this->last_credit_claimed_at) {
            return true;
        }
        return $this->last_credit_claimed_at->lt(now()->startOfDay());
    }

    public function isPro(): bool
    {
        return in_array($this->subscription_tier, ['pro', 'premium']) 
            && (!$this->subscription_ends_at || $this->subscription_ends_at->isFuture());
    }
}
