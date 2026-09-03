<?php

namespace Tests\Feature;

use App\Models\CommunityRank;
use App\Models\User;
use App\Services\CommunityRankResolver;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommunityRankTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_default_rank_thresholds_live_in_the_database(): void
    {
        $this->assertDatabaseHas('community_ranks', ['slug' => 'nuevo-miembro', 'minimum_posts' => 0]);
        $this->assertDatabaseHas('community_ranks', ['slug' => 'kohai', 'minimum_posts' => 10]);
        $this->assertDatabaseHas('community_ranks', ['slug' => 'yuri-fan', 'minimum_posts' => 50]);
        $this->assertDatabaseHas('community_ranks', ['slug' => 'yuri-senpai', 'minimum_posts' => 200]);
        $this->assertDatabaseHas('community_ranks', ['slug' => 'onee-sama', 'minimum_posts' => 500]);
    }

    public function test_admin_can_create_and_update_a_community_rank(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.community-ranks.store'), [
                'name' => 'Leyenda de Mundo Yuri',
                'slug' => 'leyenda-mundo-yuri',
                'description' => 'Reconocimiento especial de la comunidad.',
                'minimum_posts' => 999,
                'priority' => 900,
                'icon' => '♛',
                'css_class' => 'rank-legendary rank-glow',
                'color' => '#f43f8e',
                'is_special' => '1',
                'is_legacy' => '1',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.community-ranks.index'));

        $rank = CommunityRank::query()->where('slug', 'leyenda-mundo-yuri')->firstOrFail();
        $this->assertNull($rank->minimum_posts);
        $this->assertTrue($rank->is_special);
        $this->assertTrue($rank->is_legacy);

        $this->actingAs($admin)
            ->put(route('admin.community-ranks.update', $rank), [
                'name' => 'Leyenda Yuri',
                'slug' => 'leyenda-yuri',
                'description' => 'Nuevo texto administrable.',
                'minimum_posts' => 750,
                'priority' => 60,
                'icon' => '✦',
                'css_class' => 'rank-legendary',
                'color' => '#c084fc',
                'is_special' => '0',
                'is_legacy' => '0',
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.community-ranks.index'));

        $rank->refresh();
        $this->assertSame('Leyenda Yuri', $rank->name);
        $this->assertSame(750, $rank->minimum_posts);
        $this->assertSame(60, $rank->priority);
        $this->assertFalse($rank->is_special);
    }

    public function test_deleting_a_manual_special_rank_restores_automatic_calculation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $specialRank = CommunityRank::query()->create([
            'name' => 'Fundadora',
            'slug' => 'fundadora',
            'priority' => 1000,
            'is_special' => true,
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'community_message_count' => 55,
            'community_rank_id' => $specialRank->id,
        ]);

        $this->assertSame('fundadora', app(CommunityRankResolver::class)->resolve($user)?->slug);

        $this->actingAs($admin)
            ->delete(route('admin.community-ranks.destroy', $specialRank))
            ->assertRedirect(route('admin.community-ranks.index'));

        $user->refresh();
        $this->assertNull($user->community_rank_id);
        $this->assertSame('yuri-fan', app(CommunityRankResolver::class)->resolve($user)?->slug);
    }

    public function test_regular_users_cannot_manage_community_ranks(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.community-ranks.index'))
            ->assertRedirect('/');

        $this->actingAs($user)
            ->post(route('admin.community-ranks.store'), [
                'name' => 'Rango no autorizado',
                'priority' => 1,
                'minimum_posts' => 1,
                'is_special' => false,
                'is_legacy' => false,
                'is_active' => true,
            ])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('community_ranks', ['name' => 'Rango no autorizado']);
    }
}
