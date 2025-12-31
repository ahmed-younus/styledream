<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    use HasFactory;

    const TYPE_SIGNUP_BONUS = 'signup_bonus';
    const TYPE_DAILY_CLAIM = 'daily_claim';
    const TYPE_STREAK_BONUS = 'streak_bonus';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_TRY_ON = 'try_on';
    const TYPE_REFUND = 'refund';
    const TYPE_REFERRAL = 'referral';

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'reference_id',
        'balance_after',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(User $user, int $amount, string $type, ?string $description = null, ?string $referenceId = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'reference_id' => $referenceId,
            'balance_after' => $user->credits,
        ]);
    }
}
