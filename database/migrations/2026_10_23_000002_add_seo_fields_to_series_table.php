<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series', function (Blueprint $table): void {
            $table->json('alternate_titles')->nullable()->after('title');
            $table->string('seo_title')->nullable()->after('description');
            $table->string('seo_description', 320)->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table): void {
            $table->dropColumn(['alternate_titles', 'seo_title', 'seo_description']);
        });
    }
};
