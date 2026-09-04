<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LegacyProfileBadgeAward extends Pivot
{
    protected $table = 'badge_legacy_profile';

    public $incrementing = false;

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
