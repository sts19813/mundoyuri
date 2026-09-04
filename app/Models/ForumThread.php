<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['forum_id', 'user_id', 'author_name_snapshot', 'title', 'slug', 'type', 'views_count', 'upvotes_count', 'replies_count', 'last_post_at', 'accepted_answer_post_id', 'accepted_answer_at', 'is_pinned', 'is_locked', 'is_hidden'];

    protected function casts(): array
    {
        return [
            'views_count' => 'integer',
            'upvotes_count' => 'integer',
            'replies_count' => 'integer',
            'last_post_at' => 'datetime',
            'accepted_answer_at' => 'datetime',
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'is_hidden' => 'boolean',
        ];
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function visiblePosts(): HasMany
    {
        return $this->posts()->where('is_hidden', false);
    }

    public function initialPost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->where('is_initial', true);
    }

    public function previewReplies(): HasMany
    {
        return $this->posts()->where('is_initial', false)->where('is_hidden', false);
    }

    public function latestVisiblePost(): HasOne
    {
        return $this->hasOne(ForumPost::class)->where('is_hidden', false)->latestOfMany();
    }

    public function authorName(): string
    {
        return $this->author?->displayName() ?: ($this->author_name_snapshot ?: 'Miembro eliminado');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_thread_subscriptions')->withTimestamps();
    }

    public function questionTags(): BelongsToMany
    {
        return $this->belongsToMany(QuestionTag::class, 'forum_thread_question_tag');
    }

    public function acceptedAnswer(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'accepted_answer_post_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumThreadVote::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(CommunityReaction::class, 'reactable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(CommunityReport::class, 'reportable');
    }

    public function moderationLogs(): MorphMany
    {
        return $this->morphMany(CommunityModerationLog::class, 'moderatable');
    }

    public function scopeQuestions($query)
    {
        return $query->where('type', 'question');
    }

    public function isQuestion(): bool
    {
        return $this->type === 'question';
    }

    public function isResolved(): bool
    {
        return $this->accepted_answer_post_id !== null;
    }
}
