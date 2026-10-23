<?php

namespace App\Models;

use App\Support\SeriesMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'genre_id',
        'created_by',
        'approved_by',
        'title',
        'alternate_titles',
        'slug',
        'content_type',
        'catalog_section',
        'status',
        'description',
        'seo_title',
        'seo_description',
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
            'alternate_titles' => 'array',
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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'series_favorites')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->withPivot(['role', 'character_name', 'sort_order'])
            ->orderBy('artist_series.sort_order');
    }

    public function duos(): BelongsToMany
    {
        return $this->belongsToMany(Duo::class)
            ->withPivot('sort_order')
            ->orderBy('duo_series.sort_order');
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

    /** @return array<int, string> */
    public function seoAlternateTitles(): array
    {
        $titles = collect($this->alternate_titles ?? []);
        $spacedTitle = Str::squish((string) preg_replace('/[^\pL\pN]+/u', ' ', $this->title));
        $compactTitle = (string) preg_replace('/[^\pL\pN]+/u', '', $this->title);

        if ($spacedTitle !== '' && strcasecmp($spacedTitle, $this->title) !== 0) {
            $titles->push($spacedTitle);
        }

        if ($compactTitle !== '' && strcasecmp($compactTitle, $this->title) !== 0 && strcasecmp($compactTitle, $spacedTitle) !== 0) {
            $titles->push($compactTitle);
        }

        return $titles
            ->map(fn (mixed $title): string => Str::squish(strip_tags((string) $title)))
            ->filter(fn (string $title): bool => $title !== '' && strcasecmp($title, $this->title) !== 0)
            ->unique(fn (string $title): string => Str::lower($title))
            ->values()
            ->all();
    }

    public function seoContentLabel(): string
    {
        if ($this->content_type === 'movie') {
            return $this->catalog_section === 'anime' ? 'película yuri' : 'película GL';
        }

        return $this->catalog_section === 'anime' ? 'anime yuri' : 'serie GL';
    }

    public function seoPageTitle(): string
    {
        return filled($this->seo_title)
            ? trim($this->seo_title)
            : $this->title.': '.$this->seoContentLabel().' subtitulada en español';
    }

    public function seoMetaDescription(): string
    {
        if (filled($this->seo_description)) {
            return Str::limit(Str::squish(strip_tags($this->seo_description)), 160, '');
        }

        $action = $this->content_type === 'movie' ? 'Mira' : 'Descubre y mira';
        $intro = "{$action} {$this->title}, {$this->seoContentLabel()} subtitulada en español.";
        $summary = Str::squish(strip_tags((string) $this->description));

        return Str::limit(trim($intro.' '.$summary), 160, '');
    }
}
