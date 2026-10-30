<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPresenceSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PresenceLeaderboard
{
    /** @return Collection<int, User> */
    public function publicStreakers(?User $viewer = null, int $limit = 10): Collection
    {
        $today = now(config('app.timezone'))->startOfDay();

        return User::query()
            ->visibleToProfileViewer($viewer)
            ->where('last_streak_date', '>=', $today->copy()->subDay())
            ->where('current_streak_days', '>', 0)
            ->with([
                'communityRank',
                'badges' => fn ($badgeQuery) => $badgeQuery->active()->ordered(),
            ])
            ->orderByDesc('current_streak_days')
            ->orderByDesc('longest_streak_days')
            ->orderBy('id')
            ->limit(max(1, min($limit, 10)))
            ->get();
    }

    /** @return Collection<int, User> */
    public function publicMarathoners(?User $viewer = null, int $limit = 10): Collection
    {
        if (! Schema::hasTable('user_presence_sessions')) {
            return collect();
        }

        $presenceTotals = UserPresenceSession::query()
            ->select('user_id')
            ->selectRaw('SUM(total_seconds) as total_presence_seconds')
            ->groupBy('user_id');

        $query = User::query()
            ->joinSub($presenceTotals, 'presence_totals', fn ($join) => $join->on('users.id', '=', 'presence_totals.user_id'))
            ->select('users.*')
            ->addSelect('presence_totals.total_presence_seconds')
            ->visibleToProfileViewer($viewer)
            ->where(function ($query): void {
                $query
                    ->whereNull('users.role')
                    ->orWhere('users.role', '!=', 'admin');
            })
            ->with([
                'communityRank',
                'badges' => fn ($badgeQuery) => $badgeQuery->active()->ordered(),
            ]);

        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            $query->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'admin'));
        }

        return $query
            ->orderByDesc(DB::raw('presence_totals.total_presence_seconds'))
            ->orderBy('users.id')
            ->limit(max(1, min($limit, 10)))
            ->get();
    }
}
