<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumMention extends Model
{
    use HasFactory;

    protected $fillable = ['forum_post_id', 'mentioned_user_id', 'mentioner_user_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'forum_post_id');
    }

    public function mentionedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }

    public function mentioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioner_user_id');
    }
}
