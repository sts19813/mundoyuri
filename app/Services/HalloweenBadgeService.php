<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BadgeAwardLog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class HalloweenBadgeService
{
    public function awardForPresence(User $user, CarbonInterface $seenAt): void
    {
        if ($seenAt->copy()->setTimezone('America/Merida')->toDateString() !== '2026-10-31' || $user->id === 1) {
            return;
        }

        // Keep the existing automation exclusions for the curated founder profile.
        if ($user->badges()->where('slug', 'fundadora')->exists()) {
            return;
        }

        $badge = Badge::query()->active()->where('slug', 'halloween-yuri-2026')->first();
        if (! $badge) {
            return;
        }

        DB::transaction(function () use ($user, $badge, $seenAt): void {
            $condition = 'Se conectó el 31 de octubre de 2026, hora de Mérida.';
            $inserted = DB::table('badge_user')->insertOrIgnore([
                'badge_id' => $badge->id,
                'user_id' => $user->id,
                'awarded_at' => $seenAt,
                'note' => 'Asignación automática: '.$condition,
                'assignment_source' => 'automatic',
                'automation_rule' => 'halloween-presencia-2026',
                'created_at' => $seenAt,
                'updated_at' => $seenAt,
            ]);

            if ($inserted) {
                BadgeAwardLog::query()->create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'badge_name' => $badge->name,
                    'badge_slug' => $badge->slug,
                    'action' => 'assigned',
                    'source' => 'automatic',
                    'automation_rule' => 'halloween-presencia-2026',
                    'condition' => $condition,
                ]);
            }
        });
    }
}
