<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'description', 'properties', 'ip_address', 'user_agent',
    ];

    protected $casts = ['properties' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        string $description,
        ?Model $model = null,
        array $properties = []
    ): void {
        try {
            static::create([
                'user_id'    => auth()->id(),
                'action'     => $action,
                'model_type' => $model ? class_basename($model) : null,
                'model_id'   => $model?->getKey(),
                'description'=> $description,
                'properties' => empty($properties) ? null : $properties,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 300),
            ]);
        } catch (\Throwable) {
            // Never break main flow due to logging failure
        }
    }
}
