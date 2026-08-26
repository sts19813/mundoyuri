<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_table_contains_login_and_email_notification_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'episode_email_notifications_enabled' => false,
            'last_login_at' => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText('Correos')
            ->assertSeeText('Última sesión')
            ->assertSee('email_notifications_enabled', false)
            ->assertSee('last_login_at', false)
            ->assertSee('Habilitados')
            ->assertSee('Desactivados')
            ->assertSee('Sin acceso registrado');
    }
}
