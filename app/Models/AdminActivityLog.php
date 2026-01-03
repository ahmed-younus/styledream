<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Common actions
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_VIEWED = 'viewed';
    const ACTION_EXPORTED = 'exported';
    const ACTION_SETTINGS_CHANGED = 'settings_changed';
    const ACTION_CREDITS_ADDED = 'credits_added';
    const ACTION_USER_BANNED = 'user_banned';
    const ACTION_USER_UNBANNED = 'user_unbanned';

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN => 'green',
            self::ACTION_LOGOUT => 'gray',
            self::ACTION_CREATED => 'blue',
            self::ACTION_UPDATED => 'yellow',
            self::ACTION_DELETED => 'red',
            self::ACTION_CREDITS_ADDED => 'purple',
            self::ACTION_USER_BANNED => 'red',
            self::ACTION_USER_UNBANNED => 'green',
            default => 'gray',
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN => 'Logged In',
            self::ACTION_LOGOUT => 'Logged Out',
            self::ACTION_CREATED => 'Created',
            self::ACTION_UPDATED => 'Updated',
            self::ACTION_DELETED => 'Deleted',
            self::ACTION_VIEWED => 'Viewed',
            self::ACTION_EXPORTED => 'Exported',
            self::ACTION_SETTINGS_CHANGED => 'Settings Changed',
            self::ACTION_CREDITS_ADDED => 'Credits Added',
            self::ACTION_USER_BANNED => 'User Banned',
            self::ACTION_USER_UNBANNED => 'User Unbanned',
            default => ucfirst($this->action),
        };
    }

    public function getModelLabelAttribute(): string
    {
        if (!$this->model_type) {
            return '-';
        }

        $shortName = class_basename($this->model_type);
        return $this->model_id ? "{$shortName} #{$this->model_id}" : $shortName;
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_user_id', $adminId);
    }
}
