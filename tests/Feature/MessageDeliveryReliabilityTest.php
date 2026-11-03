<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use App\Services\BadgeAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageDeliveryReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_retrying_a_send_returns_the_same_message_but_a_new_send_can_have_the_same_text(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $key = (string) Str::uuid();
        $this->actingAs($sender);

        $first = $this->postJson(route('messages.store', $recipient), ['body' => 'Hola', 'client_id' => $key])->assertCreated();
        $retry = $this->postJson(route('messages.store', $recipient), ['body' => 'Hola', 'client_id' => $key])->assertCreated();
        $this->assertSame($first->json('messages.0.id'), $retry->json('messages.0.id'));
        $this->assertDatabaseCount('direct_messages', 1);

        $this->postJson(route('messages.store', $recipient), ['body' => 'Hola', 'client_id' => (string) Str::uuid()])->assertCreated();
        $this->assertDatabaseCount('direct_messages', 2);
        $other = User::factory()->create();
        $this->postJson(route('messages.store', $other), ['body' => 'Hola', 'client_id' => $key])->assertStatus(409);
    }

    public function test_background_request_limit_does_not_block_sending(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        for ($i = 0; $i < 120; $i++) RateLimiter::hit(sha1((string) $sender->id), 60);

        $this->actingAs($sender)->postJson(route('messages.store', $recipient), ['body' => 'Hola', 'client_id' => (string) Str::uuid()])->assertCreated();
    }

    public function test_send_rate_limit_remains_enabled(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $key = md5('messages-sendmessages-send:'.$sender->id);
        for ($i = 0; $i < 120; $i++) RateLimiter::hit($key, 60);

        $this->actingAs($sender)->postJson(route('messages.store', $recipient), ['body' => 'Hola'])->assertStatus(429);
        $this->assertDatabaseCount('direct_messages', 0);
    }

    public function test_attachment_retry_does_not_create_a_second_message_or_file(): void
    {
        Storage::fake('local');
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $key = (string) Str::uuid();
        $this->actingAs($sender);
        foreach (range(1, 2) as $attempt) {
            $this->postJson(route('messages.store', $recipient), [
                'client_id' => $key,
                'attachment' => UploadedFile::fake()->create('archivo.pdf', 10, 'application/pdf'),
            ])->assertCreated();
        }

        $this->assertDatabaseCount('direct_messages', 1);
        $this->assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_failure_in_badge_processing_does_not_fail_a_saved_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $this->mock(BadgeAutomationService::class)->shouldReceive('sync')->twice()->andThrow(new \RuntimeException('badge unavailable'));

        $this->actingAs($sender)->postJson(route('messages.store', $recipient), ['body' => 'Mensaje guardado'])->assertCreated();
        $this->assertDatabaseCount('direct_messages', 1);
    }

    public function test_poll_cursor_does_not_skip_messages_after_the_first_page(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        [$one, $two] = Conversation::participantIds($sender, $recipient);
        $chat = Conversation::query()->create(['user_one_id' => $one, 'user_two_id' => $two]);
        for ($i = 1; $i <= 51; $i++) {
            $chat->messages()->create(['sender_id' => $sender->id, 'recipient_id' => $recipient->id, 'body' => "Mensaje {$i}"]);
        }

        $this->actingAs($recipient);
        $first = $this->getJson(route('messages.poll', $sender).'?after_id=0')->assertOk()->assertJsonCount(50, 'messages');
        $cursor = $first->json('latest_message_id');
        $this->assertSame($first->json('messages.49.id'), $cursor);
        $this->getJson(route('messages.poll', $sender).'?after_id='.$cursor)->assertOk()->assertJsonCount(1, 'messages')->assertSee('Mensaje 51');
    }
}
