<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunityReaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'love' => ['emoji' => '❤️', 'label' => 'Me encanta'],
        'like' => ['emoji' => '👍', 'label' => 'Me gusta'],
        'laugh' => ['emoji' => '😂', 'label' => 'Me divierte'],
        'cry' => ['emoji' => '😭', 'label' => 'Me emociona'],
        'yuri' => ['emoji' => '🌸', 'label' => 'Yuri'],
    ];

    /** @var array<string, class-string<Model>> */
    private const TARGETS = [
        'thread' => ForumThread::class,
        'post' => ForumPost::class,
        'comment' => Comment::class,
    ];

    protected $fillable = ['user_id', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, array{emoji: string, label: string}> */
    public static function types(): array
    {
        return self::TYPES;
    }

    /** @return list<string> */
    public static function typeKeys(): array
    {
        return array_keys(self::TYPES);
    }

    public static function targetClass(string $target): ?string
    {
        return self::TARGETS[$target] ?? null;
    }

    public static function targetKeyFor(Model $reactable): ?string
    {
        foreach (self::TARGETS as $key => $class) {
            if ($reactable instanceof $class) {
                return $key;
            }
        }

        return null;
    }
}
