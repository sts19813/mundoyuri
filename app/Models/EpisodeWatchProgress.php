<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeWatchProgress extends Model
{
    use HasFactory;

    protected $table = 'episode_watch_progress';

    protected $fillable = [
        'user_id',
        'episode_id',
        'episode_source_id',
        'provider',
        'position_seconds',
        'duration_seconds',
        'progress_percent',
        'completed',
        'last_watched_at',
    ];

    protected function casts(): array
    {
        return [
            'position_seconds' => 'integer',
            'duration_seconds' => 'integer',
            'progress_percent' => 'float',
            'completed' => 'boolean',
            'last_watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(EpisodeSource::class, 'episode_source_id');
    }
}
