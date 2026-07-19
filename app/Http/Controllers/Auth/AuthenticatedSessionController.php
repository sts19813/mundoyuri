<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $this->rememberPublicReturnUrl($request);

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->shouldEnterAdminPanel()) {
            $request->session()->forget('url.intended');

            return redirect()->route('dashboard');
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function rememberPublicReturnUrl(Request $request): void
    {
        $returnUrl = $request->query('return');

        if (! is_string($returnUrl) || $returnUrl === '') {
            return;
        }

        $parts = parse_url($returnUrl);
        $requestHost = $request->getHost();

        if ($parts === false || (isset($parts['host']) && $parts['host'] !== $requestHost)) {
            return;
        }

        $path = '/'.ltrim($parts['path'] ?? '/', '/');

        if (str_starts_with($path, '/admin') || in_array($path, ['/login', '/register'], true)) {
            return;
        }

        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $request->session()->put('url.intended', $path.$query);
    }
}
