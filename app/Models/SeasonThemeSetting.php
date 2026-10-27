<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SeasonThemeSetting extends Model
{
    private const PUBLIC_CACHE_KEY = 'season-theme.public-settings.v1';

    public const THEMES = [
        'halloween' => 'Halloween',
        'christmas' => 'Navidad',
        'spring' => 'Primavera',
        'summer' => 'Verano',
        'autumn' => 'Otoño',
    ];

    protected $fillable = ['enabled', 'theme'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Admin saves invalidate immediately; public requests need no settings query.
        static::saved(fn () => Cache::memo('file')->forget(self::PUBLIC_CACHE_KEY));
        static::deleted(fn () => Cache::memo('file')->forget(self::PUBLIC_CACHE_KEY));
    }

    public static function activeTheme(): ?string
    {
        $settings = Cache::memo('file')->rememberForever(self::PUBLIC_CACHE_KEY, function (): array {
            $settings = static::current();

            return ['enabled' => $settings->enabled, 'theme' => $settings->theme];
        });

        return ($settings['enabled'] ?? false) && isset(self::THEMES[$settings['theme'] ?? ''])
            ? $settings['theme']
            : null;
    }

    public static function logoAsset(): string
    {
        $theme = static::activeTheme();

        return $theme ? 'assets/img/logos/mundo-yuri-'.$theme.'.svg' : 'assets/img/logos/mundo-yuri-logo.svg';
    }

    public static function current(): self
    {
        if (! Schema::hasTable('season_theme_settings')) {
            return (new static(['enabled' => true, 'theme' => 'halloween']))->forceFill(['id' => 1]);
        }

        return static::query()->firstOrNew(['id' => 1], ['enabled' => true, 'theme' => 'halloween']);
    }
}
