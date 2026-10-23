<?php

namespace App\Console\Commands;

use App\Models\DirectMessage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('messages:prune-large-attachments {--dry-run : Muestra los adjuntos que se purgarían sin borrar archivos}')]
#[Description('Elimina del disco los adjuntos grandes de mensajes privados que cumplieron 30 días')]
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

                if ($message->attachment_path) {
                    Storage::disk('local')->delete($message->attachment_path);
                }

                $message->forceFill([
                    'attachment_path' => null,
                    'attachment_purged_at' => now(),
                ])->saveQuietly();
            });

        $this->info($dryRun
            ? "Se purgarían {$count} adjuntos grandes."
            : "Se purgaron {$count} adjuntos grandes.");

        return self::SUCCESS;
    }
}
