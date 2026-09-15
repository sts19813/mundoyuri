<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
        $this->assertNotNull($user->refresh()->last_login_at);
    }

    public function test_regular_users_return_to_the_public_page_they_visited_before_login(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->get(route('login', ['return' => url('/episodios/episodio-de-prueba')]));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/episodios/episodio-de-prueba');
    }

    public function test_admins_and_moderators_enter_the_dashboard_without_a_pending_page(): void
    {
        foreach (['admin', 'moderator'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertRedirect(route('dashboard', absolute: false));
            $this->post('/logout');
        }
    }

    public function test_login_from_a_public_page_returns_json_without_leaving_the_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->get('/');
        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
            'return' => url('/series/una-serie?tab=episodios'),
        ]);

        $response->assertOk()->assertJsonPath('redirect', url('/series/una-serie?tab=episodios'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_preserves_a_pending_admin_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->withSession(['url.intended' => '/admin/series'])->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/series');
    }

    public function test_visiting_an_admin_page_first_returns_there_after_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/admin/dashboard')->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_an_external_return_url_is_ignored(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
            'return' => 'https://example.com/private',
        ]);

        $response->assertOk()->assertJsonPath('redirect', url('/'));
    }

    public function test_login_errors_are_returned_to_the_modal(): void
    {
        $user = User::factory()->create();

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_home_page_renders_the_login_and_registration_dialog(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('portal-auth-dialog')
            ->assertSee('Continuar con Google')
            ->assertSee('Registrarme con Google')
            ->assertSee('Crea tu cuenta y haz crecer la comunidad.');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
