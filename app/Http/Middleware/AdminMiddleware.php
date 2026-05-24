<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $isAdminByField = $user?->role === 'admin';
        $isAdminByRole = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        if (!$user || (!$isAdminByField && !$isAdminByRole)) {
            return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección');
        }

        return $next($request);
    }
}
