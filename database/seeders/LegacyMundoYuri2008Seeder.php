<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\LegacyProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LegacyMundoYuri2008Seeder extends Seeder
{
    private const SOURCE = 'wayback-machine-2008-member-list';

    private const SOURCE_DESCRIPTION = 'Lista de miembros archivada del antiguo Fórum Mundo Yuri, captura de Wayback Machine de 2008.';

    public function run(): void
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use (&$created, &$updated): void {
            $badges = Badge::query()
                ->whereIn('slug', ['miembro-historico', 'pionera-2007'])
                ->get()
                ->keyBy('slug');

            if ($badges->count() !== 2) {
                throw new RuntimeException('Faltan las insignias históricas requeridas. Ejecuta primero las migraciones de Comunidad.');
            }

            foreach ($this->profiles() as $position => $data) {
                $externalKey = sprintf('mundo-yuri-wayback-2008:member-%03d', $position + 1);
                $profile = LegacyProfile::query()->firstOrNew(['legacy_external_key' => $externalKey]);
                $exists = $profile->exists;

                $profile->fill([
                    'slug' => $profile->slug ?: $this->slugFor($data['nickname'], $externalKey),
                    'nickname' => $data['nickname'],
                    'legacy_joined_at' => $data['legacy_joined_at'],
                    'legacy_message_count' => $data['legacy_message_count'],
                    'legacy_location' => $data['legacy_location'],
                    'source' => self::SOURCE,
                    'legacy_source_description' => self::SOURCE_DESCRIPTION,
                    'is_legacy' => true,
                    'legacy_verified' => true,
                    'is_published' => true,
                ]);
                $profile->save();

                $badgeIds = [$badges['miembro-historico']->id];
                if (str_starts_with($data['legacy_joined_at'], '2007-')) {
                    $badgeIds[] = $badges['pionera-2007']->id;
                }

                $profile->badges()->syncWithoutDetaching(array_fill_keys($badgeIds, [
                    'awarded_at' => now(),
                    'note' => self::SOURCE_DESCRIPTION,
                ]));

                $exists ? $updated++ : $created++;
            }
        });

        $this->command?->info("Imported: {$created}");
        $this->command?->info("Updated: {$updated}");
        $this->command?->info('Skipped: 0');
        $this->command?->info('Errors: 0');
    }

    /** @return list<array{nickname: string, legacy_joined_at: string, legacy_message_count: int, legacy_location: string|null}> */
    private function profiles(): array
    {
        return [
            ['nickname' => '~~Angel~~', 'legacy_joined_at' => '2007-08-13', 'legacy_message_count' => 100, 'legacy_location' => 'Brasília'],
            ['nickname' => 'atis', 'legacy_joined_at' => '2007-08-14', 'legacy_message_count' => 65, 'legacy_location' => 'MATRIX'],
            ['nickname' => 'yukari.yuri', 'legacy_joined_at' => '2007-08-14', 'legacy_message_count' => 9, 'legacy_location' => null],
            ['nickname' => 'Miragem', 'legacy_joined_at' => '2007-08-20', 'legacy_message_count' => 115, 'legacy_location' => 'Deserto Vermelho'],
            ['nickname' => 'Splash', 'legacy_joined_at' => '2007-09-15', 'legacy_message_count' => 1, 'legacy_location' => null],
            ['nickname' => 'Hirzigoto', 'legacy_joined_at' => '2007-12-20', 'legacy_message_count' => 54, 'legacy_location' => 'Brasil'],
            ['nickname' => 'Fenix', 'legacy_joined_at' => '2007-12-31', 'legacy_message_count' => 0, 'legacy_location' => null],
            ['nickname' => 'Yuu', 'legacy_joined_at' => '2008-01-02', 'legacy_message_count' => 12, 'legacy_location' => null],
            ['nickname' => 'Yuri Lover', 'legacy_joined_at' => '2008-01-05', 'legacy_message_count' => 30, 'legacy_location' => 'São Paulo'],
            ['nickname' => 'Kaine', 'legacy_joined_at' => '2008-01-07', 'legacy_message_count' => 13, 'legacy_location' => null],
            ['nickname' => 'Chikane-chan', 'legacy_joined_at' => '2008-01-28', 'legacy_message_count' => 2, 'legacy_location' => null],
            ['nickname' => 'Kamila Souza', 'legacy_joined_at' => '2008-02-20', 'legacy_message_count' => 0, 'legacy_location' => 'Manaus'],
            ['nickname' => 'Panda Ai', 'legacy_joined_at' => '2008-02-24', 'legacy_message_count' => 29, 'legacy_location' => 'São Paulo'],
            ['nickname' => 'DarkStarfire', 'legacy_joined_at' => '2008-02-25', 'legacy_message_count' => 0, 'legacy_location' => null],
            ['nickname' => 'Mad-Hatter', 'legacy_joined_at' => '2008-03-03', 'legacy_message_count' => 2, 'legacy_location' => 'Santa Catarina'],
            ['nickname' => 'J_Jealous', 'legacy_joined_at' => '2008-03-08', 'legacy_message_count' => 0, 'legacy_location' => null],
            ['nickname' => 'Nay_desrosiers', 'legacy_joined_at' => '2008-03-20', 'legacy_message_count' => 0, 'legacy_location' => null],
            ['nickname' => 'zen-chan', 'legacy_joined_at' => '2008-03-20', 'legacy_message_count' => 0, 'legacy_location' => null],
            ['nickname' => 'racnarok', 'legacy_joined_at' => '2008-03-25', 'legacy_message_count' => 13, 'legacy_location' => 'ónde?'],
            ['nickname' => 'kyokusanagi', 'legacy_joined_at' => '2008-03-25', 'legacy_message_count' => 5, 'legacy_location' => '200,ts de la muerte'],
            ['nickname' => 'Himeko', 'legacy_joined_at' => '2008-04-01', 'legacy_message_count' => 5, 'legacy_location' => null],
        ];
    }

    private function slugFor(string $nickname, string $externalKey): string
    {
        return (Str::slug($nickname) ?: 'miembro-historico').'-'.substr(sha1($externalKey), 0, 12);
    }
}
