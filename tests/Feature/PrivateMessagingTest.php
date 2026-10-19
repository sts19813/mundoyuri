<?php

namespace Tests\Feature;

use App\Models\CommunityReaction;
use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\Sticker;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
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
        $this->assertDatabaseCount('notifications', 0);

        $this->actingAs($recipient)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('luna')
            ->assertSee('¿Viste el nuevo episodio de la serie?')
            ->assertSee('1 mensajes sin leer')
            ->assertSee('data-device-notice', false)
            ->assertSee('data-push-enable', false)
            ->assertSee('MundoYuri jamás te enviará spam');

        $this->actingAs($recipient)
            ->get(route('messages.show', $sender))
            ->assertOk()
            ->assertSee('¿Viste el nuevo episodio de la serie?')
            ->assertSee('luna')
            ->assertSee('data-device-notice', false);

        $this->assertNotNull(DirectMessage::firstOrFail()->fresh()->read_at);
    }

    public function test_private_chat_can_poll_new_messages_and_marks_them_as_read(): void
    {
        $sender = User::factory()->create(['alias' => 'luna']);
        $recipient = User::factory()->create(['alias' => 'mio']);
        $stranger = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Mensaje vivo para el chat.',
            ]);

        $message = DirectMessage::query()->firstOrFail();

        $this->actingAs($recipient)
            ->getJson(route('messages.poll', $sender).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('messages.0.id', $message->id)
            ->assertJsonPath('messages.0.incoming', true)
            ->assertJsonFragment(['latest_message_id' => $message->id])
            ->assertSee('Mensaje vivo para el chat.');

        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($stranger)
            ->getJson(route('messages.poll', $sender).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('messages', []);
    }

    public function test_private_message_status_returns_recent_chats_without_marking_unread_as_read(): void
    {
        $sender = User::factory()->create(['name' => 'Luna Remitente', 'alias' => 'luna']);
        $recipient = User::factory()->create(['name' => 'Mio Receptora', 'alias' => 'mio']);

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Mensaje pendiente para el navbar.',
            ]);

        $message = DirectMessage::query()->firstOrFail();

        $this->actingAs($recipient)
            ->getJson(route('messages.status'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('latest_unread_message_id', $message->id)
            ->assertJsonPath('latest_unread.title', 'luna')
            ->assertJsonPath('conversations.0.user_name', 'luna')
            ->assertJsonPath('conversations.0.unread_count', 1)
            ->assertJsonPath('conversations.0.preview', 'Mensaje pendiente para el navbar.');

        $this->assertNull($message->fresh()->read_at);
    }

    public function test_private_message_json_send_returns_rendered_message_without_redirect(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)
            ->postJson(route('messages.store', $recipient), [
                'body' => 'Se envió sin recargar.',
            ])
            ->assertCreated()
            ->assertJsonPath('messages.0.incoming', false);

        $message = DirectMessage::query()->firstOrFail();

        $response
            ->assertJsonPath('messages.0.id', $message->id)
            ->assertJsonFragment(['latest_message_id' => $message->id]);

        $this->assertStringContainsString('Se envió sin recargar.', $response->json('messages.0.html'));
    }

    public function test_private_messages_can_reply_to_previous_messages(): void
    {
        $sender = User::factory()->create(['name' => 'Luna']);
        $recipient = User::factory()->create(['name' => 'Mio']);

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Mensaje original para citar.',
            ]);

        $original = DirectMessage::query()->firstOrFail();

        $this->actingAs($recipient)
            ->post(route('messages.store', $sender), [
                'body' => 'Esta es mi respuesta.',
                'reply_to_message_id' => $original->id,
            ])
            ->assertRedirect(route('messages.show', $sender));

        $reply = DirectMessage::query()->latest('id')->firstOrFail();

        $this->assertSame($original->id, $reply->reply_to_message_id);

        $this->actingAs($sender)
            ->get(route('messages.show', $recipient))
            ->assertOk()
            ->assertSee('Mensaje original para citar.')
            ->assertSee('Esta es mi respuesta.')
            ->assertSee('messenger-reply-quote', false);
    }

    public function test_private_messages_can_be_reacted_to_with_existing_reactions(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'body' => 'Mensaje con reacción.',
            ]);

        $message = DirectMessage::query()->firstOrFail();
        $this->assertArrayHasKey('surprise', CommunityReaction::types());
        $this->assertArrayHasKey('yuri', CommunityReaction::types());

        $this->actingAs($recipient)
            ->postJson(route('messages.reactions.store', $message), [
                'type' => 'surprise',
            ])
            ->assertOk()
            ->assertJsonPath('active_type', 'surprise');

        $response = $this->actingAs($recipient)
            ->postJson(route('messages.reactions.store', $message), [
                'type' => 'yuri',
            ])
            ->assertOk()
            ->assertJsonPath('active_type', 'yuri');

        $this->assertStringContainsString('🌸', $response->json('html'));

        $this->assertDatabaseHas('direct_message_reactions', [
            'direct_message_id' => $message->id,
            'user_id' => $recipient->id,
            'type' => 'yuri',
        ]);

        $this->actingAs($recipient)
            ->postJson(route('messages.reactions.store', $message), [
                'type' => 'love',
            ])
            ->assertOk()
            ->assertJsonPath('active_type', 'love');

        $this->assertDatabaseMissing('direct_message_reactions', [
            'direct_message_id' => $message->id,
            'user_id' => $recipient->id,
            'type' => 'yuri',
        ]);
        $this->assertDatabaseHas('direct_message_reactions', [
            'direct_message_id' => $message->id,
            'user_id' => $recipient->id,
            'type' => 'love',
        ]);

        $this->actingAs($recipient)
            ->postJson(route('messages.reactions.store', $message), [
                'type' => 'love',
            ])
            ->assertOk()
            ->assertJsonPath('active_type', null);

        $this->assertDatabaseCount('direct_message_reactions', 0);

        $this->actingAs($stranger)
            ->postJson(route('messages.reactions.store', $message), [
                'type' => 'like',
            ])
            ->assertNotFound();
    }

    public function test_notification_center_items_are_private_and_can_be_marked_as_read(): void
    {
        $follower = User::factory()->create(['name' => 'Luna Seguidora']);
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('users.follow.store', $recipient));

        $notification = $recipient->notifications()->firstOrFail();

        $this->actingAs($stranger)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->actingAs($recipient)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Tienes una nueva persona siguiéndote')
            ->assertSee('1 pendientes por leer')
            ->assertSee('Borrar todas');

        $this->actingAs($recipient)
            ->get(route('notifications.open', $notification))
            ->assertRedirect($follower->publicProfileUrl());

        $this->assertNotNull($notification->fresh()->read_at);

        $secondFollower = User::factory()->create();

        $this->actingAs($secondFollower)
            ->post(route('users.follow.store', $recipient));

        $this->actingAs($recipient)
            ->patch(route('notifications.read-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $recipient->fresh()->unreadNotifications()->count());
    }

    public function test_users_can_delete_only_their_own_notifications(): void
    {
        $follower = User::factory()->create();
        $recipient = User::factory()->create();
        $otherRecipient = User::factory()->create();

        $this->actingAs($follower)->post(route('users.follow.store', $recipient));
        $this->actingAs($follower)->post(route('users.follow.store', $otherRecipient));

        $this->assertSame(1, $recipient->notifications()->count());
        $this->assertSame(1, $otherRecipient->notifications()->count());

        $this->actingAs($recipient)
            ->delete(route('notifications.destroy-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $recipient->fresh()->notifications()->count());
        $this->assertSame(1, $otherRecipient->fresh()->notifications()->count());
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
        $this->assertMatchesRegularExpression('/^mundoyuri-\d{4}-\d{2}-\d{2}-\d{1,2}\d{2}(am|pm)\.(webp|jpg)$/', $imageMessage->attachment_name);
        $this->assertTrue($imageMessage->attachmentIsImage());
        Storage::disk('local')->assertExists($imageMessage->attachment_path);

        $conversationResponse = $this->get(route('messages.show', $recipient))
            ->assertOk()
            ->assertSee($imageMessage->attachment_name)
            ->assertSee(route('messages.attachments.show', $imageMessage), false)
            ->assertSee('data-image-lightbox', false)
            ->assertSee('data-image-viewer', false)
            ->assertSee('Pellizca, arrastra o toca dos veces para ampliar');
        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*class="messenger-attachment-image"[^>]*target="_blank"/',
            $conversationResponse->getContent(),
        );
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

        $this->assertMatchesRegularExpression('/^mundoyuri-\d{4}-\d{2}-\d{2}-\d{1,2}\d{2}(am|pm)\.gif$/', $message->attachment_name);
        $this->assertSame('image/gif', $message->attachment_mime);
        $this->assertStringEndsWith('.gif', $message->attachment_path);
    }

    public function test_chat_loads_older_messages_without_reloading_the_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::create([
            'user_one_id' => min($sender->id, $recipient->id),
            'user_two_id' => max($sender->id, $recipient->id),
            'last_message_at' => now(),
        ]);

        foreach (range(1, 55) as $number) {
            $conversation->messages()->create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'body' => 'Mensaje de historial '.$number,
            ]);
        }

        $oldestVisible = $conversation->messages()->latest('id')->skip(49)->firstOrFail();

        $this->actingAs($recipient)
            ->getJson(route('messages.older', $sender).'?before_id='.$oldestVisible->id)
            ->assertOk()
            ->assertJsonCount(5, 'messages')
            ->assertJsonPath('has_more', false)
            ->assertSee('Mensaje de historial 1');
    }

    public function test_chat_exposes_media_documents_and_links_only_to_participants(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($sender)->post(route('messages.store', $recipient), [
            'body' => 'Mira https://mundoyuri.com/series/harmony-secret.',
            'attachment' => UploadedFile::fake()->image('foto.png', 20, 20),
        ]);
        $this->actingAs($sender)->post(route('messages.store', $recipient), [
            'attachment' => UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf'),
        ]);
        $this->actingAs($sender)->post(route('messages.store', $recipient), [
            'is_sticker' => true,
            'attachment' => UploadedFile::fake()->image('sticker.png', 20, 20),
        ]);

        $this->actingAs($recipient)
            ->getJson(route('messages.media', $sender).'?type=images')
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonMissing(['name' => 'sticker.png']);
        $this->getJson(route('messages.media', $sender).'?type=documents')
            ->assertOk()
            ->assertJsonCount(1, 'items');
        $this->getJson(route('messages.media', $sender).'?type=links')
            ->assertOk()
            ->assertJsonPath('items.0.url', 'https://mundoyuri.com/series/harmony-secret');
        $this->get(route('messages.show', $sender))
            ->assertOk()
            ->assertSee('messenger-inline-link', false)
            ->assertSee('href="https://mundoyuri.com/series/harmony-secret"', false);

        $this->actingAs($stranger)
            ->getJson(route('messages.media', $sender).'?type=images')
            ->assertOk()
            ->assertJsonPath('items', []);
    }

    public function test_users_can_send_transparent_or_animated_stickers(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), [
                'is_sticker' => true,
                'attachment' => UploadedFile::fake()->create('brillo.gif', 12, 'image/gif'),
            ])
            ->assertRedirect();

        $sticker = DirectMessage::query()->firstOrFail();
        $this->assertTrue($sticker->is_sticker);
        $this->assertSame('image/gif', $sticker->attachment_mime);
        $this->actingAs($recipient)
            ->get(route('messages.show', $sender))
            ->assertOk()
            ->assertSee('is-sticker', false);
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

    public function test_users_only_see_and_manage_their_own_private_stickers(): void
    {
        Storage::fake('local');
        $first = User::factory()->create();
        $second = User::factory()->create();

        $general = Sticker::query()->create([
            'owner_id' => $first->id,
            'name' => 'general.png',
            'path' => 'stickers/general.png',
            'mime' => 'image/png',
            'size' => 42,
            'is_general' => true,
            'approved_at' => now(),
            'approved_by' => $first->id,
        ]);
        $private = Sticker::query()->create([
            'owner_id' => $first->id,
            'name' => 'privado.png',
            'path' => 'stickers/privado.png',
            'mime' => 'image/png',
            'size' => 42,
        ]);
        Storage::disk('local')->put($general->path, 'general');
        Storage::disk('local')->put($private->path, 'privado');

        $this->actingAs($second)
            ->getJson(route('stickers.index'))
            ->assertOk()
            ->assertJsonPath('general.0.id', $general->id)
            ->assertJsonPath('mine', [])
            ->assertJsonMissing(['id' => $private->id]);

        $this->actingAs($second)
            ->get(route('stickers.file', $general))
            ->assertOk()
            ->assertHeader('cache-control', 'max-age=86400, private');

        $this->actingAs($second)->get(route('stickers.file', $private))->assertNotFound();
        $this->actingAs($second)->deleteJson(route('stickers.destroy', $private))->assertForbidden();
        $this->actingAs($first)->deleteJson(route('stickers.destroy', $private))->assertNoContent();
        $this->assertDatabaseMissing('stickers', ['id' => $private->id]);
    }

    public function test_user_can_create_a_private_sticker_and_send_only_visible_stickers(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($sender)
            ->post(route('stickers.store'), ['sticker' => UploadedFile::fake()->image('mi-sticker.png', 120, 120)])
            ->assertCreated();
        $sticker = Sticker::query()->firstOrFail();

        $response->assertJsonPath('sticker.id', $sticker->id);
        $this->assertFalse($sticker->is_general);

        $this->actingAs($sender)
            ->post(route('messages.store', $recipient), ['sticker_id' => $sticker->id])
            ->assertRedirect(route('messages.show', $recipient));

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $sender->id,
            'attachment_path' => $sticker->path,
            'is_sticker' => true,
        ]);

        $this->actingAs($other)
            ->post(route('messages.store', $recipient), ['sticker_id' => $sticker->id])
            ->assertSessionHasErrors('sticker_id');
    }

    public function test_admin_can_publish_and_approve_personal_stickers(): void
    {
        Storage::fake('local');
        $this->seed(RolePermissionSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $creator = User::factory()->create();
        $private = Sticker::query()->create([
            'owner_id' => $creator->id,
            'name' => 'candidata.gif',
            'path' => 'stickers/candidata.gif',
            'mime' => 'image/gif',
            'size' => 42,
        ]);
        Storage::disk('local')->put($private->path, 'GIF89a');

        $this->actingAs($admin)
            ->get(route('admin.stickers.index'))
            ->assertOk()
            ->assertSee('candidata.gif')
            ->assertSee('Personal');

        $this->patch(route('admin.stickers.approve', $private))
            ->assertRedirect();
        $this->assertDatabaseHas('stickers', [
            'id' => $private->id,
            'is_general' => true,
            'approved_by' => $admin->id,
        ]);

        $this->post(route('admin.stickers.store'), [
            'sticker' => UploadedFile::fake()->image('oficial.jpg', 120, 120),
        ])->assertRedirect();
        $this->assertDatabaseHas('stickers', [
            'name' => 'oficial.jpg',
            'is_general' => true,
            'approved_by' => $admin->id,
        ]);
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
