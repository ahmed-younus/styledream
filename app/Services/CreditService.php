<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreditTransaction;

class CreditService
{
    public function hasCredits(User $user, int $amount = 1): bool
    {
        return $user->credits >= $amount;
    }

    public function useCredits(User $user, int $amount, string $description, ?string $referenceId = null): bool
    {
        if (!$this->hasCredits($user, $amount)) {
            return false;
        }

        $user->decrement('credits', $amount);

        CreditTransaction::record($user, -$amount, CreditTransaction::TYPE_TRY_ON, $description, $referenceId);

        return true;
    }

    public function addCredits(User $user, int $amount, string $type, string $description, ?string $referenceId = null): void
    {
        $user->increment('credits', $amount);

        CreditTransaction::record($user, $amount, $type, $description, $referenceId);
    }

    public function refundCredits(User $user, int $amount, string $description, ?string $referenceId = null): void
    {
        $this->addCredits($user, $amount, CreditTransaction::TYPE_REFUND, $description, $referenceId);
    }

    public function claimDailyCredit(User $user): array
    {
        if (!$user->canClaimDailyCredit()) {
            return ['success' => false, 'message' => 'Already claimed today'];
        }

        $isConsecutiveDay = $user->last_credit_claimed_at &&
            $user->last_credit_claimed_at->isYesterday();

        if ($isConsecutiveDay) {
            $user->increment('current_streak');
        } else {
            $user->current_streak = 1;
        }

        $user->last_credit_claimed_at = now();
        $user->save();

        // Base credit
        $this->addCredits($user, 1, CreditTransaction::TYPE_DAILY_CLAIM, 'Daily free credit');

        // Streak bonus (every 7 days)
        $bonusCredits = 0;
        if ($user->current_streak % 7 === 0) {
            $bonusCredits = 3;
            $this->addCredits($user, $bonusCredits, CreditTransaction::TYPE_STREAK_BONUS, "{$user->current_streak}-day streak bonus");
        }

        return [
            'success' => true,
            'streak' => $user->current_streak,
            'bonusCredits' => $bonusCredits,
        ];
    }
}
