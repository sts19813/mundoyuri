<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_presence_sessions', function (Blueprint $table) {
            $table->id();
            // This is the random first-party visitor cookie, never an IP address.
            $table->string('visitor_id', 80);
            $table->uuid('session_id')->unique();
            $table->timestamp('started_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->string('current_path', 255);
            $table->string('current_title', 160)->nullable();
            $table->timestamps();

            $table->index(['visitor_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_presence_sessions');
    }
};
