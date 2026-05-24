<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('season_number')->default(1);
            $table->unsignedSmallInteger('episode_number')->default(1);
            $table->date('release_date')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->text('description')->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('moderation_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['series_id', 'season_number', 'episode_number'], 'episodes_series_season_episode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
