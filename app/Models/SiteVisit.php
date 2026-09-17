<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'visitor_id',
        'visited_on',
        'visited_at',
        'path',
        'path_hash',
    ];

    protected function casts(): array
    {
        return [
            'visited_on' => 'date',
            'visited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
