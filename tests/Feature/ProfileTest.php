<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_profile_redirects_to_the_public_profile_presentation(): void
    {
        $user = User::factory()->create(['alias' => 'luna-yuri']);

        $this->actingAs($user)
            ->get('/profile')
            ->assertRedirect($user->publicProfileUrl());

        $this->actingAs($user)
            ->get('/profile/edit')
            ->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_images_biography_and_alias_can_be_updated_from_the_portal(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'alias' => 'yuri-fan',
                'email' => $user->email,
                'profile_image' => UploadedFile::fake()->image('avatar.webp'),
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 600),
                'biography' => 'Me encantan las historias GL, el café y descubrir nuevas series.',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('yuri-fan', $user->alias);
        $this->assertSame('Me encantan las historias GL, el café y descubrir nuevas series.', $user->biography);
        $this->assertNotNull($user->profile_image);
        $this->assertNotNull($user->cover_image);
        Storage::disk('public')->assertExists($user->profile_image);
        Storage::disk('public')->assertExists($user->cover_image);
    }

    public function test_public_profile_is_visible_without_exposing_private_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Luna Rivera',
            'alias' => 'luna-yuri',
            'email' => 'private@example.com',
            'biography' => 'Fan de las historias románticas y el cine asiático.',
        ]);

        $this->get($user->publicProfileUrl())
            ->assertOk()
            ->assertSee('Luna Rivera')
            ->assertSee('@luna-yuri')
            ->assertSee('Fan de las historias románticas y el cine asiático.')
            ->assertDontSee('private@example.com')
            ->assertDontSee('Editar mi perfil');

        $this->actingAs($user)
            ->get($user->publicProfileUrl())
            ->assertOk()
            ->assertSee('Editar mi perfil')
            ->assertSee(route('profile.edit'), false);
    }

    public function test_profile_displays_badges_with_an_accessible_description(): void
    {
        $user = User::factory()->create(['alias' => 'luna-yuri']);
        $badge = Badge::query()->where('slug', 'fundadora')->firstOrFail();
        $user->badges()->attach($badge, ['awarded_at' => now()]);

        $this->get($user->publicProfileUrl())
            ->assertOk()
            ->assertSee('Insignias')
            ->assertSee('Fundadora')
            ->assertSee('Reconocimiento a quienes fundaron Mundo Yuri.')
            ->assertSee('community-badge-tooltip', false);
    }

    public function test_cover_image_can_be_removed(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-covers/old-cover.jpg', 'cover');
        $user = User::factory()->create([
            'cover_image' => 'profile-covers/old-cover.jpg',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'cover_remove' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNull($user->refresh()->cover_image);
        Storage::disk('public')->assertMissing('profile-covers/old-cover.jpg');
    }

    public function test_authenticated_comment_author_links_to_public_profile(): void
    {
        $author = User::factory()->create([
            'name' => 'Autora registrada',
            'alias' => 'autora-yuri',
            'community_message_count' => 55,
        ]);
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance',
            'is_active' => true,
        ]);
        $series = Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $author->id,
            'approved_by' => $author->id,
            'title' => 'Serie con comentarios',
            'slug' => 'serie-con-comentarios',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente completa para mostrar la serie.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $series->comments()->create([
            'user_id' => $author->id,
            'alias' => $author->alias,
            'body' => 'Comentario de una persona con sesión iniciada.',
            'is_approved' => true,
        ]);
        $series->comments()->create([
            'alias' => 'Visitante anónima',
            'body' => 'Comentario publicado sin iniciar sesión.',
            'is_approved' => true,
        ]);

        $this->get(route('catalog.series.show', $series))
            ->assertOk()
            ->assertSee($author->publicProfileUrl(), false)
            ->assertSee('Ver perfil de autora-yuri')
            ->assertSee('Yuri Fan')
            ->assertSee('<span class="comment-user">Visitante anónima</span>', false);
    }

    public function test_portal_navigation_changes_with_the_session_state(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertDontSee('Cerrar sesión');

        $user = User::factory()->create(['name' => 'Luna Rivera']);

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('LR')
            ->assertSee('Mi perfil')
            ->assertSee('Cerrar sesión');
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
