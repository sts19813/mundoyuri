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

    public function test_admins_and_moderators_always_enter_the_dashboard(): void
    {
        foreach (['admin', 'moderator'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->withSession(['url.intended' => '/series/una-serie'])->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $response->assertRedirect(route('dashboard', absolute: false));
            $this->post('/logout');
        }
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
