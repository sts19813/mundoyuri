<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('episode_sources', function (Blueprint $table) {
                $table->string('provider', 50)->default('youtube')->change();
            });

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE episode_sources MODIFY provider VARCHAR(50) NOT NULL');

            return;
        }

        Schema::table('episode_sources', function (Blueprint $table) {
            $table->string('provider', 50)->change();
        });
    }

    public function down(): void
    {
        $allowedProviders = implode("','", ['youtube', 'vimeo', 'byse', 'voe', 'ok', 'netu']);
        $driver = Schema::getConnection()->getDriverName();

        DB::table('episode_sources')
            ->whereNotIn('provider', ['youtube', 'vimeo', 'byse', 'voe', 'ok', 'netu'])
            ->update(['provider' => 'youtube']);

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE episode_sources MODIFY provider ENUM('{$allowedProviders}') NOT NULL");

            return;
        }

        Schema::table('episode_sources', function (Blueprint $table) use ($allowedProviders) {
            $table->enum('provider', explode("','", $allowedProviders))->change();
        });
    }
};
