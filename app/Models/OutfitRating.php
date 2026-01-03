<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutfitRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_post_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfitPost(): BelongsTo
    {
        return $this->belongsTo(OutfitPost::class);
    }

    protected static function booted(): void
    {
        static::created(function (OutfitRating $rating) {
            $rating->outfitPost->updateRatingStats();
        });

        static::updated(function (OutfitRating $rating) {
            $rating->outfitPost->updateRatingStats();
        });

        static::deleted(function (OutfitRating $rating) {
            $rating->outfitPost->updateRatingStats();
        });
    }
}
