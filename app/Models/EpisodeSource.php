<?php

namespace App\Models;

use App\Support\VideoSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'provider',
        'source_type',
        'label',
        'sort_order',
        'video_url',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function getPlayableUrlAttribute(): string
    {
        return VideoSource::playableUrl($this->provider, $this->video_url, $this);
    }

    public function getPlayerTypeAttribute(): string
    {
        return VideoSource::playerType($this->provider);
    }

    public function getDirectVideoUrlAttribute(): string
    {
        return VideoSource::directVideoUrl($this->provider, $this->video_url);
    }

    public function isPart(): bool
    {
        return $this->source_type === 'part';
    }

}
