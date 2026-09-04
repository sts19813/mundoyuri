<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_profile_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legacy_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claimant_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->text('evidence')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['legacy_profile_id', 'claimant_user_id'], 'legacy_profile_claims_claimant_unique');
            $table->index(['status', 'created_at'], 'legacy_profile_claims_queue_index');
            $table->index(['legacy_profile_id', 'status'], 'legacy_profile_claims_profile_index');
            $table->index(['claimant_user_id', 'status'], 'legacy_profile_claims_claimant_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_profile_claims');
    }
};
