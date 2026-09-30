<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropUnique('episodes_series_season_episode_unique');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->decimal('episode_number', 8, 2)->unsigned()->default(1)->change();
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->unique(['series_id', 'season_number', 'episode_number'], 'episodes_series_season_episode_unique');
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropUnique('episodes_series_season_episode_unique');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->unsignedSmallInteger('episode_number')->default(1)->change();
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->unique(['series_id', 'season_number', 'episode_number'], 'episodes_series_season_episode_unique');
        });
    }
};
