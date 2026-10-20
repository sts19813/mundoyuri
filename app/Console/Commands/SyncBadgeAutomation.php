<?php

namespace App\Console\Commands;

use App\Services\BadgeAutomationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('badges:sync-automation')]
#[Description('Sincronizar las insignias comunitarias que tienen condiciones automáticas')]
class SyncBadgeAutomation extends Command
{
    public function handle(BadgeAutomationService $automation): int
    {
        $result = $automation->syncAll();
        $this->info("{$result['users']} usuarios revisados; {$result['awarded']} asignadas y {$result['revoked']} retiradas.");

        return self::SUCCESS;
    }
}
