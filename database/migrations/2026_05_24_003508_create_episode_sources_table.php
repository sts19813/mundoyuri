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
        Schema::create('episode_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('provider', ['youtube', 'vimeo', 'byse', 'voe', 'ok', 'netu']);
            $table->string('label')->nullable();
            $table->string('video_url');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_sources');
    }
};
