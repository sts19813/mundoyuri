<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPresenceSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'started_at',
        'last_seen_at',
        'ended_at',
        'total_seconds',
        'heartbeat_count',
        'current_path',
        'current_title',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'ended_at' => 'datetime',
            'total_seconds' => 'integer',
            'heartbeat_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
