<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Duo extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'aliases', 'description', 'image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class)
            ->withPivot('sort_order')
            ->orderBy('duo_series.sort_order');
    }
}
