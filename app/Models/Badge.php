<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    public const TYPES = ['legacy', 'achievement', 'staff', 'special'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('priority')->orderBy('name');
    }
}
