<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'commentable_type',
        'commentable_id',
        'alias',
        'body',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->oldest();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDisplayAliasAttribute(): string
    {
        return $this->user?->alias ?: $this->user?->name ?: $this->alias ?: 'Anonimo';
    }

    public function avatarUrl(): ?string
    {
        return $this->user?->avatarUrl();
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->display_alias, 0, 1)) ?: 'A';
    }

    public function displayTime(): string
    {
        return $this->created_at->format('d M Y · g:i a');
    }
}
