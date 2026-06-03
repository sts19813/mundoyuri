<?php

namespace App\Models;

use App\Support\SeriesMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'genre_id',
        'created_by',
        'approved_by',
        'title',
        'slug',
        'content_type',
        'status',
        'description',
        'country_of_origin',
        'release_year',
        'total_seasons',
        'total_episodes',
        'duration_minutes',
        'banner_image',
        'cover_image',
        'trailer_url',
        'is_featured',
        'moderation_status',
        'moderation_notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bannerMediaPath(): ?string
    {
        return $this->banner_image ?: $this->cover_image;
    }

    public function bannerMediaUrl(): ?string
    {
        return SeriesMedia::publicUrl($this->bannerMediaPath());
    }

    public function bannerMediaType(): ?string
    {
        return SeriesMedia::detectType($this->bannerMediaPath());
    }

    public function coverMediaPath(): ?string
    {
        return $this->cover_image ?: $this->banner_image;
    }

    public function coverMediaUrl(): ?string
    {
        return SeriesMedia::publicUrl($this->coverMediaPath());
    }

    public function coverMediaType(): ?string
    {
        return SeriesMedia::detectType($this->coverMediaPath());
    }
}
