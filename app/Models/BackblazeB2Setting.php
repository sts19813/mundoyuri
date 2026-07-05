<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackblazeB2Setting extends Model
{
    protected $fillable = [
        'enabled',
        'key_id',
        'application_key',
        'bucket_name',
        'bucket_id',
        'download_url',
        'token_ttl_seconds',
        'last_verified_at',
    ];

    protected $hidden = [
        'application_key',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'application_key' => 'encrypted',
            'token_ttl_seconds' => 'integer',
            'last_verified_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrNew([
            'id' => 1,
        ], [
            'enabled' => false,
            'token_ttl_seconds' => 3600,
        ]);
    }

    public function isConfigured(): bool
    {
        return $this->enabled && $this->hasRequiredCredentials();
    }

    public function hasRequiredCredentials(): bool
    {
        return filled($this->key_id)
            && filled($this->application_key)
            && filled($this->bucket_name);
    }
}
