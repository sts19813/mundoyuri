<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'created_by',
        'approved_by',
        'title',
        'slug',
        'season_number',
        'episode_number',
        'release_date',
        'duration_minutes',
        'thumbnail_image',
        'description',
        'moderation_status',
        'moderation_notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(EpisodeSource::class)->orderBy('sort_order')->orderBy('id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
