<?php

namespace App\Console\Commands;

use App\Services\LegacyProfileRegistrar;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('community:legacy-register
    {legacy_username : Nickname exacto que aparece en el archivo}
    {legacy_joined_at : Fecha histórica exacta (por ejemplo, 2007-04-12)}
    {--messages= : Número de mensajes histórico}
    {--rank= : Rango histórico}
    {--location= : Localización histórica}
    {--occupation= : Ocupación histórica}
    {--interests= : Intereses históricos}
    {--website= : Sitio web histórico HTTP/HTTPS}
    {--avatar-url= : URL archivada del avatar HTTP/HTTPS; se conserva, no se descarga}
    {--source-url= : URL de Wayback Machine o captura HTTP/HTTPS}
    {--source-description= : Descripción de la fuente archivada}
    {--external-key= : Clave técnica estable para desambiguar perfiles}
    {--verified : Confirma que la persona administradora verificó los datos}
    {--publish : Publica la ficha en el archivo histórico}
    {--dry-run : Valida y muestra el resultado sin guardarlo}')]
#[Description('Registrar manualmente una ficha histórica sin crear una cuenta de acceso')]
class RegisterLegacyProfile extends Command
{
    public function handle(LegacyProfileRegistrar $registrar): int
    {
        $input = [
            'legacy_external_key' => $this->option('external-key'),
            'legacy_username' => $this->argument('legacy_username'),
            'legacy_joined_at' => $this->argument('legacy_joined_at'),
            'legacy_message_count' => $this->option('messages'),
            'legacy_rank' => $this->option('rank'),
            'legacy_location' => $this->option('location'),
            'legacy_occupation' => $this->option('occupation'),
            'legacy_interests' => $this->option('interests'),
            'legacy_website' => $this->option('website'),
            'legacy_avatar_url' => $this->option('avatar-url'),
            'legacy_source_url' => $this->option('source-url'),
            'legacy_source_description' => $this->option('source-description'),
        ];

        try {
            if ($this->option('dry-run')) {
                $registrar->validate($input);
                $this->info('Validación correcta. No se guardó ningún perfil por usar --dry-run.');

                return self::SUCCESS;
            }

            $profile = $registrar->register(
                $input,
                $this->option('verified') ? true : null,
                $this->option('publish') ? true : null,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info("Ficha histórica registrada: {$profile->nickname} ({$profile->legacy_external_key}).");
        $this->line($profile->legacy_verified
            ? 'Estado: datos marcados como verificados.'
            : 'Estado: pendiente de verificación; usa --verified solo tras contrastar la fuente.');

        return self::SUCCESS;
    }
}
