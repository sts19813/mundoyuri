<?php

namespace App\Console\Commands;

use App\Models\ForumThread;
use App\Models\User;
use App\Services\ForumCounterService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('community:recount-forums')]
#[Description('Recalcular los contadores derivados de temas y mensajes del foro')]
class RecountForumCounters extends Command
{
    public function handle(ForumCounterService $counters): int
    {
        ForumThread::query()->each(fn (ForumThread $thread) => $counters->synchronizeThread($thread));
        User::query()->each(fn (User $user) => $counters->synchronizeUser($user));

        $this->info('Contadores de foros reconciliados correctamente.');

        return self::SUCCESS;
    }
}
