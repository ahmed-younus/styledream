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
        'display_name',
        'bio',
        'locale',
        'currency',
        'email',
        'password',
        'onboarding_completed',
        'avatar_url',
        'google_id',
        'credits',
        'subscription_credits',
        'subscription_tier',
        'subscription_ends_at',
        'trial_activated_at',
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
            'trial_activated_at' => 'datetime',
            'last_credit_claimed_at' => 'datetime',
            'onboarding_completed' => 'boolean',
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

    public function creditPurchases(): HasMany
    {
        return $this->hasMany(CreditPurchase::class);
    }

    public function outfitPosts(): HasMany
    {
        return $this->hasMany(OutfitPost::class);
    }

    public function savedOutfits(): HasMany
    {
        return $this->hasMany(SavedOutfit::class);
    }

    public function outfitLikes(): HasMany
    {
        return $this->hasMany(OutfitLike::class);
    }

    public function outfitComments(): HasMany
    {
        return $this->hasMany(OutfitComment::class);
    }

    public function outfitRatings(): HasMany
    {
        return $this->hasMany(OutfitRating::class);
    }

    public function shareEvents(): HasMany
    {
        return $this->hasMany(ShareEvent::class);
    }

    public function postReports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    public function avatars(): HasMany
    {
        return $this->hasMany(Avatar::class);
    }

    public function defaultAvatar(): ?Avatar
    {
        return $this->avatars()->where('is_default', true)->first()
            ?? $this->avatars()->first();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()->where('status', 'active')->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get total available credits (subscription + purchased)
     */
    public function totalCredits(): int
    {
        return ($this->subscription_credits ?? 0) + ($this->credits ?? 0);
    }

    /**
     * Check if user has enough credits (subscription + purchased)
     */
    public function hasCredits(int $amount = 1): bool
    {
        return $this->totalCredits() >= $amount;
    }

    /**
     * Use credits with priority: subscription credits first, then purchased credits
     */
    public function useCredits(int $amount = 1): bool
    {
        if (!$this->hasCredits($amount)) {
            return false;
        }

        $remaining = $amount;

        // 1. Use subscription credits first (reset monthly)
        if ($this->subscription_credits > 0) {
            $fromSubscription = min($this->subscription_credits, $remaining);
            $this->decrement('subscription_credits', $fromSubscription);
            $remaining -= $fromSubscription;
        }

        // 2. Use purchased credits for remainder (lifetime credits)
        if ($remaining > 0) {
            $this->decrement('credits', $remaining);
        }

        return true;
    }

    /**
     * Add purchased credits (lifetime, never expire)
     */
    public function addCredits(int $amount): void
    {
        $this->increment('credits', $amount);
    }

    /**
     * Add or reset subscription credits (monthly)
     */
    public function setSubscriptionCredits(int $amount): void
    {
        $this->update(['subscription_credits' => $amount]);
    }

    /**
     * Get purchased credits only (lifetime)
     */
    public function getPurchasedCredits(): int
    {
        return $this->credits ?? 0;
    }

    /**
     * Get subscription credits only (monthly)
     */
    public function getSubscriptionCredits(): int
    {
        return $this->subscription_credits ?? 0;
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

    public function getDisplayNameAttribute($value): string
    {
        return $value ?? $this->name;
    }

    public function hasLikedPost(OutfitPost $post): bool
    {
        return $this->outfitLikes()->where('outfit_post_id', $post->id)->exists();
    }

    public function getRatingForPost(OutfitPost $post): ?int
    {
        $rating = $this->outfitRatings()->where('outfit_post_id', $post->id)->first();
        return $rating ? $rating->rating : null;
    }

    public function getTotalLikesReceivedAttribute(): int
    {
        return $this->outfitPosts()->sum('likes_count');
    }

    public function getPublicPostsCountAttribute(): int
    {
        return $this->outfitPosts()->public()->count();
    }
}
