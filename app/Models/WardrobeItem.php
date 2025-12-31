<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardrobeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'brand',
        'image_url',
        'original_url',
        'price',
        'category',
        'try_on_count',
        'last_tried_at',
        'is_favorite',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_favorite' => 'boolean',
        'last_tried_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incrementTryOnCount(): void
    {
        $this->increment('try_on_count');
        $this->update(['last_tried_at' => now()]);
    }

    public function toggleFavorite(): void
    {
        $this->update(['is_favorite' => !$this->is_favorite]);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
