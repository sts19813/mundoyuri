<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityReport extends Model
{
    use HasFactory;

    public const REASONS = ['spam', 'harassment', 'inappropriate_content', 'unmarked_spoiler', 'personal_information', 'other'];

    public const STATUSES = ['pending', 'reviewing', 'resolved', 'dismissed'];

    protected $fillable = ['reporter_id', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at', 'resolution'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(CommunityModerationLog::class);
    }
}
