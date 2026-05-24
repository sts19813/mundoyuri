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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genre_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('content_type', ['series', 'movie'])->default('series');
            $table->enum('status', ['ongoing', 'completed', 'upcoming'])->default('ongoing');
            $table->text('description');
            $table->string('country_of_origin', 120)->nullable();
            $table->unsignedSmallInteger('release_year')->nullable();
            $table->unsignedTinyInteger('total_seasons')->default(1);
            $table->unsignedSmallInteger('total_episodes')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('trailer_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('moderation_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
