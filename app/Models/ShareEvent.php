<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_post_id',
        'try_on_id',
        'platform',
    ];

    // Platform constants
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_FACEBOOK = 'facebook';
    const PLATFORM_TWITTER = 'twitter';
    const PLATFORM_WHATSAPP = 'whatsapp';
    const PLATFORM_COPY_LINK = 'copy_link';
    const PLATFORM_DOWNLOAD = 'download';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfitPost(): BelongsTo
    {
        return $this->belongsTo(OutfitPost::class);
    }

    public function tryOn(): BelongsTo
    {
        return $this->belongsTo(TryOn::class);
    }

    public static function record(
        int $userId,
        string $platform,
        ?int $outfitPostId = null,
        ?int $tryOnId = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'platform' => $platform,
            'outfit_post_id' => $outfitPostId,
            'try_on_id' => $tryOnId,
        ]);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
