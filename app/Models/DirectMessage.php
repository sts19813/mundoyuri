<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectMessage extends Model
{
    public function isVisibleFor(User $user): bool
    {
        return self::query()->whereKey($this->id)->visibleFor($user)->exists();
    }

    public function scopeVisibleFor(\Illuminate\Database\Eloquent\Builder $query, User $user): void
    {
        $query->whereNotExists(fn ($state) => $state->selectRaw('1')->from('conversation_user_states')
            ->whereColumn('conversation_user_states.conversation_id', 'direct_messages.conversation_id')
            ->where('conversation_user_states.user_id', $user->id)
            ->whereColumn('direct_messages.id', '<=', 'conversation_user_states.cleared_through_message_id'));
    }

    public function scopeArchivedFor(\Illuminate\Database\Eloquent\Builder $query, User $user, bool $archived): void
    {
        $method = $archived ? 'whereExists' : 'whereNotExists';
        $query->$method(fn ($state) => $state->selectRaw('1')->from('conversation_user_states')
            ->whereColumn('conversation_user_states.conversation_id', 'direct_messages.conversation_id')
            ->where('conversation_user_states.user_id', $user->id)->whereNotNull('conversation_user_states.archived_at'));
    }

    use HasFactory;

    public const VIDEO_EXTENSIONS = [
        '3g2', '3gp', '3gpp', 'amv', 'asf', 'avi', 'divx', 'drc', 'f4v', 'flv',
        'm2t', 'm2ts', 'm2v', 'm4v', 'mkv', 'mov', 'mp4', 'mpe', 'mpeg', 'mpg',
        'mpv', 'mts', 'mxf', 'ogm', 'ogv', 'qt', 'rm', 'rmvb', 'ts', 'vob',
        'webm', 'wmv', 'yuv',
    ];

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'recipient_id',
        'client_id',
        'reply_to_message_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'attachment_purged_at',
        'is_sticker',
        'read_at',
        'deleted_at',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'deleted_at' => 'datetime',
            'attachment_size' => 'integer',
            'attachment_purged_at' => 'datetime',
            'is_sticker' => 'boolean',
        ];
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function hasAttachment(): bool
    {
        return ! $this->isDeleted() && filled($this->attachment_path);
    }

    public function hasStoredAttachment(): bool
    {
        return filled($this->attachment_path);
    }

    public function moderationAttachmentPath(): ?string
    {
        return $this->attachment_path ?: $this->moderation_attachment_path;
    }

    public function attachmentWasPurged(): bool
    {
        return $this->attachment_purged_at !== null;
    }

    public function attachmentIsImage(): bool
    {
        return $this->hasAttachment() && str_starts_with((string) $this->attachment_mime, 'image/');
    }

    public function attachmentIsVideo(): bool
    {
        return $this->hasAttachment()
            && (str_starts_with((string) $this->attachment_mime, 'video/')
                || in_array(strtolower(pathinfo((string) $this->attachment_name, PATHINFO_EXTENSION)), self::VIDEO_EXTENSIONS, true));
    }

    public function attachmentSizeLabel(): string
    {
        $bytes = max(0, (int) $this->attachment_size);

        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1).' MB';
        }

        return max(1, (int) ceil($bytes / 1024)).' KB';
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(DirectMessageFavorite::class);
    }

    public function previewText(): string
    {
        if ($this->isDeleted()) {
            return 'Mensaje eliminado';
        }

        if (filled($this->body)) {
            return (string) str($this->body)->squish()->limit(110);
        }

        if ($this->attachment_name) {
            return 'Archivo: '.$this->attachment_name;
        }

        return 'Mensaje';
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(DirectMessageReaction::class);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
