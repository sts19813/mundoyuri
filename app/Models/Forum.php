<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = ['forum_category_id', 'name', 'slug', 'description', 'icon', 'sort_order', 'is_active', 'is_locked', 'minimum_role'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'is_locked' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    public function latestVisibleThread(): HasOne
    {
        return $this->hasOne(ForumThread::class)->where('is_hidden', false)->latestOfMany('last_post_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function acceptsRole(?User $user): bool
    {
        if (! $this->minimum_role) {
            return $user !== null;
        }

        if (! $user) {
            return false;
        }

        $levels = ['user' => 1, 'moderator' => 2, 'admin' => 3];
        $current = $user->isAdmin() ? 3 : ($user->shouldEnterAdminPanel() ? 2 : 1);

        return $current >= ($levels[$this->minimum_role] ?? PHP_INT_MAX);
    }
}
