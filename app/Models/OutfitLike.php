<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutfitLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_post_id',
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
        static::created(function (OutfitLike $like) {
            $like->outfitPost->incrementLikes();
        });

        static::deleted(function (OutfitLike $like) {
            $like->outfitPost->decrementLikes();
        });
    }
}
