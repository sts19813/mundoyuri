<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityBadgeDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_active_badge_is_visible_on_profile_and_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Yuri Distinguida']);
        $badge = Badge::query()->where('slug', 'miembro-historico')->firstOrFail();
        $member->badges()->attach($badge, [
            'awarded_by' => $admin->id,
            'awarded_at' => now(),
        ]);

        $this->get($member->publicProfileUrl())
            ->assertOk()
            ->assertSee('Miembro Histórico');

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Yuri Distinguida')
            ->assertSee('Miembro Histórico');
    }

    public function test_inactive_and_unassigned_badges_are_not_displayed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $badge = Badge::query()->where('slug', 'pionera-2007')->firstOrFail();

        $this->get($member->publicProfileUrl())->assertDontSee('Pionera 2007');

        $member->badges()->attach($badge, [
            'awarded_by' => $admin->id,
            'awarded_at' => now(),
        ]);
        $badge->update(['is_active' => false]);

        $this->get($member->publicProfileUrl())->assertDontSee('Pionera 2007');
        $this->get(route('community.index'))->assertDontSee('Pionera 2007');
    }

    public function test_badges_are_visible_on_comments_and_replies(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['alias' => 'autora-insignia']);
        $replier = User::factory()->create(['alias' => 'respuesta-insignia']);
        $staff = Badge::query()->where('slug', 'staff')->firstOrFail();
        $moderation = Badge::query()->where('slug', 'moderacion')->firstOrFail();
        $author->badges()->attach($staff, ['awarded_by' => $admin->id, 'awarded_at' => now()]);
        $replier->badges()->attach($moderation, ['awarded_by' => $admin->id, 'awarded_at' => now()]);
        $series = $this->publishedSeries($admin);
        $comment = $series->comments()->create([
            'user_id' => $author->id,
            'body' => 'Comentario principal con insignia.',
            'is_approved' => true,
        ]);
        $series->comments()->create([
            'user_id' => $replier->id,
            'parent_id' => $comment->id,
            'body' => 'Respuesta con insignia.',
            'is_approved' => true,
        ]);

        $this->get(route('catalog.series.show', $series))
            ->assertOk()
            ->assertSee('Staff')
            ->assertSee('Moderación');
    }

    private function publishedSeries(User $creator): Series
    {
        $genre = Genre::query()->create([
            'name' => 'Drama',
            'slug' => 'drama-insignias',
            'is_active' => true,
        ]);

        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Serie con insignias',
            'slug' => 'serie-con-insignias',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción completa para probar insignias en comentarios.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }
}
