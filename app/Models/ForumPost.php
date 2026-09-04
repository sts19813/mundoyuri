<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['forum_thread_id', 'user_id', 'author_name_snapshot', 'body', 'edited_at', 'is_initial', 'is_hidden'];

    protected function casts(): array
    {
        return ['edited_at' => 'datetime', 'is_initial' => 'boolean', 'is_hidden' => 'boolean'];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(ForumMention::class);
    }

    public function authorName(): string
    {
        return $this->author?->displayName() ?: ($this->author_name_snapshot ?: 'Miembro eliminado');
    }
}
