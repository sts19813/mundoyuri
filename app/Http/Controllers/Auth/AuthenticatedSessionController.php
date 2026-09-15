<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\AuthReturnUrl;
use Illuminate\Http\JsonResponse;
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
        AuthReturnUrl::remember($request);

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        AuthReturnUrl::remember($request);
        $request->authenticate();

        $request->session()->regenerate();

        $fallback = $request->user()->shouldEnterAdminPanel()
            ? route('dashboard', absolute: false)
            : route('home', absolute: false);
        $redirect = redirect()->intended($fallback);

        return $request->expectsJson()
            ? response()->json(['redirect' => $redirect->getTargetUrl()])
            : $redirect;
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

}
