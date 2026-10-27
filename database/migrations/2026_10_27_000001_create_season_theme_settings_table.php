<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('theme', 20)->default('halloween');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_theme_settings');
    }
};
