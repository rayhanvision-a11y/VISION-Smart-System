<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SquadCategory extends Model
{
    protected $fillable = ['key', 'label', 'label_bn', 'icon', 'color', 'badge', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function activeOrdered()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('label')->get();
    }

    public function toMetaArray(): array
    {
        return [
            'label' => $this->label,
            'label_bn' => $this->label_bn ?: $this->label,
            'icon' => $this->icon ?: '👷',
            'color' => $this->color ?: '#64748b',
            'badge' => $this->badge ?: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
        ];
    }
}
