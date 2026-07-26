<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_favorites', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'series_id']);
            $table->index(['series_id', 'created_at']);
        });

        Schema::create('user_follows', function (Blueprint $table): void {
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('followed_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['follower_id', 'followed_id']);
            $table->index(['followed_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_follows');
        Schema::dropIfExists('series_favorites');
    }
};
