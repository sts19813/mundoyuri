<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthReturnUrl;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        AuthReturnUrl::remember($request);
        $request->session()->put('registration_started_at', now()->timestamp);

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        AuthReturnUrl::remember($request);

        if (! config('services.registration.enabled')) {
            throw ValidationException::withMessages([
                'email' => 'El registro esta temporalmente pausado mientras reforzamos la seguridad de la comunidad. Intenta de nuevo en unos minutos.',
            ]);
        }

        $this->ensureHumanRegistration($request);

        $request->validate([
            'name' => ['required', 'string', 'max:80', $this->withoutLinks()],
            'alias' => ['nullable', 'string', 'max:50', $this->withoutLinks(), 'unique:users,alias'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'alias' => $request->alias,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (\Spatie\Permission\Models\Role::query()->where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('registration_started_at');

        $redirect = redirect()->intended(route('verification.notice', absolute: false));

        return $request->expectsJson()
            ? response()->json(['redirect' => $redirect->getTargetUrl()])
            : $redirect;
    }

    private function ensureHumanRegistration(Request $request): void
    {
        if (filled($request->input('website'))) {
            throw ValidationException::withMessages([
                'email' => 'No fue posible completar el registro.',
            ]);
        }

        $startedAt = (int) $request->session()->get('registration_started_at', 0);
        $elapsed = now()->timestamp - $startedAt;

        if ($startedAt === 0 || $elapsed < 2 || $elapsed > 7200) {
            throw ValidationException::withMessages([
                'email' => 'Actualiza la página y vuelve a intentar crear tu cuenta.',
            ]);
        }

        if (! config('services.turnstile.enabled')) {
            return;
        }

        try {
            $verified = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $request->input('cf-turnstile-response'),
                ])
                ->json('success') === true;
        } catch (\Throwable) {
            $verified = false;
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'email' => 'Confirma la verificación de seguridad para crear tu cuenta.',
            ]);
        }
    }

    private function withoutLinks(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $text = Str::lower(trim((string) $value));

            if (preg_match('/(?:https?:\/\/|www\.)|\b[\pL\pN-]+\.(?:com|net|org|ru|buzz|info|xyz|top|site|online|click|shop|io|co)(?:\/|\b)/iu', $text)) {
                $fail('El :attribute no puede contener enlaces.');
            }
        };
    }
}
