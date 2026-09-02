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

    public function test_admin_can_disable_and_enable_a_users_email_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create([
            'episode_email_notifications_enabled' => true,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.users.email-notifications.update', $user), [
                'enabled' => false,
            ])
            ->assertOk()
            ->assertJsonPath('email_notifications_enabled', false);

        $this->assertFalse($user->refresh()->episode_email_notifications_enabled);

        $this->actingAs($admin)
            ->patchJson(route('admin.users.email-notifications.update', $user), [
                'enabled' => true,
            ])
            ->assertOk()
            ->assertJsonPath('email_notifications_enabled', true);

        $this->assertTrue($user->refresh()->episode_email_notifications_enabled);
    }

    public function test_regular_user_cannot_change_another_users_email_notifications(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $user = User::factory()->create([
            'episode_email_notifications_enabled' => true,
        ]);

        $this->actingAs($regularUser)
            ->patchJson(route('admin.users.email-notifications.update', $user), [
                'enabled' => false,
            ])
            ->assertRedirect('/');

        $this->assertTrue($user->refresh()->episode_email_notifications_enabled);
    }
}
