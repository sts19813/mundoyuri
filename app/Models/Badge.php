<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Badge extends Model
{
    use HasFactory;

    public const TYPES = ['legacy', 'achievement', 'staff', 'special', 'contribution', 'development', 'activity', 'forum', 'questions', 'social', 'catalog', 'community', 'seniority', 'event', 'fun', 'secret'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image_path',
        'type',
        'priority',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->using(BadgeAward::class)
            ->withPivot(['awarded_by', 'awarded_at', 'note'])
            ->withTimestamps();
    }

    public function legacyProfiles(): BelongsToMany
    {
        return $this->belongsToMany(LegacyProfile::class, 'badge_legacy_profile')
            ->using(LegacyProfileBadgeAward::class)
            ->withPivot(['awarded_by', 'awarded_at', 'note'])
            ->withTimestamps();
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('priority')->orderBy('name');
    }
}
