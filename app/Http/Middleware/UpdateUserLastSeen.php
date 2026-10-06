<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $seenAt = now();

            DB::table('users')
                ->where('id', $user->getKey())
                ->update(['last_seen_at' => $seenAt]);

            $user->setAttribute('last_seen_at', $seenAt);
        }

        return $next($request);
    }
}
