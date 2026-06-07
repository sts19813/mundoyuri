<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'alias', 'email', 'email_verified_at', 'password', 'role', 'is_active', 'google_id', 'google_avatar', 'profile_image'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
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
