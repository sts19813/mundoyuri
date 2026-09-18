<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\EpisodeSource;
use App\Models\EpisodeWatchProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EpisodeWatchProgressController extends Controller
{
    public function store(Request $request, Episode $episode): JsonResponse
    {
        abort_unless($this->hostIsAllowed($request), 404);

        $data = $request->validate([
            'episode_source_id' => ['required', 'integer'],
            'position_seconds' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $source = EpisodeSource::query()
            ->whereKey($data['episode_source_id'])
            ->where('episode_id', $episode->id)
            ->firstOrFail();

        if (! in_array($source->provider, config('watch_progress.providers'), true)) {
            throw ValidationException::withMessages([
                'episode_source_id' => 'Esta fuente no admite progreso de reproducción.',
            ]);
        }

        abort_unless($episode->moderation_status === 'approved' && $episode->published_at, 404);

        $duration = $data['duration_seconds'] ?? null;
        $position = $duration
            ? min($data['position_seconds'], $duration)
            : $data['position_seconds'];
        $progressPercent = $duration
            ? round(min(100, ($position / $duration) * 100), 2)
            : null;
        $completed = (bool) ($data['completed'] ?? false);

        if (! $completed && $duration) {
            $completed = $progressPercent >= 92 || ($duration - $position) <= 30;
        }

        $progress = EpisodeWatchProgress::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'episode_id' => $episode->id,
            ],
            [
                'episode_source_id' => $source->id,
                'provider' => $source->provider,
                'position_seconds' => $position,
                'duration_seconds' => $duration,
                'progress_percent' => $progressPercent,
                'completed' => $completed,
                'last_watched_at' => now(),
            ]
        );

        $this->pruneOldProgress($request->user()->id);

        return response()->json([
            'saved' => true,
            'progress_id' => $progress->id,
        ]);
    }

    private function hostIsAllowed(Request $request): bool
    {
        $host = $request->getHost();

        if (in_array($host, config('watch_progress.hosts', []), true)) {
            return true;
        }

        return app()->environment('local')
            && in_array($host, config('watch_progress.local_hosts', []), true);
    }

    private function pruneOldProgress(int $userId): void
    {
        $keepIds = EpisodeWatchProgress::query()
            ->where('user_id', $userId)
            ->latest('last_watched_at')
            ->latest('id')
            ->limit((int) config('watch_progress.keep_per_user', 10))
            ->pluck('id');

        EpisodeWatchProgress::query()
            ->where('user_id', $userId)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
