<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'alias', 'email', 'email_verified_at', 'password', 'role', 'is_active', 'google_id', 'google_avatar', 'profile_image', 'cover_image', 'biography'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
        ];
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
