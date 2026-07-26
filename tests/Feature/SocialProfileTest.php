<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_and_remove_a_series_from_favorites(): void
    {
        $user = User::factory()->create();
        $series = $this->publishedSeries($user);

        $this->actingAs($user)
            ->post(route('series.favorites.store', $series))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('series.favorites.store', $series))
            ->assertRedirect();

        $this->assertDatabaseCount('series_favorites', 1);
        $this->assertDatabaseHas('series_favorites', [
            'user_id' => $user->id,
            'series_id' => $series->id,
        ]);

        $this->actingAs($user)
            ->get(route('catalog.series.show', $series))
            ->assertOk()
            ->assertSee('Quitar de favoritas');

        $this->get($user->publicProfileUrl())
            ->assertOk()
            ->assertSee($series->title)
            ->assertSee('1</strong> favoritas', false);

        $this->get(route('profiles.favorites', $user))
            ->assertOk()
            ->assertSee($series->title)
            ->assertSee(route('catalog.series.show', $series), false);

        $this->actingAs($user)
            ->delete(route('series.favorites.destroy', $series))
            ->assertRedirect();

        $this->assertDatabaseMissing('series_favorites', [
            'user_id' => $user->id,
            'series_id' => $series->id,
        ]);
    }

    public function test_guest_must_log_in_and_unpublished_series_cannot_be_favorited(): void
    {
        $creator = User::factory()->create();
        $series = $this->publishedSeries($creator);

        $this->post(route('series.favorites.store', $series))
            ->assertRedirect(route('login'));

        $series->update([
            'moderation_status' => 'pending',
            'published_at' => null,
        ]);

        $this->actingAs($creator)
            ->post(route('series.favorites.store', $series))
            ->assertNotFound();
    }

    public function test_users_can_follow_and_unfollow_each_other(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Seguidora Luna',
            'alias' => 'seguidora-luna',
        ]);
        $profileUser = User::factory()->create([
            'name' => 'Creadora Yuri',
            'alias' => 'creadora-yuri',
        ]);

        $this->actingAs($viewer)
            ->post(route('users.follow.store', $profileUser))
            ->assertRedirect();
        $this->actingAs($viewer)
            ->post(route('users.follow.store', $profileUser))
            ->assertRedirect();

        $this->assertDatabaseCount('user_follows', 1);
        $this->assertDatabaseHas('user_follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $profileUser->id,
        ]);

        $this->actingAs($viewer)
            ->get($profileUser->publicProfileUrl())
            ->assertOk()
            ->assertSee('Siguiendo')
            ->assertSee('1</strong> seguidores', false);

        $this->get(route('profiles.followers', $profileUser))
            ->assertOk()
            ->assertSee('Seguidora Luna')
            ->assertSee($viewer->publicProfileUrl(), false);

        $this->actingAs($viewer)
            ->delete(route('users.follow.destroy', $profileUser))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $profileUser->id,
        ]);
    }

    public function test_user_cannot_follow_self_and_guest_must_log_in(): void
    {
        $user = User::factory()->create();

        $this->post(route('users.follow.store', $user))
            ->assertRedirect(route('login'));

        $this->from($user->publicProfileUrl())
            ->actingAs($user)
            ->post(route('users.follow.store', $user))
            ->assertRedirect($user->publicProfileUrl())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('user_follows', 0);
    }

    private function publishedSeries(User $creator): Series
    {
        $genre = Genre::query()->create([
            'name' => 'Romance',
            'slug' => 'romance-'.fake()->unique()->numerify('####'),
            'is_active' => true,
        ]);

        return Series::query()->create([
            'genre_id' => $genre->id,
            'created_by' => $creator->id,
            'approved_by' => $creator->id,
            'title' => 'Serie favorita '.fake()->unique()->numerify('####'),
            'slug' => 'serie-favorita-'.fake()->unique()->numerify('####'),
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Descripción suficientemente completa para esta serie favorita.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);
    }
}
