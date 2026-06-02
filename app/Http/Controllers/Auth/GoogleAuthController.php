<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\GoogleOAuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use League\OAuth2\Client\Provider\GoogleUser;
use Throwable;

class GoogleAuthController extends Controller
{
    private const OAUTH_STATE_KEY = 'google_oauth_state';
    private const OAUTH_INTENT_KEY = 'google_oauth_intent';

    public function __construct(
        private readonly GoogleOAuthService $googleOAuthService,
    ) {
    }

    public function redirect(Request $request): RedirectResponse
    {
        try {
            $authorizationData = $this->googleOAuthService->getAuthorizationData();
        } catch (Throwable $exception) {
            return redirect()->route($this->intentRoute((string) $request->string('intent')))
                ->with('error', $exception->getMessage());
        }

        $request->session()->put(self::OAUTH_STATE_KEY, $authorizationData['state']);
        $request->session()->put(self::OAUTH_INTENT_KEY, (string) $request->string('intent'));

        return redirect()->away($authorizationData['url']);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route($this->pullIntentRoute($request))
                ->with('error', 'No se pudo completar el acceso con Google.');
        }

        $expectedState = $request->session()->pull(self::OAUTH_STATE_KEY);
        if (! is_string($expectedState) || $expectedState === '' || $expectedState !== (string) $request->string('state')) {
            return redirect()->route($this->pullIntentRoute($request))
                ->with('error', 'La sesion de Google expiro. Intenta nuevamente.');
        }

        try {
            $googleUser = $this->googleOAuthService->getUserFromCode((string) $request->string('code'));
            $user = $this->resolveUser($googleUser);
        } catch (Throwable $exception) {
            return redirect()->route($this->pullIntentRoute($request))
                ->with('error', $exception->getMessage());
        }

        $request->session()->forget(self::OAUTH_INTENT_KEY);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function resolveUser(GoogleUser $googleUser): User
    {
        $googleId = (string) $googleUser->getId();
        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $name = trim($googleUser->getName()) ?: Str::before($email, '@') ?: 'Usuario de Google';
        $avatar = $googleUser->getAvatar();

        if ($googleId === '') {
            throw new \RuntimeException('Google no devolvio un identificador de usuario valido.');
        }

        $linkedUser = User::query()->where('google_id', $googleId)->first();
        if ($linkedUser) {
            $this->syncGoogleData($linkedUser, $googleId, $avatar);

            return $linkedUser->refresh();
        }

        if ($email === '') {
            throw new \RuntimeException('Tu cuenta de Google no tiene un correo disponible para iniciar sesion.');
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($user) {
            $this->syncGoogleData($user, $googleId, $avatar);

            return $user->refresh();
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Str::random(40),
            'google_id' => $googleId,
            'google_avatar' => $avatar,
        ]);

        if (\Spatie\Permission\Models\Role::query()->where('name', 'user')->exists()) {
            $user->assignRole('user');
        }

        event(new Registered($user));

        return $user;
    }

    private function syncGoogleData(User $user, string $googleId, ?string $avatar): void
    {
        $user->forceFill([
            'google_id' => $googleId,
            'google_avatar' => $avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }

    private function pullIntentRoute(Request $request): string
    {
        return $this->intentRoute($request->session()->pull(self::OAUTH_INTENT_KEY));
    }

    private function intentRoute(?string $intent): string
    {
        return $intent === 'register' ? 'register' : 'login';
    }
}
