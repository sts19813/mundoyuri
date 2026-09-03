<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'minimum_messages',
        'is_special',
        'is_active',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'minimum_messages' => 'integer',
            'is_special' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAutomatic(Builder $query): Builder
    {
        return $query->where('is_special', false)->whereNotNull('minimum_messages');
    }

    public function scopeSpecial(Builder $query): Builder
    {
        return $query->where('is_special', true);
    }
}
