<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_sanitized_signature_and_toggle_its_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_text' => '<script>alert("xss")</script><strong>Firma segura</strong>',
            'signature_enabled' => '0',
            'show_signatures' => '0',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Firma segura', $user->signature_text);
        $this->assertFalse($user->signature_enabled);
        $this->assertFalse($user->show_signatures);
    }

    public function test_signature_image_upload_is_limited_to_safe_image_content_and_dimensions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_image' => UploadedFile::fake()->image('firma.png', 600, 180),
        ])->assertSessionHasNoErrors();

        $signaturePath = $user->refresh()->signature_image;
        $this->assertNotNull($signaturePath);
        Storage::disk('public')->assertExists($signaturePath);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_image' => UploadedFile::fake()->image('firma.gif', 600, 180),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_image' => UploadedFile::fake()->image('demasiado-ancha.png', 601, 180),
        ])->assertSessionHasErrors('signature_image');

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_image' => UploadedFile::fake()->create('maliciosa.svg', 5, 'image/svg+xml'),
        ])->assertSessionHasErrors('signature_image');

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'signature_image' => UploadedFile::fake()->create('falsa.gif', 5, 'image/gif'),
        ])->assertSessionHasErrors('signature_image');
    }

    public function test_signatures_render_under_discussion_content_and_respect_reader_preference(): void
    {
        $author = User::factory()->create([
            'signature_text' => 'Firma de autora',
            'signature_enabled' => true,
        ]);
        $replier = User::factory()->create([
            'signature_text' => 'Firma de respuesta',
            'signature_enabled' => true,
        ]);
        $reader = User::factory()->create(['show_signatures' => false]);
        $series = $this->publishedSeries($author);
        $comment = $series->comments()->create([
            'user_id' => $author->id,
            'body' => 'Comentario con firma.',
            'is_approved' => true,
        ]);
        $series->comments()->create([
            'user_id' => $replier->id,
            'parent_id' => $comment->id,
            'body' => 'Respuesta con firma.',
            'is_approved' => true,
        ]);

        $this->get(route('catalog.series.show', $series))
            ->assertOk()
            ->assertSee('Firma de autora')
            ->assertSee('Firma de respuesta');

        $this->actingAs($reader)
            ->get(route('catalog.series.show', $series))
            ->assertOk()
            ->assertDontSee('Firma de autora')
            ->assertDontSee('Firma de respuesta');
    }

    public function test_signature_is_not_repeated_for_consecutive_comments_by_the_same_user(): void
    {
        $author = User::factory()->create([
            'signature_text' => 'Firma consecutiva',
            'signature_enabled' => true,
        ]);
        $series = $this->publishedSeries($author);

        foreach (['Primer comentario.', 'Segundo comentario.'] as $body) {
            $series->comments()->create([
                'user_id' => $author->id,
                'body' => $body,
                'is_approved' => true,
            ]);
        }

        $response = $this->get(route('catalog.series.show', $series))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'Firma consecutiva'));
    }

    public function test_admin_can_remove_or_temporarily_suspend_a_signature(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile-signatures/firma.png', 'firma');
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'signature_text' => 'Firma que se moderará',
            'signature_image' => 'profile-signatures/firma.png',
            'signature_enabled' => true,
        ]);

        $this->actingAs($admin)->patch(route('admin.users.signature.suspension.update', $member), [
            'signature_suspended_until' => now()->addWeek()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->assertTrue($member->refresh()->signatureIsSuspended());
        $this->assertFalse($member->canDisplaySignatureTo(null));

        $this->actingAs($admin)->delete(route('admin.users.signature.destroy', $member))->assertRedirect();

        $member->refresh();
        $this->assertNull($member->signature_text);
        $this->assertNull($member->signature_image);
        $this->assertFalse($member->signature_enabled);
        Storage::disk('public')->assertMissing('profile-signatures/firma.png');
    }

    public function test_suspended_member_cannot_reenable_signature_and_regular_user_cannot_moderate_it(): void
    {
        $member = User::factory()->create([
            'signature_text' => 'Firma original',
            'signature_enabled' => true,
            'signature_suspended_until' => now()->addDay(),
        ]);
        $regular = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->patch(route('profile.update'), [
            'name' => $member->name,
            'email' => $member->email,
            'signature_text' => 'Intento de eludir la suspensión',
            'signature_enabled' => '1',
        ])->assertSessionHasErrors('signature_text');

        $this->assertSame('Firma original', $member->refresh()->signature_text);

        $this->actingAs($regular)->delete(route('admin.users.signature.destroy', $member))
            ->assertRedirect('/');

        $this->assertSame('Firma original', $member->refresh()->signature_text);
    }

    public function test_signature_html_is_escaped_even_if_legacy_data_bypasses_the_profile_form(): void
    {
        $user = User::factory()->create([
            'signature_text' => '<img src=x onerror=alert(1)>Firma heredada',
            'signature_enabled' => true,
        ]);

        $this->get($user->publicProfileUrl())
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;Firma heredada', false);
    }

    private function publishedSeries(User $creator): Series
    {
        $genre = Genre::query()->create([
            'name' => 'Firma',
            'slug' => 'firma-'.fake()->unique()->numerify('####'),
            'is_active' => true,
        ]);

        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Serie de firmas '.fake()->unique()->numerify('####'),
            'slug' => 'serie-de-firmas-'.fake()->unique()->numerify('####'),
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción válida para probar las firmas comunitarias.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }
}
