<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sticker extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'path',
        'mime',
        'size',
        'is_general',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'is_general' => 'boolean',
            'approved_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $stickers) use ($user): void {
            $stickers->where('is_general', true)->orWhere('owner_id', $user->id);
        });
    }

    public function canBeViewedBy(User $user): bool
    {
        return $this->is_general || $this->owner_id === $user->id || $user->isAdmin();
    }

    /** @return array{attachment_path: string, attachment_name: string, attachment_mime: string, attachment_size: int} */
    public function attachmentPayload(): array
    {
        return [
            'attachment_path' => $this->path,
            'attachment_name' => $this->name,
            'attachment_mime' => $this->mime,
            'attachment_size' => $this->size,
        ];
    }
}
