<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direct_message_favorites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('direct_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['direct_message_id', 'user_id'], 'direct_message_favorites_one_per_user');
            $table->index(['user_id', 'direct_message_id'], 'direct_message_favorites_for_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_message_favorites');
    }
};
