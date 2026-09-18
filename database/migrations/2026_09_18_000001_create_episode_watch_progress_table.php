<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_watch_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('episode_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('episode_source_id')->nullable()->constrained('episode_sources')->nullOnDelete();
            $table->string('provider', 50);
            $table->unsignedInteger('position_seconds')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->decimal('progress_percent', 5, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('last_watched_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'episode_id']);
            $table->index(['user_id', 'completed', 'last_watched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_watch_progress');
    }
};
