<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id', 'latitude', 'longitude',
        'accuracy_meters', 'battery_level', 'speed_mps', 'is_sharing',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_sharing' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
