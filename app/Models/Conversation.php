<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DirectMessage::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(DirectMessage::class)->latestOfMany();
    }

    public function otherParticipant(User $viewer): User
    {
        return $viewer->id === $this->user_one_id
            ? $this->userTwo
            : $this->userOne;
    }

    public static function between(User $first, User $second): Builder
    {
        [$userOneId, $userTwoId] = self::participantIds($first, $second);

        return self::query()
            ->where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId);
    }

    /**
     * @return array{int, int}
     */
    public static function participantIds(User $first, User $second): array
    {
        return $first->id < $second->id
            ? [$first->id, $second->id]
            : [$second->id, $first->id];
    }
}
