<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_legacy_profile', function (Blueprint $table): void {
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legacy_profile_id')->constrained('legacy_profiles')->cascadeOnDelete();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('awarded_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->primary(['badge_id', 'legacy_profile_id']);
            $table->index(['legacy_profile_id', 'awarded_at'], 'legacy_profile_badges_awarded_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_legacy_profile');
    }
};
