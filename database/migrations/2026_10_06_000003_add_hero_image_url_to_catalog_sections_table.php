<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_sections', function (Blueprint $table) {
            $table->string('hero_image_url', 2048)->nullable()->after('hero_description');
        });

        DB::table('catalog_sections')
            ->where('slug', 'series-gl')
            ->update([
                'hero_title' => 'compartiendo el yuri y GL con elegancia ✨',
                'hero_image_url' => '/assets/img/wallpaper-login.jpg',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('catalog_sections', function (Blueprint $table) {
            $table->dropColumn('hero_image_url');
        });
    }
};
