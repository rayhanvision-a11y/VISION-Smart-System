<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTechnicianTeam extends Model
{
    protected $table = 'daily_technician_teams';

    protected $fillable = [
        'duty_date',
        'category',
        'team_name',
        'leader_id',
        'member_1_id',
        'member_2_id',
        'area',
        'vehicle_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'duty_date' => 'date',
    ];

    public const CATEGORIES = [
        'complain' => [
            'label' => 'Complain Team',
            'label_bn' => 'অভিযোগ সমাধান টিম',
            'icon' => '🛠️',
            'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
            'color' => '#d97706',
        ],
        'new_connection' => [
            'label' => 'New Connection Team',
            'label_bn' => 'নতুন সংযোগ টিম',
            'icon' => '🔌',
            'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
            'color' => '#059669',
        ],
        'line_transfer' => [
            'label' => 'Transfer Team',
            'label_bn' => 'লাইন ট্রান্সফার টিম',
            'icon' => '🔄',
            'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
            'color' => '#2563eb',
        ],
        'transfer' => [
            'label' => 'Transfer Team',
            'label_bn' => 'লাইন ট্রান্সফার টিম',
            'icon' => '🔄',
            'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
            'color' => '#2563eb',
        ],
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function member1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_1_id');
    }

    public function member2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_2_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all active members in the team (Leader + Member 1 + Member 2).
     */
    public function membersCollection()
    {
        return collect([$this->leader, $this->member1, $this->member2])->filter()->values();
    }

    /**
     * Check if a specific user is in this team.
     */
    public function hasUser(int $userId): bool
    {
        return $this->leader_id === $userId || $this->member_1_id === $userId || $this->member_2_id === $userId;
    }

    public function isLeader(int $userId): bool
    {
        return $this->leader_id === $userId;
    }

    public function getCategoryMeta(): array
    {
        return self::allCategories()[$this->category] ?? self::CATEGORIES[$this->category] ?? [
            'label' => ucfirst(str_replace('_', ' ', $this->category)),
            'label_bn' => ucfirst(str_replace('_', ' ', $this->category)),
            'icon' => '👷',
            'badge' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            'color' => '#64748b',
        ];
    }

    /**
     * All categories (DB-managed + hardcoded fallback), keyed by key.
     * Cached per request to avoid repeated queries while rendering lists.
     */
    public static function allCategories(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $cache = self::CATEGORIES;
        try {
            if (\Schema::hasTable('squad_categories')) {
                foreach (SquadCategory::activeOrdered() as $cat) {
                    $cache[$cat->key] = $cat->toMetaArray();
                }
            }
        } catch (\Throwable $e) {
            // ignore — fall back to the constant
        }
        return $cache;
    }


    public function getCategoryLabel(): string
    {
        $meta = $this->getCategoryMeta();
        return $meta['label'];
    }

    public function getCategoryIcon(): string
    {
        $meta = $this->getCategoryMeta();
        return $meta['icon'];
    }
}
