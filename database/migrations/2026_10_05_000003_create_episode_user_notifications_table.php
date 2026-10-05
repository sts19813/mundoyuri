<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['episode_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_user_notifications');
    }
};
