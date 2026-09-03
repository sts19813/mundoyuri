<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LegacyProfile extends Model
{
    use HasFactory;

    public const CLAIM_STATUSES = ['unclaimed', 'pending', 'claimed', 'rejected'];

    protected $attributes = [
        'legacy_message_count' => 0,
        'claim_status' => 'unclaimed',
        'is_published' => true,
    ];

    protected $fillable = [
        'legacy_external_key',
        'slug',
        'nickname',
        'legacy_joined_at',
        'legacy_rank',
        'legacy_message_count',
        'legacy_location',
        'legacy_occupation',
        'legacy_interests',
        'legacy_website',
        'legacy_avatar_path',
        'source',
        'evidence',
        'admin_notes',
        'claim_status',
        'claimed_by_user_id',
        'claimed_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'legacy_joined_at' => 'datetime',
            'legacy_message_count' => 'integer',
            'claimed_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function avatarUrl(): ?string
    {
        return $this->legacy_avatar_path
            ? Storage::disk('public')->url($this->legacy_avatar_path)
            : null;
    }
}
