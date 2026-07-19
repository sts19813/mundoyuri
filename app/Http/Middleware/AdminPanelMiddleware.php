<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPanelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->shouldEnterAdminPanel()) {
            return redirect()->route('home')
                ->with('error', 'No tienes permiso para acceder al panel de administración.');
        }

        return $next($request);
    }
}
