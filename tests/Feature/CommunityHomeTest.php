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
use Tests\TestCase;

class CommunityHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_home_is_a_simple_entry_point_with_the_three_community_destinations(): void
    {
        [, $forum] = $this->forum();
        $author = User::factory()->create(['name' => 'Miyu Activa', 'alias' => 'miyu', 'last_login_at' => now()]);
        $newMember = User::factory()->create(['name' => 'Hana Nueva', 'alias' => 'hana']);
        $thread = app(ForumThreadService::class)->create($forum, $author, 'Hablemos de yuri', 'Primer mensaje.');
        app(ForumPostService::class)->reply($thread, $newMember, 'Me encanta esta conversación.');

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Un espacio para')
            ->assertSee('Foros')
            ->assertSee('Preguntas')
            ->assertSee('Miembros')
            ->assertSee(route('forums.index'), false)
            ->assertSee(route('questions.index'), false)
            ->assertSee(route('community.members'), false)
            ->assertSee('Actividad reciente')
            ->assertSee('Hablemos de yuri')
            ->assertSee('miyu')
            ->assertSee('hana')
            ->assertSee('Temas recientes')
            ->assertSee('General')
            ->assertSee('Ver foros')
            ->assertSee('Miembros de la comunidad')
            ->assertDontSee('Una comunidad desde 2007')
            ->assertDontSee('Temas populares')
            ->assertDontSee('Miembros activos')
            ->assertDontSee('Miembros nuevos')
            ->assertDontSee('Respuestas que buscamos juntas');
    }

    public function test_community_home_activity_excludes_private_profiles(): void
    {
        $publicAuthor = User::factory()->create(['name' => 'Miyu Pública', 'alias' => 'miyu-publica']);
        $privateAuthor = User::factory()->create([
            'name' => 'Miyu Privada',
            'alias' => 'miyu-privada',
            'profile_visibility' => 'private',
        ]);

        app(QuestionService::class)->create($publicAuthor, 'Pregunta pública', 'Esta actividad se puede ver.');
        app(QuestionService::class)->create($privateAuthor, 'Pregunta privada', 'Esta actividad no se puede ver.');

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Pregunta pública')
            ->assertDontSee('Pregunta privada')
            ->assertDontSee('Miyu Privada');
    }

    public function test_member_directory_remains_available_on_its_own_route(): void
    {
        User::factory()->create(['name' => 'Miembro del directorio']);

        $this->get(route('community.members'))
            ->assertOk()
            ->assertSee('Directorio público')
            ->assertSee('Miembro del directorio');
    }

    public function test_community_home_mixes_public_modern_and_historical_members(): void
    {
        User::factory()->create(['name' => 'Hana Moderna', 'alias' => 'hana-moderna']);
        LegacyProfile::query()->create([
            'legacy_external_key' => 'mundo-yuri:prueba-historica',
            'nickname' => 'Hana Histórica',
            'slug' => 'hana-historica',
            'legacy_joined_at' => '2007-08-13',
            'source' => 'captura-verificada',
            'is_legacy' => true,
            'legacy_verified' => true,
            'is_published' => true,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Hana Moderna')
            ->assertSee('Hana Histórica')
            ->assertSee('Ver miembros');
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
