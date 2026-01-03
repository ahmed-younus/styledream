<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OutfitComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_post_id',
        'content',
        'is_hidden',
        'is_reported',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'is_reported' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfitPost(): BelongsTo
    {
        return $this->belongsTo(OutfitPost::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(PostReport::class, 'reportable');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    protected static function booted(): void
    {
        static::created(function (OutfitComment $comment) {
            $comment->outfitPost->incrementComments();
        });

        static::deleted(function (OutfitComment $comment) {
            $comment->outfitPost->decrementComments();
        });
    }
}
