<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('series_favorites', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at'], 'series_favorites_activity_index');
        });
    }

    public function down(): void
    {
        Schema::table('series_favorites', function (Blueprint $table): void {
            $table->dropIndex('series_favorites_activity_index');
        });
    }
};
