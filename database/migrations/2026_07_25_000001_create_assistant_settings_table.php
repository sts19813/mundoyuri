<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('initial_state', 20)->default('minimized');
            $table->boolean('remember_user_state')->default(true);
            $table->unsignedSmallInteger('initial_delay_seconds')->default(20);
            $table->unsignedSmallInteger('message_interval_seconds')->default(20);
            $table->unsignedSmallInteger('bubble_duration_seconds')->default(7);
            $table->unsignedSmallInteger('peek_duration_seconds')->default(7);
            $table->json('messages');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_settings');
    }
};
