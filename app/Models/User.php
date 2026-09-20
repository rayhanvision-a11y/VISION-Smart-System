<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'avatar',
        'theme_preference',
        'locale',
        'timezone',
        'notify_on_assign',
        'notify_on_resolve',
        'notify_on_message',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_on_assign' => 'boolean',
            'notify_on_resolve' => 'boolean',
            'notify_on_message' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdminOnly(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isNoc(): bool
    {
        return $this->role === 'noc';
    }

    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    public function isCallCenter(): bool
    {
        return $this->role === 'call_center';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isSeniorSupervisor(): bool
    {
        return $this->role === 'senior_supervisor';
    }

    public function isSupervisorLevel(): bool
    {
        return in_array($this->role, ['supervisor', 'senior_supervisor']);
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            if (file_exists(public_path('storage/' . $this->avatar))) {
                return asset('storage/' . $this->avatar);
            }
            if (file_exists(public_path($this->avatar))) {
                return asset($this->avatar);
            }
        }

        $name = $this->name ?: 'User';
        $bgColors = ['f97316', 'ec4899', '6366f1', '8b5cf6', '10b981', '06b6d4', '3b82f6', 'f59e0b', 'ef4444'];
        $colorIndex = abs(crc32($name)) % count($bgColors);
        $bgHex = $bgColors[$colorIndex];
        $uiAvatarUrl = "https://ui-avatars.com/api/" . urlencode($name) . "/128/{$bgHex}/ffffff?bold=true";

        if ($this->email) {
            $hash = md5(strtolower(trim($this->email)));
            $encodedDefault = urlencode($uiAvatarUrl);
            return "https://www.gravatar.com/avatar/{$hash}?s=128&d={$encodedDefault}";
        }

        return $uiAvatarUrl;
    }
}
