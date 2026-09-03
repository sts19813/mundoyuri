<?php

namespace Tests\Feature;

use App\Models\CommunityRank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_lists_only_active_public_profiles_without_private_data(): void
    {
        $visible = User::factory()->create([
            'name' => 'Visible Yuri',
            'email' => 'visible-private@example.com',
            'profile_visibility' => 'public',
            'is_active' => true,
        ]);
        User::factory()->create(['name' => 'Solo Miembros', 'profile_visibility' => 'members']);
        User::factory()->create(['name' => 'Perfil Privado', 'profile_visibility' => 'private']);
        User::factory()->create(['name' => 'Perfil Inactivo', 'profile_visibility' => 'public', 'is_active' => false]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Visible Yuri')
            ->assertSee($visible->publicProfileUrl(), false)
            ->assertDontSee('visible-private@example.com')
            ->assertDontSee('Solo Miembros')
            ->assertDontSee('Perfil Privado')
            ->assertDontSee('Perfil Inactivo');
    }

    public function test_directory_search_and_legacy_filter_work(): void
    {
        User::factory()->create(['name' => 'Luna Antigua', 'alias' => 'luna-rosa', 'is_legacy' => true]);
        User::factory()->create(['name' => 'Akari Moderna', 'alias' => 'akari']);

        $this->get(route('community.index', ['q' => 'luna']))
            ->assertOk()
            ->assertSee('Luna Antigua')
            ->assertDontSee('Akari Moderna');

        $this->get(route('community.index', ['filter' => 'legacy']))
            ->assertOk()
            ->assertSee('Luna Antigua')
            ->assertSee('Miembro histórico')
            ->assertDontSee('Akari Moderna');
    }

    public function test_directory_supports_join_date_and_message_ordering(): void
    {
        User::factory()->create([
            'name' => 'Primera Integrante',
            'created_at' => '2010-01-01 00:00:00',
            'community_message_count' => 5,
        ]);
        User::factory()->create([
            'name' => 'Integrante Activa',
            'created_at' => '2024-01-01 00:00:00',
            'community_message_count' => 250,
        ]);

        $this->get(route('community.index', ['filter' => 'oldest']))
            ->assertOk()
            ->assertSeeInOrder(['Primera Integrante', 'Integrante Activa']);

        $this->get(route('community.index', ['sort' => 'messages', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['Integrante Activa', 'Primera Integrante']);
    }

    public function test_directory_filters_automatic_and_special_ranks(): void
    {
        $kohai = CommunityRank::query()->where('slug', 'kohai')->firstOrFail();
        $special = CommunityRank::query()->create([
            'name' => 'Fundadora',
            'slug' => 'fundadora',
            'is_special' => true,
            'is_active' => true,
            'priority' => 100,
        ]);

        User::factory()->create(['name' => 'Kohai Visible', 'community_message_count' => 20]);
        User::factory()->create(['name' => 'Yuri Fan Visible', 'community_message_count' => 80]);
        User::factory()->create(['name' => 'Fundadora Visible', 'community_rank_id' => $special->id]);

        $this->get(route('community.index', ['rank' => $kohai->id]))
            ->assertOk()
            ->assertSee('Kohai Visible')
            ->assertDontSee('Yuri Fan Visible')
            ->assertDontSee('Fundadora Visible');

        $this->get(route('community.index', ['rank' => $special->id]))
            ->assertOk()
            ->assertSee('Fundadora Visible')
            ->assertDontSee('Kohai Visible');
    }
}
