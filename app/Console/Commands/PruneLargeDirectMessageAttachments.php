<?php

namespace App\Console\Commands;

use App\Models\DirectMessage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('messages:prune-large-attachments {--dry-run : Muestra los adjuntos que se purgarían sin borrar archivos}')]
#[Description('Retira de los chats los adjuntos grandes vencidos y los conserva para moderación')]
class PruneLargeDirectMessageAttachments extends Command
{
    public function handle(): int
    {
        $threshold = (int) config('messenger.temporary_attachment_threshold_bytes');
        $retentionDays = (int) config('messenger.temporary_attachment_retention_days');
        $dryRun = (bool) $this->option('dry-run');
        $count = 0;

        DirectMessage::query()
            ->whereNotNull('attachment_path')
            ->whereNull('attachment_purged_at')
            ->where('attachment_size', '>=', $threshold)
            ->where('created_at', '<=', now()->subDays($retentionDays))
            ->orderBy('id')
            ->eachById(function (DirectMessage $message) use ($dryRun, &$count): void {
                $count++;

                if ($dryRun) {
                    return;
                }

                $message->forceFill([
                    'moderation_attachment_path' => $message->attachment_path,
                    'attachment_path' => null,
                    'attachment_purged_at' => now(),
                ])->saveQuietly();
            });

        $this->info($dryRun
            ? "Se retirarían {$count} adjuntos grandes de los chats."
            : "Se retiraron {$count} adjuntos grandes de los chats y se conservaron para moderación.");

        return self::SUCCESS;
    }
}
