<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_sections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('label', 100)->nullable();
            $table->string('hero_eyebrow', 160)->nullable();
            $table->string('hero_title');
            $table->text('hero_description')->nullable();
            $table->string('hero_video_url')->nullable();
            $table->string('hero_primary_label', 80)->nullable();
            $table->string('hero_secondary_label', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('catalog_section', 50)->default('series-gl')->after('content_type')->index();
        });

        DB::table('catalog_sections')->insert([
            [
                'slug' => 'anime',
                'name' => 'Anime',
                'label' => 'Anime',
                'hero_eyebrow' => 'Anime · Actualizado diario',
                'hero_title' => 'Historias de anime para descubrir, sentir y compartir',
                'hero_description' => 'Explora animes y películas de anime seleccionados por la comunidad de Mundo Yuri.',
                'hero_video_url' => 'https://www.youtube.com/watch?v=YD90os92IM0',
                'hero_primary_label' => 'Explorar anime',
                'hero_secondary_label' => 'Ver novedades',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'series-gl',
                'name' => 'Series GL',
                'label' => 'Serie GL',
                'hero_eyebrow' => 'Contenido GL · Actualizado diario',
                'hero_title' => 'Historias Girls’ Love para descubrir, sentir y compartir',
                'hero_description' => 'Mira series, doramas y películas GL de todo el mundo, subtituladas en español y con nuevos episodios cada semana.',
                'hero_video_url' => null,
                'hero_primary_label' => 'Explorar series GL',
                'hero_secondary_label' => 'Ver novedades',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropIndex(['catalog_section']);
            $table->dropColumn('catalog_section');
        });

        Schema::dropIfExists('catalog_sections');
    }
};
