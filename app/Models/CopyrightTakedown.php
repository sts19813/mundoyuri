<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopyrightTakedown extends Model
{
    use HasFactory;

    public const STATUSES = ['received', 'reviewing', 'resolved', 'dismissed'];

    public const ACTIONS = [
        'source_disabled',
        'episode_unpublished',
        'series_unpublished',
        'content_restored',
        'no_action',
    ];

    protected $fillable = [
        'received_at',
        'claimant_name',
        'claimant_email',
        'claimant_organization',
        'work_description',
        'reported_urls',
        'status',
        'action_taken',
        'resolution_notes',
        'resolved_at',
        'recorded_by',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
