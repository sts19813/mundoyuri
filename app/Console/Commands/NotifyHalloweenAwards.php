<?php

namespace App\Console\Commands;

use App\Models\BadgeAwardLog;
use App\Services\BadgeNotificationService;
use Illuminate\Console\Command;

class NotifyHalloweenAwards extends Command
{
    protected $signature = 'badges:notify-halloween-2026';

    protected $description = 'Avisar a quienes ya recibieron la insignia de Halloween 2026';

    public function handle(BadgeNotificationService $service): int
    {
        $count = 0;
        BadgeAwardLog::query()->where('badge_slug', 'halloween-yuri-2026')
            ->where('action', 'assigned')->whereNull('notified_at')
            ->whereHas('user', fn ($q) => $q->whereHas('badges', fn ($q) => $q->where('slug', 'halloween-yuri-2026')))
            ->orderBy('id')->each(function (BadgeAwardLog $log) use ($service, &$count): void {
                $count += (int) $service->notifyAward($log);
            });

        $this->info("Notificaciones creadas: {$count}.");

        return self::SUCCESS;
    }
}
