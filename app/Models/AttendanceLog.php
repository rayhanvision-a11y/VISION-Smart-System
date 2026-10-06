<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_no',
        'person_name',
        'event_type',
        'event_time',
        'door_name',
        'device_name',
        'shift_assigned',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'raw_data'   => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
