<?php

namespace Database\Seeders;

use App\Models\LegacyProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpsertAngelAndMiragemLegacyProfilesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $angel = LegacyProfile::query()
                ->where('legacy_external_key', 'mundo-yuri-wayback-2008:member-001')
                ->firstOrFail();
            $miragem = LegacyProfile::query()
                ->where('legacy_external_key', 'mundo-yuri-wayback-2008:member-004')
                ->firstOrFail();

            $angel->update([
                'legacy_joined_at' => '2007-08-13 12:00:00',
                'legacy_rank' => 'Sensei',
                'legacy_message_count' => 73,
                'legacy_location' => 'Brasília',
                'legacy_occupation' => 'Professora',
                'legacy_interests' => 'Yuri, shoujo-ai, animes, literatura, Harry Potter',
                'legacy_website' => 'https://www.fotolog.com/cyber_dust',
                'legacy_biography' => 'Perfil histórico recuperado del archivo de Mundo Yuri. Una de las fundadoras de la comunidad original.',
                'legacy_signature_text' => 'Yuri · shoujo-ai · animes · literatura · Harry Potter',
                'legacy_soulmate_profile_id' => $miragem->id,
                'legacy_verified' => true,
            ]);

            $miragem->update([
                'legacy_joined_at' => '2007-08-20 12:00:00',
                'legacy_rank' => 'Sensei',
                'legacy_message_count' => 61,
                'legacy_location' => 'Deserto Vermelho',
                'legacy_occupation' => 'Desocupada',
                'legacy_interests' => 'Desinteressada',
                'legacy_website' => 'https://www.fotolog.com/cami_chan',
                'legacy_biography' => 'Perfil histórico recuperado del archivo de Mundo Yuri, conservado tal como aparece en la comunidad original.',
                'legacy_signature_text' => 'Deserto Vermelho · Desinteressada',
                'legacy_soulmate_profile_id' => $angel->id,
                'legacy_verified' => true,
            ]);
        });
    }
}
