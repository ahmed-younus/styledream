<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryOn extends Model
{
    use HasFactory;

    const STATUS_QUEUED = 'queued';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'body_image_url',
        'garment_image_url',
        'garment_urls',
        'garment_categories',
        'result_image_url',
        'viewed_at',
        'status',
        'queue_position',
        'attempts',
        'processing_started_at',
        'error_message',
        'processing_time_ms',
        'credits_used',
        'garment_name',
        'garment_brand',
        'garment_category',
    ];

    protected $casts = [
        'garment_urls' => 'array',
        'garment_categories' => 'array',
        'processing_started_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeQueued($query)
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processing_started_at' => now(),
        ]);
    }

    public function markAsCompleted(string $resultUrl, int $processingTime): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'result_image_url' => $resultUrl,
            'processing_time_ms' => $processingTime,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function canRetry(int $maxAttempts = 3): bool
    {
        return $this->attempts < $maxAttempts;
    }

    /**
     * Get all garment URLs - backwards compatible
     */
    public function getAllGarmentUrls(): array
    {
        if (!empty($this->garment_urls)) {
            return $this->garment_urls;
        }

        // Fallback to single garment URL
        return $this->garment_image_url ? [$this->garment_image_url] : [];
    }

    /**
     * Get all garment categories - returns array matching garment_urls
     */
    public function getAllGarmentCategories(): array
    {
        if (!empty($this->garment_categories)) {
            return $this->garment_categories;
        }

        // Fallback: return 'auto' for each garment
        return array_fill(0, count($this->getAllGarmentUrls()), 'auto');
    }

    /**
     * Check if this try-on is in user's queue
     */
    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }
}
