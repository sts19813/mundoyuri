<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_presence_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->uuid('session_id')->unique();
            $table->timestamp('started_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->unsignedInteger('heartbeat_count')->default(0);
            $table->string('current_path');
            $table->string('current_title')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_seen_at']);
            $table->index(['user_id', 'total_seconds']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_presence_sessions');
    }
};
