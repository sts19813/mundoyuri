<?php

namespace App\Models;

use App\Support\SeriesMedia;
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

    public function thumbnailUrl(): ?string
    {
        return SeriesMedia::publicUrl($this->thumbnail_image);
    }

    public function previewMediaUrl(string $randomSize = '400/250'): string
    {
        if ($this->thumbnailUrl()) {
            return $this->thumbnailUrl();
        }

        $series = $this->resolvedSeries();

        return $series?->coverMediaUrl()
            ?: $series?->bannerMediaUrl()
            ?: $this->randomImageUrl($randomSize);
    }

    public function previewMediaType(): string
    {
        if ($this->thumbnailUrl()) {
            return 'image';
        }

        $series = $this->resolvedSeries();

        if ($series?->coverMediaUrl()) {
            return $series->coverMediaType() ?: 'image';
        }

        if ($series?->bannerMediaUrl()) {
            return $series->bannerMediaType() ?: 'image';
        }

        return 'image';
    }

    public function imageUrl(string $randomSize = '400/250'): string
    {
        if ($this->thumbnailUrl()) {
            return $this->thumbnailUrl();
        }

        $series = $this->resolvedSeries();

        if ($series?->coverMediaUrl() && $series->coverMediaType() !== 'video') {
            return $series->coverMediaUrl();
        }

        if ($series?->bannerMediaUrl() && $series->bannerMediaType() !== 'video') {
            return $series->bannerMediaUrl();
        }

        return $this->randomImageUrl($randomSize);
    }

    private function resolvedSeries(): ?Series
    {
        return $this->relationLoaded('series')
            ? $this->getRelation('series')
            : $this->series;
    }

    private function randomImageUrl(string $size): string
    {
        return "https://picsum.photos/{$size}?episode={$this->id}";
    }
}
