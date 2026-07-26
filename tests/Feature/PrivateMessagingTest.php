<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_exchange_private_messages_and_recipient_can_read_them(): void
    {
        $sender = User::factory()->create([
            'name' => 'Luna Remitente',
            'alias' => 'luna',
        ]);
        $recipient = User::factory()->create([
            'name' => 'Mio Receptora',
            'alias' => 'mio',
        ]);

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => '¿Viste el nuevo episodio de la serie?',
            ])
            ->assertRedirect(route('messages.show', $recipient))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('conversations', [
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
        ]);
        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'body' => '¿Viste el nuevo episodio de la serie?',
            'read_at' => null,
        ]);
        $this->assertDatabaseCount('notifications', 1);

        $this->actingAs($recipient)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('luna')
            ->assertSee('¿Viste el nuevo episodio de la serie?')
            ->assertSee('1 mensajes sin leer');

        $this->actingAs($recipient)
            ->get(route('messages.show', $sender))
            ->assertOk()
            ->assertSee('¿Viste el nuevo episodio de la serie?')
            ->assertSee('luna');

        $this->assertNotNull(DirectMessage::firstOrFail()->fresh()->read_at);
    }

    public function test_notifications_are_private_and_can_be_marked_as_read(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Este mensaje genera una notificación.',
            ]);

        $notification = $recipient->notifications()->firstOrFail();

        $this->actingAs($stranger)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->actingAs($recipient)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Nuevo mensaje')
            ->assertSee('1 pendientes por leer');

        $this->actingAs($recipient)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('messages.show', $sender));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($sender)
            ->post(route('users.follow.store', $recipient));

        $this->actingAs($recipient)
            ->patch(route('notifications.read-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $recipient->fresh()->unreadNotifications()->count());
    }

    public function test_follow_notification_is_created_only_for_a_new_follow(): void
    {
        $follower = User::factory()->create(['name' => 'Nueva Seguidora']);
        $profileUser = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('users.follow.store', $profileUser))
            ->assertRedirect();
        $this->actingAs($follower)
            ->post(route('users.follow.store', $profileUser))
            ->assertRedirect();

        $this->assertDatabaseCount('user_follows', 1);
        $this->assertDatabaseCount('notifications', 1);

        $this->actingAs($profileUser)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Tienes una nueva persona siguiéndote')
            ->assertSee('Nueva Seguidora comenzó a seguirte.');
    }

    public function test_blocking_removes_follows_and_prevents_interactions_in_both_directions(): void
    {
        $first = User::factory()->create(['name' => 'Primera Persona']);
        $second = User::factory()->create(['name' => 'Segunda Persona']);

        $first->following()->attach($second->id);
        $second->following()->attach($first->id);

        $this->actingAs($first)
            ->post(route('messages.store', $second), [
                'body' => 'Mensaje previo al bloqueo.',
            ])
            ->assertRedirect();

        $this->actingAs($first)
            ->post(route('users.block.store', $second))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('user_blocks', [
            'blocker_id' => $first->id,
            'blocked_id' => $second->id,
        ]);
        $this->assertDatabaseMissing('user_follows', [
            'follower_id' => $first->id,
            'followed_id' => $second->id,
        ]);
        $this->assertDatabaseMissing('user_follows', [
            'follower_id' => $second->id,
            'followed_id' => $first->id,
        ]);

        $this->actingAs($first)
            ->get(route('blocks.index'))
            ->assertOk()
            ->assertSee('Segunda Persona')
            ->assertSee('Desbloquear');

        $this->actingAs($first)
            ->post(route('messages.store', $second), ['body' => 'No debe enviarse.'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->actingAs($second)
            ->post(route('messages.store', $first), ['body' => 'Tampoco debe enviarse.'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->actingAs($second)
            ->post(route('users.follow.store', $first))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('direct_messages', 1);
        $this->assertDatabaseCount('user_follows', 0);

        $this->actingAs($first)
            ->get(route('messages.show', $second))
            ->assertOk()
            ->assertSee('Mensaje previo al bloqueo.')
            ->assertSee('Desbloquea a esta persona');

        $this->actingAs($first)
            ->delete(route('users.block.destroy', $second))
            ->assertRedirect();

        $this->actingAs($second)
            ->post(route('messages.store', $first), ['body' => 'Ya podemos conversar.'])
            ->assertRedirect(route('messages.show', $first));

        $this->assertDatabaseCount('direct_messages', 2);
    }

    public function test_guests_self_messages_and_invalid_messages_are_rejected(): void
    {
        $user = User::factory()->create();
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $this->post(route('messages.store', $user), ['body' => 'Mensaje de invitado'])
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->post(route('messages.store', $user), ['body' => 'Mensaje propio'])
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('messages.store', $inactiveUser), ['body' => 'Mensaje inactivo'])
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('messages.store', User::factory()->create()), ['body' => '   '])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('direct_messages', 0);
    }
}
