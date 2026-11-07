<?php

namespace App\Http\Controllers;

use App\Models\VisitorPresenceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorPresenceController extends Controller
{
    private const COOKIE_NAME = 'mundo_yuri_visitor_id';

    public function __invoke(Request $request): JsonResponse
    {
        $visitorId = $request->cookie(self::COOKIE_NAME);

        if (! is_string($visitorId) || ! Str::isUuid($visitorId)) {
            return response()->json(['saved' => false], 422);
        }

        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'path' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:160'],
        ]);

        $now = now();
        $session = VisitorPresenceSession::query()
            ->where('session_id', $data['session_id'])
            ->where('visitor_id', $visitorId)
            ->first();

        $values = [
            'last_seen_at' => $now,
            'current_path' => $this->normalizePath($data['path']),
            'current_title' => $data['title'] ?? null,
        ];

        if ($session) {
            $session->fill($values)->save();
        } else {
            VisitorPresenceSession::query()->create([
                'visitor_id' => $visitorId,
                'session_id' => $data['session_id'],
                'started_at' => $now,
                ...$values,
            ]);
        }

        return response()->json(['saved' => true]);
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '/';
        }

        return mb_substr(str_starts_with($path, '/') ? $path : '/'.$path, 0, 255);
    }
}
