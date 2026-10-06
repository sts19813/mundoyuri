<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_sections', function (Blueprint $table) {
            $table->string('hero_desktop_image')->nullable()->after('hero_image_url');
            $table->string('hero_mobile_image')->nullable()->after('hero_desktop_image');
            $table->boolean('hero_video_on_mobile')->default(false)->after('hero_video_url');
            $table->boolean('hero_primary_enabled')->default(true)->after('hero_video_on_mobile');
            $table->string('hero_primary_url', 2048)->nullable()->after('hero_primary_label');
            $table->boolean('hero_secondary_enabled')->default(true)->after('hero_primary_url');
            $table->string('hero_secondary_url', 2048)->nullable()->after('hero_secondary_label');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_sections', function (Blueprint $table) {
            $table->dropColumn([
                'hero_desktop_image',
                'hero_mobile_image',
                'hero_video_on_mobile',
                'hero_primary_enabled',
                'hero_primary_url',
                'hero_secondary_enabled',
                'hero_secondary_url',
            ]);
        });
    }
};
