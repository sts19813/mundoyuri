<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Notifications\NewDirectMessageNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConversationFoldersTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_chat_keeps_unread_separate_and_sends_no_push(): void
    {
        Notification::fake();
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $this->actingAs($sender)->post(route('messages.store', $recipient), ['body' => 'Primer mensaje']);
        $chat = Conversation::firstOrFail();
        $this->actingAs($recipient)->post(route('messages.archive', $chat))->assertRedirect();
        Notification::fake();
        $this->actingAs($sender)->post(route('messages.store', $recipient), ['body' => 'Mensaje silencioso'])->assertRedirect();
        Notification::assertNotSentTo($recipient, NewDirectMessageNotification::class);

        $this->actingAs($recipient)->getJson(route('messages.status'))->assertOk()
            ->assertJsonPath('unread_count', 0)->assertJsonPath('archived_unread_count', 2)
            ->assertJsonPath('latest_unread', null)->assertJsonPath('conversations', []);
        $this->get(route('messages.index'))->assertDontSee('Mensaje silencioso');
        $this->get(route('messages.index', ['archived' => 1]))->assertSee('Mensaje silencioso');
        $this->actingAs($sender)->get(route('messages.index'))->assertSee('Mensaje silencioso');
        $this->actingAs($recipient)->delete(route('messages.unarchive', $chat))->assertRedirect();
        $this->getJson(route('messages.status'))->assertJsonPath('unread_count', 2)->assertJsonPath('archived_unread_count', 0);
    }

    public function test_delete_is_personal_preserves_moderation_and_new_messages_reopen_chat(): void
    {
        Storage::fake('local');
        $this->seed(RolePermissionSeeder::class);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        [$a, $b] = Conversation::participantIds($sender, $recipient);
        $chat = Conversation::create(['user_one_id' => $a, 'user_two_id' => $b, 'last_message_at' => now()]);
        Storage::disk('local')->put('old.txt', 'private file');
        $old = $chat->messages()->create(['sender_id' => $sender->id, 'recipient_id' => $recipient->id, 'body' => 'Mensaje anterior', 'attachment_path' => 'old.txt', 'attachment_name' => 'old.txt', 'attachment_mime' => 'text/plain']);

        $this->actingAs($recipient)->delete(route('messages.conversation.destroy', $chat))->assertRedirect();
        $this->get(route('messages.index'))->assertDontSee('Mensaje anterior');
        $this->get(route('messages.show', $sender))->assertDontSee('Mensaje anterior');
        $this->get(route('messages.attachments.show', $old))->assertNotFound();
        $this->getJson(route('messages.older', $sender).'?before_id='.($old->id + 1))->assertJsonPath('messages', []);
        $this->getJson(route('messages.status'))->assertJsonPath('unread_count', 0);
        $this->actingAs($sender)->get(route('messages.show', $recipient))->assertSee('Mensaje anterior');
        $this->actingAs($admin)->get(route('admin.conversations.show', $chat))->assertOk()->assertSee('Mensaje anterior');
        $this->get(route('admin.conversations.attachments.show', $old))->assertOk();

        $this->actingAs($sender)->post(route('messages.store', $recipient), ['body' => 'Mensaje nuevo', 'reply_to_message_id' => $old->id])->assertRedirect();
        $this->actingAs($recipient)->getJson(route('messages.status'))->assertJsonPath('unread_count', 1)->assertJsonPath('conversations.0.preview', 'Mensaje nuevo');
        $this->get(route('messages.show', $sender))->assertSee('Mensaje nuevo')->assertDontSee('Mensaje anterior');
        $this->assertDatabaseHas('direct_messages', ['id' => $old->id, 'body' => 'Mensaje anterior']);
    }

    public function test_only_participants_can_change_conversation_state(): void
    {
        $one = User::factory()->create();
        $two = User::factory()->create();
        $stranger = User::factory()->create();
        [$a, $b] = Conversation::participantIds($one, $two);
        $chat = Conversation::create(['user_one_id' => $a, 'user_two_id' => $b]);
        $this->actingAs($stranger)->post(route('messages.archive', $chat))->assertNotFound();
        $this->delete(route('messages.conversation.destroy', $chat))->assertNotFound();
        $this->delete(route('messages.unarchive', $chat))->assertNotFound();
        $this->assertDatabaseCount('conversation_user_states', 0);
    }

    public function test_expired_large_attachment_remains_available_only_to_moderation(): void
    {
        Storage::fake('local');
        $this->seed(RolePermissionSeeder::class);
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        [$a, $b] = Conversation::participantIds($sender, $recipient);
        $chat = Conversation::create(['user_one_id' => $a, 'user_two_id' => $b]);
        Storage::disk('local')->put('large.pdf', 'retained');
        $message = $chat->messages()->create([
            'sender_id' => $sender->id, 'recipient_id' => $recipient->id, 'body' => 'Archivo',
            'attachment_path' => 'large.pdf', 'attachment_name' => 'large.pdf',
            'attachment_mime' => 'application/pdf', 'attachment_size' => 20 * 1024 * 1024,
        ]);
        $message->forceFill(['created_at' => now()->subDays(31)])->saveQuietly();
        $this->artisan('messages:prune-large-attachments')->assertSuccessful();
        $this->actingAs($recipient)->get(route('messages.attachments.show', $message))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.conversations.show', $chat))->assertSee('large.pdf');
        $this->get(route('admin.conversations.attachments.show', $message))->assertOk();
        Storage::disk('local')->assertExists('large.pdf');
    }
}
