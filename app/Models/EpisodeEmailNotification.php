<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeEmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'user_id',
        'email',
        'sent_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
