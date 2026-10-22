<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'episode_email_notifications_enabled' => true,
        ]);
        $this->assertNotNull(auth()->user()->last_login_at);
        Notification::assertSentTo(User::query()->where('email', 'test@example.com')->firstOrFail(), VerifyEmail::class);
        Mail::assertNothingSent();
    }

    public function test_registration_from_a_series_returns_to_that_series(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])->postJson('/register', [
            'name' => 'Nueva lectora',
            'email' => 'lectora@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'return' => url('/series/una-serie'),
        ]);

        $response->assertOk()->assertJsonPath('redirect', url('/series/una-serie'));
        $this->assertAuthenticated();
    }

    public function test_registration_rejects_the_honeypot_field(): void
    {
        $response = $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])->post('/register', [
            'name' => 'Bot User',
            'email' => 'bot@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'website' => 'https://spam.example',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
    }

    public function test_registration_rejects_an_invalid_turnstile_challenge(): void
    {
        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.secret_key', 'test-secret');
        Http::fake(['https://challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $response = $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])->post('/register', [
            'name' => 'Bot User',
            'email' => 'turnstile-bot@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'cf-turnstile-response' => 'invalid-token',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'turnstile-bot@example.com']);
    }

    public function test_registration_is_limited_per_ip_address(): void
    {
        $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])
            ->post('/register', [])
            ->assertSessionHasErrors('email');
        $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])
            ->post('/register', [])
            ->assertSessionHasErrors('email');
        $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])
            ->post('/register', [])
            ->assertSessionHasErrors('email');

        $this->withSession(['registration_started_at' => now()->subSeconds(3)->timestamp])
            ->post('/register', [])
            ->assertTooManyRequests();
    }

    public function test_unverified_members_cannot_publish_comments(): void
    {
        $member = User::factory()->unverified()->create();

        $this->actingAs($member)
            ->post('/comentarios', [])
            ->assertRedirect(route('verification.notice'));
    }
}
