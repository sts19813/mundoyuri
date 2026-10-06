<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('catalog_sections')
            ->where('slug', 'series-gl')
            ->update([
                'hero_eyebrow' => 'Contenido GL · Actualizado',
                'hero_title' => 'Compartiendo el yuri con elegancia',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('catalog_sections')
            ->where('slug', 'series-gl')
            ->update([
                'hero_eyebrow' => 'Contenido GL · Actualizado diario',
                'hero_title' => 'compartiendo el yuri y GL con elegancia ✨',
                'updated_at' => now(),
            ]);
    }
};
