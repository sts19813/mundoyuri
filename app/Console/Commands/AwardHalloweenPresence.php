<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPresenceSession;
use App\Services\HalloweenBadgeService;
use Illuminate\Console\Command;

class AwardHalloweenPresence extends Command
{
    protected $signature = 'badges:award-halloween-2026';

    protected $description = 'Asignar la insignia de Halloween a usuarios presentes durante el evento';

    public function handle(HalloweenBadgeService $service): int
    {
        $now = now();
        if ($now->copy()->setTimezone('America/Merida')->toDateString() !== '2026-10-31') {
            $this->error('Este comando solo se ejecuta el 31 de octubre de 2026, hora de Mérida.');

            return self::FAILURE;
        }

        // The database timestamps use the application's configured timezone.
        $start = $now->copy()->setTimezone('America/Merida')->startOfDay()->setTimezone(config('app.timezone'));
        $end = $start->copy()->addDay();
        $sessions = UserPresenceSession::query()->select('user_id')
            ->where('started_at', '<', $end)->where('last_seen_at', '>=', $start);

        $users = User::query()->where(function ($query) use ($start, $end, $sessions): void {
            $query->where(fn ($q) => $q->where('last_seen_at', '>=', $start)->where('last_seen_at', '<', $end))
                ->orWhere(fn ($q) => $q->where('last_login_at', '>=', $start)->where('last_login_at', '<', $end))
                ->orWhereIn('id', $sessions);
        });

        $count = 0;
        $users->orderBy('id')->each(function (User $user) use ($service, $now, &$count): void {
            $service->awardForPresence($user, $now);
            $count++;
        });
        $this->info("Usuarios con actividad revisados: {$count}.");

        return self::SUCCESS;
    }
}
