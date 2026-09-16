<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = ['priority', 'response_hours', 'resolution_hours', 'escalate_on_breach'];

    protected $casts = [
        'escalate_on_breach' => 'boolean',
    ];

    public static function forPriority(string $priority): ?self
    {
        return static::where('priority', $priority)->first();
    }
}
