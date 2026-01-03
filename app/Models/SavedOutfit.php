<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedOutfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outfit_post_id',
        'try_on_id',
        'image_url',
        'name',
        'notes',
        'garment_data',
    ];

    protected $casts = [
        'garment_data' => 'array',
    ];

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

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
