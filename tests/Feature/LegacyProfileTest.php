<?php

namespace Tests\Feature;

use App\Models\LegacyProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_profile_is_separate_from_users_and_keeps_technical_creation_date(): void
    {
        Carbon::setTestNow('2026-09-03 12:00:00');

        $profile = LegacyProfile::query()->create([
            'legacy_external_key' => 'foro-2007:akari',
            'slug' => 'akari-historica',
            'nickname' => 'Akari',
            'legacy_joined_at' => '2007-04-12',
            'legacy_message_count' => 42,
            'source' => 'captura-foro-2007',
        ]);

        $this->assertDatabaseCount('users', 0);
        $this->assertSame('2007-04-12', $profile->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame('2026-09-03', $profile->created_at?->format('Y-m-d'));
        $this->assertSame('unclaimed', $profile->claim_status);

        Carbon::setTestNow();
    }

    public function test_public_archive_clearly_marks_historical_profiles_without_private_evidence(): void
    {
        $profile = LegacyProfile::query()->create([
            'legacy_external_key' => 'foro-2007:luna',
            'slug' => 'luna-rosa',
            'nickname' => 'Luna Rosa',
            'legacy_joined_at' => '2007-05-20',
            'legacy_rank' => 'Yuri Fan',
            'legacy_message_count' => 184,
            'legacy_location' => 'Mérida',
            'source' => 'captura-2007',
            'evidence' => 'captura-privada-014',
            'admin_notes' => 'Nunca exponer esta nota.',
        ]);

        $this->get(route('legacy-profiles.index'))
            ->assertOk()
            ->assertSee('Perfiles')
            ->assertSee('históricos')
            ->assertSee('Luna Rosa')
            ->assertSee('no representan cuentas activas actuales');

        $this->get(route('legacy-profiles.show', $profile))
            ->assertOk()
            ->assertSee('Perfil histórico')
            ->assertSee('Datos recuperados del archivo de Mundo Yuri')
            ->assertSee('20 May 2007')
            ->assertSee('184')
            ->assertDontSee('captura-privada-014')
            ->assertDontSee('Nunca exponer esta nota.');
    }

    public function test_unpublished_historical_profile_is_not_publicly_available(): void
    {
        $profile = LegacyProfile::query()->create([
            'legacy_external_key' => 'foro-2007:privado',
            'slug' => 'archivo-privado',
            'nickname' => 'Archivo privado',
            'source' => 'captura-2007',
            'is_published' => false,
        ]);

        $this->get(route('legacy-profiles.index'))->assertDontSee('Archivo privado');
        $this->get(route('legacy-profiles.show', $profile))->assertNotFound();
    }

    public function test_admin_can_import_and_edit_a_historical_profile_without_credentials(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.legacy-profiles.store'), [
            'legacy_external_key' => 'foro-2008:hana',
            'nickname' => 'Hana Archive',
            'slug' => '',
            'legacy_joined_at' => '2008-01-10',
            'legacy_rank' => 'Kohai',
            'legacy_message_count' => 25,
            'legacy_location' => 'Campeche',
            'legacy_occupation' => 'Estudiante',
            'legacy_interests' => 'Manga y fotografía',
            'legacy_website' => 'https://example.org/hana',
            'source' => 'captura-2008',
            'evidence' => 'captura-021',
            'admin_notes' => 'Nota interna',
            'is_published' => true,
        ])->assertRedirect(route('admin.legacy-profiles.index'));

        $profile = LegacyProfile::query()->where('legacy_external_key', 'foro-2008:hana')->firstOrFail();
        $this->assertSame('hana-archive', $profile->slug);
        $this->assertDatabaseCount('users', 1);

        $this->actingAs($admin)->put(route('admin.legacy-profiles.update', $profile), [
            'legacy_external_key' => $profile->legacy_external_key,
            'nickname' => 'Hana Archivo',
            'slug' => $profile->slug,
            'legacy_joined_at' => '2008-01-10',
            'legacy_rank' => 'Yuri Fan',
            'legacy_message_count' => 26,
            'source' => 'captura-2008',
            'is_published' => false,
        ])->assertRedirect(route('admin.legacy-profiles.index'));

        $profile->refresh();
        $this->assertSame('Yuri Fan', $profile->legacy_rank);
        $this->assertSame(26, $profile->legacy_message_count);
        $this->assertFalse($profile->is_published);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_regular_users_cannot_manage_historical_profiles(): void
    {
        $regular = User::factory()->create(['role' => 'user']);

        $this->actingAs($regular)->get(route('admin.legacy-profiles.index'))->assertRedirect('/');
        $this->actingAs($regular)->post(route('admin.legacy-profiles.store'), [
            'legacy_external_key' => 'foro-2007:nope',
            'nickname' => 'No permitido',
            'legacy_message_count' => 0,
            'source' => 'captura',
            'is_published' => true,
        ])->assertRedirect('/');

        $this->assertDatabaseCount('legacy_profiles', 0);
    }

    public function test_csv_import_is_idempotent_and_preserves_claim_state(): void
    {
        Storage::fake('local');
        $path = Storage::disk('local')->path('imports/perfiles.csv');
        Storage::disk('local')->put('imports/perfiles.csv', implode("\n", [
            'legacy_external_key,nickname,legacy_joined_at,legacy_rank,legacy_message_count,legacy_location,source,is_published',
            'foro-2007:123,LunaRosa,2007-04-12,Yuri Fan,184,Mérida,capturas-2007,true',
        ]));

        $this->artisan('community:legacy-import', ['file' => $path])
            ->expectsOutputToContain('1 creados, 0 actualizados, 0 con error.')
            ->assertExitCode(0);

        $profile = LegacyProfile::query()->where('legacy_external_key', 'foro-2007:123')->firstOrFail();
        $claimedBy = User::factory()->create();
        $profile->update([
            'claim_status' => 'pending',
            'claimed_by_user_id' => $claimedBy->id,
        ]);

        Storage::disk('local')->put('imports/perfiles.csv', implode("\n", [
            'legacy_external_key,nickname,legacy_joined_at,legacy_rank,legacy_message_count,legacy_location,source,is_published',
            'foro-2007:123,LunaRosaActualizada,2007-04-12,Yuri Senpai,200,Mérida,capturas-2007,true',
        ]));

        $this->artisan('community:legacy-import', ['file' => $path])
            ->expectsOutputToContain('0 creados, 1 actualizados, 0 con error.')
            ->assertExitCode(0);

        $profile->refresh();
        $this->assertDatabaseCount('legacy_profiles', 1);
        $this->assertSame('LunaRosaActualizada', $profile->nickname);
        $this->assertSame('Yuri Senpai', $profile->legacy_rank);
        $this->assertSame(200, $profile->legacy_message_count);
        $this->assertSame('pending', $profile->claim_status);
        $this->assertSame($claimedBy->id, $profile->claimed_by_user_id);
    }

    public function test_csv_dry_run_and_invalid_avatar_path_do_not_write_profiles(): void
    {
        Storage::fake('local');
        $path = Storage::disk('local')->path('imports/prueba.csv');
        Storage::disk('local')->put('imports/prueba.csv', implode("\n", [
            'legacy_external_key,nickname,legacy_avatar_path',
            'foro-2007:dry,LunaDry,legacy-avatars/luna.jpg',
        ]));

        $this->artisan('community:legacy-import', ['file' => $path, '--dry-run' => true])
            ->expectsOutputToContain('1 creados, 0 actualizados, 0 con error.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('legacy_profiles', 0);

        Storage::disk('local')->put('imports/prueba.csv', implode("\n", [
            'legacy_external_key,nickname,legacy_avatar_path',
            'foro-2007:unsafe,LunaUnsafe,https://example.org/avatar.jpg',
        ]));

        $this->artisan('community:legacy-import', ['file' => $path])
            ->expectsOutputToContain('1 con error.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('legacy_profiles', 0);
    }

    public function test_manual_registration_accepts_only_archived_data_and_never_creates_a_user(): void
    {
        $this->artisan('community:legacy-register', [
            'legacy_username' => 'ArchivoPrueba',
            'legacy_joined_at' => '2007-06-18',
            '--messages' => '17',
            '--rank' => 'Yuri Fan',
            '--location' => 'Ciudad archivada',
            '--occupation' => 'Ocupación archivada',
            '--interests' => 'Intereses archivados',
            '--website' => 'https://example.org/sitio-archivado',
            '--avatar-url' => 'https://web.archive.org/avatar-prueba.png',
            '--source-url' => 'https://web.archive.org/captura-prueba',
            '--source-description' => 'Captura archivada de prueba',
            '--verified' => true,
        ])->assertExitCode(0);

        $profile = LegacyProfile::query()->where('nickname', 'ArchivoPrueba')->firstOrFail();

        $this->assertDatabaseCount('users', 0);
        $this->assertTrue($profile->is_legacy);
        $this->assertTrue($profile->legacy_verified);
        $this->assertSame('2007-06-18', $profile->legacy_joined_at?->format('Y-m-d'));
        $this->assertSame(17, $profile->legacy_message_count);
        $this->assertSame('https://web.archive.org/avatar-prueba.png', $profile->legacy_avatar_url);
        $this->assertSame('https://web.archive.org/captura-prueba', $profile->legacy_source_url);
        $this->assertFalse($profile->is_published);
    }

    public function test_manual_registration_keeps_optional_historical_fields_null_and_dry_run_does_not_write(): void
    {
        $arguments = [
            'legacy_username' => 'SoloDatosObligatorios',
            'legacy_joined_at' => '2008-01-01',
        ];

        $this->artisan('community:legacy-register', [...$arguments, '--dry-run' => true])
            ->expectsOutput('Validación correcta. No se guardó ningún perfil por usar --dry-run.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('legacy_profiles', 0);

        $this->artisan('community:legacy-register', $arguments)->assertExitCode(0);

        $profile = LegacyProfile::query()->where('nickname', 'SoloDatosObligatorios')->firstOrFail();
        $this->assertNull($profile->legacy_message_count);
        $this->assertNull($profile->legacy_rank);
        $this->assertNull($profile->legacy_avatar_url);
        $this->assertFalse($profile->legacy_verified);
    }
}
