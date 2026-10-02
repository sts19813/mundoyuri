<?php

namespace Tests\Unit;

use App\Models\CommunityRank;
use App\Models\User;
use App\Services\CommunityRankResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityRankResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_the_highest_automatic_rank_reached_by_message_count(): void
    {
        $user = User::factory()->create(['community_message_count' => 220]);

        $rank = app(CommunityRankResolver::class)->resolve($user);

        $this->assertSame('yuri-senpai', $rank?->slug);
    }

    public function test_it_resolves_automatic_ranks_by_community_join_age(): void
    {
        $this->travelTo('2026-10-02 12:00:00');

        $users = [
            'nuevo-miembro' => User::factory()->create(['created_at' => now()->subDays(20), 'community_message_count' => 0]),
            'kohai' => User::factory()->create(['created_at' => now()->subMonths(2), 'community_message_count' => 0]),
            'yuri-fan' => User::factory()->create(['created_at' => now()->subMonths(7), 'community_message_count' => 0]),
            'yuri-senpai' => User::factory()->create(['created_at' => now()->subYears(2), 'community_message_count' => 0]),
            'onee-sama' => User::factory()->create(['created_at' => now()->subYears(6), 'community_message_count' => 0]),
        ];

        $resolver = app(CommunityRankResolver::class);

        foreach ($users as $expectedRank => $user) {
            $this->assertSame($expectedRank, $resolver->resolve($user)?->slug);
        }
    }

    public function test_an_active_special_rank_overrides_automatic_progress(): void
    {
        $specialRank = CommunityRank::query()->create([
            'name' => 'Fundadora',
            'slug' => 'fundadora',
            'is_special' => true,
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'community_message_count' => 600,
            'community_rank_id' => $specialRank->id,
        ]);

        $rank = app(CommunityRankResolver::class)->resolve($user);

        $this->assertTrue($specialRank->is($rank));
    }

    public function test_an_inactive_assigned_rank_falls_back_to_automatic_progress(): void
    {
        $inactiveRank = CommunityRank::query()->create([
            'name' => 'Archivado',
            'slug' => 'archivado',
            'is_special' => true,
            'is_active' => false,
        ]);
        $user = User::factory()->create([
            'community_message_count' => 55,
            'community_rank_id' => $inactiveRank->id,
        ]);

        $rank = app(CommunityRankResolver::class)->resolve($user);

        $this->assertSame('yuri-fan', $rank?->slug);
    }
}
