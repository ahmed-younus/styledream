<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'stripe_subscription_id',
        'stripe_customer_id',
        'current_period_start',
        'current_period_end',
        'canceled_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_TRIALING = 'trialing';
    const STATUS_INCOMPLETE = 'incomplete';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPro(): bool
    {
        return $this->plan === 'pro' && $this->isActive();
    }

    public function isPremium(): bool
    {
        return $this->plan === 'premium' && $this->isActive();
    }

    public function onGracePeriod(): bool
    {
        return $this->canceled_at !== null
            && $this->current_period_end !== null
            && $this->current_period_end->isFuture();
    }

    public function getPlanDetails(): ?array
    {
        return config("subscriptions.plans.{$this->plan}");
    }
}
