<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PostReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'status',
    ];

    // Reason constants
    const REASON_SPAM = 'spam';
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_HARASSMENT = 'harassment';
    const REASON_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_DISMISSED = 'dismissed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function markAsReviewed(): void
    {
        $this->update(['status' => self::STATUS_REVIEWED]);
    }

    public function dismiss(): void
    {
        $this->update(['status' => self::STATUS_DISMISSED]);
    }

    public static function reportPost(User $user, OutfitPost $post, string $reason, ?string $details = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'reportable_type' => OutfitPost::class,
            'reportable_id' => $post->id,
            'reason' => $reason,
            'details' => $details,
        ]);
    }

    public static function reportComment(User $user, OutfitComment $comment, string $reason, ?string $details = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'reportable_type' => OutfitComment::class,
            'reportable_id' => $comment->id,
            'reason' => $reason,
            'details' => $details,
        ]);
    }
}
