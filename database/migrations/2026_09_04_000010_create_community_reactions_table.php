<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable');
            $table->string('type', 24);
            $table->timestamps();

            // Una persona conserva una única reacción activa por publicación.
            $table->unique(['user_id', 'reactable_type', 'reactable_id'], 'community_reactions_one_per_user_target');
            $table->index(['reactable_type', 'reactable_id', 'type'], 'community_reactions_summary_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_reactions');
    }
};
