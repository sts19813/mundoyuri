<?php

namespace App\Models;

/**
 * @deprecated Use Badge. This alias preserves compatibility with existing integrations.
 */
class CommunityBadge extends Badge
{
    protected $table = 'badges';
}
