<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'image_url',
        'title',
        'tagline',
        'cta_text',
        'cta_url',
        'is_active',
        'priority',
        'impressions',
        'clicks',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderBy('id');
    }

    public function recordImpression(): void
    {
        $this->increment('impressions');
    }

    public function recordClick(): void
    {
        $this->increment('clicks');
    }

    public function getCtrAttribute(): float
    {
        if ($this->impressions === 0) return 0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
