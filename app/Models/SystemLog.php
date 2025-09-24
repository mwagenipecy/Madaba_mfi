<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemLog extends Model
{
    protected $fillable = [
        'action',
        'description',
        'level',
        'model_type',
        'model_id',
        'user_id',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Create a system log entry
     */
    public static function log(string $action, string $description, string $level = 'info', $model = null, $userId = null, array $data = []): self
    {
        return self::create([
            'action' => $action,
            'description' => $description,
            'level' => $level,
            'model_type' => ($model && is_object($model)) ? get_class($model) : null,
            'model_id' => ($model && is_object($model)) ? $model->id : null,
            'user_id' => $userId ?? auth()->id(),
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get the level badge color
     */
    public function getLevelBadgeColorAttribute(): string
    {
        return match($this->level) {
            'info' => 'bg-blue-100 text-blue-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'error' => 'bg-red-100 text-red-800',
            'critical' => 'bg-red-100 text-red-900',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
