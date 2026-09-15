<?php

namespace Tests\Feature\Auth;

use App\Mail\WelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'episode_email_notifications_enabled' => true,
        ]);
        $this->assertNotNull(auth()->user()->last_login_at);
        Mail::assertSent(WelcomeMail::class, fn (WelcomeMail $mail): bool => $mail->hasTo('test@example.com')
            && str_contains($mail->render(), 'Qué gusto tenerte aquí, Test User'));
    }

    public function test_registration_from_a_series_returns_to_that_series(): void
    {
        Mail::fake();

        $response = $this->postJson('/register', [
            'name' => 'Nueva lectora',
            'email' => 'lectora@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'return' => url('/series/una-serie'),
        ]);

        $response->assertOk()->assertJsonPath('redirect', url('/series/una-serie'));
        $this->assertAuthenticated();
    }
}
