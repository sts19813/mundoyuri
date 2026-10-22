<?php

namespace App\Console\Commands;

use App\Models\CatalogSection;
use App\Models\Series;
use App\Services\CatalogImageOptimizer;
use App\Support\SeriesMedia;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('catalog:optimize-images {--dry-run : Calcula el ahorro sin modificar archivos ni base de datos}')]
#[Description('Convierte portadas, banners y hero del catálogo a WebP optimizado')]
class OptimizeCatalogImages extends Command
{
    public function handle(CatalogImageOptimizer $optimizer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totals = ['optimized' => 0, 'skipped' => 0, 'source' => 0, 'optimized_bytes' => 0];

        Series::query()->orderBy('id')->each(function (Series $series) use ($optimizer, $dryRun, &$totals): void {
            $this->optimizeModelImage($series, 'cover_image', 'cover', $optimizer, $dryRun, $totals);
            $this->optimizeModelImage($series, 'banner_image', 'banner', $optimizer, $dryRun, $totals);
        });

        CatalogSection::query()->orderBy('id')->each(function (CatalogSection $section) use ($optimizer, $dryRun, &$totals): void {
            $this->optimizeModelImage($section, 'hero_desktop_image', 'hero-desktop', $optimizer, $dryRun, $totals);
            $this->optimizeModelImage($section, 'hero_mobile_image', 'hero-mobile', $optimizer, $dryRun, $totals);
        });

        $saved = max(0, $totals['source'] - $totals['optimized_bytes']);
        $verb = $dryRun ? 'Se optimizarían' : 'Se optimizaron';
        $this->info(sprintf('%s %d imágenes; ahorro estimado: %s. Omitidas: %d.', $verb, $totals['optimized'], $this->humanBytes($saved), $totals['skipped']));

        return self::SUCCESS;
    }

    /** @param array{optimized: int, skipped: int, source: int, optimized_bytes: int} $totals */
    private function optimizeModelImage(object $model, string $field, string $profile, CatalogImageOptimizer $optimizer, bool $dryRun, array &$totals): void
    {
        $path = $model->{$field};
        if (! is_string($path) || $path === '') {
            return;
        }

        $result = $optimizer->optimizeStored($path, $profile, ! $dryRun);
        if ($result === null) {
            $totals['skipped']++;

            return;
        }

        $totals['optimized']++;
        $totals['source'] += $result['source_bytes'];
        $totals['optimized_bytes'] += $result['optimized_bytes'];

        if ($dryRun || ! $result['path']) {
            return;
        }

        $model->{$field} = $result['path'];
        $model->saveQuietly();

        if ($model instanceof Series) {
            SeriesMedia::deleteIfStored($path);
        } elseif (str_starts_with($path, 'portal-hero-images/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function humanBytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2).' MB';
    }
}
