<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BadgeAward extends Pivot
{
    protected $table = 'badge_user';

    public $incrementing = false;

    protected $fillable = [
        'badge_id',
        'user_id',
        'awarded_by',
        'awarded_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    public function awarder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
