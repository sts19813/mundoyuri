<?php

namespace App\Console\Commands;

use App\Services\LegacyProfileImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('community:legacy-import
    {file : Ruta absoluta o relativa al CSV UTF-8}
    {--dry-run : Valida y reporta sin escribir en la base de datos}')]
#[Description('Importar perfiles históricos de Mundo Yuri desde CSV de forma idempotente')]
class ImportLegacyProfiles extends Command
{
    private const REQUIRED_COLUMNS = ['legacy_external_key', 'nickname'];

    /** @var list<string> */
    private const OPTIONAL_COLUMNS = [
        'legacy_joined_at',
        'legacy_rank',
        'legacy_message_count',
        'legacy_location',
        'legacy_occupation',
        'legacy_interests',
        'legacy_website',
        'legacy_avatar_path',
        'source',
        'evidence',
        'admin_notes',
        'is_published',
    ];

    public function handle(LegacyProfileImporter $importer): int
    {
        $path = $this->resolvePath((string) $this->argument('file'));

        if ($path === null || ! is_readable($path)) {
            $this->error('No se pudo leer el archivo CSV indicado.');

            return self::FAILURE;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $this->error('No se pudo abrir el archivo CSV indicado.');

            return self::FAILURE;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            $this->error('El CSV no tiene encabezados.');

            return self::FAILURE;
        }

        $header = array_map(fn ($column) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $column)), $header);
        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
            fclose($handle);
            $this->error('Faltan columnas obligatorias: '.implode(', ', $missing));
            $this->line('Consulta docs/LEGACY_PROFILE_IMPORT.md para el formato completo.');

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $errors = 0;
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isEmptyRow($values)) {
                continue;
            }

            if (count($values) !== count($header)) {
                $this->warn("Línea {$line}: el número de columnas no coincide con el encabezado.");
                $errors++;

                continue;
            }

            /** @var array<string, string|null> $row */
            $row = array_combine($header, $values);

            try {
                $prepared = $importer->prepare($row);

                if ($this->option('dry-run')) {
                    $this->line(sprintf('Línea %d: %s %s', $line, $prepared['exists'] ? 'actualizaría' : 'crearía', $prepared['attributes']['legacy_external_key']));
                    $prepared['exists'] ? $updated++ : $created++;

                    continue;
                }

                $importer->persist($prepared['attributes']);
                $prepared['exists'] ? $updated++ : $created++;
            } catch (ValidationException $exception) {
                $this->warn("Línea {$line}: ".implode(' ', $exception->validator->errors()->all()));
                $errors++;
            }
        }

        fclose($handle);
        $this->info("Importación finalizada: {$created} creados, {$updated} actualizados, {$errors} con error.");

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function resolvePath(string $path): ?string
    {
        if (is_file($path)) {
            return $path;
        }

        $candidate = base_path($path);

        return is_file($candidate) ? $candidate : null;
    }

    /** @param array<int, string|null> $values */
    private function isEmptyRow(array $values): bool
    {
        return count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0;
    }
}
