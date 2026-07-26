<?php

namespace Tests\Feature;

use App\Models\AssistantSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssistantSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_update_miyu_behavior_and_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.assistant.edit'))
            ->assertOk()
            ->assertSee('Configurar Miyu')
            ->assertSee('Primera pregunta')
            ->assertSee('Preguntas y mensajes');

        $this->actingAs($admin)
            ->put(route('admin.settings.assistant.update'), [
                'enabled' => '1',
                'initial_state' => 'expanded',
                'remember_user_state' => '0',
                'initial_delay_seconds' => 4,
                'message_interval_seconds' => 45,
                'bubble_duration_seconds' => 9,
                'peek_duration_seconds' => 6,
                'messages' => [
                    [
                        'text' => '¿Quieres recibir recomendaciones personalizadas?',
                        'peek' => '¿Buscas una recomendación?',
                        'audience' => 'all',
                        'action_type' => 'message',
                        'label' => 'Cuéntame',
                        'url' => '',
                    ],
                ],
            ])
            ->assertRedirect();

        $settings = AssistantSetting::query()->firstOrFail();

        $this->assertTrue($settings->enabled);
        $this->assertSame('expanded', $settings->initial_state);
        $this->assertFalse($settings->remember_user_state);
        $this->assertSame(4, $settings->initial_delay_seconds);
        $this->assertSame(45, $settings->message_interval_seconds);
        $this->assertSame('¿Quieres recibir recomendaciones personalizadas?', $settings->messages[0]['text']);
        $this->assertSame('message', $settings->messages[0]['action_type']);
    }

    public function test_configured_behavior_and_audience_are_rendered_on_public_pages(): void
    {
        AssistantSetting::query()->create([
            ...AssistantSetting::defaults(),
            'id' => 1,
            'initial_state' => 'expanded',
            'remember_user_state' => false,
            'initial_delay_seconds' => 3,
            'message_interval_seconds' => 50,
            'messages' => [
                [
                    'text' => 'Mensaje para todas',
                    'peek' => '',
                    'audience' => 'all',
                    'action_type' => 'none',
                    'label' => '',
                    'url' => '',
                ],
                [
                    'text' => 'Mensaje exclusivo para visitantes',
                    'peek' => '',
                    'audience' => 'guest',
                    'action_type' => 'register',
                    'label' => 'Crear cuenta',
                    'url' => '',
                ],
                [
                    'text' => 'Mensaje exclusivo para miembros',
                    'peek' => '',
                    'audience' => 'authenticated',
                    'action_type' => 'catalog',
                    'label' => 'Ver catálogo',
                    'url' => '',
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mensaje para todas')
            ->assertSee('Mensaje exclusivo para visitantes')
            ->assertDontSee('Mensaje exclusivo para miembros')
            ->assertSee('initialState', false)
            ->assertSee('initialDelayMs', false)
            ->assertSee('messageIntervalMs', false);

        $this->assertSame([
            'initialState' => 'expanded',
            'rememberUserState' => false,
            'initialDelayMs' => 3000,
            'messageIntervalMs' => 50000,
            'bubbleDurationMs' => 7000,
            'peekDurationMs' => 7000,
        ], AssistantSetting::current()->clientConfig());

        $viewer = User::factory()->create(['role' => 'user']);

        $this->actingAs($viewer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Mensaje para todas')
            ->assertDontSee('Mensaje exclusivo para visitantes')
            ->assertSee('Mensaje exclusivo para miembros');
    }

    public function test_admin_can_disable_miyu_and_moderator_cannot_change_settings(): void
    {
        AssistantSetting::query()->create([
            ...AssistantSetting::defaults(),
            'id' => 1,
            'enabled' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-miyu-assistant', false);

        $moderator = User::factory()->create(['role' => 'moderator']);

        $this->actingAs($moderator)
            ->get(route('admin.settings.assistant.edit'))
            ->assertRedirect('/');
    }

    public function test_external_action_requires_a_valid_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.settings.assistant.edit'))
            ->put(route('admin.settings.assistant.update'), [
                'enabled' => '1',
                'initial_state' => 'minimized',
                'remember_user_state' => '1',
                'initial_delay_seconds' => 10,
                'message_interval_seconds' => 30,
                'bubble_duration_seconds' => 7,
                'peek_duration_seconds' => 7,
                'messages' => [
                    [
                        'text' => 'Visita nuestro sitio relacionado',
                        'peek' => '',
                        'audience' => 'all',
                        'action_type' => 'external',
                        'label' => 'Abrir',
                        'url' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.settings.assistant.edit'))
            ->assertSessionHasErrors('messages.0.url');
    }
}
