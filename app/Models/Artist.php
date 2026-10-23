<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'aliases', 'country_of_origin', 'biography', 'image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class)
            ->withPivot(['role', 'character_name', 'sort_order'])
            ->orderBy('artist_series.sort_order');
    }
}
