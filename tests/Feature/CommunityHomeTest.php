<?php

namespace Tests\Feature;

use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\LegacyProfile;
use App\Models\User;
use App\Services\ForumPostService;
use App\Services\ForumThreadService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CommunityHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_home_is_a_lightweight_public_hub_with_real_community_content(): void
    {
        Cache::forget('community.home.statistics.v2');
        [, $forum] = $this->forum();
        $author = User::factory()->create(['name' => 'Miyu Activa', 'alias' => 'miyu', 'last_login_at' => now()]);
        $newMember = User::factory()->create(['name' => 'Hana Nueva', 'alias' => 'hana']);
        User::factory()->create(['name' => 'Perfil Privado', 'profile_visibility' => 'private', 'last_login_at' => now()]);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Hablemos de yuri', 'Primer mensaje.');
        app(ForumPostService::class)->reply($thread, $newMember, 'Me encanta esta conversación.');
        $question = app(QuestionService::class)->create($author, '¿Qué obra recomiendan?', 'Busco una recomendación.');
        app(ForumPostService::class)->reply($question, $newMember, 'Te recomiendo empezar por esta serie.');
        app(QuestionService::class)->create($newMember, 'Pregunta todavía abierta', 'Necesito más contexto.');
        LegacyProfile::query()->create([
            'legacy_external_key' => 'foro-2007:akari',
            'slug' => 'akari-archivo',
            'nickname' => 'Akari Archivo',
            'legacy_joined_at' => '2007-04-12',
            'source' => 'captura-2007',
            'is_published' => true,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Bienvenida a la')
            ->assertSee('Temas recientes')
            ->assertSee('Hablemos de yuri')
            ->assertSee('¿Qué obra recomiendan?')
            ->assertSee('Pregunta todavía abierta')
            ->assertSee('miyu')
            ->assertSee('hana')
            ->assertSee('Akari Archivo')
            ->assertDontSee('Perfil Privado')
            ->assertSee(route('community.members'), false);

        $stats = Cache::get('community.home.statistics.v2');
        $this->assertSame(2, $stats['members']);
        $this->assertSame(1, $stats['threads']);
        $this->assertSame(2, $stats['questions']);
        $this->assertSame(1, $stats['answers']);
        $this->assertSame(5, $stats['messages']);
    }

    public function test_member_directory_remains_available_on_its_own_route(): void
    {
        User::factory()->create(['name' => 'Miembro del directorio']);

        $this->get(route('community.members'))
            ->assertOk()
            ->assertSee('Directorio público')
            ->assertSee('Miembro del directorio');
    }

    /** @return array{ForumCategory, Forum} */
    private function forum(): array
    {
        $category = ForumCategory::query()->create([
            'name' => 'Comunidad',
            'slug' => 'comunidad',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return [$category, Forum::query()->create([
            'forum_category_id' => $category->id,
            'name' => 'General',
            'slug' => 'general',
            'sort_order' => 0,
            'is_active' => true,
        ])];
    }
}
