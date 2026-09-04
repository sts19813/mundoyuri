<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityModerationLog extends Model
{
    use HasFactory;

    protected $fillable = ['actor_id', 'community_report_id', 'action', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(CommunityReport::class, 'community_report_id');
    }

    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }
}
