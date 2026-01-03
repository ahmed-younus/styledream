<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canModerateContent(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_MODERATOR]);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function logActivity(string $action, ?string $modelType = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): AdminActivityLog
    {
        return AdminActivityLog::create([
            'admin_user_id' => $this->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'red',
            self::ROLE_ADMIN => 'blue',
            self::ROLE_MODERATOR => 'green',
            default => 'gray',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MODERATOR => 'Moderator',
            default => 'Unknown',
        };
    }
}
