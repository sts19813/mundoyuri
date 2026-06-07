<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Genre;
use App\Models\Series;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['email' => 'sts19813@gmail.com'],
            [
                'name' => 'Admin MundoGL',
                'alias' => 'admin',
                'password' => Hash::make('Isabela97'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'alias' => 'tester',
                'password' => Hash::make('Password123!'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
        $user->assignRole('user');

        $genre = Genre::query()->firstOrCreate([
            'slug' => 'romance',
        ], [
            'name' => 'Romance',
            'description' => 'Historias con foco romantico.',
            'is_active' => true,
        ]);

        $series = Series::query()->firstOrCreate([
            'slug' => 'the-water',
        ], [
            'genre_id' => $genre->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'title' => 'The Water',
            'content_type' => 'series',
            'status' => 'ongoing',
            'description' => 'Drama GL ambientado en Tailandia.',
            'country_of_origin' => 'Tailandia',
            'release_year' => 2026,
            'total_seasons' => 1,
            'total_episodes' => 12,
            'duration_minutes' => 30,
            'cover_image' => 'https://picsum.photos/300/420?demo=1',
            'banner_image' => 'https://picsum.photos/1200/500?demo=1',
            'is_featured' => true,
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $episode = Episode::query()->firstOrCreate([
            'slug' => 'the-water-s1e1',
        ], [
            'series_id' => $series->id,
            'created_by' => $admin->id,
            'approved_by' => $admin->id,
            'title' => 'Inicio',
            'season_number' => 1,
            'episode_number' => 1,
            'release_date' => now()->toDateString(),
            'duration_minutes' => 28,
            'description' => 'Primer episodio de demostracion.',
            'moderation_status' => 'approved',
            'published_at' => now(),
        ]);

        $episode->sources()->firstOrCreate([
            'provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        ], [
            'label' => 'Demo',
            'is_primary' => true,
        ]);
    }
}
