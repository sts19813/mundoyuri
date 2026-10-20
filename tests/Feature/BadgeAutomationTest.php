<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\Episode;
use App\Models\User;
use App\Services\BadgeAutomationService;
use Database\Seeders\CommunityBadgeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_historical_badges_and_keeps_an_audit_log(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $member = User::factory()->create([
            'id' => 2,
            'is_legacy' => true,
            'legacy_verified' => true,
            'legacy_joined_at' => '2007-05-10',
        ]);

        $result = app(BadgeAutomationService::class)->sync($member);

        $this->assertSame(2, $result['awarded']);
        $this->assertDatabaseHas('badge_user', [
            'user_id' => $member->id,
            'badge_id' => Badge::query()->where('slug', 'miembro-historico')->value('id'),
            'assignment_source' => 'automatic',
            'automation_rule' => 'miembro-historico',
        ]);
        $this->assertDatabaseHas('badge_award_logs', [
            'user_id' => $member->id,
            'badge_slug' => 'pionera-2007',
            'action' => 'assigned',
            'source' => 'automatic',
        ]);
    }

    public function test_automation_never_removes_a_manual_award(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $member = User::factory()->create(['id' => 2]);
        $badge = Badge::query()->where('slug', 'primer-amor')->firstOrFail();
        $member->badges()->attach($badge, [
            'awarded_at' => now(),
            'assignment_source' => 'manual',
        ]);

        $result = app(BadgeAutomationService::class)->sync($member);

        $this->assertSame(0, $result['revoked']);
        $this->assertDatabaseHas('badge_user', [
            'user_id' => $member->id,
            'badge_id' => $badge->id,
            'assignment_source' => 'manual',
        ]);
    }

    public function test_chat_messages_and_chapter_comments_count_as_participation(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $member = User::factory()->create(['id' => 2]);
        $otherMember = User::factory()->create(['id' => 3]);
        [$userOneId, $userTwoId] = Conversation::participantIds($member, $otherMember);
        $conversation = Conversation::query()->create(['user_one_id' => $userOneId, 'user_two_id' => $userTwoId]);
        $message = DirectMessage::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $member->id,
            'recipient_id' => $otherMember->id,
            'body' => 'Hola, ¿qué capítulo recomiendas?',
        ]);
        DirectMessage::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $otherMember->id,
            'recipient_id' => $member->id,
            'reply_to_message_id' => $message->id,
            'body' => 'El episodio tres es precioso.',
        ]);
        Comment::query()->create([
            'user_id' => $member->id,
            'commentable_type' => (new Episode)->getMorphClass(),
            'commentable_id' => 1,
            'body' => 'Este capítulo me encantó.',
            'is_approved' => true,
        ]);

        app(BadgeAutomationService::class)->sync($member);

        $this->assertTrue($member->badges()->whereIn('slug', [
            'primeros-pasos', 'primera-conversacion', 'autora-de-temas', 'curiosa', 'tengo-la-respuesta',
        ])->count() === 5);
    }

    public function test_administrative_account_id_one_is_excluded_and_its_automatic_awards_are_cleaned_up(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $admin = User::factory()->create(['id' => 1, 'role' => 'admin']);
        $automaticBadge = Badge::query()->where('slug', 'staff')->firstOrFail();
        $admin->badges()->attach($automaticBadge, [
            'awarded_at' => now(),
            'assignment_source' => 'automatic',
            'automation_rule' => 'staff',
        ]);

        $result = app(BadgeAutomationService::class)->sync($admin);

        $this->assertSame(1, $result['revoked']);
        $this->assertDatabaseMissing('badge_user', ['user_id' => 1, 'badge_id' => $automaticBadge->id]);
    }

    public function test_founder_badge_protects_the_founder_from_automation(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $founder = User::factory()->create(['id' => 2, 'role' => 'admin']);
        $founderBadge = Badge::query()->where('slug', 'fundadora')->firstOrFail();
        $founder->badges()->attach($founderBadge, [
            'awarded_at' => now(),
            'assignment_source' => 'manual',
        ]);

        $result = app(BadgeAutomationService::class)->sync($founder);

        $this->assertSame(['awarded' => 0, 'revoked' => 0], $result);
        $this->assertDatabaseHas('badge_user', ['user_id' => $founder->id, 'badge_id' => $founderBadge->id]);
        $this->assertDatabaseMissing('badge_user', [
            'user_id' => $founder->id,
            'badge_id' => Badge::query()->where('slug', 'staff')->value('id'),
        ]);
    }

    public function test_admin_sees_automation_conditions_and_history_module(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);
        $admin = User::factory()->create(['id' => 2, 'role' => 'admin']);
        $member = User::factory()->create([
            'id' => 3,
            'is_legacy' => true,
            'legacy_verified' => true,
            'legacy_joined_at' => '2007-05-10',
        ]);
        app(BadgeAutomationService::class)->sync($member);

        $this->actingAs($admin)->get(route('admin.badges.index'))
            ->assertOk()
            ->assertSee('Catálogo y reglas')
            ->assertSee('Es miembro histórico verificado y su fecha histórica es de 2007 o anterior.')
            ->assertSee('Historial de asignaciones')
            ->assertSee('Automática');
    }
}
