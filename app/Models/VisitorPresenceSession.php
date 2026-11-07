<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorPresenceSession extends Model
{
    protected $fillable = [
        'visitor_id',
        'session_id',
        'started_at',
        'last_seen_at',
        'current_path',
        'current_title',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}
