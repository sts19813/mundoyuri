<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'alias', 'email', 'email_verified_at', 'password', 'role', 'is_active', 'episode_email_notifications_enabled', 'last_login_at', 'google_id', 'google_avatar', 'profile_image', 'cover_image', 'biography', 'profile_visibility', 'show_last_seen', 'show_join_date', 'show_favorites', 'show_activity', 'signature_text', 'signature_image', 'location', 'website', 'occupation', 'interests', 'community_message_count', 'community_reputation', 'community_rank_id', 'is_legacy', 'legacy_joined_at', 'legacy_source', 'legacy_notes', 'legacy_verified', 'profile_claimed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $attributes = [
        'episode_email_notifications_enabled' => true,
        'profile_visibility' => 'public',
        'show_last_seen' => false,
        'show_join_date' => true,
        'show_favorites' => true,
        'show_activity' => true,
        'community_message_count' => 0,
        'community_reputation' => 0,
        'is_legacy' => false,
        'legacy_verified' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'episode_email_notifications_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'show_last_seen' => 'boolean',
            'show_join_date' => 'boolean',
            'show_favorites' => 'boolean',
            'show_activity' => 'boolean',
            'community_message_count' => 'integer',
            'community_reputation' => 'integer',
            'is_legacy' => 'boolean',
            'legacy_joined_at' => 'datetime',
            'legacy_verified' => 'boolean',
            'profile_claimed_at' => 'datetime',
        ];
    }

    public function communityRank(): BelongsTo
    {
        return $this->belongsTo(CommunityRank::class);
    }

    public function communityBadges(): BelongsToMany
    {
        return $this->belongsToMany(CommunityBadge::class, 'community_badge_user')
            ->withPivot(['awarded_by', 'reason', 'awarded_at'])
            ->withTimestamps();
    }

    public function submittedSeries(): HasMany
    {
        return $this->hasMany(Series::class, 'created_by');
    }

    public function submittedEpisodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function favoriteSeries(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_favorites')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'followed_id',
            'follower_id'
        )->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follower_id',
            'followed_id'
        )->withTimestamps();
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'blocker_id',
            'blocked_id'
        )->withTimestamps();
    }

    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'blocked_id',
            'blocker_id'
        )->withTimestamps();
    }

    public function conversationsAsUserOne(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    public function conversationsAsUserTwo(): HasMany
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(DirectMessage::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(DirectMessage::class, 'recipient_id');
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->whereKey($user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $this->blockedByUsers()->whereKey($user->id)->exists();
    }

    public function cannotInteractWith(User $user): bool
    {
        return $this->hasBlocked($user) || $this->isBlockedBy($user);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function shouldEnterAdminPanel(): bool
    {
        return in_array($this->role, ['admin', 'moderator'], true)
            || $this->hasAnyRole(['admin', 'moderator']);
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    public function avatarUrl(): string
    {
        if ($this->profile_image) {
            return Storage::disk('public')->url($this->profile_image);
        }

        if ($this->google_avatar) {
            return $this->google_avatar;
        }

        return asset('metronic/assets/media/avatars/blank.png');
    }

    public function hasProfileAvatar(): bool
    {
        return filled($this->profile_image) || filled($this->google_avatar);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }

    public function publicProfileUrl(): string
    {
        return route('profiles.show', [
            'user' => $this,
            'alias' => Str::slug($this->alias ?: $this->name),
        ]);
    }

    public function displayName(): string
    {
        return $this->alias ?: $this->name;
    }

    public function communityJoinDate(): mixed
    {
        if ($this->is_legacy && $this->legacy_joined_at) {
            return $this->legacy_joined_at;
        }

        return $this->created_at;
    }

    public function scopeVisibleInCommunityDirectory(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('profile_visibility', 'public');
    }

    public function initials(): string
    {
        $name = trim($this->name ?: $this->email);
        $words = preg_split('/\s+/', $name) ?: [];

        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return $initials ?: 'U';
    }
}
