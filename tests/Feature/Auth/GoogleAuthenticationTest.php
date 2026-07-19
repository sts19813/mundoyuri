<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\GoogleOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use League\OAuth2\Client\Provider\GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_be_redirected_to_google(): void
    {
        $service = Mockery::mock(GoogleOAuthService::class);
        $service->shouldReceive('getAuthorizationData')
            ->once()
            ->andReturn([
                'url' => 'https://accounts.google.com/o/oauth2/v2/auth?state=test-state',
                'state' => 'test-state',
            ]);

        $this->app->instance(GoogleOAuthService::class, $service);

        $response = $this->get(route('auth.google.redirect', ['intent' => 'login']));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth?state=test-state');
        $this->assertSame('test-state', session('google_oauth_state'));
        $this->assertSame('login', session('google_oauth_intent'));
    }

    public function test_existing_users_are_linked_by_email_when_signing_in_with_google(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
            'google_avatar' => null,
        ]);

        $service = Mockery::mock(GoogleOAuthService::class);
        $service->shouldReceive('getUserFromCode')
            ->once()
            ->with('google-code')
            ->andReturn(new GoogleUser([
                'sub' => 'google-user-123',
                'email' => 'existing@example.com',
                'name' => 'Existing User',
                'picture' => 'https://example.com/avatar.png',
            ]));

        $this->app->instance(GoogleOAuthService::class, $service);

        $response = $this->withSession([
            'google_oauth_state' => 'valid-state',
            'google_oauth_intent' => 'login',
        ])->get(route('auth.google.callback', [
            'code' => 'google-code',
            'state' => 'valid-state',
        ]));

        $user->refresh();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
        $this->assertSame('google-user-123', $user->google_id);
        $this->assertSame('https://example.com/avatar.png', $user->google_avatar);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_new_users_can_register_with_google(): void
    {
        $service = Mockery::mock(GoogleOAuthService::class);
        $service->shouldReceive('getUserFromCode')
            ->once()
            ->with('new-google-code')
            ->andReturn(new GoogleUser([
                'sub' => 'google-user-999',
                'email' => 'nuevo@example.com',
                'name' => 'Nueva Persona',
                'picture' => 'https://example.com/nuevo.png',
            ]));

        $this->app->instance(GoogleOAuthService::class, $service);

        $response = $this->withSession([
            'google_oauth_state' => 'register-state',
            'google_oauth_intent' => 'register',
        ])->get(route('auth.google.callback', [
            'code' => 'new-google-code',
            'state' => 'register-state',
        ]));

        $user = User::query()->where('email', 'nuevo@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
        $this->assertSame('google-user-999', $user->google_id);
        $this->assertSame('https://example.com/nuevo.png', $user->google_avatar);
        $this->assertNotNull($user->email_verified_at);
    }
}
