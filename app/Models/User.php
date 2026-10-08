<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'designation',
        'office_id',
        'pop_office_id',
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
        'fcm_token',
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
            'last_login_at' => 'datetime',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function popOffice()
    {
        return $this->belongsTo(PopOffice::class, 'pop_office_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function activeTicketsCount(): int
    {
        return Ticket::forUser($this)->whereNotIn('status', ['resolved', 'closed'])->count();
    }

    public function totalTicketsCount(): int
    {
        return Ticket::forUser($this)->count();
    }

    public function resolvedTicketsCount(): int
    {
        return Ticket::forUser($this)->whereIn('status', ['resolved', 'closed'])->count();
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

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function userLocation()
    {
        return $this->hasOne(UserLocation::class);
    }

    public const TEAMS = [
        'IT Team' => 'IT Team',
        'NOC team' => 'NOC team',
        'Call center' => 'Call center',
        'Supervisor Team' => 'Supervisor Team',
    ];

    public const SHIFTS = [
        'unassigned'  => 'Unassigned Pool',
        '1st_shift'   => '1st Shift (9:00 AM - 6:00 PM)',
        '2nd_shift'   => '2nd Shift (2:00 PM - 10:00 PM)',
        'day_shift'   => '1st Shift (9:00 AM - 6:00 PM)',
        'night_shift' => '2nd Shift (2:00 PM - 10:00 PM)',
        'off_duty'    => 'Duty Complete',
        'day_off'     => 'Day Off',
    ];

    public function ensureCurrentShiftDate(): void
    {
        $today = now()->toDateString();
        if ($this->shift_date !== $today) {
            $this->forceFill([
                'current_shift' => 'unassigned',
                'shift_date' => $today,
            ])->save();
        }
    }

    public function isOnDuty(): bool
    {
        $this->ensureCurrentShiftDate();

        $shift = $this->current_shift ?? 'unassigned';
        if (in_array($shift, ['day_off', 'unassigned', 'off_duty'])) {
            return false;
        }

        $now = now();
        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $timeMinutes = $hour * 60 + $minute;

        if (in_array($shift, ['1st_shift', 'day_shift'])) {
            // 1st Shift: 7:00 AM (420 mins) to 6:00 PM (1080 mins). After 6:00 PM, shift ends
            return $timeMinutes >= 420 && $timeMinutes < 1080;
        }

        if (in_array($shift, ['2nd_shift', 'night_shift'])) {
            // 2nd Shift: 12:00 PM entry (720 mins) to 10:00 PM (1320 mins). After 10:00 PM, shift ends
            return $timeMinutes >= 720 && $timeMinutes < 1320;
        }

        return false;
    }

    public function avatarUrl(): string
    {
        $avatar = $this->attributes['avatar'] ?? null;
        if ($avatar) {
            if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
                return $avatar;
            }
            if (file_exists(public_path('storage/'.$avatar))) {
                return url('storage/'.$avatar);
            }
            if (file_exists(public_path($avatar))) {
                return url($avatar);
            }
            if (\Storage::disk('public')->exists($avatar)) {
                return url('storage/'.$avatar);
            }
        }

        $name = $this->attributes['name'] ?? 'User';
        $bgColors = ['f97316', 'ec4899', '6366f1', '8b5cf6', '10b981', '06b6d4', '3b82f6', 'f59e0b', 'ef4444'];
        $colorIndex = abs(crc32($name)) % count($bgColors);
        $bgHex = $bgColors[$colorIndex];
        $uiAvatarUrl = 'https://ui-avatars.com/api/'.urlencode($name)."/128/{$bgHex}/ffffff?bold=true";

        $email = $this->attributes['email'] ?? null;
        if ($email) {
            $hash = md5(strtolower(trim($email)));
            $encodedDefault = urlencode($uiAvatarUrl);

            return "https://www.gravatar.com/avatar/{$hash}?s=128&d={$encodedDefault}";
        }

        return $uiAvatarUrl;
    }
}
