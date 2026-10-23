<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('category', 40)->default('theme')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('series_tag', function (Blueprint $table) {
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['series_id', 'tag_id']);
        });

        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('aliases')->nullable();
            $table->string('country_of_origin', 120)->nullable();
            $table->text('biography')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('artist_series', function (Blueprint $table) {
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('cast');
            $table->string('character_name')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['artist_id', 'series_id']);
        });

        Schema::create('duos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('duo_series', function (Blueprint $table) {
            $table->foreignId('duo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['duo_id', 'series_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duo_series');
        Schema::dropIfExists('duos');
        Schema::dropIfExists('artist_series');
        Schema::dropIfExists('artists');
        Schema::dropIfExists('series_tag');
        Schema::dropIfExists('tags');
    }
};
