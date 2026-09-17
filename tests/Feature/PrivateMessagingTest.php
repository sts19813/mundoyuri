<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use Database\Seeders\RolePermissionSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
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
            ->assertSessionMissing('success');

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

    public function test_users_can_send_private_images_and_documents_and_only_participants_can_open_them(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'attachment' => UploadedFile::fake()->image('recuerdo.png', 640, 480),
            ])
            ->assertRedirect(route('messages.show', $recipient));

        $imageMessage = DirectMessage::query()->firstOrFail();
        $this->assertSame('', $imageMessage->body);
        $this->assertContains($imageMessage->attachment_name, ['recuerdo.png', 'recuerdo.webp', 'recuerdo.jpg']);
        $this->assertTrue($imageMessage->attachmentIsImage());
        Storage::disk('local')->assertExists($imageMessage->attachment_path);

        $this->get(route('messages.show', $recipient))
            ->assertOk()
            ->assertSee($imageMessage->attachment_name)
            ->assertSee(route('messages.attachments.show', $imageMessage), false);
        $this->get(route('messages.attachments.show', $imageMessage))
            ->assertOk()
            ->assertHeader('content-type', $imageMessage->attachment_mime);
        $this->actingAs($stranger)
            ->get(route('messages.attachments.show', $imageMessage))
            ->assertNotFound();

        $this->actingAs($recipient)
            ->post(route('messages.store', $sender), [
                'body' => 'Te comparto el documento.',
                'attachment' => UploadedFile::fake()->create('guia.pdf', 80, 'application/pdf'),
            ])
            ->assertRedirect(route('messages.show', $sender));

        $documentMessage = DirectMessage::query()->latest('id')->firstOrFail();
        $this->assertFalse($documentMessage->attachmentIsImage());
        $this->actingAs($sender)
            ->get(route('messages.attachments.show', $documentMessage))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename=guia.pdf');
    }

    public function test_gif_attachments_are_not_converted_so_animation_can_remain(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'attachment' => UploadedFile::fake()->create('animacion.gif', 24, 'image/gif'),
            ])
            ->assertRedirect(route('messages.show', $recipient));

        $message = DirectMessage::query()->firstOrFail();

        $this->assertSame('animacion.gif', $message->attachment_name);
        $this->assertSame('image/gif', $message->attachment_mime);
        $this->assertStringEndsWith('.gif', $message->attachment_path);
    }

    public function test_sender_can_delete_message_without_removing_database_content_for_moderation(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Mensaje que quiero ocultar.',
                'attachment' => UploadedFile::fake()->image('secreto.png', 640, 480),
            ]);

        $message = DirectMessage::query()->firstOrFail();

        $this->actingAs($sender)
            ->delete(route('messages.destroy', $message))
            ->assertRedirect()
            ->assertSessionHas('success');

        $message = $message->fresh();
        $this->assertNotNull($message->deleted_at);
        $this->assertSame('Mensaje que quiero ocultar.', $message->body);
        $this->assertNotNull($message->attachment_path);

        $this->actingAs($recipient)
            ->get(route('messages.show', $sender))
            ->assertOk()
            ->assertSee('Mensaje eliminado')
            ->assertDontSee('Mensaje que quiero ocultar.');

        $this->get(route('messages.attachments.show', $message))->assertNotFound();
    }

    public function test_private_message_attachments_reject_unsupported_files(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'attachment' => UploadedFile::fake()->create('programa.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('attachment');

        $this->assertDatabaseCount('direct_messages', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('direct-message-attachments'));
    }

    public function test_messenger_workspace_keeps_the_conversation_list_beside_the_active_chat_and_supports_search(): void
    {
        $viewer = User::factory()->create();
        $luna = User::factory()->create(['name' => 'Luna Rosa', 'alias' => 'lunarosa']);
        $sol = User::factory()->create(['name' => 'Sol Azul', 'alias' => 'solazul']);

        $this->actingAs($luna)->post(route('messages.store', $viewer), ['body' => 'Mensaje de Luna']);
        $this->actingAs($sol)->post(route('messages.store', $viewer), ['body' => 'Mensaje de Sol']);

        $this->actingAs($viewer)
            ->get(route('messages.index').'?q=Luna')
            ->assertOk()
            ->assertSee('messenger-shell-empty', false)
            ->assertSee('lunarosa')
            ->assertSee('Mensaje de Luna')
            ->assertDontSee('solazul')
            ->assertDontSee('Mensaje de Sol');

        $this->get(route('messages.show', $luna))
            ->assertOk()
            ->assertSee('messenger-shell has-active-chat', false)
            ->assertSee('messenger-sidebar', false)
            ->assertSee('messenger-chat', false)
            ->assertSee('messenger-mobile-back', false)
            ->assertSee('action="'.route('messages.show', $luna).'"', false)
            ->assertDontSee('data-miyu-assistant', false)
            ->assertSee('lunarosa')
            ->assertSee('solazul')
            ->assertSee('Mensaje de Luna');
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

    public function test_admin_can_review_private_conversations_and_deleted_message_content(): void
    {
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $sender = User::factory()->create(['name' => 'Remitente Chat']);
        $recipient = User::factory()->create(['name' => 'Receptora Chat']);

        $this->actingAs($sender)->post(route('messages.store', $recipient), ['body' => 'Contenido moderable.']);
        $message = DirectMessage::query()->firstOrFail();
        $this->actingAs($sender)->delete(route('messages.destroy', $message));

        $this->actingAs($admin)
            ->get(route('admin.conversations.index'))
            ->assertOk()
            ->assertSee('Remitente Chat')
            ->assertSee('Receptora Chat')
            ->assertSee('Mensaje eliminado');

        $this->get(route('admin.conversations.show', $message->conversation))
            ->assertOk()
            ->assertSee('Contenido moderable.')
            ->assertSee('Eliminado por');
    }
}
