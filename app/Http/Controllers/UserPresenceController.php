<?php

namespace App\Http\Controllers;

use App\Models\UserPresenceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPresenceController extends Controller
{
    private const MAX_HEARTBEAT_SECONDS = 75;

    private const STALE_AFTER_SECONDS = 300;

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'path' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:160'],
            'ending' => ['sometimes', 'boolean'],
        ]);

        $now = now();
        $session = UserPresenceSession::query()
            ->where('session_id', $data['session_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        $elapsedSeconds = 0;

        if ($session && $session->last_seen_at) {
            $rawElapsed = $session->last_seen_at->diffInSeconds($now, false);

            if ($rawElapsed > 0 && $rawElapsed <= self::STALE_AFTER_SECONDS) {
                $elapsedSeconds = (int) floor(min($rawElapsed, self::MAX_HEARTBEAT_SECONDS));
            }
        }

        $values = [
            'user_id' => $request->user()->id,
            'started_at' => $session?->started_at ?: $now,
            'last_seen_at' => $now,
            'ended_at' => ($data['ending'] ?? false) ? $now : null,
            'total_seconds' => ($session?->total_seconds ?? 0) + $elapsedSeconds,
            'heartbeat_count' => ($session?->heartbeat_count ?? 0) + 1,
            'current_path' => $this->normalizePath($data['path']),
            'current_title' => $data['title'] ?? null,
            'user_agent' => $session?->user_agent ?: mb_substr((string) $request->userAgent(), 0, 500),
        ];

        if ($session) {
            $session->fill($values)->save();
        } else {
            $session = UserPresenceSession::query()->create([
                'session_id' => $data['session_id'],
                ...$values,
            ]);
        }

        return response()->json([
            'saved' => true,
            'total_seconds' => $session->total_seconds,
        ]);
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return mb_substr($path, 0, 255);
    }
}
