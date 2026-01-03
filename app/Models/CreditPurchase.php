<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'pack',
        'credits',
        'amount',
        'currency',
        'stripe_session_id',
        'stripe_payment_intent',
        'status',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted price (converts cents to dollars)
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->amount / 100, 2);
    }
}
