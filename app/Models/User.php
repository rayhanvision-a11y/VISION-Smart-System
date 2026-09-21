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
        'team',
        'current_shift',
        'shift_date',
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

    public const TEAMS = [
        'IT Team'         => 'IT Team',
        'NOC team'        => 'NOC team',
        'Call center'     => 'Call center',
        'Supervisor Team' => 'Supervisor Team',
    ];

    public const SHIFTS = [
        'unassigned'  => 'Unassigned Pool',
        'day_shift'   => 'Day Shift (9:00 AM - 6:00 PM)',
        'night_shift' => 'Night Shift (2:00 PM - 10:00 PM)',
        'day_off'     => 'Day Off',
    ];

    public function ensureCurrentShiftDate(): void
    {
        $today = now()->toDateString();
        if ($this->shift_date !== $today) {
            $this->forceFill([
                'current_shift' => 'unassigned',
                'shift_date'    => $today,
            ])->save();
        }
    }

    public function isOnDuty(): bool
    {
        $this->ensureCurrentShiftDate();

        $shift = $this->current_shift ?? 'unassigned';
        if (in_array($shift, ['day_off', 'unassigned'])) {
            return false;
        }

        $now = now();
        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $timeMinutes = $hour * 60 + $minute;

        if ($shift === 'day_shift') {
            // 9:00 AM (540 mins) to 6:00 PM (1080 mins)
            return $timeMinutes >= 540 && $timeMinutes <= 1080;
        }

        if ($shift === 'night_shift') {
            // 2:00 PM (840 mins = 14:00) to 10:00 PM (1320 mins = 22:00)
            return $timeMinutes >= 840 && $timeMinutes <= 1320;
        }

        return false;
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
