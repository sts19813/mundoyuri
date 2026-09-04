<?php

namespace Tests\Feature;

use Database\Seeders\CommunityBadgeCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityBadgeCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_import_is_idempotent_and_never_awards_badges(): void
    {
        $this->seed(CommunityBadgeCatalogSeeder::class);

        $this->assertDatabaseCount('badges', 58);
        $this->assertDatabaseHas('badges', [
            'slug' => 'generacion-2008',
            'icon' => '💿',
            'type' => 'legacy',
            'description' => 'Miembro registrado en 2008.',
        ]);
        $this->assertDatabaseHas('badges', [
            'slug' => 'nyan-yuri',
            'icon' => '🐱',
            'type' => 'secret',
        ]);
        $this->assertDatabaseCount('badge_user', 0);

        $this->seed(CommunityBadgeCatalogSeeder::class);

        $this->assertDatabaseCount('badges', 58);
        $this->assertDatabaseCount('badge_user', 0);
    }
}
