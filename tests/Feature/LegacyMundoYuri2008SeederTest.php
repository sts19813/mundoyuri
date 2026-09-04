<?php

namespace Tests\Feature;

use App\Models\LegacyProfile;
use Carbon\Carbon;
use Database\Seeders\LegacyMundoYuri2008Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyMundoYuri2008SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_only_the_21_verified_historical_profiles_without_users(): void
    {
        Carbon::setTestNow('2026-09-04 12:00:00');
        $this->seed(LegacyMundoYuri2008Seeder::class);

        $this->assertDatabaseCount('legacy_profiles', 21);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('legacy_profiles', ['nickname' => 'CIALIScheapestVIAGRA']);
        $this->assertDatabaseMissing('legacy_profiles', ['nickname' => 'FreePHOTOSHOPforumTOPIC']);

        $angel = LegacyProfile::query()->with('badges')->where('nickname', '~~Angel~~')->firstOrFail();
        $this->assertSame('2007-08-13', $angel->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame(100, $angel->legacy_message_count);
        $this->assertSame('Brasília', $angel->legacy_location);
        $this->assertTrue($angel->is_legacy);
        $this->assertTrue($angel->legacy_verified);
        $this->assertSame('2026-09-04', $angel->created_at?->format('Y-m-d'));
        $this->assertSame(['miembro-historico', 'pionera-2007'], $angel->badges->sortBy('slug')->pluck('slug')->values()->all());

        $yukari = LegacyProfile::query()->with('badges')->where('nickname', 'yukari.yuri')->firstOrFail();
        $this->assertNull($yukari->legacy_location);
        $this->assertNull($yukari->legacy_occupation);
        $this->assertNull($yukari->legacy_interests);
        $this->assertNull($yukari->legacy_website);

        $yuu = LegacyProfile::query()->with('badges')->where('nickname', 'Yuu')->firstOrFail();
        $this->assertSame('2008-01-02', $yuu->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame(12, $yuu->legacy_message_count);
        $this->assertSame(['miembro-historico'], $yuu->badges->pluck('slug')->all());

        Carbon::setTestNow();
    }

    public function test_it_is_idempotent_and_keeps_legacy_message_counts_separate_from_modern_users(): void
    {
        $this->seed(LegacyMundoYuri2008Seeder::class);
        $this->seed(LegacyMundoYuri2008Seeder::class);

        $this->assertDatabaseCount('legacy_profiles', 21);
        $this->assertDatabaseCount('badge_legacy_profile', 28);
        $this->assertDatabaseCount('users', 0);

        $profile = LegacyProfile::query()->where('nickname', 'Miragem')->firstOrFail();
        $this->assertSame(115, $profile->legacy_message_count);
        $this->assertSame('unclaimed', $profile->claim_status);
    }

    public function test_imported_profiles_are_visible_in_the_historical_archive(): void
    {
        $this->seed(LegacyMundoYuri2008Seeder::class);

        $this->get(route('legacy-profiles.index'))
            ->assertOk()
            ->assertSee('~~Angel~~')
            ->assertSee('Miembro Histórico')
            ->assertSee('Pionera 2007')
            ->assertSee('Himeko');
    }
}
