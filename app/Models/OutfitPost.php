<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OutfitPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'try_on_id',
        'image_url',
        'caption',
        'visibility',
        'likes_count',
        'comments_count',
        'avg_rating',
        'ratings_count',
        'is_sponsored',
        'brand_id',
        'is_hidden',
        'tags',
    ];

    protected $casts = [
        'is_sponsored' => 'boolean',
        'is_hidden' => 'boolean',
        'tags' => 'array',
        'avg_rating' => 'decimal:2',
    ];

    // Visibility constants
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryOn(): BelongsTo
    {
        return $this->belongsTo(TryOn::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(OutfitLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OutfitComment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(OutfitRating::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(PostReport::class, 'reportable');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC)
                     ->where('is_hidden', false);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeFeed($query)
    {
        return $query->public()
                     ->with(['user:id,name,display_name,avatar_url', 'tryOn:id,garment_name,garment_brand'])
                     ->latest();
    }

    // Helpers
    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getRatingBy(?User $user): ?int
    {
        if (!$user) return null;
        $rating = $this->ratings()->where('user_id', $user->id)->first();
        return $rating ? $rating->rating : null;
    }

    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    public function incrementComments(): void
    {
        $this->increment('comments_count');
    }

    public function decrementComments(): void
    {
        $this->decrement('comments_count');
    }

    public function updateRatingStats(): void
    {
        $stats = $this->ratings()
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        $this->update([
            'ratings_count' => $stats->count ?? 0,
            'avg_rating' => $stats->average ?? 0,
        ]);
    }
}
