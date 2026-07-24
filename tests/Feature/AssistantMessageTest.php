<?php

namespace Tests\Feature;

use App\Models\AssistantMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_the_persistent_assistant(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-miyu-assistant', false)
            ->assertSee('yuri-neko-open.webp', false)
            ->assertSee('yuri-neko-blink.webp', false)
            ->assertSee('Minimizar a Miyu')
            ->assertSee('¡Hola! Soy tu asistente Miyu.')
            ->assertSee('¿Encontraste un problema en la página? Ayúdanos a mejorar.')
            ->assertSee('¿Hay alguna serie o película que te encantaría ver aquí?')
            ->assertSee('Crea una cuenta gratis para preparar tu lista de favoritas y descubrir las próximas novedades.')
            ->assertSee('data-miyu-peek', false)
            ->assertSee("const startsMinimized = storedState !== 'expanded';", false)
            ->assertSee("localStorage.setItem(storageKey, 'expanded');", false)
            ->assertSee('¿Quieres mandarnos un mensaje?')
            ->assertDontSee('Dime otra cosa');
    }

    public function test_guest_can_send_a_request_from_the_assistant(): void
    {
        $response = $this->postJson(route('assistant-messages.store'), [
            'type' => 'request',
            'contact_email' => 'viewer@example.com',
            'message' => 'Me gustaría que agregaran esta película al catálogo.',
            'page_url' => route('catalog.series.index'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', '¡Listo! Tu mensaje llegó al equipo.');

        $this->assertDatabaseHas('assistant_messages', [
            'type' => 'request',
            'contact_email' => 'viewer@example.com',
            'status' => 'unread',
        ]);
    }

    public function test_authenticated_message_uses_the_account_email_when_none_is_supplied(): void
    {
        $user = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($user)
            ->postJson(route('assistant-messages.store'), [
                'type' => 'report',
                'message' => 'El botón del episodio no responde en esta página.',
                'page_url' => route('home'),
            ])
            ->assertCreated();

        $message = AssistantMessage::firstOrFail();

        $this->assertSame($user->id, $message->user_id);
        $this->assertSame('member@example.com', $message->contact_email);
    }

    public function test_assistant_rejects_short_or_unknown_messages(): void
    {
        $this->postJson(route('assistant-messages.store'), [
            'type' => 'other',
            'message' => 'Corto',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'message']);
    }
}
